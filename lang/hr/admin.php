<?php

declare(strict_types=1);

return [
    'title' => 'Planovi',

    'plans' => [
        'title' => 'Definicije planova',
        'description' => 'Read-only pregled planova, kljuceva i entitlements vrijednosti sinkroniziranih iz config/plans.php.',
    ],

    'empty' => [
        'title' => 'Nema planova',
        'description' => 'Pokreni plans seeder ili plans:sync komandu nakon deploya migracija.',
    ],

    'actions' => [
        'details' => 'Detalji',
        'back_to_plans' => 'Natrag na planove',
    ],

    'labels' => [
        'active' => 'Aktivan',
        'disabled' => 'Iskljuceno',
        'enabled' => 'Ukljuceno',
        'inactive' => 'Neaktivan',
        'initial_sync' => 'Prvi sync',
        'key' => 'Kljuc',
        'monthly_price' => 'Mjesecna cijena',
        'fair_use' => 'Fair use',
        'status' => 'Status',
        'type' => 'Tip',
        'unlimited' => 'Bez ogranicenja',
        'value' => 'Vrijednost',
        'yearly_price' => 'Godisnja cijena',
    ],
];
