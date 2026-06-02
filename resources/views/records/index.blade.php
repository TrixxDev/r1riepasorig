@extends('layouts.app')

@section('body-title', 'main-schedule')
@section('title', 'Tests')

@section('content')
    <link rel="stylesheet" href="{{asset('css/schedule.css?rev=' . time())}}">
    <div class="container-fluid records">
        <div class="">
            <div class="main-content clearfix col-md-12 col-xl-12">
                <div class="loading" style="display: none"></div>
                <div id="content-wrapper" class="right-column col-lg-12">
                <div class="schedule-table dashboard">
                    @include('components.calendar')
                    @for ($day = 0; $day <= $visibleDays; $day++)
                    @php
                      $date = Date('d.m.Y', strtotime('+' . $day . ' days'));
                      $dayOfWeek = $dayTitles[date('N', strtotime($date.' 00:00:00'))];
                    @endphp
                    <h1 style="font-size: 1.7em; margin-top: 20px;">{{ $dayOfWeek }}, {{ Date('d.m.Y', strtotime('+' . $day . ' days')) }}</h1>
                    <div class="row">
                      @foreach ($offices as $office)
                        <div class="col-md-{{ round(12 / $queueSum * max(1, (int)($clientQueuesCountByOffice[$office->office_id] ?? 0))) }} grid grid-cols-{{ max(1, (int)($clientQueuesCountByOffice[$office->office_id] ?? 0)) }}" style="@if ($office->office_id == 1){{'border-right: 2px solid black;'}}@endif" data-date="{{ date('Y-m-d', strtotime($date.' 00:00:00')) }}">
                          @foreach ($workingDays as $workingDay)
                            @if ($workingDay->date == Date('Y-m-d', strtotime('+' . $day . ' days')))
                              @php
                                $_queueRow = $queuesById[$workingDay->queue_id] ?? null;
                              @endphp
                              @if (!$_queueRow || !$_queueRow->isAvailableForPublicBooking())
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

                                //$timeStep = $workingDay->timestep;

                                $numberOfSteps = ceil($opentime->diffInMinutes($closetime) / $timeStep);

                                $workingOffice = $officesById[$workingDay->office_id] ?? null;
                              @endphp
                              @if ($workingOffice && $workingOffice->office_id == $office->office_id)
                                @if ($workingDay->weekday != 7)
                                  @if ($workingDay->is_opened == 1)
                                    <div class="table office_{{ $workingOffice->office_id }}" data-queue-id="{{ $workingDay->queue_id }}" data-allow-all="@if (is_null($halfAcService) && is_null($halfMotoService)){{'true'}}@else{{'false'}}@endif">
                                      <div class="title text-sm">{{ $workingOffice->title }}</div>
                                      @for ($i = $opentime->diffInMinutes($closetime) / $timeStep - $openTime1->diffInMinutes($closetime) / $timeStep; $i <= $numberOfSteps; $i++)
                                        @php
                                          $slot = $slotsByKey[\App\Models\Slot::reservationGridKey($workingDay->queue_id, $workingDay->date, $i)] ?? null;
                                          $currentTime = $opentime->copy()->addMinutes($timeStep * $i)->format('H:i');
                                        @endphp

                                        @php
                                          $takenBy = null;
                                          if (!is_null($slot)) {
                                              if ($slot->status === 1) {
                                                  $takenBy = json_decode($slot->takenby);
                                              }

                                              switch ($slot->status) {
                                                  case 0:
                                                      $slotClass = 'time-free';
                                                      $content = '<div class="slot free-slot-link available-slot">Brīvs</div>';
                                                      if ($slot->comment !== null) {
                                                          $slotClass = 'time-free discount';
                                                          $content = '<button class="status free-slot-link discount-slot available-slot">' . $slot->comment . '</button>';
                                                      }
                                                      if (date('Y-m-d') == $workingDay->date) {
                                                          $slotClass = \Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now() ? 'time-free' : 'slot-gray';
                                                          $content = $slotClass === 'time-free'
                                                              ? '<div class="slot free-slot-link available-slot">Brīvs</div>'
                                                              : '<div class="slot unavailable taken-slot disabled-slot">Aizņemts</div>';
                                                              if ($slot->comment !== null) {
                                                                  $slotClass = \Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now() ? 'time-free discount' : 'slot-gray';
                                                                  $content = $slotClass === 'time-free discount'
                                                                      ? '<button class="status free-slot-link discount-slot available-slot">' . $slot->comment . '</button>'
                                                                      : '<div class="slot unavailable taken-slot disabled-slot">Aizņemts</div>';
                                                              }
                                                      }
                                                      break;
                                                  case 1:
                                                      $slotClass = 'taken-slot';
                                                      if ($slot->takenby !== null) {
                                                        $content = '<div class="slot taken-slot">' . \App\Http\Controllers\Records\RecordController::truncateCharacters(trim($takenBy->car_brand), 6, '&mldr;', 1) . ' xxxxx' . substr($takenBy->phone_number, -3, 3) . '</div>';
                                                      } else {
                                                        $content = '<div class="slot taken-slot">xxxxx</div>';
                                                      }
                                                      if (date('Y-m-d') == $workingDay->date) {
                                                          if ($slot->takenby !== null) {
                                                            $slotClass = \Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now() ? 'taken-slot' : 'slot-gray';
                                                            $content = $slotClass === 'taken-slot'
                                                              ? '<div class="slot taken-slot">' . \App\Http\Controllers\Records\RecordController::truncateCharacters(trim($takenBy->car_brand), 6, '&mldr;', 1) . ' xxxxx' . substr($takenBy->phone_number, -3, 3) . '</div>'
                                                              : '<div class="slot unavailable taken-slot">' . \App\Http\Controllers\Records\RecordController::truncateCharacters(trim($takenBy->car_brand), 6, '&mldr;', 1) . ' xxxxx' . substr($takenBy->phone_number, -3, 3) . '</div>';
                                                          } else {
                                                            $slotClass = \Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now() ? 'taken-slot' : 'slot-gray';
                                                            $content = $slotClass === 'taken-slot'
                                                              ? '<div class="slot taken-slot">xxxxx</div>'
                                                              : '<div class="slot unavailable taken-slot disabled-slot">Aizņemts</div>';
                                                          }
                                                      }
                                                      break;
                                                  case 3:
                                                      $slotClass = 'time-closed';
                                                      $content = '<div class="slot closed-slot">Slēgts</div>';
                                                      break;
                                              }
                                          } else {
                                              $slotClass = 'time-free';
                                              $content = '<button class="status free-slot-link available-slot">Brīvs</button>';
                                              if (date('Y-m-d') == $workingDay->date) {
                                                  if (\Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now()) {
                                                      $slotClass = 'time-free';
                                                      $content = '<button class="status free-slot-link available-slot">Brīvs</button>';
                                                  } else {
                                                      $slotClass = 'time-taken';
                                                      $content = '<div class="slot unavailable taken-slot disabled-slot">Aizņemts</div>';
                                                  }
                                              }
                                          }

                                          if ($workingDay->is_half) {
                                              $bothHalfServices = \App\Http\Controllers\Records\RecordController::isBothHalfServices($workingDay);
                                              $halfRole = \App\Http\Controllers\Records\RecordController::halfSlotDisplayRole($i, $workingDay);
                                              $halfMotoServiceSlot = $halfMotoService;
                                              $halfAcServiceSlot = $halfAcService;
                                              if ($bothHalfServices) {
                                                  if ($halfRole === 'blocked') {
                                                      $halfMotoServiceSlot = null;
                                                  } elseif ($halfRole === 'ac') {
                                                      $halfMotoServiceSlot = null;
                                                  } elseif ($halfRole === 'moto') {
                                                      $halfAcServiceSlot = null;
                                                  }
                                              }
                                              $useOddHalfBranch = $bothHalfServices
                                                  ? ($halfRole === 'blocked' || $halfRole === 'moto')
                                                  : ($i % 2 == 1);
                                              if ($useOddHalfBranch) {
                                                  if ($slot) {
                                                      $halfIntervalOverride = false;
                                                      // For half-slot schedules, odd intervals are normally not bookable.
                                                      // If slots are pre-created in DB with status=0, ensure UI still shows them as unavailable (Aizņemts),
                                                      // unless the "half moto" mode is enabled (then show Moto montāža).
                                                      if ($slot->status == 0 && $slot->comment === null) {
                                                          if (!is_null($halfMotoServiceSlot)) {
                                                              $slotClass = 'time-free';
                                                              $content = '<button class="status free-slot-link available-slot">Moto montāža</button>';
                                                              $halfIntervalOverride = true;
                                                          } else {
                                                              $slotClass = 'time-taken-half';
                                                              $content = '<div class="slot taken-slot">Aizņemts</div>';
                                                              $halfIntervalOverride = true;
                                                          }
                                                      }
                                                      if (date('Y-m-d') == $workingDay->date) {
                                                        if (!$halfIntervalOverride && \Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now()) {
                                                          $slotClass = ($slot->edituser !== 0 && is_null($slot->takenby)) ? 'time-free' : 'taken-slot';
                                                          if ($slot->comment !== null && is_null($slot->takenby)) {
                                                            $slotClass = 'time-free';
                                                          }
                                                        }
                                                        $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '"><div class="time-slot">' . $currentTime . '</div>' . $content . '</div>';
                                                      } else {
                                                        $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '"><div class="time-slot">' . $currentTime . '</div>' . $content . '</div>';
                                                      }
                                                  } else {
                                                      if (date('Y-m-d') == $workingDay->date) {
                                                        if (\Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now()) {
                                                          if ($i >= 0) {
                                                            if (!is_null($halfMotoServiceSlot)) {
                                                              $content = '<div class="time-status flex time-free" data-iorder="' . $i . '" data-moto="true"><div class="time-slot">' . $currentTime . '</div><button class="status free-slot-link available-slot">Moto montāža</button></div>';
                                                            } else {
                                                              $content = '<div class="time-status flex time-taken-half" data-iorder="' . $i . '" data-moto="true"><div class="time-slot">' . $currentTime . '</div><div class="slot taken-slot">Aizņemts</div></div>';
                                                            }
                                                          } else {
                                                            $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                          }
                                                        } else {
                                                          if ($i >= 0) {
                                                            $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '" data-moto="true"><div class="time-slot">' . $currentTime . '</div><div class="slot unavailable taken-slot">Aizņemts</div></div>';
                                                          } else {
                                                            $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                          }
                                                        }
                                                      } else {
                                                          if (!is_null($halfMotoServiceSlot)) {
                                                              if ($i >= 0) {
                                                                $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '" data-moto="true"><div class="time-slot">' . $currentTime . '</div><button class="status free-slot-link available-slot">Moto montāža</button></div>';
                                                              } else {
                                                                $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                              }
                                                          } else {
                                                              if ($i >= 0) {
                                                                $content = '<div class="time-status flex time-taken-half" data-iorder="' . $i . '" data-moto="true"><div class="time-slot">' . $currentTime . '</div><div class="slot taken-slot">Aizņemts</div></div>';
                                                              } else {
                                                                $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                              }
                                                          }
                                                      }
                                                  }
                                              } else {
                                                  if ($slot) {
                                                      if (date('Y-m-d') == $workingDay->date) {
                                                        if (\Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now()) {
                                                          $slotClass = 'taken-slot';
                                                          if ($slot->comment !== null && is_null($slot->takenby)) {
                                                            $slotClass = 'time-free';
                                                          }
                                                        }
                                                        $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '"><div class="time-slot">' . $currentTime . '</div>' . $content . '</div>';
                                                      } else {
                                                        $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '"><div class="time-slot">' . $currentTime . '</div>' . $content . '</div>';
                                                      }
                                                  } else {
                                                      if (date('Y-m-d') == $workingDay->date) {
                                                        if (\Carbon\Carbon::parse($currentTime)->subMinutes(30) >= \Carbon\Carbon::now()) {
                                                          if ($i >= 0) {
                                                            if (!is_null($halfAcServiceSlot)) {
                                                                $content = '<div class="time-status flex time-free" data-iorder="' . $i . '" data-ac="true"><div class="time-slot">' . $currentTime . '</div><button class="status free-slot-link available-slot">Kondicioniera apkope</button></div>';
                                                            } else {
                                                              $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '"><div class="time-slot">' . $currentTime . '</div><button class="status free-slot-link available-slot">Brīvs</button></div>';
                                                            }
                                                          } else {
                                                            $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                          }
                                                        } else {
                                                          if ($i >= 0) {
                                                            $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '" data-ac="true"><div class="time-slot">' . $currentTime . '</div><div class="slot unavailable taken-slot">Aizņemts</div></div>';
                                                          } else {
                                                            $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                          }
                                                        }
                                                      } else {
                                                          if (!is_null($halfAcServiceSlot)) {
                                                              if ($i >= 0) {
                                                                $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '" data-ac="true"><div class="time-slot">' . $currentTime . '</div><button class="status free-slot-link available-slot">Kondicioniera apkope</button></div>';
                                                              } else {
                                                                $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                              }
                                                          } else {
                                                              if ($i >= 0) {
                                                                $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '"><div class="time-slot">' . $currentTime . '</div><button class="status free-slot-link available-slot">Brīvs</button></div>';
                                                              } else {
                                                                $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                                              }
                                                          }
                                                      }
                                                  }
                                              }
                                          } else {
                                              if ($i >= 0) {
                                                  $content = '<div class="time-status flex ' . $slotClass . '" data-iorder="' . $i . '"><div class="time-slot">' . $currentTime . '</div>' . $content . '</div>';
                                              } else {
                                                  $content = '<div class="time-status flex time-closed" data-iorder="' . $i . '"></div>';
                                              }
                                          }
                                        @endphp

                                        {!! $content !!}
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
                <section id="mobile-main">
                    <form method="POST">
                        <input type="hidden" name="date" title="">
                        <input type="hidden" name="slotNumber" title="">
                        <input type="hidden" name="filiale" title="">
                        <input type="hidden" name="part" title="">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="mobile-modalTitle">
                                        Pieraksts
                                    </h5>
                                </div>
                                <div class="modal-body mobile-reservation-modal-body">
                                    <div class="container-fluid">

                                        <div class="col-md-12 mobile-body">

                                            <div class="form-group reservation-filiale" style="margin-bottom: 0!important;">
                                                <span class="validate">*</span><label for="select">Filiāle</label>

                                                <div id="mobile-filiale">

                                                    <div class="filiale_grid">
                                                        <label class="filiale_card">
                                                            <input name="filiale" class="filiale_radio" type="radio" id="filiale_ulbroka" value="1" title="">

                                                            <span class="filiale_plan-details">
                                      <span class="filiale_plan-cost">Ulbroka</span>
                                      <span>Acones iela 2a</span>
                                      <span>67910555</span>
                                    </span>
                                                        </label>
                                                        <label class="filiale_card">
                                                            <input name="filiale" class="filiale_radio" type="radio" id="filiale_riga" value="2" title="">

                                                            <span class="filiale_plan-details" aria-hidden="true">
                                      <span class="filiale_plan-cost">Rīga</span>
                                      <span>Kalnciema iela 39</span>
                                      <span>67615615</span>
                                    </span>
                                                        </label>
                                                    </div>

                                                </div>
                                            </div>

                                            <div id="mobile-slots-choice">
                                                <div class="w">
                                                    <div>
                                                        <div class="reservation">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="mobile-reservation-form" tabindex="1">

                                                <div class="form-group purpose">
                                                    <label for="serviceOption"><span class="validate">*</span>Es vēlos:</label>
                                                    <div id="mobile-service">
                                                        <select class="custom-select" name="serviceOption" required="required">
                                                            @foreach ($services as $service)
                                                                <option value="{{ $service->service_id }}" @if ($service->f_save == 1) data-save="1"@endif @if ($service->f_save == 2) data-save="2"@endif @if ($service->f_ac == 1) data-ac="1" @endif @if ($service->f_moto == 1) data-moto="1" @endif>{{ $service->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group rims-with-mobile" style="display: none;">
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="rimsWith1">
                                                            <img loading="lazy" src="https://r1riepas.lv/images/bez_diskiem.jpg" alt="">
                                                        </label>
                                                        <br>
                                                        <input value="1" class="form-check-input" type="radio" name="rims_with_input" id="rimsWith1" title="">
                                                        Līdzi būs riepas bez diskiem
                                                    </div>
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="rimsWith2">
                                                            <img loading="lazy" src="https://r1riepas.lv/images/ar_diskiem.png" alt="">
                                                        </label>
                                                        <br>
                                                        <input value="2" class="form-check-input" type="radio" name="rims_with_input" id="rimsWith2" title="">
                                                        Līdzi būs riepas ar diskiem
                                                    </div>
                                                </div>

                                                <div class="form-group rims-storageBin" style="display: none;">
                                                    <label for="mobile_storage_bin">Glabāšanas talona numurs:</label>
                                                    <input id="mobile_storage_bin" type="text" class="form-control" title="">
                                                    <span style="font-size: 11px;line-height: 10px;">Ja Jums pašlaik nav zināms glabāšanas talona numurs, tas nekas, atradīsim Jūsu riepas vai riteņus pēc automašīnas numura</span>
                                                </div>
                                                <div class="form-group">
                                                    <label for="mobile-reg_nr"><span class="validate">*</span>Reģistrācijas numurs:</label>
                                                    <input type="text" class="form-control" id="mobile-reg_nr" title="">
                                                </div>
                                                <div class="form-group">
                                                    <label for="mobile-brand"><span class="validate">*</span>Auto marka:</label>
                                                    <input id="mobile-brand" type="text" class="form-control" title="">
                                                </div>

                                                <div class="form-group">
                                                    <label for="mobile-model"><span class="validate">*</span>Auto modelis:</label>
                                                    <input id="mobile-model" type="text" class="form-control" title="">
                                                </div>

                                                <div class="form-group">
                                                    <label for="mobile-comment">Piezīmes</label>
                                                    <textarea id="mobile-comment" cols="40" rows="4" class="form-control"></textarea>
                                                </div>

                                                <div class="form-group">
                                                    <label for="mobile-name">Mans vārds:</label>
                                                    <input id="mobile-name" type="text" class="form-control" title="">
                                                </div>

                                                <div class="form-group phone-number">
                                                    <label for="mobile-phone"><span class="validate">*</span>Mans tālruņa numurs:</label>
                                                    <input type="text" class="form-control" id="mobile-phone" title="">
                                                </div>
                                                <div class="form-group client-email last">
                                                    <label for="mobile-email">Mans e-pasts:</label>
                                                    <input type="email" class="form-control" id="mobile-email" title="">
                                                </div>
                                                <div class="modal-footer reservation-modal-footer">
                                                    <button type="button" class="btn btn-primary" id="mobile-submit-reservation">Pierakstīties</button>
                                                    <button type="button" class="btn btn-primary" id="mobile-close-modal" style="display: none;">Atgriezties</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mobile-body-success" style="display: none;">
                                            <div class="alert alert-success">

                                            </div>
                                            <a href="https://r1riepas.lv/pieraksts" class="btn btn-success">Atgriezties</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </section>
            </div>
        </div>
    </div>
</div>

{{--    <div class="grid grid-cols-5">--}}

{{--    </div>--}}

<!-- Main modal -->
    {{--    <div id="record-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">--}}
    {{--        <div class="relative w-full max-w-4xl max-h-full">--}}
    {{--            <!-- Modal content -->--}}
    {{--            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">--}}
    {{--                <button type="button" class="close-modal absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto flex items-center dark:hover:bg-gray-800 dark:hover:text-white">--}}
    {{--                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>--}}
    {{--                    <span class="sr-only">Close modal</span>--}}
    {{--                </button>--}}
    {{--                <div class="px-6 py-6 lg:px-8">--}}
    {{--                    <h3 class="mb-4 text-xl font-medium text-gray-900 dark:text-white">Pieraksts</h3>--}}
    {{--                    <form class="space-y-6 reservation-form" method="post">--}}
    {{--                        {!! csrf_field() !!}--}}
    {{--                        <div class="grid md:grid-cols-2 md:gap-6">--}}
    {{--                            <div class="relative z-0 w-full mb-6 group">--}}
    {{--                                <input type="text" name="car_brand" id="car_brand" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required="">--}}
    {{--                                <label for="car_brand" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Auto marka <span class="text-red-500">*</span></label>--}}
    {{--                            </div>--}}
    {{--                            <div class="relative z-0 w-full mb-6 group">--}}
    {{--                                <input type="text" name="car_model" id="car_model" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required="">--}}
    {{--                                <label for="car_model" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Auto modelis <span class="text-red-500">*</span></label>--}}
    {{--                            </div>--}}
    {{--                        </div>--}}
    {{--                        <div class="relative z-0 w-full mb-6 group">--}}
    {{--                            <input type="text" name="lic_plate" id="lic_plate" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required="">--}}
    {{--                            <label for="lic_plate" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Reģistrācijas numurs <span class="text-red-500">*</span></label>--}}
    {{--                        </div>--}}
    {{--                        <div class="relative z-0 w-full mb-6 group">--}}
    {{--                            <h3 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Es vēlos <span class="text-red-500">*</span></h3>--}}
    {{--                            <ul class="w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white services">--}}
    {{--                                @foreach (\App\Models\Service::all() as $service)--}}
    {{--                                <li class="w-full border-b border-gray-200 rounded-t-lg dark:border-gray-600">--}}
    {{--                                    <div class="flex items-center ml-3">--}}
    {{--                                        <input id="service_{{ $service->service_id }}" type="radio" value="{{ $service->service_id }}" name="service" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-700 dark:focus:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500" @if ($service->enabled && $service->f_ac) {{'data-ac="true"'}} @elseif ($service->enabled && $service->f_moto) {{'data-moto="true"'}} @endif >--}}
    {{--                                        <label for="service_{{ $service->service_id }}" class="w-full py-3 ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ $service->title }}</label>--}}
    {{--                                    </div>--}}
    {{--                                </li>--}}
    {{--                                @endforeach--}}
    {{--                            </ul>--}}
    {{--                        </div>--}}
    {{--                        <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Piezīmes</label>--}}
    {{--                        <textarea id="message" name="user_comment" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder=""></textarea>--}}
    {{--                        <div class="relative z-0 w-full mb-6 group">--}}
    {{--                            <input type="text" name="name" id="name" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required="">--}}
    {{--                            <label for="name" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Mans vārds</label>--}}
    {{--                        </div>--}}
    {{--                        <div class="relative z-0 w-full mb-6 group">--}}
    {{--                            <input type="text" name="phone_number" id="phone_number" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required="">--}}
    {{--                            <label for="phone_number" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Mans tālruņa numurs <span class="text-red-500">*</span></label>--}}
    {{--                        </div>--}}
    {{--                        <div class="relative z-0 w-full mb-6 group">--}}
    {{--                            <input type="email" name="email" id="email" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required="">--}}
    {{--                            <label for="email" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Mans e-pasts</label>--}}
    {{--                        </div>--}}

    {{--                        <ul class="error-list w-full text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" style="display: none;">--}}

    {{--                        </ul>--}}

    {{--                        <button type="submit" name="fillSlot" class="w-full max-w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Pierakstīties</button>--}}
    {{--                        <button type="button" class="w-full max-w-full text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Atcelt</button>--}}
    {{--                    </form>--}}
    {{--                    <div style="display: none;" id="success-alert" class="p-4 mb-4 text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800" role="alert">--}}
    {{--                        <div class="mt-2 mb-4 text-sm text-message">--}}

    {{--                        </div>--}}
    {{--                        <div class="flex">--}}
    {{--                            <button type="button" class="close-modal text-green-800 bg-transparent border border-green-800 hover:bg-green-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center dark:hover:bg-green-600 dark:border-green-600 dark:text-green-400 dark:hover:text-white dark:focus:ring-green-800" aria-label="Close">--}}
    {{--                                Aizvērt--}}
    {{--                            </button>--}}
    {{--                        </div>--}}
    {{--                    </div>--}}
    {{--                    <div style="display: none;" id="warning-alert" class="p-4 mb-4 text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800" role="alert">--}}
    {{--                        <div class="mt-2 mb-4 text-sm text-message">--}}

    {{--                        </div>--}}
    {{--                        <div class="flex">--}}
    {{--                            <button type="button" class="close-modal text-white bg-yellow-800 hover:bg-yellow-900 focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 mr-2 text-center flex items-center dark:bg-yellow-300 dark:text-gray-800 dark:hover:bg-yellow-400 dark:focus:ring-yellow-800" aria-label="Close">--}}
    {{--                                Aizvērt--}}
    {{--                            </button>--}}
    {{--                        </div>--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </div>--}}

    @php
      $carInfoToken = app(\App\Services\CarInfoTokenService::class)->issue(request());
    @endphp
    <div class="modal fade" id="reservation" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" data-car-info-url="{{ url('/api/car-info') }}">
        <div class="modal-background" data-dissmiss="modal"></div>
        <form method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" title="">
            <input type="hidden" name="car_info_token" value="{{ $carInfoToken }}" title="">
            <input type="hidden" name="date" title="">
            <input type="hidden" name="queue_id" title="">
            <input type="hidden" name="slotNumber" title="">
            <input type="hidden" name="part" title="">
            <div class="modal-dialog reservation-modal-dialog" role="document">
                <div class="modal-content">
                    <div class="loader-block">
                        <span class="loading"><span class="fa fa-spinner fa-spin fa-3x"></span></span>
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">
                            Mans pieraksts - <span class="datedayOfWeek"></span> <span class="timeOfDay"></span>, <span class="officeTitle"></span>
                        </h5>
                    </div>
                    <div class="modal-body reservation-modal-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <label for="reg_nr" class="col-sm-12 col-md-3" style="text-align: left;"><span class="validate">*</span>Reģistrācijas numurs:</label>
                                        <div class="col-sm-12 col-md-9">
                                            <input type="text" class="form-control" id="reg_nr" title="">
                                        </div>
                                    </div>
                                    <div class="form-row row">
                                        <div class="form-group col-md-3 col-sm-12 hidden-sm-down">
                                            <label for="brand"><span class="validate" style="color: red;">*</span>Auto marka un modelis:</label>
                                        </div>
                                        <div class="col-md-3 col-sm-12 hidden-md-up">
                                            <label for="brand"><span class="validate" style="color: red;">*</span>Auto marka:</label>
                                        </div>
                                        <div class="form-group col-md-5 col-sm-12">
                                            <input type="text" class="form-control" id="brand" title="">
                                        </div>
                                        <div class="col-md-3 col-sm-12 hidden-md-up">
                                            <label for="model"><span class="validate" style="color: red;">*</span>Auto modelis:</label>
                                        </div>
                                        <div class="form-group col-md-4 col-sm-12">
                                            <input type="text" class="form-control" id="model" title="">
                                        </div>
                                    </div>
                                    <div class="form-group services">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <label for="service"><span class="validate" style="color: red;">*</span>Es vēlos:</label>
                                            </div>
                                            <div class="col-md-8" id="service">
                                              @foreach ($services as $service)
                                                <div class="form-check">
                                                  <input class="form-check-input" type="radio" name="serviceOption" id="serviceOption{{ $service->service_id }}" @if ($service->f_save == 1) data-save="1"@endif @if ($service->f_save == 2) data-save="2"@endif @if ($service->f_ac == 1) data-ac="1" @endif @if ($service->f_moto == 1) data-moto="1" @endif value="{{ $service->service_id }}">
                                                  <label class="form-check-label" for="serviceOption{{ $service->service_id }}">
                                                    {{ $service->title }}
                                                  </label>
                                                </div>
                                              @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row rims_with" style="display: none;">
                                        <label for="save_nr" class="col-sm-3" style="text-align:left;">Izvēle:</label>
                                        <div class="col-sm-9">
                                            <div class="form-check col-sm-6">
                                                <input class="form-check-input" type="radio" name="rims_with_input" id="flexRadioDefault1" value="1">
                                                <label class="form-check-label" for="flexRadioDefault1">
                                                    Riepas bez diskiem

                                                    <img class="rims-with-img" loading="lazy" src="https://r1riepas.lv/images/bez_diskiem.jpg" alt="riepas_ar_diskiem">
                                                </label>
                                            </div>

                                            <div class="form-check col-sm-6">
                                                <input class="form-check-input" type="radio" name="rims_with_input" id="flexRadioDefault2" value="2">
                                                <label class="form-check-label" for="flexRadioDefault2">
                                                    Riepas ar Diskiem

                                                    <img class="rims-with-img" loading="lazy" src="https://r1riepas.lv/images/ar_diskiem.png" alt="riepas_ar_diskiem">
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-group row temp_save_nr" style="display: none;">
                                        <label for="save_nr" class="col-sm-3" style="text-align:left;">Glabāšanas talona numurs:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="save_nr"></div>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9" style="font-size: 11px; line-height: 10px;">Ja Jums pašlaik nav zināms glabāšanas talona numurs, tas nekas, atradīsim Jūsu riepas vai riteņus pēc automašīnas numura</div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="comment" class="col-sm-3" style="text-align: left;">Piezīmes:</label>
                                        <div class="col-sm-9">
                                            <textarea id="comment" class="form-control" cols="20" rows="4"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="name" class="col-sm-3" style="text-align: left;">Mans vārds:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="name" title="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="phone" class="col-sm-3" style="text-align: left;"><span class="validate" style="color: red;">*</span>Mans tālruņa numurs:</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="phone" title="">
                                        </div>
                                    </div>
                                    <div class="form-group row last">
                                        <label for="email" class="col-sm-3" style="text-align: left;">Mans e-pasts:</label>
                                        <div class="col-sm-9">
                                            <input type="email" class="form-control" id="email" title="">
                                        </div>
                                    </div>
                                    <input type="hidden" name="recaptcha" id="recaptcha" title="">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer reservation-modal-footer">
                            <button type="button" class="btn btn-secondary col-xs-12 col-md-6 mb-1" id="close-modal">Atcelt</button>
                            <span style="margin: 0 auto;" class="hidden-md-down"></span>
                            <button type="button" class="btn btn-primary col-xs-12 col-md-6 mb-1" id="submit-reservation">Pierakstīties</button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

<script src="{{ asset('js/simple-slot-lock.js?rev=' . time()) }}"></script>
<script src="{{ asset('js/client.js?rev=' . time()) }}"></script>
@endsection
