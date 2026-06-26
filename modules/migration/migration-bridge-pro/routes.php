<?php
// Ahost One Module Center uyumluluk rotaları.
return [
    ['GET',  '/admin/modules/migration-bridge-pro',                    'MigrationBridgeProController@index'],
    ['POST', '/admin/modules/migration-bridge-pro/connect',            'MigrationBridgeProController@connect'],
    ['POST', '/admin/modules/migration-bridge-pro/scan',               'MigrationBridgeProController@scan'],
    ['GET',  '/admin/modules/migration-bridge-pro/scan/{id}',          'MigrationBridgeProController@preview'],
    ['POST', '/admin/modules/migration-bridge-pro/import',             'MigrationBridgeProController@import'],
    ['POST', '/admin/modules/migration-bridge-pro/upload',             'MigrationBridgeProController@upload'],
    ['GET',  '/admin/modules/migration-bridge-pro/logs',               'MigrationBridgeProController@logs']
];
