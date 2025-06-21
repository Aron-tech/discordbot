<!DOCTYPE html>
<html lang="hu" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DutyManager 2.0 - Discord Bot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        discord: '#5865F2',
                        'discord-dark': '#4752C4',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-slate-100 min-h-screen">
<!-- Navigation -->
<nav class="fixed top-0 w-full bg-slate-900/95 backdrop-blur-sm border-b border-blue-500/20 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-red-500 rounded-lg flex items-center justify-center">
                    <i class="fab fa-discord text-white text-xl"></i>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-red-400 bg-clip-text text-transparent">
                        DutyManager 2.0
                    </span>
            </div>

            <div class="hidden md:flex items-center space-x-8">
                <a href="#features" class="text-slate-300 hover:text-blue-400 transition-colors duration-300">Funkciók</a>
                <a href="#commands" class="text-slate-300 hover:text-blue-400 transition-colors duration-300">Parancsok</a>
                <a href="#setup" class="text-slate-300 hover:text-blue-400 transition-colors duration-300">Telepítés</a>
                <a href="#support" class="text-slate-300 hover:text-blue-400 transition-colors duration-300">Támogatás</a>
            </div>

            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{route('dashboard')}}" class="bg-discord hover:bg-discord-dark text-white px-6 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                        <i class="fa fa-tachometer mr-2" ></i>
                        Vezérlőpult
                    </a>
                @endauth
                @guest
                        <a href="{{route('login')}}" class="bg-discord hover:bg-discord-dark text-white px-6 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                            <i class="fab fa-discord mr-2"></i>
                            Bejelentkezés
                        </a>
                @endguest
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<main class="pt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center py-20">
            <div class="mb-8">
                <div class="inline-flex items-center px-4 py-2 bg-slate-800/50 rounded-full border border-blue-500/30 mb-6">
                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                    <span class="text-sm text-slate-300">Aktív és üzemkész</span>
                </div>
            </div>

            <h1 class="text-5xl md:text-7xl font-bold mb-6">
                    <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-red-400 bg-clip-text text-transparent">
                        DutyManager 2.0
                    </span>
            </h1>

            <p class="text-xl md:text-2xl text-slate-300 mb-8 max-w-3xl mx-auto leading-relaxed">
                A következő generációs Discord bot szolgálati idő kezeléshez.
                Automatizált, megbízható és könnyen használható.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                <a href="https://discord.com/oauth2/authorize?client_id=1377217826104016928&permissions=8&integration_type=0&scope=bot" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-blue-500/25">
                    <i class="fab fa-discord mr-2"></i>
                    Bot hozzáadása
                </a>
                <button class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 border border-slate-600 hover:border-slate-500">
                    <i class="fas fa-book mr-2"></i>
                    Dokumentáció
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-2xl mx-auto">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-400 mb-1">{{$guild_count}}</div>
                    <div class="text-sm text-slate-400">Szerver</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-400 mb-1">{{$user_count}}</div>
                    <div class="text-sm text-slate-400">Felhasználó</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-400 mb-1">99.9%</div>
                    <div class="text-sm text-slate-400">Uptime</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-400 mb-1">24/7</div>
                    <div class="text-sm text-slate-400">Támogatás</div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="py-20">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 bg-gradient-to-r from-blue-400 to-red-400 bg-clip-text text-transparent">
                    Főbb funkciók
                </h2>
                <p class="text-xl text-slate-400 max-w-2xl mx-auto">
                    Minden eszköz egy helyen a hatékony Discord szerver kezeléshez
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700/50 hover:border-blue-500/50 transition-all duration-300 hover:transform hover:scale-105">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-blue-400">Felhasználó kezelés</h3>
                    <p class="text-slate-300 leading-relaxed">
                        Felhasználók felvétele, eltávolítása és jogosultságok kezelése egyszerűen.
                    </p>
                </div>

                <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700/50 hover:border-red-500/50 transition-all duration-300 hover:transform hover:scale-105">
                    <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fa-solid fa-pen text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-red-400">Modern design</h3>
                    <p class="text-slate-300 leading-relaxed">
                        A bot modern Discord beágyazott (embed) üzenetekkel válaszol, és korszerű, interaktív menürendszerrel segíti a felhasználókat a parancsok kezelésében.
                    </p>
                </div>

                <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700/50 hover:border-purple-500/50 transition-all duration-300 hover:transform hover:scale-105">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-purple-400">Jelentések</h3>
                    <p class="text-slate-300 leading-relaxed">
                        Részletes statisztikák és jelentések a csapat teljesítményéről.
                    </p>
                </div>

                <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700/50 hover:border-green-500/50 transition-all duration-300 hover:transform hover:scale-105">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-cog text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-green-400">Automatizálás</h3>
                    <p class="text-slate-300 leading-relaxed">
                        Teljes automatizálás lehetősége. A bot önállóan kezeli a rutinfeladatokat. (Előléptetés, figyelmeztetések)
                    </p>
                </div>

                <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700/50 hover:border-yellow-500/50 transition-all duration-300 hover:transform hover:scale-105">
                    <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-yellow-400">Biztonság</h3>
                    <p class="text-slate-300 leading-relaxed">
                        Magas szintű biztonság és adatvédelem. Minden adatot titkosítva tárolunk.
                    </p>
                </div>

                <div class="bg-slate-800/50 rounded-2xl p-8 border border-slate-700/50 hover:border-indigo-500/50 transition-all duration-300 hover:transform hover:scale-105">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mb-6">
                        <i class="fas fa-puzzle-piece text-white text-xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-4 text-indigo-400">Egyszerű használat</h3>
                    <p class="text-slate-300 leading-relaxed">
                        Könnyű telepítés, teljes folyamat weboldalon történik, majd discord bot parancs használata után a bot összes funkciója elérhetővé válik.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20">
            <div class="bg-gradient-to-r from-blue-600/20 to-red-600/20 rounded-3xl p-12 text-center border border-blue-500/30">
                <h2 class="text-4xl font-bold mb-6 bg-gradient-to-r from-blue-400 to-red-400 bg-clip-text text-transparent">
                    Kezdj el még ma!
                </h2>
                <p class="text-xl text-slate-300 mb-8 max-w-2xl mx-auto">
                    Csatlakozz több ezer elégedett felhasználóhoz, és tapasztald meg a DutyManager 2.0 előnyeit.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fab fa-discord mr-2"></i>
                        Ingyenes telepítés
                    </button>
                    <button class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 border border-slate-600">
                        <i class="fas fa-play mr-2"></i>
                        Demó megtekintése
                    </button>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Footer -->
<footer class="bg-slate-900/80 border-t border-slate-700/50 py-12 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 md:mb-0">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-red-500 rounded-lg flex items-center justify-center">
                    <i class="fab fa-discord text-white"></i>
                </div>
                <span class="text-xl font-bold bg-gradient-to-r from-blue-400 to-red-400 bg-clip-text text-transparent">
                        DutyManager 2.0
                    </span>
            </div>

            <div class="flex items-center space-x-6 text-slate-400">
                <a href="#" class="hover:text-blue-400 transition-colors">Adatvédelem</a>
                <a href="#" class="hover:text-blue-400 transition-colors">Feltételek</a>
                <a href="#" class="hover:text-blue-400 transition-colors">Támogatás</a>
                <a href="#" class="hover:text-blue-400 transition-colors">
                    <i class="fab fa-discord text-xl"></i>
                </a>
            </div>
        </div>

        <div class="text-center text-slate-500 text-sm mt-8 pt-8 border-t border-slate-700/50">
            © 2025 DutyManager 2.0. Minden jog fenntartva.
        </div>
    </div>
</footer>

<script>
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Header background opacity on scroll
    window.addEventListener('scroll', () => {
        const header = document.querySelector('nav');
        if (window.scrollY > 100) {
            header.classList.add('bg-slate-900/98');
            header.classList.remove('bg-slate-900/95');
        } else {
            header.classList.add('bg-slate-900/95');
            header.classList.remove('bg-slate-900/98');
        }
    });
</script>
</body>
</html>
