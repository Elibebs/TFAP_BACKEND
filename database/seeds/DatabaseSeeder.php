<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SystemUser;
use App\Post;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed the default permissions
        if ($this->command->confirm('Reset all default permissions. This may revoke all permissions assigned to user [y|N]', false)) {
            $this->command->info('Default Permissions removed.');
            Permission::truncate();
        }
        $permissions = Permission::defaultPermissions();

        foreach ($permissions as $perms) {
            Permission::firstOrCreate(['name' => $perms]);
        }

        $this->command->info('Default Permissions added.');

        // Confirm roles needed
        if ($this->command->confirm('Create Roles for Systemuser, default is super admin? [y|N]', true)) {

            // Ask for roles from input
            $input_roles = $this->command->ask('Enter roles in comma separate format.', 'SuperAdmin');

            // Explode roles
            $roles_array = explode(',', $input_roles);

            // add roles
            foreach($roles_array as $role) {
                $role = Role::firstOrCreate(['name' => trim($role)]);

                if( $role->name == 'SuperAdmin' ) {
                    // assign all permissions
                    $role->syncPermissions(Permission::all());

                    // create super admin user
                    $this->createSuperAdminUser($role);
                    $this->command->info('Super Admin granted all the permissions');
                } else {
                    // for others by default only read access
                    $role->syncPermissions(Permission::where('name', 'LIKE', 'view_%')->get());
                }
            }

            $this->command->info('Roles ' . $input_roles . ' added successfully');
        } else {
            Role::firstOrCreate(['name' => 'User']);
            $this->command->info('Added only default user role.');
        }


        // now lets seed some posts for demo
        // factory(Post::class, 30)->create();
        // $this->command->info('Some Posts data seeded.');
        // $this->command->warn('All done :)');
    }

    /**
     * Create a user with given role
     *
     * @param $role
     */
    private function createSuperAdminUser($role)
    {
        $user = SystemUser::create([
            'name' => 'Tema First',
            'status' => 'ENABLED',
            'email' => 'eli.cyriaano@ijikod.com',
            'password' => Hash::make('secret77'),
        ]);
        $user->assignRole($role->name);
        $user->save();

        $this->command->info('Here is your super admin details to login:');
        $this->command->warn($user->email);
        $this->command->warn($user->password);
    }
}
