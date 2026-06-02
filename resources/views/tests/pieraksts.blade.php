<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Testing Page</title>
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
          crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.3/js/jquery.tablesorter.min.js"
          integrity="sha512-qzgd5cYSZcosqpzpn7zF2ZId8f/8CHmFKZ8j7mU4OUXTNRd5g+ZHBPsgKEwoqxCtdQvExE5LprwwPAgoicguNg=="
          crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.8.0/chart.min.js" integrity="sha512-sW/w8s4RWTdFFSduOTGtk4isV1+190E/GghVffMA9XczdJ2MDzSzLEubKAs5h0wzgSJOQTRYyaz73L3d6RtJSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  {{--  <link rel="stylesheet" href="{{ asset('css/custom.css?rev=' . time()) }}" type="text/css" media="all">--}}
  <script type="text/javascript" src="{{ asset('js/atc.js?rev=' . time()) }}"></script>

</head>
<body>

@extends('layouts.app')

@section('content')

  <div class="schedule-table">
    <div class="container">
      @for ($day = 0; $day < $visibleDays; $day++)

        @php
          $date = $workingDays[$day];
          $dayOfWeek = $_weekDays[date('N', strtotime($date.' 00:00:00'))];
          $dateFmt = date('d.m.Y', strtotime($date.' 00:00:00'));
          $today = date('Y-m-d');

          $openTime = 0;
          $closeTime = -1;
        @endphp

        <h1>{{ $dayOfWeek . ', ' . $dateFmt }}</h1>
          <div class="row">
            @foreach ($offices as $office)
              @php

                $openTime = 0;
                $closeTime = -1;

                $office->_openQueues = 0;
                foreach ($office->_queues as $queue){
                    if ($queue->isVisible($date)) $office->_openQueues++;
                }

                if ($openTime==0){
                    $openTime = $office->getOpenTime($date);
                } else {
                    $t = $office->getOpenTime($date);
                    if ($t>0){
                        $openTime = min($openTime, $t);
                    }
                }
                $closeTime = max($closeTime, $office->getCloseTime($date));

              @endphp
              @if (count($office->_queues) >= 3)
                <div class="col-7">
              @else
                <div class="col-5">
              @endif
              <table class="table table-@if($office->office_id === 1){{'ulbroka'}}@else{{'kalnciema'}}@endif">
                <thead>
                <tr>
                  <th scope="col" colspan="{{ count($office->_queues) * 2 }}" class="text-center">{{ $office['title'] }}</th>
                </tr>
                </thead>
                <tbody>
                @php
                @endphp
                @if (($openTime==0)&&($closeTime==0))
                  <tr>
                    @foreach ($office->_queues as $queue)
                      <td class="time-slot"></td>
                      <td class="closed-slot">Slēgts</td>
                    @endforeach
                  </tr>
                @else
                  @for ($i=$openTime;$i<$closeTime;$i+=$timeStep)
                    <tr>
                      @foreach ($office->_queues as $queue)
                      @php $slotNumber = $queue->getSlotNumberByInterval($date,$i); @endphp
                      @if (($slotNumber!==false)&&($queue->_workingDays[$date]->isVisible()))
                        @if ($queue->isIntervalBeginning($date,$i))
                          @php $slot = $queue->_slots[$date][$slotNumber]; @endphp

                          @switch ($slot->status)
                            @case (0)
                              @if ($date == $today)
                                @php
                                  $slotClass = 'slot-gray';
                                  $slotCaption = '&nbsp;';
                                  $slotText = ''.$slotCaption.'';
                                @endphp
                              @else
                              @if (trim($slot->comment)=='')
                                @php
                                  $slotClass = 'available-slot';
                                  $slotCaption = '<button class="free-slot-link" id="slot' . $slot->iorder . '-' . $slot->queue_id . '" data-col="' . $slot->queue_id . '" data-iorder="' . $slot->iorder . '" data-date="' . $slot->date . '" data-toggle="modal" data-target="#reservation">Brīvs</button>';
                                @endphp
                              @else
                                @php
                                  $slotClass = 'slot-offer';
                                  $slotCaption = $slot->comment;
                                @endphp
                              @endif
                                @php
                                  //'. url_self_reference(array('d'=>$date,'qu'=>$queue->id,'time'=>$slot->iorder)).'
                                  $slotText = $slotCaption;
                                @endphp
                              @endif
                            @break;

                            @case (1)
                              @if ($date == $today)
                                @php $slotClass = 'slot-gray'; @endphp
                              @else
                                @php $slotClass = 'taken-slot'; @endphp
                              @endif
                              @php
                              $takenBy = json_decode($slot->toArray()['takenby']);
                              $plate = substr($takenBy->ownerPhone,-3,3);
                              $plate = filter_var($plate, FILTER_SANITIZE_NUMBER_INT);
                              $plate = trim($plate,' -.');

                              $slotText = ''. \App\Helper\Tires::truncateCharacters(trim($takenBy->vehicleMake),8,'&mldr;',1).' xxxxx'.$plate.'';
                              @endphp
                            @break

                            @case (2)
                            @if ($date == $today)
                              @php
                                $slotClass = 'slot-gray';
                                $slotText = '&nbsp;';
                              @endphp
                            @else
                              @php
                                $slotClass = 'slot-offer';
                                //'. url_self_reference(array('d'=>$date,'qu'=>$queue->id,'time'=>$slot->iorder)).'
                                $slotText = '<a href="">'.$slot->comment.'</a>';
                              @endphp
                            @endif
                            @break

                            @case (3)
                            @if (trim($slot->comment)=='')
                              @php $slotCaption = 'Slēgts'; @endphp
                            @else
                              @php $slotCaption = $slot->comment; @endphp
                            @endif
                            @if ($date == $today)
                              @php
                                $slotClass = 'slot-gray';
                                $slotText = '&nbsp;';
                              @endphp
                            @else
                              @php
                                $slotClass = 'closed-slot';
                                $slotText = $slotCaption;
                              @endphp
                            @endif
                            @break
                          @endswitch

                          @if ($queue->_workingDays[$date]->secondaryAvailable)
                            <td class="time-slot"@if ($queue->_workingDays[$date]->slotSize > 1) rowspan="{{ $queue->_workingDays[$date]->slotSize/$timeStep/2 }}" @endif>{{ App\Models\Office::timeByInterval($i) }}</td>
                            <td class="{{ $slotClass }} slot"@if ($queue->_workingDays[$date]->slotSize > 1) rowspan="{{ $queue->_workingDays[$date]->slotSize/$timeStep/2 }}" @endif>
                              {!! $slotText !!}
                            </td>
                          @else
                            <td class="time-slot">{{ App\Models\Office::timeByInterval($i) }}</td>
                            <td class="{{ $slotClass }} slot">
                              {!! $slotText !!}
                            </td>
                          @endif
                        @else
                          @if ($queue->_workingDays[$date]->secondaryAvailable)
                            @php $slot = $queue->_slots[$date][$slotNumber]; @endphp
                            <td class="time-slot">{{ App\Models\Office::timeByInterval($i) }}</td>
                            <td class="{{ $slotClass }} slot">
                              {!! $slotText !!}
                            </td>
                          @endif
                        @endif
                      @else
                        @if (!$queue->isVisible($date))
                          @if ($office->_openQueues==0)
                            <td class="header-time">&nbsp;</td><td class="slot slot-closed">Slēgts</td>
                          @else
                            <td class="header-empty"></td><td class="slot-empty"></td>
                          @endif
                        @else
                          <td class="header-empty"></td><td class="slot-empty"></td>
                        @endif
                      @endif
                      @endforeach
                    </tr>
                  @endfor
                @endif
                </tbody>
                </table>
                </div>
            @endforeach
          </div>
    @endfor
    </div>
  </div>

  <script>

  </script>
@endsection

</body>
</html>
