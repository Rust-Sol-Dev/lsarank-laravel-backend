<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');

        $newUser = User::create([
            'name' => Str::random(10),
            'email' => Str::random(10) . '@mail.com',
            'google_id'=> '845734589435',
            'password' => encrypt('123456dummy'),
            'active' => 1
        ]);

        $newUser->assignRole('Customer');


    }
}
