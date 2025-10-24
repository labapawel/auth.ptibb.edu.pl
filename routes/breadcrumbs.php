<?php

// Home
Breadcrumbs::for('home', function ($trail) {
    $trail->push('Home', route('admin.dashboard'));
});

// Admin Dashboard
Breadcrumbs::for('admin.dashboard', function ($trail) {
    $trail->parent('home');
    $trail->push('Dashboard', route('admin.dashboard'));
});

// LDAP Users
Breadcrumbs::for('admin.ldap.users.index', function ($trail) {
    $trail->parent('admin.dashboard');
    $trail->push('Użytkownicy LDAP', route('admin.ldap.users.index'));
});

// LDAP Users Create
Breadcrumbs::for('admin.ldap.users.create', function ($trail) {
    $trail->parent('admin.ldap.users.index');
    $trail->push('Dodaj użytkownika');
});

// LDAP Users Delete
Breadcrumbs::for('admin.ldap.users.delete', function ($trail, $user) {
    $trail->parent('admin.ldap.users.index');
    $trail->push('Usuń użytkownika');
});