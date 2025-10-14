<nav id="siteNav" class="fixed top-0 w-full bg-slate-900/95 backdrop-blur-sm border-b border-blue-500/20 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-blue-500 to-red-500 rounded-lg flex items-center justify-center">
                    <img src="{{ asset('assets/images/dutymanagr.png') }}" class="rounded-lg" alt="Dutymanager">
                </div>
                <span
                    class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-red-400 bg-clip-text text-transparent">
          DutyManager 2.0
        </span>
            </div>

            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('welcome').'#features' }}" class="text-slate-300 hover:text-blue-400">Funkciók</a>
                <a href="{{ route('documentation') }}" class="text-slate-300 hover:text-blue-400">Dokumentáció</a>
                <a href="{{ isset($showHomeLink) && $showHomeLink ? '/#support' : '#support' }}"
                   class="text-slate-300 hover:text-blue-400">Támogatás</a>
            </div>

            <div class="hidden md:flex items-center space-x-4">
                @if(isset($backButton) && $backButton)
                    <a href="{{ $backButton['url'] }}"
                       class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-6 py-2 rounded-lg border border-slate-600">
                        <i class="fas fa-arrow-left mr-2"></i>{{ $backButton['text'] }}
                    </a>
                @else
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="bg-discord hover:bg-discord-dark text-white px-6 py-2 rounded-lg transform hover:scale-105">
                            <i class="fa fa-tachometer mr-2"></i> Vezérlőpult
                        </a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}"
                           class="bg-discord hover:bg-discord-dark text-white px-6 py-2 rounded-lg transform hover:scale-105">
                            <i class="fab fa-discord mr-2"></i> Bejelentkezés
                        </a>
                    @endguest
                @endif
            </div>

            <div class="md:hidden">
                <button id="menuToggle" class="text-slate-300 hover:text-blue-400 focus:outline-none">
                    <svg id="iconOpen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="iconClose" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 hidden" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobileMenu" class="md:hidden hidden pb-4 space-y-3">
            <a href="{{ route('welcome').'#features' }}" class="block text-slate-300 hover:text-blue-400 js-close-menu">Funkciók</a>
            <a href="{{ route('documentation') }}" class="block text-slate-300 hover:text-blue-400 js-close-menu">Dokumentáció</a>
            <a href="{{ isset($showHomeLink) && $showHomeLink ? '/#support' : '#support' }}"
               class="block text-slate-300 hover:text-blue-400 js-close-menu">Támogatás</a>

            <div class="pt-3 border-t border-slate-700">
                @if(isset($backButton) && $backButton)
                    <a href="{{ $backButton['url'] }}"
                       class="block bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-lg border border-slate-600 js-close-menu">
                        <i class="fas fa-arrow-left mr-2"></i>{{ $backButton['text'] }}
                    </a>
                @else
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="block bg-discord hover:bg-discord-dark text-white px-4 py-2 rounded-lg js-close-menu">
                            <i class="fa fa-tachometer mr-2"></i> Vezérlőpult
                        </a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}"
                           class="block bg-discord hover:bg-discord-dark text-white px-4 py-2 rounded-lg js-close-menu">
                            <i class="fab fa-discord mr-2"></i> Bejelentkezés
                        </a>
                    @endguest
                @endif
            </div>
        </div>
    </div>
</nav>

<script>
    (() => {
        const nav = document.getElementById('siteNav');
        const btn = document.getElementById('menuToggle');
        const menu = document.getElementById('mobileMenu');
        const iconOpen = document.getElementById('iconOpen');
        const iconClose = document.getElementById('iconClose');
        const MD = 768;

        const openMenu = () => {
            menu.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
        };

        const closeMenu = () => {
            if (menu.classList.contains('hidden')) return;
            menu.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
        };

        const toggleMenu = () => menu.classList.contains('hidden') ? openMenu() : closeMenu();

        btn.addEventListener('click', toggleMenu);
        menu.querySelectorAll('.js-close-menu').forEach(el => el.addEventListener('click', closeMenu));
        document.addEventListener('click', e => !nav.contains(e.target) && closeMenu());
        document.addEventListener('keydown', e => e.key === 'Escape' && closeMenu());
        window.addEventListener('resize', () => window.innerWidth >= MD && closeMenu());
    })();
</script>
