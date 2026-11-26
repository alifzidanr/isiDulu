{{-- shadcn/ui Pagination. Set as the default in AppServiceProvider, so every ->links() uses it. --}}
@if ($paginator->hasPages())
    @php
        $base = 'inline-flex h-9 items-center justify-center gap-1 rounded-md text-sm font-medium transition-colors';
        $ghost = $base . ' hover:bg-accent hover:text-accent-foreground';
        $disabled = $base . ' text-muted-foreground pointer-events-none opacity-50';
    @endphp
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-muted-foreground text-sm">
            Menampilkan <span class="text-foreground font-medium">{{ $paginator->firstItem() }}</span>–<span class="text-foreground font-medium">{{ $paginator->lastItem() }}</span>
            dari <span class="text-foreground font-medium">{{ $paginator->total() }}</span> data
        </p>

        <ul class="flex flex-row items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $disabled }} px-2.5"><x-ui.icon name="chevron-left" /><span class="hidden sm:inline">Sebelumnya</span></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $ghost }} px-2.5"><x-ui.icon name="chevron-left" /><span class="hidden sm:inline">Sebelumnya</span></a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="hidden sm:block"><span class="flex size-9 items-center justify-center"><x-ui.icon name="ellipsis" /></span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="{{ abs($page - $paginator->currentPage()) > 1 ? 'hidden sm:block' : '' }}">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $base }} bg-background size-9 border shadow-xs">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $ghost }} size-9">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $ghost }} px-2.5"><span class="hidden sm:inline">Berikutnya</span><x-ui.icon name="chevron-right" /></a>
                @else
                    <span class="{{ $disabled }} px-2.5"><span class="hidden sm:inline">Berikutnya</span><x-ui.icon name="chevron-right" /></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
