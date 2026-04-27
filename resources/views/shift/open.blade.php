@extends('layouts.auth')

@section('title', 'Open Shift')

@section('content')
<div class="p-8" x-data="openShiftForm()">
    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 flex items-center justify-center shrink-0">
            <img src="{{ asset('gambar/logo.png') }}" alt="Warkop POS Logo" class="w-full h-full object-contain drop-shadow-sm">
        </div>
        <div>
            <h1 class="text-xl font-black text-slate-800">Open Shift</h1>
            <p class="text-sm font-medium text-slate-500">Mulai shift baru</p>
        </div>
    </div>

    <!-- Error Message -->
    <div x-show="errorMessage" x-transition class="mb-6 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl flex items-center gap-3">
        <span class="material-symbols-outlined">error</span>
        <p class="text-sm font-bold" x-text="errorMessage"></p>
    </div>

    <form @submit.prevent="submitOpenShift">
        <div class="mb-6">
            <label class="block text-sm font-bold text-slate-700 mb-2">Kas Awal (Opening Cash)</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-500">Rp</span>
                <input 
                    type="number" 
                    x-model.number="openingCash" 
                    required 
                    min="0"
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-lg font-black focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all text-slate-800">
            </div>
            <p class="text-xs font-semibold text-slate-400 mt-2">Pastikan jumlah uang fisik di laci sesuai.</p>
        </div>

        <button type="submit" :disabled="isLoading" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-bold py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/30">
            <span x-show="!isLoading">Buka Shift</span>
            <span x-show="isLoading" class="material-symbols-outlined animate-spin">refresh</span>
        </button>
    </form>
    
    <div class="mt-6 text-center">
        <button @click="logout()" class="text-slate-500 hover:text-slate-700 font-semibold text-sm">Logout</button>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('openShiftForm', () => ({
            openingCash: 0,
            isLoading: false,
            errorMessage: '',
            
            async init() {
                // Check if shift is already open
                try {
                    const response = await axios.get('/shifts/current');
                    if (response.data.data) {
                        window.location.href = '/shift/close'; // Redirect to close shift if already open
                    }
                } catch (error) {
                    if (error.response?.status === 401) {
                        window.location.href = '/login';
                    }
                }
            },
            
            async submitOpenShift() {
                this.isLoading = true;
                this.errorMessage = '';
                
                try {
                    await axios.post('/shifts/open', {
                        opening_cash: this.openingCash
                    });
                    
                    window.location.href = '/pos';
                } catch (error) {
                    this.errorMessage = error.response?.data?.message || 'Gagal membuka shift.';
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
</script>
@endpush
@endsection
