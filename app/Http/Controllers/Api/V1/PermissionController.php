<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('role_id')) {
            $role = Role::findById($request->role_id);
            $permissions = $role->permissions;
        } elseif ($request->has('user_id')) {
            $user = User::findOrFail($request->user_id);
            $permissions = $user->getAllPermissions();
        } else {
            $permissions = Permission::all();
        }

        $permissions = $permissions->sortBy('name');
        $permissions = PermissionResource::make($permissions);
        return response()->json(['data' => $permissions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $permission = $request->instance . '.' . $request->action;
        Permission::create([
            'name' => $permission,
            'guard_name' => 'api'
        ]);
        return response()->json(['message' => 'Permission created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $instance)
    {
        $permissions = Permission::where('name', 'like', "$instance.%")->get();
        $permissions = PermissionResource::make($permissions);
        return response()->json(['data' => $permissions]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $instance)
    {
        if (is_numeric($instance)) {
            $permissions = Permission::where(['id' => $instance])->get();
        } else {
            $permissions = Permission::where('name', 'like', "$instance.%")->get();
        }

        foreach ($permissions as $permission) {
            $permission->delete();
        }

        return response()->json(['message' => 'Permission deleted successfully']);
    }

    public function assignRole(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $role = Role::findById($request->role_id);

            $user->syncRoles([]);
            $user->assignRole($role->name);
            $user->role_id = $role->id;
            $user->save();

            DB::commit();
            return response()->json(['message' => 'Role assigned successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }

    public function assignRoles(Request $request)
    {
        try {
            DB::beginTransaction();

            $users = User::whereRoleId($request->role_id)->get();

            if ($users->isNotEmpty()) {
                $role = Role::findById($request->role_id);
                foreach ($users as $user) {
                    $user->assignRole($role->name);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Roles assigned successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }

    public function assignUserPermissions(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $user->syncPermissions([]);

            if (!is_array($request->permissions) && $request->permissions == 'all') {
                $user->givePermissionTo(Permission::all());
            } else {
                $user->givePermissionTo($request->permissions);
            }

            DB::commit();
            return response()->json(['message' => 'Permission assigned successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }

    public function assignRolePermissions(Request $request)
    {
        try {
            DB::beginTransaction();

            $role = Role::findById($request->role_id);
            $role->syncPermissions([]);

            if (!is_array($request->permissions) && $request->permissions == 'all') {
                $role->givePermissionTo(Permission::all());
            } else {
                $role->givePermissionTo($request->permissions);
            }

            DB::commit();
            return response()->json(['message' => 'Permission assigned successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?? 500);
        }
    }
}
