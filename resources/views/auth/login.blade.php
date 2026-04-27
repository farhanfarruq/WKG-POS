@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="p-8">
    <div class="text-center mb-8">
        <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center">
            <img src="{{ asset('gambar/logo.png') }}" alt="Warkop POS Logo" class="w-full h-full object-contain drop-shadow-md">
        </div>
        <h1 class="text-2xl font-black text-slate-800">Warkop POS</h1>
        <p class="text-slate-500 font-medium mt-1">Sign in to your account</p>
    </div>

    <!-- Error Message -->
    <div x-show="errorMessage" x-transition class="mb-6 bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-xl flex items-center gap-3">
        <span class="material-symbols-outlined">error</span>
        <p class="text-sm font-bold" x-text="errorMessage"></p>
    </div>

    <form @submit.prevent="login" x-data="loginForm()">
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Email Address</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">mail</span>
                    <input type="email" x-model="email" required class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">lock</span>
                    <input type="password" x-model="password" required class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition-all">
                </div>
            </div>
        </div>

        <button type="submit" :disabled="isLoading" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 rounded-xl mt-8 transition-colors flex items-center justify-center gap-2">
            <span x-show="!isLoading">Sign In</span>
            <span x-show="isLoading" class="material-symbols-outlined animate-spin">refresh</span>
        </button>
    </form>
    
    <div class="mt-6 text-center">
        <a href="{{ route('attendance.index') }}" class="text-emerald-600 hover:text-emerald-700 font-bold text-sm">Crew Attendance (PIN)</a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('loginForm', () => ({
            email: '',
            password: '',
            
            async login() {
                this.isLoading = true;
                this.errorMessage = '';
                
                try {
                    const response = await axios.post('/auth/login', {
                        email: this.email,
                        password: this.password
                    });
                    
                    const data = response.data.data;
                    sessionStorage.setItem('auth_token', data.token);
                    
                    // Role-based redirection
                    const roles = data.roles || [];
                    if (roles.includes('barista')) {
                        window.location.href = '/kds';
                    } else if (roles.includes('admin') || roles.includes('super_admin') || roles.includes('manager')) {
                        window.location.href = '/admin';
                    } else {
                        window.location.href = '/pos';
                    }
                } catch (error) {
                    this.showError(error.response?.data?.message || 'Login failed. Please check your credentials.');
                } finally {
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection
