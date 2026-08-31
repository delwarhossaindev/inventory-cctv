<?php

/*
 | Company profile used on printed documents (invoice, quotation, reports).
 | Edit these values to rebrand the printed output.
 */

return [
    'name' => 'JM INTERNATIONAL',
    'address' => 'A/36, New Super Market, Baitul Mokarram, Dhaka-1000',
    'phones' => '01971904993, 01898798803, 01898798804',
    'email' => 'jminternationalcctv@gmail.com',

    // Logo file inside the public/ directory.
    'logo' => 'jm.png',

    // Note printed near the bottom of invoices.
    'nb' => 'N.B No Warranty for Broken, Sticker removes, Burning & Physical Damage',

    // Branch list printed in the document footer.
    'branches' => [
        ['title' => 'Head Office', 'lines' => ['59/3/2, Purana Paltan', 'Dhaka-1000']],
        ['title' => 'Branch-1', 'lines' => ['C/11, New Super Market', 'Baitul Mokarram, Dhaka-1000']],
        ['title' => 'Branch-2', 'lines' => ['B/33, New Super Market', 'Baitul Mokarram, Dhaka-1000']],
        ['title' => 'Branch-3', 'lines' => ['A/36, New Super Market', 'Baitul Mokarram, Dhaka-1000']],
    ],

    'currency' => '৳',
];
