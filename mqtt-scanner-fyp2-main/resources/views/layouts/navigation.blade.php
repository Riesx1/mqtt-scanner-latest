<!-- Navigation Bar -->
<nav class="bg-gray-800 border-b border-gray-700 sticky top-0 z-50 shadow-lg">
    <div class="container mx-auto px-6">
        <div class="flex items-center justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center space-x-8">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span class="text-xl font-bold text-white">MQTT Security Scanner</span>
                </a>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-300 hover:text-white px-3 py-2 rounded-md transition {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        MQTT Scanner
                    </a>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-300 hover:text-white px-3 py-2 rounded-md transition {{ request()->routeIs('profile.edit') ? 'bg-gray-700 text-white' : '' }}">
                        Profile
                    </a>
                </div>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-400 hidden md:block">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition">
                        Logout
                    </button>
                </form>

                <!-- Mobile menu button -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-400 hover:text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-gray-700 mt-2">
            <div class="space-y-1 pt-2">
                <a href="{{ route('dashboard') }}" class="block text-sm text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded-md transition {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}">
                    MQTT Scanner
                </a>
                <a href="{{ route('profile.edit') }}" class="block text-sm text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded-md transition {{ request()->routeIs('profile.edit') ? 'bg-gray-700 text-white' : '' }}">
                    Profile
                </a>
                <div class="px-3 py-2 text-sm text-gray-400">
                    Logged in as: {{ Auth::user()->name }}
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script>
