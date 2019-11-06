<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        # create role
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'admin_sub']);
        Role::create(['name' => 'agent']);
        Role::create(['name' => 'agent_sub']);

        # assign role to admin
        $admin = User::where('agent_id', 0)->where('type', 1)->first();
        $admin->assignRole('admin');
    }
}
