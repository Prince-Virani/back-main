<?php

return [

  /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

  'menu' => [
    [
      'text' => 'Navigation',
      'is_header' => true
    ],
    [
      'url' => 'dashboard',
      'icon' => 'fa fa-laptop',
      'text' => 'Dashboard',
      'permission' => 'view-dashboard'
    ],
    [
      'url' => 'users.index',
      'icon' => 'fa fa-users',
      'text' => 'User',
      'permission' => 'view-users'
    ],
    [
      'url' => 'roles.index',
      'icon' => 'fa fa-user',
      'text' => 'Role',
      'permission' => 'view-roles'
    ],
    [
      'url' => 'analytics.index',
      'icon' => 'fa fa-chart-pie',
      'text' => 'Google Analytics',
      'permission' => 'view-analytics'
    ],
    [
      'text' => 'Components',
      'is_header' => true
    ],
    [
      'url' => 'pages.index',
      'icon' => 'fa fa-file-alt',
      'text' => 'Manage Pages',
      'permission' => 'view-pages'
    ],
    [
      'url' => 'themes.index',
      'icon' => 'fa fa-heart',
      'text' => 'Manage Themes',
      'permission' => 'view-theme'
    ],
    [
      'url' => 'categories.index',
      'icon' => 'fa fa-wallet',
      'text' => 'Manage Categories',
      'permission' => 'view-categories'
    ],
    [
      'url' => 'Commonpages.index',
      'icon' => 'fa fa-file-alt',
      'text' => 'Manage Common Pages',
      'permission' => 'view-common-pages'
    ],
    [
      'url' => 'websites.index',
      'icon' => 'fa fa-globe',
      'text' => 'Manage Websites',
      'permission' => 'view-websites'
    ],
    [
      'url' => 'applications.index',
      'icon' => 'fa-brands fa-google-play',
      'text' => 'Manage Applications',
      'permission' => 'view-application'
    ],
    [
      'text' => 'Ad Managment',
      'is_header' => true
    ],
    [
      'icon' => 'fa fa-globe',
      'text' => 'Website Management',
      'children' => [
        [
          'url'  => 'google-settings.index',
          'icon' => 'fa-solid fa-rectangle-ad',
          'text' => 'Manage Google Ads Settings',
          'permission' => 'view-google-ads-settings'
        ],
        [
          'url' => 'ad-units.index',
          'icon' => 'fa-solid fa-rectangle-ad',
          'text' => 'Manage Ad Units',
          'permission' => 'view-ad-unit'
        ],
        [
          'url' => 'tagmanagers.index',
          'icon' => 'fa-solid fa-tag',
          'text' => 'Manage Tag Manager',
          'permission' => 'view-tag-manager'
        ],
        [
          'url' => 'adstxt.index',
          'icon' => 'fa-solid fa-text-slash',
          'text' => 'Manage Ads Txt',
          'permission' => 'view-ads-management'
        ],
        [
          'url' => 'adpositions.index',
          'icon' => 'fa-solid fa-plus',
          'text' => 'Manage Ad Position',
          'permission' => 'view-ads-position'
        ],
        [
          'url' => 'verticals.index',
          'icon' => 'fa fa-bullhorn',
          'text' => 'Manage Verticals',
          'permission' => 'view-manage-verticals'
        ]
      ]

    ],
    [
      'icon' => 'fa-brands fa-google-play',
      'text' => 'Application Management',
      'children' => [
        [
          'url'  => 'api_keys.index',
          'icon' => 'fa-solid fa-rectangle-ad',
          'text' => 'Manage Api Keys',
          'permission' => 'view-inactive-api-keys'
        ],
        [
          'url'  => 'native_ads.index',
          'icon' => 'fa-solid fa-rectangle-ad',
          'text' => 'Manage Native Ads',
          'permission' => 'edit-native-ads'
        ],
        [
          'url'  => 'firestore-app-settings.index',
          'icon' => 'fa-solid fa-rectangle-ad',
          'text' => 'Manage FireStore Settings',
          'permission' => 'view-firestore-app-setting'
        ],
        [
          'url' => 'links.index',
          'icon' => 'fa-solid fa-rectangle-ad',
          'text' => 'Manage Custom Urls',
          'permission' => 'view-link-manager'
        ],
      ]

    ],
  ]
];
