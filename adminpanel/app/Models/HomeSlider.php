<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSlider extends Model
{
    protected $fillable = ['eyebrow','title','description','offer_label','offer_text','offer_note','button_text','button_url','image','background_color','position','status'];
    protected $appends = ['image_url'];
    protected function casts(): array { return ['position' => 'integer', 'status' => 'boolean']; }
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('admin/home_sliders/'.basename($this->image)) : null;
    }
}
