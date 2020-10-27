<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'Customer', 'guard_name' => 'api']);
        Role::create(['name' => 'Manager', 'guard_name' => 'api']);
    }
}
