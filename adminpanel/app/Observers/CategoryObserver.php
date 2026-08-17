<?php

namespace App\Observers;

use App\Models\Category;
use App\Support\ShopFilterCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class CategoryObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Category $category): void { $this->invalidate(); }
    public function deleted(Category $category): void { $this->invalidate(); }

    private function invalidate(): void
    {
        Cache::forget('api.sections-with-categories.v5');
        Cache::forget('admin.category-form.sections.v3');
        Cache::forget('admin.category-form.parents.v3');
        ShopFilterCache::invalidate();
    }
}
