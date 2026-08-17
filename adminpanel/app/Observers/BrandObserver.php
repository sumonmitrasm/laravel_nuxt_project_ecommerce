<?php

namespace App\Observers;

use App\Models\Brand;
use App\Support\ShopFilterCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class BrandObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Brand $brand): void { ShopFilterCache::invalidate(); }
    public function deleted(Brand $brand): void { ShopFilterCache::invalidate(); }
}
