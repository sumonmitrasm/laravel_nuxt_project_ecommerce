<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    public function run(): void
    {
        AboutPage::query()->updateOrCreate(['id' => 1], [
            'hero_title' => 'Better products.',
            'hero_highlight' => 'Better everyday living.',
            'hero_text' => 'We bring thoughtfully selected technology, fashion and home essentials together in one dependable shopping experience.',
            'intro_title' => 'Shopping should feel simple, useful and trustworthy.',
            'intro_text' => 'We started with a clear idea: online shopping should help people make confident choices without unnecessary complexity. That means useful products, clear information and support that is easy to reach.',
            'promise_title' => 'With you before, during and after every purchase.',
            'promise_text' => 'A great store does more than deliver products. We make it easy to get product information, delivery assistance and return support whenever you need it.',
            'cta_title' => 'Find something made for your everyday.',
            'return_days' => 7,
            'value_1_title' => 'Quality first',
            'value_1_text' => 'We select dependable products that offer genuine value and everyday usefulness.',
            'value_2_title' => 'Customer focused',
            'value_2_text' => 'Every decision starts with making shopping simpler, clearer and more supportive.',
            'value_3_title' => 'Built on trust',
            'value_3_text' => 'Straightforward pricing, secure payments and honest communication at every step.',
            'value_4_title' => 'Local understanding',
            'value_4_text' => 'A shopping experience designed around the needs of customers across Bangladesh.',
            'meta_title' => 'About Us',
            'meta_description' => 'Learn about our story and our commitment to a better online shopping experience.',
        ]);
    }
}