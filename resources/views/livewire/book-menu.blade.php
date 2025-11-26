@php
    $componentDomId = 'menuBook-' . $this->getId();
@endphp

<div class="menu-book-wrapper py-2" data-lenis-prevent>
    <div class="container-xxl no-p">
        <div class="menu-book-category-panel mb-5">
            <div class="d-flex flex-nowrap gap-3 overflow-auto pb-3">
                @forelse($categories as $category)
                    @php
                        $range = $categoryPageRanges[$category['id']] ?? ['total' => 0, 'start' => null, 'end' => null];
                        $hasPages = ($range['total'] ?? 0) > 0;
                    @endphp
                    <button type="button"
                            wire:click="goToCategory({{ $category['id'] }})"
                            @disabled(! $hasPages)
                            @class([
                                'menu-book-category-card',
                                'active' => $activeCategoryId === $category['id'],
                                'disabled' => ! $hasPages,
                            ])
                            style="--panel-bg: url('https://picsum.photos/300/200?random={{ $category['id'] }}')"
                            data-first-page="{{ $categoryPageIndex[$category['id']] ?? '' }}">
                        <span class="category-name">{{ $category['name'] }}</span>
                        <span class="category-subtle">
                            {{ $hasPages ? ($range['total'] . ' page' . ($range['total'] > 1 ? 's' : '')) : 'Coming Soon' }}
                        </span>
                    </button>
                @empty
                    <div class="text-muted">No categories yet.</div>
                @endforelse
            </div>
        </div>

        <div class="menu-book-body">
            @if (empty($pages))
                <div class="menu-book-empty text-center py-5">
                    <div class="fw-semibold text-muted">This menu is being plated. Please check back soon.</div>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 px-3">
                        <button
                            type="button"
                            class="btn btn-outline-dark btn-sm"
                            data-menu-book-nav="prev"
                            data-component-id="{{ $this->getId() }}"
                        >
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline-dark btn-sm"
                            data-menu-book-nav="next"
                            data-component-id="{{ $this->getId() }}"
                        >
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                </div>
                <div class="menu-book-scroll">
                    <div class="premium-book flip-book" id="{{ $componentDomId }}" wire:ignore>
                        @foreach ($pages as $index => $page)
                            <div class="page menu-page" data-page-index="{{ $index }}" data-category="{{ $page['category_id'] }}">
                                <div class="menu-page-inner h-100 d-flex flex-column">
                                    <div class="menu-page-header d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <div class="text-uppercase text-muted small tracking-wide">{{ $page['category_name'] }}</div>
                                            <div class="muted-subtitle fst-italic">Curated Menu</div>
                                        </div>
                                        <span class="menu-page-badge">Page {{ $page['page_number'] }} / {{ $page['page_total'] }}</span>
                                    </div>
                                    <h2 class="section-title mb-4">Curated Picks</h2>

                                    <div class="row g-4 row-cols-1 row-cols-lg-2">
                                        @foreach ($page['products'] as $product)
                                            @php
                                                $formattedPrice = 'Rp ' . number_format($product['price'], 0, ',', '.');
                                            @endphp
                                            <div class="col">
                                                <div class="menu-item-row">
                                                    <div class="menu-item-heading">
                                                        <span class="menu-item-title">{{ $product['name'] }}</span>
                                                        <span class="menu-item-leader"></span>
                                                        <div class="menu-item-price-inline">
                                                            <span class="menu-item-price">{{ $formattedPrice }}</span>
                                                            @if(!empty($product['prep_time']))
                                                                <span class="menu-item-prep">{{ $product['prep_time'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @if(!empty($product['alternate_name']))
                                                        <div class="menu-item-alt text-muted fst-italic">{{ $product['alternate_name'] }}</div>
                                                    @endif
                                                    <p class="menu-item-desc text-muted">{{ $product['description'] }}</p>
                                                    <div class="menu-item-price-mobile">
                                                        <span class="menu-item-price">{{ $formattedPrice }}</span>
                                                        @if(!empty($product['prep_time']))
                                                            <span class="menu-item-prep">{{ $product['prep_time'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="menu-page-footer mt-auto pt-4 text-uppercase small fw-semibold text-muted">
                                        <span>Warmly Served</span>
                                        <span>Flip to Explore</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@script
<script>
$js('menuBookInit', () => {
    const componentId = @js($this->getId());
    const bookId = @js($componentDomId);
    if (typeof Livewire === 'undefined') {
        return;
    }

    const boot = () => {
        const bookEl = document.getElementById(bookId);
        const LivewireComponent = Livewire.find(componentId);
        if (!bookEl || !LivewireComponent) {
            return;
        }

        window.__menuBooks = window.__menuBooks || {};
        if (window.__menuBooks[componentId]) {
            return;
        }

        const isSingle = true;
        const computeWidth = () => {
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;
            if (viewportWidth && viewportWidth <= 768) {
                return Math.max(360, viewportWidth - 32);
            }
            const safeWidth = Math.max(viewportWidth, 720);
            return Math.max(700, Math.min(safeWidth - 80, 880));
        };
        const calcHeight = () => {
            let maxHeight = 0;
            bookEl.querySelectorAll('.menu-page-inner').forEach((page) => {
                maxHeight = Math.max(maxHeight, page.scrollHeight);
            });
            const paddingAllowance = isSingle ? 160 : 120;
            const computed = maxHeight > 0 ? maxHeight + paddingAllowance : (isSingle ? 960 : 680);
            const minHeight = isSingle ? 900 : 660;
            const maxHeightAllowed = 2200;
            return Math.min(Math.max(computed, minHeight), maxHeightAllowed);
        };

        const width = computeWidth();
        const pageFlip = new St.PageFlip(bookEl, {
            width,
            height: calcHeight(),
            size: 'stretch',
            minWidth: Math.min(700, width),
            maxWidth: 1200,
            minHeight: 400,
            maxHeight: 2500,
            showCover: false,
            drawShadow: true,
            maxShadowOpacity: 0.25,
            mobileScrollSupport: true,
            usePortrait: true,
            flippingTime: 900,
            startPage: @js($activeGlobalPageIndex ?? 0),
        });

        pageFlip.loadFromHTML(bookEl.querySelectorAll('.menu-page'));

        pageFlip.on('flip', (event) => {
            if (!event || typeof event.data !== 'number') {
                return;
            }
            LivewireComponent.call('updateActivePage', event.data);
        });

        window.__menuBooks[componentId] = pageFlip;
    };

    const attemptInit = (tries = 0) => {
        if (window.St && window.St.PageFlip) {
            boot();
            return;
        }
        if (tries >= 20) {
            console.warn('PageFlip CDN has not finished loading.');
            return;
        }
        setTimeout(() => attemptInit(tries + 1), 200 + tries * 25);
    };

    attemptInit();
});

document.addEventListener('livewire:init', () => {
    $js.menuBookInit();
});
document.addEventListener('livewire:navigated', () => {
    $js.menuBookInit();
});
window.addEventListener('load', () => {
    $js.menuBookInit();
});

$wire.on('goToCategory', (payload) => {
    const componentId = @js($this->getId());
    if (!payload || payload.componentId !== componentId) {
        return;
    }
    if (!window.__menuBooks || !window.__menuBooks[componentId]) {
        return;
    }
    const pageIndex = Number(payload.pageIndex ?? 0);
    window.__menuBooks[componentId].turnToPage(pageIndex);
});

document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-menu-book-nav]');
    if (!btn) {
        return;
    }
    const dir = btn.getAttribute('data-menu-book-nav');
    const componentId = btn.getAttribute('data-component-id');
    const book = window.__menuBooks?.[componentId];
    if (!book) {
        return;
    }

    const turn = (offset) => {
        if (typeof book.getCurrentPageIndex === 'function' && typeof book.turnToPage === 'function') {
            const nextIndex = Math.max(0, (book.getCurrentPageIndex() ?? 0) + offset);
            book.turnToPage(nextIndex);
            return true;
        }
        return false;
    };

    if (dir === 'next') {
        if (typeof book.flipNext === 'function') {
            book.flipNext();
            return;
        }
        turn(1);
        return;
    }

    if (dir === 'prev') {
        if (typeof book.flipPrev === 'function') {
            book.flipPrev();
            return;
        }
        turn(-1);
    }
});
</script>
@endscript
