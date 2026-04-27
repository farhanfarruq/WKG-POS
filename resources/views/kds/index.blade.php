@extends('layouts.kds')

@section('content')
<div class="h-screen flex flex-col" x-data="kdsSystem()">
    
    <!-- HEADER -->
    <header class="bg-slate-900 border-b border-slate-800 p-4 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-3">
                <img src="{{ asset('gambar/logo.png') }}" alt="Warkop POS Logo" class="h-8 object-contain drop-shadow-sm">
                Kitchen Display
            </h1>
            <div class="flex items-center gap-2 bg-slate-800 px-3 py-1.5 rounded-full text-sm font-semibold border border-slate-700">
                <div class="w-2 h-2 rounded-full" :class="isOnline ? 'bg-emerald-500' : 'bg-red-500'"></div>
                <span :class="isOnline ? 'text-emerald-400' : 'text-red-400'" x-text="isOnline ? 'Online' : 'Offline'"></span>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="text-slate-400 font-bold font-mono text-xl" x-text="currentTime"></div>
            
            <div class="flex items-center gap-2">
                <div class="mr-3 flex items-center gap-3 overflow-hidden rounded-full border border-slate-700 bg-slate-800 px-3 py-2" x-show="user">
                    <div class="relative h-10 w-10 overflow-hidden rounded-full bg-slate-700 flex items-center justify-center text-white shrink-0">
                        <template x-if="user?.avatar_url">
                            <img :src="user.avatar_url" :alt="user.name" class="absolute inset-0 block w-full h-full max-w-none object-cover">
                        </template>
                        <span x-show="!user?.avatar_url && user" x-text="user?.name?.charAt(0)" class="text-sm font-bold uppercase"></span>
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-bold text-white" x-text="user?.name"></div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-slate-400" x-text="user?.roles?.[0] || 'Crew'"></div>
                    </div>
                </div>

                <!-- POS Link (Hidden for Barista) -->
                <template x-if="user && !user.roles.includes('barista')">
                    <a href="{{ route('pos.index') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 p-2 rounded-xl border border-slate-700 transition-colors flex items-center gap-2 px-4">
                        <span class="material-symbols-outlined text-[20px]">point_of_sale</span>
                        <span class="text-xs font-bold uppercase tracking-wider">POS Terminal</span>
                    </a>
                </template>

                <!-- Logout -->
                <button @click="logout()" class="bg-slate-800 hover:bg-rose-500/20 text-slate-300 hover:text-rose-400 p-2 rounded-xl border border-slate-700 hover:border-rose-500/50 transition-all flex items-center gap-2 px-4">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span class="text-xs font-bold uppercase tracking-wider">Logout</span>
                </button>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT KANBAN -->
    <main class="flex-1 overflow-x-auto overflow-y-hidden p-6">
        <div class="flex gap-6 h-full min-w-max">
            
            <!-- COLUMN: PENDING -->
            <div class="w-96 flex flex-col h-full bg-slate-900/50 rounded-2xl border border-slate-800 flex-shrink-0">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50 rounded-t-2xl">
                    <h2 class="text-lg font-bold text-rose-400 flex items-center gap-2">
                        <span class="material-symbols-outlined">pending_actions</span>
                        PENDING
                    </h2>
                    <span class="bg-rose-500/20 text-rose-400 px-2 py-1 rounded-md text-xs font-black" x-text="pendingOrders.length"></span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <template x-for="order in pendingOrders" :key="order.id">
                        <div class="bg-slate-800 rounded-xl border-l-4 border-l-rose-500 p-4 shadow-lg">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="text-xs text-slate-400 font-bold mb-1" x-text="'#' + order.receipt_number"></div>
                                    <div class="font-black text-white text-lg leading-none" x-text="order.order_type === 'dine_in' ? 'Table ' + order.table_id : 'Takeaway'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-400 font-bold mb-1" x-text="getTimeDiff(order.created_at)"></div>
                                    <div class="text-sm font-semibold text-slate-300" x-text="order.customer_name"></div>
                                </div>
                            </div>
                            
                            <div class="space-y-2 mt-4 pt-4 border-t border-slate-700">
                                <template x-for="item in order.items.filter(i => i.status === 'pending')" :key="item.id">
                                    <div class="flex justify-between items-start group">
                                        <div class="flex-1">
                                            <div class="font-bold text-slate-200"><span x-text="item.quantity + 'x'"></span> <span x-text="item.product.name"></span></div>
                                            <div x-show="item.notes" class="text-xs text-rose-300 font-semibold mt-0.5 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">warning</span> <span x-text="item.notes"></span>
                                            </div>
                                        </div>
                                        <button @click="updateItemStatus(order.id, item.id, 'processing')" class="text-xs bg-indigo-500 hover:bg-indigo-400 text-white font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                            Process
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- COLUMN: PROCESSING -->
            <div class="w-96 flex flex-col h-full bg-slate-900/50 rounded-2xl border border-slate-800 flex-shrink-0">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50 rounded-t-2xl">
                    <h2 class="text-lg font-bold text-indigo-400 flex items-center gap-2">
                        <span class="material-symbols-outlined">skillet</span>
                        PROCESSING
                    </h2>
                    <span class="bg-indigo-500/20 text-indigo-400 px-2 py-1 rounded-md text-xs font-black" x-text="processingOrders.length"></span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <template x-for="order in processingOrders" :key="order.id">
                        <div class="bg-slate-800 rounded-xl border-l-4 border-l-indigo-500 p-4 shadow-lg">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="text-xs text-slate-400 font-bold mb-1" x-text="'#' + order.receipt_number"></div>
                                    <div class="font-black text-white text-lg leading-none" x-text="order.order_type === 'dine_in' ? 'Table ' + order.table_id : 'Takeaway'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-400 font-bold mb-1" x-text="getTimeDiff(order.created_at)"></div>
                                    <div class="text-sm font-semibold text-slate-300" x-text="order.customer_name"></div>
                                </div>
                            </div>
                            
                            <div class="space-y-2 mt-4 pt-4 border-t border-slate-700">
                                <template x-for="item in order.items.filter(i => i.status === 'processing')" :key="item.id">
                                    <div class="flex justify-between items-start group">
                                        <div class="flex-1">
                                            <div class="font-bold text-indigo-300"><span x-text="item.quantity + 'x'"></span> <span x-text="item.product.name"></span></div>
                                            <div x-show="item.notes" class="text-xs text-rose-300 font-semibold mt-0.5 flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">warning</span> <span x-text="item.notes"></span>
                                            </div>
                                        </div>
                                        <button @click="updateItemStatus(order.id, item.id, 'completed')" class="text-xs bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-black px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                            Done
                                        </button>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Complete Order Button (if all items are done) -->
                            <button 
                                @click="completeOrder(order.id)"
                                class="w-full mt-4 bg-emerald-500/20 hover:bg-emerald-500 text-emerald-400 hover:text-slate-900 border border-emerald-500/50 py-2 rounded-lg font-bold transition-all text-sm">
                                Mark Order Complete
                            </button>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- COLUMN: READY TO SERVE -->
            <div class="w-96 flex flex-col h-full bg-slate-900/50 rounded-2xl border border-slate-800 flex-shrink-0">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50 rounded-t-2xl">
                    <h2 class="text-lg font-bold text-emerald-400 flex items-center gap-2">
                        <span class="material-symbols-outlined">delivery_dining</span>
                        READY TO SERVE
                    </h2>
                    <span class="bg-emerald-500/20 text-emerald-400 px-2 py-1 rounded-md text-xs font-black" x-text="readyOrders.length"></span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    <template x-for="order in readyOrders" :key="order.id">
                        <div class="bg-slate-800 rounded-xl border-l-4 border-l-emerald-500 p-4 shadow-lg animate-pulse-slow">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="text-xs text-slate-400 font-bold mb-1" x-text="'#' + order.receipt_number"></div>
                                    <div class="font-black text-white text-lg leading-none" x-text="order.order_type === 'dine_in' ? 'Table ' + order.table_id : 'Takeaway'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-400 font-bold mb-1" x-text="getTimeDiff(order.created_at)"></div>
                                    <div class="text-sm font-semibold text-slate-300" x-text="order.customer_name"></div>
                                </div>
                            </div>
                            
                            <div class="space-y-1 mt-4 pt-4 border-t border-slate-700">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-400" x-text="item.quantity + 'x ' + item.product.name"></span>
                                        <span class="text-emerald-500 material-symbols-outlined text-sm">check_circle</span>
                                    </div>
                                </template>
                            </div>
                            
                            <button 
                                @click="serveOrder(order.id)"
                                class="w-full mt-4 bg-emerald-500 hover:bg-emerald-400 text-slate-900 py-3 rounded-lg font-black transition-all text-sm flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20">
                                <span class="material-symbols-outlined">concierge_bell</span>
                                SERVE / DELIVER
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- COLUMN: COMPLETED TODAY -->
            <div class="w-96 flex flex-col h-full bg-slate-900/30 rounded-2xl border border-slate-800/50 flex-shrink-0 opacity-80">
                <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/30 rounded-t-2xl">
                    <h2 class="text-lg font-bold text-slate-400 flex items-center gap-2">
                        <span class="material-symbols-outlined">history</span>
                        COMPLETED TODAY
                    </h2>
                    <span class="bg-slate-700 text-slate-400 px-2 py-1 rounded-md text-xs font-black" x-text="completedOrders.length"></span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4 grayscale-[0.5]">
                    <template x-for="order in completedOrders" :key="order.id">
                        <div class="bg-slate-800/50 rounded-xl border-l-4 border-l-slate-600 p-4 border border-slate-700/50">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="text-[10px] text-slate-500 font-bold mb-0.5" x-text="'#' + order.receipt_number"></div>
                                    <div class="font-bold text-slate-300 text-sm" x-text="order.order_type === 'dine_in' ? 'Table ' + order.table_id : 'Takeaway'"></div>
                                </div>
                                <div class="text-[10px] text-slate-500 font-bold" x-text="getTimeDiff(order.created_at)"></div>
                            </div>
                            <div class="space-y-0.5 mt-2 pt-2 border-t border-slate-700/30">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex justify-between text-[11px]">
                                        <span class="text-slate-500" x-text="item.quantity + 'x ' + item.product.name"></span>
                                        <span class="text-slate-600 material-symbols-outlined text-[12px]">check</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
    </main>

    <!-- Scripts -->
    <script src="{{ asset('js/kds.js') }}?v={{ time() }}"></script>
</div>
@endsection
