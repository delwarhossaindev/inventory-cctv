<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct()
    {
        // 'children' stays open to any authenticated user — the product form needs it.
        $this->middleware('permission:manage categories')->except('children');
    }

    public function index(Request $request)
    {
        $query = Category::with('parent')->orderBy('level')->orderBy('name');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create', [
            'category' => new Category(),
            'mains' => Category::level(Category::LEVEL_MAIN)->orderBy('name')->get(),
            'cats' => Category::level(Category::LEVEL_CATEGORY)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'mains' => Category::level(Category::LEVEL_MAIN)->orderBy('name')->get(),
            'cats' => Category::level(Category::LEVEL_CATEGORY)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validateData($request);

        if ($data['name'] !== $category->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Return child categories of a given parent as JSON (for dependent dropdowns).
     */
    public function children(Request $request)
    {
        $children = Category::where('parent_id', $request->parent_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($children);
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'in:1,2,3'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Level 1 (Main) has no parent.
        if ((int) $data['level'] === Category::LEVEL_MAIN) {
            $data['parent_id'] = null;
        }

        return $data;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Category::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
