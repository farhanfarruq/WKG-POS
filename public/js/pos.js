document.addEventListener('alpine:init', () => {
    Alpine.data('posSystem', () => ({
        user: null,
        shift: null,
        categories: [],
        products: [],
        filteredProducts: [],
        activeCategory: 'All',
        searchQuery: '',
        
        cart: [],
        orderType: 'dine_in', // dine_in, takeaway, delivery
        customerName: '',
        tableId: '',
        notes: '',
        
        taxRate: 0.11,
        serviceRate: 0.05,
        
        isPaymentModalOpen: false,
        paymentMethod: 'cash',
        amountTendered: 0,
        
        currentTime: '',
        clockInterval: null,
        
        isCartOpen: false,
        isLoading: false,
        
        toast: {
            show: false,
            message: '',
            type: 'success'
        },

        init() {
            this.setupAxios();
            this.startClock();
            this.checkAuth();
        },

        setupAxios() {
            axios.defaults.baseURL = '/api/v1';
            axios.defaults.headers.common['Accept'] = 'application/json';
            const token = sessionStorage.getItem('auth_token');
            if (token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            }
        },

        startClock() {
            const updateTime = () => {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' · ' + now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            };
            updateTime();
            this.clockInterval = setInterval(updateTime, 1000);
        },

        async checkAuth() {
            try {
                const response = await axios.get('/auth/me');
                this.user = response.data.data;
                await this.checkShift();
                await this.fetchMenu();
            } catch (error) {
                if (error.response?.status === 401) {
                    window.location.href = '/login';
                }
            }
        },

        async checkShift() {
            try {
                const response = await axios.get('/shifts/current');
                this.shift = response.data.data;
                if (!this.shift) {
                    // No active shift, redirect to open shift
                    window.location.href = '/shift/open';
                }
            } catch (error) {
                console.error('Failed to fetch shift status', error);
            }
        },

        async fetchMenu() {
            try {
                const response = await axios.get('/menu');
                const menuData = response.data.data;
                
                this.categories = ['All', ...menuData.map(c => c.category)];
                
                this.products = [];
                menuData.forEach(cat => {
                    cat.products.forEach(prod => {
                        this.products.push({
                            ...prod,
                            category_name: cat.category
                        });
                    });
                });
                
                this.filterMenu();
            } catch (error) {
                console.error('Failed to fetch menu', error);
            }
        },

        setCategory(category) {
            this.activeCategory = category;
            this.filterMenu();
        },

        filterMenu() {
            let filtered = this.products;
            
            if (this.activeCategory !== 'All') {
                filtered = filtered.filter(p => p.category_name === this.activeCategory);
            }
            
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                filtered = filtered.filter(p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q));
            }
            
            this.filteredProducts = filtered;
        },

        addToCart(product) {
            const existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image_url,
                    quantity: 1,
                    note: ''
                });
            }
            this.showToast(`${product.name} ditambahkan`);
        },

        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3000);
        },

        increaseQty(index) {
            this.cart[index].quantity++;
        },

        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
            } else {
                this.removeFromCart(index);
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get tax() {
            return Math.round(this.subtotal * this.taxRate);
        },

        get serviceCharge() {
            return Math.round(this.subtotal * this.serviceRate);
        },

        get total() {
            return this.subtotal + this.tax + this.serviceCharge;
        },

        get categorizedMenu() {
            if (this.activeCategory !== 'All' || this.searchQuery.trim() !== '') {
                return [{
                    name: this.activeCategory === 'All' ? 'Search Results' : this.activeCategory,
                    products: this.filteredProducts
                }];
            }
            
            const grouped = [];
            this.products.forEach(p => {
                let cat = grouped.find(c => c.name === p.category_name);
                if (!cat) {
                    cat = { name: p.category_name, products: [] };
                    grouped.push(cat);
                }
                cat.products.push(p);
            });
            
            return grouped;
        },

        get change() {
            if (this.paymentMethod !== 'cash') return 0;
            return Math.max(0, this.amountTendered - this.total);
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        },

        openNewOrder() {
            this.cart = [];
            this.orderType = 'dine_in';
            this.customerName = '';
            this.tableId = '';
            this.notes = '';
            this.isPaymentModalOpen = false;
        },

        async processCheckout() {
            if (this.cart.length === 0) return;
            if (this.orderType === 'dine_in' && !this.tableId) {
                alert('Table Number is required for Dine In');
                return;
            }

            if (this.paymentMethod === 'cash' && this.amountTendered < this.total) {
                alert('Amount tendered is less than total');
                return;
            }

            this.isLoading = true;

            try {
                if (!this.shift) {
                    alert('Buka shift terlebih dahulu!');
                    return;
                }

                // 1. Create Order
                const orderData = {
                    shift_id: this.shift.id,
                    order_type: this.orderType,
                    table_id: (this.orderType === 'dine_in' && this.tableId) ? (parseInt(this.tableId) || null) : null,
                    customer_name: this.customerName || 'Walk-in Customer',
                    notes: this.notes
                };

                const orderRes = await axios.post('/orders', orderData);
                const orderId = orderRes.data.data.id;

                // 2. Add Items
                for (const item of this.cart) {
                    await axios.post(`/orders/${orderId}/items`, {
                        product_id: item.id,
                        quantity: item.quantity,
                        notes: item.note
                    });
                }

                // 3. Checkout (Pay)
                await axios.post(`/orders/${orderId}/checkout`, {
                    payments: [
                        {
                            method: this.paymentMethod,
                            amount: this.paymentMethod === 'cash' ? parseFloat(this.amountTendered) : this.total,
                        }
                    ]
                });

                // Success
                alert('Payment Successful!');
                this.openNewOrder();

            } catch (error) {
                console.error('Checkout failed', error);
                const errorData = error.response?.data;
                let errorMsg = 'Checkout failed';
                
                if (errorData?.errors) {
                    errorMsg = Object.values(errorData.errors).flat().join('\n');
                } else if (errorData?.message) {
                    errorMsg = errorData.message;
                }
                
                alert(errorMsg);
            } finally {
                this.isLoading = false;
            }
        },

        logout() {
            sessionStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
    }));
});
