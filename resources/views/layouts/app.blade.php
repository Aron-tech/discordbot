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
    <x-layout>
        <x-slot:header>
            <x-layout.header>
                <x-slot:right>
                    <x-theme-switch only-icons />
                    <x-avatar image="{{auth()->user()->avatar}}"/>
                    <x-dropdown icon="chevron-down">
                        <x-dropdown.items @class('focus:bg-gray-200 border-b-2 border-gray-300') text="Hello, {{ auth()->user()->name }}!" />
                        <x-dropdown.items text="{{__('Select server')}}" icon="globe-alt" href="{{route('guild.selector')}}" />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown.items text="Logout" icon="arrow-left-start-on-rectangle" onclick="event.preventDefault(); this.closest('form').submit();" />
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
                       @if(auth()->user()->can('hasPermission', [\App\Models\GuildSelector::getGuild(), \App\Enums\PermissionEnum::VIEW_ADMIN_PANEL]))
                            <x-side-bar.separator text="Admin Menü" line />
                            <x-side-bar.item text="{{__('Admin Panel')}}" icon="computer-desktop" :route="route('admin.panel')"  :current="Route::currentRouteNamed('admin.panel')"/>
                            <x-side-bar.item text="{{__('Duty logs')}}" icon="clipboard-document-list" :route="route('admin.logs')"  :current="Route::currentRouteNamed('admin.logs')"/>
                            <x-side-bar.item text="{{__('Settings')}}" icon="cog" :route="route('admin.settings')"  :current="Route::currentRouteNamed('admin.settings')" />
                        @endif
                </x-side-bar>
            </x-slot:menu>
        @endif
        {{ $slot }}
    </x-layout>

    @livewireScripts
</body>
</html>
