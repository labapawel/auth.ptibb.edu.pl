<?php

return [
    'default' => env('ADLDAP_DEFAULT_CONNECTION', 'default'),
    'connections' => [
        'default' => [
            'hosts'    => explode(' ', env('LDAP_HOSTS', '127.0.0.1')),
            'base_dn'  => env('LDAP_BASE_DN', 'dc=example,dc=com'),
            'username' => env('LDAP_USERNAME', 'cn=admin,dc=example,dc=com'),
            'password' => env('LDAP_PASSWORD', 'secret'),
            'port'     => env('LDAP_PORT', 389),
            'use_ssl'  => env('LDAP_USE_SSL', false),
            'use_tls'  => env('LDAP_USE_TLS', false),
            'options'  => [
                'logging' => env('LDAP_LOGGING', false),
                'auto_connect' => env('LDAP_AUTO_CONNECT', true),
                'timeout' => env('LDAP_TIMEOUT', 5),
                'follow_referrals' => env('LDAP_FOLLOW_REFERRALS', false),
                'account_prefix' => env('LDAP_ACCOUNT_PREFIX', ''),
                'account_suffix' => env('LDAP_ACCOUNT_SUFFIX', ''),
            ],
        ],
    ],
];