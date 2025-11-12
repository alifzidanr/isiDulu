<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HelpDesk')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <!-- Updated Navigation Bar with modern styling -->
    <nav class="relative bg-white shadow">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
            <div class="relative flex h-16 justify-between">
                <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                    @auth
                        <!-- Mobile menu button -->
                        <button type="button" id="mobile-menu-button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-600">
                            <span class="absolute -inset-0.5"></span>
                            <span class="sr-only">Open main menu</span>
                            <svg id="hamburger-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="size-6">
                                <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg id="hamburger-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" class="size-6 hidden">
                                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    @endauth
                </div>
                
               <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                    <div class="flex shrink-0 items-center">
                        <a href="{{ auth()->check() ? route('dashboard') : route('public.index') }}" class="text-xl font-bold text-blue-600">HelpDesk</a>
                    </div>
                </div>
                
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 sm:static sm:inset-auto sm:ml-6 sm:pr-0">
                    @guest
                        @if(!request()->routeIs('login'))
                        <a href="{{ route('login') }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">Login</a>
                        @endif
                    @else
                        <!-- Profile dropdown -->
                        <div class="relative ml-3">
                            <button id="profile-dropdown-button" class="relative flex rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
                                <span class="absolute -inset-1.5"></span>
                                <span class="sr-only">Open user menu</span>
                                <div class="size-8 rounded-full bg-blue-600 flex items-center justify-center">
                                    <span class="text-xs font-medium text-white">{{ substr(auth()->user()->nama_lengkap, 0, 1) }}</span>
                                </div>
                            </button>

                            <!-- Profile dropdown menu -->
                            <div id="profile-dropdown-menu" class="absolute right-0 z-10 mt-2 w-52 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden">
                                <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-200">
                                    <div class="font-medium">{{ auth()->user()->nama_lengkap }}</div>
                                    <div class="text-gray-500">{{ auth()->user()->email }}</div>
                                </div>
                                <form action="{{ route('logout') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        @auth
        <div id="mobile-menu" class="sm:hidden hidden">
            <div class="space-y-1 pb-3 pt-2">
                <a href="{{ route('dashboard') }}" 
                   class="block border-l-4 {{ request()->routeIs('dashboard') ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} py-2 pl-3 pr-4 text-base font-medium">
                    Dashboard
                </a>
                <a href="{{ route('permohonan.index') }}" 
                   class="block border-l-4 {{ request()->routeIs('permohonan.index') ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} py-2 pl-3 pr-4 text-base font-medium">
                    Permohonan
                </a>
                <a href="{{ route('permohonan.print') }}" 
                   class="block border-l-4 {{ request()->routeIs('permohonan.print') ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800' }} py-2 pl-3 pr-4 text-base font-medium">
                    Print Permohonan
                </a>
            </div>
        </div>
        @endauth
    </nav>

    @auth
    <div class="flex">
        <!-- Mobile sidebar overlay -->
        <div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>
        
        <!-- Updated Sidebar with modern styling -->
        <aside id="sidebar" class="fixed lg:relative lg:translate-x-0 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 w-64 bg-white shadow-sm min-h-screen border-r border-gray-200">
            <div class="relative flex grow flex-col gap-y-5 overflow-y-auto px-6 pt-5">
                <!-- Close button for mobile -->
                <div class="lg:hidden flex justify-end pt-4">
                    <button id="sidebar-close" class="p-2 rounded-md text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                
                <nav class="relative flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-1">
                                <li>
                                    <a href="{{ route('dashboard') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-tachometer-alt {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('permohonan.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('permohonan.index') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-file-alt {{ request()->routeIs('permohonan.index') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Permohonan
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('permohonan.print') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('permohonan.print') ? 'bg-gray-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                                        <i class="fas fa-print {{ request()->routeIs('permohonan.print') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }} text-base"></i>
                                        Print Permohonan
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        @if(auth()->user()->access_level <= 1)
                        <li>
                            <div class="text-xs font-semibold leading-6 text-gray-400">Master Data</div>
                            <ul role="list" class="-mx-2 mt-2 space-y-1">
                                <li>
                                    <a href="{{ route('master.kampus.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.kampus.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-building {{ request()->routeIs('master.kampus.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Kampus
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master.unit.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.unit.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-sitemap {{ request()->routeIs('master.unit.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Unit
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master.sub-unit.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.sub-unit.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-project-diagram {{ request()->routeIs('master.sub-unit.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Sub Unit
                                    </a>
                                </li>
                                
                                @if(auth()->user()->access_level === 0)
                                <li>
                                    <a href="{{ route('master.user.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.user.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-users {{ request()->routeIs('master.user.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        User
                                    </a>
                                </li>
                                @endif
                                
                                <li>
                                    <a href="{{ route('master.jenis-perangkat.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.jenis-perangkat.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-laptop {{ request()->routeIs('master.jenis-perangkat.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Jenis Perangkat
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master.jenis-perawatan.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.jenis-perawatan.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-tools {{ request()->routeIs('master.jenis-perawatan.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Jenis Perawatan
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master.detail-perawatan.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.detail-perawatan.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-list-ul {{ request()->routeIs('master.detail-perawatan.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Detail Perawatan
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master.perangkat-terdaftar.index') }}" 
                                       class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold {{ request()->routeIs('master.perangkat-terdaftar.*') ? 'bg-gray-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}">
                                        <i class="fas fa-clipboard-list {{ request()->routeIs('master.perangkat-terdaftar.*') ? 'text-blue-600' : 'text-gray-400 group-hover:text-blue-600' }} text-base"></i>
                                        Perangkat Terdaftar
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </aside>
        
        <main class="flex-1 p-4 md:p-6 w-full min-w-0">
            @yield('content')
        </main>
    </div>
    @else
    <main class="p-4 md:p-6">
        @yield('content')
    </main>
    @endauth

    @stack('scripts')
    
    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('mobile-sidebar-overlay');
            const sidebarClose = document.getElementById('sidebar-close');
            const hamburgerOpen = document.getElementById('hamburger-open');
            const hamburgerClose = document.getElementById('hamburger-close');
            const profileDropdownButton = document.getElementById('profile-dropdown-button');
            const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
            const mobileMenu = document.getElementById('mobile-menu');

            // Toggle sidebar
            function toggleSidebar() {
                if (sidebar) {
                    const isOpen = !sidebar.classList.contains('-translate-x-full');
                    
                    if (isOpen) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                }
            }

            function openSidebar() {
                if (sidebar && sidebarOverlay && hamburgerOpen && hamburgerClose) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.remove('hidden');
                    hamburgerOpen.classList.add('hidden');
                    hamburgerClose.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            }

            function closeSidebar() {
                if (sidebar && sidebarOverlay && hamburgerOpen && hamburgerClose) {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    hamburgerOpen.classList.remove('hidden');
                    hamburgerClose.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }

            // Toggle mobile menu
            function toggleMobileMenu() {
                if (mobileMenu && hamburgerOpen && hamburgerClose) {
                    mobileMenu.classList.toggle('hidden');
                    const isOpen = !mobileMenu.classList.contains('hidden');
                    hamburgerOpen.classList.toggle('hidden', isOpen);
                    hamburgerClose.classList.toggle('hidden', !isOpen);
                }
            }

            // Toggle profile dropdown
            function toggleProfileDropdown() {
                if (profileDropdownMenu) {
                    profileDropdownMenu.classList.toggle('hidden');
                }
            }

            // Event listeners
            if (mobileMenuButton) {
                mobileMenuButton.addEventListener('click', function() {
                    if (window.innerWidth < 1024) { // lg breakpoint
                        toggleSidebar();
                    } else {
                        toggleMobileMenu();
                    }
                });
            }

            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }

            if (profileDropdownButton) {
                profileDropdownButton.addEventListener('click', toggleProfileDropdown);
            }

            // Close sidebar when clicking on menu items (mobile only)
            if (sidebar) {
                const menuItems = sidebar.querySelectorAll('a');
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        if (window.innerWidth < 1024) { // lg breakpoint
                            closeSidebar();
                        }
                    });
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                if (profileDropdownMenu && profileDropdownButton && 
                    !profileDropdownMenu.contains(event.target) && 
                    !profileDropdownButton.contains(event.target)) {
                    profileDropdownMenu.classList.add('hidden');
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) { // lg breakpoint
                    closeSidebar();
                    profileDropdownMenu && profileDropdownMenu.classList.add('hidden');
                    mobileMenu && mobileMenu.classList.add('hidden');
                }
            });

            // Handle escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeSidebar();
                    profileDropdownMenu && profileDropdownMenu.classList.add('hidden');
                    mobileMenu && mobileMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>