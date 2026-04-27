@extends('layouts.app')

@section('title', 'POS Terminal')

@section('content')
<div class="flex min-h-screen" x-data="posSystem()">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 fixed h-full z-40 hidden md:flex flex-col">
        <div class="p-6 border-b border-slate-800">
            <div class="mb-8 flex items-center gap-3">
                <img src="{{ asset('gambar/logo.png') }}" alt="Warkop POS Logo" class="h-10 object-contain drop-shadow-md">
                <span class="text-xl font-black text-emerald-500 tracking-tighter uppercase">Warkop POS</span>
            </div>
            <div class="flex items-center gap-3 mb-6">
                <div class="relative w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white overflow-hidden shrink-0">
                    <template x-if="user?.avatar_url">
                        <img :src="user.avatar_url" :alt="user.name" class="absolute inset-0 block w-full h-full max-w-none object-cover">
                    </template>
                    <span class="material-symbols-outlined" x-show="!user?.avatar_url && !user">account_circle</span>
                    <span x-show="!user?.avatar_url && user" x-text="user?.name?.charAt(0)" class="text-xl font-bold uppercase"></span>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-200" x-text="user?.name || 'Kasir'"></div>
                    <div class="text-[10px] font-bold text-slate-500 uppercase">
                        Role: <span x-text="user?.roles?.[0] || 'Guest'"></span>
                    </div>
                </div>
            </div>
            <button @click="openNewOrder()" class="w-full bg-emerald-500 text-slate-900 py-3 rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg hover:bg-emerald-400 transition-colors">
                <span class="material-symbols-outlined">add_circle</span> New Order
            </button>
        </div>
        <nav class="flex-1 py-4 flex flex-col gap-1">
            <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-6 py-4 font-semibold hover:text-white transition-colors {{ request()->routeIs('pos.*') ? 'active-tab' : 'text-slate-400 hover:bg-slate-800' }}">
                <span class="material-symbols-outlined">point_of_sale</span> POS Terminal
            </a>
            <a href="{{ route('kds.index') }}" class="flex items-center gap-3 px-6 py-4 font-semibold hover:text-white transition-colors {{ request()->routeIs('kds.*') ? 'active-tab' : 'text-slate-400 hover:bg-slate-800' }}">
                <span class="material-symbols-outlined">kitchen</span> Kitchen Display
            </a>
            <a href="#" @click.prevent="window.location.href = shift ? '{{ route('shift.close') }}' : '{{ route('shift.open') }}'" class="flex items-center gap-3 px-6 py-4 font-semibold hover:text-white transition-colors {{ request()->routeIs('shift.*') ? 'active-tab' : 'text-slate-400 hover:bg-slate-800' }}">
                <span class="material-symbols-outlined">schedule</span> Shift Management
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 md:ml-64 flex flex-col">
        <!-- TOPBAR -->
        <header class="h-16 bg-slate-900 border-b border-slate-800 flex justify-between items-center px-6 sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <div class="bg-emerald-500/10 border border-emerald-500/30 px-3 py-1 rounded-full flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :class="shift ? 'bg-emerald-500' : 'bg-red-500'"></span>
                    <span class="text-xs font-bold" :class="shift ? 'text-emerald-500' : 'text-red-500'" x-text="shift ? 'Shift Active' : 'No Active Shift'"></span>
                </div>
                <div class="text-slate-400 text-sm font-semibold" x-text="currentTime"></div>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <div class="hidden sm:flex items-center gap-3 overflow-hidden rounded-full border border-slate-800 bg-slate-800/70 px-3 py-2">
                    <div class="relative h-9 w-9 overflow-hidden rounded-full bg-slate-700 flex items-center justify-center text-white shrink-0">
                        <template x-if="user?.avatar_url">
                            <img :src="user.avatar_url" :alt="user.name" class="absolute inset-0 block w-full h-full max-w-none object-cover">
                        </template>
                        <span x-show="!user?.avatar_url && user" x-text="user?.name?.charAt(0)" class="text-sm font-bold uppercase"></span>
                        <span class="material-symbols-outlined text-base" x-show="!user?.avatar_url && !user">person</span>
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-semibold text-slate-200" x-text="user?.name || 'Kasir'"></div>
                        <div class="text-[10px] font-bold uppercase text-slate-500" x-text="user?.roles?.[0] || 'Guest'"></div>
                    </div>
                </div>
                <button @click="logout()" class="text-rose-500 border border-rose-500/30 px-4 py-2 rounded-lg text-sm font-bold bg-slate-800 hover:bg-rose-500 hover:text-white transition-colors">
                    Logout
                </button>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <div class="flex-1 flex flex-col relative pb-20 md:pb-0">
            @yield('pos-content')
        </div>
    </main>

    <!-- MOBILE FOOTER -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-slate-900 border-t border-slate-800 z-50 flex justify-around items-center">
        <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'text-emerald-500' : 'text-slate-500' }} flex flex-col items-center"><span class="material-symbols-outlined">point_of_sale</span><span class="text-[10px] font-bold mt-1">POS</span></a>
        <a href="{{ route('kds.index') }}" class="{{ request()->routeIs('kds.*') ? 'text-emerald-500' : 'text-slate-500' }} flex flex-col items-center"><span class="material-symbols-outlined">kitchen</span><span class="text-[10px] font-bold mt-1">KDS</span></a>
        <a href="#" @click.prevent="window.location.href = shift ? '{{ route('shift.close') }}' : '{{ route('shift.open') }}'" class="{{ request()->routeIs('shift.*') ? 'text-emerald-500' : 'text-slate-500' }} flex flex-col items-center"><span class="material-symbols-outlined">schedule</span><span class="text-[10px] font-bold mt-1">SHIFT</span></a>
    </nav>
    
    <!-- GLOBAL COMPONENTS (High Z-Index) -->
    
    <!-- MOBILE CART DRAWER -->
    <div 
        x-show="isCartOpen" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="md:hidden fixed inset-0 bg-white z-[70] flex flex-col"
    >
        <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-white shrink-0">
            <h2 class="text-xl font-black text-slate-800">Detail Pesanan</h2>
            <button @click="isCartOpen = false" class="w-10 h-10 flex items-center justify-center bg-slate-100 rounded-full text-slate-500">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-hidden">
            @include('pos.components.cart')
        </div>
    </div>

    <!-- TOAST NOTIFICATION (Top Center) -->
    <div 
        x-show="toast.show" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[100] w-auto max-w-[90%]"
    >
        <div class="bg-slate-800 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 border border-slate-700">
            <span class="material-symbols-outlined text-emerald-400">check_circle</span>
            <span class="text-sm font-bold whitespace-nowrap" x-text="toast.message"></span>
        </div>
    </div>

    <!-- FLOATING VIEW CART BUTTON -->
    <div class="md:hidden fixed bottom-24 inset-x-0 z-[90] flex justify-center px-4" x-show="cart.length > 0 && !isCartOpen" x-transition>
        <button 
            @click="isCartOpen = true"
            class="w-full max-w-md bg-emerald-500 text-slate-900 py-4 rounded-2xl shadow-[0_20px_50px_rgba(98,121,124,0.3)] flex items-center justify-between px-6 font-black border-2 border-white animate-bounce-subtle">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined">shopping_basket</span>
                    <span class="absolute -top-2 -right-2 bg-rose-500 text-white text-[10px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-emerald-500" x-text="cart.length"></span>
                </div>
                <span>Lihat Keranjang</span>
            </div>
            <span x-text="formatMoney(total)"></span>
        </button>
    </div>

    <!-- PAYMENT MODAL -->
    @include('pos.components.payment-modal')
    
    <!-- Scripts for POS -->
    <script src="{{ asset('js/pos.js') }}"></script>
</div>
@endsection
