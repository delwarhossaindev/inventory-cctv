@extends('layouts.app')
@section('title', 'Add Expense')
@section('heading', 'Add Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <form action="{{ route('admin.expenses.store') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Shop Rent, Electricity" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="form-control @error('amount') is-invalid @enderror" required>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="expense_category_id" class="form-select" id="cat-select">
                                <option value="">Uncategorized</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('expense_category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                                <option value="__new__">+ New Category</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="new-cat-wrap" style="display:none">
                            <label class="form-label">New Category Name</label>
                            <input type="text" name="new_category" class="form-control" placeholder="Category name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <input type="text" name="note" value="{{ old('note') }}" class="form-control" placeholder="Optional note">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Expense</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('cat-select').addEventListener('change', function () {
    document.getElementById('new-cat-wrap').style.display = this.value === '__new__' ? '' : 'none';
    if (this.value === '__new__') this.value = '';
});
</script>
@endpush
