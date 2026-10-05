<?php

namespace App\Observers;

use App\Support\ContentCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

// Clear only after a successful commit; rolled-back edits keep their cache.
class ContentCacheObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        // Stock and purchase-cost changes do not affect the cached price range
        // or attribute counts. Checkout must not empty these caches each time.
        if (class_basename($model) === 'ProductVariant' && $model->exists && !$model->wasRecentlyCreated) {
            if (!$model->wasChanged(['product_id', 'price', 'status'])) {
                return;
            }
        }
        $this->clear($model);
    }
    public function deleted(Model $model): void { $this->clear($model); }
    public function restored(Model $model): void { $this->clear($model); }

    private function clear(Model $model): void
    {
        $groups = match (class_basename($model)) {
            'Setting' => ['settings'],
            'HomeSlider' => ['sliders'],
            'AboutPage' => ['about'],
            'ShippingMethod' => ['shipping'],
            'Division', 'District', 'Upazila' => ['locations'],
            'Section', 'Category', 'Product' => ['menu', 'shop-filters'],
            'Brand', 'ProductVariant', 'ProductAttributeDefinition', 'ProductAttributeValue' => ['shop-filters'],
            default => [],
        };

        foreach ($groups as $group) {
            ContentCache::forget($group);
        }
    }
}
