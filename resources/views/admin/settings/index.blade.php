@extends('layouts.app')
@section('title', 'Business Settings')
@section('heading', 'Business Settings')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-building me-2"></i>Company Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" class="form-control @error('company_name') is-invalid @enderror" required>
                            @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}" class="form-control @error('company_email') is-invalid @enderror">
                            @error('company_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" class="form-control" style="max-width:120px">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="company_address" rows="2" class="form-control">{{ old('company_address', $settings['company_address']) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Invoice Footer Text</label>
                            <input type="text" name="invoice_footer" value="{{ old('invoice_footer', $settings['invoice_footer']) }}" class="form-control" placeholder="Thank you for your business!">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-image me-2"></i>Company Logo</div>
                <div class="card-body text-center">
                    @if ($settings['company_logo'])
                        <img src="{{ $settings['company_logo'] }}" alt="Logo" class="img-fluid mb-3" style="max-height:120px">
                    @else
                        <div class="bg-light rounded p-4 mb-3">
                            <i class="bi bi-building fs-1 text-muted"></i>
                            <div class="text-muted small mt-1">No logo uploaded</div>
                        </div>
                    @endif
                    <input type="file" name="company_logo" accept="image/*" class="form-control form-control-sm @error('company_logo') is-invalid @enderror">
                    @error('company_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="text-muted small mt-1">Max 1MB. PNG or JPG recommended.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
    </div>
</form>

<div class="row g-4 mt-2">
    {{-- SMS Configuration --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-chat-dots me-2"></i>SMS Notification</div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="sms_enabled" value="0">
                        <input type="checkbox" name="sms_enabled" value="1" class="form-check-input" {{ ($settings['sms_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label">Enable SMS</label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Provider</label>
                        <select name="sms_provider" class="form-select form-select-sm">
                            <option value="">Select</option>
                            <option value="bulksmsbd" @selected(($settings['sms_provider'] ?? '') === 'bulksmsbd')>BulkSMSBD</option>
                            <option value="sslwireless" @selected(($settings['sms_provider'] ?? '') === 'sslwireless')>SSL Wireless</option>
                            <option value="twilio" @selected(($settings['sms_provider'] ?? '') === 'twilio')>Twilio</option>
                            <option value="custom" @selected(($settings['sms_provider'] ?? '') === 'custom')>Custom API</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">API Key</label>
                        <input type="text" name="sms_api_key" value="{{ $settings['sms_api_key'] ?? '' }}" class="form-control form-control-sm" placeholder="API key">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Sender ID</label>
                        <input type="text" name="sms_sender_id" value="{{ $settings['sms_sender_id'] ?? '' }}" class="form-control form-control-sm" placeholder="Sender ID / Mask">
                    </div>
                    <button class="btn btn-sm btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save SMS Config</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Loyalty Points --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-star me-2"></i>Loyalty Points</div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="loyalty_enabled" value="0">
                        <input type="checkbox" name="loyalty_enabled" value="1" class="form-check-input" {{ ($settings['loyalty_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label">Enable Loyalty Points</label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Points per ৳100 spent</label>
                        <input type="number" name="loyalty_points_per_100" value="{{ $settings['loyalty_points_per_100'] ?? 1 }}" class="form-control form-control-sm" min="0">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">1 Point = ৳ ?</label>
                        <input type="number" step="0.01" name="loyalty_redeem_value" value="{{ $settings['loyalty_redeem_value'] ?? 1 }}" class="form-control form-control-sm" min="0">
                    </div>
                    <button class="btn btn-sm btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Loyalty Config</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Database Backup --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-database-down me-2"></i>Database Backup</div>
            <div class="card-body text-center">
                <p class="text-muted small mb-3">Download a full copy of your database. Keep it safe!</p>
                <a href="{{ route('admin.settings.backup') }}" class="btn btn-outline-success" download onclick="return confirm('Download database backup?')">
                    <i class="bi bi-download me-1"></i>Download Backup
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
