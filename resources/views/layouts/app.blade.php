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
<body class="min-h-screen">
@auth
    <div x-data="{ sidebarOpen: false }"
         x-effect="document.documentElement.classList.toggle('overflow-hidden', sidebarOpen)"
         x-on:keydown.escape.window="sidebarOpen = false"
         x-on:resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
         class="bg-sidebar flex min-h-dvh">
        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity x-on:click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

        {{-- Sidebar --}}
        <aside x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="no-print bg-sidebar text-sidebar-foreground fixed top-0 left-0 z-50 flex h-dvh w-72 max-w-[85vw] flex-col border-r shadow-xl transition-transform duration-200 lg:sticky lg:w-64 lg:translate-x-0 lg:border-r-0 lg:shadow-none">
            <div class="flex h-16 shrink-0 items-center gap-2 px-3">
                <a href="{{ route('dashboard') }}" class="hover:bg-sidebar-accent flex flex-1 items-center rounded-lg px-2 py-1.5 transition-colors">
                    <x-app-logo />
                </a>
                <x-ui.button variant="ghost" size="icon-sm" icon="x" class="lg:hidden" x-on:click="sidebarOpen = false">
                    <span class="sr-only">Tutup menu</span>
                </x-ui.button>
            </div>

            <nav class="min-h-0 flex-1 space-y-6 overflow-y-auto overscroll-contain px-3 pt-2 pb-6">
                @foreach ($nav as $group)
                    <div class="grid gap-1">
                        <p class="text-muted-foreground/80 px-3 pb-1 text-[11px] font-semibold tracking-wider uppercase">{{ $group['label'] }}</p>
                        @foreach ($group['items'] as [$label, $route, $pattern, $icon])
                            @php $active = request()->routeIs($pattern); @endphp
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                               @class([
                                   'group relative flex h-9 items-center gap-3 rounded-lg px-3 text-sm transition-all',
                                   'bg-background text-foreground font-medium shadow-xs ring-1 ring-black/5' => $active,
                                   'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-foreground' => ! $active,
                               ])>
                                @if ($active)
                                    <span class="bg-primary absolute top-1/2 -left-3 hidden h-5 w-1 -translate-y-1/2 rounded-r-full lg:block"></span>
                                @endif
                                <x-ui.icon :name="$icon" @class([
                                    'size-4 transition-colors',
                                    'text-primary' => $active,
                                    'text-muted-foreground group-hover:text-sidebar-foreground' => ! $active,
                                ]) />
                                <span class="truncate">{{ $label }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

        </aside>

        {{-- Main: an inset panel on desktop, full width on mobile --}}
        <div class="bg-background flex min-w-0 flex-1 flex-col lg:my-2 lg:mr-2 lg:rounded-xl lg:border lg:shadow-sm print:m-0 print:border-0 print:shadow-none">
            <header class="no-print bg-background/90 sticky top-0 z-30 flex h-14 shrink-0 items-center gap-2 border-b px-4 backdrop-blur lg:rounded-t-xl lg:px-6">
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
