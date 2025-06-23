<footer class="bg-slate-900/80 border-t border-slate-700/50 py-12 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 md:mb-0">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-red-500 rounded-lg flex items-center justify-center">
                    <img src="{{asset('assets/images/dutymanagr.png')}}" class="rounded-lg" alt="Dutymanager">
                </div>
                <span class="text-xl font-bold bg-gradient-to-r from-blue-400 to-red-400 bg-clip-text text-transparent">
                    DutyManager 2.0
                </span>
            </div>

            <div class="flex items-center space-x-6 text-slate-400">
                <a href="{{ route('privacy.policy') }}" class="hover:text-blue-400 transition-colors {{ request()->routeIs('privacy.policy') ? 'text-blue-400' : '' }}">Adatvédelemi tájékoztató</a>
                <a href="{{ route('terms.of.service') }}" class="hover:text-blue-400 transition-colors {{ request()->routeIs('terms.of.service') ? 'text-blue-400' : '' }}">Felhasználási feltételek</a>
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
