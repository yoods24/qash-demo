<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Collection;
use Livewire\Component;

class BookMenu extends Component
{
    public array $categories = [];
    public array $pages = [];
    public array $categoryPageIndex = [];
    public array $categoryPageRanges = [];
    public ?int $activeCategoryId = null;
    public int $activeGlobalPageIndex = 0;

    public function mount(): void
    {
        $categoryCollection = Category::with([
            'products' => fn ($query) => $query->orderBy('name'),
        ])->orderBy('name')->get();

        $this->categories = $categoryCollection->map(fn ($category) => [
            'id' => (int) $category->id,
            'name' => $category->name,
        ])->values()->all();

        $this->buildPages($categoryCollection);
    }

    private function buildPages(Collection $categories): void
    {
        $pages = [];
        $categoryPageIndex = [];
        $categoryPageRanges = [];
        $globalIndex = 0;

        foreach ($categories as $category) {
            $categoryId = (int) $category->id;
            $categoryPageIndex[$categoryId] = null;
            $categoryPageRanges[$categoryId] = [
                'start' => null,
                'end' => null,
                'total' => 0,
            ];

            $products = $category->products->sortBy('name')->values();
            if ($products->isEmpty()) {
                continue;
            }

            $chunks = $products->chunk(8)->values();
            $totalPages = $chunks->count();
            $startIndex = $globalIndex;

            foreach ($chunks as $chunkIndex => $chunk) {
                $pages[] = [
                    'category_id' => $categoryId,
                    'category_name' => $category->name,
                    'page_number' => $chunkIndex + 1,
                    'page_total' => $totalPages,
                    'products' => $chunk->map(fn ($product) => [
                        'id' => (int) $product->id,
                        'name' => $product->name,
                        'alternate_name' => $product->alternate_name,
                        'description' => $product->description,
                        'price' => (float) $product->price,
                        'prep_time' => $this->formatPrepTime($product->estimated_seconds),
                    ])->values()->all(),
                ];
                $globalIndex++;
            }

            $categoryPageIndex[$categoryId] = $startIndex;
            $categoryPageRanges[$categoryId] = [
                'start' => $startIndex,
                'end' => $globalIndex - 1,
                'total' => $totalPages,
            ];
        }

        $this->pages = $pages;
        $this->categoryPageIndex = $categoryPageIndex;
        $this->categoryPageRanges = $categoryPageRanges;

        $firstPage = $pages[0] ?? null;
        $this->activeCategoryId = $firstPage['category_id'] ?? ($this->categories[0]['id'] ?? null);
        $this->activeGlobalPageIndex = $firstPage ? 0 : 0;
    }

    private function formatPrepTime(?int $seconds): ?string
    {
        if ($seconds === null) {
            return null;
        }

        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = (int) ceil($seconds / 60);

        return $minutes . 'm';
    }

    public function goToCategory(int $categoryId): void
    {
        $this->activeCategoryId = $categoryId;
        $pageIndex = $this->categoryPageIndex[$categoryId] ?? null;

        if ($pageIndex === null) {
            return;
        }

        $this->activeGlobalPageIndex = $pageIndex;

        $this->dispatch('goToCategory', componentId: $this->getId(), pageIndex: $pageIndex);
    }

    public function updateActivePage(int $pageIndex): void
    {
        $maxIndex = max(count($this->pages) - 1, 0);
        $pageIndex = max(0, min($pageIndex, $maxIndex));
        $page = $this->pages[$pageIndex] ?? null;

        if (! $page) {
            return;
        }

        $this->activeGlobalPageIndex = $pageIndex;
        $this->activeCategoryId = $page['category_id'] ?? null;
    }

    public function render()
    {
        return view('livewire.book-menu');
    }
}
