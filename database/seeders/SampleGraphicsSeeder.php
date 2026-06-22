<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\LowerThird;
use App\Models\Scripture;
use App\Models\Song;
use Illuminate\Database\Seeder;

class SampleGraphicsSeeder extends Seeder
{
    public function run(): void
    {
        LowerThird::insert([
            ["name" => "Pastor John Doe", "subtitle" => "Guest Speaker", "template" => "slide_left", "created_at" => now(), "updated_at" => now()],
            ["name" => "Elder Mark Smith", "subtitle" => "Associate Pastor", "template" => "slide_right", "created_at" => now(), "updated_at" => now()],
            ["name" => "Special Music", "subtitle" => "Worship Team", "template" => "fade", "created_at" => now(), "updated_at" => now()],
            ["name" => "Welcome", "subtitle" => "Church Greeters", "template" => "slide_left", "created_at" => now(), "updated_at" => now()],
            ["name" => "Prayer Ministry", "subtitle" => null, "template" => "zoom", "created_at" => now(), "updated_at" => now()],
        ]);

        $amazingGrace = Song::create(["title" => "Amazing Grace", "artist" => "John Newton", "category" => "Hymn"]);
        $amazingGrace->slides()->createMany([
            ["slide_order" => 0, "content" => "Amazing grace! How sweet the sound\nThat saved a wretch like me!"],
            ["slide_order" => 1, "content" => "I once was lost, but now am found;\nWas blind, but now I see."],
            ["slide_order" => 2, "content" => "Through many dangers, toils and snares\nI have already come;"],
            ["slide_order" => 3, "content" => "'Tis grace hath brought me safe thus far,\nAnd grace will lead me home."],
        ]);

        $howGreat = Song::create(["title" => "How Great Thou Art", "artist" => "Carl Boberg", "category" => "Hymn"]);
        $howGreat->slides()->createMany([
            ["slide_order" => 0, "content" => "O Lord my God, when I in awesome wonder\nConsider all the worlds Thy hands have made"],
            ["slide_order" => 1, "content" => "I see the stars, I hear the rolling thunder\nThy power throughout the universe displayed"],
            ["slide_order" => 2, "content" => "Then sings my soul, my Saviour God, to Thee\nHow great Thou art, how great Thou art!"],
        ]);

        $iSurrender = Song::create(["title" => "I Surrender All", "artist" => "Judson W. Van DeVenter", "category" => "Hymn"]);
        $iSurrender->slides()->createMany([
            ["slide_order" => 0, "content" => "All to Jesus I surrender\nAll to Him I freely give"],
            ["slide_order" => 1, "content" => "I will ever love and trust Him\nIn His presence daily live"],
            ["slide_order" => 2, "content" => "I surrender all, I surrender all\nAll to Thee, my blessed Saviour\nI surrender all"],
        ]);

        Scripture::insert([
            ["reference" => "John 3:16", "text" => "For God so loved the world, that He gave His only begotten Son, that whoever believes in Him shall not perish, but have eternal life.", "translation" => "NASB", "created_at" => now(), "updated_at" => now()],
            ["reference" => "Psalm 23:1-4", "text" => "The Lord is my shepherd, I shall not want. He makes me lie down in green pastures; He leads me beside quiet waters. He restores my soul; He guides me in the paths of righteousness for His name's sake.", "translation" => "NASB", "created_at" => now(), "updated_at" => now()],
            ["reference" => "Philippians 4:13", "text" => "I can do all things through Him who strengthens me.", "translation" => "NASB", "created_at" => now(), "updated_at" => now()],
            ["reference" => "Jeremiah 29:11", "text" => "For I know the plans that I have for you, declares the Lord, plans for welfare and not for calamity, to give you a future and a hope.", "translation" => "NASB", "created_at" => now(), "updated_at" => now()],
        ]);

        Announcement::insert([
            ["title" => "Welcome to Our Service", "content" => "We are glad you are here! Please fill out a connection card and join us for fellowship after the service.", "image" => null, "created_at" => now(), "updated_at" => now()],
            ["title" => "Youth Night", "content" => "Every Friday at 7 PM. All teens are welcome!", "image" => null, "created_at" => now(), "updated_at" => now()],
            ["title" => "Bible Study", "content" => "Wednesday mornings at 10 AM. Join us as we study the book of Acts.", "image" => null, "created_at" => now(), "updated_at" => now()],
        ]);
    }
}
