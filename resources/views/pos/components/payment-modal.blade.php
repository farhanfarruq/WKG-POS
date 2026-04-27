<!-- Payment Modal -->
<div x-show="isPaymentModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div 
        x-show="isPaymentModalOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
        @click="isPaymentModalOpen = false"
    ></div>

    <!-- Modal Content -->
    <div 
        x-show="isPaymentModalOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto md:overflow-hidden flex flex-col md:flex-row"
    >
        <!-- Left: Summary -->
        <div class="bg-slate-50 p-6 md:w-1/2 border-b md:border-b-0 md:border-r border-slate-200 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-black text-slate-800">Payment Summary</h3>
                <button @click="isPaymentModalOpen = false" class="md:hidden text-slate-400 bg-slate-200/50 p-1.5 rounded-full">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
            
            <div class="space-y-3 flex-1">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-medium">Order Type</span>
                    <span class="font-bold text-slate-800 capitalize" x-text="orderType.replace('_', ' ')"></span>
                </div>
                <div class="flex justify-between text-sm" x-show="orderType === 'dine_in'">
                    <span class="text-slate-500 font-medium">Table</span>
                    <span class="font-bold text-slate-800" x-text="tableId || '-'"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-medium">Items</span>
                    <span class="font-bold text-slate-800" x-text="cart.length"></span>
                </div>
                <hr class="border-slate-200 my-4">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-medium">Subtotal</span>
                    <span class="font-bold text-slate-700" x-text="formatMoney(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-medium">Tax</span>
                    <span class="font-bold text-slate-700" x-text="formatMoney(tax)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500 font-medium">Service Charge</span>
                    <span class="font-bold text-slate-700" x-text="formatMoney(serviceCharge)"></span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-200">
                <div class="text-sm font-bold text-slate-500 mb-1">Total Due</div>
                <div class="text-4xl font-black text-emerald-600 tracking-tight" x-text="formatMoney(total)"></div>
            </div>
        </div>

        <!-- Right: Action -->
        <div class="p-6 md:w-1/2 bg-white flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-black text-slate-800">Method</h3>
                <button @click="isPaymentModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 p-2 rounded-full transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <button 
                    @click="paymentMethod = 'cash'; amountTendered = total;"
                    :class="paymentMethod === 'cash' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'"
                    class="border-2 rounded-xl p-4 flex flex-col items-center gap-2 transition-all font-bold">
                    <span class="material-symbols-outlined text-3xl">payments</span>
                    Cash
                </button>
                <button 
                    @click="paymentMethod = 'qris'; amountTendered = total;"
                    :class="paymentMethod === 'qris' ? 'bg-emerald-50 border-emerald-500 text-emerald-700' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'"
                    class="border-2 rounded-xl p-4 flex flex-col items-center gap-2 transition-all font-bold">
                    <span class="material-symbols-outlined text-3xl">qr_code_scanner</span>
                    QRIS
                </button>
            </div>

            <!-- Cash Input -->
            <div x-show="paymentMethod === 'cash'" x-collapse class="mb-6 space-y-3">
                <div>
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Amount Tendered</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-500">Rp</span>
                        <input 
                            type="number" 
                            x-model.number="amountTendered" 
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-lg font-black focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all text-slate-800">
                    </div>
                </div>

                <!-- Quick Cash Buttons -->
                <div class="grid grid-cols-3 gap-2">
                    <button @click="amountTendered = total" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-lg text-sm">Exact</button>
                    <button @click="amountTendered = 50000" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-lg text-sm">50K</button>
                    <button @click="amountTendered = 100000" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-lg text-sm">100K</button>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center mt-2">
                    <span class="font-bold text-slate-500">Change</span>
                    <span class="font-black text-xl" :class="change >= 0 ? 'text-slate-800' : 'text-rose-500'" x-text="formatMoney(change)"></span>
                </div>
            </div>

    <button 
        @click="processCheckout()"
        :disabled="isLoading || (paymentMethod === 'cash' && amountTendered < total)"
        :class="(paymentMethod === 'cash' && amountTendered < total) ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-900 shadow-lg shadow-emerald-500/30'"
        class="w-full py-4 rounded-xl font-black text-lg flex items-center justify-center gap-2 mt-8 transition-all relative overflow-hidden">
                <span x-show="!isLoading">Confirm Payment</span>
                <span x-show="isLoading" class="flex items-center gap-2">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-slate-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </span>
            </button>
        </div>
    </div>
</div>
