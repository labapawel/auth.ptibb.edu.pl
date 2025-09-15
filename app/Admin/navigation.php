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
        'url'   => url('admin')
    ],
    [
        'title' => __('lang.admin.users'),
        'icon'  => 'fas fa-users',
        'url'   => url('admin/users')
    ],
    [
        'title' => 'Użytkownicy LDAP',
        'icon'  => 'fas fa-users-cog',
        'url'   => url('admin/ldap/users')
    ],
    [
        'title' => 'Grupy LDAP',
        'icon'  => 'fas fa-layer-group',
        'url'   => url('admin/ldap/groups')
    ],
    // Wyloguj - dodane na końcu aby było na dole menu
    [
        'title' => 'Wyloguj',
        'icon'  => 'fas fa-sign-out-alt',
        'url'   => url('admin/logout'),
        'attributes' => [
            'style' => 'border-top: 1px solid #e5e7eb; margin-top: 10px; padding-top: 10px;'
        ]
    ]
];
