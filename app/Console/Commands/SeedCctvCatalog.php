<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeedCctvCatalog extends Command
{
    protected $signature = 'app:seed-cctv-catalog {--fresh : Wipe the existing catalog and every transaction that references it before seeding}';

    protected $description = 'Seed the CCTV shop catalog: category tree, products with Unsplash photos, prices and FIFO stock layers';

    /**
     * Unsplash photo ids grouped by the kind of product they suit.
     * Every id was taken from a real Unsplash search page, so the URLs resolve.
     */
    private const PHOTOS = [
        'dome' => [
            '1643123182527-3bd30840e7ed', '1671038389411-096c80030750', '1705147293087-9e183bf149ef',
            '1614469422872-6e09d2569cc0', '1767059439630-ca3844d07d77', '1769209436053-b2547531fc24',
            '1549109926-58f039549485',
        ],
        'bullet' => [
            '1496368077930-c1e31b4e5b44', '1530151928300-3864d0e5d178', '1565591452825-67d6b7df1d47',
            '1585206031650-9e9a7c87dcfe', '1495714096525-285e85481946', '1481597262637-0545b18186ea',
            '1759771618528-8179a49ae81a',
        ],
        'ptz' => [
            '1589935447067-5531094415d1', '1618482914248-29272d021005', '1528312635006-8ea0bc49ec63',
            '1672073311074-f60c4a5e7b92', '1639242689301-1d51c12f595d', '1758461265346-db7c11c0cde7',
            '1762953007649-8ea70115059a',
        ],
        'wifi' => [
            '1730967693281-c114d9930860', '1617897711385-df9c86b7dfe3', '1520697830682-bbb6e85e2b0b',
            '1510849911856-cdc9335e5597', '1563920443079-783e5c786b83', '1529265895721-65945a176cff',
            '1557597774-9d273605dfa9', '1590613607026-15c463e30ca5', '1609234153285-78b715b9dfd7',
            '1650214962171-8198a113621a', '1633194883650-df448a10d554',
        ],
        'recorder' => [
            '1605810230434-7631ac76ec81', '1708807472445-d33589e6b090', '1506399309177-3b43e99fead2',
            '1695668548342-c0c1ad479aee', '1639066648921-82d4500abf1a', '1667264501379-c1537934c7ab',
            '1702478475268-aa8ef54c084e', '1698668975271-2ba9a323be6b',
        ],
        'hdd' => [
            '1531492746076-161ca9bcad58', '1597852074816-d933c7d2b988', '1601737487795-dab272f52420',
            '1581725645226-92ad3b4c16d8', '1589995186011-a7b485edc4bf', '1613826591816-1b80e944fc2d',
            '1602493054445-4a0b4fa7fdd6', '1613070541337-b40942ee6527',
        ],
        'card' => [
            '1499336969384-ebe67b79faa8', '1629265339808-9b5849d71bbb', '1632251350035-7f750a5973b6',
        ],
        'switch' => [
            '1750711731797-25c3f2551ff8', '1783683783819-e6cb806bba69', '1691435828932-911a7801adfb',
            '1663932210347-164a05ed0ccd',
        ],
        'router' => [
            '1606904825846-647eb07f5be2', '1516044734145-07ca8eef8731', '1745847768408-b7b83796cae6',
            '1606420187127-dae7c868fa7a', '1750712263185-edde9f359e33', '1750711158632-5273ec9b9b86',
        ],
        'cable' => [
            '1578016980868-197203ff4b02', '1574405345169-f45c7d66480e', '1607631755187-298a3f9a640a',
            '1544197150-b99a580bb7a8', '1683322499436-f4383dd59f5a', '1531668383211-64743e924c66',
            '1624965439943-09e0238644e2', '1681321570365-df53da7dbaa2', '1729549223893-b340db51e577',
            '1558494949-ef010cbdcc31',
        ],
        'power' => [
            '1756043827116-5764e6d23d85', '1770417999483-e5a313b33468', '1770417999317-64fb254c9e3a',
            '1781331534854-c75885f01c01', '1517320069935-381614f8c1e5',
        ],
        'monitor' => [
            '1611648694931-1aeda329f9da', '1570485071395-29b575ea3b4e', '1534972195531-d756b9bfa9f2',
            '1666771410140-0573b232426e', '1547658718-1cdaa0852790',
        ],
        'access' => [
            '1601602364439-591bc32e912e', '1585079374502-415f8516dcc3', '1776329255945-15212b50cd7f',
            '1682944525540-874749c83b9d', '1637597384329-f2dd4cdf61e6',
        ],
        'intercom' => [
            '1572613090232-224fa91e5394', '1770197247933-63e02c014cb7', '1528817466667-942353411fee',
            '1646753002835-74296cb27a83',
        ],
        'rack' => [
            '1506399558188-acca6f8cbf41', '1584169417032-d34e8d805e8b', '1687300172792-68a13c4e149a',
            '1558494949-ef010cbdcc31',
        ],
    ];

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->wipe();
        }

        $this->seedUnits();
        $this->seedSuppliers();

        $rows = $this->catalog();
        $seeded = 0;

        DB::transaction(function () use ($rows, &$seeded) {
            foreach ($rows as $row) {
                $mainId = $this->category($row['main'], Category::LEVEL_MAIN, null);
                $catId = $this->category($row['cat'], Category::LEVEL_CATEGORY, $mainId);
                $subId = $this->category($row['sub'] ?? '', Category::LEVEL_SUB, $catId);

                $product = Product::updateOrCreate(
                    ['slug' => Str::slug($row['name'])],
                    [
                        'name' => $row['name'],
                        'model' => $row['model'],
                        'sku' => $row['sku'],
                        'main_category_id' => $mainId,
                        'category_id' => $catId,
                        'sub_category_id' => $subId,
                        'status' => 'active',
                        'unit' => $row['unit'],
                        'warranty_days' => $row['warranty'],
                        'purchase_price' => $row['cost'],
                        'sale_price' => $row['price'],
                        'alert_quantity' => $row['alert'],
                        'image_url' => $this->photo($row['img'], $row['pic']),
                        'gallery_images' => $this->gallery($row['img'], $row['pic']),
                        'short_description' => $this->paragraphs($row['short']),
                        'description' => $this->paragraphs($row['desc']),
                        'advantages' => $this->bullets($row['plus']),
                        'specifications' => $this->bullets($row['spec']),
                        'meta_title' => $row['name'] . ' | Price in Bangladesh',
                        'meta_description' => Str::limit($row['short'], 155),
                        'faqs' => null,
                    ]
                );

                $product->ensureBarcode();
                $this->stockUp($product, $row['stock']);

                $seeded++;
                $this->line(sprintf('  ✓ %-58s %s', Str::limit($product->name, 56), $product->barcode));
            }
        });

        $this->newLine();
        $this->info("Seeded {$seeded} CCTV products across " . Category::count() . ' categories.');

        return self::SUCCESS;
    }

    /**
     * Drop the old catalog plus everything that points at it, so no row is left orphaned.
     */
    private function wipe(): void
    {
        $tables = [
            'installment_payments', 'installment_plans', 'loyalty_transactions',
            'sale_return_items', 'sale_returns', 'payments', 'sale_items', 'sales',
            'quotation_items', 'quotations', 'purchase_items', 'purchases',
            'stock_movements', 'stock_batches', 'products', 'categories',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
        Schema::enableForeignKeyConstraints();

        $this->warn('Wiped the old catalog and all transactions that referenced it.');
    }

    private function seedUnits(): void
    {
        foreach ([
            ['Piece', 'pcs'], ['Box', 'box'], ['Set', 'set'],
            ['Pack', 'pack'], ['Roll', 'roll'], ['Yard', 'yard'],
        ] as [$name, $short]) {
            Unit::updateOrCreate(['short_name' => $short], ['name' => $name, 'status' => 'active']);
        }
    }

    private function seedSuppliers(): void
    {
        foreach ([
            ['Hikvision Bangladesh', 'Digital Security & Surveillance BD', '01711000101', 'sales@hikvisionbd.com'],
            ['Dahua Technology BD', 'Dahua Authorised Distributor', '01711000102', 'info@dahuabd.com'],
            ['Uniview Bangladesh', 'UNV Surveillance BD Ltd.', '01711000103', 'sales@unvbd.com'],
            ['TP-Link Bangladesh', 'Excel Technologies Ltd.', '01711000104', 'support@tplinkbd.com'],
            ['Smart Technologies (BD) Ltd.', 'Smart Technologies', '01711000105', 'info@smartbd.com'],
        ] as [$name, $company, $phone, $email]) {
            Supplier::updateOrCreate(
                ['name' => $name],
                ['company' => $company, 'phone' => $phone, 'email' => $email, 'status' => 'active']
            );
        }
    }

    /** Idempotently create a category at a level and return its id. */
    private function category(string $name, int $level, ?int $parentId): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        return Category::firstOrCreate(
            ['name' => $name, 'level' => $level, 'parent_id' => $parentId],
            ['slug' => $this->uniqueCategorySlug(Str::slug($name)), 'status' => 'active']
        )->id;
    }

    private function uniqueCategorySlug(string $base): string
    {
        $slug = $base ?: 'category';
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function photo(string $pool, int $index): string
    {
        $ids = self::PHOTOS[$pool];

        return 'https://images.unsplash.com/photo-' . $ids[$index % count($ids)]
            . '?auto=format&fit=crop&w=800&q=80';
    }

    /** Main shot plus the next two photos from the same pool. */
    private function gallery(string $pool, int $index): array
    {
        return [
            $this->photo($pool, $index),
            $this->photo($pool, $index + 1),
            $this->photo($pool, $index + 2),
        ];
    }

    /**
     * The content fields are edited with TinyMCE and printed with {!! !!},
     * so they have to hold HTML — plain newlines would collapse into one line.
     */
    private function paragraphs(string $text): string
    {
        $parts = preg_split('/\n\s*\n/', trim($text));

        return implode('', array_map(fn ($p) => '<p>' . e(trim($p)) . '</p>', $parts));
    }

    private function bullets(array $lines): string
    {
        return '<ul>' . implode('', array_map(fn ($l) => '<li>' . e($l) . '</li>', $lines)) . '</ul>';
    }

    /**
     * Two FIFO cost layers: an older, cheaper opening batch and a newer restock at list cost.
     */
    private function stockUp(Product $product, int $quantity): void
    {
        $product->batches()->delete();
        $product->stockMovements()->delete();
        $product->forceFill(['stock_quantity' => 0])->save();

        $cost = (float) $product->purchase_price;
        $older = round($cost * 0.92 / 5) * 5;
        $opening = (int) ceil($quantity * 0.6);

        $product->stockIn($opening, $older, 'purchase', null, 'Opening batch (older)', now()->subDays(45));
        $product->stockIn($quantity - $opening, $cost, 'purchase', null, 'Restock batch (newer)', now()->subDays(7));
    }

    /**
     * The shop catalog. Prices are BDT: cost = what we pay, price = counter price.
     * img/pic pick a photo from the PHOTOS pools above.
     */
    private function catalog(): array
    {
        $ipDome = ['main' => 'CCTV Camera', 'cat' => 'IP Camera', 'sub' => 'Dome Camera'];
        $ipBullet = ['main' => 'CCTV Camera', 'cat' => 'IP Camera', 'sub' => 'Bullet Camera'];
        $ipPtz = ['main' => 'CCTV Camera', 'cat' => 'IP Camera', 'sub' => 'PTZ Camera'];
        $hdDome = ['main' => 'CCTV Camera', 'cat' => 'HD Analog Camera', 'sub' => 'Dome Camera'];
        $hdBullet = ['main' => 'CCTV Camera', 'cat' => 'HD Analog Camera', 'sub' => 'Bullet Camera'];
        $wifiIn = ['main' => 'CCTV Camera', 'cat' => 'Wi-Fi Camera', 'sub' => 'Indoor Camera'];
        $wifiOut = ['main' => 'CCTV Camera', 'cat' => 'Wi-Fi Camera', 'sub' => 'Outdoor Camera'];
        $nvr = ['main' => 'Recorder', 'cat' => 'NVR', 'sub' => ''];
        $dvr = ['main' => 'Recorder', 'cat' => 'DVR', 'sub' => ''];
        $hddCat = ['main' => 'Storage', 'cat' => 'Surveillance HDD', 'sub' => ''];
        $cardCat = ['main' => 'Storage', 'cat' => 'Memory Card', 'sub' => ''];
        $psu = ['main' => 'Power & Cable', 'cat' => 'Power Supply', 'sub' => ''];
        $cableCat = ['main' => 'Power & Cable', 'cat' => 'Cable', 'sub' => ''];
        $conn = ['main' => 'Power & Cable', 'cat' => 'Connector & Accessories', 'sub' => ''];
        $poe = ['main' => 'Network Device', 'cat' => 'PoE Switch', 'sub' => ''];
        $routerCat = ['main' => 'Network Device', 'cat' => 'Router', 'sub' => ''];
        $attendance = ['main' => 'Access Control', 'cat' => 'Time Attendance', 'sub' => ''];
        $doorPhone = ['main' => 'Access Control', 'cat' => 'Video Door Phone', 'sub' => ''];
        $monitorCat = ['main' => 'Display & Rack', 'cat' => 'Monitor', 'sub' => ''];
        $rackCat = ['main' => 'Display & Rack', 'cat' => 'Rack & Box', 'sub' => ''];

        $rows = [
            // ---------------- IP dome ----------------
            $ipDome + [
                'name' => 'Hikvision DS-2CD1143G2-I 4MP IP Dome Camera',
                'model' => 'DS-2CD1143G2-I', 'sku' => 'IPC-DM-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 3900, 'price' => 4900, 'alert' => 6, 'stock' => 40,
                'img' => 'dome', 'pic' => 0,
                'short' => '4MP indoor/outdoor IP dome with 30m IR, motion detection 2.0 and PoE — the everyday shop-and-office camera.',
                'desc' => "A 4MP fixed dome that covers a shop floor, reception or corridor from a single ceiling point. The 1/3\" progressive scan sensor holds detail down to 0.01 lux and switches to 30m infrared after dark, while the IP67 housing lets you mount it under an outdoor shade without extra protection.\n\nIt draws power over the same Cat6 run that carries video (PoE), so one cable per camera reaches the NVR. Motion Detection 2.0 filters out leaves and headlights, which keeps the recorder from filling up with false events.",
                'plus' => ['One Cat6 cable carries both video and power (802.3af PoE)', '30m IR range covers a full shop floor at night', 'IP67 body — safe under an outdoor shade or veranda', 'Human/vehicle filtering cuts false motion alerts'],
                'spec' => ['Resolution: 4MP (2560 × 1440) @ 20fps', 'Lens: 2.8mm fixed, 103° horizontal', 'IR distance: up to 30m', 'Compression: H.265+ / H.265 / H.264', 'Power: 12V DC or PoE (802.3af), 6.5W max', 'Protection: IP67'],
            ],
            $ipDome + [
                'name' => 'Dahua IPC-HDBW1431E-S4 4MP IR Dome Camera',
                'model' => 'IPC-HDBW1431E-S4', 'sku' => 'IPC-DM-002',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 3600, 'price' => 4600, 'alert' => 6, 'stock' => 36,
                'img' => 'dome', 'pic' => 1,
                'short' => '4MP Dahua entry dome with 30m IR, H.265 and PoE — the value pick for a 4 to 8 camera home or shop package.',
                'desc' => "Dahua's Lite-series dome is what most 4-camera home packages are built around: 4MP detail, H.265 to keep the hard disk lasting, and a metal-and-plastic IP67 body that survives a Dhaka monsoon under an eave.\n\nSmart IR adjusts LED strength by subject distance, so a face at the door does not blow out to white the way it does on cheaper cameras.",
                'plus' => ['Cheapest reliable route to 4MP recording', 'H.265 roughly halves the storage a 2MP H.264 camera needs', 'Smart IR stops close-up faces washing out', 'Works with any ONVIF NVR, not just Dahua'],
                'spec' => ['Resolution: 4MP (2688 × 1520) @ 20fps', 'Lens: 2.8mm fixed, 105° horizontal', 'IR distance: up to 30m', 'Compression: H.265 / H.264', 'Power: 12V DC or PoE (802.3af)', 'Protection: IP67'],
            ],
            $ipDome + [
                'name' => 'Uniview IPC3614LE-ADF28K 4MP Mini Dome Camera',
                'model' => 'IPC3614LE-ADF28K', 'sku' => 'IPC-DM-003',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 3400, 'price' => 4400, 'alert' => 6, 'stock' => 30,
                'img' => 'dome', 'pic' => 2,
                'short' => '4MP Uniview mini dome with built-in mic and 30m IR — compact enough for a low false ceiling.',
                'desc' => "A genuinely small dome, which matters when the false ceiling is only a few inches deep. It records audio through a built-in microphone, so a counter dispute has sound as well as picture.\n\nUniview's Easy series is the usual third option when a customer wants 4MP but neither Hikvision nor Dahua stock is moving.",
                'plus' => ['Built-in microphone records audio at the counter', 'Compact body fits a shallow false ceiling', 'Ultra 265 compression saves recorder space', 'ONVIF — pairs with Hikvision and Dahua NVRs'],
                'spec' => ['Resolution: 4MP (2592 × 1520) @ 20fps', 'Lens: 2.8mm fixed', 'IR distance: up to 30m', 'Audio: built-in microphone', 'Power: 12V DC or PoE (802.3af)', 'Protection: IP67'],
            ],
            $ipDome + [
                'name' => 'Hikvision DS-2CD2143G2-IU 4MP AcuSense Dome Camera',
                'model' => 'DS-2CD2143G2-IU', 'sku' => 'IPC-DM-004',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 6200, 'price' => 7800, 'alert' => 4, 'stock' => 24,
                'img' => 'dome', 'pic' => 3,
                'short' => '4MP AcuSense dome that only alerts on people and vehicles, with a built-in mic and 30m IR.',
                'desc' => "AcuSense puts a deep-learning model on the camera itself: it classifies what moved before sending an event, so alerts arrive for a person climbing the boundary wall and not for a cat or a swinging branch.\n\nWith a built-in microphone and 30m IR this is the dome to sell when a customer has been burned by a cheaper system that alerted a hundred times a night.",
                'plus' => ['Human and vehicle classification on the camera — far fewer false alarms', 'Playback can be filtered to person/vehicle events only', 'Built-in microphone for audio evidence', '120dB WDR handles a bright doorway against a dark interior'],
                'spec' => ['Resolution: 4MP (2688 × 1520) @ 20fps', 'Lens: 2.8mm fixed, 102° horizontal', 'IR distance: up to 30m', 'WDR: 120dB', 'Analytics: line crossing, intrusion, human/vehicle filter', 'Power: 12V DC or PoE (802.3af)'],
            ],
            $ipDome + [
                'name' => 'Dahua IPC-HDBW2841E-S 8MP WizSense Dome Camera',
                'model' => 'IPC-HDBW2841E-S', 'sku' => 'IPC-DM-005',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 8500, 'price' => 10500, 'alert' => 3, 'stock' => 18,
                'img' => 'dome', 'pic' => 4,
                'short' => '8MP 4K WizSense dome with SMD Plus, perimeter protection and 30m IR for showrooms and warehouses.',
                'desc' => "4K resolution across a wide dome view means you can digitally zoom into a face or a number plate after the fact instead of wishing you had mounted a second camera.\n\nWizSense adds SMD Plus (Smart Motion Detection) and perimeter tripwires, both classifying human versus vehicle, so a warehouse boundary can be armed overnight without a guard watching the screen.",
                'plus' => ['4K detail lets you crop into a face during playback', 'SMD Plus records only human and vehicle motion', 'Perimeter tripwire and intrusion zones built in', 'Starlight sensor keeps colour longer at dusk'],
                'spec' => ['Resolution: 8MP (3840 × 2160) @ 20fps', 'Lens: 2.8mm fixed, 106° horizontal', 'IR distance: up to 30m', 'Compression: H.265+ / H.265 / H.264', 'Power: 12V DC or PoE (802.3af)', 'Protection: IP67'],
            ],

            // ---------------- IP bullet ----------------
            $ipBullet + [
                'name' => 'Hikvision DS-2CD1043G2-LIU 4MP ColorVu Bullet Camera',
                'model' => 'DS-2CD1043G2-LIU', 'sku' => 'IPC-BL-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 4300, 'price' => 5400, 'alert' => 6, 'stock' => 40,
                'img' => 'bullet', 'pic' => 0,
                'short' => '4MP ColorVu bullet that records full colour at night using a white-light LED instead of infrared.',
                'desc' => "Infrared gives you a grey silhouette; ColorVu gives you the colour of the shirt and the colour of the bike. An F1.0 aperture plus a warm white-light LED keeps the picture in colour right through the night, which is what actually identifies someone.\n\nThe built-in mic records audio, and the bullet form makes the camera visibly present — useful as a deterrent over a shop shutter.",
                'plus' => ['24-hour colour video — clothing and vehicle colour stay visible', 'Visible white light is a deterrent in itself', 'Built-in microphone for audio', 'IP67 bullet body suits a shutter or gate mount'],
                'spec' => ['Resolution: 4MP (2560 × 1440) @ 20fps', 'Lens: 2.8mm, F1.0 aperture', 'White light distance: up to 30m', 'Audio: built-in microphone', 'Power: 12V DC or PoE (802.3af)', 'Protection: IP67'],
            ],
            $ipBullet + [
                'name' => 'Dahua IPC-HFW1431S-S4 4MP IR Bullet Camera',
                'model' => 'IPC-HFW1431S-S4', 'sku' => 'IPC-BL-002',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 3700, 'price' => 4700, 'alert' => 6, 'stock' => 38,
                'img' => 'bullet', 'pic' => 1,
                'short' => '4MP Dahua bullet with 30m IR and IP67 — the standard outdoor camera in a 4 or 8 camera package.',
                'desc' => "The bullet twin of the HDBW1431E dome, meant for walls and gate posts where a dome would look down at the wrong angle. The sunshade keeps direct sun and rain off the lens, and the three-axis bracket lets you aim it along a boundary wall.\n\nH.265 keeps a month of 4MP footage inside a 2TB disk on a typical 4-camera setup.",
                'plus' => ['Adjustable bracket aims easily along a wall or driveway', 'Integrated sunshade against rain and glare', 'H.265 keeps a month of recording on a 2TB disk', 'IP67 rated for open outdoor mounting'],
                'spec' => ['Resolution: 4MP (2688 × 1520) @ 20fps', 'Lens: 2.8mm fixed, 105° horizontal', 'IR distance: up to 30m', 'Compression: H.265 / H.264', 'Power: 12V DC or PoE (802.3af)', 'Protection: IP67'],
            ],
            $ipBullet + [
                'name' => 'Uniview IPC2124LE-ADF28KM 4MP Mini Bullet Camera',
                'model' => 'IPC2124LE-ADF28KM', 'sku' => 'IPC-BL-003',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 3500, 'price' => 4500, 'alert' => 6, 'stock' => 30,
                'img' => 'bullet', 'pic' => 2,
                'short' => '4MP Uniview mini bullet with built-in mic, 30m IR and Ultra 265 compression.',
                'desc' => "A slim bullet that does not dominate a shop front the way full-size housings do. Ultra 265 is Uniview's own tuning of H.265 and noticeably reduces bitrate on mostly-static scenes such as a closed shutter overnight.\n\nBuilt-in audio and ONVIF support mean it drops into an existing mixed-brand system without argument.",
                'plus' => ['Slim housing keeps a shop front tidy', 'Ultra 265 cuts bitrate hard on static night scenes', 'Built-in microphone', 'ONVIF compatible with Hikvision and Dahua recorders'],
                'spec' => ['Resolution: 4MP (2592 × 1520) @ 20fps', 'Lens: 2.8mm fixed', 'IR distance: up to 30m', 'Compression: Ultra 265 / H.265 / H.264', 'Power: 12V DC or PoE (802.3af)', 'Protection: IP67'],
            ],
            $ipBullet + [
                'name' => 'Hikvision DS-2CD2T87G2-L 8MP ColorVu Bullet Camera',
                'model' => 'DS-2CD2T87G2-L', 'sku' => 'IPC-BL-004',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 11500, 'price' => 14000, 'alert' => 3, 'stock' => 14,
                'img' => 'bullet', 'pic' => 3,
                'short' => '4K ColorVu bullet with 60m white light and AcuSense filtering — for gates, yards and long driveways.',
                'desc' => "8MP resolution with 60m of white light covers a long approach in full colour, which is the combination you need at a factory gate or a long apartment driveway where a 30m camera simply stops seeing.\n\nAcuSense classification means the night alerts you receive are people and vehicles, not moths crossing the lens.",
                'plus' => ['60m white light — twice the reach of a standard camera', '4K colour at night makes number plates and faces usable', 'AcuSense human/vehicle filter cuts night-time false alerts', 'Built-in mic and alarm I/O for a siren or strobe'],
                'spec' => ['Resolution: 8MP (3840 × 2160) @ 20fps', 'Lens: 4mm, F1.0 aperture', 'White light distance: up to 60m', 'WDR: 130dB', 'Interfaces: audio in/out, alarm in/out, microSD slot', 'Power: 12V DC or PoE (802.3af)'],
            ],
            $ipBullet + [
                'name' => 'Dahua IPC-HFW3849T1-AS-PV 8MP Active Deterrence Bullet Camera',
                'model' => 'IPC-HFW3849T1-AS-PV', 'sku' => 'IPC-BL-005',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 10500, 'price' => 12800, 'alert' => 3, 'stock' => 14,
                'img' => 'bullet', 'pic' => 4,
                'short' => '8MP full-colour bullet with a red-blue strobe and built-in siren that warns an intruder off before anything happens.',
                'desc' => "Most cameras record a break-in. This one tries to stop it: cross the tripwire after hours and the camera fires a red-blue strobe and a siren, and pushes an alert to the phone. For a warehouse yard or a rooftop that is worth more than better footage of the theft.\n\nTiOC (Three in One Camera) means full-colour night vision, active deterrence and AI classification in a single unit.",
                'plus' => ['Red-blue strobe and siren scare off an intruder in real time', 'Full-colour night video, no infrared grey', 'Deterrence triggers only on a human, not on a stray dog', 'Two-way audio to speak through the camera from your phone'],
                'spec' => ['Resolution: 8MP (3840 × 2160) @ 20fps', 'Lens: 2.8mm, F1.0 aperture', 'Warm light: up to 30m', 'Deterrence: red-blue strobe + built-in siren and speaker', 'Analytics: SMD Plus, tripwire, intrusion, human/vehicle', 'Power: 12V DC or PoE (802.3af)'],
            ],

            // ---------------- PTZ ----------------
            $ipPtz + [
                'name' => 'Hikvision DS-2DE4225IW-DE 2MP 25x IR Network PTZ Camera',
                'model' => 'DS-2DE4225IW-DE', 'sku' => 'IPC-PZ-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 42000, 'price' => 49500, 'alert' => 2, 'stock' => 8,
                'img' => 'ptz', 'pic' => 0,
                'short' => '2MP PTZ with 25x optical zoom, 100m IR and auto-tracking — one camera to cover an entire yard.',
                'desc' => "A PTZ replaces four or five fixed cameras on a large open area: it pans a full 360°, tilts, and zooms 25x optically to read a plate at the far gate.\n\nAuto-tracking locks onto a moving person and follows them across the yard, and up to 300 presets with patrol patterns let it sweep a fixed route unattended.",
                'plus' => ['25x optical zoom reads a number plate at the far end of a yard', 'Auto-tracking follows a person without an operator', '360° endless pan with 300 presets and patrol routes', '100m IR for full night coverage'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 50fps', 'Optical zoom: 25x (4.8–120mm)', 'Pan: 360° endless, Tilt: −15° to 90°', 'IR distance: up to 100m', 'Power: 24V AC / Hi-PoE', 'Protection: IP66'],
            ],
            $ipPtz + [
                'name' => 'Dahua SD49225DB-HNY 2MP 25x Starlight PTZ Camera',
                'model' => 'SD49225DB-HNY', 'sku' => 'IPC-PZ-002',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 39000, 'price' => 46000, 'alert' => 2, 'stock' => 6,
                'img' => 'ptz', 'pic' => 1,
                'short' => '2MP Starlight PTZ, 25x optical zoom and 100m IR with AI auto-tracking for perimeters and open grounds.',
                'desc' => "The Starlight sensor is the point of difference here: it holds a usable colour image at very low light where an ordinary PTZ has already dropped to black and white.\n\nAI auto-tracking 3.0 keeps a person or vehicle centred and zoomed as it moves, and the IP66 metal dome survives being pole-mounted in the open.",
                'plus' => ['Starlight sensor keeps colour at very low light', 'AI auto-tracking holds a subject centred and zoomed', 'Fast 240°/s pan reaches a preset almost instantly', 'IP66 metal housing for open pole mounting'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 50fps', 'Optical zoom: 25x (4.8–120mm)', 'Pan: 360° endless at up to 240°/s', 'IR distance: up to 100m', 'Power: 24V AC / Hi-PoE (802.3bt)', 'Protection: IP66'],
            ],
            $ipPtz + [
                'name' => 'Uniview IPC6612SR-X33-VG 2MP 33x Lighthunter PTZ Camera',
                'model' => 'IPC6612SR-X33-VG', 'sku' => 'IPC-PZ-003',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 52000, 'price' => 61000, 'alert' => 1, 'stock' => 5,
                'img' => 'ptz', 'pic' => 2,
                'short' => '2MP Lighthunter PTZ with 33x optical zoom and 150m IR — the long-range option for factory perimeters.',
                'desc' => "33x zoom and 150m of infrared put this in a different bracket from the usual 25x PTZ: it is meant for a factory perimeter or a large campus where the far fence is beyond what a normal camera resolves.\n\nLighthunter is Uniview's low-light pipeline, giving colour where competing cameras have gone monochrome.",
                'plus' => ['33x optical zoom for genuinely long perimeters', '150m IR covers a far fence line at night', 'Lighthunter low-light imaging holds colour after dusk', 'Built-in wiper keeps the dome clear in monsoon rain'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 60fps', 'Optical zoom: 33x (4.5–148.5mm)', 'Pan: 360° endless, Tilt: −20° to 90°', 'IR distance: up to 150m', 'Power: 24V AC / Hi-PoE', 'Protection: IP67, IK10, built-in wiper'],
            ],

            // ---------------- HD analog dome ----------------
            $hdDome + [
                'name' => 'Hikvision DS-2CE56D0T-IRPF 2MP HD Indoor Dome Camera',
                'model' => 'DS-2CE56D0T-IRPF', 'sku' => 'AHD-DM-001',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 1250, 'price' => 1650, 'alert' => 10, 'stock' => 80,
                'img' => 'dome', 'pic' => 5,
                'short' => '2MP Turbo HD dome with 20m IR — the cheapest way to add a camera to an existing DVR system.',
                'desc' => "When a customer already has a DVR and coaxial cable in the walls, this is the camera that keeps the job to one afternoon. 1080p over the existing coax, 20m IR, and a plastic dome that suits a shop ceiling.\n\nIt switches between TVI, AHD, CVI and CVBS, so it works on almost any DVR already installed.",
                'plus' => ['Runs on existing coaxial cable — no rewiring', 'Four-in-one output works with almost any DVR', 'Lowest-cost route to add a camera to a running system', '20m Smart IR for indoor coverage'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 25fps', 'Lens: 2.8mm fixed, 92° horizontal', 'IR distance: up to 20m', 'Output: TVI / AHD / CVI / CVBS switchable', 'Power: 12V DC, 4W max', 'Protection: IP66'],
            ],
            $hdDome + [
                'name' => 'Dahua HAC-HDW1200TRQ 2MP HDCVI Dome Camera',
                'model' => 'HAC-HDW1200TRQ', 'sku' => 'AHD-DM-002',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 1150, 'price' => 1550, 'alert' => 10, 'stock' => 80,
                'img' => 'dome', 'pic' => 6,
                'short' => '2MP HDCVI dome, 25m IR and a plastic IP67 body — the budget replacement camera.',
                'desc' => "A straightforward 1080p analog dome for replacing a dead camera on an existing Dahua or generic DVR. Four-in-one switching means it will lock onto whatever signal format the recorder expects.\n\nAt this price it is the camera to quote when the customer wants coverage rather than evidence-grade detail.",
                'plus' => ['Direct drop-in replacement on any coax DVR', 'Four-in-one signal switching, no compatibility guesswork', '25m IR at an entry price point', 'IP67 body tolerates a covered outdoor spot'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 25fps', 'Lens: 2.8mm fixed', 'IR distance: up to 25m', 'Output: CVI / TVI / AHD / CVBS switchable', 'Power: 12V DC', 'Protection: IP67'],
            ],
            $hdDome + [
                'name' => 'Hikvision DS-2CE76H0T-ITMF 5MP HD Indoor Dome Camera',
                'model' => 'DS-2CE76H0T-ITMF', 'sku' => 'AHD-DM-003',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 2200, 'price' => 2800, 'alert' => 8, 'stock' => 50,
                'img' => 'dome', 'pic' => 0,
                'short' => '5MP Turbo HD dome with 20m EXIR — more detail over the same old coaxial cable.',
                'desc' => "The upgrade path for a customer with coax already in the walls who is unhappy with 2MP footage: 5MP more than doubles the pixels without pulling a single new cable, provided the DVR supports 5MP input.\n\nEXIR 2.0 spreads infrared more evenly than LED-ring cameras, so the centre of the frame is not blown out while the edges stay dark.",
                'plus' => ['5MP detail over the existing coaxial run', 'EXIR 2.0 lights the frame evenly, no hot centre', 'Metal-reinforced dome resists a knock', 'Four-in-one output for mixed DVR estates'],
                'spec' => ['Resolution: 5MP (2560 × 1944) @ 20fps', 'Lens: 2.8mm fixed, 85.5° horizontal', 'IR distance: up to 20m (EXIR 2.0)', 'Output: TVI / AHD / CVI / CVBS switchable', 'Power: 12V DC', 'Protection: IP67'],
            ],

            // ---------------- HD analog bullet ----------------
            $hdBullet + [
                'name' => 'Hikvision DS-2CE16D0T-IRPF 2MP HD Outdoor Bullet Camera',
                'model' => 'DS-2CE16D0T-IRPF', 'sku' => 'AHD-BL-001',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 1300, 'price' => 1700, 'alert' => 10, 'stock' => 90,
                'img' => 'bullet', 'pic' => 5,
                'short' => '2MP Turbo HD bullet with 20m IR and IP66 — the highest-volume outdoor analog camera on the shelf.',
                'desc' => "The camera that goes out of the shop more than any other: 1080p, 20m IR, IP66, and a bracket that mounts on a wall or under an eave in ten minutes.\n\nPaired with a 4-channel DVR and a 1TB disk it is the standard entry package for a home or a small shop.",
                'plus' => ['The default outdoor camera for entry-level packages', 'IP66 body handles direct rain', 'Four-in-one output fits any existing DVR', 'Bracket and screws included in the box'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 25fps', 'Lens: 2.8mm fixed, 92° horizontal', 'IR distance: up to 20m', 'Output: TVI / AHD / CVI / CVBS switchable', 'Power: 12V DC, 4W max', 'Protection: IP66'],
            ],
            $hdBullet + [
                'name' => 'Dahua HAC-HFW1200TH-I8 2MP HDCVI Bullet Camera',
                'model' => 'HAC-HFW1200TH-I8', 'sku' => 'AHD-BL-002',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 1750, 'price' => 2250, 'alert' => 8, 'stock' => 60,
                'img' => 'bullet', 'pic' => 6,
                'short' => '2MP HDCVI bullet with an 80m IR reach for long driveways and boundary walls.',
                'desc' => "80m of infrared is the reason to pick this over a standard 20m bullet: a long boundary wall or a driveway that a normal camera leaves in darkness past the first few metres.\n\nThe metal housing and IP67 rating make it suitable for a fully exposed mount.",
                'plus' => ['80m IR — four times the reach of an entry bullet', 'Metal IP67 housing for fully exposed positions', 'Smart IR prevents close-subject overexposure', 'Four-in-one output for any coax DVR'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 25fps', 'Lens: 3.6mm fixed', 'IR distance: up to 80m', 'Output: CVI / TVI / AHD / CVBS switchable', 'Power: 12V DC', 'Protection: IP67'],
            ],
            $hdBullet + [
                'name' => 'Hikvision DS-2CE16H0T-ITF 5MP HD Outdoor Bullet Camera',
                'model' => 'DS-2CE16H0T-ITF', 'sku' => 'AHD-BL-003',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 2300, 'price' => 2900, 'alert' => 8, 'stock' => 50,
                'img' => 'bullet', 'pic' => 0,
                'short' => '5MP Turbo HD bullet with 20m EXIR — the 5MP outdoor partner to the H0T dome.',
                'desc' => "Same 5MP sensor as the indoor H0T dome in a weatherproof bullet body, for customers upgrading a full coax system rather than a single camera.\n\nRemember to check the DVR first: a 5MP camera on a 2MP-only recorder will fall back to 1080p.",
                'plus' => ['5MP over existing coaxial cable', 'EXIR 2.0 for even night illumination', 'IP67 metal body for outdoor mounting', 'Matches the DS-2CE76H0T dome for a consistent system'],
                'spec' => ['Resolution: 5MP (2560 × 1944) @ 20fps', 'Lens: 2.8mm fixed, 85.5° horizontal', 'IR distance: up to 20m (EXIR 2.0)', 'Output: TVI / AHD / CVI / CVBS switchable', 'Power: 12V DC', 'Protection: IP67'],
            ],

            // ---------------- Wi-Fi indoor ----------------
            $wifiIn + [
                'name' => 'TP-Link Tapo C210 3MP Pan/Tilt Wi-Fi Camera',
                'model' => 'Tapo C210', 'sku' => 'WIF-IN-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 2600, 'price' => 3200, 'alert' => 10, 'stock' => 60,
                'img' => 'wifi', 'pic' => 0,
                'short' => '3MP pan/tilt Wi-Fi camera with 360° coverage, two-way audio and microSD recording — no NVR needed.',
                'desc' => "The best-selling standalone camera on the counter. It sets up from the Tapo app in a couple of minutes, needs no recorder, and stores footage on a microSD card up to 512GB.\n\n360° horizontal pan and 114° tilt cover a whole room from one corner, and two-way audio lets a parent speak to a child or a shopkeeper challenge someone from their phone.",
                'plus' => ['Works alone — no NVR, no cabling beyond a power point', '360° pan and 114° tilt cover a full room', 'Two-way audio to speak through the camera', 'Records to microSD up to 512GB, no subscription'],
                'spec' => ['Resolution: 3MP (2304 × 1296)', 'Pan/Tilt: 360° horizontal, 114° vertical', 'Night vision: up to 30ft (9m)', 'Storage: microSD up to 512GB', 'Wi-Fi: 2.4GHz 802.11b/g/n', 'Audio: two-way with noise cancellation'],
            ],
            $wifiIn + [
                'name' => 'Ezviz C6N 1080p Pan/Tilt Wi-Fi Camera',
                'model' => 'C6N', 'sku' => 'WIF-IN-002',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 2500, 'price' => 3100, 'alert' => 10, 'stock' => 55,
                'img' => 'wifi', 'pic' => 1,
                'short' => '1080p indoor PT camera with smart motion tracking and two-way talk — Hikvision quality at a consumer price.',
                'desc' => "Ezviz is Hikvision's consumer brand, which shows in the image processing at this price. The camera detects motion and pans to follow it, so a person walking across a room stays in frame.\n\nA privacy mode tilts the lens fully down when the family is home, which sells well for bedrooms and living rooms.",
                'plus' => ['Auto motion tracking keeps a moving person in frame', 'Privacy mode physically points the lens away', 'Backed by Hikvision imaging at a consumer price', 'microSD up to 256GB or Ezviz CloudPlay'],
                'spec' => ['Resolution: 1080p (1920 × 1080)', 'Pan/Tilt: 340° horizontal, 105° vertical', 'Night vision: up to 10m', 'Storage: microSD up to 256GB', 'Wi-Fi: 2.4GHz', 'Audio: two-way'],
            ],
            $wifiIn + [
                'name' => 'Xiaomi Smart Camera C300 2K Wi-Fi Camera',
                'model' => 'C300', 'sku' => 'WIF-IN-003',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 3200, 'price' => 3900, 'alert' => 8, 'stock' => 40,
                'img' => 'wifi', 'pic' => 2,
                'short' => '2K indoor camera with F1.4 aperture, AI human detection and 360° view through the Mi Home app.',
                'desc' => "2K resolution and a bright F1.4 lens make this noticeably better in a dim room than the 1080p competition, and the Mi Home app is already installed on many customers' phones.\n\nAI human-shape detection means the phone buzzes for a person and not for a ceiling fan shadow.",
                'plus' => ['2K resolution with a bright F1.4 lens for dim rooms', 'AI human detection cuts pointless phone alerts', 'Integrates with an existing Mi Home smart-home setup', 'Full-colour night mode as well as infrared'],
                'spec' => ['Resolution: 2K (2304 × 1296)', 'Aperture: F1.4', 'Pan/Tilt: 360° horizontal, 108° vertical', 'Storage: microSD up to 256GB or NAS', 'Wi-Fi: 2.4GHz / 5GHz dual band', 'Audio: two-way'],
            ],
            $wifiIn + [
                'name' => 'Hikvision DS-2CV2Q21FD-IW 2MP Wi-Fi Cube Camera',
                'model' => 'DS-2CV2Q21FD-IW', 'sku' => 'WIF-IN-004',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 3300, 'price' => 4100, 'alert' => 6, 'stock' => 30,
                'img' => 'wifi', 'pic' => 3,
                'short' => '2MP Wi-Fi cube camera that also joins a Hik-Connect NVR — a wireless camera that fits a professional system.',
                'desc' => "Unlike consumer Wi-Fi cameras, this one adds to an existing Hikvision NVR over the network, so a customer can put a camera in a room with no cable run and still have it recorded centrally with the rest.\n\nIt keeps a microSD slot as a fallback if the Wi-Fi drops.",
                'plus' => ['Joins an existing Hikvision NVR — central recording, no cable', 'microSD fallback keeps recording if Wi-Fi drops', 'Built-in mic and speaker for two-way audio', 'Hik-Connect app, the same one the rest of the system uses'],
                'spec' => ['Resolution: 2MP (1920 × 1080) @ 25fps', 'Lens: 2.8mm fixed', 'IR distance: up to 10m', 'Storage: microSD up to 256GB', 'Wi-Fi: 2.4GHz 802.11b/g/n', 'Compression: H.265+'],
            ],

            // ---------------- Wi-Fi outdoor ----------------
            $wifiOut + [
                'name' => 'TP-Link Tapo C320WS 2K Outdoor Wi-Fi Camera',
                'model' => 'Tapo C320WS', 'sku' => 'WIF-OT-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 4200, 'price' => 5100, 'alert' => 6, 'stock' => 30,
                'img' => 'wifi', 'pic' => 4,
                'short' => '2K outdoor Wi-Fi camera, IP66, with colour night vision, a siren and person/pet detection.',
                'desc' => "An outdoor camera that needs only a power point — useful on a rented flat's balcony or a shop front where running Cat6 back to a recorder is not practical.\n\nIt distinguishes people, pets and vehicles, and can sound its own siren and flash a light when something crosses the zone you drew.",
                'plus' => ['No recorder or network cabling needed — just power', 'Colour night vision plus a spotlight and siren', 'Person, pet and vehicle detection with custom zones', 'IP66 rated for direct rain'],
                'spec' => ['Resolution: 2K QHD (2560 × 1440)', 'Night vision: colour with spotlight, or IR to 30m', 'Detection: person / pet / vehicle, custom zones', 'Storage: microSD up to 512GB', 'Wi-Fi: 2.4GHz', 'Protection: IP66'],
            ],
            $wifiOut + [
                'name' => 'Ezviz H3C 2K Outdoor Colour Night Wi-Fi Camera',
                'model' => 'H3C 2K', 'sku' => 'WIF-OT-002',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 4500, 'price' => 5500, 'alert' => 6, 'stock' => 26,
                'img' => 'wifi', 'pic' => 5,
                'short' => '2K outdoor Wi-Fi camera with dual-band Wi-Fi, full-colour night vision and AI person detection.',
                'desc' => "Dual-band Wi-Fi matters outdoors: the 5GHz band is usually far less congested than 2.4GHz in a dense Dhaka neighbourhood, which is the usual reason an outdoor Wi-Fi camera keeps dropping.\n\nFull-colour night vision plus a smart light gives usable identification instead of grey infrared shapes.",
                'plus' => ['Dual-band Wi-Fi avoids congested 2.4GHz in dense areas', 'Full-colour night vision with a smart light', 'AI person detection with active siren response', 'IP67 body for a fully exposed mount'],
                'spec' => ['Resolution: 2K (2560 × 1440)', 'Night vision: full colour with light, or IR to 30m', 'Wi-Fi: 2.4GHz / 5GHz dual band', 'Storage: microSD up to 512GB', 'Audio: two-way with siren', 'Protection: IP67'],
            ],
            $wifiOut + [
                'name' => 'Ezviz CB3 Battery-Powered Wi-Fi Camera',
                'model' => 'CB3', 'sku' => 'WIF-OT-003',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 6500, 'price' => 7900, 'alert' => 4, 'stock' => 18,
                'img' => 'wifi', 'pic' => 6,
                'short' => 'Battery Wi-Fi camera with no wiring at all — mount it anywhere and charge it every few months.',
                'desc' => "For places with no power point: a boundary wall, a construction site, a rented shop where drilling is not allowed. A 5,200mAh battery lasts months on event-triggered recording, and a solar panel accessory removes charging altogether.\n\nPIR-triggered recording is what makes the battery last — it wakes only when the sensor sees body heat.",
                'plus' => ['Zero wiring — mount it anywhere in minutes', 'Months of battery life on PIR-triggered recording', 'Optional solar panel makes it permanent', 'Full-colour night vision with a built-in spotlight'],
                'spec' => ['Resolution: 2K (2304 × 1296)', 'Battery: 5,200mAh rechargeable', 'Trigger: PIR motion detection', 'Storage: microSD up to 512GB or CloudPlay', 'Wi-Fi: 2.4GHz', 'Protection: IP66'],
            ],

            // ---------------- NVR ----------------
            $nvr + [
                'name' => 'Hikvision DS-7608NI-K1 8-Channel 4K NVR',
                'model' => 'DS-7608NI-K1', 'sku' => 'NVR-08-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 8500, 'price' => 10500, 'alert' => 4, 'stock' => 24,
                'img' => 'recorder', 'pic' => 0,
                'short' => '8-channel 4K NVR with one SATA bay, H.265+ and 4K HDMI output — the standard recorder for an 8-camera IP system.',
                'desc' => "The recorder most 6 to 8 camera IP jobs are built on. It takes one hard disk up to 10TB, decodes 4K to an HDMI monitor, and handles 80Mbps of incoming camera bandwidth.\n\nNote it has no built-in PoE ports — pair it with a PoE switch, which is usually cheaper and more flexible than the /8P model anyway.",
                'plus' => ['Handles 8 cameras up to 8MP each', 'H.265+ roughly halves disk consumption', '4K HDMI plus VGA for a second monitor', 'Hik-Connect app for phone access from anywhere'],
                'spec' => ['Channels: 8 IP, up to 8MP each', 'Incoming bandwidth: 80Mbps', 'HDD: 1 × SATA up to 10TB', 'Output: HDMI 4K + VGA 1080p', 'Compression: H.265+ / H.265 / H.264+', 'Network: 1 × RJ45 10/100/1000M'],
            ],
            $nvr + [
                'name' => 'Hikvision DS-7616NI-K2/16P 16-Channel PoE NVR',
                'model' => 'DS-7616NI-K2/16P', 'sku' => 'NVR-16-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 24000, 'price' => 28500, 'alert' => 2, 'stock' => 10,
                'img' => 'recorder', 'pic' => 1,
                'short' => '16-channel NVR with 16 built-in PoE ports and two SATA bays — one box powers and records the whole system.',
                'desc' => "Sixteen PoE ports on the recorder means no separate switch and no separate power supply: each camera takes one Cat6 cable straight back to the box. For a 16-camera office or showroom that is a materially simpler install.\n\nTwo drive bays take up to 10TB each, enough for a month of 16 cameras at 4MP.",
                'plus' => ['16 PoE ports built in — no separate switch or power supply', 'Two drive bays, up to 20TB total', '160Mbps incoming bandwidth for 4MP and 8MP cameras', 'Plug-and-play: cameras self-configure on the PoE ports'],
                'spec' => ['Channels: 16 IP with 16 × PoE (802.3af/at, 200W total)', 'Incoming bandwidth: 160Mbps', 'HDD: 2 × SATA up to 10TB each', 'Output: HDMI 4K + VGA', 'Compression: H.265+ / H.265 / H.264+', 'Network: 1 × RJ45 10/100/1000M'],
            ],
            $nvr + [
                'name' => 'Dahua NVR2104HS-P-4KS3 4-Channel PoE NVR',
                'model' => 'NVR2104HS-P-4KS3', 'sku' => 'NVR-04-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 7500, 'price' => 9200, 'alert' => 4, 'stock' => 22,
                'img' => 'recorder', 'pic' => 2,
                'short' => '4-channel NVR with 4 PoE ports and AI search — the complete recorder for a 4-camera home package.',
                'desc' => "The recorder half of a typical home package: four PoE ports, one drive bay, and SMD Plus so the customer can search a day of footage for people rather than scrubbing through it.\n\nFace and human-body search work off the AI cameras in the Dahua WizSense range, turning playback from a chore into a query.",
                'plus' => ['4 PoE ports — one cable per camera, no extra hardware', 'AI search finds people and vehicles instead of scrubbing', 'Compact desktop box fits behind a TV or in a cupboard', 'DMSS app for phone viewing and playback'],
                'spec' => ['Channels: 4 IP with 4 × PoE (802.3af/at)', 'Incoming bandwidth: 80Mbps', 'HDD: 1 × SATA up to 16TB', 'Output: HDMI 4K + VGA', 'Compression: Smart H.265+ / H.265 / H.264', 'AI: SMD Plus, face and human-body search'],
            ],
            $nvr + [
                'name' => 'Uniview NVR301-16S3-P16 16-Channel PoE NVR',
                'model' => 'NVR301-16S3-P16', 'sku' => 'NVR-16-002',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 22000, 'price' => 26500, 'alert' => 2, 'stock' => 8,
                'img' => 'recorder', 'pic' => 3,
                'short' => '16-channel Uniview NVR with 16 PoE ports and Ultra 265 support — the value 16-camera recorder.',
                'desc' => "A cheaper route to sixteen PoE channels than the equivalent Hikvision, and it takes ONVIF cameras from any brand, which matters when a customer is expanding a mixed system.\n\nUltra 265 support means Uniview cameras record at roughly half the bitrate of H.264, stretching the single drive bay further than the spec suggests.",
                'plus' => ['16 PoE ports at a lower price than equivalent Hikvision', 'Ultra 265 stretches a single drive much further', 'Accepts ONVIF cameras from any brand', 'EZView app with easy remote setup'],
                'spec' => ['Channels: 16 IP with 16 × PoE', 'Incoming bandwidth: 160Mbps', 'HDD: 1 × SATA up to 10TB', 'Output: HDMI 4K + VGA', 'Compression: Ultra 265 / H.265 / H.264', 'Network: 1 × RJ45 10/100/1000M'],
            ],
            $nvr + [
                'name' => 'Dahua NVR5232-EI 32-Channel 4K AI NVR',
                'model' => 'NVR5232-EI', 'sku' => 'NVR-32-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 38000, 'price' => 45000, 'alert' => 1, 'stock' => 5,
                'img' => 'recorder', 'pic' => 4,
                'short' => '32-channel 4K AI NVR with two drive bays and face recognition — for factories, hospitals and large showrooms.',
                'desc' => "The recorder for a real installation: 32 channels, 384Mbps of incoming bandwidth, and on-box AI that runs face recognition and perimeter analytics even on cameras that lack their own.\n\nTwo bays up to 20TB give a month of retention at 32 channels if you keep sensible bitrates.",
                'plus' => ['32 channels at 384Mbps for a full building', 'On-box AI adds analytics to non-AI cameras', 'Face recognition with a stored watchlist database', 'Two drive bays, up to 20TB total'],
                'spec' => ['Channels: 32 IP, up to 32MP each', 'Incoming bandwidth: 384Mbps', 'HDD: 2 × SATA up to 10TB each', 'Output: dual HDMI 4K + VGA', 'AI: face recognition, perimeter protection, SMD Plus', 'Network: 1 × RJ45 10/100/1000M'],
            ],

            // ---------------- DVR ----------------
            $dvr + [
                'name' => 'Hikvision iDS-7204HQHI-M1/S 4-Channel AcuSense DVR',
                'model' => 'iDS-7204HQHI-M1/S', 'sku' => 'DVR-04-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 5200, 'price' => 6500, 'alert' => 5, 'stock' => 30,
                'img' => 'recorder', 'pic' => 5,
                'short' => '4-channel Turbo HD DVR with AcuSense human/vehicle filtering — an analog recorder that alerts like an IP one.',
                'desc' => "AcuSense on a coax DVR is the interesting part: the recorder itself classifies motion as human or vehicle, so an existing analog system gets sensible alerts without replacing a single camera.\n\nIt takes analog and IP inputs together, which is how most upgrades actually happen — a few new IP cameras alongside the old coax ones.",
                'plus' => ['Human/vehicle filtering on existing analog cameras', 'Takes analog and IP cameras on the same recorder', 'Playback filtered to human and vehicle events only', 'Supports up to 5MP analog input'],
                'spec' => ['Channels: 4 analog (up to 5MP) + 2 IP', 'HDD: 1 × SATA up to 10TB', 'Output: HDMI 1080p + VGA', 'Compression: H.265 Pro+', 'AI: human and vehicle classification', 'Input: TVI / AHD / CVI / CVBS / IP'],
            ],
            $dvr + [
                'name' => 'Hikvision DS-7208HGHI-K1 8-Channel Turbo HD DVR',
                'model' => 'DS-7208HGHI-K1', 'sku' => 'DVR-08-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 6200, 'price' => 7700, 'alert' => 5, 'stock' => 28,
                'img' => 'recorder', 'pic' => 6,
                'short' => '8-channel 1080p DVR with H.265 Pro+ and one SATA bay — the volume recorder for analog camera packages.',
                'desc' => "The recorder in most 8-camera analog packages: 1080p across all channels, H.265 Pro+ to keep the disk lasting, and hybrid input so two IP cameras can join later.\n\nStraightforward, well understood, and cheap to replace if lightning takes one out.",
                'plus' => ['Eight analog channels at 1080p in one cheap box', 'H.265 Pro+ stretches a 1TB disk to weeks', 'Hybrid: two IP cameras can be added later', 'Hik-Connect app with easy QR-code setup'],
                'spec' => ['Channels: 8 analog (up to 2MP) + 2 IP', 'HDD: 1 × SATA up to 10TB', 'Output: HDMI 1080p + VGA', 'Compression: H.265 Pro+ / H.265 / H.264+', 'Input: TVI / AHD / CVI / CVBS / IP', 'Audio: 1-channel RCA in/out'],
            ],
            $dvr + [
                'name' => 'Dahua XVR1B16-I 16-Channel Penta-brid DVR',
                'model' => 'XVR1B16-I', 'sku' => 'DVR-16-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 9500, 'price' => 11500, 'alert' => 3, 'stock' => 14,
                'img' => 'recorder', 'pic' => 7,
                'short' => '16-channel penta-brid DVR with SMD Plus — accepts CVI, TVI, AHD, CVBS and IP on the same box.',
                'desc' => "Penta-brid means it will take whatever camera the site already has: CVI, TVI, AHD, CVBS or IP, mixed freely across the sixteen channels. For a shop that services other people's ageing systems that flexibility saves arguments.\n\nSMD Plus adds human and vehicle filtering to plain analog cameras.",
                'plus' => ['Accepts five signal types on the same recorder', 'SMD Plus filters human and vehicle motion on analog cameras', '16 channels at a price close to an 8-channel Hikvision', 'DMSS app for phone access'],
                'spec' => ['Channels: 16 analog (up to 5MP) + 8 IP', 'HDD: 1 × SATA up to 16TB', 'Output: HDMI + VGA', 'Compression: Smart H.265+ / H.265 / H.264', 'Input: CVI / TVI / AHD / CVBS / IP', 'AI: SMD Plus'],
            ],
            $dvr + [
                'name' => 'Uniview XVR301-08G3 8-Channel Hybrid DVR',
                'model' => 'XVR301-08G3', 'sku' => 'DVR-08-002',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 6000, 'price' => 7400, 'alert' => 4, 'stock' => 18,
                'img' => 'recorder', 'pic' => 0,
                'short' => '8-channel Uniview hybrid DVR supporting 5MP analog input and up to 4 extra IP channels.',
                'desc' => "A hybrid recorder that handles 5MP analog, which is the sweet spot for customers upgrading cameras on existing coax without moving to IP.\n\nFour IP channels on top give room for a couple of network cameras where coax cannot reach.",
                'plus' => ['5MP analog input on existing coaxial cable', 'Four extra IP channels for hard-to-reach spots', 'Ultra 265 support on the IP channels', 'Competitively priced against Hikvision and Dahua'],
                'spec' => ['Channels: 8 analog (up to 5MP) + 4 IP', 'HDD: 1 × SATA up to 10TB', 'Output: HDMI + VGA', 'Compression: Ultra 265 / H.265 / H.264', 'Input: TVI / AHD / CVI / CVBS / IP', 'Network: 1 × RJ45 10/100M'],
            ],

            // ---------------- Storage ----------------
            $hddCat + [
                'name' => 'Seagate SkyHawk 1TB Surveillance Hard Disk',
                'model' => 'ST1000VX013', 'sku' => 'HDD-ST-001',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 4800, 'price' => 5700, 'alert' => 8, 'stock' => 50,
                'img' => 'hdd', 'pic' => 0,
                'short' => '1TB surveillance-grade 3.5" SATA drive rated for 24/7 writing — never fit a desktop drive to a DVR.',
                'desc' => "Surveillance drives are built for a workload desktop drives are not: continuous writing, all day every day, from multiple camera streams at once. A desktop drive in a DVR typically fails inside a year.\n\nImagePerfect firmware prioritises dropping nothing during a write, so recordings do not develop gaps.",
                'plus' => ['Rated for 24/7 continuous write, unlike a desktop drive', 'ImagePerfect firmware avoids dropped frames in recordings', 'Handles up to 64 camera streams', '3-year warranty with data recovery service'],
                'spec' => ['Capacity: 1TB', 'Interface: SATA 6Gb/s', 'Form factor: 3.5 inch', 'Cache: 256MB', 'Workload rating: 180TB/year', 'Warranty: 3 years'],
            ],
            $hddCat + [
                'name' => 'Seagate SkyHawk 2TB Surveillance Hard Disk',
                'model' => 'ST2000VX017', 'sku' => 'HDD-ST-002',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 6800, 'price' => 8000, 'alert' => 8, 'stock' => 45,
                'img' => 'hdd', 'pic' => 1,
                'short' => '2TB surveillance drive — roughly a month of recording for four 4MP cameras on H.265.',
                'desc' => "The capacity most 4 to 8 camera jobs land on. With H.265 cameras, 2TB holds around a month of continuous recording for four 4MP streams, or considerably longer on motion-only recording.\n\nSame ImagePerfect firmware and 24/7 rating as the 1TB, at a better cost per terabyte.",
                'plus' => ['About a month of retention for four 4MP H.265 cameras', 'Better cost per terabyte than the 1TB drive', 'Rated for 24/7 recording workloads', '3-year warranty with data recovery service'],
                'spec' => ['Capacity: 2TB', 'Interface: SATA 6Gb/s', 'Form factor: 3.5 inch', 'Cache: 256MB', 'Workload rating: 180TB/year', 'Warranty: 3 years'],
            ],
            $hddCat + [
                'name' => 'Western Digital Purple 4TB Surveillance Hard Disk',
                'model' => 'WD43PURZ', 'sku' => 'HDD-WD-001',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 11500, 'price' => 13500, 'alert' => 5, 'stock' => 30,
                'img' => 'hdd', 'pic' => 2,
                'short' => '4TB WD Purple surveillance drive with AllFrame technology, rated for 64 camera streams.',
                'desc' => "WD Purple is the other half of the surveillance drive market and behaves identically in practice. AllFrame reduces the frame loss that causes stuttering playback when several cameras write at once.\n\n4TB is the sensible choice for a 16-channel recorder that needs a month of history.",
                'plus' => ['AllFrame technology reduces stutter with many simultaneous streams', 'Supports up to 64 camera streams', 'Good fit for a 16-channel NVR needing a month of history', '3-year manufacturer warranty'],
                'spec' => ['Capacity: 4TB', 'Interface: SATA 6Gb/s', 'Form factor: 3.5 inch', 'Cache: 256MB', 'Workload rating: 180TB/year', 'Warranty: 3 years'],
            ],
            $hddCat + [
                'name' => 'Toshiba S300 6TB Surveillance Hard Disk',
                'model' => 'HDWT360UZSVA', 'sku' => 'HDD-TS-001',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 16500, 'price' => 19500, 'alert' => 3, 'stock' => 16,
                'img' => 'hdd', 'pic' => 3,
                'short' => '6TB Toshiba S300 for large systems — long retention on 32-channel recorders.',
                'desc' => "For a 32-channel recorder or anywhere the customer needs 60 to 90 days of history, 6TB per bay is where the maths starts working. The S300 is rated for 24/7 operation with up to 64 cameras.\n\nUsually the cheapest per terabyte of the three surveillance brands at this capacity.",
                'plus' => ['Long retention on 32-channel systems', 'Often the best cost per terabyte at 6TB', 'Rated for up to 64 camera streams, 24/7', '3-year manufacturer warranty'],
                'spec' => ['Capacity: 6TB', 'Interface: SATA 6Gb/s', 'Form factor: 3.5 inch', 'Cache: 256MB', 'Rotation: 7200 RPM', 'Warranty: 3 years'],
            ],
            $cardCat + [
                'name' => 'SanDisk Extreme 64GB microSDXC Card',
                'model' => 'SDSQXAH-064G', 'sku' => 'MEM-SD-001',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 900, 'price' => 1250, 'alert' => 15, 'stock' => 100,
                'img' => 'card', 'pic' => 0,
                'short' => '64GB A2 V30 microSD for Wi-Fi cameras — fast enough to record 2K without dropped frames.',
                'desc' => "Wi-Fi cameras write continuously to the card, which kills ordinary cards fast. A V30-rated card sustains 30MB/s of writing, which is what 2K recording actually needs.\n\n64GB gives roughly a week of continuous 1080p recording, or much longer on motion-triggered clips.",
                'plus' => ['V30 sustained write keeps 2K recording gap-free', 'About a week of continuous 1080p on one card', 'Waterproof, temperature-proof and shock-proof', 'Standard fit for Tapo, Ezviz and Hikvision Wi-Fi cameras'],
                'spec' => ['Capacity: 64GB', 'Speed class: UHS-I, U3, V30, A2', 'Read: up to 170MB/s', 'Write: up to 80MB/s', 'Format: microSDXC with SD adapter', 'Warranty: 1 year'],
            ],
            $cardCat + [
                'name' => 'Samsung PRO Endurance 128GB microSDXC Card',
                'model' => 'MB-MJ128KA', 'sku' => 'MEM-SS-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 2100, 'price' => 2700, 'alert' => 10, 'stock' => 60,
                'img' => 'card', 'pic' => 1,
                'short' => '128GB endurance card rated for 140,400 hours of continuous recording — built for cameras, not phones.',
                'desc' => "The card to sell to anyone whose previous microSD died in six months. PRO Endurance is designed specifically for continuous video writing and is rated for roughly sixteen years of it, against a few months for a standard card.\n\n128GB gives around two weeks of continuous 1080p.",
                'plus' => ['Rated 140,400 hours of continuous recording', 'Outlasts a standard microSD many times over in a camera', 'About two weeks of continuous 1080p', '2-year warranty'],
                'spec' => ['Capacity: 128GB', 'Endurance: up to 140,400 hours continuous recording', 'Speed class: UHS-I, U1, Class 10', 'Read: up to 100MB/s', 'Write: up to 40MB/s', 'Warranty: 2 years'],
            ],

            // ---------------- Power ----------------
            $psu + [
                'name' => '12V 5A CCTV Power Supply Adapter',
                'model' => 'PA-12V5A', 'sku' => 'PWR-AD-001',
                'unit' => 'pcs', 'warranty' => 180, 'cost' => 450, 'price' => 700, 'alert' => 20, 'stock' => 120,
                'img' => 'power', 'pic' => 0,
                'short' => '12V 5A regulated adapter that powers up to four analog cameras from one point through a splitter.',
                'desc' => "One adapter and a 1-to-4 splitter is the usual way an analog install is powered: cheaper and tidier than four separate adapters at four separate sockets.\n\nRegulated output matters — an unregulated supply sags when all four cameras switch their IR on at dusk, and the picture rolls.",
                'plus' => ['Runs up to four analog cameras through a splitter', 'Regulated output holds 12V when IR LEDs kick in', 'Short-circuit and overload protection', 'Standard 2.1mm DC barrel plug'],
                'spec' => ['Input: 100–240V AC 50/60Hz', 'Output: 12V DC 5A (60W)', 'Connector: 2.1 × 5.5mm DC barrel', 'Protection: short-circuit, overload, over-voltage', 'Cable length: 1m', 'Warranty: 6 months'],
            ],
            $psu + [
                'name' => '12V 10A 9-Channel CCTV Power Supply Box',
                'model' => 'PB-12V10A-9CH', 'sku' => 'PWR-BX-001',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 1300, 'price' => 1800, 'alert' => 10, 'stock' => 60,
                'img' => 'power', 'pic' => 1,
                'short' => 'Metal 9-output power distribution box with individual fuses — the proper way to power an 8-camera analog system.',
                'desc' => "Nine individually fused outputs in a lockable metal box, mounted next to the DVR. When one camera shorts, its fuse blows and the other eight keep recording — which is the whole point over a splitter.\n\nThe fuses also make fault-finding a two-minute job instead of an afternoon.",
                'plus' => ['Individual fuse per camera — one fault does not take the system down', 'Makes fault-finding immediate', 'Lockable metal enclosure mounts beside the DVR', 'Single mains point for the whole camera set'],
                'spec' => ['Input: 100–240V AC 50/60Hz', 'Output: 12V DC 10A total across 9 channels', 'Protection: individual fuse per output', 'Enclosure: lockable metal wall-mount box', 'Indicator: per-channel LED', 'Warranty: 1 year'],
            ],
            $psu + [
                'name' => '12V 2A DC Camera Power Adapter',
                'model' => 'PA-12V2A', 'sku' => 'PWR-AD-002',
                'unit' => 'pcs', 'warranty' => 180, 'cost' => 220, 'price' => 350, 'alert' => 25, 'stock' => 200,
                'img' => 'power', 'pic' => 2,
                'short' => 'Single-camera 12V 2A adapter — the standard replacement when a camera stops working.',
                'desc' => "The most common warranty-adjacent sale in the shop: a camera that has gone dark usually has a dead adapter, not a dead camera. Keep these in volume.\n\n2A is enough for one analog or IP camera including its IR LEDs at full power.",
                'plus' => ['Powers one camera including IR at full draw', 'The usual fix when a single camera goes dark', 'Regulated and short-circuit protected', 'Standard 2.1mm plug fits nearly every camera'],
                'spec' => ['Input: 100–240V AC 50/60Hz', 'Output: 12V DC 2A (24W)', 'Connector: 2.1 × 5.5mm DC barrel', 'Protection: short-circuit and overload', 'Cable length: 1m', 'Warranty: 6 months'],
            ],

            // ---------------- Cable ----------------
            $cableCat + [
                'name' => '3+1 CCTV Coaxial Cable 100 Yard Roll (Copper)',
                'model' => 'COAX-3P1-100Y', 'sku' => 'CBL-CX-001',
                'unit' => 'roll', 'warranty' => 0, 'cost' => 2600, 'price' => 3300, 'alert' => 5, 'stock' => 40,
                'img' => 'cable', 'pic' => 0,
                'short' => '100-yard 3+1 coax roll carrying video and power in one run — the standard analog installation cable.',
                'desc' => "3+1 means one coaxial video core plus three power conductors in a single sheath, so a camera needs one cable pull instead of two. Copper conductors, not copper-clad aluminium, which is what keeps a 1080p signal clean past 60 metres.\n\nOne roll typically covers a 4 to 6 camera house.",
                'plus' => ['Video and power in a single cable pull', 'Genuine copper cores hold 1080p past 60m', 'One roll usually covers a 4–6 camera house', 'PVC sheath suitable for indoor and conduit runs'],
                'spec' => ['Type: 3+1 (RG-59 coax + 3 power cores)', 'Length: 100 yards (≈91m)', 'Conductor: solid copper', 'Shield: aluminium braid', 'Jacket: PVC', 'Recommended run: up to 100m at 1080p'],
            ],
            $cableCat + [
                'name' => 'Cat6 UTP Cable 305m Box (Solid Copper)',
                'model' => 'CAT6-UTP-305', 'sku' => 'CBL-C6-001',
                'unit' => 'box', 'warranty' => 0, 'cost' => 7500, 'price' => 9000, 'alert' => 4, 'stock' => 24,
                'img' => 'cable', 'pic' => 1,
                'short' => '305m box of solid-copper Cat6 — the cable every IP camera and PoE run needs.',
                'desc' => "Solid copper, not CCA. This is the single most important shortcut to refuse on an IP job: copper-clad aluminium will pass a link test on the bench and then fail to deliver PoE power at 80 metres.\n\nOne box covers a typical 8 to 16 camera IP installation.",
                'plus' => ['Solid copper delivers full PoE power at 100m', 'Covers a typical 8–16 camera IP job per box', 'Gigabit rated, so it also serves the customer network', 'Pull-box packaging feeds without tangling'],
                'spec' => ['Category: Cat6 UTP', 'Length: 305m (1000ft) pull box', 'Conductor: 23AWG solid copper', 'Bandwidth: 250MHz', 'Rating: Gigabit Ethernet, PoE/PoE+ capable', 'Jacket: PVC, indoor'],
            ],
            $cableCat + [
                'name' => 'Cat6 Outdoor UV Cable 305m Box (Armoured)',
                'model' => 'CAT6-OUT-305', 'sku' => 'CBL-C6-002',
                'unit' => 'box', 'warranty' => 0, 'cost' => 9500, 'price' => 11500, 'alert' => 3, 'stock' => 15,
                'img' => 'cable', 'pic' => 2,
                'short' => '305m outdoor Cat6 with a UV-resistant jacket and drain wire for rooftop and pole runs.',
                'desc' => "Indoor Cat6 on a Dhaka rooftop cracks within two summers and then lets water into the sheath. Outdoor cable has a UV-stabilised jacket and a drain wire, and is what should be quoted for any run that leaves the building.\n\nUse it for pole-mounted cameras, rooftop runs and anything crossing between buildings.",
                'plus' => ['UV-stabilised jacket survives years of direct sun', 'Drain wire and gel-free water blocking for outdoor runs', 'The correct cable for rooftop and pole-mounted cameras', 'Solid copper, full PoE capable at 100m'],
                'spec' => ['Category: Cat6 outdoor UTP with drain wire', 'Length: 305m (1000ft) pull box', 'Conductor: 23AWG solid copper', 'Jacket: UV-resistant HDPE', 'Rating: Gigabit Ethernet, PoE/PoE+ capable', 'Use: outdoor, direct sun, conduit'],
            ],

            // ---------------- Connectors ----------------
            $conn + [
                'name' => 'BNC Connector Pair for CCTV (Pack of 10)',
                'model' => 'BNC-MF-10', 'sku' => 'ACC-BN-001',
                'unit' => 'pack', 'warranty' => 0, 'cost' => 250, 'price' => 400, 'alert' => 20, 'stock' => 150,
                'img' => 'cable', 'pic' => 3,
                'short' => 'Ten pairs of screw-type BNC connectors with DC plugs — enough for five analog cameras.',
                'desc' => "Screw-type rather than crimp, so they go on in the field without a crimping tool. Each pack terminates five cameras end to end.\n\nNickel-plated bodies resist the corrosion that eventually causes a fuzzy picture on outdoor terminations.",
                'plus' => ['Screw-on fitting — no crimping tool needed on site', 'One pack terminates five cameras end to end', 'Nickel plating resists outdoor corrosion', 'DC power plugs included in the pack'],
                'spec' => ['Contents: 10 × BNC male + 10 × DC connector pairs', 'Type: screw-on (twist-on) for RG-59', 'Plating: nickel body, gold-plated pin', 'Impedance: 75Ω', 'Compatible cable: RG-59 / 3+1', 'Use: analog CCTV terminations'],
            ],
            $conn + [
                'name' => 'RJ45 Cat6 Connector (Pack of 100)',
                'model' => 'RJ45-C6-100', 'sku' => 'ACC-RJ-001',
                'unit' => 'pack', 'warranty' => 0, 'cost' => 350, 'price' => 550, 'alert' => 15, 'stock' => 120,
                'img' => 'cable', 'pic' => 4,
                'short' => '100 gold-plated Cat6 RJ45 plugs — the consumable every IP camera install burns through.',
                'desc' => "Gold-plated contacts and a Cat6-width body that actually accepts 23AWG solid conductors, which cheap Cat5e plugs do not.\n\nBuy these by the hundred; a 16-camera job with patch panels uses forty of them before anyone has tested anything.",
                'plus' => ['Cat6 body fits 23AWG solid conductor properly', 'Gold-plated contacts for a reliable gigabit link', '100 per pack — the right buying unit for installers', 'Works with any standard RJ45 crimping tool'],
                'spec' => ['Contents: 100 × RJ45 8P8C plugs', 'Category: Cat6, 23AWG compatible', 'Plating: 50μ gold-plated contacts', 'Rating: Gigabit Ethernet', 'Tool: standard RJ45 crimper', 'Use: IP camera and network terminations'],
            ],
            $conn + [
                'name' => 'CCTV Camera Junction Box (Metal)',
                'model' => 'JB-CAM-STD', 'sku' => 'ACC-JB-001',
                'unit' => 'pcs', 'warranty' => 0, 'cost' => 120, 'price' => 200, 'alert' => 25, 'stock' => 200,
                'img' => 'rack', 'pic' => 0,
                'short' => 'Weatherproof junction box that hides camera cable joints behind the bracket.',
                'desc' => "Every outdoor camera should have one: it keeps the BNC and DC joints out of the rain and out of sight, instead of taped to a wall where they corrode within a season.\n\nIt also gives an installer somewhere to leave service slack, so the camera can be re-aimed later without recabling.",
                'plus' => ['Keeps cable joints dry and out of sight', 'Room for service slack so the camera can be re-aimed later', 'Mounts between the wall and the camera bracket', 'Fits standard Hikvision, Dahua and Uniview brackets'],
                'spec' => ['Material: aluminium alloy with gasket', 'Mounting: between wall and camera bracket', 'Cable entry: rubber grommet', 'Protection: weather resistant', 'Compatibility: standard dome and bullet brackets', 'Finish: white powder coat'],
            ],

            // ---------------- PoE switch ----------------
            $poe + [
                'name' => 'TP-Link TL-SF1008P 8-Port PoE Switch',
                'model' => 'TL-SF1008P', 'sku' => 'NET-SW-001',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 2800, 'price' => 3500, 'alert' => 8, 'stock' => 40,
                'img' => 'switch', 'pic' => 0,
                'short' => '8-port switch with 4 PoE ports and a 57W budget — the cheapest way to power four IP cameras.',
                'desc' => "Four PoE ports at 15.4W each and four ordinary ports for the NVR and the uplink. For a four-camera IP job on a non-PoE recorder this is the standard, unglamorous answer.\n\nCheck the budget before quoting: 57W total covers four normal cameras but not four PTZs or heated housings.",
                'plus' => ['Powers four IP cameras with no separate adapters', 'Four extra ports for the NVR and router uplink', 'Fanless — silent enough for an office cupboard', 'Plug-and-play, no configuration needed'],
                'spec' => ['Ports: 8 × 10/100Mbps, 4 with PoE', 'PoE standard: 802.3af', 'PoE budget: 57W total', 'Switching capacity: 1.6Gbps', 'Cooling: fanless', 'Warranty: 3 years'],
            ],
            $poe + [
                'name' => 'Hikvision DS-3E0109P-E/M 8-Port PoE Switch',
                'model' => 'DS-3E0109P-E/M', 'sku' => 'NET-SW-002',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 4200, 'price' => 5200, 'alert' => 6, 'stock' => 26,
                'img' => 'switch', 'pic' => 1,
                'short' => '8 PoE ports with a 250m extend mode — reaches cameras a normal switch cannot.',
                'desc' => "The extend mode is the reason to pay more than the TP-Link: flip the switch and PoE ports carry 10Mbps up to 250 metres instead of the usual 100. That covers the far corner of a factory without a mid-span repeater.\n\n110W across eight ports handles a mixed set of cameras.",
                'plus' => ['250m extend mode reaches cameras beyond the 100m limit', '110W budget for eight mixed cameras', 'Per-port PoE watchdog reboots a hung camera automatically', 'Metal chassis, fanless and silent'],
                'spec' => ['Ports: 8 × 10/100Mbps PoE + 1 × Gigabit uplink', 'PoE standard: 802.3af/at', 'PoE budget: 110W total', 'Extend mode: 250m at 10Mbps', 'Cooling: fanless metal chassis', 'Warranty: 3 years'],
            ],
            $poe + [
                'name' => 'TP-Link TL-SG1016PE 16-Port Gigabit PoE Switch',
                'model' => 'TL-SG1016PE', 'sku' => 'NET-SW-003',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 12500, 'price' => 15000, 'alert' => 3, 'stock' => 12,
                'img' => 'switch', 'pic' => 2,
                'short' => '16-port gigabit switch with 8 PoE+ ports and a 110W budget, rack mountable and Easy Smart managed.',
                'desc' => "Gigabit throughout, which matters once 8MP cameras are on the network and a single 100Mbps uplink starts to choke playback.\n\nEasy Smart management adds VLANs and per-port monitoring, useful when the camera network shares a building's switching with office traffic.",
                'plus' => ['Gigabit throughout — no bottleneck with 4K cameras', 'VLAN support keeps camera traffic off the office network', '19-inch rack mountable with brackets included', 'PoE+ at 30W per port for PTZ and heated cameras'],
                'spec' => ['Ports: 16 × Gigabit, 8 with PoE+', 'PoE standard: 802.3af/at, up to 30W per port', 'PoE budget: 110W total', 'Switching capacity: 32Gbps', 'Management: Easy Smart (VLAN, QoS, monitoring)', 'Mounting: 19-inch rack or desktop'],
            ],

            // ---------------- Router ----------------
            $routerCat + [
                'name' => 'TP-Link Archer C6 AC1200 Dual Band Router',
                'model' => 'Archer C6', 'sku' => 'NET-RT-001',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 3200, 'price' => 3900, 'alert' => 8, 'stock' => 35,
                'img' => 'router', 'pic' => 0,
                'short' => 'AC1200 dual-band gigabit router — the usual pairing with an NVR for reliable remote phone viewing.',
                'desc' => "Remote viewing is only as good as the router behind it. Gigabit WAN and LAN ports mean the NVR's upload is not throttled by a 100Mbps router, which is the most common cause of stuttering remote playback.\n\nDual band also keeps Wi-Fi cameras off the congested 2.4GHz band where possible.",
                'plus' => ['Gigabit ports stop the NVR upload being bottlenecked', 'Dual band gives Wi-Fi cameras a clear 5GHz channel', 'Four external antennas for whole-flat coverage', '3-year warranty'],
                'spec' => ['Wi-Fi: AC1200 (867Mbps 5GHz + 300Mbps 2.4GHz)', 'Ports: 1 × Gigabit WAN + 4 × Gigabit LAN', 'Antennas: 4 × external fixed', 'Features: MU-MIMO, Beamforming, IPTV, VPN', 'Management: Tether app / web', 'Warranty: 3 years'],
            ],
            $routerCat + [
                'name' => 'TP-Link TL-WR840N 300Mbps Wireless Router',
                'model' => 'TL-WR840N', 'sku' => 'NET-RT-002',
                'unit' => 'pcs', 'warranty' => 1095, 'cost' => 1350, 'price' => 1750, 'alert' => 12, 'stock' => 60,
                'img' => 'router', 'pic' => 1,
                'short' => 'Entry 300Mbps N router for small installs where the NVR just needs an internet path.',
                'desc' => "The budget option when the customer only needs the recorder online for phone access and has no other network demands. 100Mbps ports are fine for a four-camera system's remote stream.\n\nDo not fit this where 4K cameras or heavy remote playback are expected — quote the Archer C6 instead.",
                'plus' => ['Cheapest reliable way to get an NVR online', 'Adequate for a 4-camera system remote stream', 'Simple setup for customers with no IT support', '3-year warranty'],
                'spec' => ['Wi-Fi: 300Mbps 2.4GHz 802.11n', 'Ports: 1 × 100Mbps WAN + 4 × 100Mbps LAN', 'Antennas: 2 × 5dBi fixed', 'Features: WPA/WPA2, parental controls, guest network', 'Management: Tether app / web', 'Warranty: 3 years'],
            ],

            // ---------------- Access control ----------------
            $attendance + [
                'name' => 'ZKTeco K40 Fingerprint Time Attendance Terminal',
                'model' => 'K40', 'sku' => 'ACS-TA-001',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 8500, 'price' => 10500, 'alert' => 4, 'stock' => 20,
                'img' => 'access', 'pic' => 0,
                'short' => 'Fingerprint and RFID attendance terminal for up to 1,000 staff with TCP/IP and USB report download.',
                'desc' => "The standard attendance machine for a small factory or office: 1,000 fingerprints, 1,000 cards, 60,000 stored punches, and reports that export to Excel through the free ZKTime software.\n\nIt also drives an electric lock directly, so one unit covers both attendance and door access.",
                'plus' => ['Handles up to 1,000 staff on fingerprint or RFID card', 'Exports attendance reports straight to Excel', 'Drives an electric lock for door access as well', 'TCP/IP, USB and optional Wi-Fi connectivity'],
                'spec' => ['Capacity: 1,000 fingerprints, 1,000 cards, 60,000 records', 'Display: 2.8-inch TFT colour', 'Communication: TCP/IP, USB host/client, optional Wi-Fi', 'Access control: built-in relay for electric lock', 'Software: ZKTime 5.0 included', 'Power: 12V DC 3A'],
            ],
            $attendance + [
                'name' => 'ZKTeco SpeedFace-V5L Face Recognition Terminal',
                'model' => 'SpeedFace-V5L', 'sku' => 'ACS-TA-002',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 22000, 'price' => 26000, 'alert' => 2, 'stock' => 10,
                'img' => 'access', 'pic' => 1,
                'short' => 'Contactless face and palm recognition terminal that identifies in under half a second, masks included.',
                'desc' => "Contactless attendance solved the shift-change queue problem: recognition in under 0.3 seconds means a hundred workers clock in without a bottleneck at the gate, and nobody touches a shared sensor.\n\nIt recognises staff wearing masks and works in complete darkness thanks to infrared illumination.",
                'plus' => ['Under 0.3s recognition — no queue at shift change', 'Works with masks on and in complete darkness', 'Contactless, so no shared-surface hygiene concern', 'Face, palm, card and password in one terminal'],
                'spec' => ['Capacity: 6,000 faces, 3,000 palms, 10,000 cards', 'Recognition speed: < 0.3 seconds', 'Display: 5-inch touch screen', 'Communication: TCP/IP, Wi-Fi, USB', 'Access control: Wiegand in/out, relay output', 'Power: 12V DC 3A or PoE'],
            ],
            $doorPhone + [
                'name' => 'Hikvision DS-KIS603-P Video Door Phone Kit',
                'model' => 'DS-KIS603-P', 'sku' => 'ACS-VD-001',
                'unit' => 'set', 'warranty' => 730, 'cost' => 26000, 'price' => 31000, 'alert' => 2, 'stock' => 8,
                'img' => 'intercom', 'pic' => 0,
                'short' => 'Two-wire video intercom kit with a 7-inch indoor monitor and an outdoor station that unlocks the gate.',
                'desc' => "A complete kit: outdoor door station with a 2MP camera, 7-inch indoor touch monitor, and a distributor. Two-wire cabling means it retrofits into an existing apartment's intercom wiring rather than needing new cable through finished walls.\n\nCalls also forward to the Hik-Connect app, so the gate can be answered from outside the flat.",
                'plus' => ['Two-wire retrofit — reuses existing intercom cabling', 'Calls forward to your phone through Hik-Connect', 'Unlocks the gate from the indoor monitor or the app', 'Records a snapshot of every visitor who rings'],
                'spec' => ['Kit: outdoor station + 7-inch indoor monitor + distributor', 'Outdoor camera: 2MP with night illumination', 'Indoor display: 7-inch touch screen', 'Wiring: two-wire (retrofit friendly)', 'App: Hik-Connect call forwarding', 'Lock output: electric strike / magnetic lock'],
            ],
            $doorPhone + [
                'name' => 'Dahua KTX01(S) Villa Video Intercom Kit',
                'model' => 'KTX01(S)', 'sku' => 'ACS-VD-002',
                'unit' => 'set', 'warranty' => 730, 'cost' => 18000, 'price' => 22000, 'alert' => 2, 'stock' => 8,
                'img' => 'intercom', 'pic' => 1,
                'short' => 'IP villa intercom kit with a 7-inch indoor monitor, card unlock and DMSS app forwarding.',
                'desc' => "An IP-based kit for a house or duplex, using standard Cat6 rather than proprietary cable — which suits new construction where the network is being run anyway.\n\nResidents can unlock with a card as well as from the monitor, and the whole thing sits on the same DMSS app as the Dahua cameras.",
                'plus' => ['Runs on standard Cat6, ideal for new construction', 'Card unlock as well as monitor and app', 'Shares the DMSS app with the Dahua camera system', 'Records visitor snapshots and missed calls'],
                'spec' => ['Kit: IP outdoor station + 7-inch indoor monitor', 'Outdoor camera: 2MP with IR illumination', 'Indoor display: 7-inch touch screen', 'Wiring: Cat6 / PoE', 'App: DMSS call forwarding', 'Unlock: monitor, app, or RFID card'],
            ],

            // ---------------- Display & rack ----------------
            $monitorCat + [
                'name' => 'Hikvision DS-D5022QE-B 21.5" FHD Surveillance Monitor',
                'model' => 'DS-D5022QE-B', 'sku' => 'MON-HK-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 12000, 'price' => 14500, 'alert' => 3, 'stock' => 12,
                'img' => 'monitor', 'pic' => 0,
                'short' => '21.5-inch 1080p monitor rated for 24/7 operation — a desktop monitor will not last on a DVR.',
                'desc' => "Office monitors are rated for eight hours a day; a security monitor runs continuously. This panel is specified for 24/7 duty and holds calibration over that duty cycle.\n\nHDMI, VGA and BNC inputs mean it connects to an IP NVR, an analog DVR, or a camera directly for on-site aiming.",
                'plus' => ['Rated for 24/7 operation, unlike a desktop monitor', 'HDMI, VGA and BNC inputs cover NVR, DVR and direct camera', 'Narrow bezel suits a multi-screen control wall', 'Built-in speakers for camera audio'],
                'spec' => ['Size: 21.5-inch LED', 'Resolution: 1920 × 1080 Full HD', 'Inputs: HDMI, VGA, BNC (composite)', 'Duty cycle: 24/7 continuous', 'Contrast: 3000:1', 'Mounting: VESA 100 × 100'],
            ],
            $monitorCat + [
                'name' => 'Dahua LM22-F200 21.5" FHD Monitor',
                'model' => 'LM22-F200', 'sku' => 'MON-DH-001',
                'unit' => 'pcs', 'warranty' => 730, 'cost' => 11000, 'price' => 13500, 'alert' => 3, 'stock' => 12,
                'img' => 'monitor', 'pic' => 1,
                'short' => '21.5-inch Full HD monitor with HDMI and VGA, built for continuous surveillance display.',
                'desc' => "Dahua's equivalent 21.5-inch panel, usually a little cheaper and functionally the same for a single-screen DVR position.\n\nNarrow bezels let two or three sit side by side on a small control desk without a wide gap between images.",
                'plus' => ['Built for continuous surveillance duty', 'Narrow bezel for side-by-side multi-screen desks', 'HDMI and VGA inputs for NVR or DVR', 'Slightly cheaper than the equivalent Hikvision panel'],
                'spec' => ['Size: 21.5-inch LED', 'Resolution: 1920 × 1080 Full HD', 'Inputs: HDMI, VGA', 'Duty cycle: continuous operation', 'Response time: 5ms', 'Mounting: VESA 100 × 100'],
            ],
            $rackCat + [
                'name' => '9U Wall Mount Network Rack Cabinet',
                'model' => 'RACK-9U-WM', 'sku' => 'RCK-9U-001',
                'unit' => 'pcs', 'warranty' => 365, 'cost' => 6500, 'price' => 8000, 'alert' => 2, 'stock' => 10,
                'img' => 'rack', 'pic' => 1,
                'short' => '9U lockable wall cabinet with a glass door and cooling fan — houses the NVR, switch and patch panel.',
                'desc' => "Where the whole system lives. A lockable 9U cabinet keeps the NVR, PoE switch, patch panel and power box off the floor, out of the dust, and away from whoever might want to unplug the recorder.\n\nThe fan matters in a Dhaka summer — an NVR in a sealed box without ventilation throttles and eventually drops recordings.",
                'plus' => ['Locks the recorder away from tampering', 'Fan-cooled — an NVR in a sealed box overheats', 'Keeps the switch, patch panel and power box tidy', 'Wall mounted, so it takes no floor space'],
                'spec' => ['Size: 9U wall mount, 600 × 450mm', 'Door: toughened glass with lock', 'Cooling: 1 × 220V fan fitted', 'Load capacity: up to 60kg', 'Entry: top and bottom cable entry', 'Finish: powder-coated steel'],
            ],
            $rackCat + [
                'name' => 'CCTV DVR Metal Lock Box',
                'model' => 'DVR-LB-STD', 'sku' => 'RCK-LB-001',
                'unit' => 'pcs', 'warranty' => 180, 'cost' => 900, 'price' => 1300, 'alert' => 8, 'stock' => 40,
                'img' => 'rack', 'pic' => 2,
                'short' => 'Ventilated steel lock box for a DVR — stops the recorder walking out with the evidence.',
                'desc' => "A break-in where the thief takes the DVR leaves the customer with cameras and no footage. A bolted lock box is a few hundred taka against that, and it is the accessory most often skipped and most often regretted.\n\nVentilation slots keep the recorder within temperature.",
                'plus' => ['Stops a thief removing the recorder and the evidence', 'Ventilated so the DVR stays within temperature', 'Bolts to a wall or inside a cupboard', 'Fits standard 1U desktop DVR and NVR chassis'],
                'spec' => ['Material: powder-coated steel', 'Fit: standard desktop DVR / NVR chassis', 'Ventilation: perforated top and side panels', 'Lock: keyed cam lock', 'Mounting: wall or shelf bolt-down', 'Cable entry: rear cut-out'],
            ],
        ];

        return $rows;
    }
}
