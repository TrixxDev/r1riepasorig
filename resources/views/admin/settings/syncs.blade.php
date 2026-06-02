@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">

      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">Logi</div>
            <div class="card-body logs"></div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header font-weight-bold">Accrual sinhronizācija</div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-3">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="accrual_last_time">{{ $accrual_last_time }}</span></div>
                    <button class="card-body btn" id="accrual">Sinhronizēt</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header font-weight-bold">Sinhronizācijas - Auto riepas</div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <div class="card bg-light">
                      <div class="card-header text-center font-weight-bold">Lattako</div>
                      <button class="card-body btn" id="i3-auto">Sinhronizēt</button>
                      <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="i3auto_last_time">{{ $i3_auto }}</span></div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card bg-light">
                      <div class="card-header text-center font-weight-bold">GoodYear</div>
                      <button class="card-body btn" id="gy-auto">Sinhronizēt</button>
                      <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="gy_last_time">{{ $gy_auto }}</span></div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card bg-light">
                      <div class="card-header text-center font-weight-bold">Riepu Zona</div>
                      <button class="card-body btn" id="rz-auto">Sinhronizēt</button>
                      <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="rz_last_time">{{ $rz_auto }}</span></div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="card bg-light">
                      <div class="card-header text-center font-weight-bold">Riepu Garāža</div>
                      <button class="card-body btn" id="rg-auto">Sinhronizēt</button>
                      <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="rg_last_time">{{ $rg_auto }}</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header font-weight-bold">Sinhronizācijas - Auto diski</div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Lattako</div>
                    <button class="card-body btn" id="i3-alloy-rims">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="i3alloyrims_last_time">{{ $i3_alloy_rims }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header font-weight-bold">Sinhronizācijas - Moto riepas</div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Lattako</div>
                    <button class="card-body btn" id="i3-moto">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="i3moto_last_time">{{ $i3_moto }}</span></div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Duell</div>
                    <button class="card-body btn" id="duell-moto">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="duellmoto_last_time">{{ $duell_moto }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header font-weight-bold">Sinhronizācijas - Kvadraciklu riepas</div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Lattako</div>
                    <button class="card-body btn" id="i3-quadr">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="i3quadr_last_time">{{ $i3_quadr }}</span></div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Duell</div>
                    <button class="card-body btn" id="duell-quadr">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="duellquadr_last_time">{{ $duell_quadr }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header font-weight-bold">Sinhronizācijas - Lielās riepas</div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Lattako (Truck)</div>
                    <button class="card-body btn" id="i3-big">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="i3big_last_time">{{ $i3_big }}</span></div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Lattako (Agro)</div>
                    <button class="card-body btn" id="i3-agro">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="i3agro_last_time">{{ $i3_agro }}</span></div>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="card bg-light">
                    <div class="card-header text-center font-weight-bold">Bohnenkamp</div>
                    <button class="card-body btn" id="starco-big">Sinhronizēt</button>
                    <div class="card-footer text-center font-weight-bold">Pēdējo reizi sinhronizēts<br><span class="starcobig_last_time">{{ $starco_big }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

@endsection
