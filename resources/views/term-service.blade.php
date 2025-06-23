@extends('layouts.guest')

@section('title', 'DutyManager 2.0 - Felhasználási feltételek')

@section('content')
    <x-page-header
        title="Felhasználási feltételek"
        subtitle="Utoljára frissítve: 2025. június 23."
        :badge="['icon' => 'fas fa-gavel', 'text' => 'Jogi információk']"
        notice="Kérjük, figyelmesen olvassa el ezeket a feltételeket a szolgáltatás használata előtt." />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="space-y-12">
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-blue-400 mb-6 flex items-center">
                    <i class="fas fa-handshake mr-3"></i>
                    1. Elfogadás és alkalmazhatóság
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        A DutyManager 2.0 Discord bot ("Szolgáltatás") használatával Ön elfogadja és vállalja, hogy betartja jelen Felhasználási Feltételeket ("Feltételek"). Ha nem ért egyet ezekkel a feltételekkel, kérjük, ne használja a szolgáltatást.
                    </p>
                    <p>
                        Ezek a feltételek minden felhasználóra vonatkoznak, aki hozzáfér vagy használja a DutyManager 2.0 bot szolgáltatásait, függetlenül attól, hogy regisztrált felhasználó-e vagy sem.
                    </p>
                    <p>
                        Fenntartjuk a jogot, hogy bármikor módosítsuk ezeket a feltételeket. A változásokról a weboldalunkon keresztül értesítjük a felhasználókat.
                    </p>
                </div>
            </section>

            <!-- Section 2 -->
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-red-400 mb-6 flex items-center">
                    <i class="fas fa-user-check mr-3"></i>
                    2. Szolgáltatás leírása és korhatár
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        A DutyManager 2.0 egy Discord bot, amely szolgálati idő kezelést, felhasználó adminisztrációt és kapcsolódó funkciókat biztosít Discord szerverek számára.
                    </p>
                    <p>
                        <strong class="text-yellow-400">Korhatár:</strong> A szolgáltatás használatához minimum 13 éves kornak kell lennie, összhangban a Discord felhasználási feltételeivel. 13 és 18 év közötti felhasználók csak szülői engedéllyel használhatják a szolgáltatást.
                    </p>
                    <p>
                        A szolgáltatás "ahogy van" alapon kerül biztosításra, és nem garantáljuk a folyamatos rendelkezésre állást vagy hibamentes működést.
                    </p>
                </div>
            </section>

            <!-- Section 3 -->
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-green-400 mb-6 flex items-center">
                    <i class="fas fa-user-shield mr-3"></i>
                    3. Felhasználói kötelezettségek
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p><strong class="text-green-400">Tilos a következő tevékenységek:</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>A bot vagy szolgáltatás működésének megzavarása vagy meghackelése</li>
                        <li>Visszaélés a bot parancsaival vagy funkcióival</li>
                        <li>Automatizált eszközök használata a bot túlterhelésére</li>
                        <li>Illegális vagy káros tartalom tárolása vagy továbbítása</li>
                        <li>Mások személyes adatainak jogosulatlan gyűjtése</li>
                        <li>A szolgáltatás kereskedelmi célú felhasználása előzetes engedély nélkül</li>
                    </ul>
                    <p>
                        <strong class="text-yellow-400">Felelősség:</strong> Ön teljes mértékben felelős a fiókja és a bot használatának minden következményéért.
                    </p>
                </div>
            </section>

            <!-- Section 4 -->
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-purple-400 mb-6 flex items-center">
                    <i class="fas fa-database mr-3"></i>
                    4. Adatkezelés és magánélet
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        A DutyManager 2.0 a működéséhez szükséges minimális adatokat gyűjti és kezeli:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Discord felhasználói azonosítók és felhasználónevek</li>
                        <li>Szerver azonosítók és beállítások</li>
                        <li>Szolgálati idő és kapcsolódó statisztikák</li>
                        <li>Bot parancsok használati naplói</li>
                    </ul>
                    <p>
                        <strong class="text-purple-400">Adatvédelem:</strong> Minden adat titkosítva kerül tárolásra, és csak a szolgáltatás működéséhez szükséges célokra használjuk fel.
                    </p>
                    <p>
                        Az adatok harmadik félnek való átadása csak jogi kötelezettség esetén vagy az Ön kifejezett hozzájárulásával történik.
                    </p>
                </div>
            </section>

            <!-- Section 5 -->
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-yellow-400 mb-6 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    5. Felelősség korlátozása
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        <strong class="text-yellow-400">Szolgáltatás rendelkezésre állása:</strong> Nem garantáljuk a szolgáltatás 100%-os rendelkezésre állását. Karbantartás, frissítések vagy technikai problémák miatt időszakos kimaradások előfordulhatnak.
                    </p>
                    <p>
                        <strong class="text-red-400">Felelősség kizárása:</strong> A DutyManager 2.0 fejlesztői nem vállalnak felelősséget:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>A szolgáltatás használatából eredő közvetlen vagy közvetett károkért</li>
                        <li>Adatvesztésért vagy adatsérülésért</li>
                        <li>Harmadik felek által okozott károkért</li>
                        <li>A szolgáltatás megszakításából eredő következményekért</li>
                    </ul>
                    <p>
                        A szolgáltatást saját felelősségére használja.
                    </p>
                </div>
            </section>

            <!-- Section 6 -->
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-indigo-400 mb-6 flex items-center">
                    <i class="fas fa-ban mr-3"></i>
                    6. Felfüggesztés és megszüntetés
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        Fenntartjuk a jogot, hogy:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Felfüggesszük vagy megszüntessük a hozzáférését a szolgáltatáshoz</li>
                        <li>Töröljük a bot-ot szerveréről</li>
                        <li>Korlátozzuk bizonyos funkciók használatát</li>
                    </ul>
                    <p>
                        <strong class="text-indigo-400">Indokok:</strong> A fenti intézkedések alkalmazhatók a feltételek megsértése, visszaélés, vagy a szolgáltatás biztonságának veszélyeztetése esetén.
                    </p>
                    <p>
                        A felhasználó bármikor kérheti a bot eltávolítását szerveréről vagy fiókja törlését.
                    </p>
                </div>
            </section>

            <!-- Section 7 -->
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-cyan-400 mb-6 flex items-center">
                    <i class="fas fa-balance-scale mr-3"></i>
                    7. Szellemi tulajdon
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        A DutyManager 2.0 bot, annak forráskódja, dokumentációja és kapcsolódó anyagai szellemi tulajdont képeznek, és szerzői jogvédelem alatt állnak.
                    </p>
                    <p>
                        <strong class="text-cyan-400">Engedélyezett használat:</strong> Engedélyt adunk a bot használatára Discord szerverén a jelen feltételek betartása mellett.
                    </p>
                    <p>
                        <strong class="text-red-400">Tilos:</strong>
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>A bot másolása, módosítása vagy visszafejtése</li>
                        <li>Kereskedelmi célú felhasználás engedély nélkül</li>
                        <li>A bot újraterjesztése vagy harmadik félnek való átadása</li>
                    </ul>
                </div>
            </section>

            <!-- Section 8 -->
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-pink-400 mb-6 flex items-center">
                    <i class="fas fa-gavel mr-3"></i>
                    8. Joghatóság és vitarendezés
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        <strong class="text-pink-400">Irányadó jog:</strong> Jelen feltételekre a magyar jog az irányadó.
                    </p>
                    <p>
                        <strong class="text-green-400">Vitarendezés:</strong> A szolgáltatással kapcsolatos vitákat elsősorban békés úton, tárgyalás útján igyekszünk rendezni.
                    </p>
                    <p>
                        Ha a békés rendezés nem vezet eredményre, a magyar bíróságok rendelkeznek kizárólagos illetékességgel a viták elbírálására.
                    </p>
                    <p>
                        <strong class="text-yellow-400">Kapcsolat:</strong> Kérdések vagy panaszok esetén forduljon hozzánk a támogatási csatornáinkon keresztül.
                    </p>
                </div>
            </section>

            <!-- Contact Section -->
            <section class="bg-gradient-to-r from-blue-600/20 to-red-600/20 rounded-2xl p-8 border border-blue-500/30 text-center">
                <h2 class="text-2xl font-bold text-blue-400 mb-4">
                    <i class="fas fa-headset mr-2"></i>
                    Kérdése van?
                </h2>
                <p class="text-slate-300 mb-6">
                    Ha bármilyen kérdése van a felhasználási feltételekkel kapcsolatban, ne habozzon kapcsolatba lépni velünk.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="bg-discord hover:bg-discord-dark text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                        <i class="fab fa-discord mr-2"></i>
                        Discord támogatás
                    </a>
                    <a href="mailto:support@dutymanager.eu" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-6 py-3 rounded-lg font-medium transition-all duration-300 border border-slate-600">
                        <i class="fas fa-envelope mr-2"></i>
                        E-mail küldése
                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection
