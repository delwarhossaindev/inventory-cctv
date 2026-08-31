<?php

/*
 | Admin sidebar menu definition.
 | Each entry is a section: a `label` (null = no header / always-open top group),
 | a `collapsible` flag, and a list of `items`.
 | Each item: label, icon (bootstrap-icons class), route name, optional `active`
 | route pattern (defaults to the route name), and optional `can` permission.
 | Items the user lacks permission for are hidden; empty sections disappear.
 */

return [
    [
        'label' => null,
        'collapsible' => false,
        'items' => [
            ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
            ['label' => 'POS', 'icon' => 'bi-cart-check', 'route' => 'admin.pos.index', 'active' => 'admin.pos.index', 'can' => 'access pos'],
            ['label' => 'POS 2', 'icon' => 'bi-cart-check-fill', 'route' => 'admin.pos.pos2', 'active' => 'admin.pos.pos2', 'can' => 'access pos'],
        ],
    ],
    [
        'label' => 'Catalog',
        'collapsible' => true,
        'items' => [
            ['label' => 'Products', 'icon' => 'bi-box', 'route' => 'admin.products.index', 'active' => 'admin.products.*', 'can' => 'view products'],
            ['label' => 'Categories', 'icon' => 'bi-diagram-3', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'can' => 'manage categories'],
            ['label' => 'Stock', 'icon' => 'bi-clipboard-data', 'route' => 'admin.stock.index', 'active' => 'admin.stock.*', 'can' => 'view stock'],
            ['label' => 'Batches', 'icon' => 'bi-upc-scan', 'route' => 'admin.batches.index', 'active' => 'admin.batches.*', 'can' => 'view stock'],
        ],
    ],
    [
        'label' => 'Transactions',
        'collapsible' => true,
        'items' => [
            ['label' => 'Quotations', 'icon' => 'bi-file-earmark-text', 'route' => 'admin.quotations.index', 'active' => 'admin.quotations.*', 'can' => 'access pos'],
            ['label' => 'Purchases', 'icon' => 'bi-bag-plus', 'route' => 'admin.purchases.index', 'active' => 'admin.purchases.*', 'can' => 'view purchases'],
            ['label' => 'Sales', 'icon' => 'bi-receipt', 'route' => 'admin.sales.index', 'active' => 'admin.sales.*', 'can' => 'view sales'],
            ['label' => 'Returns', 'icon' => 'bi-arrow-return-left', 'route' => 'admin.returns.index', 'active' => 'admin.returns.*', 'can' => 'view sales'],
            ['label' => 'Payments', 'icon' => 'bi-cash-stack', 'route' => 'admin.payments.index', 'active' => 'admin.payments.*', 'can' => 'view sales'],
            ['label' => 'Expenses', 'icon' => 'bi-wallet2', 'route' => 'admin.expenses.index', 'active' => 'admin.expenses.*', 'can' => 'manage expenses'],
            ['label' => 'Installments', 'icon' => 'bi-calendar2-check', 'route' => 'admin.installments.index', 'active' => 'admin.installments.*', 'can' => 'view sales'],
            ['label' => 'Cash Register', 'icon' => 'bi-safe', 'route' => 'admin.cash-register.index', 'active' => 'admin.cash-register.*', 'can' => 'access pos'],
        ],
    ],
    [
        'label' => 'Reports',
        'collapsible' => true,
        'items' => [
            ['label' => 'Sales & Profit', 'icon' => 'bi-graph-up', 'route' => 'admin.reports.sales', 'can' => 'view reports'],
            ['label' => 'Daily Summary', 'icon' => 'bi-calendar3', 'route' => 'admin.reports.daily', 'can' => 'view reports'],
            ['label' => 'Top Products', 'icon' => 'bi-trophy', 'route' => 'admin.reports.products', 'can' => 'view reports'],
            ['label' => 'Purchases', 'icon' => 'bi-bag-check', 'route' => 'admin.reports.purchases', 'can' => 'view reports'],
            ['label' => 'Stock Valuation', 'icon' => 'bi-cash-stack', 'route' => 'admin.reports.stock', 'can' => 'view reports'],
            ['label' => 'Profit & Loss', 'icon' => 'bi-bar-chart-line', 'route' => 'admin.reports.profit-loss', 'can' => 'view reports'],
        ],
    ],
    [
        'label' => 'Accounting',
        'collapsible' => true,
        'items' => [
            ['label' => 'Day Book', 'icon' => 'bi-journal-bookmark', 'route' => 'admin.accounting.day-book', 'active' => 'admin.accounting.*', 'can' => 'view reports'],
            ['label' => 'Trial Balance', 'icon' => 'bi-list-columns-reverse', 'route' => 'admin.accounting.trial-balance', 'active' => 'admin.accounting.*', 'can' => 'view reports'],
        ],
    ],
    [
        'label' => 'Contacts',
        'collapsible' => true,
        'items' => [
            ['label' => 'Suppliers', 'icon' => 'bi-truck', 'route' => 'admin.suppliers.index', 'active' => 'admin.suppliers.*', 'can' => 'manage suppliers'],
            ['label' => 'Customers', 'icon' => 'bi-people', 'route' => 'admin.customers.index', 'active' => 'admin.customers.*', 'can' => 'manage customers'],
        ],
    ],
    [
        'label' => 'Settings',
        'collapsible' => true,
        'items' => [
            ['label' => 'Unit', 'icon' => 'bi-rulers', 'route' => 'admin.units.index', 'active' => 'admin.units.*', 'can' => 'manage categories'],
            ['label' => 'Main Category', 'icon' => 'bi-diagram-3', 'route' => 'admin.categories.index', 'params' => ['level' => 1], 'active' => 'admin.categories.index', 'can' => 'manage categories'],
            ['label' => 'Category', 'icon' => 'bi-diagram-2', 'route' => 'admin.categories.index', 'params' => ['level' => 2], 'active' => 'admin.categories.index', 'can' => 'manage categories'],
            ['label' => 'Sub Category', 'icon' => 'bi-diagram-3-fill', 'route' => 'admin.categories.index', 'params' => ['level' => 3], 'active' => 'admin.categories.index', 'can' => 'manage categories'],
        ],
    ],
    [
        'label' => 'Access Control',
        'collapsible' => true,
        'items' => [
            ['label' => 'Users', 'icon' => 'bi-person-gear', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'can' => 'manage users'],
            ['label' => 'Roles', 'icon' => 'bi-shield-lock', 'route' => 'admin.roles.index', 'active' => 'admin.roles.*', 'can' => 'manage roles'],
            ['label' => 'Permissions', 'icon' => 'bi-key', 'route' => 'admin.permissions.index', 'active' => 'admin.permissions.*', 'can' => 'manage permissions'],
        ],
    ],
    [
        'label' => 'Administration',
        'collapsible' => true,
        'items' => [
            ['label' => 'Branches', 'icon' => 'bi-building', 'route' => 'admin.branches.index', 'can' => 'manage settings'],
            ['label' => 'Login History', 'icon' => 'bi-person-check', 'route' => 'admin.login-history.index', 'can' => 'manage users'],
            ['label' => 'Activity Log', 'icon' => 'bi-clock-history', 'route' => 'admin.activity-log.index', 'can' => 'manage settings'],
            ['label' => 'Settings', 'icon' => 'bi-gear', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*', 'can' => 'manage settings'],
            ['label' => 'User Manual', 'icon' => 'bi-book', 'route' => 'admin.manual.index', 'active' => 'admin.manual.*'],
        ],
    ],
];
