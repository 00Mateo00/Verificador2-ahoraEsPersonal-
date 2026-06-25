@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex justify-center">
        <div class="flex items-center gap-2">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="cursor-not-allowed opacity-50" aria-disabled="true" aria-label="Anterior">
                    &lsaquo;
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:click.prevent="previousPage('{{ $paginator->getPageName() }}')" rel="prev" aria-label="Anterior">
                    &lsaquo;
                </a>
            @endif

            {{-- Elementos de paginación --}}
            @foreach ($elements as $element)
                {{-- Separador "..." --}}
                @if (is_string($element))
                    <span class="cursor-default">{{ $element }}</span>
                @endif

                {{-- Array de enlaces --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" wire:click.prevent="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" aria-label="Ir a la página {{ $page }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:click.prevent="nextPage('{{ $paginator->getPageName() }}')" rel="next" aria-label="Siguiente">
                    &rsaquo;
                </a>
            @else
                <span class="cursor-not-allowed opacity-50" aria-disabled="true" aria-label="Siguiente">
                    &rsaquo;
                </span>
            @endif
        </div>
    </nav>
@endif