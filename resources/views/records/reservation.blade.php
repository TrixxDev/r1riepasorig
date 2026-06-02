@extends('layouts.app')

@section('content')
  <div class="container-fluid records">
    <div class="">
      <div class="main-content clearfix col-md-12 col-xl-12">
        <div class="loading" style="display: none"></div>
        <div id="content-wrapper" class="right-column col-lg-12">
          <div class="schedule-table reservations_page">
            @include('components.calendar')
            @php
              $todayDate = date('Y-m-d');

              $yesterday = date('Y-m-d', strtotime("-1 days",$currentDate));
              $tomorrow = date('Y-m-d', strtotime("+1 days",$currentDate));
              $buildTakenByMeta = function ($takenByRaw) {
                $takenBy = json_decode($takenByRaw, true);
                if (!is_array($takenBy)) {
                  return [null, 'xxxxx'];
                }

                $carBrand = trim((string)($takenBy['car_brand'] ?? ''));
                $carModel = trim((string)($takenBy['car_model'] ?? ''));
                $phoneNumber = trim((string)($takenBy['phone_number'] ?? ''));
                $label = trim(preg_replace('/\s+/', ' ', $carBrand . ' ' . $carModel . ' ' . $phoneNumber));

                return [$takenBy, $label !== '' ? $label : 'xxxxx'];
              };
            @endphp
            <div class="col-12 day-select">
              <div class="row">
                <a href="{{ route('rezervacijas') }}">
                  <img class="icons" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsTAAALEwEAmpwYAAAA2UlEQVR4nO2TMQrCQBBFn0JsBMtYW3iA4Am00ivoIQQbW6/gHTyDlV5AvIBFai0VbSx0ZWECQ8BsNkRBzIdpZt+fD7O78G+aSZWuGrAAjNQSqJc1vAGsZPBdykjPnqGCvdUE1mK+AkOgD5yltwVaRQPawF6MRyBSZ5H0jGK8AjrAQUwx0HUwXgE94CSGHRBmsKEwSYD1ZmoAXATeyH597ukGjN6BY/VCbAU5hicKlM/OmKSBKfAosk8l7X0CcxfoK5PXlwaNo34nIK0q4PMrMo5/kZf7fkAltF7Bq5Eyw69PvAAAAABJRU5ErkJggg==">
                </a>
                <a href="{{ route('rezervacijas.date', $yesterday) }}">
                  <img class="icons" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAYUlEQVR4nO3UMQqAMBBE0X+JBL3/SdRCFG20yHEUJYKVVRgF50GKrT6EZcHMrLAIzECjjq7ABgyqaACWHE1A5WhpU/5exeveCrdPS1UjFH4bj7cDMirDV/xYuP6czMy+ZAcJB1CbbvpUogAAAABJRU5ErkJggg==">
                </a>
                <a href="{{ route('rezervacijas.date', $tomorrow) }}">
                  <img class="icons" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAVklEQVR4nO3UwQnAIAxG4beEhe6/ST0UWr3Ug+MogjMkRf8Pcn4QQkBE5IcuIAGHdfgBGlCs4wH4ZrwCp+JDnCuxmIxTOLHykYVtosPr9UBur5cpIovrWVNQoo8QJhgAAAAASUVORK5CYII=">
                </a>
              </div>
            </div>
            @for ($day = 0; $day <= $visibleDays; $day++)
              @php
                $strtotime = $currentDate;
                if ($visibleDays !== 0) $strtotime = strtotime('+' . $day . ' days');
                $date = Date('d.m.Y', $strtotime);
                $dayOfWeek = $dayTitles[date('N', strtotime($date.' 00:00:00'))];
              @endphp
              <h1 style="font-size: 1.7em; margin-top: 20px;">{{ $dayOfWeek }}, {{ $date }}</h1>
              <div class="row no-gutters reservations-office-row">
                @foreach ($offices as $office)
                  <div class="col-md-{{ round(12 / $queueSum * max(1, (int)($queuesCountByOffice[$office->office_id] ?? 0))) }} grid grid-cols-{{ max(1, (int)($queuesCountByOffice[$office->office_id] ?? 0)) }}" style="@if ($office->office_id == 1){{'border-right: 2px solid black;'}}@endif" data-date="{{ date('Y-m-d', strtotime($date.' 00:00:00')) }}">
                    @foreach ($workingDays as $workingDay)
                      @if ($workingDay->date == Date('Y-m-d', $strtotime))
                        @php
                          $_queueRow = $queuesById[$workingDay->queue_id] ?? null;
                        @endphp
                        @if (!$_queueRow)
                          @continue
                        @endif
                        @php
                          $openTime1Str = $minTimeopenByDate[$workingDay->date] ?? $workingDay->timeopen;
                          $timeStep = $workingDay->timeStep;
                          $opentime = \Carbon\Carbon::parse($workingDay->timeopen);
                          $openTime1 = \Carbon\Carbon::parse($openTime1Str);
                          $closetime = \Carbon\Carbon::parse($workingDay->timeclose)->subMinutes($timeStep);

                          $halfAcService = $workingDay->ac_toggle;
                          $halfMotoService = $workingDay->moto_toggle;

                          $numberOfSteps = ceil($opentime->diffInMinutes($closetime) / $timeStep);

                          $startSlot = $opentime->diffInMinutes($closetime) / $timeStep - $openTime1->diffInMinutes($closetime) / $timeStep;

                          $workingOffice = $officesById[$workingDay->office_id] ?? null;
                        @endphp
                        @if ($workingOffice && $workingOffice->office_id == $office->office_id)
                          @if ($workingDay->weekday != 7)
                            @if ($workingDay->is_opened == 1)
                              <div class="table office_{{ $workingOffice->office_id }}" @if ($workingDay->is_half) data-half="1" @endif data-queue-id="{{ $workingDay->queue_id }}">
                                <div class="title text-sm">{{ $workingOffice->title }}</div>
                                @for ($i = $startSlot; $i <= $numberOfSteps; $i++)
                                  @php
                                    $slot = $slotsByKey[\App\Models\Slot::reservationGridKey($workingDay->queue_id, $workingDay->date, $i)] ?? null;
                                    $currentTime = $opentime->copy()->addMinutes($timeStep * $i)->format('H:i');

                                  @endphp

                                  @if (!is_null($slot))
                                    @switch ($slot->status)
                                      @case(0)
                                      @php
                                        $slotClass = 'time-free';
                                        $content = '<button class="status status slot-free"></button>';
                                        if ($slot->comment !== null) {
                                            $slotClass = 'time-discount';
                                            $content = '<button class="slot status slot-free discount">' . $slot->comment . '</button>';
                                        }
                                      //$content = '<button class="bg-green-400 text-sm hover:bg-green-600 text-white py-2 px-4 status">Brīvs</button>';
                                      @endphp
                                      @break
                                      @case(1)
                                      @php
                                        $slotClass = 'time-taken';
                                        $serviceSlotClass = '';
                                        if ($slot->takenby !== null) {
                                            [$takenBy, $takenByLabel] = $buildTakenByMeta($slot->takenby);
                                            $service = null;
                                            if (is_array($takenBy) && isset($takenBy['service'])) {
                                              $service = $servicesById[$takenBy['service']] ?? null;
                                            }
                                            $ac = (isset($halfAcService) && !is_null($halfAcService)) ? '*' : '';
                                            if ($service && $service->service_id == 6) {
                                                $serviceSlotClass = 'time-taken-ac';
                                            } elseif ($service && ($service->service_id == 8 || $service->service_id == 9)) {
                                                $serviceSlotClass = 'time-taken-moto';
                                            }

                                            $content = '<button class="slot status taken-slot ' . $serviceSlotClass . '">'. $takenByLabel . '</button>';
                                            //$content = '<span class="bg-gray-300 text-sm text-gray py-2 px-4 status" style="cursor: default;">'. \App\Http\Controllers\MainController::truncateCharacters(trim($takenBy->car_brand),6,'&mldr;',1) . ' xxxxx'.$plate.'</span>';
                                        } else {
                                            $content = '<button class="slot status taken-slot ' . $serviceSlotClass . '">xxxxx</button>';
                                        }

                                      @endphp
                                      @break
                                      @case(2)
                                      @php
                                        if ($slot->takenby !== null) {
                                          $slotClass = 'time-taken';
                                          $serviceSlotClass = '';
                                          [$takenBy, $takenByLabel] = $buildTakenByMeta($slot->takenby);
                                          $service = null;
                                          if (is_array($takenBy) && isset($takenBy['service'])) {
                                            $service = $servicesById[$takenBy['service']] ?? null;
                                          }
                                          $ac = (isset($halfAcService) && !is_null($halfAcService)) ? '*' : '';
                                          if ($service && $service->service_id == 6) {
                                              $serviceSlotClass = 'time-taken-ac';
                                          } elseif ($service && ($service->service_id == 8 || $service->service_id == 9)) {
                                              $serviceSlotClass = 'time-taken-moto';
                                          }

                                          $content = '<button class="slot status taken-slot ' . $serviceSlotClass . '">'. $takenByLabel . '</button>';
                                          //$content = '<span class="bg-gray-300 text-sm text-gray py-2 px-4 status" style="cursor: default;"></span>';
                                        } else {
                                          $slotClass = 'time-offer';
                                          $content = '<button class="slot status slot-offer">' . $slot->comment . '</button>';
                                          //$content = '<button class="bg-orange-500 hover:bg-orange-200 text-black py-2 px-4 status">' . $slot->comment . '</button>';
                                        }
                                      @endphp
                                      @break
                                      @case(3)
                                      @php
                                        $slotClass = 'time-closed';
                                        $content = '<button class="slot status closed-slot">Slēgts</button>';
                                        //$content = '<span class="bg-red-500 text-white py-2 px-4 status" style="cursor: default;">Slēgts</span>';
                                      @endphp
                                      @break
                                    @endswitch
                                  @else
                                    @php
                                      $slotClass = 'time-free';
                                      $content = '<button class="slot status slot-free"></button>';
                                      //$content = '<button class="bg-green-400 text-sm hover:bg-green-600 text-white py-2 px-4 status">Brīvs</button>';
                                    @endphp
                                  @endif

                                  @if ($workingDay->is_half)
                                    @if (!is_null($slot))
                                      @switch ($slot->status)
                                        @case(0)
                                        @php
                                          $slotClass = 'time-free';
                                          $content = '<button class="status status slot-free"></button>';
                                          if ($slot->comment !== null) {
                                            $slotClass = 'time-discount';
                                            $content = '<button class="slot status slot-free discount">' . $slot->comment . '</button>';
                                          }
                                          //$content = '<button class="bg-green-400 text-sm hover:bg-green-600 text-white py-2 px-4 status">Brīvs</button>';
                                        @endphp
                                        @break
                                        @case(1)
                                        @php
                                          $slotClass = 'time-taken';
                                          $serviceSlotClass = '';
                                          $className = ($i % 2 == 1) ? 'text-red' : '';
                                          if ($slot->takenby !== null) {
                                              [$takenBy, $takenByLabel] = $buildTakenByMeta($slot->takenby);
                                              $service = null;
                                              if (is_array($takenBy) && isset($takenBy['service'])) {
                                                $service = $servicesById[$takenBy['service']] ?? null;
                                              }
                                              $ac = (isset($halfAcService) && !is_null($halfAcService)) ? '*' : '';
                                              if ($service && $service->service_id == 6) {
                                                  $serviceSlotClass = 'time-taken-ac';
                                              } elseif ($service && ($service->service_id == 8 || $service->service_id == 9)) {
                                                  $serviceSlotClass = 'time-taken-moto';
                                              }

                                              $content = '<button class="slot status ' . $className .  ' taken-slot ' . $serviceSlotClass . '">'. $takenByLabel . '</button>';
                                              //$content = '<span class="bg-gray-300 text-sm text-gray py-2 px-4 status" style="cursor: default;">'. \App\Http\Controllers\MainController::truncateCharacters(trim($takenBy->car_brand),6,'&mldr;',1) . ' xxxxx'.$plate.'</span>';
                                          } else {
                                              $content = '<button class="slot status ' . $className . ' taken-slot ' . $serviceSlotClass . '">xxxxx</button>';
                                          }

                                        @endphp
                                        @break
                                        @case(2)
                                        @php
                                          if ($slot->takenby !== null) {
                                            $slotClass = 'time-taken';
                                            $serviceSlotClass = '';
                                            [$takenBy, $takenByLabel] = $buildTakenByMeta($slot->takenby);
                                            $service = null;
                                            if (is_array($takenBy) && isset($takenBy['service'])) {
                                              $service = $servicesById[$takenBy['service']] ?? null;
                                            }
                                            $ac = (isset($halfAcService) && !is_null($halfAcService)) ? '*' : '';
                                            if ($service && $service->service_id == 6) {
                                                $serviceSlotClass = 'time-taken-ac';
                                            } elseif ($service && ($service->service_id == 8 || $service->service_id == 9)) {
                                                $serviceSlotClass = 'time-taken-moto';
                                            }
                                            $className = ($i % 2 == 1) ? 'text-red' : '';
                                            $content = '<button class="slot status ' . $className . ' taken-slot ' . $serviceSlotClass . '">'. $takenByLabel . '</button>';
                                            //$content = '<span class="bg-gray-300 text-sm text-gray py-2 px-4 status" style="cursor: default;"></span>';
                                          } else {
                                            $slotClass = 'time-offer';
                                            $content = '<button class="slot status slot-offer">' . $slot->comment . '</button>';
                                            //$content = '<button class="bg-orange-500 hover:bg-orange-200 text-black py-2 px-4 status">' . $slot->comment . '</button>';
                                          }
                                        @endphp
                                        @break
                                        @case(3)
                                        @php
                                          $slotClass = 'time-closed';
                                          $content = '<button class="slot status closed-slot">Slēgts</button>';
                                          //$content = '<span class="bg-red-500 text-white py-2 px-4 status" style="cursor: default;">Slēgts</span>';
                                        @endphp
                                        @break
                                      @endswitch
                                    @else
                                      @php
                                        $slotClass = 'time-free';
                                        $content = '<button class="slot status slot-free"></button>';
                                        //$content = '<button class="bg-green-400 text-sm hover:bg-green-600 text-white py-2 px-4 status">Brīvs</button>';
                                      @endphp
                                    @endif
                                    @if ($i % 2 == 1)
                                      @if ($slot)
                                        <div class="time-status flex time-taken" data-iorder="{{ $i }}">
                                          <div class="time-slot">{{ $currentTime }}</div>{!! $content !!}
                                        </div>
                                      @else
                                        @if ($i >= 0)
                                          <div class="time-status flex {{ $slotClass }}" data-iorder="{{ $i }}">
                                            <div class="time-slot">{{ $currentTime }}</div><button class="slot status slot-free" style="background: #bfbfbf"></button>
                                          </div>
                                        @else
                                          <div class="time-status flex time-free" data-iorder="{{ $i }}">
                                            <div class="time-slot"></div><div style="cursor: default" class="slot status"></div>
                                          </div>
                                        @endif
                                      @endif
                                    @else
                                      @if ($slot)
                                        <div class="time-status flex time-taken" data-iorder="{{ $i }}">
                                          <div class="time-slot">{{ $currentTime }}</div>{!! $content !!}
                                        </div>
                                      @else
                                        @if ($i >= 0)
                                          <div class="time-status flex {{ $slotClass }}" data-iorder="{{ $i }}">
                                            <div class="time-slot">{{ $currentTime }}</div><button class="slot status slot-free"></button>
                                          </div>
                                        @else
                                          <div class="time-status flex time-free" data-iorder="{{ $i }}">
                                            <div class="time-slot"></div><div style="cursor: default" class="slot status"></div>
                                          </div>
                                        @endif
                                      @endif
                                    @endif
                                  @else
                                    @if ($i >= 0)
                                      <div class="time-status flex {{ $slotClass }}" data-iorder="{{ $i }}">
                                        <div class="time-slot">{{ $currentTime }}</div>{!! $content !!}
                                      </div>
                                    @else
                                      <div class="time-status flex time-disabled" data-iorder="{{ $i }}">
                                        <div class="time-slot"></div><div style="cursor: default" class="slot status"></div>
                                      </div>
                                    @endif
                                  @endif
                                @endfor
                              </div>
                            @else
                              <div class="table office_{{ $workingOffice->office_id }}" data-queue-id="{{ $workingDay->queue_id }}">
                                <div class="title text-sm">{{ $workingOffice->title }}</div>
                              </div>
                            @endif
                          @else
                            <div class="table" style="border-right: none;">
                              <div class="slot closed-slot">Slēgts</div>
                            </div>
                          @endif
                        @endif
                      @endif
                    @endforeach
                  </div>
                @endforeach
              </div>
            @endfor
          </div>


        {{--    <div class="grid grid-cols-5">--}}

        {{--    </div>--}}

        <!-- Main modal -->
          @php
            $carInfoToken = app(\App\Services\CarInfoTokenService::class)->issue(request());
          @endphp
          <div class="reservation_edit modal fade" id="slotModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="slotModalLabel" aria-hidden="true" data-car-info-url="{{ url('/api/car-info') }}">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="loader-block">
                                <span class="loading">
                                    <span class="fa fa-spinner fa-spin fa-3x"></span>
            {{--                        <span class="loading-bar">--}}
                                  {{--                            <div class="loading-progress-bar"></div>--}}
                                  {{--                        </span>--}}
                                </span>
                </div>
                <div class="modal-header">
                  <h5 class="modal-title" id="slotModalLabel">Labot notikumu</h5>
                </div>
                <div class="modal-body" style="padding-top: 0px;">
                  <form method="post">
                    <input type="hidden" name="car_info_token" value="{{ $carInfoToken }}">
                    <input type="hidden" name="queue_id">
                    <input type="hidden" name="date">
                    <input type="hidden" name="slot">
                    <input type="hidden" name="part">
                    <input type="hidden" name="cancelId">
                    <div class="form-group row time bg-light">
                      <label for="f_date" class="col-sm-3 col-form-label text-right">Datums un laiks:</label>
                      {{--                        <div class="col-3"><input type="text" class="form-control ui-datepicker" id="f_date"></div>--}}
                      <div class="col-3">
                        <select class="form-control" id="f_date">
                          @foreach ($dateRanges as $dateRange)
                            <option value="{{ $dateRange->format('Y-m-d') }}">{{ $dateRange->format('Y-m-d') }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-3">
                        {{--                          <input type="text" class="form-control ui-datepicker" id="f_time">--}}
                        <select class="form-control" id="f_time">
                          @foreach ($timeRanges as $timeRange)
                            <option value="{{ $timeRange->format('H:i') }}">{{ $timeRange->format('H:i') }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="title" class="col-sm-3 col-form-label text-right">Filiāle/rinda:</label>
                      <div class="col-8">
                        <select class="form-control" id="f_office">
                          @foreach ($reservationModalQueues as $pubQueue)
                            @php $pubOffice = $officesById[$pubQueue->office_id] ?? null; @endphp
                            @if ($pubOffice)
                              <option value="{{ $pubQueue->queue_id }}">{{ $pubOffice->title }} | {{ $pubQueue->title }}</option>
                            @endif
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="separator"></div>
                    <div class="form-group row time bg-light">
                      <label for="f_car" class="col-sm-3 col-form-label text-right">Auto marka un modelis:</label>
                      <div class="col-3"><input type="text" class="form-control check-input" id="f_car"></div>
                      <span class="timeSeparator">-</span>
                      <div class="col-3"><input type="text" class="form-control check-input" id="f_model"></div>
                    </div>
                    <div class="form-group row time">
                      <label for="f_plate" class="col-sm-3 col-form-label text-right">Reģistrācijas numurs:</label>
                      <div class="col-3"><input type="text" class="form-control ui-datepicker check-input" id="f_plate"></div>
                    </div>
                    <div class="form-group services row bg-light" style="margin-bottom: 0px;">
                      <div class="form-group col-md-3 text-right" style="margin-bottom: 0px;">
                        <label for="service"><span class="validate" style="color: red;">*</span>Es vēlos:</label>
                      </div>
                      <div class="col-md-8" id="service">
                        <label>
                          <select class="custom-select select-service-option">
                            @foreach ($reservationModalServices as $service)
                              @if (is_object($service) && isset($service->service_id))
                                <option name="serviceOption" id="serviceOption{{ $service->service_id }}" class="form-check-input" @if ($service->f_save == 1) data-save="1"@endif @if ($service->f_save == 2) data-save="2"@endif value="{{ $service->service_id }}">{{ $service->title }}</option>
                              @endif
                            @endforeach
                          </select>
                        </label>
                      </div>
                    </div>
                    <div class="rims-with-select-row row bg-light">
                      <div class="form-group col-md-3 text-right">
                        <label for="service"><span class="validate" style="color: red;">*</span>Izvēle:</label>
                      </div>
                      <div class="col-md-8" id="service">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="1">
                          <label class="form-check-label" for="flexRadioDefault1">
                            Riepas bez Diskiem
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="2">
                          <label class="form-check-label" for="flexRadioDefault2">
                            Riepas ar Diskiem
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row temp_save_nr bg-light" style="display: none;">
                      <div class="col-md-3 text-right">
                        <label for="save_nr">Glabāšanas talona numurs:</label>
                      </div>
                      <div class="col-sm-9">
                        <input type="text" class="form-control check-input" id="save_nr">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="title" class="col-sm-3 col-form-label text-right">Piezīmes:</label>
                      <div class="col-9">
                        <textarea id="f_comment" class="form-control specialClass check-input" cols="30" rows="2" style="margin-top: 0.5rem"></textarea>
                      </div>
                    </div>
                    <div class="form-group row time bg-light">
                      <label for="f_name" class="col-sm-3 col-form-label text-right">Vārds/Telefons:</label>
                      <div class="col-3">
                        <input type="text" class="form-control ui-datepicker check-input" id="f_name">
                      </div>
                      <span class="timeSeparator">/</span>
                      <div class="col-3">
                        <input type="text" class="form-control ui-datepicker check-input" id="f_phone">
                      </div>
                    </div>
                    <div class="form-group row time">
                      <label for="f_email" class="col-sm-3 col-form-label text-right">E-pasts:</label>
                      <div class="col-3"><input type="text" class="form-control ui-datepicker check-input" id="f_email"></div>
                    </div>
                    <div class="separator"></div>
                    <div class="form-group row bg-light">
                      <label for="f_status" class="col-sm-3 col-form-label text-right">Statuss: </label>
                      <div class="col-3">
                        <select class="custom-select mr-sm-2" id="f_status">
                          <option value="0">Brīvs</option>
                          <option value="1" selected>Aizņemts</option>
                          <option value="3">Slēgts</option>
                        </select>
                      </div>
                      <div class="col-2" style="margin-right: 10px;">
                        <input type="checkbox" class="reservationOption" id="newReservation" value="1" title="" style="margin-right: 5px;"><label for="newReservation">Jauns</label>
                      </div>
                      <div class="col-2" style="margin-right: 10px;">
                        <input type="checkbox" class="reservationOption" id="editReservation" value="2" title="" style="margin-right: 5px;"><label for="editReservation">Labots</label>
                      </div>
                      <div class="col-2">
                        <input type="checkbox" class="reservationOption" id="deleteReservation" value="3" title="" style="margin: 0 5px 0 0;"><label for="deleteReservation" style="">Dzēsts</label>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="title" class="col-sm-3 col-form-label text-right">Atlaide:</label>
                      <div class="col-9">
                        <select class="custom-select select-discount-option" style="margin-bottom: 10px;">
                          <option class="form-check-input" value="empty" selected>Nav</option>
                          <option name="discountOption" id="discountOption1" class="form-check-input" value="1">-20% darbam ! ! !</option>
                          <option name="discountOption" id="discountOption2" class="form-check-input" value="2">-50% darbam ! ! !</option>
                          <option name="discountOption" id="discountOption3" class="form-check-input" value="3">-80% darbam ! ! !</option>
                          <option name="discountOption" id="discountOption4" class="form-check-input" value="other">Cits</option>
                        </select>
                        <textarea id="f_slotcomment" style="display: none" class="form-control" cols="30" rows="2"></textarea>
                      </div>
                    </div>
                    <div class="form-group row bg-light">
                      <label class="col-sm-3 col-form-label text-right">Auto dati</label>
                      <div class="col-9">
                        <button type="button" class="btn btn-primary btn-sm" id="car-info-toggle" disabled>Apskatīt datus</button>
                        <pre id="car-info-json" style="display:none; max-height: 260px; overflow:auto; background:#f8f9fa; padding:10px; border-radius:6px; margin-top: 8px;"></pre>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary decline" data-dismiss="modal">Atcelt</button>
                  <button type="button" class="btn btn-primary submit">Saglabāt</button>
                </div>
              </div>
            </div>
          </div>

          <div class="queue_edit modal fade" id="queueModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="slotModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="loader-block">
                  <span class="loading"><span class="fa fa-spinner fa-spin fa-3x"></span></span>
                </div>
                <div class="modal-header">
                  <h5 class="modal-title" id="queueModalLabel">Labot rindu</h5>
                </div>
                <div class="modal-body" style="padding-top: 0px;">
                  <form method="post">
                    <div class="form-group row time bg-light">
                      <label for="title" class="col-sm-3 col-form-label text-right">Nosaukums:</label>
                      <div class="col-8"><input type="text" class="form-control ui-datepicker" id="title"></div>
                    </div>
                    <div class="separator"></div>
                    <div class="form-group row time ">
                      <label for="f_timeinterval" class="col-sm-3 col-form-label text-right">Laika intervāls</label>
                      <div class="col-3">
                        <select class="form-control" id="f_timeinterval">
                          @for ($i = 5; $i <= 30; $i+=5)
                            <option value="{{ $i }}">{{ $i }}</option>
                          @endfor
                        </select>
                      </div>
                    </div>
                    <div class="separator"></div>
                    <div class="form-group row time ">
                      <label for="f_date" class="col-sm-3 col-form-label text-right">Atv./Aizv. laiki:</label>
                      <div class="col-3">
                        <select class="form-control" id="f_opentime">

                        </select>
                      </div>
                      <div class="col-3">
                        <select class="form-control" id="f_closetime">

                        </select>
                      </div>
                    </div>

                    <div class="rims-with-select-row row bg-light">
                      <div class="form-group col-md-3 text-right">
                        <label for="service"><span class="validate" style="color: red;">*</span>Mainīt:</label>
                      </div>
                      <div class="col-md-8" id="service">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="changeVal" id="one_day" value="1" checked>
                          <label class="form-check-label" for="one_day">
                            Vienai dienai
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="changeVal" id="all_days" value="2">
                          <label class="form-check-label" for="all_days">
                            Visām <b class="f_day"></b> uz priekšu (ieskaitot)
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="changeVal" id="all_working_days" value="3">
                          <label class="form-check-label" for="all_working_days">
                            Visām darba dienām uz priekšu (ieskaitot)
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="form-group col-md-3 text-right">
                        <label for="service"><span class="validate" style="color: red;">*</span>Rindu sadalījums:</label>
                      </div>
                      <div class="col-md-8" id="service">
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="queue" id="fullQueue" value="1">
                          <label class="form-check-label" for="fullQueue">
                            Pilna rinda
                          </label>
                        </div>
                        <div class="form-check">
                          <input class="form-check-input" type="radio" name="queue" id="halfQueue" value="2">
                          <label class="form-check-label" for="halfQueue">
                            Pusrinda
                          </label>
                        </div>
                      </div>
                        <div class="form-group col-md-3 text-right queue_services">
                            <label for="queue_services"><span class="validate" style="color: red;">*</span>Pakalpojumi:</label>
                        </div>
                        <div class="col-md-8 queue_services" id="queue_services">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ac_toggle" id="ac_toggle">
                                <label class="form-check-label" for="ac_toggle">
                                    Kondicionieru uzpilde
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="moto_toggle" id="moto_toggle">
                                <label class="form-check-label" for="moto_toggle">
                                    Motociklu montāža
                                </label>
                            </div>
                        </div>
                    </div>
                  </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary decline" data-dismiss="modal">Atcelt</button>
                  <button type="button" class="btn btn-primary submit">Saglabāt</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="{{ asset('js/simple-slot-lock.js?rev=' . time()) }}"></script>
  <script src="{{ asset('js/reservations.js?rev=' . time()) }}"></script>
@endsection
