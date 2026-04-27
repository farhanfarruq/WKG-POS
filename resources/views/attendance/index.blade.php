@extends('layouts.auth')

@section('title', 'Crew Attendance')

@section('content')
<div class="p-8" x-data="attendanceForm()">
    <div class="text-center mb-8">
        <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center">
            <img src="{{ asset('gambar/logo.png') }}" alt="Warkop POS Logo" class="w-full h-full object-contain drop-shadow-md">
        </div>
        <h1 class="text-2xl font-black text-slate-800">Crew Attendance</h1>
        <p class="text-slate-500 font-medium mt-1" x-text="currentTime"></p>
    </div>

    <!-- Message Display -->
    <div x-show="message" x-transition class="mb-6 px-4 py-3 rounded-xl flex items-center gap-3" :class="isSuccess ? 'bg-emerald-50 border border-emerald-200 text-emerald-600' : 'bg-rose-50 border border-rose-200 text-rose-600'">
        <span class="material-symbols-outlined" x-text="isSuccess ? 'check_circle' : 'error'"></span>
        <p class="text-sm font-bold" x-text="message"></p>
    </div>

    <!-- PIN Display -->
    <div class="mb-8">
        <div class="flex justify-center gap-3">
            <template x-for="i in 6">
                <div class="w-12 h-12 rounded-xl border-2 flex items-center justify-center text-2xl font-black transition-colors"
                     :class="pin.length >= i ? 'border-emerald-500 text-slate-800' : 'border-slate-200 text-transparent'">
                     <span x-show="pin.length >= i">•</span>
                </div>
            </template>
        </div>
    </div>

    <!-- Numpad -->
    <div class="grid grid-cols-3 gap-3 mb-8 max-w-[240px] mx-auto">
        <template x-for="n in 9">
            <button @click="addDigit(n)" class="bg-slate-50 hover:bg-slate-100 active:bg-slate-200 text-slate-800 font-bold text-xl py-4 rounded-xl transition-colors" x-text="n"></button>
        </template>
        <button @click="clearPin()" class="bg-rose-50 hover:bg-rose-100 text-rose-500 font-bold text-sm py-4 rounded-xl transition-colors">CLEAR</button>
        <button @click="addDigit(0)" class="bg-slate-50 hover:bg-slate-100 active:bg-slate-200 text-slate-800 font-bold text-xl py-4 rounded-xl transition-colors">0</button>
        <button @click="deleteDigit()" class="bg-slate-50 hover:bg-slate-100 text-slate-600 flex items-center justify-center py-4 rounded-xl transition-colors">
            <span class="material-symbols-outlined">backspace</span>
        </button>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <button 
            @click="submitAttendance('clock-in')" 
            :disabled="pin.length !== 6 || isLoading"
            :class="pin.length !== 6 ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-900 shadow-lg shadow-emerald-500/30'"
            class="py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
            Clock In
        </button>
        <button 
            @click="submitAttendance('clock-out')" 
            :disabled="pin.length !== 6 || isLoading"
            :class="pin.length !== 6 ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-slate-800 hover:bg-slate-700 text-white shadow-lg'"
            class="py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
            Clock Out
        </button>
    </div>
    
    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-slate-500 hover:text-slate-700 font-semibold text-sm flex items-center justify-center gap-1">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Login
        </a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('attendanceForm', () => ({
            pin: '',
            message: '',
            isSuccess: false,
            currentTime: '',
            
            init() {
                const updateTime = () => {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' · ' + now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                };
                updateTime();
                setInterval(updateTime, 1000);
            },
            
            addDigit(digit) {
                if (this.pin.length < 6) {
                    this.pin += digit.toString();
                }
            },
            
            deleteDigit() {
                this.pin = this.pin.slice(0, -1);
            },
            
            clearPin() {
                this.pin = '';
                this.message = '';
            },
            
            async submitAttendance(type) {
                if (this.pin.length !== 6) return;
                
                this.isLoading = true;
                this.message = '';
                
                const endpoint = type === 'clock-in' ? '/attendance/clock-in' : '/attendance/clock-out';
                
                try {
                    const response = await axios.post(endpoint, { pin: this.pin });
                    const data = response.data.data;
                    this.isSuccess = true;
                    
                    let msg = `Berhasil ${type.replace('-', ' ')}. Halo, ${data.employee}!`;
                    
                    if (type === 'clock-in' && data.shift_name) {
                        msg += ` Shift Anda: ${data.shift_name} (${data.shift_start}).`;
                        if (data.is_late) {
                            msg += ` Anda terlambat ${data.late_minutes} menit.`;
                        } else {
                            msg += ` Anda datang tepat waktu.`;
                        }
                    } else if (type === 'clock-out' && data.duration) {
                        msg += ` Total kerja hari ini: ${data.duration}.`;
                    }
                    
                    this.message = msg;
                    this.pin = '';
                } catch (error) {
                    this.isSuccess = false;
                    this.message = error.response?.data?.message || 'Terjadi kesalahan.';
                    this.pin = '';
                } finally {
                    this.isLoading = false;
                    setTimeout(() => {
                        if (this.isSuccess) this.message = '';
                    }, 5000);
                }
            }
        }));
    });
</script>
@endpush
@endsection
