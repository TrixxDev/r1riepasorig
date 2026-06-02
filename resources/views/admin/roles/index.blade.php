@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">

    @if (session('success'))
      <div class="alert alert-success">
        {{ session('success') }}
      </div>
    @endif

    <div class="row">

      <div class="col-9">

        <div class="m-4 role-settings">
          <ul class="nav nav-tabs" id="myTab">
            @foreach ($roles as $role)
            @if ($loop->first)
              @php $id = $role->id; @endphp
            @endif
            <li class="nav-item">
              <a href="#{{ Str::slug($role->name) }}" data-id="{{ $role->id }}" class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab">{{ ucwords($role->name) }}</a>
            </li>
            @endforeach
          </ul>
          <div class="tab-content">
            @foreach ($roles as $role)
            <div class="tab-pane @if ($loop->first) show active @endif fade" data-role-id="{{ $role->id }}" id="{{ Str::slug($role->name) }}">
              <h3>Atļaujas</h3>
              <div class="permissions">
              @foreach (\Spatie\Permission\Models\Permission::all() as $permission)
                <span class="permission">
                  <input type="checkbox" data-switch="@if ($role->hasPermissionTo($permission->name)) {{ 'disable-' . $role->id . '-' . $permission->id }} @else {{ 'enable-' . $role->id . '-' . $permission->id }} @endif" value="{{ $permission->id }}" @if ($role->hasPermissionTo($permission->name)) checked @endif>{{ ucwords($permission->name) }} <a href="{{ route('admin.settings.roles.removePermission', $permission->id) }}">Dzēst</a><br>
                </span>
              @endforeach
              </div>
            </div>
            @endforeach
          </div>
          <ul class="nav nav-tabs revert">
            <li class="nav-item">
              <a href="{{ route('admin.settings.roles.create') }}" class="nav-link">Jauna loma</a>
            </li>
            <li class="nav-item delete-role">
              <a href="{{ route('admin.settings.roles.destroy', $id) }}" onclick="if (!confirm('Tiešām vēlaties dzēst?')) return false" class="nav-link">Dzēst lomu</a>
            </li>
            <hr style="border: double!important; border-color: transparent!important;">
            <li class="nav-item delete-role">
              <a href="#" class="nav-link">Izveidot atļauju</a>
            </li>
          </ul>
        </div>

      </div>

    </div>

  </div>

@endsection
