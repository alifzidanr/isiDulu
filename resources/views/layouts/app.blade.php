@php
    $user = auth()->user();
    $nav = [
        ['label' => 'Menu', 'items' => [
            ['Dashboard', 'dashboard', 'dashboard', 'layout-dashboard'],
            ['Permohonan', 'permohonan.index', 'permohonan.index', 'file-text'],
            ['Print Permohonan', 'permohonan.print', 'permohonan.print*', 'printer'],
        ]],
    ];
    if ($user && $user->access_level <= 1) {
        $master = [
            ['Kampus', 'master.kampus.index', 'master.kampus.*', 'building-2'],
            ['Unit', 'master.unit.index', 'master.unit.*', 'network'],
            ['Sub Unit', 'master.sub-unit.index', 'master.sub-unit.*', 'git-branch'],
        ];
        if ($user->access_level === 0) {
            $master[] = ['User', 'master.user.index', 'master.user.*', 'users'];
        }
        array_push($master,
            ['Jenis Perangkat', 'master.jenis-perangkat.index', 'master.jenis-perangkat.*', 'laptop'],
            ['Jenis Perawatan', 'master.jenis-perawatan.index', 'master.jenis-perawatan.*', 'wrench'],
            ['Detail Perawatan', 'master.detail-perawatan.index', 'master.detail-perawatan.*', 'list-checks'],
            ['Perangkat Terdaftar', 'master.perangkat-terdaftar.index', 'master.perangkat-terdaftar.*', 'clipboard-list'],
        );
        $nav[] = ['label' => 'Master Data', 'items' => $master];
    }
    $roles = [0 => 'Superadmin', 1 => 'Admin', 2 => 'User'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HelpDesk')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen">
@auth
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity x-on:click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

        {{-- Sidebar --}}
        <aside x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="no-print bg-sidebar text-sidebar-foreground border-sidebar-border fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex h-14 items-center gap-2 px-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <span class="bg-primary text-primary-foreground flex size-8 items-center justify-center rounded-lg">
                        <x-ui.icon name="headset" />
                    </span>
                    <span class="grid leading-tight">
                        <span class="text-sm font-semibold">HelpDesk</span>
                        <span class="text-muted-foreground text-xs">Layanan TI</span>
                    </span>
                </a>
                <x-ui.button variant="ghost" size="icon-sm" icon="x" class="ml-auto lg:hidden" x-on:click="sidebarOpen = false">
                    <span class="sr-only">Tutup menu</span>
                </x-ui.button>
            </div>

            <nav class="flex-1 overflow-y-auto px-2 py-2">
                @foreach ($nav as $group)
                    <div class="mb-4">
                        <p class="text-muted-foreground px-2 pb-1 text-xs font-medium">{{ $group['label'] }}</p>
                        <ul class="grid gap-0.5">
                            @foreach ($group['items'] as [$label, $route, $pattern, $icon])
                                @php $active = request()->routeIs($pattern); @endphp
                                <li>
                                    <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                                       @class([
                                           'flex h-8 items-center gap-2 rounded-md px-2 text-sm transition-colors',
                                           'bg-sidebar-accent text-sidebar-accent-foreground font-medium' => $active,
                                           'hover:bg-sidebar-accent hover:text-sidebar-accent-foreground text-sidebar-foreground/80' => ! $active,
                                       ])>
                                        <x-ui.icon :name="$icon" @class(['text-primary' => $active]) />
                                        {{ $label }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <div class="border-sidebar-border border-t p-2">
                <div class="flex items-center gap-2 rounded-md p-2">
                    <x-ui.avatar :name="$user->nama_lengkap" />
                    <div class="grid min-w-0 flex-1 leading-tight">
                        <span class="truncate text-sm font-medium">{{ $user->nama_lengkap }}</span>
                        <span class="text-muted-foreground truncate text-xs">{{ $roles[$user->access_level] ?? 'User' }}</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="no-print bg-background/95 supports-[backdrop-filter]:bg-background/60 sticky top-0 z-30 flex h-14 items-center gap-2 border-b px-4 backdrop-blur lg:px-6">
                <x-ui.button variant="ghost" size="icon-sm" icon="panel-left" class="lg:hidden" x-on:click="sidebarOpen = true">
                    <span class="sr-only">Buka menu</span>
                </x-ui.button>
                <x-ui.separator orientation="vertical" class="!h-4 lg:hidden" />
                <span class="text-sm font-medium">@yield('page', 'HelpDesk')</span>

                <div class="ml-auto">
                    <x-ui.dropdown-menu align="end" width="w-56">
                        <x-slot:trigger>
                            <button type="button" class="focus-visible:ring-ring/50 cursor-pointer rounded-full outline-none focus-visible:ring-[3px]">
                                <x-ui.avatar :name="$user->nama_lengkap" />
                                <span class="sr-only">Menu pengguna</span>
                            </button>
                        </x-slot:trigger>
                        <div class="px-2 py-1.5">
                            <p class="truncate text-sm font-medium">{{ $user->nama_lengkap }}</p>
                            <p class="text-muted-foreground truncate text-xs">{{ $user->email }}</p>
                        </div>
                        <x-ui.dropdown-menu.separator />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.dropdown-menu.item type="submit" icon="log-out">Keluar</x-ui.dropdown-menu.item>
                        </form>
                    </x-ui.dropdown-menu>
                </div>
            </header>

            <main class="flex-1 p-4 md:p-6 lg:p-8">
                <div class="mx-auto w-full max-w-7xl">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
@else
    <header class="bg-background/95 sticky top-0 z-30 border-b backdrop-blur">
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('public.index') }}" class="flex items-center gap-2">
                <span class="bg-primary text-primary-foreground flex size-8 items-center justify-center rounded-lg">
                    <x-ui.icon name="headset" />
                </span>
                <span class="text-sm font-semibold">HelpDesk</span>
            </a>
            @unless (request()->routeIs('login'))
                <x-ui.button :href="route('login')" variant="outline" size="sm" icon="log-in">Login</x-ui.button>
            @endunless
        </div>
    </header>
    <main class="px-4 py-6 sm:px-6 sm:py-10">
        <div class="mx-auto w-full max-w-7xl">
            @yield('content')
        </div>
    </main>
@endauth

    <x-ui.toaster />
    @stack('scripts')
</body>
</html>
