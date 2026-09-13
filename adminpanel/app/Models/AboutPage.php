<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    protected $fillable = [
        'hero_title', 'hero_highlight', 'hero_text', 'intro_title', 'intro_text',
        'promise_title', 'promise_text', 'cta_title', 'return_days',
        'value_1_title', 'value_1_text', 'value_2_title', 'value_2_text',
        'value_3_title', 'value_3_text', 'value_4_title', 'value_4_text',
        'meta_title', 'meta_description',
    ];
}