@extends('layouts.app')

@section('content')


  <div class="container">
    <div class="row">
      <div class="main-content clearfix col-md-12 col-xl-10">
        <div id="content-wrapper" class="right-column col-lg-12">
          <section id="main">
            <div class="cart-grid row">
              @php
                $carInfoToken = app(\App\Services\CarInfoTokenService::class)->issue(request());
              @endphp
              <form method="POST" class="cart-form" data-car-info-url="{{ url('/api/car-info') }}">
                @CSRF
                <input type="hidden" name="car_info_token" value="{{ $carInfoToken }}">

                <div class="stepper-wrapper">
                  <ol class="stepper">
                    <li class="stepper-item stepper-completed">
                      <h3 class="stepper-title hidden-md-down">Grozs</h3>
                    </li>
                    <li class="stepper-item stepper-active">
                      <h3 class="stepper-title hidden-md-down">Dati</h3>
                    </li>
                    <li class="stepper-item">
                      <h3 class="stepper-title hidden-md-down">Maksājums</h3>
                    </li>
                    <li class="stepper-item stepper-last">
                      <h3 class="stepper-title hidden-md-down">Pabeigts</h3>
                    </li>
                  </ol>
                </div>

              <h1 class="cart-header">Pasūtījuma noformēšana - <span id="status-name">Privātpersona</span></h1>

              <div class="cart-card card row">

                <div class="btn-group btn-group-toggle person-status-container col-md-12" data-toggle="buttons">

                  <label class="btn btn-secondary @if ($existingOrder && !$existingOrder->company_reg_nr) active @endif person-status-item" id="f">
                    <input type="radio" name="data[status]" @if ($existingOrder && !$existingOrder->company_reg_nr) checked @endif value="1">
                    Fiziska persona

                  </label>

                  <label class="btn btn-secondary person-status-item @if ($existingOrder && $existingOrder->company_reg_nr) active @endif" id="j">
                    <input type="radio" name="data[status]" @if ($existingOrder && $existingOrder->company_reg_nr) checked @endif value="2">
                    Juridiska persona
                  </label>

                </div>
              </div>


              <div class="cart-card card">
                <h4>Pamatinformācija</h4>

                  <div class="row justify-content-between text-left ">
                    <div class="form-group col-sm-6 flex-column d-flex">
                      <label for="name">Vārds<span class="required-field"> *</span></label>
                      <input type="text" class="form-control" name="data[name]" id="name" value="{{ old('data.name', $existingOrder->customer_name ?? '') }}" placeholder="Vārds" required>
                    </div>
                    <div class="form-group col-sm-6 flex-column d-flex">
                      <label for="surname">Uzvārds<span class="required-field"> *</span></label>
                      <input type="text" class="form-control" name="data[surname]" id="surname" value="{{ old('data.surname', $existingOrder->customer_surname ?? '') }}" placeholder="Uzvārds" required>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="email">E-pasts<span class="required-field"> *</span></label>
                    <input type="email" class="form-control @error('data.email') is-invalid @enderror" name="data[email]" id="email" value="{{ old('data.email', $existingOrder->email ?? '') }}" placeholder="E-pasts" required>
                    @error('data.email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                  </div>

                  <div class="form-group">
                    <label for="phone_number">Tālrunis<span class="required-field"> *</span></label>
                    <div class="phone-input-group @error('data.phone_number') has-error @enderror">
                      <select class="form-select country-select @error('data.phone_number') is-invalid @enderror" name="data[phone_country_code]" id="phone_country_code">
                        @php
                          $countryCodes = [
                            ['code' => '+371', 'flag' => '🇱🇻', 'country' => 'LV', 'name' => 'Latvia'],
                            ['code' => '+370', 'flag' => '🇱🇹', 'country' => 'LT', 'name' => 'Lithuania'],
                            ['code' => '+372', 'flag' => '🇪🇪', 'country' => 'EE', 'name' => 'Estonia'],
                            ['code' => '+7', 'flag' => '🇷🇺', 'country' => 'RU', 'name' => 'Russia'],
                            ['code' => '+380', 'flag' => '🇺🇦', 'country' => 'UA', 'name' => 'Ukraine'],
                            ['code' => '+375', 'flag' => '🇧🇾', 'country' => 'BY', 'name' => 'Belarus'],
                            ['code' => '+48', 'flag' => '🇵🇱', 'country' => 'PL', 'name' => 'Poland'],
                            ['code' => '+49', 'flag' => '🇩🇪', 'country' => 'DE', 'name' => 'Germany'],
                            ['code' => '+44', 'flag' => '🇬🇧', 'country' => 'GB', 'name' => 'United Kingdom'],
                            ['code' => '+1', 'flag' => '🇺🇸', 'country' => 'US', 'name' => 'USA/Canada'],
                            ['code' => '+46', 'flag' => '🇸🇪', 'country' => 'SE', 'name' => 'Sweden'],
                            ['code' => '+358', 'flag' => '🇫🇮', 'country' => 'FI', 'name' => 'Finland'],
                            ['code' => '+47', 'flag' => '🇳🇴', 'country' => 'NO', 'name' => 'Norway'],
                            ['code' => '+45', 'flag' => '🇩🇰', 'country' => 'DK', 'name' => 'Denmark'],
                            ['code' => '+31', 'flag' => '🇳🇱', 'country' => 'NL', 'name' => 'Netherlands'],
                            ['code' => '+32', 'flag' => '🇧🇪', 'country' => 'BE', 'name' => 'Belgium'],
                            ['code' => '+33', 'flag' => '🇫🇷', 'country' => 'FR', 'name' => 'France'],
                            ['code' => '+352', 'flag' => '🇱🇺', 'country' => 'LU', 'name' => 'Luxembourg'],
                            ['code' => '+353', 'flag' => '🇮🇪', 'country' => 'IE', 'name' => 'Ireland'],
                            ['code' => '+34', 'flag' => '🇪🇸', 'country' => 'ES', 'name' => 'Spain'],
                            ['code' => '+351', 'flag' => '🇵🇹', 'country' => 'PT', 'name' => 'Portugal'],
                            ['code' => '+39', 'flag' => '🇮🇹', 'country' => 'IT', 'name' => 'Italy'],
                            ['code' => '+43', 'flag' => '🇦🇹', 'country' => 'AT', 'name' => 'Austria'],
                            ['code' => '+41', 'flag' => '🇨🇭', 'country' => 'CH', 'name' => 'Switzerland'],
                            ['code' => '+36', 'flag' => '🇭🇺', 'country' => 'HU', 'name' => 'Hungary'],
                            ['code' => '+420', 'flag' => '🇨🇿', 'country' => 'CZ', 'name' => 'Czech Republic'],
                            ['code' => '+421', 'flag' => '🇸🇰', 'country' => 'SK', 'name' => 'Slovakia'],
                            ['code' => '+386', 'flag' => '🇸🇮', 'country' => 'SI', 'name' => 'Slovenia'],
                            ['code' => '+385', 'flag' => '🇭🇷', 'country' => 'HR', 'name' => 'Croatia'],
                            ['code' => '+381', 'flag' => '🇷🇸', 'country' => 'RS', 'name' => 'Serbia'],
                            ['code' => '+30', 'flag' => '🇬🇷', 'country' => 'GR', 'name' => 'Greece'],
                            ['code' => '+359', 'flag' => '🇧🇬', 'country' => 'BG', 'name' => 'Bulgaria'],
                            ['code' => '+40', 'flag' => '🇷🇴', 'country' => 'RO', 'name' => 'Romania'],
                            ['code' => '+373', 'flag' => '🇲🇩', 'country' => 'MD', 'name' => 'Moldova'],
                            ['code' => '+90', 'flag' => '🇹🇷', 'country' => 'TR', 'name' => 'Turkey'],
                            ['code' => '+354', 'flag' => '🇮🇸', 'country' => 'IS', 'name' => 'Iceland'],
                            ['code' => '+355', 'flag' => '🇦🇱', 'country' => 'AL', 'name' => 'Albania'],
                            ['code' => '+356', 'flag' => '🇲🇹', 'country' => 'MT', 'name' => 'Malta'],
                            ['code' => '+357', 'flag' => '🇨🇾', 'country' => 'CY', 'name' => 'Cyprus'],
                            ['code' => '+382', 'flag' => '🇲🇪', 'country' => 'ME', 'name' => 'Montenegro'],
                            ['code' => '+387', 'flag' => '🇧🇦', 'country' => 'BA', 'name' => 'Bosnia & Herzegovina'],
                            ['code' => '+389', 'flag' => '🇲🇰', 'country' => 'MK', 'name' => 'North Macedonia'],
                            ['code' => '+377', 'flag' => '🇲🇨', 'country' => 'MC', 'name' => 'Monaco'],
                            ['code' => '+376', 'flag' => '🇦🇩', 'country' => 'AD', 'name' => 'Andorra'],
                            ['code' => '+61', 'flag' => '🇦🇺', 'country' => 'AU', 'name' => 'Australia'],
                            ['code' => '+64', 'flag' => '🇳🇿', 'country' => 'NZ', 'name' => 'New Zealand'],
                            ['code' => '+81', 'flag' => '🇯🇵', 'country' => 'JP', 'name' => 'Japan'],
                            ['code' => '+82', 'flag' => '🇰🇷', 'country' => 'KR', 'name' => 'South Korea'],
                            ['code' => '+86', 'flag' => '🇨🇳', 'country' => 'CN', 'name' => 'China'],
                            ['code' => '+91', 'flag' => '🇮🇳', 'country' => 'IN', 'name' => 'India'],
                            ['code' => '+92', 'flag' => '🇵🇰', 'country' => 'PK', 'name' => 'Pakistan'],
                            ['code' => '+852', 'flag' => '🇭🇰', 'country' => 'HK', 'name' => 'Hong Kong'],
                            ['code' => '+65', 'flag' => '🇸🇬', 'country' => 'SG', 'name' => 'Singapore'],
                            ['code' => '+60', 'flag' => '🇲🇾', 'country' => 'MY', 'name' => 'Malaysia'],
                            ['code' => '+66', 'flag' => '🇹🇭', 'country' => 'TH', 'name' => 'Thailand'],
                            ['code' => '+62', 'flag' => '🇮🇩', 'country' => 'ID', 'name' => 'Indonesia'],
                            ['code' => '+63', 'flag' => '🇵🇭', 'country' => 'PH', 'name' => 'Philippines'],
                            ['code' => '+84', 'flag' => '🇻🇳', 'country' => 'VN', 'name' => 'Vietnam'],
                            ['code' => '+20', 'flag' => '🇪🇬', 'country' => 'EG', 'name' => 'Egypt'],
                            ['code' => '+27', 'flag' => '🇿🇦', 'country' => 'ZA', 'name' => 'South Africa'],
                            ['code' => '+55', 'flag' => '🇧🇷', 'country' => 'BR', 'name' => 'Brazil'],
                            ['code' => '+52', 'flag' => '🇲🇽', 'country' => 'MX', 'name' => 'Mexico'],
                            ['code' => '+54', 'flag' => '🇦🇷', 'country' => 'AR', 'name' => 'Argentina'],
                            ['code' => '+56', 'flag' => '🇨🇱', 'country' => 'CL', 'name' => 'Chile'],
                            ['code' => '+57', 'flag' => '🇨🇴', 'country' => 'CO', 'name' => 'Colombia'],
                            ['code' => '+971', 'flag' => '🇦🇪', 'country' => 'AE', 'name' => 'United Arab Emirates'],
                            ['code' => '+972', 'flag' => '🇮🇱', 'country' => 'IL', 'name' => 'Israel'],
                            ['code' => '+966', 'flag' => '🇸🇦', 'country' => 'SA', 'name' => 'Saudi Arabia'],
                            ['code' => '+974', 'flag' => '🇶🇦', 'country' => 'QA', 'name' => 'Qatar'],
                            ['code' => '+965', 'flag' => '🇰🇼', 'country' => 'KW', 'name' => 'Kuwait'],
                            ['code' => '+964', 'flag' => '🇮🇶', 'country' => 'IQ', 'name' => 'Iraq'],
                          ];
                          $selectedCode = old('data.phone_country_code', $existingOrder->phone_country_code ?? '+371');
                        @endphp
                        @foreach($countryCodes as $country)
                          <option value="{{ $country['code'] }}" 
                                  data-name="{{ $country['name'] }}"
                                  data-country="{{ $country['country'] }}"
                                  @if($selectedCode == $country['code']) selected @endif>
                            {{ $country['code'] }} ({{ $country['name'] }})
                          </option>
                        @endforeach
                      </select>
                      <input type="text" class="form-control phone-mask @error('data.phone_number') is-invalid @enderror" name="data[phone_number]" id="phone_number" 
                             value="{{ old('data.phone_number', $existingOrder->phone_number) }}"
                             placeholder="XXXXXXXX" required>
                    </div>
                    @error('data.phone_number')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Izvēlieties valsts kodu un ievadiet tālruņa numuru bez koda</small>
                  </div>

		  <div class="reveal-if-active">

                      <div class="form-group">
                        <label for="company_registration_number">Reģistrācijas Nr.<span class="required-field"> *</span></label>
                        <input type="text" class="form-control reg-number-mask" name="data[company_registration_number]" id="company_registration_number" value="{{ old('data.company_registration_number', $existingOrder->company_reg_nr ?? '') }}" placeholder="12345678901" maxlength="11" required>
                        <small class="form-text text-muted">Jāievada 11 cipari</small>
                        <span class="registration_number_error" style="display: none; color: #e74c3c; font-size: 0.875rem;">Jūsu ievadītais reģistrācijas numurs nav pareizs</span>
                      </div>

                      <div class="form-group">
                        <label for="company_pvn_number">PVN numurs</label>
                        <input type="text" class="form-control pvn-mask" name="data[company_pvn_number]" id="company_pvn_number" value="{{ old('data.company_pvn_number', $existingOrder->company_pvn_nr ?? '') }}" placeholder="LV12345678901" maxlength="13">
                        <small class="form-text text-muted">Formāts: LV + 11 cipari (piemērs: LV12345678901)</small>
                        <span class="pvn_number_error" style="display: none; color: #e74c3c; font-size: 0.875rem;">PVN numurs jāievada formātā LV + 11 cipari</span>
                      </div>

                      <div class="form-group">
                        <label for="company_name">Uzņēmuma nosaukums<span class="required-field"> *</span></label>
                        <input type="text" class="form-control" name="data[company_name]" id="company_name" value="{{ old('data.company_name', $existingOrder->company_name ?? '') }}" placeholder="Uzņēmuma nosaukums" required>
                      </div>

                      <div class="form-group">
                        <label for="company_address">Juridiskā adrese<span class="required-field"> *</span></label>
                        <input type="text" class="form-control" name="data[company_address]" id="company_address" value="{{ old('data.company_address', $existingOrder->company_address ?? '') }}" placeholder="Juridiskā adrese" required>
                      </div>

                    </div>

                  <div class="form-group">
                    <label for="notes">Piezīmes</label>
                    <input type="text" class="form-control" name="data[notes]" id="notes" value="{{ old('data.notes', $existingOrder->comments ?? '') }}" placeholder="Piezīmes">
                  </div>

                  <h4>Informācija par transportlīdzekli</h4>

                  <div class="form-group">
                    <label for="car_reg_nr">Reģistrācijas numurs</label>
                    <input type="text" class="form-control" name="data[car_plate]" id="car_reg_nr"
                      value="{{ old('data.car_plate', ($existingOrder && !empty($existingOrder->car_details) && \App\Helper\Utility::decode_info($existingOrder->car_details)->car_plate) ? \App\Helper\Utility::decode_info($existingOrder->car_details)->car_plate : '' ) }}"
                      placeholder="AA1234">
                    <small class="form-text text-muted">Ievadiet numuru bez atstarpēm (piemēram: AA1234)</small>
                  </div>

                  <div class="form-group">
                    <label for="brand">Marka</label>
                    <input type="text" class="form-control" name="data[car_brand]" id="brand" 
                      value="{{ old('data.car_brand', ($existingOrder && !empty($existingOrder->car_details) && \App\Helper\Utility::decode_info($existingOrder->car_details)->car_brand) ? \App\Helper\Utility::decode_info($existingOrder->car_details)->car_brand : '' ) }}" 
                      placeholder="Ievadiet marku">
                    <small class="form-text text-muted">Piemēram: Audi, BMW, Mercedes, Toyota</small>
                  </div>

                  <div class="form-group">
                    <label for="model">Modelis</label>
                    <input type="text" class="form-control" name="data[car_model]" id="model" 
                      value="{{ old('data.car_model', ($existingOrder && !empty($existingOrder->car_details) && \App\Helper\Utility::decode_info($existingOrder->car_details)->car_model) ? \App\Helper\Utility::decode_info($existingOrder->car_details)->car_model : '' ) }}" 
                      placeholder="Ievadiet modeli">
                    <small class="form-text text-muted">Piemēram: A4, X5, E-Class, Corolla</small>
                  </div>

                  <div class="form-group">
                    <label for="car_release-year">Izlaiduma gads</label>
                    <input type="text" class="form-control" name="data[car_release_year]" id="car_release-year" 
                      value="{{ old('data.car_release_year', ($existingOrder && !empty($existingOrder->car_details) && \App\Helper\Utility::decode_info($existingOrder->car_details)->car_release_year) ? \App\Helper\Utility::decode_info($existingOrder->car_details)->car_release_year : '' ) }}" 
                      placeholder="Ievadiet izlaiduma gadu">
                    <small class="form-text text-muted">Piemēram: 2018, 2020, 2023</small>
                  </div>

                  <div class="form-group">
                    <label for="car_engine_size">Dzineja tilpums</label>
                    <input type="text" class="form-control" name="data[car_engine_size]" id="car_engine_size" 
                      value="{{ old('data.car_engine_size', ($existingOrder && !empty($existingOrder->car_details) && \App\Helper\Utility::decode_info($existingOrder->car_details)->car_engine_size) ? \App\Helper\Utility::decode_info($existingOrder->car_details)->car_engine_size : '' ) }}" 
                      placeholder="Ievadiet dzinēja tilpumu">
                    <small class="form-text text-muted">Piemēram: 1.6, 2.0, 3.0, Elektriskais, Hibrīds</small>
                  </div>

                <div class="cart-checkboxes-card">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="@if ($existingOrder && $existingOrder->email_notification){{$existingOrder->email_notification}}@endif" id="email_notifications" name="data[email_notifications]">
                    <label class="form-check-label" for="email_notifications">
                      Atļaut man sūtīt paziņojumus par akcijām un jaunumiem uz norādīto e-pastu
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="defaultCheck2" required>
                    <label class="form-check-label" for="defaultCheck2">
                      <span class="required-field">*</span>Piekrītu SIA R1 <a href="https://www.r1-dev.area.lv/privatuma-politika" target="_blank">privātuma politikai</a>
                    </label>
                  </div>
                </div>

                  <a href="{{ route('cart') }}" class="btn btn-secondary">Atgriezties</a>
                  <button type="submit" class="btn btn-primary btn-checkout float-right" name="submit">Tālāk</button>
                </div>

              </form>
            </div>
          </section>
        </div>
      </div>
      @include('components.right-sidebar')
    </div>
  </div>

<!-- Подключаем CSS Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Подключаем необходимые скрипты напрямую -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Убедимся, что jQuery доступен
if (typeof jQuery !== 'undefined') {
    // Инициализируем после полной загрузки DOM
    jQuery(document).ready(function($) {
        // Инициализируем Select2 для выпадающего списка стран
        $('#phone_country_code').select2({
            templateResult: formatCountry,
            templateSelection: formatCountrySelection,
            escapeMarkup: function(m) { return m; },
            dropdownParent: $('body'),
            width: '135px',
            minimumResultsForSearch: 8,
            language: {
                searching: function() {
                    return "Meklēšana...";
                },
                noResults: function() {
                    return "Nav atrasts";
                }
            }
        });
        
        function formatCountry(country) {
            if (!country.id) return country.text;
            var $flag = $(country.element).data('flag');
            var $name = $(country.element).data('name');
            var $country = $(country.element).data('country');
            return $('<span class="country-option">' +
                    '<span class="country-details">' +
                        '<span class="country-code">' + $country + ' ' + country.id + '</span>' +
                        '<span class="country-name">' + $name + '</span>' +
                    '</span>' +
                    '</span>');
        }
        
        function formatCountrySelection(country) {
            if (!country.id) return country.text;
            var $flag = $(country.element).data('flag');
            var $country = $(country.element).data('country');
            return $('<span class="country-selected">' +
                    '<span class="country-code">' + $country + ' ' + country.id + '</span>' +
                    '</span>');
        }
        
        
        @if($errors->has('data.phone_number'))
        // Применяем стили ошибки к полю кода страны и номеру телефона
        $('.phone-input-group').addClass('has-error');
        $('#phone_number').addClass('is-invalid');
        $('.select2-selection').addClass('is-invalid');
        @endif
        
        // Остальные функции формы
        var typePerson = $('input[name=person_type]:checked').val();
        if (typePerson == 'legal') {
            $('.legal-entity-fields').show();
        } else {
            $('.legal-entity-fields').hide();
        }

        $('input[name=person_type]').change(function(){
            var typePerson = $('input[name=person_type]:checked').val();
            if (typePerson == 'legal') {
                $('.legal-entity-fields').show();
            } else {
                $('.legal-entity-fields').hide();
            }
        });

	// Инициализация масок для полей регистрации и PVN
        $('.reg-number-mask').mask('00000000000', {
            placeholder: '12345678901',
            clearIfNotMatch: true
        });

        $('.pvn-mask').mask('LV00000000000', {
            placeholder: 'LV12345678901',
            clearIfNotMatch: true
        });

        $('#email').blur(function(){
            var emailInput = $(this);
            var email = emailInput.val().trim();
            
            if (email === '') {
                emailInput.removeClass('is-invalid is-valid');
                return;
            }
            
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                emailInput.addClass('is-invalid').removeClass('is-valid');
            } else {
                emailInput.addClass('is-valid').removeClass('is-invalid');
            }
        });

	$('#company_registration_number').on('blur', function() {
            var value = $(this).val().replace(/\D/g, ''); // Убираем все нецифровые символы
            var $input = $(this);
            var $error = $('.registration_number_error');
            
            if ($input.closest('.reveal-if-active').is(':visible')) {
                if (value.length === 0) {
                    $input.removeClass('is-invalid is-valid');
                    $error.hide();
                } else if (value.length === 11) {
                    $input.val(value).removeClass('is-invalid').addClass('is-valid');
                    $error.hide();
                } else {
                    $input.removeClass('is-valid').addClass('is-invalid');
                    $error.show();
                }
            }
        });

        // Валидация PVN номера при потере фокуса
        $('#company_pvn_number').on('blur', function() {
            var value = $(this).val().trim().toUpperCase();
            var $input = $(this);
            var $error = $('.pvn_number_error');
            
            if ($input.closest('.reveal-if-active').is(':visible') && value.length > 0) {
                // Проверяем формат: LV + 11 цифр
                var pvnRegex = /^LV\d{11}$/;
                if (pvnRegex.test(value)) {
                    $input.val(value).removeClass('is-invalid').addClass('is-valid');
                    $error.hide();
                } else {
                    $input.removeClass('is-valid').addClass('is-invalid');
                    $error.show();
                }
            } else if (value.length === 0) {
                $input.removeClass('is-invalid is-valid');
                $error.hide();
            }
        });

        // Валидация при вводе для номера регистрации (только цифры)
        $('#company_registration_number').on('input', function() {
            var value = $(this).val().replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            $(this).val(value);
        });

        // Валидация при вводе для PVN номера
        $('#company_pvn_number').on('input', function() {
            var value = $(this).val().toUpperCase();
            // Если начинается не с LV, добавляем LV
            if (value.length > 0 && !value.startsWith('LV')) {
                // Убираем все нецифровые символы и добавляем LV
                var digits = value.replace(/\D/g, '');
                if (digits.length > 0) {
                    value = 'LV' + digits;
                } else {
                    value = 'LV';
                }
            }
            // Ограничиваем до LV + 11 цифр
            if (value.startsWith('LV')) {
                var digits = value.substring(2).replace(/\D/g, '');
                if (digits.length > 11) {
                    digits = digits.substring(0, 11);
                }
                value = 'LV' + digits;
            }
            $(this).val(value);
        });

        // Валидация формы перед отправкой
        $('.cart-form').on('submit', function(e) {
            var isValid = true;
            
            // Проверяем номер регистрации, если видно поле
            if ($('#company_registration_number').closest('.reveal-if-active').is(':visible')) {
                var regNumber = $('#company_registration_number').val().replace(/\D/g, '');
                if (regNumber.length !== 11) {
                    $('#company_registration_number').addClass('is-invalid');
                    $('.registration_number_error').show();
                    isValid = false;
                }
            }
            
            // Проверяем PVN номер, если он заполнен
            var pvnNumber = $('#company_pvn_number').val().trim().toUpperCase();
            if ($('#company_pvn_number').closest('.reveal-if-active').is(':visible') && pvnNumber.length > 0) {
                var pvnRegex = /^LV\d{11}$/;
                if (!pvnRegex.test(pvnNumber)) {
                    $('#company_pvn_number').addClass('is-invalid');
                    $('.pvn_number_error').show();
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                // Прокручиваем к первой ошибке
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
                return false;
            }
        });
    });
} else {
    console.error('jQuery is not defined');
}
</script>

<style>
/* Стилизация формы */
.form-group {
    margin-bottom: 1.5rem;
}

label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: inline-block;
}

.required-field {
    color: #e74c3c;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: border-color 0.2s ease-in-out;
}

.form-control:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

.form-control.is-invalid {
    border-color: #e74c3c;
}

.invalid-feedback {
    color: #e74c3c;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block !important;
}

.d-block {
    display: block !important;
}

.is-invalid {
    border-color: #e74c3c !important;
    background-color: #fff8f8 !important;
}

.is-valid {
    border-color: #2ecc71 !important;
    background-color: #f8fff8 !important;
}

.cart-card {
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.cart-card h4 {
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #eee;
    color: #333;
}

.btn-primary {
    background-color: #4CAF50;
    border-color: #4CAF50;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.2s ease-in-out;
}

.btn-primary:hover {
    background-color: #388E3C;
    border-color: #388E3C;
}

.btn-secondary {
    background-color: #f8f9fa;
    border-color: #ddd;
    color: #333;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.2s ease-in-out;
}

.btn-secondary:hover {
    background-color: #e9ecef;
    border-color: #ccc;
}

.person-status-container {
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: center;
}

.person-status-item {
    padding: 1rem 2rem;
    font-weight: 600;
    text-transform: uppercase;
}

.person-status-item.active {
    background-color: #4CAF50;
    border-color: #4CAF50;
    color: white;
}

/* Стили для телефонного поля */
.phone-input-group {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    flex-wrap: nowrap;
    width: 100%;
    position: relative;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.phone-input-group input {
    border-radius: 0 4px 4px 0;
    flex: 1;
    height: 42px;
    border-left: none;
    padding-left: 12px;
}

.phone-input-group input:focus {
    box-shadow: none;
    border-color: #4CAF50;
    z-index: 1;
}

.select2-container {
    min-width: 135px !important;
    max-width: 135px !important;
    width: 135px !important;
    flex-shrink: 0;
    margin-right: 0;
}

.select2-container--default .select2-selection--single {
    border-radius: 4px 0 0 4px !important;
    border-right: none;
}

.select2-container--default.select2-container--focus .select2-selection--single {
    border-color: #4CAF50;
}

.select2-container--open .select2-selection--single {
    border-color: #4CAF50 !important;
}

/* Стили для отображения ошибки в телефонном поле */
.phone-input-group.has-error .select2-selection,
.phone-input-group.has-error input {
    border-color: #e74c3c !important;
    background-color: #fff8f8 !important;
}

.phone-input-group.has-error {
    box-shadow: 0 0 0 1px rgba(231, 76, 60, 0.25);
}

/* Стили для Select2 */
.select2-container--default .select2-selection--single {
    height: 42px !important;
    display: flex !important;
    align-items: center !important;
    background-color: #f8f9fa;
    border: 1px solid #ddd;
    transition: border-color 0.2s ease-in-out;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    padding: 0 8px !important;
}

.select2-container--default .select2-selection--single:hover {
    border-color: #4CAF50;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    display: flex !important;
    align-items: center !important;
    height: 100% !important;
    line-height: normal !important;
    padding-left: 0 !important;
    font-weight: 500;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px !important;
}

.country-option {
    display: flex;
    align-items: center;
    padding: 5px 0;
}

.country-option .flag {
    margin-right: 8px;
    font-size: 1.4em;
}

.country-option .country-details {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}

.country-option .country-code {
    font-weight: 500;
}

.country-option .country-name {
    color: #555;
    font-size: 0.85em;
}

.country-selected {
    display: flex;
    align-items: center;
}

.country-selected .flag {
    margin-right: 6px;
    font-size: 1.2em;
}

.country-selected .country-code {
    font-weight: 500;
}

.select2-container {
    min-width: 180px !important;
    width: auto !important;
    flex-shrink: 0;
    margin-right: -1px;
}

.phone-input-group input {
    border-radius: 0 4px 4px 0;
    flex: 1;
    height: 42px;
}

.select2-container--default .select2-selection--single {
    border-radius: 4px 0 0 4px !important;
}

/* Стили для отображения ошибки в телефонном поле */
.phone-input-group.has-error .select2-selection,
.phone-input-group.has-error input {
    border-color: #e74c3c !important;
    background-color: #fff8f8 !important;
}

/* Стили для dropdown */
.select2-dropdown {
    border-color: #ddd !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    border-radius: 4px !important;
    padding: 5px 0;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #4CAF50 !important;
    color: white !important;
}

.select2-container--default .select2-results__option {
    padding: 6px 12px;
    transition: all 0.2s;
}

.select2-results__option {
    border-radius: 2px;
    margin: 0 5px;
}

.select2-search--dropdown {
    padding: 8px 10px;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 10px;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.07);
}

/* Улучшения для мобильных устройств */
@media (max-width: 768px) {
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-control, .select2-container--default .select2-selection--single {
        padding: 0.625rem;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .person-status-item {
        padding: 0.75rem 1rem;
    }
    
    .select2-container {
        min-width: 120px !important;
    }
}
</style>
@endsection
