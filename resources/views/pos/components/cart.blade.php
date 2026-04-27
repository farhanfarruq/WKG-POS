<div class="flex flex-col h-full overflow-hidden bg-white">
    <!-- Customer & Order Type Section (Fixed Top) -->
    <div class="p-3 border-b border-slate-100 bg-white shrink-0">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-base font-black text-slate-800">Current Order</h2>
            <button @click="openNewOrder()" class="text-[10px] font-bold text-rose-500 bg-rose-50 px-2 py-1 rounded-lg hover:bg-rose-100 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">delete</span> Clear
            </button>
        </div>

        <!-- Order Type Toggle -->
        <div class="bg-slate-100 p-1 rounded-xl flex mb-3">
            <button 
                @click="orderType = 'dine_in'"
                :class="orderType === 'dine_in' ? 'bg-white text-emerald-600 shadow-sm font-bold' : 'text-slate-500 font-medium'"
                class="flex-1 py-1.5 text-xs rounded-lg transition-all flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-[16px]">restaurant</span> Dine In
            </button>
            <button 
                @click="orderType = 'takeaway'"
                :class="orderType === 'takeaway' ? 'bg-white text-emerald-600 shadow-sm font-bold' : 'text-slate-500 font-medium'"
                class="flex-1 py-1.5 text-xs rounded-lg transition-all flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-[16px]">takeout_dining</span> Takeaway
            </button>
        </div>

        <!-- Compact Form Fields -->
        <div class="grid grid-cols-2 gap-2">
            <div x-show="orderType === 'dine_in'" x-collapse>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-sm">table_restaurant</span>
                    <input type="text" x-model="tableId" placeholder="Meja" class="w-full pl-7 pr-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold outline-none">
                </div>
            </div>
            <div :class="orderType === 'dine_in' ? '' : 'col-span-2'">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-sm">person</span>
                    <input type="text" x-model="customerName" placeholder="Pelanggan" class="w-full pl-7 pr-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold outline-none">
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items List (Scrollable Area) -->
    <div class="flex-1 overflow-y-auto p-3 bg-slate-50 min-h-0">
        <template x-if="cart.length === 0">
            <div class="h-full flex flex-col items-center justify-center text-slate-400 space-y-2">
                <span class="material-symbols-outlined text-4xl">shopping_cart</span>
                <p class="text-xs font-medium">Belum ada pesanan</p>
            </div>
        </template>

        <div class="space-y-2">
            <template x-for="(item, index) in cart" :key="index">
                <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex flex-col gap-1 group">
                    <div class="flex justify-between items-start">
                        <div class="pr-6">
                            <h4 class="font-bold text-sm text-slate-800 leading-tight line-clamp-1" x-text="item.name"></h4>
                            <div class="text-[10px] font-semibold text-slate-500" x-text="formatMoney(item.price)"></div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <div class="font-black text-emerald-600 text-sm" x-text="formatMoney(item.price * item.quantity)"></div>
                            <button @click="removeFromCart(index)" class="text-rose-500 hover:text-rose-600 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <input type="text" x-model="item.note" placeholder="Catatan..." class="text-[10px] bg-slate-50 px-2 py-1 rounded-md border border-transparent outline-none w-1/2">
                        
                        <div class="flex items-center bg-slate-100 rounded-lg p-0.5 border border-slate-200">
                            <button @click="decreaseQty(index)" class="w-6 h-6 flex items-center justify-center bg-white rounded text-slate-600 shadow-sm">
                                <span class="material-symbols-outlined text-[14px]">remove</span>
                            </button>
                            <span class="w-6 text-center font-bold text-xs text-slate-800" x-text="item.quantity"></span>
                            <button @click="increaseQty(index)" class="w-6 h-6 flex items-center justify-center bg-white rounded text-slate-600 shadow-sm">
                                <span class="material-symbols-outlined text-[14px]">add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Summary & Checkout (Fixed Bottom) -->
    <div class="p-3 bg-white border-t border-slate-100 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.02)] shrink-0">
        <div class="space-y-1 mb-3">
            <div class="flex justify-between text-xs">
                <span class="text-slate-500 font-medium">Subtotal</span>
                <span class="font-bold text-slate-700" x-text="formatMoney(subtotal)"></span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500 font-medium">Tax (11%)</span>
                <span class="font-bold text-slate-700" x-text="formatMoney(tax)"></span>
            </div>
            <div class="flex justify-between text-xs">
                <span class="text-slate-500 font-medium">Service Charge (5%)</span>
                <span class="font-bold text-slate-700" x-text="formatMoney(serviceCharge)"></span>
            </div>
            <div class="pt-1 border-t border-slate-100 flex justify-between items-end">
                <span class="text-xs font-bold text-slate-800">Total</span>
                <span class="text-xl font-black text-emerald-600" x-text="formatMoney(total)"></span>
            </div>
        </div>
        
        <button 
            @click="isPaymentModalOpen = true; amountTendered = total;"
            :disabled="cart.length === 0"
            :class="cart.length === 0 ? 'bg-slate-300 cursor-not-allowed text-slate-500' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-900 shadow-lg shadow-emerald-500/30'"
            class="w-full py-3 rounded-xl font-black text-base flex items-center justify-center gap-2 transition-all">
            <span>Charge</span>
            <span class="material-symbols-outlined">arrow_forward</span>
        </button>
    </div>
</div>
