@if ($paginator->hasPages())
<nav aria-label="Pagination Navigation" style="display:flex; justify-content:center; padding:4px 0;">
    <ul style="display:inline-flex; flex-wrap:wrap; align-items:center; gap:4px; list-style:none; margin:0; padding:0;">

        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li style="display:inline-flex;">
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:600;color:#94a3b8;background:#f8fafc;border:1.5px solid #f1f5f9;cursor:not-allowed;user-select:none;">
                    <i class="fas fa-chevron-left" style="font-size:10px;"></i>
                </span>
            </li>
        @else
            <li style="display:inline-flex;">
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                   style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:600;color:#334155;background:#fff;border:1.5px solid #e2e8f0;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);transition:all 0.15s ease;"
                   onmouseover="this.style.borderColor='#b91c1c';this.style.color='#b91c1c';this.style.transform='translateY(-1px)';"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#334155';this.style.transform='none';">
                    <i class="fas fa-chevron-left" style="font-size:10px;"></i>
                </a>
            </li>
        @endif

        {{-- Page Number Links --}}
        @if (method_exists($paginator, 'lastPage'))
            @php
                $current = $paginator->currentPage();
                $last    = $paginator->lastPage();
                $start   = max(1, $current - 2);
                $end     = min($last, $current + 2);
            @endphp

            {{-- First page + ellipsis --}}
            @if ($start > 1)
                <li style="display:inline-flex;">
                    <a href="{{ $paginator->url(1) }}"
                       style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:600;color:#334155;background:#fff;border:1.5px solid #e2e8f0;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);">1</a>
                </li>
                @if ($start > 2)
                    <li style="display:inline-flex;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 8px;border-radius:8px;font-size:13px;font-weight:600;color:#94a3b8;background:#f8fafc;border:1.5px solid #f1f5f9;">…</span>
                    </li>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $current)
                    <li style="display:inline-flex;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:700;color:#fff;background:#b91c1c;border:1.5px solid #b91c1c;box-shadow:0 3px 8px rgba(185,28,28,0.3);cursor:default;">{{ $page }}</span>
                    </li>
                @else
                    <li style="display:inline-flex;">
                        <a href="{{ $paginator->url($page) }}"
                           style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:600;color:#334155;background:#fff;border:1.5px solid #e2e8f0;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);"
                           onmouseover="this.style.borderColor='#b91c1c';this.style.color='#b91c1c';this.style.background='#fff7f7';"
                           onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#334155';this.style.background='#fff';">{{ $page }}</a>
                    </li>
                @endif
            @endfor

            {{-- Last page + ellipsis --}}
            @if ($end < $last)
                @if ($end < $last - 1)
                    <li style="display:inline-flex;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 8px;border-radius:8px;font-size:13px;font-weight:600;color:#94a3b8;background:#f8fafc;border:1.5px solid #f1f5f9;">…</span>
                    </li>
                @endif
                <li style="display:inline-flex;">
                    <a href="{{ $paginator->url($last) }}"
                       style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:600;color:#334155;background:#fff;border:1.5px solid #e2e8f0;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);">{{ $last }}</a>
                </li>
            @endif
        @else
            {{-- SimplePaginator (no lastPage): just show current page --}}
            <li style="display:inline-flex;">
                <span style="display:inline-flex;align-items:center;justify-content:center;height:36px;padding:0 14px;border-radius:8px;font-size:12.5px;font-weight:600;color:#475569;background:#f8fafc;border:1.5px solid #e2e8f0;">
                    Page {{ $paginator->currentPage() }}
                </span>
            </li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li style="display:inline-flex;">
                <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                   style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:600;color:#334155;background:#fff;border:1.5px solid #e2e8f0;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,0.04);transition:all 0.15s ease;"
                   onmouseover="this.style.borderColor='#b91c1c';this.style.color='#b91c1c';this.style.transform='translateY(-1px)';"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#334155';this.style.transform='none';">
                    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                </a>
            </li>
        @else
            <li style="display:inline-flex;">
                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:8px;font-size:13px;font-weight:600;color:#94a3b8;background:#f8fafc;border:1.5px solid #f1f5f9;cursor:not-allowed;user-select:none;">
                    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                </span>
            </li>
        @endif

    </ul>
</nav>
@endif
