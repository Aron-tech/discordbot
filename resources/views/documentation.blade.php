@extends('layouts.guest')

@section('title', 'DutyManager 2.0 - Dokumentáció')

@section('content')
    <x-page-header
        title="DutyManager 2.0 Dokumentáció"
        subtitle="Utoljára frissítve: {{ now()->format('Y. F j.') }}"
        :badge="['icon' => 'fas fa-book', 'text' => 'Hivatalos dokumentáció']"
        notice="A bot teljes körű használati útmutatója és dokumentációja." />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-12">
            <!-- Introduction Section -->
            <x-docs.section title="Bevezetés" icon="fa-info-circle" color="purple">
                <div class="space-y-4 text-slate-300 leading-relaxed">
                    <p>
                        A DutyManager egy speciális Discord bot, amelyet kifejezetten <strong class="text-yellow-400">FiveM roleplay rendvédelmi frakciók</strong> számára fejlesztettünk. A bot célja, hogy egyszerűbbé és hatékonyabbá tegye a frakciók szolgálatainak, figyelmeztetéseinek és tagkezelésének folyamatait.
                    </p>
                    <div class="bg-blue-900/20 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <p class="font-medium text-blue-200">
                            <i class="fas fa-lightbulb mr-2"></i>
                            A bot folyamatos fejlesztés alatt áll.
                        </p>
                    </div>
                </div>
            </x-docs.section>

            <!-- Installation Section -->
            <x-docs.section title="Telepítés és kezdeti beállítás" icon="fa-rocket" color="green">
                <div class="space-y-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-green-900/30 rounded-lg p-3">
                            <i class="fas fa-shield-alt text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-green-300">Előfeltételek</h3>
                            <p class="mt-1 text-slate-300">
                                Győződj meg róla, hogy rendelkezel <code class="bg-slate-700 px-2 py-1 rounded">Tulajdonosi</code> jogosultsággal a Discord szerveren, ahol a botot telepíteni szeretnéd.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-blue-900/30 rounded-lg p-3">
                            <i class="fas fa-terminal text-blue-400 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-blue-300">Telepítési lépések</h3>
                            <ol class="mt-1 text-slate-300 list-decimal list-inside space-y-2">
                                <li>Add hozzá a botot a szerveredhez a <a href="https://discord.com/oauth2/authorize?client_id=1377217826104016928&permissions=8&integration_type=0&scope=bot" target="_blank" class="text-blue-400 hover:underline">meghívó link</a> segítségével</li>
                                <li>Add meg a szükséges beállításokat a <a href="{{route('admin.install')}}" target="_blank" class="text-blue-400 hover:underline">weboldalon</a> keresztül.</li>
                                <li>Használd a szerveren az alábbi parancsot:</li>
                            </ol>
                            <div class="mt-2 bg-slate-800 rounded-lg p-4 overflow-x-auto">
                                <code class="text-green-300 font-mono">/install</code>
                            </div>
                            <p class="mt-2 text-slate-300">
                                Ez a parancs elindítja az interaktív telepítő varázslót, ha sikeresen lefutott máris elérhetővé válik az összes parancs.
                            </p>
                        </div>
                    </div>

                    <div class="bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                        <p class="font-medium text-yellow-200">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            A discord bot <code class="bg-yellow-900/50 px-2 py-1 rounded">béta állapotban</code> elérhető. Előfordulhatnak kisebb hibák vagy hiányosságok. Kérjük, jelezd ezeket a discordon: roka2003-nak.
                        </p>
                    </div>
                </div>
            </x-docs.section>

            <!-- Commands Section -->
            <x-docs.section title="Parancsok teljes listája" icon="fa-terminal" color="purple">
                <!-- User Management -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-purple-300 mb-4 flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        Felhasználókezelés
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <x-docs.command-card
                            command="adduser"
                            description="Felhasználó hozzáadása a rendszerhez"
                            usage="/adduser @felhasználó IC név Jelvényszám"
                            example="/adduser @Roka 'Aron Fox' 1234"
                        />

                        <x-docs.command-card
                            command="deleteuser"
                            description="Felhasználó eltávolítása a rendszerből"
                        />

                        <x-docs.command-card
                            command="info"
                            description="Saját információid megtekintése"
                        />

                        <x-docs.command-card
                            command="userinfo"
                            description="Felhasználó információinak megtekintése (moderátor és admin)"
                            usage="/userinfo @felhasználó"
                            example="/userinfo @Roka"
                        />

                        <x-docs.command-card
                            command="rolemanager"
                            description="Frakció rangok kezelése"
                        />
                    </div>
                </div>

                <!-- Warnings -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-red-300 mb-4 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Figyelmeztetések
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <x-docs.command-card
                            command="addwarn"
                            description="Figyelmeztetés hozzáadása egy felhasználónak"
                            usage="/addwarn @felhasználó indok"
                            example="/addwarn @Roka 'Több órás inaktivitás'"
                            color="red"
                        />
                    </div>
                </div>

                <!-- System Tools -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-blue-300 mb-4 flex items-center">
                        <i class="fas fa-tools mr-2"></i>
                        Rendszer és fejlesztői eszközök
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <x-docs.command-card
                            command="devmod"
                            description="Fejlesztői mód kapcsolása"
                            color="blue"
                        />
                    </div>
                </div>
            </x-docs.section>

            <!-- Duty Management Section -->
            <x-docs.section title="Szolgálatkezelés" icon="fa-clock" color="orange">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Basic Duty Commands -->
                    <div>
                        <h3 class="text-xl font-bold text-orange-300 mb-4">Alapvető szolgálati parancsok</h3>
                        <div class="space-y-4">
                            <x-docs.command-card
                                command="dutyon"
                                description="Szolgálat kezdése"
                                color="orange"
                            />

                            <x-docs.command-card
                                command="dutyoff"
                                description="Szolgálat befejezése"
                                color="orange"
                            />

                            <x-docs.command-card
                                command="dutycancel"
                                description="Szolgálat megszakítása (saját)"
                                color="orange"
                            />

                            <x-docs.command-card
                                command="dutyfcancel"
                                description="Szolgálat erőszakos megszakítása (admin)"
                                color="orange"
                            />
                        </div>
                    </div>

                    <!-- Duty Utilities -->
                    <div>
                        <h3 class="text-xl font-bold text-orange-300 mb-4">Szolgálati segédletek</h3>
                        <div class="space-y-4">
                            <x-docs.command-card
                                command="dutycheck"
                                description="Szolgálat állapotának ellenőrzése"
                                color="orange"
                            />

                            <x-docs.command-card
                                command="dutylist"
                                description="Aktív szolgálatban lévők listája"
                                color="orange"
                            />

                            <x-docs.command-card
                                command="dutyclear"
                                description="Szolgálati adatok törlése"
                                color="orange"
                            />

                            <x-docs.command-card
                                command="dutyreset"
                                description="Szolgálatok teljes alaphelyzetbe állítása"
                                color="orange"
                            />

                            <x-docs.command-card
                                command="dutymanager"
                                description="Interaktív szolgálatkezelő menü"
                                color="orange"
                            />
                        </div>
                    </div>
                </div>
            </x-docs.section>

            <!-- Button Functions Section -->
            <x-docs.section title="Gomb Funkciók" icon="fa-mouse-pointer" color="indigo">
                <div class="grid md:grid-cols-2 gap-4">
                    <x-docs.button-card
                        label="Szolgálat indítása"
                        description="Ugyanaz, mint az /dutyon parancs, de gombbal"
                        color="indigo"
                    />

                    <x-docs.button-card
                        label="Szolgálat leállítása"
                        description="Ugyanaz, mint az /dutyoff parancs"
                        color="red"
                    />

                    <x-docs.button-card
                        label="Szolgálat megszakítása"
                        description="Szolgálat megszakítása admin jogosultsággal"
                        color="yellow"
                    />

                    <x-docs.button-card
                        label="Információk"
                        description="Részletes információk megjelenítése"
                        color="blue"
                    />
                </div>
            </x-docs.section>

            <!-- Additional Info Section -->
            <x-docs.section title="További információk" icon="fa-info-circle" color="cyan">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-xl font-bold text-cyan-300 mb-4">Technikai információk</h3>
                        <div class="space-y-3">
                            <p class="text-slate-300"><span class="font-medium text-cyan-200">Verzió:</span> 2.0</p>
                            <p class="text-slate-300"><span class="font-medium text-cyan-200">Fejlesztő:</span>roka2003</p>
                            <p class="text-slate-300"><span class="font-medium text-cyan-200">Kontakt:</span> <a href="mailto:contact@dutymanager.hu" class="text-cyan-400 hover:underline">aron.papp2003@gmail.com</a></p>
                        </div>
                    </div>

                    <div>
                        <div class="bg-slate-800/50 p-4 rounded-lg border border-slate-700/50">
                            <h4 class="font-medium text-white mb-2">Licenc</h4>
                            <p class="text-slate-300 text-sm">
                                Ez a projekt egyéni licenc alatt áll. Bármilyen használat, módosítás vagy terjesztés előtt vedd fel a kapcsolatot a fejlesztővel.
                            </p>
                        </div>
                    </div>
                </div>
            </x-docs.section>

            <!-- Support Section -->
            <section class="bg-gradient-to-r from-blue-600/20 to-purple-600/20 rounded-2xl p-8 border border-blue-500/30 text-center">
                <h2 class="text-2xl font-bold text-blue-400 mb-4">
                    <i class="fas fa-question-circle mr-2"></i>
                    Segítségre van szükséged?
                </h2>
                <p class="text-slate-300 mb-6">
                    Ha bármilyen kérdésed van a bot használatával kapcsolatban, vagy hibát észleltél, keress minket bizalommal.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="bg-discord hover:bg-discord-dark text-white px-6 py-3 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">
                        <i class="fab fa-discord mr-2"></i>
                        Discord Támogatás
                    </a>
                    <a href="mailto:aron.papp2003@gmail.com" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-6 py-3 rounded-lg font-medium transition-all duration-300 border border-slate-600">
                        <i class="fas fa-envelope mr-2"></i>
                        Email Küldése
                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection
