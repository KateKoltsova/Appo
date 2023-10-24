<?php
return [
    'db' => [
        'roles' => [
            'master' => 'master',
            'client' => 'client',
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
                ]
            ],
        ],
        'payment' => [
            ['name' => 'full'],
            ['name' => 'prepayment',
                'value' => 100,
            ]
        ],
        'status' => [
            'available' => 'available',
            'unavailable' => 'unavailable'
        ]
    ]
];
