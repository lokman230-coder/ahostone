<?php
/**
 * Migration Bridge Pro v1.3.0
 * Harici modül: Kaynak Sistem/WiseCP/Blesta tarama, seçmeli import, fiyat ve domain uzantı fiyat aktarımı.
 */
return [
    'slug' => 'migration-bridge-pro',
    'name' => 'Migration Bridge Pro',
    'version' => '1.3.0',
    'menu' => [
        'admin' => [
            'title' => 'Migration Bridge Pro',
            'icon' => 'shuffle',
            'route' => '/admin/modules/migration-bridge-pro'
        ]
    ],
    'providers' => ['Kaynak Sistem', 'WiseCP', 'Blesta'],
    'features' => [
        'selective_import',
        'product_pricing_import',
        'domain_extension_pricing_import',
        'currency_mapping',
        'conflict_resolver',
        'dry_run_preview'
    ]
];
