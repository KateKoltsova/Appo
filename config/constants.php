<?php
return [
    'db' => [
        'default_master' => [
            'firstname' => 'Default',
            'lastname' => 'Master',
            'password' => 'default',
        ],
        'roles' => [
            'master' => 'master',
            'client' => 'client',
            'admin' => 'admin',
            'super-admin' => 'super-admin'
        ],
        'permissions' => [
            'instances' => [
                'roles',
                'permissions',
                'users',
                'services',
                'prices',
                'schedules',
                'galleries',
                'appointments',
                'carts',
                'orders'
            ],
            'actions' => [
                'create-own',
                'create-other',
                'read-all',
                'read-own',
                'read-other',
                'update-own',
                'update-other',
                'delete-own',
                'delete-other'
            ]
        ],
        'services' => [
            ['category' => 'Lashes',
                'title' => [
                    'Eyelash extension classic',
                    'Eyelash extension 2D-3D',
                    'Eyelash extension 4D-5D',
                    'Eyelash extension volumes',
                    'Color eyelash extension (any volumes)',
                    'Eyelash coloring',
                    'Eyelash lamination (coloring include)',
                    'Only removing lashes',
                ]
            ],
            ['category' => 'Nails',
                'title' => [
                    'Manicure',
                    'Mono gel polish (manicure include)',
                    'Color gel polish (manicure include)',
                    'Nails extension 1-3 length (manicure include)',
                    'Nails extension more 4 length (manicure include)',
                    'Color nail extension',
                    'Nail design',
                    'Only removing nail polish',
                ]
            ],
            ['category' => 'Brows',
                'title' => [
                    'Brow coloring',
                    'Brow modeling',
                    'Brow lamination (coloring include)',
                    'Brow permanent',
                    'Brow permanent correction',
                    'Brow permanent refresh'
                ]
            ],
        ],
        'payment' => [
            'full' => ['full'],
            'prepayment' => ['prepayment', 100],
        ],
        'status' => [
            'available' => 'available',
            'unavailable' => 'unavailable'
        ],
        'blocked' => [
            'minutes' => 15
        ],
        'diff_between_services' => [
            'minutes' => 60
        ],
        'cart_limit' => [
            'items' => 3
        ]
    ]
];
