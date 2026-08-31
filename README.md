<div align="center">

```
██╗███╗   ██╗██╗   ██╗███████╗███╗   ██╗████████╗ ██████╗ ██████╗ ██╗   ██╗
██║████╗  ██║██║   ██║██╔════╝████╗  ██║╚══██╔══╝██╔═══██╗██╔══██╗╚██╗ ██╔╝
██║██╔██╗ ██║██║   ██║█████╗  ██╔██╗ ██║   ██║   ██║   ██║██████╔╝ ╚████╔╝ 
██║██║╚██╗██║╚██╗ ██╔╝██╔══╝  ██║╚██╗██║   ██║   ██║   ██║██╔══██╗  ╚██╔╝  
██║██║ ╚████║ ╚████╔╝ ███████╗██║ ╚████║   ██║   ╚██████╔╝██║  ██║   ██║   
╚═╝╚═╝  ╚═══╝  ╚═══╝  ╚══════╝╚═╝  ╚═══╝   ╚═╝    ╚═════╝ ╚═╝  ╚═╝   ╚═╝  
```

**JM INTERNATIONAL — Inventory & Point-of-Sale platform for a CCTV & security-equipment shop, built on Laravel 10**

*Cameras · Recorders · Storage · Cabling · Network gear · Access control — priced, stocked and sold from one panel.*

[![Laravel](https://img.shields.io/badge/Laravel-10-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![SQLite](https://img.shields.io/badge/SQLite-Default_DB-003B57?style=for-the-badge&logo=sqlite&logoColor=white)](https://sqlite.org)
[![Sanctum](https://img.shields.io/badge/Sanctum-API_Auth-F05340?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/sanctum)
![License](https://img.shields.io/badge/License-MIT-22c55e?style=for-the-badge)

### 📖 [**Read the User Manual →**](docs/USER-MANUAL.md)  ·  [**বাংলা ম্যানুয়াল →**](docs/USER-MANUAL-BN.md)

*A screen-by-screen walkthrough of all 45 screens, with screenshots. Available in English and Bangla.*

</div>

---

## 🗺️ System at a Glance

```
╔══════════════════════════════════════════════════════════════════════════╗
║                 JM INTERNATIONAL · CCTV INVENTORY & POS                 ║
╠══════════════════╦═══════════════════╦════════════════╦═════════════════╣
║   👤 Auth        ║   📦 Inventory    ║   🛒 POS       ║   💰 Finance   ║
║   ─────────      ║   ─────────────   ║   ────────     ║   ──────────   ║
║   Login          ║   Products        ║   Quick Sale   ║   Due Collect  ║
║   Roles (RBAC)   ║   Categories      ║   Barcode Scan ║   EMI / Plans  ║
║   Login History  ║   FIFO Stock      ║   Multi-Pay    ║   Expenses     ║
║   Activity Log   ║   Stock Batches   ║   Invoice A4   ║   P&L Report   ║
║   Profile Mgmt   ║   Adjustments     ║   Thermal 80mm ║   Quotations   ║
╠══════════════════╬═══════════════════╬════════════════╬═════════════════╣
║   📊 Reports     ║   👥 CRM          ║   ⚙️ Admin     ║   🔌 API       ║
║   ─────────      ║   ────────────    ║   ──────────   ║   ──────────   ║
║   Sales & Profit ║   Customer Ledger ║   Multi-Branch ║   Products     ║
║   Daily Summary  ║   Supplier Ledger ║   SMS Gateway  ║   Categories   ║
║   Top Products   ║   Loyalty Points  ║   DB Backup    ║   Stock Check  ║
║   Stock Value    ║   Warranty Track  ║   Settings     ║   Sanctum Auth ║
║   Dashboard      ║   Purchase Hist.  ║   User Manual  ║   REST v1      ║
╚══════════════════╩═══════════════════╩════════════════╩═════════════════╝
```

---

## ⚡ Feature Highlights

### 🛒 Point of Sale
| | Feature | Details |
|---|---------|---------|
| ⚡ | **Instant Search** | Find products by name, SKU, barcode, or model |
| 📷 | **Barcode Scan** | EAN-13 / CODE128 support out of the box |
| 💳 | **Multi-Payment** | Cash · Card · Mobile Banking · Due |
| 👤 | **Quick Customer** | Register customer mid-sale without leaving POS |
| 🧾 | **Invoice (A4)** | Branded PDF with company logo, QR, terms |
| 🖨️ | **Thermal Receipt** | 58 mm & 80 mm POS printer optimized |
| 🔄 | **Auto Stock-Out** | FIFO deduction + warranty expiry auto-calculated |

### 📦 Inventory & Stock
| | Feature | Details |
|---|---------|---------|
| 📊 | **FIFO Costing** | Per-batch cost tracking — exact COGS on every sale |
| 📋 | **Movement Log** | Full audit trail: every stock-in, out, and adjustment |
| ⚠️ | **Low Stock Alerts** | Threshold-based dashboard warnings |
| 🔧 | **Manual Adjustments** | Add / subtract / set stock for corrections |
| 🏷️ | **Barcode Labels** | Auto-generate EAN-13 labels — A4 & thermal layouts |
| 📥 | **Bulk Import** | Spreadsheet upload for products & purchase orders |

### 💰 Finance & Accounting
| | Feature | Details |
|---|---------|---------|
| 💵 | **Due Collection** | Partial payments on sales and supplier purchases |
| 📅 | **Installment / EMI** | Monthly plans with due-date tracking |
| 🧾 | **Expense Tracking** | Categorized spend; create categories on the fly |
| 📊 | **Profit & Loss** | Revenue − COGS − Returns − Expenses = Net Profit |
| 💱 | **Quotations** | Printable price estimates before sale confirmation |
| 🏦 | **Cash Register** | Shift open / close with full balance accountability |

### 📈 Reports & Analytics
| | Feature | Details |
|---|---------|---------|
| 📊 | **Dashboard Charts** | 7-day sales trend line + category doughnut (Chart.js 4) |
| 💹 | **Sales & Profit** | Per-sale revenue, COGS, and margin % |
| 📅 | **Daily Summary** | Day-by-day revenue breakdown |
| 🏆 | **Top Products** | Best sellers ranked by qty & revenue |
| 📦 | **Stock Valuation** | Current stock value summed per FIFO batch |
| 📊 | **P&L Statement** | Complete printable profit & loss report |

### 👥 CRM & Contacts
| | Feature | Details |
|---|---------|---------|
| 📒 | **Customer Ledger** | Full purchase history · total · paid · outstanding |
| 📒 | **Supplier Ledger** | Full purchase history · total · paid · outstanding |
| ⭐ | **Loyalty Points** | Configurable points-per-purchase & redemption value |
| 🛡️ | **Warranty Tracking** | Auto expiry date per sale item — searchable |

### 📖 Accounting & Operations
| | Feature | Details |
|---|---------|---------|
| 📒 | **Day Book** | Chronological record of all financial transactions |
| ⚖️ | **Trial Balance** | Debit vs. credit totals verification |
| 📐 | **Units of Measure** | Seeded for the trade: Piece · Box · Set · Pack · Roll · Yard |
| 📦 | **FIFO Batches** | View all stock batches with received date, unit cost, and remaining qty |

### 📱 Responsive / Mobile
| | Feature | Details |
|---|---------|---------|
| 📲 | **Off-canvas Sidebar** | Slides in behind a hamburger below 992 px, with backdrop |
| 🛒 | **Mobile POS** | Product grid and cart capped at 42 vh / 38 vh so both stay usable |
| 📌 | **Sticky Checkout** | Total and **Complete Sale** pinned to the bottom of the screen on phones |
| 📊 | **Adaptive Tables** | Product list sheds columns by breakpoint; the rest scroll horizontally |
| 🌓 | **Dark Mode** | Bootstrap `data-bs-theme` toggle, persisted in `localStorage` |

### 🔐 Security & Administration
| | Feature | Details |
|---|---------|---------|
| 🛡️ | **RBAC** | Spatie Permissions — 4 built-in roles, fully customizable |
| 📝 | **Activity Log** | Who · what · when · IP — every action recorded |
| 🔑 | **Login History** | User · timestamp · IP · browser for all logins |
| 🏢 | **Multi-Branch** | Add and switch between multiple store locations |
| 📱 | **SMS Gateway** | BulkSMSBD · SSL Wireless · Twilio · Custom HTTP |
| 💾 | **DB Backup** | One-click SQLite download from the dashboard |
| 📘 | **In-App Manual** | Scroll-spy user guide in English & Bangla · PDF export |
| 📖 | **User Manual** | Full illustrated guide — every screen, with screenshots · [English](docs/USER-MANUAL.md) · [বাংলা](docs/USER-MANUAL-BN.md) |

---

## 📷 The CCTV Catalog

`php artisan migrate --seed` builds a ready-to-trade shop: **63 products across 30 categories**, each with a real model number, BDT pricing, warranty period, EAN-13 barcode, Unsplash photography and a written spec sheet.

```
CCTV Camera ──┬─ IP Camera ────────┬─ Dome Camera            5
              │                    ├─ Bullet Camera          5
              │                    └─ PTZ Camera             3
              ├─ HD Analog Camera ─┬─ Dome Camera            3
              │                    └─ Bullet Camera          3
              └─ Wi-Fi Camera ─────┬─ Indoor Camera          4
                                   └─ Outdoor Camera         3
Recorder ─────┬─ NVR                                         5
              └─ DVR                                         4
Storage ──────┬─ Surveillance HDD                            4
              └─ Memory Card                                 2
Power & Cable ┬─ Power Supply                                3
              ├─ Cable                                       3
              └─ Connector & Accessories                     3
Network ──────┬─ PoE Switch                                  3
              └─ Router                                      2
Access Ctrl ──┬─ Time Attendance                             2
              └─ Video Door Phone                            2
Display & Rack┬─ Monitor                                     2
              └─ Rack & Box                                  2
                                                     ─────────
                                                            63
```

**Brands stocked** — Hikvision · Dahua · Uniview · Ezviz · TP-Link (Tapo & Archer) · Xiaomi · Seagate SkyHawk · WD Purple · Toshiba · SanDisk · Samsung · ZKTeco

**What every product carries**

| Field | Example |
|-------|---------|
| Name & model | `Hikvision DS-2CD1043G2-LIU 4MP ColorVu Bullet Camera` · `DS-2CD1043G2-LIU` |
| SKU & barcode | `IPC-BL-001` · `2000000001135` (auto EAN-13) |
| Pricing | Purchase ৳4,300 → Sale ৳5,400 |
| Stock | 40 pcs across **two FIFO layers** (older batch 8 % cheaper) |
| Warranty | 730 days |
| Media | 1 main + 3 gallery photos from Unsplash |
| Copy | Short description, description, advantages, specifications — stored as HTML for the TinyMCE editor |

> Prices reflect the Dhaka market and are a starting point — adjust them from **Products → Bulk Pricing**.

Re-seed the catalog at any time:

```bash
php artisan app:seed-cctv-catalog           # idempotent — updates in place, keeps ids
php artisan app:seed-cctv-catalog --fresh   # ⚠️ wipes the catalog AND every sale,
                                            #    purchase and quotation referencing it
```

---

## 🔄 Data Flow

```
  ┌────────────┐     purchase      ┌────────────┐     stock-in      ┌─────────────┐
  │  Supplier  │ ─────────────────▶│  Purchase  │ ─────────────────▶│ Stock Batch │
  └────────────┘                   └────────────┘                   │  (FIFO)     │
                                                                     └──────┬──────┘
                                                                            │ deduct
  ┌────────────┐      sale         ┌────────────┐     stock-out             ▼
  │  Customer  │ ─────────────────▶│  POS Sale  │ ─────────────────▶┌─────────────┐
  └────────────┘                   └─────┬──────┘                   │  Movement   │
                                         │                           │    Log      │
                 ┌───────────────────────┼────────────────────┐     └─────────────┘
                 ▼                       ▼                    ▼
          ┌────────────┐        ┌────────────────┐   ┌──────────────┐
          │  Invoice   │        │ Warranty Set   │   │  Loyalty Pts │
          │  Receipt   │        │ (auto expiry)  │   │  Earned      │
          └────────────┘        └────────────────┘   └──────────────┘
                                         │
  ┌──────────────────────────────────────┘
  │
  ▼
  Revenue − COGS (FIFO) − Returns − Expenses  ═══▶  💰 Net Profit
```

---

## 💸 Money Flow

```
        💵 MONEY IN                              💸 MONEY OUT
     ─────────────────                        ─────────────────────
  ┌──────────────────────┐                 ┌───────────────────────┐
  │ 🛒  Sale Revenue      │◀── POS/Invoice  │ 📦  Purchase Cost     │──▶ Supplier
  ├──────────────────────┤                 ├───────────────────────┤
  │ 💳  Due Collection    │◀── Payments     │ 🏠  Operating Expenses│──▶ Rent/Bills
  ├──────────────────────┤                 ├───────────────────────┤
  │ 📅  EMI Installments  │◀── Schedules    │ ↩️  Customer Refunds  │──▶ Returns
  └──────────┬───────────┘                 └───────────┬───────────┘
             │                                         │
             └──────────────────┬────────────────────── ┘
                                ▼
                    ┌───────────────────────┐
                    │        📊 NET PROFIT   │
                    │                        │
                    │  Revenue               │
                    │  − Cost of Goods Sold  │
                    │  − Expenses            │
                    │  − Returns             │
                    │  ═══════════════════   │
                    │        = 💰 Profit     │
                    └───────────────────────┘
```

---

## 🏗️ Architecture

```
inventory-cctv/
├── app/
│   ├── Console/Commands/
│   │   ├── SeedCctvCatalog          ← CCTV catalog + photos + FIFO layers
│   │   ├── ImportProducts           ← Legacy xlsx importer (kept, unused)
│   │   ├── SeedFifoData             ← Legacy price/batch seeder (kept, unused)
│   │   └── RebuildStockBatches      ← Backfill batches for un-batched stock
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController      ← KPIs, chart data
│   │   │   │   ├── PosController            ← Point of Sale
│   │   │   │   ├── ProductController        ← CRUD, bulk import, labels
│   │   │   │   ├── CategoryController       ← 3-level hierarchy
│   │   │   │   ├── PurchaseController       ← POs, bulk import
│   │   │   │   ├── SaleController           ← View, invoice, receipt
│   │   │   │   ├── SaleReturnController     ← Returns + stock restore
│   │   │   │   ├── StockController          ← View, adjust, movements
│   │   │   │   ├── PaymentController        ← Due collection
│   │   │   │   ├── InstallmentController    ← EMI plans
│   │   │   │   ├── ExpenseController        ← Categorized expenses
│   │   │   │   ├── QuotationController      ← Price estimates
│   │   │   │   ├── CashRegisterController   ← Shift open/close
│   │   │   │   ├── ReportController         ← 6 reports + P&L
│   │   │   │   ├── CustomerController       ← CRUD + ledger
│   │   │   │   ├── SupplierController       ← CRUD + ledger
│   │   │   │   ├── UserController           ← User management
│   │   │   │   ├── RoleController           ← Role management
│   │   │   │   ├── ProfileController        ← Profile & password
│   │   │   │   ├── SettingController        ← Business config, backup
│   │   │   │   ├── ActivityLogController    ← Audit trail
│   │   │   │   ├── LoginHistoryController   ← Login tracking
│   │   │   │   ├── BranchController         ← Multi-store
│   │   │   │   └── ManualController         ← In-app docs (EN + BN)
│   │   │   └── Api/
│   │   │       └── ProductApiController     ← REST API v1
│   │   └── Middleware/
│   └── Models/
│       ├── Product          ← Barcode, stockIn/stockOut (FIFO)
│       ├── Category         ← 3-level parent/child tree
│       ├── Sale             ← Header with totals, status
│       ├── SaleItem         ← warranty_expires, FIFO batch ref
│       ├── Purchase         ← Header with supplier
│       ├── PurchaseItem     ← Auto stock-in on save
│       ├── StockBatch       ← FIFO batches with unit cost
│       ├── StockMovement    ← Full movement audit log
│       ├── Payment          ← Polymorphic (sale / purchase)
│       ├── SaleReturn       ← Return header
│       ├── SaleReturnItem   ← Stock restore on save
│       ├── Expense          ← With ExpenseCategory
│       ├── Quotation        ← Price estimate with items
│       ├── InstallmentPlan  ← EMI schedule
│       ├── CashRegister     ← Shift records
│       ├── ActivityLog      ← Action tracking (who/what/when/IP)
│       ├── LoginHistory     ← Session / browser tracking
│       ├── LoyaltyTransaction ← Points earned & redeemed
│       ├── Branch           ← Multi-store locations
│       ├── Setting          ← Key-value app config
│       ├── Customer         ← With ledger totals
│       ├── Supplier         ← With ledger totals
│       └── User             ← Spatie HasRoles
├── routes/
│   ├── web.php              ← All admin panel routes
│   └── api.php              ← REST API v1 (Sanctum)
├── resources/views/         ← Blade templates (Bootstrap 5.3, mobile-first)
├── database/
│   ├── migrations/
│   └── seeders/             ← Roles, users, then the CCTV catalog
└── public/
```

---

## 👤 Roles & Permissions

| Role | Dashboard | POS | Inventory | Finance | Reports | Users | Settings |
|------|:---------:|:---:|:---------:|:-------:|:-------:|:-----:|:--------:|
| 🌟 **Super Admin** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| 👔 **Manager** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |
| 📦 **Storekeeper** | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| 🛒 **Salesperson** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

> Roles and permissions are managed via [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) and are fully customizable from the admin panel.

---

## 🔌 REST API

All endpoints require a **Sanctum Bearer Token** in the `Authorization` header.

```
Base URL:  /api/v1

GET  /products                    Active products, paginated
                                  ?q=        name or SKU (partial match)
                                  ?category= main_category_id
                                  ?per_page= default 20
GET  /products/{id}               Single product with its main category
GET  /products/{id}/stock         Real-time stock availability
GET  /categories                  Root categories with children.children nested
```

**Example request:**
```bash
curl -H "Authorization: Bearer <token>" \
     "https://your-domain.com/api/v1/products?q=ColorVu&per_page=1"
```

**Example response** — a standard Laravel paginator; each item is the full product record:
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 113,
      "name": "Hikvision DS-2CD1043G2-LIU 4MP ColorVu Bullet Camera",
      "slug": "hikvision-ds-2cd1043g2-liu-4mp-colorvu-bullet-camera",
      "model": "DS-2CD1043G2-LIU",
      "sku": "IPC-BL-001",
      "barcode": "2000000001135",
      "purchase_price": "4300.00",
      "sale_price": "5400.00",
      "stock_quantity": 40,
      "alert_quantity": 6,
      "unit": "pcs",
      "warranty_days": 730,
      "status": "active",
      "image_url": "https://images.unsplash.com/photo-1496368077930-...",
      "gallery_images": ["...", "...", "..."],
      "main_category": { "id": 59, "name": "CCTV Camera", "level": 1 }
    }
  ],
  "per_page": 1,
  "total": 1
}
```

**Stock check** — `GET /products/113/stock`:
```json
{
  "id": 113,
  "name": "Hikvision DS-2CD1043G2-LIU 4MP ColorVu Bullet Camera",
  "stock_quantity": 40,
  "in_stock": true,
  "sale_price": "5400.00"
}
```

---

## 🛠️ Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend** | Laravel 10 · PHP 8.1+ | Application framework |
| **Frontend** | Bootstrap 5.3 · Bootstrap Icons 1.11 | UI components & icons |
| **Charts** | Chart.js 4 | Sales trend & category charts |
| **Editor** | TinyMCE (GPL build) | Rich-text product descriptions & specs |
| **Photos** | Unsplash CDN | Catalog imagery via `images.unsplash.com` |
| **Barcodes** | JsBarcode | EAN-13 / CODE128 generation |
| **PDF** | mPDF | Invoice, receipt & report export |
| **Database** | SQLite (default) · MySQL compatible | Data persistence |
| **Auth** | Laravel Sanctum | Session + API token auth |
| **RBAC** | Spatie Laravel Permission | Role & permission management |
| **SMS** | BulkSMSBD · SSL Wireless · Twilio · Custom | Notification gateway |

---

## ⚡ Quick Start

### Requirements
```
PHP      ≥ 8.1
Composer ≥ 2.x
SQLite   (bundled with PHP — zero config)
```

### Install

```bash
# 1. Clone the repository
git clone <repo-url> inventory-cctv
cd inventory-cctv

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Create the SQLite database file
touch database/database.sqlite          # Linux/macOS
# Windows: type nul > database\database.sqlite

# 5. Run migrations and seed the CCTV catalog
#    (roles + users, then 63 products across 30 categories with FIFO stock)
php artisan migrate --seed

# 6. Link public storage
php artisan storage:link

# 7. Start the development server
php artisan serve
```

Open **http://localhost:8000** in your browser.

### Default Credentials

| Role | Email | Password |
|------|-------|----------|
| 🌟 Super Admin | `admin@example.com` | `password` |
| 🛒 Salesperson | `cashier@example.com` | `password` |

> ⚠️ **Change all default passwords immediately after your first login.**

### Next step

Work through the User Manual — it walks every screen in order, from the first sign-in to closing the cash register, and explains how FIFO costing decides your profit figures.

- 🇬🇧 **[English](docs/USER-MANUAL.md)** — for developers and administrators
- 🇧🇩 **[বাংলা](docs/USER-MANUAL-BN.md)** — plain Bangla, written for counter staff

---

## 🗃️ Database

The application ships with **SQLite** by default — no database server required. To switch to MySQL, update your `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory
DB_USERNAME=root
DB_PASSWORD=secret
```

Then run `php artisan migrate --seed`.

---

## 🧰 Artisan Commands

| Command | Purpose |
|---------|---------|
| `php artisan app:seed-cctv-catalog` | Seed / refresh the CCTV catalog — categories, products, photos, prices, suppliers, units and two FIFO layers per product. Idempotent. |
| `php artisan app:seed-cctv-catalog --fresh` | ⚠️ Wipe the catalog **and every sale, purchase, return, payment and quotation that references it**, then reseed. |
| `php artisan app:rebuild-stock-batches` | Create opening FIFO batches for any current stock not yet backed by a batch. |
| `php artisan app:import-products` | Legacy: import products from `public/products.xlsx`. Superseded by the catalog seeder, kept for spreadsheet imports. |
| `php artisan app:seed-fifo-data` | Legacy: overwrite every product's price and rebuild two FIFO layers. Kept for demo resets. |

---

## 📁 Key Environment Variables

```ini
APP_NAME="JM INTERNATIONAL"
APP_URL=http://localhost

# Database — SQLite (default).
# DB_DATABASE must be an ABSOLUTE path to the .sqlite file; a relative or
# stale path will silently read and write a different database than you expect.
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

# SMS Gateway (optional)
SMS_DRIVER=bulksmsbd          # bulksmsbd | ssl | twilio | custom
SMS_API_KEY=your_api_key

# Mail (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

---

## 📄 License

Released under the **MIT License**.

> ℹ️ No `LICENSE` file is committed yet — add one at the repository root to make the terms binding.

---

<div align="center">

Built with ❤️ using [Laravel](https://laravel.com) · [Bootstrap](https://getbootstrap.com) · [Chart.js](https://chartjs.org)

</div>
