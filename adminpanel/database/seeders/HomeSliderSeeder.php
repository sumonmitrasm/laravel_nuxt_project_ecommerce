<?php

namespace Database\Seeders;

use App\Models\HomeSlider;
use Illuminate\Database\Seeder;

class HomeSliderSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['title'=>'Built for your everyday.','eyebrow'=>'New season · smart living','description'=>'Considered technology and modern essentials, selected for a smarter life.','offer_label'=>'From','offer_text'=>'৳6,990','offer_note'=>'Free delivery','button_text'=>'Shop collection','button_url'=>'/shop','background_color'=>'#f3f5e9','position'=>1],
            ['title'=>'Power your next big idea.','eyebrow'=>'Trade-in offer · save 25%','description'=>'Fast laptops, crisp displays and accessories built to keep up with you.','offer_label'=>'Save up to','offer_text'=>'25%','offer_note'=>'Official warranty','button_text'=>'Explore technology','button_url'=>'/shop','background_color'=>'#eef3f7','position'=>2],
            ['title'=>'Turn up every moment.','eyebrow'=>'Sound that moves you','description'=>'Premium wireless audio with all-day comfort and incredible clarity.','offer_label'=>'Special price','offer_text'=>'৳8,490','offer_note'=>'40-hour battery','button_text'=>'Shop audio','button_url'=>'/shop','background_color'=>'#fff6ef','position'=>3],
        ];
        foreach ($rows as $row) HomeSlider::updateOrCreate(['title'=>$row['title']], [...$row,'status'=>true]);
    }
}
