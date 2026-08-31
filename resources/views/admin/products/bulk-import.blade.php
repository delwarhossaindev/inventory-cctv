@extends('layouts.app')
@section('title', 'Bulk Import Products')
@section('heading', 'Bulk Import Products')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        @if ($result = session('import_result'))
            <div class="alert alert-{{ $result['created'] > 0 ? 'success' : 'warning' }}">
                <strong>{{ $result['created'] }}</strong> products imported,
                <strong>{{ $result['skipped'] }}</strong> skipped.
                @if (!empty($result['errors']))
                    <ul class="mb-0 mt-2 small">
                        @foreach ($result['errors'] as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Upload CSV or Excel (.xlsx)</div>
            <form action="{{ route('admin.products.bulk-import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" name="file" accept=".csv,.txt,.xlsx" class="form-control" required>
                        <div class="form-text">First row must be the column headers. Max 10 MB.</div>
                    </div>
                    <div class="alert alert-light border small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Same column layout as <code>products.xlsx</code>. Categories are created automatically by name.
                        Only <strong>Name</strong> is required; barcode auto-generates if blank.
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="{{ route('admin.products.import-template') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>Download Template
                    </a>
                    <div>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Supported columns</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($columns as $col)
                        <span class="badge {{ $col === 'Name' ? 'bg-primary' : 'bg-light text-dark border' }}">{{ $col }}</span>
                    @endforeach
                </div>
                <div class="form-text mt-2">Unknown columns are ignored. Column order doesn’t matter — matching is by header name.</div>
            </div>
        </div>
    </div>
</div>
@endsection
