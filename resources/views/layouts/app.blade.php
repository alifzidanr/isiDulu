@php
    $user = auth()->user();
    $nav = [
        ['label' => 'Layanan', 'items' => [
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
        $nav[] = ['label' => 'Organisasi', 'items' => $master];
        $nav[] = ['label' => 'Perangkat & Perawatan', 'items' => [
            ['Jenis Perangkat', 'master.jenis-perangkat.index', 'master.jenis-perangkat.*', 'laptop'],
            ['Perangkat Terdaftar', 'master.perangkat-terdaftar.index', 'master.perangkat-terdaftar.*', 'clipboard-list'],
            ['Jenis Perawatan', 'master.jenis-perawatan.index', 'master.jenis-perawatan.*', 'wrench'],
            ['Detail Perawatan', 'master.detail-perawatan.index', 'master.detail-perawatan.*', 'list-checks'],
        ]];
    }
    $roles = [0 => 'Superadmin', 1 => 'Admin', 2 => 'User'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
    <title>@yield('title', 'HelpDesk')</title>
    @stack('styles')
</head>
<body class="bg-muted/40 min-h-screen">
@auth
    <div x-data="{ sidebarOpen: false }"
         x-effect="document.documentElement.classList.toggle('overflow-hidden', sidebarOpen)"
         x-on:keydown.escape.window="sidebarOpen = false"
         x-on:resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
         class="bg-muted/40 flex min-h-dvh">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity x-on:click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/30 lg:hidden"></div>

        {{-- Sidebar --}}
        <aside x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="no-print bg-background text-foreground fixed top-0 left-0 z-50 flex h-dvh w-72 max-w-[85vw] shrink-0 flex-col border-r shadow-lg transition-transform duration-200 ease-in-out lg:sticky lg:w-64 lg:translate-x-0 lg:shadow-none">
            <div class="flex h-16 shrink-0 items-center gap-2 border-b px-5">
                <a href="{{ route('dashboard') }}" class="flex flex-1 items-center">
                    <x-app-logo size="size-8" />
                </a>
                <x-ui.button variant="ghost" size="icon-sm" icon="x" class="lg:hidden" x-on:click="sidebarOpen = false">
                    <span class="sr-only">Tutup menu</span>
                </x-ui.button>
            </div>

            <nav class="min-h-0 flex-1 space-y-6 overflow-y-auto overscroll-contain px-3 py-4">
                @foreach ($nav as $group)
                    <div class="grid gap-1">
                        <p class="text-muted-foreground px-3 pb-1 text-[11px] font-semibold tracking-wider uppercase">{{ $group['label'] }}</p>
                        @foreach ($group['items'] as [$label, $route, $pattern, $icon])
                            @php $active = request()->routeIs($pattern); @endphp
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                               @class([
                                   'group flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                   'bg-accent text-accent-foreground' => $active,
                                   'text-muted-foreground hover:bg-accent/50 hover:text-foreground' => ! $active,
                               ])>
                                <x-ui.icon :name="$icon" class="size-4" />
                                <span class="truncate">{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

        </aside>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="no-print bg-background/95 supports-[backdrop-filter]:bg-background/80 sticky top-0 z-30 flex h-16 shrink-0 items-center gap-2 border-b px-4 backdrop-blur sm:px-6">
                <x-ui.button variant="ghost" size="icon-sm" icon="panel-left" class="-ml-1 lg:hidden" x-on:click="sidebarOpen = true">
                    <span class="sr-only">Buka menu</span>
                </x-ui.button>
                <x-ui.separator orientation="vertical" class="mr-1 !h-4 lg:hidden" />
                <nav aria-label="Breadcrumb" class="flex min-w-0 items-center gap-1.5 text-sm">
                    @foreach (explode(' / ', trim($__env->yieldContent('page', 'HelpDesk'))) as $crumb)
                        @if (! $loop->first)<span class="hidden sm:contents"><x-ui.icon name="chevron-right" class="text-muted-foreground size-3.5" /></span>@endif
                        <span @class(['truncate', 'text-foreground font-medium' => $loop->last, 'text-muted-foreground hidden sm:inline' => ! $loop->last])>{{ $crumb }}</span>
                    @endforeach
                </nav>
                <x-ui.dropdown-menu align="end" width="w-60" class="ml-auto">
                    <x-slot:trigger>
                        <button type="button" class="hover:bg-accent flex cursor-pointer items-center gap-3 rounded-md px-2 py-1.5 text-left transition-colors" aria-label="Menu akun">
                            <span class="hidden text-right sm:block">
                                <span class="block max-w-48 truncate text-sm leading-tight font-medium">{{ $user->nama_lengkap }}</span>
                                <span class="text-muted-foreground block text-xs leading-tight">{{ $roles[$user->access_level] ?? 'User' }}</span>
                            </span>
                            <x-ui.avatar :name="$user->nama_lengkap" size="size-9" />
                        </button>
                    </x-slot:trigger>
                    <x-ui.dropdown-menu.label>
                        <p class="truncate font-medium">{{ $user->nama_lengkap }}</p>
                        <p class="text-muted-foreground truncate text-xs font-normal">{{ $user->unit->nama_unit ?? $user->email }}</p>
                    </x-ui.dropdown-menu.label>
                    <x-ui.dropdown-menu.separator />
                    <x-ui.dropdown-menu.item icon="user-cog" :href="route('profile.edit')">Pengaturan Profil</x-ui.dropdown-menu.item>
                    <x-ui.dropdown-menu.item icon="key-round" :href="route('profile.edit') . '#ganti-password'">Ganti Password</x-ui.dropdown-menu.item>
                    <x-ui.dropdown-menu.separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.dropdown-menu.item type="submit" icon="log-out" variant="destructive">Keluar</x-ui.dropdown-menu.item>
                    </form>
                </x-ui.dropdown-menu>
            </header>

            <main class="flex-1 p-4 md:p-6 lg:p-8">
                <div class="mx-auto w-full max-w-7xl">
                    @yield('content')
                </div>
            </main>

            @include('partials.footer')
        </div>
    </div>
@else
    <header class="bg-background/90 sticky top-0 z-30 border-b backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('public.index') }}"><x-app-logo /></a>
            @unless (request()->routeIs('login'))
                <x-ui.button :href="route('login')" variant="outline" size="sm" icon="log-in">Login</x-ui.button>
            @endunless
        </div>
    </header>
    <div class="flex min-h-[calc(100dvh-4rem)] flex-col">
        <main class="flex-1 px-4 py-6 sm:px-6 sm:py-10">
            <div class="mx-auto w-full max-w-7xl">
                @yield('content')
            </div>
        </main>

        @include('partials.footer')
    </div>
@endauth

    <x-ui.toaster />
    @stack('scripts')
</body>
</html>
