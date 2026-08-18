<?php

namespace App\Observers;

use App\Models\ProductAttributeDefinition;
use App\Models\ProductAttributeValue;
use App\Support\ShopFilterCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductAttributeObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(ProductAttributeDefinition|ProductAttributeValue $attribute): void
    {
        ShopFilterCache::invalidate();
    }

    public function deleted(ProductAttributeDefinition|ProductAttributeValue $attribute): void
    {
        ShopFilterCache::invalidate();
    }
}
