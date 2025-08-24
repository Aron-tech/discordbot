<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="tallstackui_darkTheme()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }} - DutyManager</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <tallstackui:script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-bind:class="{ 'dark bg-gray-700': darkTheme, 'bg-white': !darkTheme }">
    <x-dialog />
    <x-toast />
    @if(request()->routeIs('admin.settings') || request()->routeIs('admin.install'))
        <x-banner color="red" text="Figyelem! A weboldalon 15 perces késleltetéssel történek meg a változások a rangok és a csatornák megjelenítésében. A mentésnél nincs." lg />
    @endif
    <x-layout>
        <x-slot:header>
            <x-layout.header>
                <x-slot:right>
                    <x-theme-switch only-icons />
                    <x-avatar image="{{auth()->user()->avatar}}"/>
                    <x-dropdown icon="chevron-down">
                        <x-dropdown.items @class('focus:bg-gray-200 border-b-2 border-gray-300') text="Hello, {{ auth()->user()->name }}!" />
                        <x-dropdown.items text="{{__('Szerver választó')}}" icon="globe-alt" href="{{route('guild.selector')}}" />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown.items text="Kijelentkezés" icon="arrow-left-start-on-rectangle" onclick="event.preventDefault(); this.closest('form').submit();" />
                        </form>
                    </x-dropdown>
                </x-slot:right>
            </x-layout.header>
        </x-slot:header>
        @if(\App\Models\GuildSelector::hasGuild())
            <x-slot:menu>
                <x-side-bar>
                    <x-side-bar.item text="{{\App\Models\GuildSelector::getGuild()->name ?? 'Nincs kiválasztva szerver'}}" current/>
                        <x-side-bar.separator text="Menü" line />
                        <x-side-bar.item text="{{__('Statisztikád')}}" icon="book-open" :route="route('dashboard')" :current="Route::currentRouteNamed('dashboard')"/>
                        <x-side-bar.item text="{{__('Toplista')}}" icon="numbered-list" :route="route('toplist')" :current="Route::currentRouteNamed('toplist')"/>
                        <x-side-bar.item text="{{__('Vizsgák')}}" icon="academic-cap" :route="route('exam')" :current="Route::currentRouteNamed('exam')"/>
                       @if(auth()->user()->can('hasPermission', [\App\Models\GuildSelector::getGuild(), \App\Enums\PermissionEnum::VIEW_ADMIN_PANEL]))
                            <x-side-bar.separator text="Admin Menü" line />
                            <x-side-bar.item text="{{__('Panel')}}" icon="computer-desktop" :route="route('admin.panel')"  :current="Route::currentRouteNamed('admin.panel')"/>
                            <x-side-bar.item text="{{__('Szolgálatban lévők')}}" icon="clipboard-document-list" :route="route('admin.duty.active')"  :current="Route::currentRouteNamed('admin.duty.active')"/>
                            <x-side-bar.item text="{{__('Duty log')}}" icon="archive-box" :route="route('admin.logs')"  :current="Route::currentRouteNamed('admin.logs')"/>
                            <x-side-bar.item text="{{__('Feketelista log')}}" icon="flag" :route="route('admin.blacklist')"  :current="Route::currentRouteNamed('admin.blacklist')"/>
                            <x-side-bar.item text="{{__('Ticket panel')}}" icon="ticket" :route="route('admin.ticket-manager')"  :current="Route::currentRouteNamed('admin.ticket-manager')"/>
                            <x-side-bar.item text="{{__('Vizsga panel')}}" icon="pencil-square" :route="route('admin.exam-manager')"  :current="Route::currentRouteNamed('admin.exam-manager')"/>
                            <x-side-bar.item text="{{__('Vizsga eredmények')}}" icon="presentation-chart-bar" :route="route('admin.exam-results')"  :current="Route::currentRouteNamed('admin.exam-results')"/>
                            <x-side-bar.item text="{{__('Statisztika')}}" icon="chart-pie" :route="route('admin.statistics')"  :current="Route::currentRouteNamed('admin.statistics')"/>
                            <x-side-bar.item text="{{__('Beállítások')}}" icon="cog" :route="route('admin.settings')"  :current="Route::currentRouteNamed('admin.settings')" />
                        @endif
                </x-side-bar>
            </x-slot:menu>
        @endif
        {{ $slot }}
    </x-layout>
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</body>
</html>
