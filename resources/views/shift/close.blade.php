@extends('layouts.auth')

@section('title', 'Close Shift')

@section('content')
<div class="p-8" x-data="closeShiftForm()">
    <div class="flex items-center gap-4 mb-8">
        <div class="w-12 h-12 flex items-center justify-center shrink-0">
            <img src="{{ asset('gambar/logo.png') }}" alt="Warkop POS Logo" class="w-full h-full object-contain drop-shadow-sm">
        </div>
        <div>
            <h1 class="text-xl font-black text-slate-800">Close Shift</h1>
            <p class="text-sm font-medium text-slate-500">Tutup shift kasir</p>
        </div>
    </div>

    <!-- Error Message -->
    <div x-show="errorMessage" x-transition class="mb-6 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl flex items-center gap-3">
        <span class="material-symbols-outlined">error</span>
        <p class="text-sm font-bold" x-text="errorMessage"></p>
    </div>

    <div x-show="shiftData" x-cloak class="mb-6 space-y-4">
        <!-- Summary Info -->
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-medium">Opening Cash</span>
                <span class="font-bold text-slate-700" x-text="formatMoney(shiftData?.opening_cash)"></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-medium">Cash Sales</span>
                <span class="font-bold text-slate-700" x-text="formatMoney(shiftData?.cash_sales)"></span>
            </div>
            <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                <span class="text-sm font-bold text-slate-800">Expected Cash</span>
                <span class="text-lg font-black text-emerald-600" x-text="formatMoney(shiftData?.expected_cash)"></span>
            </div>
        </div>

        <form @submit.prevent="submitCloseShift">
            <div class="mb-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Kas Akhir (Actual Cash)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-500">Rp</span>
                    <input 
                        type="text" 
                        x-model="actualCashDisplay"
                        @input="updateActualCash($event.target.value)"
                        required 
                        class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl text-lg font-black focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all text-slate-800 shadow-sm">
                </div>
                <p class="text-xs font-semibold text-slate-400 mt-2">Hitung jumlah uang fisik yang ada di laci saat ini.</p>
            </div>

            <!-- Variance Display -->
            <div x-show="variance !== 0" x-transition class="mb-6 p-4 rounded-xl border flex justify-between items-center" :class="variance > 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200'">
                <span class="font-bold text-sm" :class="variance > 0 ? 'text-emerald-700' : 'text-rose-700'">Selisih (Variance)</span>
                <span class="font-black text-lg" :class="variance > 0 ? 'text-emerald-700' : 'text-rose-700'" x-text="formatMoney(variance)"></span>
            </div>

            <button type="submit" :disabled="isLoading" class="w-full bg-rose-500 hover:bg-rose-400 text-white font-bold py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2 shadow-lg shadow-rose-500/30">
                <span x-show="!isLoading">Tutup Shift</span>
                <span x-show="isLoading" class="material-symbols-outlined animate-spin">refresh</span>
            </button>
        </form>
    </div>
    
    <div class="mt-6 flex justify-between items-center">
        <a href="{{ route('pos.index') }}" class="text-slate-500 hover:text-slate-700 font-semibold text-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Kembali
        </a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('closeShiftForm', () => ({
            shiftData: null,
            actualCash: 0,
            actualCashDisplay: '',
            isLoading: false,
            errorMessage: '',
            
            async init() {
                try {
                    const response = await axios.get('/shifts/current');
                    this.shiftData = response.data.data;
                    
                    if (!this.shiftData) {
                        window.location.href = '/shift/open'; 
                        return;
                    }

                    const summaryResponse = await axios.get(`/shifts/${this.shiftData.id}/summary`);
                    this.shiftData = { ...this.shiftData, ...summaryResponse.data.data };
                    
                    this.actualCash = this.shiftData.expected_cash || 0;
                    this.actualCashDisplay = this.actualCash.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                } catch (error) {
                    console.error(error);
                    if (error.response?.status === 401) {
                        window.location.href = '/login';
                    } else {
                        this.errorMessage = 'Gagal memuat data shift.';
                    }
                }
            },

            updateActualCash(value) {
                // Remove all non-numeric characters (including dots)
                const cleanValue = value.replace(/\D/g, '');
                this.actualCash = cleanValue ? parseInt(cleanValue) : 0;
                
                // Format back with dots for display
                this.actualCashDisplay = this.actualCash.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            },

            get variance() {
                if (!this.shiftData) return 0;
                return this.actualCash - (this.shiftData.expected_cash || 0);
            },

            formatMoney(amount) {
                if(amount === undefined || amount === null) return '-';
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
            },
            
            async submitCloseShift() {
                this.isLoading = true;
                this.errorMessage = '';
                
                try {
                    await axios.post('/shifts/close', {
                        closing_cash: this.actualCash
                    });
                    
                    // Shift closed, log out or redirect to login
                    sessionStorage.removeItem('auth_token');
                    window.location.href = '/login';
                } catch (error) {
                    this.errorMessage = error.response?.data?.message || 'Gagal menutup shift.';
                } finally {
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection
