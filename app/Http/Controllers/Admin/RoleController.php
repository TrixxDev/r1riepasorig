<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{

  public function __construct()
  {

  }

  public function index()
  {

//    $role = Role::with('permissions')->where('name', 'Super Admin')->first();
//    $role = Role::create(['name' => 'Super Admin']);
//    $role->givePermissionTo('edit users');
//    dd($role);

    $roles = Role::with('permissions')->get();

    return view('admin.roles.index', compact('roles'));
  }

  public function create()
  {
    return view('admin.roles.create');
  }

  public function insert(Request $request)
  {
    $role = Role::where('name', $request->role)->first();
    if (!$role) {
      $role = Role::create(['name' => $request->role]);
      if ($role) {
        return redirect(route('admin.settings.roles'));
      }
    } else {
      return redirect(route('admin.settings.roles.create'))->with('danger', 'Šāda loma jau eksistē!');
    }
  }

  public function destroy($id)
  {
    $role = Role::where('id', $id)->delete();
    if ($role) return redirect()->back();
  }

  public function togglePermission(Request $request) {
    $data = $request->data;
    $data = explode('-', $data);
    $enabled = ($data[0] == 'enable') ? 1 : 0;
    $roleId = $data[1];
    $permissionId = $data[2];

    $role = Role::findById($roleId);
    $permission = Permission::findById($permissionId);

    if ($enabled == 1) {
      $role->givePermissionTo($permission->name);
      return json_encode(['done' => 'Ieslēgts']);
    } else {
      $role->revokePermissionTo($permission->name);
      return json_encode(['done' => 'Izslēgts']);
    }
  }

  public function removePermission($id)
  {

    $permission = Permission::findById($id)->delete();
    if ($permission) return redirect()->back();

  }

}
