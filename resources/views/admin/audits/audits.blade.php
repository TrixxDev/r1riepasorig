@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">Notikumu žurnāls</div>
            <div class="card-body">
              <div class="row">
                <div class="col-12">
                  <form method="get" id="auditFilters">
                    <div class="form-row mb-2">
                      <div class="col-md-2">
                        <label class="small">Modelis</label>
                        <select name="model" class="form-control form-control-sm">
                          <option value="">Visi</option>
                          @foreach ($models as $model_name => $model)
                            <optgroup label="{{ $model['title'] }}">
                              @foreach ($model['searchBy'] as $model_col => $model_title)
                                <option @if ($filters['modelname'] == $model_name . ';' . $model_col) selected @endif value="{{ $model_name }};{{ $model_col }}">{{ ucfirst($model_title) }}</option>
                              @endforeach
                            </optgroup>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="small">Parametri</label>
                        <input type="text" name="params" class="form-control form-control-sm" placeholder="Meklēt..." value="{{ $filters['param'] }}">
                      </div>
                      <div class="col-md-2">
                        <label class="small">Datums no</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                      </div>
                      <div class="col-md-2">
                        <label class="small">Datums līdz</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                      </div>
                      <div class="col-md-2">
                        <label class="small">Lietotājs</label>
                        <select name="user_id" class="form-control form-control-sm">
                          <option value="">Visi</option>
                          @foreach ($users as $user)
                            <option @if ($filters['user_id'] == $user->id) selected @endif value="{{ $user->id }}">{{ $user->fullName }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label class="small">Apakšsistēma</label>
                        <select name="facility" class="form-control form-control-sm">
                          <option value="">Visas</option>
                          @foreach ($facilities as $fac_id => $fac_name)
                            <option @if ($filters['facility'] == $fac_id) selected @endif value="{{ $fac_id }}">{{ $fac_name }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="col-md-2">
                        <label class="small">Nozīmīgums</label>
                        <select name="severity" class="form-control form-control-sm">
                          <option value="">Visi</option>
                          @foreach ($severities as $sev_id => $sev_name)
                            <option @if ($filters['severity'] == $sev_id) selected @endif value="{{ $sev_id }}">{{ $sev_name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-10">
                        <label class="small">&nbsp;</label><br>
                        <button type="submit" class="btn btn-success btn-sm">
                          <i class="cil-search"></i> Meklēt
                        </button>
                        <a href="{{ route('admin.audits') }}" class="btn btn-warning btn-sm">
                          <i class="cil-x"></i> Notīrīt filtrus
                        </a>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                      <th width="50">&nbsp;</th>
                      <th width="">Laiks</th>
                      <th width="">Apraksts</th>
                      <th width="">Apakšsistēma</th>
                      <th width="">Lietotājs</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($audits as $audit)
                      <tr style="cursor: pointer;" onclick="window.location.href='{{ route('admin.audit', $audit->id) }}'">
                        <td></td>
                        <td>{{ $audit->audit_time }}</td>
                        <td>{{ $audit->formatted_event }}</td>
                        <td align="center">{{ \App\Models\Audit::get_facility_name($audit->audit_facility) }}</td>
                        <td align="center">{{ $audit->user_display }}</td>
                      </tr>
                    @endforeach
                    </tbody>
                  </table>
                  {{ $audits->links() }}
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

@endsection


