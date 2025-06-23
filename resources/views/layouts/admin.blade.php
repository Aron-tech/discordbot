<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }} - DutyManager</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <tallstackui:script />
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<x-layout>
    <x-slot:header>
        <x-layout.header>
            <x-slot:right>
                <x-avatar image="{{auth()->user()->avatar}}"/>
                <x-dropdown icon="chevron-down">
                    <x-dropdown.items @class('bg-gray-200 hover:bg-gray-200 focus:bg-gray-200 border-b-2 border-gray-300') text="Hello, {{ auth()->user()->name }}!" />
                    @if(\App\Models\GuildSelector::hasGuild())
                        <x-dropdown.items text="{{__('Select server')}}" icon="globe-alt" href="{{route('home')}}" />
                    @endif
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
                <x-side-bar.item text="{{__('Select server')}}" icon="globe-alt" :route="route('home')" />
                <x-side-bar.item text="Settings" icon="cog" :route="route('home')" />
            </x-side-bar>
        </x-slot:menu>
    @endif
    {{ $slot }}
</x-layout>

@livewireScripts
</body>
</html>
