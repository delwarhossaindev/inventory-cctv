<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage roles');
    }

    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create', [
            'role' => new Role(),
            'groups' => $this->groupedPermissions(),
            'assigned' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role' => $role,
            'groups' => $this->groupedPermissions(),
            'assigned' => $role->permissions->pluck('name')->all(),
        ]);
    }

    /**
     * All permissions keyed by their group (custom ones included), for the role screen.
     *
     * @return array<string, array<int, string>>
     */
    private function groupedPermissions(): array
    {
        return Permission::orderBy('group')->orderBy('name')->get()
            ->groupBy(fn ($p) => $p->group ?: 'Other')
            ->map(fn ($g) => $g->pluck('name')->all())
            ->all();
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validateData($request, $role->id);

        // Renaming "Super Admin" would break the Gate::before bypass — keep it locked.
        if ($role->name !== 'Super Admin') {
            $role->update(['name' => $data['name']]);
        }
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Super Admin'])) {
            return back()->with('success', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($ignoreId)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);
    }
}
