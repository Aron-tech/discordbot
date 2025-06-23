<nav class="fixed top-0 w-full bg-slate-900/95 backdrop-blur-sm border-b border-blue-500/20 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-red-500 rounded-lg flex items-center justify-center">
                    <img src="{{asset('assets/images/dutymanagr.png')}}" class="rounded-lg" alt="Dutymanager">
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-red-400 bg-clip-text text-transparent">
                    DutyManager 2.0
                </span>
            </div>

            <div class="hidden md:flex items-center space-x-8">
                <a href="{{route('welcome').'#features'}}" class="text-slate-300 hover:text-blue-400 transition-colors duration-300">Funkciók</a>
                <a href="{{ isset($showHomeLink) && $showHomeLink ? '/#setup' : '#setup' }}" class="text-slate-300 hover:text-blue-400 transition-colors duration-300">Dokumentáció</a>
                <a href="{{ isset($showHomeLink) && $showHomeLink ? '/#support' : '#support' }}" class="text-slate-300 hover:text-blue-400 transition-colors duration-300">Támogatás</a>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center space-x-4">
                @if(isset($backButton) && $backButton)
                    <a href="{{ $backButton['url'] }}" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-6 py-2 rounded-lg font-medium transition-all duration-300 border border-slate-600">
                        <i class="fas fa-arrow-left mr-2"></i>
                        {{ $backButton['text'] }}
                    </a>
                @else
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-discord hover:bg-discord-dark text-white px-6 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                            <i class="fa fa-tachometer mr-2"></i>
                            Vezérlőpult
                        </a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}" class="bg-discord hover:bg-discord-dark text-white px-6 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                            <i class="fab fa-discord mr-2"></i>
                            Bejelentkezés
                        </a>
                    @endguest
                @endif
            </div>
        </div>
    </div>
</nav>
