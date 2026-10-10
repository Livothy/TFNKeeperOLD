<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Sidebar Links
    |--------------------------------------------------------------------------
    |
    | Admin panel sidebar links.
    | Add links here to have them show up in the admin panel.
    | Users that do not have the listed power will not be able to
    | view the links in that section.
    |
    */

    'Admin'      => [
        'power' => 'admin',
        'links' => [
            [
                'name' => 'User Ranks',
                'url'  => 'admin/users/ranks',
            ],
            [
                'name' => 'Rank Rewards',
                'url'  => 'admin/rank-rewards',
            ],
            [
                'name' => 'Admin Logs',
                'url'  => 'admin/logs',
            ],
            [
                'name' => 'Staff Reward Settings',
                'url'  => 'admin/staff-reward-settings',
            ],
        ],
    ],
    'Reports'    => [
        'power' => 'manage_reports',
        'links' => [
            [
                'name' => 'Report Queue',
                'url'  => 'admin/reports/pending',
            ],
        ],
    ],
    'News' => [
        'power' => 'manage_news',
        'links' => [
            [
                'name' => 'News',
                'url'  => 'admin/news',
            ],
        ],
    ],
    'Sales' => [
        'power' => 'manage_sales',
        'links' => [
            [
                'name' => 'Sales',
                'url'  => 'admin/sales',
            ],
        ],
    ],
    'Pages'       => [
        'power' => 'edit_pages',
        'links' => [
            [
                'name' => 'Pages',
                'url'  => 'admin/pages',
            ],
        ],
    ],
    'User_Ads' => [
        'power' => 'manage_user_ads',
        'links' => [
            [
                'name' => 'User Ads',
                'url'  => 'admin/user_ads',
            ],
        ],
    ],
    'Users'      => [
        'power' => 'edit_user_info',
        'links' => [
            [
                'name' => 'User Index',
                'url'  => 'admin/users',
            ],
            [
                'name' => 'Invitation Keys',
                'url'  => 'admin/invitations',
            ],
        ],
    ],
    'Queues'     => [
        'power' => 'manage_submissions',
        'links' => [
            [
                'name' => 'Gallery Submissions',
                'url'  => 'admin/gallery/submissions',
            ],
            [
                'name' => 'Gallery Currency Awards',
                'url'  => 'admin/gallery/currency',
            ],
            [
                'name' => 'Prompt Submissions',
                'url'  => 'admin/submissions',
            ],
            [
                'name' => 'Claim Submissions',
                'url'  => 'admin/claims',
            ],
        ],
    ],
    'Grants'     => [
        'power' => 'edit_inventories',
        'links' => [
            [
                'name' => 'Currency Grants',
                'url'  => 'admin/grants/user-currency',
            ],
            [
                'name' => 'Item Grants',
                'url'  => 'admin/grants/items',
            ],
            [
                'name' => 'Award Grants',
                'url'  => 'admin/grants/awards',
            ],
            [
                'name' => 'Border Grants',
                'url'  => 'admin/grants/borders',
            ],
        ],
    ],
    'Masterlist' => [
        'power' => 'manage_characters',
        'links' => [
            [
                'name' => 'Create Character',
                'url'  => 'admin/masterlist/create-character',
            ],
            [
                'name' => 'Create MYO Slot',
                'url'  => 'admin/masterlist/create-myo',
            ],
            [
                'name' => 'Character Transfers',
                'url'  => 'admin/masterlist/transfers/incoming',
            ],
            [
                'name' => 'Character Trades',
                'url'  => 'admin/masterlist/trades/incoming',
            ],
            [
                'name' => 'Design Updates',
                'url'  => 'admin/design-approvals/pending',
            ],
            [
                'name' => 'MYO Approvals',
                'url'  => 'admin/myo-approvals/pending',
            ],
        ],
    ],
    'Data'       => [
        'power' => 'edit_data',
        'links' => [
            [
                'name' => 'Galleries',
                'url'  => 'admin/data/galleries',
            ],
            [
                'name' => 'Award Categories',
                'url'  => 'admin/data/award-categories',
            ],
            [
                'name' => 'Awards',
                'url'  => 'admin/data/awards',
            ],
            [
                'name' => 'Character Categories',
                'url'  => 'admin/data/character-categories',
            ],
            [
                'name' => 'Sub Masterlists',
                'url'  => 'admin/data/sublists',
            ],
            [
                'name' => 'Rarities',
                'url'  => 'admin/data/rarities',
            ],
            [
                'name' => 'Species',
                'url'  => 'admin/data/species',
            ],
            [
                'name' => 'Subtypes',
                'url'  => 'admin/data/subtypes',
            ],
            [
                'name' => 'Traits',
                'url'  => 'admin/data/traits',
            ],
            [
                'name' => 'Markings',
                'url'  => 'admin/data/markings',
            ],
            [
                'name' => 'Carriers',
                'url'  => 'admin/data/carriers',
            ],
            [
                'name' => 'Bases',
                'url'  => 'admin/data/bases',
            ],
            [
                'name' => 'Character Titles',
                'url' => 'admin/data/character-titles'
            ],
            [
                'name' => 'Shops',
                'url'  => 'admin/data/shops',
            ],
            [
                'name' => 'Dailies',
                'url'  => 'admin/data/dailies',
            ],
            [
                'name' => 'Currencies',
                'url'  => 'admin/data/currencies',
            ],
            [
                'name' => 'Prompts',
                'url'  => 'admin/data/prompts',
            ],
            [
                'name' => 'Loot Tables',
                'url'  => 'admin/data/loot-tables',
            ],
            [
                'name' => 'Items',
                'url'  => 'admin/data/items',
            ],
            [
                'name' => 'Library',
                'url'  => 'admin/data/volumes',
            ],
            [
                'name' => 'User Borders',
                'url'  => 'admin/data/borders',
            ],
            [
                'name' => 'Referrals',
                'url'  => 'admin/data/referrals',
            ],
            [
                'name' => 'Periodic Reward Defaults',
                'url'  => 'admin/data/periodic-defaults',
            ],
            [
                'name' => 'Fetch Quests',
                'url'  => 'admin/data/fetch-quests',
            ],
            [
                'name' => 'Forms & Polls',
                'url'  => 'admin/forms',
            ],
            [
                'name' => 'Random Generators',
                'url'  => 'admin/data/random',
            ],
            [
                'name' => 'Criteria Rewards',
                'url'  => 'admin/data/criteria',
            ],
            [
                'name' => 'Transformations',
                'url'  => 'admin/data/transformations',
            ],
        ],
    ],
    'Raids'       => [
        'power' => 'edit_data',
        'links' => [
            [
                'name' => 'Raids',
                'url'  => 'admin/data/raids',
            ],
            [
                'name' => 'Raid Bosses',
                'url'  => 'admin/data/raid-bosses',
            ],
        ],
    ],
    'WorldExpansion' => [
        'power' => 'manage_world',
        'links' => [
            [
                'name' => 'Concepts',
                'url'  => 'admin/world/concepts',
            ],
            [
                'name' => 'Fauna',
                'url'  => 'admin/world/faunas',
            ],
            [
                'name' => 'Flora',
                'url'  => 'admin/world/floras',
            ],
            [
                'name' => ' Events',
                'url'  => 'admin/world/events',
            ],
            [
                'name' => ' Figures',
                'url'  => 'admin/world/figures',
            ],
            [
                'name' => 'Factions',
                'url'  => 'admin/world/factions',
            ],
            [
                'name' => 'Glossary',
                'url'  => 'admin/world/glossary',
            ],
            [
                'name' => 'Locations',
                'url'  => 'admin/world/locations',
            ],
        ],
    ],
    'Raffles'    => [
        'power' => 'manage_raffles',
        'links' => [
            [
                'name' => 'Raffles',
                'url'  => 'admin/raffles',
            ],
        ],
    ],
    'Settings'   => [
        'power' => 'edit_site_settings',
        'links' => [
            [
                'name' => 'Site Settings',
                'url'  => 'admin/settings',
            ],
            [
                'name' => 'Site Images',
                'url'  => 'admin/images',
            ],
            [
                'name' => 'File Manager',
                'url'  => 'admin/files',
            ],
            [
                'name' => 'Ajax Search Settings',
                'url'  => 'admin/ajax-search',
            ],
        ],
    ],
];
