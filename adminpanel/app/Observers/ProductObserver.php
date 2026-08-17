<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\ShopFilterCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Product $product): void { $this->invalidate(); }
    public function deleted(Product $product): void { $this->invalidate(); }

    private function invalidate(): void
    {
        Cache::forget('api.sections-with-categories.v5');
        ShopFilterCache::invalidate();
    }
}
