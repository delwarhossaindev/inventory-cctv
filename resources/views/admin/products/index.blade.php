@extends('layouts.app')
@section('title', 'Products')
@section('heading', 'Products')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       style="flex:1 1 220px; min-width:200px;" placeholder="Search name, model, slug...">
                <select name="main_category_id" class="form-select form-select-sm" style="flex:0 1 200px; min-width:170px;">
                    <option value="">All Main Categories</option>
                    @foreach ($mains as $m)
                        <option value="{{ $m->id }}" @selected(request('main_category_id') == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-select form-select-sm" style="flex:0 1 150px; min-width:130px;">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                @can('view products')<a href="{{ route('admin.products.labels') }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-upc"></i> Labels</a>@endcan
                @can('edit products')<a href="{{ route('admin.products.bulk-pricing') }}" class="btn btn-sm btn-outline-success"><i class="bi bi-tags"></i> Bulk Pricing</a>@endcan
                @can('create products')<a href="{{ route('admin.products.bulk-import') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-upload"></i> Import</a>@endcan
                @can('create products')<a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add</a>@endcan
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                {{-- Nine columns will not fit a phone, so the narrow ones drop away
                     and the name cell carries model / category / status instead. --}}
                <tr>
                    <th class="d-none d-md-table-cell">#</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th class="d-none d-lg-table-cell">Model</th>
                    <th class="d-none d-xl-table-cell">Main</th>
                    <th class="d-none d-xl-table-cell">Category</th>
                    <th class="d-none d-xl-table-cell">Sub</th>
                    <th class="d-none d-md-table-cell">Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td class="text-muted d-none d-md-table-cell">{{ $product->id }}</td>
                        <td>
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="" width="42" height="42" class="rounded object-fit-cover" style="object-fit:cover;">
                            @else
                                <span class="text-muted"><i class="bi bi-image"></i></span>
                            @endif
                        </td>
                        <td style="min-width:160px">
                            <a href="{{ route('admin.products.show', $product) }}" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                            <div class="small text-muted d-none d-lg-block">{{ $product->slug }}</div>
                            {{-- Stands in for the columns hidden on narrow screens. --}}
                            <div class="small text-muted d-lg-none">
                                {{ $product->model ?: '—' }}
                                <span class="d-xl-none">· {{ optional($product->category)->name ?: '—' }}</span>
                            </div>
                            @if ($product->status !== 'active')
                                <span class="badge bg-secondary d-md-none">{{ ucfirst($product->status) }}</span>
                            @endif
                        </td>
                        <td class="d-none d-lg-table-cell">{{ $product->model ?: '—' }}</td>
                        <td class="small d-none d-xl-table-cell">{{ optional($product->mainCategory)->name ?: '—' }}</td>
                        <td class="small d-none d-xl-table-cell">{{ optional($product->category)->name ?: '—' }}</td>
                        <td class="small d-none d-xl-table-cell">{{ optional($product->subCategory)->name ?: '—' }}</td>
                        <td class="d-none d-md-table-cell"><span class="badge bg-{{ $product->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($product->status) }}</span></td>
                        <td class="text-end text-nowrap">
                            @can('edit products')<a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>@endcan
                            @can('delete products')
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $products->links() }}</div>
</div>
@endsection
