<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as Role;
Use App\User;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
            try {
                    $user = new User([
                    'full_legal_name' => 'Ankit Arora Manager',
                    'email' => 'ankit+manager@gmail.com',
                    'password' => bcrypt('Pass@2020')
                ]);
                    $user->save();
                    $user->assignRole('Manager'); //: Assigns role to a user
                    DB::commit();                                
                    
                } catch (\Exception $e) {
                    DB::rollback();                    
                }
    }
}
