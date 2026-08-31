<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ব্যবহারকারী নির্দেশিকা — Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600;700;800&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans Bengali', 'Segoe UI', sans-serif; font-size: 13px; color: #1f2937; background: #fff; line-height: 1.7; }

        .no-print { text-align: center; padding: 15px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; }
        .no-print button { padding: 8px 24px; font-size: 14px; border: none; background: #0d6efd; color: #fff; border-radius: 6px; cursor: pointer; font-family: inherit; }
        .no-print button:hover { background: #0b5ed7; }
        .no-print a { margin-left: 10px; color: #0d6efd; text-decoration: none; font-size: 14px; }

        .page { max-width: 800px; margin: 0 auto; padding: 30px 40px; }

        /* Cover */
        .cover { background: linear-gradient(135deg, #1f2937, #374151, #0d6efd); color: #fff; padding: 80px 50px; border-radius: 10px; margin-bottom: 40px; text-align: center; }
        .cover h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 8px; }
        .cover p { opacity: .8; font-size: 1.1rem; }
        .cover .date { margin-top: 20px; opacity: .6; font-size: .85rem; }

        /* Section */
        .section { margin-bottom: 30px; page-break-inside: avoid; }
        .section-title { display: flex; align-items: center; gap: 10px; font-size: 1.2rem; font-weight: 700; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 14px; }
        .section-title .ic { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .step { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; display: flex; gap: 10px; align-items: flex-start; }
        .step-n { width: 26px; height: 26px; border-radius: 50%; background: #0d6efd; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .75rem; flex-shrink: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .step strong { font-size: .9rem; display: block; margin-bottom: 2px; }
        .step p { margin: 0; color: #6b7280; font-size: .85rem; }

        .tip { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px 14px; margin: 10px 0; border-radius: 0 6px 6px 0; font-size: .85rem; color: #1e40af; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .warn { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px 14px; margin: 10px 0; border-radius: 0 6px 6px 0; font-size: .85rem; color: #92400e; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* Flow diagram */
        .flow { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 4px; margin: 14px 0; }
        .flow-node { text-align: center; padding: 6px 10px; }
        .flow-node .fi { width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem; margin-bottom: 4px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .flow-node .fl { font-weight: 700; font-size: .75rem; }
        .flow-node .fs { font-size: .65rem; color: #6b7280; }
        .flow-arr { color: #3b82f6; font-size: 1.2rem; padding-top: 6px; }

        .toc { columns: 2; column-gap: 20px; margin: 15px 0; }
        .toc a { display: block; color: #374151; text-decoration: none; padding: 3px 0; font-size: .88rem; border-bottom: 1px dotted #e5e7eb; }
        .toc a:hover { color: #0d6efd; }
        .toc a i { color: #0d6efd; margin-right: 6px; font-size: .8rem; }

        @media print {
            .no-print { display: none !important; }
            .page { padding: 0; max-width: 100%; }
            .cover { page-break-after: always; border-radius: 0; padding: 100px 60px; }
            @page { size: A4; margin: 15mm 12mm; }
        }
    </style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()"><i class="bi bi-file-earmark-pdf me-1"></i> PDF ডাউনলোড করুন</button>
    <a href="{{ route('admin.manual.index') }}"><i class="bi bi-arrow-left"></i> ফিরে যান</a>
</div>

<div class="page">

    <!-- COVER -->
    <div class="cover">
        <i class="bi bi-box-seam" style="font-size:3rem;opacity:.7"></i>
        <h1>ইনভেন্টরি ম্যানেজমেন্ট সিস্টেম</h1>
        <p>সম্পূর্ণ ব্যবহারকারী নির্দেশিকা (বাংলা)</p>
        <div class="date">তারিখ: {{ now()->format('d M Y') }}</div>
    </div>

    <!-- TOC -->
    <div class="section">
        <div class="section-title"><div class="ic" style="background:#6366f1"><i class="bi bi-list-ul"></i></div> সূচিপত্র</div>
        <div class="toc">
            <a href="#bn-login"><i class="bi bi-box-arrow-in-right"></i> লগইন ও নিরাপত্তা</a>
            <a href="#bn-dashboard"><i class="bi bi-speedometer2"></i> ড্যাশবোর্ড ও চার্ট</a>
            <a href="#bn-wf-sales"><i class="bi bi-cart-check"></i> বিক্রয় প্রক্রিয়া</a>
            <a href="#bn-pos"><i class="bi bi-cart-check"></i> POS (পয়েন্ট অব সেল)</a>
            <a href="#bn-pos2"><i class="bi bi-search"></i> POS 2 (সার্চ মোড)</a>
            <a href="#bn-sales"><i class="bi bi-receipt"></i> বিক্রয় ও ইনভয়েস</a>
            <a href="#bn-quotation"><i class="bi bi-file-earmark-text"></i> কোটেশন</a>
            <a href="#bn-returns"><i class="bi bi-arrow-return-left"></i> বিক্রয় ফেরত</a>
            <a href="#bn-payments"><i class="bi bi-cash-stack"></i> বাকি আদায়</a>
            <a href="#bn-installments"><i class="bi bi-calendar2-check"></i> কিস্তি / EMI</a>
            <a href="#bn-cashregister"><i class="bi bi-safe"></i> ক্যাশ রেজিস্টার</a>
            <a href="#bn-products"><i class="bi bi-box"></i> পণ্য ও ওয়ারেন্টি</a>
            <a href="#bn-units"><i class="bi bi-rulers"></i> পরিমাপ একক (Units)</a>
            <a href="#bn-stock"><i class="bi bi-clipboard-data"></i> স্টক ব্যবস্থাপনা</a>
            <a href="#bn-batches"><i class="bi bi-layers"></i> স্টক ব্যাচ (FIFO)</a>
            <a href="#bn-purchases"><i class="bi bi-bag-plus"></i> ক্রয়</a>
            <a href="#bn-barcodes"><i class="bi bi-upc"></i> বারকোড লেবেল</a>
            <a href="#bn-expenses"><i class="bi bi-wallet2"></i> খরচ ট্র্যাকিং</a>
            <a href="#bn-accounting"><i class="bi bi-journal-bookmark"></i> অ্যাকাউন্টিং</a>
            <a href="#bn-reports"><i class="bi bi-graph-up"></i> রিপোর্ট</a>
            <a href="#bn-contacts"><i class="bi bi-truck"></i> সরবরাহকারী ও ক্রেতা</a>
            <a href="#bn-users"><i class="bi bi-person-gear"></i> ব্যবহারকারী ও ভূমিকা</a>
            <a href="#bn-admin"><i class="bi bi-gear"></i> প্রশাসন ও সেটিংস</a>
            <a href="#bn-api"><i class="bi bi-plug"></i> ই-কমার্স API</a>
        </div>
    </div>

    <!-- LOGIN -->
    <div class="section" id="bn-login">
        <div class="section-title"><div class="ic" style="background:#10b981"><i class="bi bi-shield-check"></i></div> লগইন ও নিরাপত্তা</div>
        <div class="step"><div class="step-n">১</div><div><strong>অ্যাপ্লিকেশন খুলুন</strong><p>ব্রাউজারে আপনার অ্যাপ্লিকেশনের URL যান। লগইন পেজ দেখবেন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>ইমেইল ও পাসওয়ার্ড দিন</strong><p>আপনার ইমেইল ও পাসওয়ার্ড লিখে Login বাটনে ক্লিক করুন। প্রতিটি লগইন IP সহ রেকর্ড হয়।</p></div></div>
        <div class="step"><div class="step-n">৩</div><div><strong>লগআউট</strong><p>সাইডবারের নিচে Logout বাটনে ক্লিক করুন।</p></div></div>
        <div class="tip"><i class="bi bi-lightbulb-fill me-1"></i> <strong>ডিফল্ট অ্যাডমিন:</strong> ইমেইল: admin@example.com / পাসওয়ার্ড: password — প্রথম লগইনের পর অবশ্যই পরিবর্তন করুন।</div>
    </div>

    <!-- DASHBOARD -->
    <div class="section" id="bn-dashboard">
        <div class="section-title"><div class="ic" style="background:#3b82f6"><i class="bi bi-speedometer2"></i></div> ড্যাশবোর্ড ও চার্ট</div>
        <div class="step"><div class="step-n"><i class="bi bi-cash-coin"></i></div><div><strong>আজকের বিক্রয়</strong><p>আজকের মোট বিক্রয় পরিমাণ ও ইনভয়েস সংখ্যা।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-graph-up"></i></div><div><strong>৭ দিনের সেলস চার্ট</strong><p>লাইন চার্টে গত ৭ দিনের দৈনিক বিক্রয় ট্রেন্ড দেখায়।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-pie-chart"></i></div><div><strong>টপ ক্যাটাগরি চার্ট</strong><p>ডোনাট চার্টে সেরা ৫টি ক্যাটাগরি রেভিনিউ অনুযায়ী দেখায়।</p></div></div>
    </div>

    <!-- SALES WORKFLOW -->
    <div class="section" id="bn-wf-sales">
        <div class="section-title"><div class="ic" style="background:#059669"><i class="bi bi-cart-check"></i></div> বিক্রয় প্রক্রিয়া (Workflow)</div>
        <div class="flow">
            <div class="flow-node"><div class="fi" style="background:#6366f1"><i class="bi bi-person"></i></div><div class="fl">ক্রেতা আসে</div></div>
            <div class="flow-arr"><i class="bi bi-arrow-right"></i></div>
            <div class="flow-node"><div class="fi" style="background:#0d6efd"><i class="bi bi-upc-scan"></i></div><div class="fl">স্ক্যান / সার্চ</div></div>
            <div class="flow-arr"><i class="bi bi-arrow-right"></i></div>
            <div class="flow-node"><div class="fi" style="background:#f59e0b"><i class="bi bi-cart-plus"></i></div><div class="fl">কার্টে যোগ</div></div>
            <div class="flow-arr"><i class="bi bi-arrow-right"></i></div>
            <div class="flow-node"><div class="fi" style="background:#10b981"><i class="bi bi-cash-coin"></i></div><div class="fl">পেমেন্ট</div></div>
            <div class="flow-arr"><i class="bi bi-arrow-right"></i></div>
            <div class="flow-node"><div class="fi" style="background:#8b5cf6"><i class="bi bi-printer"></i></div><div class="fl">ইনভয়েস</div></div>
        </div>
        <div class="tip"><i class="bi bi-lightbulb-fill me-1"></i> স্টক স্বয়ংক্রিয়ভাবে কমে (FIFO), ওয়ারেন্টি সেট হয়, কার্যকলাপ লগ হয়।</div>
    </div>

    <!-- POS -->
    <div class="section" id="bn-pos">
        <div class="section-title"><div class="ic" style="background:#059669"><i class="bi bi-cart-check"></i></div> POS (পয়েন্ট অব সেল)</div>
        <div class="step"><div class="step-n">১</div><div><strong>বারকোড স্ক্যান বা সার্চ করুন</strong><p>বারকোড স্ক্যানার দিয়ে স্ক্যান করুন, অথবা নাম/SKU/মডেল দিয়ে সার্চ করুন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>কার্টে যোগ করুন</strong><p>পণ্যের কার্ডে ক্লিক করুন। আবার ক্লিক করলে পরিমাণ বাড়বে।</p></div></div>
        <div class="step"><div class="step-n">৩</div><div><strong>ক্রেতা নির্বাচন (ঐচ্ছিক)</strong><p>ড্রপডাউন থেকে বাছুন অথবা + বাটনে নতুন ক্রেতা যোগ করুন।</p></div></div>
        <div class="step"><div class="step-n">৪</div><div><strong>ডিসকাউন্ট, ট্যাক্স ও পেমেন্ট</strong><p>নগদ / কার্ড / মোবাইল / বাকি — পেমেন্ট পদ্ধতি বাছুন।</p></div></div>
        <div class="step"><div class="step-n">৫</div><div><strong>বিক্রয় সম্পন্ন করুন</strong><p>সবুজ "Complete Sale" বাটনে ক্লিক করুন। ইনভয়েস স্বয়ংক্রিয়ভাবে খুলবে।</p></div></div>
    </div>

    <!-- POS 2 -->
    <div class="section" id="bn-pos2">
        <div class="section-title"><div class="ic" style="background:#0284c7"><i class="bi bi-search"></i></div> POS 2 — সার্চ মোড</div>
        <p style="color:#6b7280;font-size:.88rem;margin-bottom:10px">POS 2 হলো হালকা, সার্চ-প্রথম ইন্টারফেস — পণ্যের গ্রিড আগে লোড হয় না, তাই বড় ক্যাটালগে দ্রুত কাজ করে।</p>
        <div class="step"><div class="step-n">১</div><div><strong>নাম বা বারকোড টাইপ করুন</strong><p>পণ্যের নাম, SKU, মডেল টাইপ করুন বা বারকোড স্ক্যান করুন। ফলাফল তাৎক্ষণিক দেখা যাবে।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>নির্বাচন করুন ও কার্টে যোগ করুন</strong><p>ফলাফল থেকে পণ্যে ক্লিক করুন। কার্টে সরাসরি পরিমাণ পরিবর্তন করুন।</p></div></div>
        <div class="step"><div class="step-n">৩</div><div><strong>পেমেন্ট সম্পন্ন করুন</strong><p>সাধারণ POS এর মতোই — ক্রেতা, ডিসকাউন্ট, পেমেন্ট পদ্ধতি।</p></div></div>
        <div class="tip">💡 যখন পণ্যের সংখ্যা অনেক বেশি বা ডিভাইসের RAM কম, তখন POS 2 ব্যবহার করুন — পুরো গ্রিড লোড করতে হয় না বলে দ্রুত কাজ করে।</div>
    </div>

    <!-- SALES -->
    <div class="section" id="bn-sales">
        <div class="section-title"><div class="ic" style="background:#8b5cf6"><i class="bi bi-receipt"></i></div> বিক্রয় ও ইনভয়েস</div>
        <div class="step"><div class="step-n"><i class="bi bi-printer"></i></div><div><strong>ইনভয়েস প্রিন্ট (A4)</strong><p>কোম্পানির লোগো ও তথ্য সহ পেশাদার ইনভয়েস।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-printer"></i></div><div><strong>থার্মাল রিসিট (58mm/80mm)</strong><p>POS প্রিন্টারের জন্য কমপ্যাক্ট রিসিট।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-calendar2-check"></i></div><div><strong>কিস্তি/EMI তৈরি</strong><p>EMI বাটনে ক্লিক করে কিস্তির পরিকল্পনা তৈরি করুন।</p></div></div>
        <div class="warn"><i class="bi bi-exclamation-triangle-fill me-1"></i> বিক্রয় মুছে ফেললে স্টক স্বয়ংক্রিয় ফেরত আসবে। ক্রেতার ফেরতের জন্য Return ব্যবহার করুন।</div>
    </div>

    <!-- QUOTATION -->
    <div class="section" id="bn-quotation">
        <div class="section-title"><div class="ic" style="background:#d97706"><i class="bi bi-file-earmark-text"></i></div> কোটেশন / মূল্য প্রস্তাব</div>
        <div class="step"><div class="step-n">১</div><div><strong>কোটেশন তৈরি করুন</strong><p>Transactions > Quotations > New। ক্রেতা, পণ্য, মূল্য ও মেয়াদ দিন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>প্রিন্ট ও শেয়ার</strong><p>কোম্পানির তথ্য সহ প্রিন্ট করুন। স্টকে কোনো প্রভাব পড়ে না।</p></div></div>
    </div>

    <!-- RETURNS -->
    <div class="section" id="bn-returns">
        <div class="section-title"><div class="ic" style="background:#ef4444"><i class="bi bi-arrow-return-left"></i></div> বিক্রয় ফেরত (Return)</div>
        <div class="flow">
            <div class="flow-node"><div class="fi" style="background:#ef4444"><i class="bi bi-person-x"></i></div><div class="fl">ক্রেতা ফেরত দেয়</div></div>
            <div class="flow-arr"><i class="bi bi-arrow-right"></i></div>
            <div class="flow-node"><div class="fi" style="background:#f59e0b"><i class="bi bi-list-check"></i></div><div class="fl">পণ্য নির্বাচন</div></div>
            <div class="flow-arr"><i class="bi bi-arrow-right"></i></div>
            <div class="flow-node"><div class="fi" style="background:#10b981"><i class="bi bi-box-seam"></i></div><div class="fl">স্টক ফেরত</div></div>
            <div class="flow-arr"><i class="bi bi-arrow-right"></i></div>
            <div class="flow-node"><div class="fi" style="background:#8b5cf6"><i class="bi bi-wallet2"></i></div><div class="fl">ফেরত রেকর্ড</div></div>
        </div>
        <div class="step"><div class="step-n">১</div><div><strong>বিক্রয় খুলুন ও Return ক্লিক করুন</strong><p>পণ্য নির্বাচন করুন, পরিমাণ ঠিক করুন, কারণ দিন — স্টক স্বয়ংক্রিয় ফেরত আসবে।</p></div></div>
    </div>

    <!-- DUE / PAYMENTS -->
    <div class="section" id="bn-payments">
        <div class="section-title"><div class="ic" style="background:#0891b2"><i class="bi bi-cash-stack"></i></div> বাকি আদায় ও পেমেন্ট</div>
        <div class="step"><div class="step-n">১</div><div><strong>বিক্রয় থেকে আদায়</strong><p>বাকি আছে এমন বিক্রয় খুলুন, নিচে পেমেন্ট ফর্ম আছে — পরিমাণ, পদ্ধতি, তারিখ দিন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>সরবরাহকারীকে পরিশোধ</strong><p>বাকি আছে এমন ক্রয় খুলুন, একই ফর্ম আছে।</p></div></div>
        <div class="step"><div class="step-n">৩</div><div><strong>সব পেমেন্ট দেখুন</strong><p>Transactions > Payments — বিক্রয় বা ক্রয় অনুযায়ী ফিল্টার করুন।</p></div></div>
    </div>

    <!-- INSTALLMENTS -->
    <div class="section" id="bn-installments">
        <div class="section-title"><div class="ic" style="background:#7c3aed"><i class="bi bi-calendar2-check"></i></div> কিস্তি / EMI</div>
        <div class="step"><div class="step-n">১</div><div><strong>পরিকল্পনা তৈরি</strong><p>বিক্রয় পৃষ্ঠা থেকে EMI বাটনে ক্লিক করুন। ডাউন পেমেন্ট ও কিস্তির সংখ্যা দিন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>আদায় ট্র্যাক করুন</strong><p>প্রতিটি কিস্তির নির্ধারিত তারিখ আছে। আদায় হলে সবুজ বাটনে ক্লিক করুন। বিলম্বিত কিস্তি লাল দেখায়।</p></div></div>
    </div>

    <!-- CASH REGISTER -->
    <div class="section" id="bn-cashregister">
        <div class="section-title"><div class="ic" style="background:#dc2626"><i class="bi bi-safe"></i></div> ক্যাশ রেজিস্টার</div>
        <div class="step"><div class="step-n">১</div><div><strong>রেজিস্টার খুলুন</strong><p>প্রারম্ভিক ব্যালেন্স দিয়ে Open Register ক্লিক করুন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>শিফট শেষে বন্ধ করুন</strong><p>সমাপনী ব্যালেন্স দিন। পার্থক্য দেখাবে — জবাবদিহিতা নিশ্চিত হবে।</p></div></div>
    </div>

    <!-- PRODUCTS -->
    <div class="section" id="bn-products">
        <div class="section-title"><div class="ic" style="background:#f59e0b"><i class="bi bi-box"></i></div> পণ্য ও ওয়ারেন্টি</div>
        <div class="step"><div class="step-n">১</div><div><strong>পণ্য যোগ করুন</strong><p>নাম, দাম, ক্যাটাগরি, স্টক, SKU, বারকোড, ছবি, ওয়ারেন্টি (দিন) দিন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>ওয়ারেন্টি ট্র্যাকিং</strong><p>পণ্যে ওয়ারেন্টি দিন সেট করলে বিক্রয়ের সময় মেয়াদ স্বয়ংক্রিয় গণনা হয়।</p></div></div>
        <div class="step"><div class="step-n">৩</div><div><strong>বাল্ক ইমপোর্ট ও প্রাইসিং</strong><p>স্প্রেডশিট থেকে ইমপোর্ট, একসাথে অনেক পণ্যের দাম আপডেট।</p></div></div>
    </div>

    <!-- UNITS -->
    <div class="section" id="bn-units">
        <div class="section-title"><div class="ic" style="background:#64748b"><i class="bi bi-rulers"></i></div> পরিমাপ একক (Units of Measure)</div>
        <div class="step"><div class="step-n">১</div><div><strong>একক যোগ করুন</strong><p>Catalog > Units > New। নাম (যেমন: পিস, কেজি, মিটার, বাক্স) ও সংক্ষেপ দিন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>সম্পাদনা ও মুছে ফেলা</strong><p>যেকোনো সময় নাম পরিবর্তন করুন। কোনো পণ্যে ব্যবহৃত একক মুছা যাবে না।</p></div></div>
        <div class="tip">💡 পণ্য তৈরির সময় ডিফল্ট একক সেট করুন — এটি ইনভয়েস ও স্টক রিপোর্টে দেখাবে।</div>
    </div>

    <!-- STOCK -->
    <div class="section" id="bn-stock">
        <div class="section-title"><div class="ic" style="background:#06b6d4"><i class="bi bi-clipboard-data"></i></div> স্টক ব্যবস্থাপনা</div>
        <div class="step"><div class="step-n">১</div><div><strong>স্টক দেখুন</strong><p>Catalog > Stock — সব পণ্যের বর্তমান পরিমাণ।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>ম্যানুয়াল সমন্বয়</strong><p>ক্ষতি, হারানো, সংশোধনের জন্য Adjust ক্লিক করুন।</p></div></div>
        <div class="step"><div class="step-n">৩</div><div><strong>মুভমেন্ট লগ</strong><p>Stock > Movements — প্রতিটি পরিবর্তনের সম্পূর্ণ রেকর্ড।</p></div></div>
        <div class="tip"><i class="bi bi-lightbulb-fill me-1"></i> <strong>FIFO কস্টিং:</strong> সবচেয়ে পুরনো ক্রয়মূল্য আগে ব্যবহার হয় — সঠিক লাভ গণনা নিশ্চিত।</div>
    </div>

    <!-- BATCHES -->
    <div class="section" id="bn-batches">
        <div class="section-title"><div class="ic" style="background:#0369a1"><i class="bi bi-layers"></i></div> স্টক ব্যাচ (FIFO)</div>
        <p style="color:#6b7280;font-size:.88rem;margin-bottom:10px">প্রতিটি ক্রয়ে একটি স্টক ব্যাচ তৈরি হয়। ব্যাচগুলো পুরনো থেকে নতুন ক্রমে ব্যবহৃত হয় (FIFO) — এতে প্রকৃত পণ্যমূল্য সঠিকভাবে গণনা হয়।</p>
        <div class="step"><div class="step-n">১</div><div><strong>সব ব্যাচ দেখুন</strong><p>Catalog > Batches। প্রতিটি ব্যাচের নম্বর (B000001), প্রাপ্তির তারিখ, একক মূল্য, মূল পরিমাণ এবং <strong>অবশিষ্ট পরিমাণ</strong> দেখুন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>পণ্য অনুযায়ী ফিল্টার করুন</strong><p>পণ্যের নাম দিয়ে সার্চ করলে সেই পণ্যের সব ব্যাচ দেখাবে — কোন কস্ট লেয়ার এখন বিক্রি হচ্ছে তা বোঝা যাবে।</p></div></div>
        <div class="tip">💡 <strong>FIFO কেন?</strong> পণ্য বিক্রির সময় সবচেয়ে পুরনো ব্যাচের মূল্য আগে ব্যবহার হয়। ক্রয়মূল্য ওঠানামা করলেও সঠিক মুনাফা গণনা নিশ্চিত হয়।</div>
    </div>

    <!-- PURCHASES -->
    <div class="section" id="bn-purchases">
        <div class="section-title"><div class="ic" style="background:#2563eb"><i class="bi bi-bag-plus"></i></div> ক্রয়</div>
        <div class="step"><div class="step-n">১</div><div><strong>ক্রয় তৈরি করুন</strong><p>সরবরাহকারী, পণ্য, খরচ দিন। স্টক স্বয়ংক্রিয় বাড়ে।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>বাল্ক ইমপোর্ট</strong><p>স্প্রেডশিট থেকে অনেক আইটেম একসাথে আমদানি।</p></div></div>
        <div class="warn"><i class="bi bi-exclamation-triangle-fill me-1"></i> ক্রয় তৈরির পর সম্পাদনা করা যায় না। ভুল হলে মুছে নতুন করুন।</div>
    </div>

    <!-- BARCODES -->
    <div class="section" id="bn-barcodes">
        <div class="section-title"><div class="ic" style="background:#1f2937"><i class="bi bi-upc"></i></div> বারকোড লেবেল</div>
        <div class="step"><div class="step-n">১</div><div><strong>লেবেল তৈরি ও প্রিন্ট</strong><p>Products > Labels। লেআউট বাছুন: A4 (2/3/4 কলাম), থার্মাল 58mm, থার্মাল 80mm।</p></div></div>
    </div>

    <!-- EXPENSES -->
    <div class="section" id="bn-expenses">
        <div class="section-title"><div class="ic" style="background:#dc2626"><i class="bi bi-wallet2"></i></div> খরচ ট্র্যাকিং</div>
        <div class="step"><div class="step-n">১</div><div><strong>খরচ যোগ করুন</strong><p>শিরোনাম (ভাড়া, বিদ্যুৎ ইত্যাদি), পরিমাণ, ক্যাটাগরি, তারিখ দিন।</p></div></div>
        <div class="step"><div class="step-n">২</div><div><strong>ফিল্টার ও মোট</strong><p>ক্যাটাগরি ও তারিখ অনুযায়ী ফিল্টার করুন। মোট পরিমাণ দেখায়।</p></div></div>
    </div>

    <!-- ACCOUNTING -->
    <div class="section" id="bn-accounting">
        <div class="section-title"><div class="ic" style="background:#0f766e"><i class="bi bi-journal-bookmark"></i></div> অ্যাকাউন্টিং</div>
        <div class="step"><div class="step-n"><i class="bi bi-journal-check"></i></div><div><strong>ডে বুক (Day Book)</strong><p>নির্ধারিত তারিখের সকল আর্থিক লেনদেন — বিক্রয়, ক্রয়, পেমেন্ট, খরচ — কালানুক্রমিকভাবে দেখা যায়। প্রতিদিনের মিলানের জন্য আদর্শ।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-scale"></i></div><div><strong>ট্রায়াল ব্যালেন্স (Trial Balance)</strong><p>সকল অ্যাকাউন্টের ডেবিট ও ক্রেডিট মোটের সারসংক্ষেপ। P&L তৈরির আগে বই মিলিয়ে দেখুন।</p></div></div>
        <div class="tip">💡 Accounting > Day Book — নির্দিষ্ট তারিখে কী হয়েছে তা যাচাই করার সবচেয়ে দ্রুত উপায়।</div>
    </div>

    <!-- REPORTS -->
    <div class="section" id="bn-reports">
        <div class="section-title"><div class="ic" style="background:#7c3aed"><i class="bi bi-graph-up"></i></div> রিপোর্ট</div>
        <div class="step"><div class="step-n"><i class="bi bi-graph-up"></i></div><div><strong>বিক্রয় ও মুনাফা</strong><p>রেভিনিউ, COGS, গ্রস প্রফিট প্রতি বিক্রয় মার্জিন সহ।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-calendar3"></i></div><div><strong>দৈনিক সারসংক্ষেপ</strong><p>দিন অনুযায়ী বিক্রয়, আয় ও মুনাফার বিভাজন।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-trophy"></i></div><div><strong>শীর্ষ পণ্য</strong><p>সর্বাধিক বিক্রিত পণ্য পরিমাণ ও আয় অনুযায়ী।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-bar-chart-line"></i></div><div><strong>লাভ-ক্ষতি (P&L)</strong><p>আয় - পণ্যমূল্য - ফেরত - খরচ = নিট মুনাফা।</p></div></div>
    </div>

    <!-- CONTACTS & LEDGER -->
    <div class="section" id="bn-contacts">
        <div class="section-title"><div class="ic" style="background:#059669"><i class="bi bi-journal-text"></i></div> সরবরাহকারী, ক্রেতা ও লেজার</div>
        <div class="step"><div class="step-n"><i class="bi bi-truck"></i></div><div><strong>সরবরাহকারী</strong><p>নাম, কোম্পানি, ফোন, ইমেইল। লেজার বাটনে ক্লিক করে সম্পূর্ণ লেনদেন দেখুন।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-person"></i></div><div><strong>ক্রেতা</strong><p>POS থেকে দ্রুত যোগ। লেজারে মোট ক্রয়, পরিশোধ, বাকি দেখুন।</p></div></div>
    </div>

    <!-- USERS -->
    <div class="section" id="bn-users">
        <div class="section-title"><div class="ic" style="background:#be185d"><i class="bi bi-person-gear"></i></div> ব্যবহারকারী, ভূমিকা ও অনুমতি</div>
        <div class="step"><div class="step-n"><i class="bi bi-person-plus"></i></div><div><strong>ব্যবহারকারী যোগ</strong><p>নাম, ইমেইল, পাসওয়ার্ড, ভূমিকা নির্ধারণ করুন।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-shield-lock"></i></div><div><strong>ভূমিকা ব্যবস্থাপনা</strong><p>নির্দিষ্ট অনুমতি সহ ভূমিকা তৈরি/সম্পাদনা করুন। সাইডবার স্বয়ংক্রিয় লুকায়।</p></div></div>
        <div class="tip"><i class="bi bi-lightbulb-fill me-1"></i> ৪টি ডিফল্ট ভূমিকা: <strong>Super Admin</strong> (সম্পূর্ণ), <strong>Manager</strong> (ব্যবহারকারী ছাড়া সব), <strong>Storekeeper</strong> (স্টক ও ক্রয়), <strong>Salesperson</strong> (শুধু POS)।</div>
    </div>

    <!-- ADMIN FEATURES -->
    <div class="section" id="bn-admin">
        <div class="section-title"><div class="ic" style="background:#64748b"><i class="bi bi-gear"></i></div> প্রশাসন ও সেটিংস</div>
        <div class="step"><div class="step-n"><i class="bi bi-building"></i></div><div><strong>শাখা</strong><p>একাধিক দোকান/শাখা যোগ ও পরিচালনা করুন।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-gear"></i></div><div><strong>সেটিংস</strong><p>কোম্পানির নাম, ফোন, ইমেইল, ঠিকানা, লোগো — সব ইনভয়েসে দেখায়।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-chat-dots"></i></div><div><strong>SMS নোটিফিকেশন</strong><p>Settings এ SMS চালু করুন, প্রোভাইডার, API কী ও সেন্ডার আইডি দিন।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-star"></i></div><div><strong>লয়ালটি পয়েন্ট</strong><p>Settings এ চালু করুন। প্রতি ১০০ টাকায় কত পয়েন্ট ও রিডিম মূল্য সেট করুন।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-clock-history"></i></div><div><strong>কার্যকলাপ লগ</strong><p>বিক্রয়, ক্রয়, স্টক সমন্বয়, লগইন — কে, কখন, কোন IP থেকে।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-person-check"></i></div><div><strong>লগইন ইতিহাস</strong><p>কে কখন লগইন করেছে, IP ও ব্রাউজার সহ।</p></div></div>
        <div class="step"><div class="step-n"><i class="bi bi-database-down"></i></div><div><strong>ডাটাবেজ ব্যাকআপ</strong><p>Settings > Download Backup — এক ক্লিকে পূর্ণ ডাটাবেজ ডাউনলোড।</p></div></div>
        <div class="warn"><i class="bi bi-exclamation-triangle-fill me-1"></i> নিয়মিত ব্যাকআপ নিন! সমস্যা হলে ব্যাকআপ ফাইল থেকে পুনরুদ্ধার করতে পারবেন।</div>
    </div>

    <!-- API -->
    <div class="section" id="bn-api">
        <div class="section-title"><div class="ic" style="background:#1f2937"><i class="bi bi-plug"></i></div> ই-কমার্স API</div>
        <div class="step"><div class="step-n"><i class="bi bi-code-slash"></i></div><div><strong>REST API এন্ডপয়েন্ট</strong>
            <p>
                GET /api/v1/products — পণ্যের তালিকা<br>
                GET /api/v1/products/{id} — পণ্যের বিস্তারিত<br>
                GET /api/v1/products/{id}/stock — স্টক চেক<br>
                GET /api/v1/categories — ক্যাটাগরি ট্রি
            </p>
        </div></div>
        <div class="tip"><i class="bi bi-lightbulb-fill me-1"></i> Laravel Sanctum টোকেন দিয়ে অথেন্টিকেশন হয়।</div>
    </div>

</div>
</body>
</html>
