<?php

use SleepingOwl\Admin\Navigation\Page;

// Default check access logic
AdminNavigation::setAccessLogic(function(Page $page) {
    return auth()->user()->isAdmin();
});

return [
    [
        'title' => __('lang.admin.dashboard'),
        'icon'  => 'fas fa-tachometer-alt',
        'url'   => url('admin'),
        'priority' => 100
    ],
    [
        'title' => __('lang.admin.users'),
        'icon'  => 'fas fa-users',
        'url'   => url('admin/users'),
        'priority' => 200
    ],
    [
    'title' => 'Użytkownicy LDAP',
    'icon'  => 'fas fa-users-cog',
    'url'   => url('admin/ldap/users'),
    'priority' => 350
    ],
    [
        'title' => 'Grupy LDAP',
        'icon'  => 'fas fa-layer-group',
        'url'   => url('admin/ldap/organizational-units'),
        'priority' => 360
    ]
];
