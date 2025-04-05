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
        'title' => __('lang.admin.vpn'),
        'icon'  => 'fas fa-shield-alt',
        'url'   => url('admin/vpn'),
        'priority' => 300
    ],
    
    [
        'title' => __('lang.admin.pc'),
        'icon'  => 'fas fa-desktop',
        'url'   => url('admin/pc'),
        'priority' => 400
    ],
    
    [
        'title' => __('lang.admin.tasks'), 
        'icon'  => 'fas fa-tasks',
        'url'   => 'https://zadania.t24.ovh', 
        'priority' => 500, 
    ],

];
