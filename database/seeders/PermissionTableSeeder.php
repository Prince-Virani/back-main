<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{

    public function run(): void
    {
        $permissions = [

            // ************************ dashboard ************************
            'view-dashboard',

            // ************************ analytics ************************
            'view-analytics',

            // ************************ user managment ************************
            // User permissions
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            // Role & Permission management
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',

            // ************************ Manage Pages ************************
            'view-pages',
            'add-pages',
            'edit-pages',
            'edit-status',

            // ************************ Manage Themes ************************
            'add-theme',
            'edit-theme',
            'view-theme',
            'edit-theme-status',

            // ************************ Manage Categories ************************
            'add-categories',
            'edit-categories',
            'view-categories',
            'delete-categories',

            // ************************ Manage Common Pages ************************
            'add-common-pages',
            'edit-common-pages',
            'view-common-pages',
            'delete-common-pages',

            // ************************ Manage Websites ************************
            'add-websites',
            'edit-websites',
            'view-websites',
            'delete-websites',
            'purge-websites',
            'refresh-cache-websites',
            'view-cache',
            'refresh-cache',
            'change-ads-live',
            'change-analytics',
            'change-pause-cloudflare',

            // ************************ Manage Applications ************************
            'add-application',
            'edit-application',
            'view-application',
            'fetch-application',

            // ************************ Manage Google Ads Settings ************************
            'add-google-ads-settings',
            'view-google-ads-settings',
            'edit-google-ads-settings',

            // ************************ Ad Unit ************************
            'add-ad-unit',
            'edit-ad-unit',
            'view-ad-unit',
            'delete-ad-unit',
            'edit-ad-unit-status',
            'edit-ad-unit-lazy',

            // ************************ Tag Manager ************************
            'add-tag-manager',
            'edit-tag-manager',
            'view-tag-manager',
            'delete-tag-manager',

            // ************************ Ads Management ************************
            'add-ads-management',
            'edit-ads-management',
            'view-ads-management',
            'delete-ads-management',

            // ************************ Ad Positions ************************
            'add-ads-position',
            'edit-ads-position',
            'view-ads-position',
            'delete-ads-position',

            // ************************ Manage Verticals ************************
            'add-manage-verticals',
            'edit-manage-verticals',
            'view-manage-verticals',
            'delete-manage-verticals',

            // ************************ Inactive API Keys ************************
            'edit-inactive-api-keys',
            'view-inactive-api-keys',

            // ************************ Native Ads ************************
            'add-native-ads',
            'view-native-ads',
            'edit-native-ads',

            // ************************ Manage Firestore App Settings ************************
            'add-firestore-app-setting',
            'edit-firestore-app-setting',
            'view-firestore-app-setting',
            'change-firestore-app-setting',

            // ************************ Links Manager ************************
            'add-link-manager',
            'edit-link-manager',
            'view-link-manager',
            'delete-link-manager',
            'change-status-link-manager',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin']);

        $admin->syncPermissions($permissions);
    }
}
