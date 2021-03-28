<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $user = [

            'name' => 'Admin User',
            'email' => 'superuser@email.com',
            'password' => bcrypt('admin123$')

        ];

        User::create($user);

    }
}
