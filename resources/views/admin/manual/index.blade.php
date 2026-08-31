@extends('layouts.app')
@section('title', 'User Manual')
@section('heading', 'User Manual')

@push('styles')
<style>
    /* ===== BASE STYLES ===== */
    .manual-hero {
        background: linear-gradient(135deg, #1f2937 0%, #374151 50%, #0d6efd 100%);
        border-radius: .75rem;
        padding: 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
        animation: heroSlide .8s ease;
    }
    .manual-hero h1 { font-size: 2rem; font-weight: 800; margin-bottom: .5rem; }
    .manual-hero p { opacity: .85; font-size: 1.05rem; margin-bottom: 0; }

    .manual-nav { position: sticky; top: 80px; max-height: calc(100vh - 120px); overflow-y: auto; }
    .manual-nav .nav-link { color: #4b5563; padding: .4rem .75rem; font-size: .82rem; border-left: 3px solid transparent; border-radius: 0; transition: all .2s; }
    .manual-nav .nav-link:hover, .manual-nav .nav-link.active { color: #0d6efd; background: #eff6ff; border-left-color: #0d6efd; }
    .manual-nav .section-head { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: #9ca3af; padding: .65rem .75rem .2rem; font-weight: 700; }

    .manual-section { scroll-margin-top: 90px; }
    .manual-section .section-title { display: flex; align-items: center; gap: .75rem; font-size: 1.3rem; font-weight: 700; color: #1f2937; margin-bottom: 1.25rem; padding-bottom: .75rem; border-bottom: 2px solid #e5e7eb; }
    .manual-section .section-title .icon-box { width: 44px; height: 44px; border-radius: .6rem; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #fff; flex-shrink: 0; }

    .step-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: .6rem; padding: 1rem 1.25rem; margin-bottom: .75rem; display: flex; gap: 1rem; align-items: flex-start; transition: all .25s; }
    .step-card:hover { border-color: #0d6efd; transform: translateX(4px); box-shadow: 0 2px 8px rgba(13,110,253,.1); }
    .step-num { width: 32px; height: 32px; border-radius: 50%; background: #0d6efd; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; flex-shrink: 0; }
    .step-card h6 { font-weight: 700; margin-bottom: .2rem; font-size: .95rem; }
    .step-card p { margin-bottom: 0; color: #6b7280; font-size: .88rem; line-height: 1.5; }

    .tip-box { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-left: 4px solid #3b82f6; border-radius: 0 .5rem .5rem 0; padding: .85rem 1.25rem; margin: 1rem 0; font-size: .88rem; color: #1e40af; }
    .tip-box i { margin-right: .4rem; }
    .warning-box { background: linear-gradient(135deg, #fef3c7, #fde68a33); border-left: 4px solid #f59e0b; border-radius: 0 .5rem .5rem 0; padding: .85rem 1.25rem; margin: 1rem 0; font-size: .88rem; color: #92400e; }
    .kbd { background: #1f2937; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: .8rem; font-family: monospace; }

    .role-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .78rem; font-weight: 600; }
    .role-super { background: #fef3c7; color: #92400e; }
    .role-manager { background: #dbeafe; color: #1e40af; }
    .role-store { background: #d1fae5; color: #065f46; }
    .role-sales { background: #ede9fe; color: #5b21b6; }

    .feature-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .6rem; margin: 1rem 0; }
    .feature-item { background: #fff; border: 1px solid #e5e7eb; border-radius: .5rem; padding: .7rem .85rem; display: flex; align-items: center; gap: .5rem; font-size: .82rem; font-weight: 500; transition: all .25s; }
    .feature-item:hover { border-color: #0d6efd; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .feature-item i { font-size: 1rem; color: #0d6efd; }

    /* ===== ANIMATIONS ===== */
    @keyframes heroSlide { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes pulseGlow { 0%, 100% { box-shadow: 0 0 0 0 rgba(13,110,253,.3); } 50% { box-shadow: 0 0 0 8px rgba(13,110,253,0); } }
    @keyframes flowArrow { from { background-position: 0 0; } to { background-position: 30px 0; } }
    @keyframes dotPulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.4); opacity: .6; } }

    .reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* ===== WORKFLOW DIAGRAM ===== */
    .flow-container {
        display: flex; flex-wrap: wrap; justify-content: center; align-items: flex-start;
        gap: 0; margin: 1.5rem 0; padding: 1rem 0;
    }

    .flow-node {
        display: flex; flex-direction: column; align-items: center; gap: .5rem;
        padding: .5rem .8rem; min-width: 95px; text-align: center;
        transition: transform .3s;
    }
    .flow-node:hover { transform: translateY(-4px); }
    .flow-node .flow-icon {
        width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; font-size: 1.4rem; color: #fff;
        box-shadow: 0 4px 14px rgba(0,0,0,.15);
        animation: pulseGlow 2.5s ease infinite;
    }
    .flow-node .flow-label { font-weight: 700; font-size: .8rem; color: #1f2937; line-height: 1.2; }
    .flow-node .flow-sub { font-size: .7rem; color: #6b7280; max-width: 110px; line-height: 1.3; }

    .flow-arrow {
        display: flex; align-items: center; padding-top: 8px;
        width: 50px; justify-content: center; position: relative;
    }
    .flow-arrow-line {
        width: 100%; height: 2px;
        background: repeating-linear-gradient(90deg, #93c5fd 0, #93c5fd 6px, transparent 6px, transparent 10px);
        position: relative;
        animation: flowArrow .8s linear infinite;
    }
    .flow-arrow-line::after {
        content: '';
        position: absolute;
        right: -1px; top: -4px;
        border: 5px solid transparent;
        border-left: 7px solid #3b82f6;
    }

    @media (max-width: 768px) {
        .flow-container { flex-direction: column; align-items: center; }
        .flow-arrow { width: auto; height: 30px; padding-top: 0; }
        .flow-arrow-line { width: 2px; height: 100%; background: repeating-linear-gradient(180deg, #93c5fd 0, #93c5fd 6px, transparent 6px, transparent 10px); }
        .flow-arrow-line::after { right: auto; top: auto; bottom: -1px; left: -4px; border: 5px solid transparent; border-top: 7px solid #3b82f6; border-left-color: transparent; }
    }

    /* ===== DATA FLOW DIAGRAM ===== */
    .dataflow {
        background: linear-gradient(135deg, #f8fafc, #eef2ff);
        border: 1px solid #e0e7ff;
        border-radius: .75rem;
        padding: 1.5rem;
        margin: 1.5rem 0;
        position: relative;
        overflow: hidden;
    }

    .df-row { display: flex; justify-content: center; align-items: center; gap: .5rem; margin-bottom: .75rem; flex-wrap: wrap; position: relative; z-index: 1; }
    .df-box {
        background: #fff;
        border: 2px solid #e5e7eb;
        border-radius: .6rem;
        padding: .6rem 1rem;
        text-align: center;
        min-width: 120px;
        transition: all .3s;
        position: relative;
    }
    .df-box:hover { border-color: #0d6efd; transform: translateY(-3px); box-shadow: 0 6px 16px rgba(13,110,253,.12); }
    .df-box i { font-size: 1.4rem; display: block; margin-bottom: .2rem; }
    .df-box .df-title { font-weight: 700; font-size: .8rem; color: #1f2937; }
    .df-box .df-desc { font-size: .68rem; color: #6b7280; }
    .df-box.highlight { border-color: #0d6efd; background: linear-gradient(135deg, #eff6ff, #dbeafe); }
    .df-box.success { border-color: #10b981; background: linear-gradient(135deg, #ecfdf5, #d1fae5); }
    .df-box.danger { border-color: #ef4444; background: linear-gradient(135deg, #fef2f2, #fee2e2); }
    .df-box.warning { border-color: #f59e0b; background: linear-gradient(135deg, #fffbeb, #fef3c7); }

    .df-connector { display: flex; align-items: center; padding: 0 .2rem; }
    .df-arrow-h {
        display: block; width: 32px; height: 2px; background: #93c5fd; position: relative;
    }
    .df-arrow-h::after {
        content: '';
        position: absolute; right: -1px; top: -4px;
        border: 5px solid transparent;
        border-left: 6px solid #3b82f6;
    }

    .df-dot { width: 10px; height: 10px; border-radius: 50%; background: #0d6efd; animation: dotPulse 1.5s ease infinite; display: inline-block; margin: 0 .25rem; }
    .df-dot.green { background: #10b981; animation-delay: .3s; }
    .df-dot.red { background: #ef4444; animation-delay: .6s; }
    .df-dot.yellow { background: #f59e0b; animation-delay: .9s; }

    /* ===== FLOW TIMELINE ===== */
    .timeline { position: relative; padding-left: 40px; margin: 1.5rem 0; }
    .timeline::before { content: ''; position: absolute; left: 16px; top: 0; bottom: 0; width: 3px; background: linear-gradient(to bottom, #0d6efd, #8b5cf6, #10b981); border-radius: 2px; }
    .timeline-item { position: relative; margin-bottom: 1.25rem; animation: fadeInLeft .5s ease backwards; }
    .timeline-item:nth-child(1) { animation-delay: .1s; }
    .timeline-item:nth-child(2) { animation-delay: .2s; }
    .timeline-item:nth-child(3) { animation-delay: .3s; }
    .timeline-item:nth-child(4) { animation-delay: .4s; }
    .timeline-item:nth-child(5) { animation-delay: .5s; }
    .timeline-item:nth-child(6) { animation-delay: .6s; }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -32px; top: 8px;
        width: 14px; height: 14px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #0d6efd;
        z-index: 1;
    }
    .timeline-item.active::before { background: #0d6efd; box-shadow: 0 0 0 4px rgba(13,110,253,.2); }
    .timeline-item .tl-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: .5rem;
        padding: .75rem 1rem;
        transition: all .25s;
    }
    .timeline-item:hover .tl-card { border-color: #0d6efd; transform: translateX(4px); }
    .timeline-item .tl-title { font-weight: 700; font-size: .9rem; margin-bottom: .15rem; }
    .timeline-item .tl-desc { font-size: .82rem; color: #6b7280; margin: 0; }

    /* ===== PDF / PRINT ===== */
    @media print {
        /* Hide app shell */
        .sidebar, header, .no-print, .manual-nav, .page-loader { display: none !important; }
        .content { margin: 0 !important; }
        body, .content, main { background: #fff !important; }
        main { padding: 0 !important; }

        /* Full width content */
        .col-lg-3 { display: none !important; }
        .col-lg-9 { flex: 0 0 100% !important; max-width: 100% !important; }
        .row { display: block !important; }

        /* Hero for PDF cover */
        .manual-hero {
            background: #1f2937 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            page-break-after: always;
            padding: 3rem;
            margin-bottom: 0;
        }
        .manual-hero h1 { font-size: 2.5rem; }
        .manual-hero::after {
            content: 'Generated on {{ now()->format("d M Y") }}';
            display: block;
            margin-top: 1.5rem;
            opacity: .6;
            font-size: .85rem;
        }

        /* Sections */
        .manual-section { page-break-inside: avoid; margin-bottom: 1.5rem !important; }
        .reveal { opacity: 1 !important; transform: none !important; }

        /* Flow diagrams */
        .flow-node .flow-icon { animation: none !important; box-shadow: none !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .flow-arrow-line { animation: none !important; }
        .df-dot { animation: none !important; }
        .dataflow { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .dataflow::before { display: none; }

        /* Cards & boxes */
        .step-card, .tip-box, .warning-box, .feature-item, .df-box {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            break-inside: avoid;
        }
        .step-card:hover { transform: none; box-shadow: none; }
        .feature-item:hover { transform: none; box-shadow: none; }

        /* Table */
        .table thead th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Timeline */
        .timeline::before { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .timeline-item { animation: none !important; }
        .timeline-item::before { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Section titles */
        .section-title .icon-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Footer */
        @page { size: A4; margin: 15mm 12mm; }
    }
</style>
@endpush

@section('content')
{{-- Hero --}}
<div class="manual-hero">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-book fs-2"></i>
            <div>
                <h1>Inventory Management System</h1>
                <p>Complete user guide — everything you need to run your business smoothly</p>
            </div>
        </div>
        <div class="d-flex gap-2 no-print">
            <button onclick="printManual()" class="btn btn-light btn-sm fw-semibold" style="white-space:nowrap">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF (English)
            </button>
            <a href="{{ route('admin.manual.bangla') }}" target="_blank" class="btn btn-outline-light btn-sm fw-semibold" style="white-space:nowrap">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF (বাংলা)
            </a>
        </div>
    </div>
</div>

<div class="row">
    {{-- Sidebar Navigation --}}
    <div class="col-lg-3 mb-4">
        <div class="manual-nav card border-0 shadow-sm">
            <div class="card-body p-2">
                <div class="section-head">Getting Started</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#overview">Overview</a>
                    <a class="nav-link" href="#system-dataflow">System Data Flow</a>
                    <a class="nav-link" href="#login">Login & Security</a>
                    <a class="nav-link" href="#dashboard">Dashboard & Charts</a>
                </nav>

                <div class="section-head">Workflows</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#wf-sales">Sales Workflow</a>
                    <a class="nav-link" href="#wf-purchase">Purchase Workflow</a>
                    <a class="nav-link" href="#wf-return">Return Workflow</a>
                    <a class="nav-link" href="#wf-money">Money Flow</a>
                </nav>

                <div class="section-head">Daily Operations</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#pos">POS (Point of Sale)</a>
                    <a class="nav-link" href="#pos2">POS 2 (Search Mode)</a>
                    <a class="nav-link" href="#sales">Sales & Invoices</a>
                    <a class="nav-link" href="#thermal">Thermal Receipt</a>
                    <a class="nav-link" href="#quotations">Quotations</a>
                    <a class="nav-link" href="#returns">Sale Returns</a>
                    <a class="nav-link" href="#payments">Due Collection</a>
                    <a class="nav-link" href="#installments">Installment / EMI</a>
                    <a class="nav-link" href="#cashregister">Cash Register</a>
                </nav>

                <div class="section-head">Inventory</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#products">Products & Warranty</a>
                    <a class="nav-link" href="#categories">Categories</a>
                    <a class="nav-link" href="#units">Units of Measure</a>
                    <a class="nav-link" href="#stock">Stock Management</a>
                    <a class="nav-link" href="#batches">Stock Batches (FIFO)</a>
                    <a class="nav-link" href="#purchases">Purchases</a>
                    <a class="nav-link" href="#barcodes">Barcode Labels</a>
                </nav>

                <div class="section-head">Finance</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#expenses">Expenses</a>
                    <a class="nav-link" href="#accounting">Accounting</a>
                    <a class="nav-link" href="#reports">Reports</a>
                </nav>

                <div class="section-head">Contacts</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#contacts">Suppliers & Customers</a>
                    <a class="nav-link" href="#ledger">Customer / Supplier Ledger</a>
                    <a class="nav-link" href="#loyalty">Loyalty Points</a>
                </nav>

                <div class="section-head">Administration</div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="#users">Users & Roles</a>
                    <a class="nav-link" href="#branches">Branches</a>
                    <a class="nav-link" href="#settings">Settings</a>
                    <a class="nav-link" href="#sms">SMS Notification</a>
                    <a class="nav-link" href="#activitylog">Activity Log</a>
                    <a class="nav-link" href="#loginhistory">Login History</a>
                    <a class="nav-link" href="#backup">Database Backup</a>
                    <a class="nav-link" href="#api">E-commerce API</a>
                </nav>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="col-lg-9">

        {{-- ============== OVERVIEW ============== --}}
        <div class="manual-section mb-4 reveal" id="overview">
            <div class="section-title">
                <div class="icon-box" style="background:#6366f1"><i class="bi bi-stars"></i></div>
                Overview
            </div>
            <p class="text-muted mb-3">A complete inventory management system — products, stock, sales, purchases, expenses, reporting, and much more — all from one place.</p>
            <div class="feature-grid">
                <div class="feature-item"><i class="bi bi-cart-check"></i> Point of Sale</div>
                <div class="feature-item"><i class="bi bi-box"></i> Products</div>
                <div class="feature-item"><i class="bi bi-clipboard-data"></i> FIFO Stock</div>
                <div class="feature-item"><i class="bi bi-bag-plus"></i> Purchases</div>
                <div class="feature-item"><i class="bi bi-receipt"></i> Invoices</div>
                <div class="feature-item"><i class="bi bi-file-earmark-text"></i> Quotations</div>
                <div class="feature-item"><i class="bi bi-arrow-return-left"></i> Returns</div>
                <div class="feature-item"><i class="bi bi-cash-stack"></i> Due Collection</div>
                <div class="feature-item"><i class="bi bi-calendar2-check"></i> EMI</div>
                <div class="feature-item"><i class="bi bi-wallet2"></i> Expenses</div>
                <div class="feature-item"><i class="bi bi-bar-chart-line"></i> P&L Report</div>
                <div class="feature-item"><i class="bi bi-shield-check"></i> Warranty</div>
                <div class="feature-item"><i class="bi bi-safe"></i> Cash Register</div>
                <div class="feature-item"><i class="bi bi-journal-text"></i> Ledger</div>
                <div class="feature-item"><i class="bi bi-star"></i> Loyalty Points</div>
                <div class="feature-item"><i class="bi bi-building"></i> Multi-Branch</div>
                <div class="feature-item"><i class="bi bi-clock-history"></i> Activity Log</div>
                <div class="feature-item"><i class="bi bi-plug"></i> API</div>
                <div class="feature-item"><i class="bi bi-printer"></i> Thermal Print</div>
                <div class="feature-item"><i class="bi bi-chat-dots"></i> SMS</div>
            </div>

            <div class="mt-3">
                <strong>Default user roles:</strong>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <span class="role-badge role-super"><i class="bi bi-star-fill me-1"></i>Super Admin — full access</span>
                    <span class="role-badge role-manager"><i class="bi bi-person-badge me-1"></i>Manager — all except users</span>
                    <span class="role-badge role-store"><i class="bi bi-box-seam me-1"></i>Storekeeper — stock & purchases</span>
                    <span class="role-badge role-sales"><i class="bi bi-cart me-1"></i>Salesperson — POS only</span>
                </div>
            </div>
        </div>

        {{-- ============== SYSTEM DATA FLOW ============== --}}
        <div class="manual-section mb-4 reveal" id="system-dataflow">
            <div class="section-title">
                <div class="icon-box" style="background:#0d6efd"><i class="bi bi-diagram-3"></i></div>
                System Data Flow
            </div>
            <p class="text-muted mb-3">How data moves through the entire system — from purchase to profit.</p>

            <div class="dataflow">
                {{-- Row 1: Inputs --}}
                <div class="df-row">
                    <div class="df-box highlight">
                        <i class="bi bi-truck text-primary"></i>
                        <div class="df-title">Supplier</div>
                        <div class="df-desc">Provides goods</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box highlight">
                        <i class="bi bi-bag-plus text-primary"></i>
                        <div class="df-title">Purchase</div>
                        <div class="df-desc">Buy stock</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box success">
                        <i class="bi bi-clipboard-data text-success"></i>
                        <div class="df-title">Stock (FIFO)</div>
                        <div class="df-desc">Inventory increases</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box">
                        <i class="bi bi-box text-secondary"></i>
                        <div class="df-title">Products</div>
                        <div class="df-desc">Ready to sell</div>
                    </div>
                </div>

                {{-- Animated dots --}}
                <div class="text-center my-2">
                    <span class="df-dot"></span>
                    <span class="df-dot green"></span>
                    <span class="df-dot"></span>
                    <span class="df-dot yellow"></span>
                    <span class="df-dot"></span>
                </div>

                {{-- Row 2: Sales process --}}
                <div class="df-row">
                    <div class="df-box">
                        <i class="bi bi-people text-info"></i>
                        <div class="df-title">Customer</div>
                        <div class="df-desc">Walks in</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box highlight">
                        <i class="bi bi-cart-check text-primary"></i>
                        <div class="df-title">POS Sale</div>
                        <div class="df-desc">Scan & sell</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box success">
                        <i class="bi bi-receipt text-success"></i>
                        <div class="df-title">Invoice</div>
                        <div class="df-desc">Print receipt</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box warning">
                        <i class="bi bi-cash-coin text-warning"></i>
                        <div class="df-title">Revenue</div>
                        <div class="df-desc">Money in</div>
                    </div>
                </div>

                {{-- Animated dots --}}
                <div class="text-center my-2">
                    <span class="df-dot green"></span>
                    <span class="df-dot"></span>
                    <span class="df-dot red"></span>
                    <span class="df-dot"></span>
                    <span class="df-dot green"></span>
                </div>

                {{-- Row 3: Finance --}}
                <div class="df-row">
                    <div class="df-box danger">
                        <i class="bi bi-wallet2 text-danger"></i>
                        <div class="df-title">Expenses</div>
                        <div class="df-desc">Rent, bills, salary</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box warning">
                        <i class="bi bi-calculator text-warning"></i>
                        <div class="df-title">COGS (FIFO)</div>
                        <div class="df-desc">Cost of goods</div>
                    </div>
                    <div class="df-connector"><span class="df-arrow-h"></span></div>
                    <div class="df-box success" style="border-width:3px">
                        <i class="bi bi-bar-chart-line text-success"></i>
                        <div class="df-title">Profit & Loss</div>
                        <div class="df-desc">Net profit = Revenue - COGS - Expenses</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============== SALES WORKFLOW ============== --}}
        <div class="manual-section mb-4 reveal" id="wf-sales">
            <div class="section-title">
                <div class="icon-box" style="background:#059669"><i class="bi bi-cart-check"></i></div>
                Sales Workflow
            </div>
            <p class="text-muted mb-3">Complete sales process — from customer walk-in to invoice print.</p>

            <div class="flow-container">
                <div class="flow-node">
                    <div class="flow-icon" style="background:#6366f1"><i class="bi bi-person"></i></div>
                    <div class="flow-label">Customer</div>
                    <div class="flow-sub">Walk-in or registered</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#0d6efd"><i class="bi bi-upc-scan"></i></div>
                    <div class="flow-label">Scan / Search</div>
                    <div class="flow-sub">Barcode or name</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#f59e0b"><i class="bi bi-cart-plus"></i></div>
                    <div class="flow-label">Add to Cart</div>
                    <div class="flow-sub">Qty, price, discount</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#10b981"><i class="bi bi-cash-coin"></i></div>
                    <div class="flow-label">Payment</div>
                    <div class="flow-sub">Cash/Card/Mobile/Due</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#8b5cf6"><i class="bi bi-printer"></i></div>
                    <div class="flow-label">Invoice</div>
                    <div class="flow-sub">A4 or Thermal</div>
                </div>
            </div>

            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> Stock decreases (FIFO), warranty is auto-set, activity is logged, and loyalty points are earned — all automatically!</div>
        </div>

        {{-- ============== PURCHASE WORKFLOW ============== --}}
        <div class="manual-section mb-4 reveal" id="wf-purchase">
            <div class="section-title">
                <div class="icon-box" style="background:#2563eb"><i class="bi bi-bag-plus"></i></div>
                Purchase Workflow
            </div>
            <p class="text-muted mb-3">How stock enters your system — from supplier to shelf.</p>

            <div class="flow-container">
                <div class="flow-node">
                    <div class="flow-icon" style="background:#64748b"><i class="bi bi-truck"></i></div>
                    <div class="flow-label">Supplier</div>
                    <div class="flow-sub">Goods arrive</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#2563eb"><i class="bi bi-clipboard-plus"></i></div>
                    <div class="flow-label">Create Purchase</div>
                    <div class="flow-sub">Items + cost</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#10b981"><i class="bi bi-box-seam"></i></div>
                    <div class="flow-label">Stock In (FIFO)</div>
                    <div class="flow-sub">Batches created</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#f59e0b"><i class="bi bi-cash-stack"></i></div>
                    <div class="flow-label">Payment</div>
                    <div class="flow-sub">Paid or Due</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#8b5cf6"><i class="bi bi-journal-text"></i></div>
                    <div class="flow-label">Supplier Ledger</div>
                    <div class="flow-sub">Auto-updated</div>
                </div>
            </div>
        </div>

        {{-- ============== RETURN WORKFLOW ============== --}}
        <div class="manual-section mb-4 reveal" id="wf-return">
            <div class="section-title">
                <div class="icon-box" style="background:#ef4444"><i class="bi bi-arrow-return-left"></i></div>
                Return Workflow
            </div>
            <p class="text-muted mb-3">When a customer returns products — stock and money are automatically adjusted.</p>

            <div class="flow-container">
                <div class="flow-node">
                    <div class="flow-icon" style="background:#ef4444"><i class="bi bi-person-x"></i></div>
                    <div class="flow-label">Customer Returns</div>
                    <div class="flow-sub">Defective / wrong</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#f59e0b"><i class="bi bi-list-check"></i></div>
                    <div class="flow-label">Select Items</div>
                    <div class="flow-sub">Qty & reason</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#10b981"><i class="bi bi-box-seam"></i></div>
                    <div class="flow-label">Stock Restored</div>
                    <div class="flow-sub">Auto FIFO batch</div>
                </div>
                <div class="flow-arrow"><div class="flow-arrow-line"></div></div>
                <div class="flow-node">
                    <div class="flow-icon" style="background:#8b5cf6"><i class="bi bi-wallet2"></i></div>
                    <div class="flow-label">Refund Tracked</div>
                    <div class="flow-sub">Amount recorded</div>
                </div>
            </div>
        </div>

        {{-- ============== MONEY FLOW ============== --}}
        <div class="manual-section mb-4 reveal" id="wf-money">
            <div class="section-title">
                <div class="icon-box" style="background:#f59e0b"><i class="bi bi-currency-exchange"></i></div>
                Money Flow
            </div>
            <p class="text-muted mb-3">How money moves in and out of your business — tracked automatically.</p>

            <div class="timeline">
                <div class="timeline-item active">
                    <div class="tl-card">
                        <div class="tl-title"><i class="bi bi-arrow-down-circle text-success me-2"></i>Money In — Sales Revenue</div>
                        <p class="tl-desc">POS sales generate revenue. Cash, card, or mobile payments are recorded instantly.</p>
                    </div>
                </div>
                <div class="timeline-item active">
                    <div class="tl-card">
                        <div class="tl-title"><i class="bi bi-arrow-down-circle text-info me-2"></i>Money In — Due Collection</div>
                        <p class="tl-desc">Partial payments are tracked. Collect remaining due from sale or purchase detail pages.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="tl-card">
                        <div class="tl-title"><i class="bi bi-arrow-up-circle text-danger me-2"></i>Money Out — Purchases</div>
                        <p class="tl-desc">Buying stock costs money. Paid amount and supplier due are tracked in the supplier ledger.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="tl-card">
                        <div class="tl-title"><i class="bi bi-arrow-up-circle text-warning me-2"></i>Money Out — Expenses</div>
                        <p class="tl-desc">Rent, electricity, salary, transport — all categorized and tracked with date filters.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="tl-card">
                        <div class="tl-title"><i class="bi bi-arrow-up-circle text-secondary me-2"></i>Money Out — Refunds</div>
                        <p class="tl-desc">Customer returns reduce revenue. Tracked separately in the P&L report.</p>
                    </div>
                </div>
                <div class="timeline-item active">
                    <div class="tl-card" style="background:#ecfdf5;border-color:#10b981">
                        <div class="tl-title"><i class="bi bi-trophy text-success me-2"></i>Result — Net Profit</div>
                        <p class="tl-desc"><strong>Revenue - COGS - Returns - Expenses = Net Profit.</strong> View anytime in Profit & Loss report.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============== LOGIN ============== --}}
        <div class="manual-section mb-4 reveal" id="login">
            <div class="section-title"><div class="icon-box" style="background:#10b981"><i class="bi bi-shield-check"></i></div> Login & Security</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Open the application</h6><p>Go to your application URL. You'll see the login page.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Enter credentials</h6><p>Email + Password, click <span class="kbd">Login</span>. Every login is recorded with IP and timestamp.</p></div></div>
            <div class="step-card"><div class="step-num">3</div><div><h6>Logout</h6><p>Click <i class="bi bi-box-arrow-right"></i> <strong>Logout</strong> at the bottom of sidebar.</p></div></div>
            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> <strong>Default admin:</strong> <span class="kbd">admin@example.com</span> / <span class="kbd">password</span></div>
        </div>

        {{-- ============== DASHBOARD ============== --}}
        <div class="manual-section mb-4 reveal" id="dashboard">
            <div class="section-title"><div class="icon-box" style="background:#3b82f6"><i class="bi bi-speedometer2"></i></div> Dashboard & Charts</div>
            <div class="row g-2">
                <div class="col-md-6"><div class="step-card"><i class="bi bi-cash-coin text-success fs-4"></i><div><h6>Today's Sales</h6><p>Amount + invoice count</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-graph-up-arrow text-primary fs-4"></i><div><h6>Month Sales</h6><p>Running monthly total</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-graph-up text-info fs-4"></i><div><h6>7-Day Line Chart</h6><p>Daily sales trend — spot patterns</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-pie-chart text-warning fs-4"></i><div><h6>Category Doughnut</h6><p>Top 5 categories by revenue</p></div></div></div>
            </div>
        </div>

        {{-- ============== POS ============== --}}
        <div class="manual-section mb-4 reveal" id="pos">
            <div class="section-title"><div class="icon-box" style="background:#059669"><i class="bi bi-cart-check"></i></div> POS (Point of Sale)</div>
            <div class="step-card"><div class="step-num">1</div><div><h6><i class="bi bi-upc-scan me-1"></i> Scan or search</h6><p>Barcode scanner or search by name/SKU/model/barcode.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6><i class="bi bi-plus-circle me-1"></i> Add to cart</h6><p>Click product to add. Click again to increase qty.</p></div></div>
            <div class="step-card"><div class="step-num">3</div><div><h6><i class="bi bi-person me-1"></i> Select customer</h6><p>Dropdown or <i class="bi bi-person-plus"></i> instant add.</p></div></div>
            <div class="step-card"><div class="step-num">4</div><div><h6><i class="bi bi-calculator me-1"></i> Discount, tax, payment</h6><p>Cash / Card / Mobile / Due. Enter paid amount.</p></div></div>
            <div class="step-card"><div class="step-num">5</div><div><h6><i class="bi bi-check2-circle me-1"></i> Complete Sale</h6><p>Invoice opens auto. Stock & warranty auto-calculated.</p></div></div>
        </div>

        {{-- ============== POS 2 ============== --}}
        <div class="manual-section mb-4 reveal" id="pos2">
            <div class="section-title"><div class="icon-box" style="background:#0284c7"><i class="bi bi-search"></i></div> POS 2 — Search Mode</div>
            <p class="text-muted mb-3">POS 2 is a lightweight, search-first interface — no pre-loaded product grid, ideal for large catalogs where scrolling is impractical.</p>
            <div class="step-card"><div class="step-num">1</div><div><h6><i class="bi bi-search me-1"></i> Type to search</h6><p>Begin typing a product name, SKU, model, or scan a barcode. Matching results appear instantly — nothing is pre-loaded.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6><i class="bi bi-cart-plus me-1"></i> Select & add to cart</h6><p>Click a result to add it. Adjust quantity directly in the cart.</p></div></div>
            <div class="step-card"><div class="step-num">3</div><div><h6><i class="bi bi-cash-coin me-1"></i> Complete payment</h6><p>Same as standard POS — customer, discount, tax, payment method.</p></div></div>
            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> Use POS 2 when you have hundreds of products. It's faster on low-RAM devices because it skips loading the full product grid.</div>
        </div>

        {{-- ============== SALES ============== --}}
        <div class="manual-section mb-4 reveal" id="sales">
            <div class="section-title"><div class="icon-box" style="background:#8b5cf6"><i class="bi bi-receipt"></i></div> Sales & Invoices</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-printer"></i></div><div><h6>Print Invoice (A4)</h6><p>Professional invoice with company logo.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-calendar2-check"></i></div><div><h6>Create EMI</h6><p>Click <strong>EMI</strong> to create installment plan.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-arrow-return-left"></i></div><div><h6>Process Return</h6><p>Click <strong>Return</strong> to return items and restore stock.</p></div></div>
        </div>

        {{-- ============== THERMAL ============== --}}
        <div class="manual-section mb-4 reveal" id="thermal">
            <div class="section-title"><div class="icon-box" style="background:#0891b2"><i class="bi bi-printer"></i></div> Thermal Receipt (58mm / 80mm)</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Open sale detail</h6><p>Click <strong>Thermal Receipt</strong> button.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Print</h6><p><strong>Print Receipt</strong> (80mm) or <strong>Print 58mm</strong>. Monospace, compact.</p></div></div>
        </div>

        {{-- ============== QUOTATIONS ============== --}}
        <div class="manual-section mb-4 reveal" id="quotations">
            <div class="section-title"><div class="icon-box" style="background:#d97706"><i class="bi bi-file-earmark-text"></i></div> Quotations</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Create quotation</h6><p>Customer, products, prices, validity date. Does not affect stock.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>View & print</h6><p>Print with company info. Share with customer for approval.</p></div></div>
        </div>

        {{-- ============== RETURNS ============== --}}
        <div class="manual-section mb-4 reveal" id="returns">
            <div class="section-title"><div class="icon-box" style="background:#ef4444"><i class="bi bi-arrow-return-left"></i></div> Sale Returns</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Find sale & click Return</h6><p>Open sale, click red Return button.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Select items & process</h6><p>Check items, adjust qty, select reason. Stock auto-restores.</p></div></div>
        </div>

        {{-- ============== PAYMENTS ============== --}}
        <div class="manual-section mb-4 reveal" id="payments">
            <div class="section-title"><div class="icon-box" style="background:#0891b2"><i class="bi bi-cash-stack"></i></div> Due Collection</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Collect from sale</h6><p>Open sale with due, use payment form at bottom.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Pay supplier</h6><p>Open purchase with due, same form.</p></div></div>
            <div class="step-card"><div class="step-num">3</div><div><h6>View all payments</h6><p><strong>Transactions > Payments</strong>. Filter by type.</p></div></div>
        </div>

        {{-- ============== INSTALLMENTS ============== --}}
        <div class="manual-section mb-4 reveal" id="installments">
            <div class="section-title"><div class="icon-box" style="background:#7c3aed"><i class="bi bi-calendar2-check"></i></div> Installment / EMI</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Create plan</h6><p>From sale page, click EMI. Set down payment + number of installments.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Track & collect</h6><p>Each installment has due date. Mark as paid when collected. Overdue shown in red.</p></div></div>
        </div>

        {{-- ============== CASH REGISTER ============== --}}
        <div class="manual-section mb-4 reveal" id="cashregister">
            <div class="section-title"><div class="icon-box" style="background:#dc2626"><i class="bi bi-safe"></i></div> Cash Register</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Open shift</h6><p>Enter opening balance, click Open Register.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Close shift</h6><p>Enter closing balance. Difference shows accountability.</p></div></div>
        </div>

        {{-- ============== PRODUCTS ============== --}}
        <div class="manual-section mb-4 reveal" id="products">
            <div class="section-title"><div class="icon-box" style="background:#f59e0b"><i class="bi bi-box"></i></div> Products & Warranty</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-plus-lg"></i></div><div><h6>Add product</h6><p>Name, prices, category, stock, SKU, barcode, images, warranty days.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-shield-check"></i></div><div><h6>Warranty</h6><p>Set <strong>Warranty (days)</strong> — auto-calculates expiry per sale item.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-upload"></i></div><div><h6>Bulk Import & Pricing</h6><p>Import from spreadsheet, update prices in bulk.</p></div></div>
        </div>

        {{-- ============== CATEGORIES ============== --}}
        <div class="manual-section mb-4 reveal" id="categories">
            <div class="section-title"><div class="icon-box" style="background:#a855f7"><i class="bi bi-diagram-3"></i></div> Categories</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-layers"></i></div><div><h6>3-level hierarchy</h6><p>Main Category > Category > Sub Category.</p></div></div>
        </div>

        {{-- ============== UNITS ============== --}}
        <div class="manual-section mb-4 reveal" id="units">
            <div class="section-title"><div class="icon-box" style="background:#64748b"><i class="bi bi-rulers"></i></div> Units of Measure</div>
            <p class="text-muted mb-3">Define the measurement units used when adding products (e.g. Pcs, Kg, Meters, Liters).</p>
            <div class="step-card"><div class="step-num"><i class="bi bi-plus-lg"></i></div><div><h6>Add a unit</h6><p>Catalog > Units > New. Enter the unit name (e.g. "Pcs", "Box", "Kg") and abbreviation.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-pencil"></i></div><div><h6>Edit or delete</h6><p>Update the name anytime. You cannot delete a unit that is assigned to products.</p></div></div>
            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> Set a default unit when creating products. It appears on invoices and stock reports.</div>
        </div>

        {{-- ============== STOCK ============== --}}
        <div class="manual-section mb-4 reveal" id="stock">
            <div class="section-title"><div class="icon-box" style="background:#06b6d4"><i class="bi bi-clipboard-data"></i></div> Stock Management</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-eye"></i></div><div><h6>View levels</h6><p>All products with current qty.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-pencil"></i></div><div><h6>Manual adjust</h6><p>For damage, loss, corrections.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-clock-history"></i></div><div><h6>Movements log</h6><p>Every change with running balance.</p></div></div>
            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> <strong>FIFO:</strong> First-In-First-Out — oldest cost used first for accurate profit.</div>
        </div>

        {{-- ============== BATCHES ============== --}}
        <div class="manual-section mb-4 reveal" id="batches">
            <div class="section-title"><div class="icon-box" style="background:#0369a1"><i class="bi bi-layers"></i></div> Stock Batches (FIFO)</div>
            <p class="text-muted mb-3">Every purchase creates a stock batch. Batches are consumed oldest-first (FIFO) to give you accurate cost of goods sold.</p>
            <div class="step-card"><div class="step-num"><i class="bi bi-eye"></i></div><div><h6>View all batches</h6><p>Catalog > Batches. See each batch's number (e.g. B000001), received date, unit cost, original quantity, and <strong>remaining quantity</strong>.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-search"></i></div><div><h6>Filter by product</h6><p>Search by product name to see all batches for that SKU and identify which cost layer is currently being sold.</p></div></div>
            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> <strong>Why FIFO?</strong> When you sell a product, the cost from the oldest batch is used first. This gives the most accurate profit margin, especially when purchase prices fluctuate.</div>
        </div>

        {{-- ============== PURCHASES ============== --}}
        <div class="manual-section mb-4 reveal" id="purchases">
            <div class="section-title"><div class="icon-box" style="background:#2563eb"><i class="bi bi-bag-plus"></i></div> Purchases</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Create purchase</h6><p>Supplier, items, cost. Stock auto-increases.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Bulk import</h6><p>Spreadsheet upload for many items.</p></div></div>
        </div>

        {{-- ============== BARCODES ============== --}}
        <div class="manual-section mb-4 reveal" id="barcodes">
            <div class="section-title"><div class="icon-box" style="background:#1f2937"><i class="bi bi-upc"></i></div> Barcode Labels</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-layout-split"></i></div><div><h6>Layout options</h6><p>A4 (2/3/4 col), Thermal 58mm, Thermal 80mm. Select from dropdown.</p></div></div>
        </div>

        {{-- ============== EXPENSES ============== --}}
        <div class="manual-section mb-4 reveal" id="expenses">
            <div class="section-title"><div class="icon-box" style="background:#dc2626"><i class="bi bi-wallet2"></i></div> Expenses</div>
            <div class="step-card"><div class="step-num">1</div><div><h6>Add expense</h6><p>Title, amount, category (create on-the-fly), date.</p></div></div>
            <div class="step-card"><div class="step-num">2</div><div><h6>Filter & track</h6><p>By category, date range. Total shown.</p></div></div>
        </div>

        {{-- ============== ACCOUNTING ============== --}}
        <div class="manual-section mb-4 reveal" id="accounting">
            <div class="section-title"><div class="icon-box" style="background:#0f766e"><i class="bi bi-journal-bookmark"></i></div> Accounting</div>
            <p class="text-muted mb-3">High-level financial views — useful for reconciliation and accountants.</p>
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="step-card">
                        <i class="bi bi-journal-check text-teal fs-4" style="color:#0f766e"></i>
                        <div>
                            <h6>Day Book</h6>
                            <p>Chronological record of every financial transaction — sales, purchases, payments, expenses — for any date range. Use it for daily reconciliation.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="step-card">
                        <i class="bi bi-scale text-info fs-4"></i>
                        <div>
                            <h6>Trial Balance</h6>
                            <p>Summary of debit and credit totals across all accounts. Confirms the books are balanced before producing the P&L.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> Accounting > Day Book is the fastest way to verify what happened on a specific date — filter by date range and export or print the result.</div>
        </div>

        {{-- ============== REPORTS ============== --}}
        <div class="manual-section mb-4 reveal" id="reports">
            <div class="section-title"><div class="icon-box" style="background:#7c3aed"><i class="bi bi-graph-up"></i></div> Reports</div>
            <div class="row g-2">
                <div class="col-md-6"><div class="step-card"><i class="bi bi-graph-up text-primary fs-4"></i><div><h6>Sales & Profit</h6><p>Revenue, COGS, margin per sale</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-calendar3 text-success fs-4"></i><div><h6>Daily Summary</h6><p>Day-by-day breakdown</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-trophy text-warning fs-4"></i><div><h6>Top Products</h6><p>Best sellers by qty/revenue</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-bar-chart-line text-danger fs-4"></i><div><h6>Profit & Loss</h6><p>Full P&L statement</p></div></div></div>
            </div>
        </div>

        {{-- ============== CONTACTS ============== --}}
        <div class="manual-section mb-4 reveal" id="contacts">
            <div class="section-title"><div class="icon-box" style="background:#0d9488"><i class="bi bi-people"></i></div> Suppliers & Customers</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-truck"></i></div><div><h6>Suppliers</h6><p>Manage vendors. Name, company, phone, email.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-person"></i></div><div><h6>Customers</h6><p>Manage buyers. Quick-add from POS.</p></div></div>
        </div>

        {{-- ============== LEDGER ============== --}}
        <div class="manual-section mb-4 reveal" id="ledger">
            <div class="section-title"><div class="icon-box" style="background:#059669"><i class="bi bi-journal-text"></i></div> Customer / Supplier Ledger</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-journal-text"></i></div><div><h6>Click Ledger icon</h6><p>From customer/supplier list, click <i class="bi bi-journal-text text-success"></i>. See total, paid, due, every transaction.</p></div></div>
        </div>

        {{-- ============== LOYALTY ============== --}}
        <div class="manual-section mb-4 reveal" id="loyalty">
            <div class="section-title"><div class="icon-box" style="background:#f59e0b"><i class="bi bi-star"></i></div> Loyalty Points</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-gear"></i></div><div><h6>Configure in Settings</h6><p>Enable loyalty, set points per ৳100 and redeem value.</p></div></div>
        </div>

        {{-- ============== USERS ============== --}}
        <div class="manual-section mb-4 reveal" id="users">
            <div class="section-title"><div class="icon-box" style="background:#be185d"><i class="bi bi-person-gear"></i></div> Users, Roles & Permissions</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-person-plus"></i></div><div><h6>Add users</h6><p>Name, email, password, role.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-shield-lock"></i></div><div><h6>Manage roles</h6><p>Create roles with specific permissions. Sidebar auto-hides unauthorized items.</p></div></div>

            <div class="mt-3">
                <strong>Default roles:</strong>
                <div class="table-responsive mt-2">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr><th>Feature</th><th class="text-center">Super Admin</th><th class="text-center">Manager</th><th class="text-center">Storekeeper</th><th class="text-center">Salesperson</th></tr>
                        </thead>
                        <tbody>
                            @php
                                $access = [
                                    ['Dashboard & Charts', true, true, true, true],
                                    ['POS & Quotations', true, true, false, true],
                                    ['Products & Stock', true, true, true, 'View'],
                                    ['Purchases', true, true, true, false],
                                    ['Sales & Returns', true, true, false, 'View'],
                                    ['Payments & EMI', true, true, false, false],
                                    ['Cash Register', true, true, false, true],
                                    ['Expenses', true, true, false, false],
                                    ['Reports & P/L', true, true, false, false],
                                    ['Customers', true, true, false, true],
                                    ['Suppliers', true, true, true, false],
                                    ['Users & Roles', true, false, false, false],
                                    ['Settings & Backup', true, true, false, false],
                                    ['Activity/Login Log', true, true, false, false],
                                ];
                            @endphp
                            @foreach ($access as [$feat, $sa, $mgr, $sk, $sp])
                                <tr>
                                    <td>{{ $feat }}</td>
                                    @foreach ([$sa, $mgr, $sk, $sp] as $v)
                                        <td class="text-center">{!! $v === true ? '<i class="bi bi-check-circle-fill text-success"></i>' : ($v ? "<span class='small text-muted'>$v</span>" : '<i class="bi bi-x-circle text-danger"></i>') !!}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ============== BRANCHES ============== --}}
        <div class="manual-section mb-4 reveal" id="branches">
            <div class="section-title"><div class="icon-box" style="background:#64748b"><i class="bi bi-building"></i></div> Branches</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-plus-lg"></i></div><div><h6>Add branches</h6><p>Name, address, phone. Manage multiple store locations.</p></div></div>
        </div>

        {{-- ============== SETTINGS ============== --}}
        <div class="manual-section mb-4 reveal" id="settings">
            <div class="section-title"><div class="icon-box" style="background:#64748b"><i class="bi bi-gear"></i></div> Settings</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-building"></i></div><div><h6>Company info</h6><p>Name, phone, email, address, logo — appears on invoices.</p></div></div>
            <div class="step-card"><div class="step-num"><i class="bi bi-currency-exchange"></i></div><div><h6>Currency</h6><p>Change symbol (default: ৳).</p></div></div>
        </div>

        {{-- ============== SMS ============== --}}
        <div class="manual-section mb-4 reveal" id="sms">
            <div class="section-title"><div class="icon-box" style="background:#2563eb"><i class="bi bi-chat-dots"></i></div> SMS Notification</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-gear"></i></div><div><h6>Configure</h6><p>Settings > enable SMS, choose provider, enter API key and sender ID.</p></div></div>
        </div>

        {{-- ============== ACTIVITY LOG ============== --}}
        <div class="manual-section mb-4 reveal" id="activitylog">
            <div class="section-title"><div class="icon-box" style="background:#f59e0b"><i class="bi bi-clock-history"></i></div> Activity Log</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-eye"></i></div><div><h6>Monitor actions</h6><p>Sales, purchases, stock adjustments, logins, backups — who, when, IP address.</p></div></div>
        </div>

        {{-- ============== LOGIN HISTORY ============== --}}
        <div class="manual-section mb-4 reveal" id="loginhistory">
            <div class="section-title"><div class="icon-box" style="background:#be185d"><i class="bi bi-person-check"></i></div> Login History</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-eye"></i></div><div><h6>Track logins</h6><p>User, time, IP, browser. Detect unauthorized access.</p></div></div>
        </div>

        {{-- ============== BACKUP ============== --}}
        <div class="manual-section mb-4 reveal" id="backup">
            <div class="section-title"><div class="icon-box" style="background:#059669"><i class="bi bi-database-down"></i></div> Database Backup</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-download"></i></div><div><h6>One-click download</h6><p>Settings > Download Backup. Store safely.</p></div></div>
            <div class="warning-box"><i class="bi bi-exclamation-triangle-fill me-1"></i> Take regular backups! Restore from backup file if anything goes wrong.</div>
        </div>

        {{-- ============== API ============== --}}
        <div class="manual-section mb-4 reveal" id="api">
            <div class="section-title"><div class="icon-box" style="background:#1f2937"><i class="bi bi-plug"></i></div> E-commerce API</div>
            <div class="step-card"><div class="step-num"><i class="bi bi-code-slash"></i></div><div><h6>REST Endpoints</h6>
                <p>
                    <span class="kbd">GET /api/v1/products</span> — List products<br>
                    <span class="kbd">GET /api/v1/products/{id}</span> — Product detail<br>
                    <span class="kbd">GET /api/v1/products/{id}/stock</span> — Stock check<br>
                    <span class="kbd">GET /api/v1/categories</span> — Category tree
                </p>
            </div></div>
            <div class="tip-box"><i class="bi bi-lightbulb-fill"></i> Authenticated via Laravel Sanctum tokens. Create token from user profile.</div>
        </div>

        {{-- ============== QUICK TIPS ============== --}}
        <div class="manual-section mb-4 reveal" id="shortcuts">
            <div class="section-title"><div class="icon-box" style="background:#1f2937"><i class="bi bi-keyboard"></i></div> Quick Tips</div>
            <div class="row g-2">
                <div class="col-md-6"><div class="step-card"><i class="bi bi-upc-scan text-primary fs-4"></i><div><h6>Barcode Scanner</h6><p>Auto-focused in POS. Scan to add instantly.</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-printer text-success fs-4"></i><div><h6>Print Everywhere</h6><p>A4 & thermal supported. All print-optimized.</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-shield-check text-info fs-4"></i><div><h6>Warranty Auto-Track</h6><p>Set days, expiry auto-calculates on sale.</p></div></div></div>
                <div class="col-md-6"><div class="step-card"><i class="bi bi-database-down text-secondary fs-4"></i><div><h6>Backup Weekly</h6><p>One-click from Settings. Keep data safe.</p></div></div></div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Scroll-spy for sidebar
    const links = document.querySelectorAll('.manual-nav .nav-link');
    const sections = document.querySelectorAll('.manual-section');

    function onScroll() {
        let current = '';
        sections.forEach(s => {
            if (window.scrollY >= s.offsetTop - 120) current = s.id;
        });
        links.forEach(l => {
            l.classList.toggle('active', l.getAttribute('href') === '#' + current);
        });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Scroll reveal animation
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    reveals.forEach(el => observer.observe(el));
});

function printManual() {
    // Make all reveal sections visible before printing
    document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
    // Small delay then trigger print (browser Save as PDF)
    setTimeout(() => window.print(), 200);
}
</script>
@endpush
