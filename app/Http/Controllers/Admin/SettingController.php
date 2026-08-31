<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage settings');
    }

    public function index()
    {
        $settings = Setting::getAll();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:191',
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:191',
            'currency_symbol' => 'nullable|string|max:10',
            'invoice_footer' => 'nullable|string|max:500',
            'company_logo' => 'nullable|image|max:1024',
        ]);

        $data = $request->only([
            'company_name', 'company_address', 'company_phone',
            'company_email', 'currency_symbol', 'invoice_footer',
            'sms_enabled', 'sms_provider', 'sms_api_key', 'sms_sender_id',
            'loyalty_enabled', 'loyalty_points_per_100', 'loyalty_redeem_value',
        ]);

        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('logos', 'public');
            $data['company_logo'] = '/storage/' . $path;
        }

        Setting::setMany($data);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function backup()
    {
        $dbPath = config('database.connections.sqlite.database');

        if (!file_exists($dbPath)) {
            return back()->with('error', 'Database file not found.');
        }

        ActivityLog::log('backup', 'Downloaded database backup');

        $filename = 'backup-' . date('Y-m-d-His') . '.sqlite';

        return response()->download($dbPath, $filename);
    }
}
