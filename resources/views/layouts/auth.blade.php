<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warkop POS - @yield('title')</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8faff; color: #131b2e; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        [x-cloak] { display: none !important; }
    </style>

    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @stack('scripts')
</head>
<body class="min-h-screen font-sans antialiased bg-slate-100 flex items-center justify-center p-4" x-data="authSystem()">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl overflow-hidden">
        @unless (request()->routeIs('login'))
            <div x-show="currentUser" x-cloak class="border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="relative h-12 w-12 overflow-hidden rounded-full bg-slate-200 flex items-center justify-center text-slate-700 shrink-0">
                        <template x-if="currentUser?.avatar_url">
                            <img :src="currentUser.avatar_url" :alt="currentUser.name" class="absolute inset-0 block w-full h-full max-w-none object-cover">
                        </template>
                        <span x-show="!currentUser?.avatar_url && currentUser" x-text="currentUser?.name?.charAt(0)" class="text-lg font-bold uppercase"></span>
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-bold text-slate-800" x-text="currentUser?.name"></div>
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-500" x-text="currentUser?.roles?.[0] || 'User'"></div>
                    </div>
                </div>
            </div>
        @endunless

        @yield('content')
    </div>
    
    <!-- Scripts -->
    <script>
        // Setup axios defaults immediately
        axios.defaults.baseURL = '/api/v1';
        axios.defaults.headers.common['Accept'] = 'application/json';
        
        const token = sessionStorage.getItem('auth_token');
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('authSystem', () => ({
                currentUser: null,
                isLoading: false,
                errorMessage: '',

                init() {
                    if (sessionStorage.getItem('auth_token')) {
                        this.loadCurrentUser();
                    }
                },
                
                showError(message) {
                    this.errorMessage = message;
                    setTimeout(() => this.errorMessage = '', 5000);
                },

                async loadCurrentUser() {
                    try {
                        const response = await axios.get('/auth/me');
                        this.currentUser = response.data.data;
                    } catch (error) {
                        this.currentUser = null;

                        if (error.response?.status === 401) {
                            sessionStorage.removeItem('auth_token');
                            delete axios.defaults.headers.common['Authorization'];
                        }
                    }
                }
            }));
        });
    </script>
</body>
</html>
