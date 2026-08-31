<?php

/*
 | Central catalog of permissions, grouped by module.
 | Used by the seeder to create permissions and by the Roles UI to render them.
 | The roles map defines which permissions each default role receives.
 */

return [

    'groups' => [
        'Products' => ['view products', 'create products', 'edit products', 'delete products'],
        'Categories' => ['manage categories'],
        'Stock' => ['view stock', 'adjust stock'],
        'Purchases' => ['view purchases', 'create purchases', 'delete purchases'],
        'Sales' => ['view sales', 'delete sales'],
        'POS' => ['access pos'],
        'Suppliers' => ['manage suppliers'],
        'Customers' => ['manage customers'],
        'Users' => ['manage users'],
        'Roles' => ['manage roles', 'manage permissions'],
        'Reports' => ['view reports'],
        'Expenses' => ['manage expenses'],
        'Settings' => ['manage settings'],
    ],

    // 'Super Admin' is intentionally omitted — it bypasses all checks via Gate::before.
    'roles' => [
        'Manager' => [
            'view reports',
            'view products', 'create products', 'edit products', 'delete products',
            'manage categories',
            'view stock', 'adjust stock',
            'view purchases', 'create purchases', 'delete purchases',
            'view sales', 'delete sales',
            'access pos',
            'manage suppliers', 'manage customers',
            'manage expenses', 'manage settings',
        ],
        'Storekeeper' => [
            'view products', 'create products', 'edit products',
            'manage categories',
            'view stock', 'adjust stock',
            'view purchases', 'create purchases',
            'manage suppliers',
        ],
        'Salesperson' => [
            'view products',
            'access pos',
            'view sales',
            'manage customers',
        ],
    ],

];
