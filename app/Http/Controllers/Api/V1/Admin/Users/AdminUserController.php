<?php

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\AdminListUserRequest;
use App\Http\Requests\Admin\User\AdminStoreUserRequest;
use App\Http\Requests\Admin\User\AdminUpdateUserRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the users.
     */
    public function index(AdminListUserRequest $request)
    {
        $query = User::query()->with('roles');

        // Apply filters more efficiently
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleName = $request->input('role')) {
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('show_deleted')) {
            $query->withTrashed();
        }

        // Validate and apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        
        if (in_array($sortBy, ['id', 'name', 'email', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Limit maximum per page
        $perPage = min($request->input('per_page', 15), 100);
        $users = $query->paginate($perPage);

        return $this->successResponse(
            AdminUserResource::collection($users)->response()->getData(true),
            'Users retrieved successfully'
        );
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(AdminStoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            $userData = $request->validated();

            // Extraer roles; usar 'user' por defecto si no se proporcionan
            $roleNames = $userData['roles'] ?? ['user'];
            unset($userData['roles']);

            // Extraer permisos directos si se proporcionan (opcional)
            $permissions = null;
            if (isset($userData['permissions'])) {
                $permissions = $userData['permissions'];
                unset($userData['permissions']);
            }

            // Encriptar password y asegurar que is_active esté definido
            $userData['password'] = Hash::make($userData['password']);
            $userData['is_active'] = $userData['is_active'] ?? true;

            // Crear el usuario
            $user = User::create($userData);

            // Asignar roles utilizando el método centralizado del trait HasRoles
            $user->syncRolesByName($roleNames);

            // Asignar permisos directos si se han proporcionado
            if ($permissions) {
                $user->syncPermissions($permissions);
            }

            DB::commit();

            return $this->successResponse(
                new AdminUserResource($user->fresh('roles')),
                'User created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(int $id)
    {
        $user = User::with('roles')->findOrFail($id);
        
        return $this->successResponse(
            new AdminUserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Update the specified user in storage.
     */
    public function update(AdminUpdateUserRequest $request, int $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $userData = $request->validated();

            // Si se proporcionan roles, sincronizarlos utilizando el método centralizado
            if (isset($userData['roles'])) {
                $user->syncRolesByName($userData['roles']);
                unset($userData['roles']);
            }

            // Si se proporcionan permisos directos, sincronizarlos
            if (isset($userData['permissions'])) {
                $permissions = $userData['permissions'];
                unset($userData['permissions']);
                $user->syncPermissions($permissions);
            }

            // Encriptar el password si se actualiza
            if (isset($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            }

            // Actualizar el usuario con los datos restantes
            $user->update($userData);

            DB::commit();

            return $this->successResponse(
                new AdminUserResource($user->fresh('roles')),
                'User updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return $this->errorResponse('You cannot delete your own account', 403);
            }
            
            $user->delete();

            return $this->successResponse(
                null,
                'User deleted successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int $id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();

            return $this->successResponse(
                new AdminUserResource($user),
                'User restored successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to restore user: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete(int $id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);
            
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                return $this->errorResponse('You cannot delete your own account', 403);
            }
            
            // Detach all roles
            $user->roles()->detach();
            
            // Force delete
            $user->forceDelete();

            return $this->successResponse(
                null,
                'User permanently deleted'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to permanently delete user: ' . $e->getMessage());
        }
    }

    /**
     * Update user's active status.
     */
    public function toggleActive(int $id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deactivating yourself
            if ($user->id === auth()->id()) {
                return $this->errorResponse('You cannot change your own active status', 403);
            }
            
            $user->is_active = !$user->is_active;
            $user->save();

            $status = $user->is_active ? 'activated' : 'deactivated';

            return $this->successResponse(
                new AdminUserResource($user),
                "User {$status} successfully"
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update user status: ' . $e->getMessage());
        }
    }
}
