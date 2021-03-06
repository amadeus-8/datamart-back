<?php

use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_admin = Role::where('name', 'admin')->first();

        $employee = new User();
        $employee->name = 'admin';
        $employee->email = env('ADMIN_LOGIN');
        $employee->password = bcrypt(env('ADMIN_PASSWORD'));
        $employee->save();
        $employee->roles()->attach($role_admin);

    }
}
