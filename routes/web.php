<?php
use Spatie\Permission\Models\Role as Role;
use Spatie\Permission\Models\Permission as Permission;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    Role::create(['name' => 'Customer', 'guard_name' => 'api']);
    Role::create(['name' => 'Manager', 'guard_name' => 'api']);
    return view('welcome');
});
