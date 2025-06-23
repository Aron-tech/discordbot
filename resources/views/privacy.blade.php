@extends('layouts.guest')

@section('title', 'DutyManager 2.0 - Adatvédelmi irányelvek')

@section('content')
    <x-page-header
        title="Adatvédelmi irányelvek"
        subtitle="Utoljára frissítve: 2025. június 23."
        :badge="['icon' => 'fas fa-shield-alt', 'text' => 'Adatvédelem']"
        notice="Ez a dokumentum részletesen leírja, hogyan gyűjtjük, kezeljük és védjük adatait." />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="space-y-12">
            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-blue-400 mb-6 flex items-center">
                    <i class="fas fa-info-circle mr-3"></i>
                    1. Bevezetés
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        A DutyManager 2.0 ("Mi", "Szolgáltatás") elkötelezett az Ön adatainak védelme mellett. Jelen Adatvédelmi Irányelvek ("Irányelvek") leírják, hogyan gyűjtjük, használjuk, tároljuk és védjük személyes adatait a Discord botunk használata során.
                    </p>
                    <p>
                        A szolgáltatás használatával Ön elfogadja az itt leírt adatgyűjtési és -feldolgozási gyakorlatokat. Kérjük, figyelmesen olvassa el ezeket az irányelveket.
                    </p>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-green-400 mb-6 flex items-center">
                    <i class="fas fa-database mr-3"></i>
                    2. Gyűjtött adatok típusai
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p><strong class="text-green-400">A szolgáltatás működéséhez szükséges adatok:</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><strong>Discord felhasználói adatok:</strong> Felhasználói azonosító (ID), felhasználónév, discriminator, avatar URL</li>
                        <li><strong>Szerver adatok:</strong> Szerver ID, szerver neve, taglista, csatorna információk</li>
                        <li><strong>Szolgálati információk:</strong> Szolgálati időpontok, időtartamok, státuszok, megjegyzések</li>
                        <li><strong>Bot interakciók:</strong> Parancsok használata, időbélyegek, hibanaplók</li>
                    </ul>

                    <p><strong class="text-yellow-400">Nem gyűjtünk:</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Valódi neveket, címeket vagy telefonszámokat</li>
                        <li>Pénzügyi információk vagy fizetési adatok</li>
                        <li>Biometrikus adatokat</li>
                        <li>Discord jelszavakat vagy hozzáférési tokeneket</li>
                    </ul>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-purple-400 mb-6 flex items-center">
                    <i class="fas fa-cog mr-3"></i>
                    3. Adatgyűjtés céljai
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>Az Ön adatait a következő célokra használjuk fel:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>A szolgáltatás alapvető működésének biztosítása</li>
                        <li>Szolgálati idő nyilvántartás és jelentések generálása</li>
                        <li>Bot parancsok feldolgozása és válaszadás</li>
                        <li>Szolgáltatás fejlesztése és hibajavítás</li>
                        <li>Biztonsági problémák azonosítása és megelőzése</li>
                        <li>Jogtiszta működés biztosítása</li>
                    </ul>
                    <p>
                        <strong class="text-purple-400">Adatfeldolgozás jogalapja:</strong> Az adatfeldolgozás jogalapja a szolgáltatás szerződéses teljesítése, illetve az Ön hozzájárulása.
                    </p>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-red-400 mb-6 flex items-center">
                    <i class="fas fa-lock mr-3"></i>
                    4. Adatbiztonság és tárolás
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        <strong class="text-red-400">Adatbiztonság:</strong> Komoly erőfeszítéseket teszünk az Ön adatainak védelme érdekében:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Minden adat titkosított csatornán (SSL/TLS) kerül továbbításra</li>
                        <li>Biztonságos adatbázisokban tároljuk az információkat</li>
                        <li>Szigorú hozzáférés-vezérlés alkalmazása</li>
                        <li>Rendszeres biztonsági auditok és frissítések</li>
                    </ul>

                    <p>
                        <strong class="text-yellow-400">Adatmegőrzés:</strong> Az adatokat csak a szükséges időtartamig őrizzük meg:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Aktív felhasználói adatok: Amíg a bot használatban van a szerveren</li>
                        <li>Inaktív szerverek adatai: 6 hónap inaktivitás után törlésre kerülnek</li>
                        <li>Hibanaplók: 30 napig tároljuk</li>
                    </ul>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-indigo-400 mb-6 flex items-center">
                    <i class="fas fa-share-alt mr-3"></i>
                    5. Adattovábbítás és harmadik felek
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        <strong class="text-indigo-400">Harmadik feleknek való adattovábbítás:</strong> Általában nem osztunk meg személyes adatokat harmadik felekkel, kivéve a következő esetekben:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Ha erre jogi kötelezettségünk van (pl. bírósági végzés)</li>
                        <li>A szolgáltatás működtetéséhez szükséges alvállalkozóknak (pl. hosting szolgáltató)</li>
                        <li>Az Ön kifejezett hozzájárulásával</li>
                    </ul>

                    <p>
                        <strong class="text-blue-400">Alvállalkozók:</strong> A következő harmadik felekhez fordulhatunk a szolgáltatás nyújtásához:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Hosting szolgáltatók (pl. Amazon Web Services, DigitalOcean)</li>
                        <li>Analitikai eszközök (pl. Google Analytics, anonimizált adatokkal)</li>
                        <li>Hibakezelő rendszerek (pl. Sentry)</li>
                    </ul>
                    <p>Minden alvállalkozónk megfelel az EU adatvédelmi szabályozásának (GDPR).</p>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-cyan-400 mb-6 flex items-center">
                    <i class="fas fa-user-cog mr-3"></i>
                    6. Felhasználói jogok
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>Az Ön adatvédelmi jogai:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><strong>Hozzáférési jog:</strong> Kérheti a személyes adatai másolatát</li>
                        <li><strong>Javítási jog:</strong> Kérheti helytelen adatainak javítását</li>
                        <li><strong>Törlési jog ("elfelejtési jog"):</strong> Kérheti adatainak törlését</li>
                        <li><strong>Korlátozási jog:</strong> Kérheti adatainak feldolgozásának korlátozását</li>
                        <li><strong>Adathordozhatóság:</strong> Kérheti adatainak géppel olvasható formátumban történő átadását</li>
                        <li><strong>Kifogás jog:</strong> Kifogásolhatja adatainak bizonyos célú feldolgozását</li>
                    </ul>

                    <p>
                        <strong class="text-cyan-400">Joggyakorlás:</strong> Jogainak gyakorlásához lépjen kapcsolatba velünk az alábbi elérhetőségeken. Választhat továbbá a Discord adatvédelmi kérelmek benyújtását is.
                    </p>

                    <p>
                        <strong class="text-yellow-400">Azonosítás:</strong> A jogok gyakorlása érdekében bizonyos azonosításra lehet szükség, hogy megbizonyosodjunk arról, Ön az adott felhasználó.
                    </p>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-pink-400 mb-6 flex items-center">
                    <i class="fas fa-cookie mr-3"></i>
                    7. Cookie-k és követési technológiák
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        <strong class="text-pink-400">Weboldal cookie-k:</strong> Weboldalunk alapvető működéséhez szükséges cookie-kat használ:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Munkamenet cookie-k (bejelentkezéshez)</li>
                        <li>Preferencia cookie-k (nyelvi beállítások)</li>
                        <li>Biztonsági cookie-k (védelem botnet támadások ellen)</li>
                    </ul>

                    <p>
                        <strong class="text-blue-400">Analitikai cookie-k:</strong> Google Analytics segítségével gyűjtünk anonimizált használati statisztikákat a weboldal fejlesztéséhez.
                    </p>

                    <p>
                        <strong class="text-green-400">Discord bot követés:</strong> A bot nem használ követési technológiákat vagy cookie-kat a Discord platformon belül.
                    </p>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-yellow-400 mb-6 flex items-center">
                    <i class="fas fa-child mr-3"></i>
                    8. Gyermekek adatvédelme
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        A szolgáltatás nem gyűjt tudatosan személyes adatokat 13 év alatti gyermekektől, összhangban a Discord felhasználási feltételeivel.
                    </p>
                    <p>
                        <strong class="text-yellow-400">13-18 éves felhasználók:</strong> 13 és 18 év közötti felhasználóinkat javasoljuk, hogy szülői vagy gyámfelügyelet mellett használják a szolgáltatást.
                    </p>
                    <p>
                        Ha tudomásunkra jut, hogy 13 év alatti gyermek adatait gyűjtöttük, azonnal intézkedünk az adatok törlésére.
                    </p>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-orange-400 mb-6 flex items-center">
                    <i class="fas fa-globe-europe mr-3"></i>
                    9. Nemzetközi adatátvitel
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        <strong class="text-orange-400">Adattárolás helye:</strong> Az adatokat elsősorban az Európai Unión belül tároljuk. Egyes alvállalkozóink (pl. hosting szolgáltatók) adatközpontjai az Egyesült Államokban is elhelyezkedhetnek.
                    </p>
                    <p>
                        <strong class="text-blue-400">Átviteli mechanizmusok:</strong> Az EU-n kívüli adatátvitel esetén megfelelő védelmi mechanizmusokat alkalmazunk:
                    </p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Megfelelőségi határozatok (pl. EU-US Privacy Shield)</li>
                        <li>Szabványos szerződési záradékok</li>
                        <li>Vállalati kötelezettségvállalási szabályok</li>
                    </ul>
                </div>
            </section>

            <section class="bg-slate-800/30 rounded-2xl p-8 border border-slate-700/50">
                <h2 class="text-2xl font-bold text-sky-400 mb-6 flex items-center">
                    <i class="fas fa-question-circle mr-3"></i>
                    10. További információk
                </h2>
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        <strong class="text-sky-400">Változtatások:</strong> Fenntartjuk a jogot, hogy időnként frissítsük ezeket az Adatvédelmi Irányelveket. A változásokról értesítjük felhasználóinkat a weboldalunkon vagy a Discord szerverünkön keresztül.
                    </p>
                    <p>
                        <strong class="text-purple-400">Kapcsolat:</strong> Kérdéseivel vagy észrevételeivel kapcsolatban keressen minket bátran az alábbi elérhetőségeken.
                    </p>
                    <p>
                        <strong class="text-yellow-400">Felügyeleti hatóság:</strong> Ha úgy véli, hogy adatvédelmi jogait megsértettük, jogában áll panaszt benyújtani a releváns adatvédelmi hatósághoz.
                    </p>
                </div>
            </section>

            <section class="bg-gradient-to-r from-indigo-600/20 to-emerald-600/20 rounded-2xl p-8 border border-indigo-500/30 text-center">
                <h2 class="text-2xl font-bold text-indigo-400 mb-4">
                    <i class="fas fa-headset mr-2"></i>
                    Kapcsolatfelvétel
                </h2>
                <p class="text-slate-300 mb-6">
                    Ha bármilyen kérdése van az adatvédelmi irányelveinkkel kapcsolatban, vagy jogait szeretné gyakorolni, keressen minket az alábbi módokon:
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="bg-discord hover:bg-discord-dark text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                        <i class="fab fa-discord mr-2"></i>
                        Discord támogatás
                    </a>
                    <a href="mailto:privacy@dutymanager.eu" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-6 py-3 rounded-lg font-medium transition-all duration-300 border border-slate-600">
                        <i class="fas fa-envelope mr-2"></i>
                        Adatvédelmi email
                    </a>
                </div>
                <p class="text-slate-400 mt-6 text-sm">
                    Adatvédelmi tisztviselő: John Doe<br>
                    Email: dpo@dutymanager.eu
                </p>
            </section>
        </div>
    </div>
@endsection
