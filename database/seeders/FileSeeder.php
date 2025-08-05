<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Hidehalo\Nanoid\Client;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        foreach (range(1, 20) as $i) {
            File::create([
                'uuid' => (new Client())->generateId(16),
                'filename' => $faker->words(rand(2, 5), true) . '.' . $faker->fileExtension(),
                'file_location_name' => '',
                'parent_uuid' => '0', // or set a valid UUID if you want nesting
                'owner_id' => 1, // Replace with an actual user ID or randomize if needed
                'file_size' => $faker->numberBetween(1000, 10000000),
                'is_folder' => $faker->boolean(),
            ]);
        }
    }
}
