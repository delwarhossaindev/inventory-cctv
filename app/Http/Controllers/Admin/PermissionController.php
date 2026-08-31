<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage permissions');
    }

    public function index(Request $request)
    {
        $query = Permission::withCount('roles')->orderBy('group')->orderBy('name');
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        return view('admin.permissions.index', [
            'permissions' => $query->paginate(30)->withQueryString(),
            'groups' => Permission::query()->whereNotNull('group')->distinct()->orderBy('group')->pluck('group'),
        ]);
    }

    public function create()
    {
        return view('admin.permissions.create', [
            'permission' => new Permission(),
            'groups' => $this->existingGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $permission = Permission::create(['name' => $data['name'], 'guard_name' => 'web']);
        $permission->forceFill(['group' => $data['group'] ?: null])->save();

        $this->flushCache();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', [
            'permission' => $permission,
            'groups' => $this->existingGroups(),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $this->validateData($request, $permission->id);

        $permission->name = $data['name'];
        $permission->group = $data['group'] ?: null;
        $permission->save();

        $this->flushCache();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        $this->flushCache();

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($ignoreId)],
            'group' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function existingGroups()
    {
        return Permission::query()->whereNotNull('group')->distinct()->orderBy('group')->pluck('group');
    }

    private function flushCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
