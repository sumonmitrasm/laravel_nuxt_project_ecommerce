<?php

namespace App\Observers;

use App\Models\Section;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class SectionObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Section $section): void { $this->invalidate(); }
    public function deleted(Section $section): void { $this->invalidate(); }

    private function invalidate(): void
    {
        Cache::forget('api.sections-with-categories.v5');
        Cache::forget('admin.category-form.sections.v3');
    }
}
