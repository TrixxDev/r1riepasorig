<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="CoreUI - Open Source Bootstrap Admin Template">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Łukasz Holeczek">
    <meta name="keyword" content="Bootstrap,Admin,Template,Open,Source,jQuery,CSS,HTML,RWD,Dashboard">
    <title>R1Riepas admin</title>
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('admins/assets/favicon/apple-icon-57x57.png?rev=' . time()) }}">
    <link rel="manifest" href="{{ asset('admins/assets/favicon/manifest.json?rev=' . time()) }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('admins/assets/favicon/ms-icon-144x144.png?rev=' . time()) }}">
    <meta name="theme-color" content="#ffffff">
    <link href="{{ asset('admins/css/style.css?rev=' . time()) }}" rel="stylesheet">
    <link href="{{ asset('admins/css/dataTable.bootstrap4.css?rev=' . time()) }}" rel="stylesheet">
    <link href="{{ asset('admins/css/coreui-chartjs.css?rev=' . time()) }}" rel="stylesheet">
    <link href="{{ asset('admins/css/bootstrap-multiselect.css?rev=' . time()) }}" rel="stylesheet">
    <link href="{{ asset('admins/assets/coreui-icons/css/free.min.css?rev=' . time()) }}" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
  <body class="c-app">
    <div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">
      <div class="c-sidebar-brand d-lg-down-none">
        R1Riepas<i class="cil-energy"></i>
      </div>
      <ul class="c-sidebar-nav">
        <li class="c-sidebar-nav-title">Kopīgais</li>
        <li class="c-sidebar-nav-item c-sidebar-nav-dropdown"><a class="c-sidebar-nav-link" href="{{ route('admin.home') }}">
            <svg class="c-sidebar-nav-icon">
              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-star"></use>
            </svg> Sākums</a>
        </li>
        <li class="c-sidebar-nav-divider"></li>
        <li class="c-sidebar-nav-title">Riepas</li>
        <li class="c-sidebar-nav-item c-sidebar-nav-dropdown"><a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
            <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-car-alt"></use>
                </svg> Auto riepas</a>
            <ul class="c-sidebar-nav-dropdown-items">
                <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.auto.tires') }}" target="_top">
                    <svg class="c-sidebar-nav-icon">
                        <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                    </svg> Visas riepas</a>
                </li>
{{--                <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.auto.brands') }}" target="_top">--}}
{{--                    <svg class="c-sidebar-nav-icon">--}}
{{--                        <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>--}}
{{--                    </svg> Riepu brendi</a>--}}
{{--                </li>--}}
{{--                <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.auto.treads') }}" target="_top">--}}
{{--                    <svg class="c-sidebar-nav-icon">--}}
{{--                        <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>--}}
{{--                    </svg> Riepu modeļi</a>--}}
{{--                </li>--}}
                <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.auto.import') }}" target="_top">
                    <svg class="c-sidebar-nav-icon">
                        <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                    </svg> Riepu imports</a>
                </li>
            </ul>
        </li>
          <li class="c-sidebar-nav-item c-sidebar-nav-dropdown"><a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
                  <svg class="c-sidebar-nav-icon">
                      <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-life-ring"></use>
                  </svg> Kvadru riepas</a>
              <ul class="c-sidebar-nav-dropdown-items">
                  <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.quadr.tires') }}" target="_top">
                          <svg class="c-sidebar-nav-icon">
                              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                          </svg> Visas riepas</a>
                  </li>
                  <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.quadr.import') }}" target="_top">
                          <svg class="c-sidebar-nav-icon">
                              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                          </svg> Riepu imports</a>
                  </li>
              </ul>
          </li>
          <li class="c-sidebar-nav-item c-sidebar-nav-dropdown"><a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
                  <svg class="c-sidebar-nav-icon">
                      <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-bike"></use>
                  </svg> Moto riepas</a>
              <ul class="c-sidebar-nav-dropdown-items">
                  <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.moto.tires') }}" target="_top">
                          <svg class="c-sidebar-nav-icon">
                              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                          </svg> Visas riepas</a>
                  </li>
                  <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.moto.import') }}" target="_top">
                          <svg class="c-sidebar-nav-icon">
                              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                          </svg> Riepu imports</a>
                  </li>
              </ul>
          </li>
          <li class="c-sidebar-nav-item c-sidebar-nav-dropdown"><a class="c-sidebar-nav-link c-sidebar-nav-dropdown-toggle" href="#">
                  <svg class="c-sidebar-nav-icon">
                      <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-bike"></use>
                  </svg> Lielās riepas</a>
              <ul class="c-sidebar-nav-dropdown-items">
                  <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.big.tires') }}" target="_top">
                          <svg class="c-sidebar-nav-icon">
                              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                          </svg> Visas riepas</a>
                  </li>
                  <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.big.import') }}" target="_top">
                          <svg class="c-sidebar-nav-icon">
                              <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                          </svg> Riepu imports</a>
                  </li>
              </ul>
          </li>
          <li class="c-sidebar-nav-title">Radzes</li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.studs.index') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-cog"></use>
              </svg> Radzes</a>
          </li>
          <li class="c-sidebar-nav-title">Diski</li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.rims') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-soccer"></use>
              </svg> Jauni lietie diski</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.quadrims') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-soccer"></use>
              </svg> Kvadru diski</a>
          </li>

          <li class="c-sidebar-nav-title">Rezervācijas</li>
{{--          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.reservations') }}" target="_top">--}}
{{--              <svg class="c-sidebar-nav-icon">--}}
{{--                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-list-numbered"></use>--}}
{{--              </svg> Rezervācijas</a>--}}
{{--          </li>--}}
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.records') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-clock"></use>
              </svg> Darba laiki</a>
          </li>
          <li class="c-sidebar-nav-title">Veikals</li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.orders') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-boat-alt"></use>
              </svg> Pasūtījumi</a>
          </li>
          <li class="c-sidebar-nav-title">Iestatījumi</li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.audits') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-list"></use>
              </svg> Notikumu žurnāls</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.promo.index') }}" target="_top">
                  <svg class="c-sidebar-nav-icon">
                      <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-list"></use>
                  </svg> Promo kodi</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.settings.services') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-list"></use>
              </svg> Pakalpojumi</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.settings.users') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-lock-locked"></use>
              </svg> Lietotāji</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.settings.syncs') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-sync"></use>
              </svg> Sinhronizācijas</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.settings.pages') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-browser"></use>
              </svg> Lapas</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.settings.banners') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-bullhorn"></use>
              </svg> Skrienošā josla</a>
          </li>
          <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.settings.codes') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-description"></use>
              </svg> Paskaidrojumi</a>
          </li>
	  <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="{{ route('admin.settings.prices') }}" target="_top">
              <svg class="c-sidebar-nav-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-dollar"></use>
              </svg> Cenas</a>
          </li>

      </ul>
      <button class="c-sidebar-minimizer c-class-toggler" type="button" data-target="_parent" data-class="c-sidebar-minimized"></button>
    </div>
    <div class="c-wrapper c-fixed-components">
      <header class="c-header c-header-light c-header-fixed c-header-with-subheader">
        <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
          <svg class="c-icon c-icon-lg">
            <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-menu"></use>
          </svg>
        </button><a class="c-header-brand d-lg-none" href="#">
          <svg width="118" height="46" alt="CoreUI Logo">
            <use xlink:href="{{ asset('admins/assets/brand/coreui.svg#full') }}"></use>
          </svg></a>
        <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true">
          <svg class="c-icon c-icon-lg">
            <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-menu"></use>
          </svg>
        </button>
        <ul class="c-header-nav ml-auto mr-4">
          <li class="c-header-nav-item d-md-down-none mx-2">
              <select class="form-control" id="seasonChange">
                  <option value="1" @if (config('site.season') === 1){{'selected'}}@endif>Vasara</option>
                  <option value="2" @if (config('site.season') === 2){{'selected'}}@endif>Ziema</option>
              </select>
          </li>
          <li class="c-header-nav-item d-md-down-none mx-2"><a class="c-header-nav-link" href="#">
              <svg class="c-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-bell"></use>
              </svg></a></li>
          <li class="c-header-nav-item d-md-down-none mx-2"><a class="c-header-nav-link" href="#">
              <svg class="c-icon">
                <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-list-rich"></use>
              </svg></a></li>
          <li class="c-header-nav-item d-md-down-none mx-2"><a class="c-header-nav-link" href="#">
              <svg class="c-icon">
                <use xlink:href="{{ asset('admins/assets/icons/envelope-open.svg') }}"></use>
              </svg></a></li>
          <li class="c-header-nav-item dropdown"><a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
              <div class="c-avatar"><img class="c-avatar-img" src="{{ asset('admins/assets/img/avatars/6.jpg') }}" alt="@if (Auth::check()){{ Auth::user()->email }}@endif"></div>
            </a>
            <div class="dropdown-menu dropdown-menu-right pt-0">
              <div class="dropdown-header bg-light py-2">
                <strong>Profils</strong>
              </div>
              <a class="dropdown-item" href="#">
                <svg class="c-icon mr-2">
                  <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-bell"></use>
                </svg> Jauni pasūtījumi<span class="badge badge-info ml-auto">0</span>
              </a>
              <div class="dropdown-header bg-light py-2">
                <strong>Iestatījumi</strong>
              </div>
              <a class="dropdown-item" href="{{ route('admin.settings.user.pwdChange', Auth::user()->id) }}">
                <svg class="c-icon mr-2">
                  <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-user"></use>
                </svg> Parole maiņa
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="/logout">
                <svg class="c-icon mr-2">
                  <use xlink:href="{{ asset('admins/assets/coreui-icons/sprites/free.svg') }}#cil-account-logout"></use>
                </svg> Iziet
              </a>
            </div>
          </li>
        </ul>
      </header>
      <div class="c-body">
        <main class="c-main">
          @yield('content')
        </main>
        <footer class="c-footer">
        </footer>
      </div>
    </div>
    <script src="https://unpkg.com/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://unpkg.com/tippy.js@4.3.5/umd/index.all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://cdn.tiny.cloud/1/3nivlf7ukirc5znzq6r1m68qaf80subltkj10h3an5njfepn/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        $(document).ready(function() {


            $('#add-banner-button').on('click', function() {
              $('#add-image-card').toggle();
            })

            $('.fdjhifudhfds label').mouseup(function(e) {
              e.preventDefault();
              console.log($(this));
            });

            $('#formFile').change(function(){
              let reader = new FileReader();
              reader.onload = (e) => {
                $('#preview-image').attr('src', e.target.result).css('width', '760px').css('height', '100px');
              }
              reader.readAsDataURL(this.files[0]);
              $('.fdjhifudhfds').submit();
            });

            let pathParts = window.location.pathname.split('/');
            let tread_id = pathParts[4];

            $('#file-input').on('change', function(){
                const [file] = this.files;
                if (file) {
                    $('.preview-image img').attr('src', URL.createObjectURL(file));
                }
            });

            $('button[type=clear]').on('click', function(e) {
                e.preventDefault();
                $('#file-input').val(null);
                $('.preview-image img').attr('src', '/storage/app/public/' + pathParts[3] + '/' + pathParts[2] + '_' + tread_id + '.png');
            });

            $('.brand_delete').on('click', function() {
                let tire_id = $(this).attr('id');
                if (confirm('Vai tiešām vēlaties dzēst?') === true) {
                    window.location.href = window.location + '/' + tire_id + '/delete';
                } else {
                    return false;
                }
            });
        });

        tinymce.init({
          selector: 'textarea#editor',
          plugins: 'advlist autolink lists link image charmap preview anchor pagebreak',
          toolbar_mode: 'floating',
        });
    </script>
    <script>
      $(document).ready(function() {
        $('select[name="status[]"]').multiselect();
      });
    </script>
    <script src="{{ asset('admins/js/ajaxUpdate.js?rev=' . time()) }}"></script>
    <script src="{{ asset('admins/js/bootstrap-multiselect.js?rev=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- CoreUI and necessary plugins-->
    <script src="{{ asset('admins/js/coreui.bundle.min.js?rev=' . time()) }}"></script>
    <!--[if IE]><!-->
    <script src="{{ asset('admins/js/svgxuse.min.js?rev=' . time()) }}"></script>
    <!--<![endif]-->
    <!-- Plugins and scripts required by this view-->
    <script src="{{ asset('admins/js/coreui-chartjs.bundle.js?rev=' . time()) }}"></script>
    <script src="{{ asset('admins/js/coreui-utils.js?rev=' . time()) }}"></script>
    <script src="{{ asset('admins/js/main.js?rev=' . time()) }}"></script>

    @yield('scripts')
  </body>
</html>
