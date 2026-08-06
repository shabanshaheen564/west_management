<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view containers','create containers','edit containers','delete containers',
            'view vehicles','create vehicles','edit vehicles','delete vehicles',
            'view drivers','create drivers','edit drivers','delete drivers',
            'view routes','create routes','edit routes','delete routes','optimize routes',
            'view dumpsites','create dumpsites','edit dumpsites','delete dumpsites',
            'view complaints','create complaints','edit complaints','delete complaints','assign complaints',
            'view reports','generate reports',
            'view map','spatial analysis',
            'view users','create users','edit users','delete users',
            'view roles','create roles','edit roles','delete roles',
            'view settings','edit settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $supervisorRole->syncPermissions([
            'view containers','edit containers',
            'view vehicles','view drivers',
            'view routes','create routes','edit routes','optimize routes',
            'view dumpsites',
            'view complaints','edit complaints','assign complaints',
            'view reports','generate reports',
            'view map','spatial analysis',
        ]);

        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $driverRole->syncPermissions(['view containers','view routes','view map']);

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions(['view containers','view map','create complaints','view complaints']);
    }
}