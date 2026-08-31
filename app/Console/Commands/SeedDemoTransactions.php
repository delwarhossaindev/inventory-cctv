<?php

namespace App\Console\Commands;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\LoyaltyTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedDemoTransactions extends Command
{
    protected $signature = 'app:seed-demo-transactions
                            {--days=60 : How many days of trading history to generate}
                            {--fresh : Wipe existing transactions and reset stock to the catalog baseline first}';

    protected $description = 'Generate realistic demo trading history — purchases, sales, returns, payments, expenses, quotations, installments and cash register shifts';

    private User $user;

    /** Every sale created, so returns/payments/installments can reference them. */
    private array $sales = [];

    public function handle(): int
    {
        // Deterministic: the same command always produces the same shop history.
        mt_srand(20260831);

        $this->user = User::where('email', 'admin@example.com')->first() ?? User::firstOrFail();

        if (Product::count() === 0) {
            $this->error('No products found. Run `php artisan app:seed-cctv-catalog` first.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->wipe();
        }

        $days = max(7, (int) $this->option('days'));

        $this->seedCustomers();
        $this->seedExpenses($days);
        $this->seedPurchases($days);
        $this->seedSales($days);
        $this->seedReturns();
        $this->seedPayments();
        $this->seedQuotations($days);
        $this->seedInstallments();
        $this->seedCashRegisters($days);

        $this->summary();

        return self::SUCCESS;
    }

    /**
     * Clear demo trading history and put stock back to the catalog's opening position.
     * The product catalog itself is never touched.
     */
    private function wipe(): void
    {
        $tables = [
            'installment_payments', 'installment_plans', 'loyalty_transactions',
            'sale_return_items', 'sale_returns', 'payments', 'sale_items', 'sales',
            'quotation_items', 'quotations', 'purchase_items', 'purchases',
            'expenses', 'expense_categories', 'cash_registers',
            'stock_movements', 'stock_batches',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }
        Schema::enableForeignKeyConstraints();

        // The catalog seeder rebuilds each product's two opening FIFO layers.
        $this->warn('Wiped existing transactions; rebuilding opening stock from the catalog...');
        Artisan::call('app:seed-cctv-catalog');
    }

    private function seedCustomers(): void
    {
        $customers = [
            ['Walk-in Customer', null, null],
            ['Rahim Electronics', '01711223344', 'Mirpur 10, Dhaka'],
            ['Nasir Uddin', '01812334455', 'Uttara Sector 7, Dhaka'],
            ['Green View Apartments', '01913445566', 'Dhanmondi 27, Dhaka'],
            ['Dhaka Textile Mills Ltd.', '01614556677', 'Tongi, Gazipur'],
            ['Karim Traders', '01715667788', 'Islampur, Old Dhaka'],
            ['Shahjalal Tower', '01816778899', 'Motijheel C/A, Dhaka'],
            ['Ayesha Begum', '01917889900', 'Bashundhara R/A, Dhaka'],
            ['Metro Pharmacy', '01618990011', 'Farmgate, Dhaka'],
            ['Fahim Hasan', '01719001122', 'Banani, Dhaka'],
            ['Sonali Garments Ltd.', '01820112233', 'Savar, Dhaka'],
        ];

        foreach ($customers as [$name, $phone, $address]) {
            Customer::updateOrCreate(
                ['name' => $name],
                ['phone' => $phone, 'address' => $address, 'status' => 'active']
            );
        }

        $this->line('  customers    ' . Customer::count());
    }

    private function seedExpenses(int $days): void
    {
        $categories = [
            'Shop Rent' => [[35000, 35000], 'monthly'],
            'Electricity Bill' => [[4200, 7800], 'monthly'],
            'Staff Salary' => [[48000, 52000], 'monthly'],
            'Internet Bill' => [[1500, 1500], 'monthly'],
            'Transport & Delivery' => [[300, 1800], 'often'],
            'Tools & Equipment' => [[800, 6500], 'rare'],
            'Tea & Refreshment' => [[150, 600], 'often'],
            'Marketing & Signage' => [[2000, 12000], 'rare'],
        ];

        $count = 0;

        foreach ($categories as $name => [$range, $frequency]) {
            $category = ExpenseCategory::firstOrCreate(['name' => $name]);

            $dates = match ($frequency) {
                // Fixed costs land on the 5th of each month covered by the window.
                'monthly' => $this->monthlyDates($days, 5),
                'often' => $this->randomDates($days, (int) ($days / 3)),
                default => $this->randomDates($days, max(2, (int) ($days / 15))),
            };

            foreach ($dates as $date) {
                $amount = $this->round(mt_rand($range[0], $range[1]), 10);

                $expense = Expense::create([
                    'expense_category_id' => $category->id,
                    'title' => $name . ' — ' . $date->format('M Y'),
                    'amount' => $amount,
                    'expense_date' => $date->toDateString(),
                    'note' => null,
                    'created_by' => $this->user->id,
                ]);

                $this->stamp($expense, $date);
                $count++;
            }
        }

        $this->line('  expenses     ' . $count . ' in ' . count($categories) . ' categories');
    }

    private function seedPurchases(int $days): void
    {
        $suppliers = Supplier::pluck('id', 'name');
        if ($suppliers->isEmpty()) {
            $this->warn('  no suppliers — skipping purchases');

            return;
        }

        // Restock the fast-moving lines a handful of times across the window.
        $count = 0;
        foreach ($this->randomDates($days, 9) as $date) {
            $supplierId = $suppliers->random();
            $products = Product::inRandomOrder()->limit(mt_rand(2, 5))->get();

            $lines = [];
            $subtotal = 0;
            foreach ($products as $product) {
                $qty = mt_rand(4, 20);
                // Suppliers quote a little above or below the recorded cost.
                $unitCost = $this->round((float) $product->purchase_price * (mt_rand(96, 108) / 100), 5);
                $lineSub = $qty * $unitCost;
                $subtotal += $lineSub;
                $lines[] = compact('product', 'qty', 'unitCost', 'lineSub');
            }

            $discount = $this->round($subtotal * (mt_rand(0, 3) / 100), 10);
            $total = $subtotal - $discount;
            // Two in five purchases are left partly unpaid, creating supplier dues.
            $paid = mt_rand(1, 5) <= 3 ? $total : $this->round($total * (mt_rand(40, 80) / 100), 100);

            $purchase = Purchase::create([
                'invoice_no' => 'PUR-' . str_pad((string) (Purchase::max('id') + 1), 5, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierId,
                'purchase_date' => $date->toDateString(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'paid' => $paid,
                'due' => max($total - $paid, 0),
                'status' => 'received',
                'note' => null,
            ]);

            $this->stamp($purchase, $date);

            foreach ($lines as $line) {
                $purchase->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['qty'],
                    'unit_cost' => $line['unitCost'],
                    'subtotal' => $line['lineSub'],
                ]);

                // Each received line becomes a FIFO batch dated to the purchase.
                $line['product']->stockIn(
                    $line['qty'],
                    $line['unitCost'],
                    'purchase',
                    $purchase,
                    'Purchase ' . $purchase->invoice_no,
                    $date
                );
            }

            $count++;
        }

        $this->line('  purchases    ' . $count);
    }

    private function seedSales(int $days): void
    {
        $customers = Customer::where('name', '!=', 'Walk-in Customer')->pluck('id')->all();
        $walkIn = Customer::where('name', 'Walk-in Customer')->value('id');
        $methods = ['cash', 'cash', 'cash', 'mobile', 'card', 'due'];

        $skipped = 0;

        for ($d = $days; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d);

            // Friday is the quiet day in a Dhaka market; weekends elsewhere are busy.
            $invoicesToday = $date->isFriday() ? mt_rand(0, 2) : mt_rand(1, 5);

            for ($i = 0; $i < $invoicesToday; $i++) {
                // Most counter sales are walk-ins; named accounts buy less often but bigger.
                $isAccount = mt_rand(1, 100) <= 35;
                $customerId = $isAccount ? $customers[array_rand($customers)] : $walkIn;

                $lineCount = $isAccount ? mt_rand(2, 5) : mt_rand(1, 3);
                $products = Product::where('stock_quantity', '>', 5)
                    ->inRandomOrder()->limit($lineCount)->get();

                $lines = [];
                $subtotal = 0;
                foreach ($products as $product) {
                    $maxQty = $isAccount ? 6 : 3;
                    $qty = min(mt_rand(1, $maxQty), (int) $product->stock_quantity);
                    if ($qty < 1) {
                        continue;
                    }
                    $unitPrice = (float) $product->sale_price;
                    $lineSub = $qty * $unitPrice;
                    $subtotal += $lineSub;
                    $lines[] = compact('product', 'qty', 'unitPrice', 'lineSub');
                }

                if (! $lines) {
                    $skipped++;
                    continue;
                }

                $discount = mt_rand(1, 100) <= 30 ? $this->round($subtotal * (mt_rand(1, 6) / 100), 10) : 0;
                $total = $subtotal - $discount;

                $method = $methods[array_rand($methods)];
                // Credit sales leave a balance; everything else is settled at the counter.
                $paid = $method === 'due'
                    ? $this->round($total * (mt_rand(20, 70) / 100), 100)
                    : $total;

                $sale = Sale::create([
                    'invoice_no' => 'INV-' . str_pad((string) (Sale::max('id') + 1), 5, '0', STR_PAD_LEFT),
                    'customer_id' => $customerId,
                    'sale_date' => $date->toDateString(),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => 0,
                    'total' => $total,
                    'paid' => $paid,
                    'due' => max($total - $paid, 0),
                    'payment_method' => $method,
                    'status' => 'completed',
                    'source' => 'pos',
                    'note' => null,
                ]);

                $this->stamp($sale, $date->copy()->addHours(mt_rand(10, 20))->addMinutes(mt_rand(0, 59)));

                foreach ($lines as $line) {
                    $product = $line['product'];
                    // FIFO stock-out returns the real cost of the units consumed.
                    $cogs = $product->stockOut($line['qty'], 'sale', $sale, 'Sale ' . $sale->invoice_no);

                    $sale->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $line['qty'],
                        'unit_price' => $line['unitPrice'],
                        'subtotal' => $line['lineSub'],
                        'cost_total' => $cogs,
                        'warranty_expires' => $product->warranty_days
                            ? $date->copy()->addDays($product->warranty_days)->toDateString()
                            : null,
                    ]);
                }

                // Loyalty: 1 point per ৳100 spent, for named accounts only.
                if ($isAccount) {
                    $this->stamp(LoyaltyTransaction::create([
                        'customer_id' => $customerId,
                        'points' => (int) floor($total / 100),
                        'type' => 'earned',
                        'description' => 'Earned on ' . $sale->invoice_no,
                    ]), $date);
                }

                $this->sales[] = $sale;
            }
        }

        $this->line('  sales        ' . count($this->sales) . ($skipped ? "  ({$skipped} skipped — insufficient stock)" : ''));
    }

    private function seedReturns(): void
    {
        // A few sales come back — a faulty camera, a wrong cable spec.
        $reasons = [
            'Faulty unit — no video output',
            'Wrong model supplied',
            'Customer changed requirement',
            'Damaged in transit',
        ];

        $candidates = collect($this->sales)->filter(fn ($s) => $s->created_at->lt(now()->subDays(3)))->shuffle()->take(5);
        $count = 0;

        foreach ($candidates as $sale) {
            $item = $sale->items()->inRandomOrder()->first();
            if (! $item || $item->quantity < 1) {
                continue;
            }

            $qty = max(1, (int) floor($item->quantity / 2)) ?: 1;
            $qty = min($qty, (int) $item->quantity);
            $lineSub = $qty * (float) $item->unit_price;
            $date = $sale->created_at->copy()->addDays(mt_rand(1, 3));

            $return = $sale->returns()->create([
                'return_date' => $date->toDateString(),
                'total' => $lineSub,
                'reason' => $reasons[array_rand($reasons)],
                'note' => null,
                'created_by' => $this->user->id,
            ]);

            $this->stamp($return, $date);

            $return->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $qty,
                'unit_price' => $item->unit_price,
                'subtotal' => $lineSub,
            ]);

            // Returned goods go back on the shelf at the product's cost.
            $product = Product::find($item->product_id);
            $product?->stockIn(
                $qty,
                (float) $product->purchase_price,
                'return',
                $return,
                'Sale return from ' . $sale->invoice_no,
                $date
            );

            $count++;
        }

        $this->line('  returns      ' . $count);
    }

    private function seedPayments(): void
    {
        $count = 0;

        // Customers paying down credit sales.
        foreach (Sale::where('due', '>', 0)->get() as $sale) {
            if (mt_rand(1, 100) > 60) {
                continue; // some dues are still outstanding
            }

            $amount = $this->round((float) $sale->due * (mt_rand(40, 100) / 100), 100);
            if ($amount <= 0) {
                continue;
            }

            $date = $sale->created_at->copy()->addDays(mt_rand(2, 12));
            if ($date->isFuture()) {
                $date = Carbon::today();
            }

            $this->stamp(Payment::create([
                'payable_type' => Sale::class,
                'payable_id' => $sale->id,
                'amount' => $amount,
                'method' => ['cash', 'mobile', 'card'][array_rand(['cash', 'mobile', 'card'])],
                'payment_date' => $date->toDateString(),
                'note' => 'Due collection',
                'created_by' => $this->user->id,
            ]), $date);

            $sale->forceFill([
                'paid' => (float) $sale->paid + $amount,
                'due' => max((float) $sale->due - $amount, 0),
            ])->save();

            $count++;
        }

        // Payments out to suppliers.
        foreach (Purchase::where('due', '>', 0)->get() as $purchase) {
            if (mt_rand(1, 100) > 65) {
                continue;
            }

            $amount = $this->round((float) $purchase->due * (mt_rand(50, 100) / 100), 100);
            if ($amount <= 0) {
                continue;
            }

            $date = $purchase->created_at->copy()->addDays(mt_rand(5, 20));
            if ($date->isFuture()) {
                $date = Carbon::today();
            }

            $this->stamp(Payment::create([
                'payable_type' => Purchase::class,
                'payable_id' => $purchase->id,
                'amount' => $amount,
                'method' => 'cash',
                'payment_date' => $date->toDateString(),
                'note' => 'Supplier payment',
                'created_by' => $this->user->id,
            ]), $date);

            $purchase->forceFill([
                'paid' => (float) $purchase->paid + $amount,
                'due' => max((float) $purchase->due - $amount, 0),
            ])->save();

            $count++;
        }

        $this->line('  payments     ' . $count);
    }

    private function seedQuotations(int $days): void
    {
        // Installation jobs quoted before the site survey.
        $jobs = [
            'CCTV package quote — 8 camera office setup',
            'CCTV package quote — 16 camera factory perimeter',
            'CCTV package quote — 4 camera shop front',
            'Apartment building — lobby and parking coverage',
            'Warehouse yard — PTZ and perimeter deterrence',
            'Showroom upgrade — 4K ColorVu replacement',
        ];

        $customers = Customer::where('name', '!=', 'Walk-in Customer')->pluck('id')->all();
        $statuses = ['draft', 'sent', 'accepted', 'sent', 'draft'];
        $count = 0;

        foreach ($this->randomDates($days, 6) as $i => $date) {
            $products = Product::where('stock_quantity', '>', 0)->inRandomOrder()->limit(mt_rand(3, 6))->get();

            $subtotal = 0;
            $lines = [];
            foreach ($products as $product) {
                $qty = mt_rand(1, 8);
                $unitPrice = (float) $product->sale_price;
                $lineSub = $qty * $unitPrice;
                $subtotal += $lineSub;
                $lines[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSub,
                ];
            }

            $discount = $this->round($subtotal * (mt_rand(0, 8) / 100), 100);
            $total = $subtotal - $discount;

            $quotation = Quotation::create([
                'quote_no' => 'QT-' . str_pad((string) (Quotation::max('id') + 1), 5, '0', STR_PAD_LEFT),
                'customer_id' => $customers[array_rand($customers)],
                'quote_date' => $date->toDateString(),
                'valid_until' => $date->copy()->addDays(15)->toDateString(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'status' => $statuses[$i % count($statuses)],
                'note' => $jobs[$i % count($jobs)],
                'created_by' => $this->user->id,
            ]);

            $this->stamp($quotation, $date);

            foreach ($lines as $line) {
                $quotation->items()->create($line);
            }

            $count++;
        }

        $this->line('  quotations   ' . $count);
    }

    private function seedInstallments(): void
    {
        // The biggest invoices get turned into monthly plans.
        $candidates = collect($this->sales)
            ->sortByDesc(fn ($s) => (float) $s->total)
            ->take(3);

        $count = 0;

        foreach ($candidates as $sale) {
            $total = (float) $sale->total;
            $downPayment = $this->round($total * 0.30, 100);
            $months = [3, 4, 6][$count % 3];
            $perMonth = $this->round(($total - $downPayment) / $months, 10);

            $plan = InstallmentPlan::create([
                'sale_id' => $sale->id,
                'customer_id' => $sale->customer_id,
                'total_amount' => $total,
                'down_payment' => $downPayment,
                'num_installments' => $months,
                'installment_amount' => $perMonth,
                'status' => 'active',
            ]);

            $this->stamp($plan, $sale->created_at);

            $allPaid = true;
            for ($n = 1; $n <= $months; $n++) {
                $due = $sale->created_at->copy()->addMonths($n);
                // Anything already past its due date has been collected.
                $isPaid = $due->isPast();
                $allPaid = $allPaid && $isPaid;

                InstallmentPayment::create([
                    'installment_plan_id' => $plan->id,
                    'installment_no' => $n,
                    'due_date' => $due->toDateString(),
                    'paid_date' => $isPaid ? $due->copy()->addDays(mt_rand(0, 4))->toDateString() : null,
                    'amount' => $perMonth,
                    'status' => $isPaid ? 'paid' : 'pending',
                ]);
            }

            if ($allPaid) {
                $plan->forceFill(['status' => 'completed'])->save();
            }

            $count++;
        }

        $this->line('  installments ' . $count . ' plans');
    }

    private function seedCashRegisters(int $days): void
    {
        // One closed shift per day for the last two weeks, reconciled against
        // that day's cash takings so the Diff column means something.
        $count = 0;

        for ($d = min($days, 14); $d >= 1; $d--) {
            $date = Carbon::today()->subDays($d);

            $opening = 5000;
            $takings = (float) Sale::whereDate('sale_date', $date->toDateString())
                ->where('payment_method', 'cash')
                ->sum('paid');

            $expected = $opening + $takings;
            // Most shifts balance; a couple are a few hundred taka out.
            $drift = mt_rand(1, 100) <= 25 ? mt_rand(-400, 300) : 0;

            $this->stamp(CashRegister::create([
                'user_id' => $this->user->id,
                'opening_balance' => $opening,
                'closing_balance' => $this->round($expected + $drift, 5),
                'opened_at' => $date->copy()->setTime(9, 30),
                'closed_at' => $date->copy()->setTime(21, 0),
                // The Diff column shows closing − opening (the day's takings), so the
                // count discrepancy has to be spelled out here to mean anything.
                'note' => $this->shiftNote($drift),
            ]), $date->copy()->setTime(21, 0));

            $count++;
        }

        $this->line('  cash shifts  ' . $count);
    }

    // ------------------------------------------------------------------
    // helpers
    // ------------------------------------------------------------------

    /** N distinct random dates within the window, oldest first. */
    private function randomDates(int $days, int $count): array
    {
        $picked = [];
        $guard = 0;
        while (count($picked) < $count && $guard++ < $count * 20) {
            $picked[mt_rand(0, $days)] = true;
        }

        $offsets = array_keys($picked);
        rsort($offsets);

        return array_map(fn ($o) => Carbon::today()->subDays($o), $offsets);
    }

    /** The same day-of-month in each month the window covers, oldest first. */
    private function monthlyDates(int $days, int $dayOfMonth): array
    {
        $dates = [];
        $cursor = Carbon::today()->subDays($days)->startOfMonth();
        $end = Carbon::today();

        while ($cursor->lte($end)) {
            $date = $cursor->copy()->day(min($dayOfMonth, $cursor->daysInMonth));
            if ($date->betweenIncluded(Carbon::today()->subDays($days), $end)) {
                $dates[] = $date;
            }
            $cursor->addMonth();
        }

        return $dates;
    }

    private function round(float $n, int $to): float
    {
        return round($n / $to) * $to;
    }

    /** Spell out the count discrepancy, since the Diff column shows takings, not variance. */
    private function shiftNote(int $drift): string
    {
        if ($drift === 0) {
            return 'Balanced — counted cash matched expected';
        }

        return $drift > 0
            ? 'Counted ৳' . number_format($drift) . ' over expected — checking receipts'
            : 'Counted ৳' . number_format(abs($drift)) . ' short — under review';
    }

    /**
     * Backdate a record's timestamps.
     *
     * None of these models list created_at/updated_at in $fillable, so passing them
     * to create() is silently dropped and every row lands on today. Reports read the
     * business date columns, but list screens order by created_at — so they have to
     * be forced in after the fact.
     */
    private function stamp($model, Carbon $at)
    {
        $model->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();

        return $model;
    }

    private function summary(): void
    {
        $revenue = (float) Sale::sum('total');
        $cogs = (float) DB::table('sale_items')->sum('cost_total');
        $returns = (float) SaleReturn::sum('total');
        $expenses = (float) Expense::sum('amount');
        $net = $revenue - $cogs - $returns - $expenses;

        $this->newLine();
        $this->info('Demo trading history created.');
        $this->newLine();
        $this->table(
            ['Figure', 'Amount (BDT)'],
            [
                ['Revenue', number_format($revenue, 2)],
                ['− Cost of goods sold (FIFO)', number_format($cogs, 2)],
                ['− Returns', number_format($returns, 2)],
                ['− Expenses', number_format($expenses, 2)],
                ['= Net profit', number_format($net, 2)],
                ['', ''],
                ['Outstanding from customers', number_format((float) Sale::sum('due'), 2)],
                ['Outstanding to suppliers', number_format((float) Purchase::sum('due'), 2)],
                ['Stock on hand (units)', number_format((float) Product::sum('stock_quantity'))],
            ]
        );
    }
}
