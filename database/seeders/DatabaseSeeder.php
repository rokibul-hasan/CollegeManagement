<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Roles, permissions and the super admin account are created by migrations,
     * so production only needs the website content below.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            User::query()->firstOrCreate(['email' => 'admin@college.test'], [
                'name' => 'অ্যাডমিন',
                'password' => 'password',
            ])->syncRoles(['admin']);
        }

        $this->call([
            MenuSeeder::class,
            NoticeSeeder::class,
            StaffSeeder::class,
            CampusPhotoSeeder::class,
        ]);
    }
}
