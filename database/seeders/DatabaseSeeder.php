<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Gret',
            'email' => 'gret@admin.com',
            'password' => bcrypt('admin'),
        ]);

        $this->call(SampleGraphicsSeeder::class);
    }
}