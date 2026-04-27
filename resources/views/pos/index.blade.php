@extends('layouts.pos')

@section('pos-content')
<!-- Main POS Container -->
<div class="flex-1 flex flex-col md:flex-row h-full overflow-hidden relative">
    
    <!-- LEFT PANEL: MENU & CATEGORIES -->
    <div class="flex-1 flex flex-col bg-slate-50 relative overflow-hidden h-full">
        
        <!-- Category & Search Bar (Fixed Top) -->
        <div class="p-4 bg-white border-b border-slate-200 z-10 shadow-sm flex flex-col sm:flex-row gap-4 items-center justify-between shrink-0">
            
            <!-- Categories (Horizontal Scroll) -->
            <div class="flex gap-2 overflow-x-auto no-scrollbar w-full sm:w-auto pb-1" id="categoryContainer">
                <template x-for="category in categories" :key="category">
                    <button 
                        @click="setCategory(category)"
                        :class="activeCategory === category ? 'bg-emerald-500 text-white font-bold shadow-md shadow-emerald-500/30' : 'bg-slate-100 text-slate-600 font-semibold hover:bg-slate-200'"
                        class="px-5 py-2.5 rounded-full whitespace-nowrap transition-all duration-200 text-sm"
                        x-text="category">
                    </button>
                </template>
            </div>
            
            <!-- Search -->
            <div class="relative w-full sm:w-64 shrink-0 group">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">search</span>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input="filterMenu()"
                    placeholder="Cari Menu..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-100 border-2 border-transparent focus:border-emerald-500 focus:bg-white rounded-2xl outline-none transition-all font-medium text-sm text-slate-800 placeholder-slate-400">
            </div>
        </div>

        <!-- Product Grid (Scrollable Area) -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-slate-50/50 relative min-h-0">
            <div class="space-y-8">
                <template x-for="category in categorizedMenu" :key="category.name">
                    <div x-show="category.products.length > 0">
                        <!-- Category Header - Only show if in 'All' and no search -->
                        <div x-show="activeCategory === 'All' && searchQuery.trim() === ''" 
                             class="flex items-center gap-4 mb-4">
                            <h2 class="text-lg font-black text-slate-800 uppercase tracking-wider" x-text="category.name"></h2>
                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            <template x-for="product in category.products" :key="product.id">
                                <button 
                                    @click="addToCart(product)"
                                    class="bg-white rounded-2xl p-3 border border-slate-200 hover:border-emerald-500 hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 text-left group flex flex-col relative overflow-hidden h-full">
                                    
                                    <!-- Stock Badge -->
                                    <div x-show="product.stock_quantity <= 5" class="absolute top-2 left-2 bg-rose-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-md z-10 tracking-wider">
                                        LOW
                                    </div>
                                    
                                    <div class="aspect-square bg-slate-100 rounded-xl mb-3 overflow-hidden group-hover:scale-[1.02] transition-transform duration-300 shrink-0">
                                        <template x-if="product.image_url">
                                            <img :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!product.image_url">
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <span class="material-symbols-outlined text-4xl">coffee</span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex flex-col flex-1">
                                        <h3 class="font-bold text-slate-800 text-xs md:text-sm leading-tight mb-1 group-hover:text-emerald-600 transition-colors line-clamp-2" x-text="product.name"></h3>
                                        <div class="mt-auto pt-2">
                                            <span class="font-black text-emerald-600 text-xs md:text-sm" x-text="formatMoney(product.price)"></span>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredProducts.length === 0" class="py-12 flex flex-col items-center justify-center text-slate-400">
                <span class="material-symbols-outlined text-6xl mb-4">search_off</span>
                <p class="font-semibold text-lg">Menu tidak ditemukan</p>
            </div>
            
            <!-- Padding for mobile floating button -->
            <div class="h-24 md:hidden"></div>
        </div>
    </div>

    <!-- RIGHT PANEL: CART (Desktop Only) -->
    <div class="hidden md:flex w-96 bg-white border-l border-slate-200 flex-col h-full shadow-[-4px_0_15px_-3px_rgba(0,0,0,0.05)] z-20">
        @include('pos.components.cart')
    </div>

</div>

@endsection
