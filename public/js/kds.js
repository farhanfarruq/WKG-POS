document.addEventListener('alpine:init', () => {
    Alpine.data('kdsSystem', () => ({
        isOnline: true,
        currentTime: '',
        orders: [],
        user: null,
        pollInterval: null,

        init() {
            this.checkAuth();
            this.startClock();
            this.fetchOrders();
            
            // Poll every 5 seconds
            this.pollInterval = setInterval(() => {
                this.fetchOrders();
            }, 5000);
        },

        async checkAuth() {
            try {
                const response = await axios.get('/auth/me');
                this.user = response.data.data;
            } catch (error) {
                if (error.response?.status === 401) {
                    window.location.href = '/login';
                }
            }
        },

        logout() {
            sessionStorage.removeItem('auth_token');
            window.location.href = '/login';
        },

        startClock() {
            const updateTime = () => {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            };
            updateTime();
            setInterval(updateTime, 1000);
        },

        async fetchOrders() {
            try {
                const response = await axios.get('/kds');
                this.orders = response.data.data;
                this.isOnline = true;
            } catch (error) {
                console.error('Failed to fetch KDS orders', error);
                this.isOnline = false;
                if (error.response?.status === 401 || error.response?.status === 403) {
                    window.location.href = '/login';
                }
            }
        },

        get pendingOrders() {
            // An order is in PENDING column if it's not ready/completed/cancelled
            // AND at least one item is still 'pending'
            return this.orders.filter(order => 
                order.status !== 'ready' && 
                order.status !== 'completed' &&
                order.status !== 'cancelled' &&
                order.items.some(i => i.status === 'pending')
            );
        },

        get processingOrders() {
            // An order is in PROCESSING column if it's not ready/completed/cancelled
            // AND it has NO pending items, but has at least one 'processing' item
            return this.orders.filter(order => 
                order.status !== 'ready' && 
                order.status !== 'completed' &&
                order.status !== 'cancelled' &&
                !order.items.some(i => i.status === 'pending') &&
                order.items.some(i => i.status === 'processing')
            );
        },

        get readyOrders() {
            // Orders with status 'ready'
            return this.orders.filter(order => order.status === 'ready');
        },

        get completedOrders() {
            // Orders with status 'completed' (Today's history)
            return this.orders.filter(order => order.status === 'completed');
        },

        async updateItemStatus(orderId, itemId, status) {
            try {
                await axios.patch(`/kds/${orderId}/items/${itemId}/status`, { status });
                this.fetchOrders();
            } catch (error) {
                console.error('Failed to update item status', error);
                alert('Failed to update item status');
            }
        },

        async completeOrder(orderId) {
            try {
                await axios.post(`/kds/${orderId}/complete`);
                this.fetchOrders();
            } catch (error) {
                console.error('Failed to complete order', error);
                alert('Failed to complete order');
            }
        },

        async serveOrder(orderId) {
            try {
                await axios.post(`/kds/${orderId}/serve`);
                this.fetchOrders();
            } catch (error) {
                console.error('Failed to serve order', error);
                alert('Failed to serve order');
            }
        },

        getTimeDiff(createdAt) {
            const created = new Date(createdAt);
            const now = new Date();
            const diffMs = now - created;
            const diffMins = Math.floor(diffMs / 60000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            const diffHrs = Math.floor(diffMins / 60);
            return `${diffHrs}h ${diffMins % 60}m ago`;
        }
    }));
});
