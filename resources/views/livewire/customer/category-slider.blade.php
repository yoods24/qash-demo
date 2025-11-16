@php
    /** @var \Illuminate\Support\Collection|\App\Models\Category[] $categories */
    $categories = $categories ?? collect();
@endphp

@if($categories->isEmpty())
    <div class="alert alert-light mb-0" role="alert">
        Menu is coming soon. Please check back later.
    </div>
@else
    <div class="menu-category-slider-root">
        <div id="category-slider" class="splide menu-category-splide" wire:ignore>
            <div class="splide__track">
                <ul class="splide__list">
                    @foreach($categories as $category)
                        @php
                            $isActive = $activeCategoryId === $category->id;
                            $image = $category->image_url ?: "https://picsum.photos/seed/{$category->id}/600/400";
                            $bgUrl = "url('{$image}')";
                        @endphp
                        <li class="splide__slide">
                            <button
                                type="button"
                                class="menu-category-card {{ $isActive ? 'active' : '' }}"
                                data-category-id="{{ $category->id }}"
                                style="--category-bg: {{ $bgUrl }};"
                                wire:click="selectCategory({{ $category->id }})"
                            >
                                <span class="menu-category-card-title">
                                    {{ strtoupper($category->name) }}
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
