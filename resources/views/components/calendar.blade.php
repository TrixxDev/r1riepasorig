@if (Auth::check() && !Auth::user()->hasRole(['Administrators', 'Moderators']))
  <div class="card extra-options">
      <div class="dropdown-calendar">
          <div class="btn btn-danger">Kalendārs</div>
          <div class="dropdown-content calendar">
              <a href="{{ route('pieraksts') }}">Pieraksts</a>
              <a href="{{ route('rezervacijas') }}">Rezervācijas</a>
          </div>
      </div>

      <div class="flex">
          <div class="dropdown-print">
              <div class="btn btn-danger" onclick="togglePrintDropdown()">Drukāt</div>
              <div class="dropdown-content print">

                  @foreach (\App\Models\Office::all() as $office)
                      @php
                          $visibleDays2 = 6;
                          $workingDays = [];
                          $workingDays1 = [];
                      @endphp
                      @for ($day = 0; $day < $visibleDays; $day++)
                          @php
                              array_push($workingDays, date('Y-m-d', strtotime(date('Y-m-d').'+' . $day . ' days')));
                          @endphp
                      @endfor
                      @for ($day = 0; $day < $visibleDays2; $day++)
                          @php
                              array_push($workingDays1, date('Y-m-d', strtotime(date('Y-m-d').'+' . $day . ' days')));
                          @endphp
                      @endfor
                      @for ($day = 0; $day < $visibleDays2; $day++)
                          @php
                              $date1 = $workingDays1[$day];
                              $date2 = date('Y-m-d', strtotime($date1.'-2 days'));
                              $dayOfWeek1 = $dayTitles[date('N', strtotime($date1.' 00:00:00'))];
                              $dateFmt1 = date('d.m.Y', strtotime($date2.' 00:00:00'));
                              $today = date('Y-m-d');

                              $openTime = 0;
                              $closeTime = -1;
                          @endphp
                          <a href="{{ route('pieraksts.print', [$office->office_id ,date('Y-m-d', strtotime($dateFmt1))]) }}">
                              @if (date('Y-m-d', strtotime($dateFmt1)) === $today)
                                  <b>{{ $office->title . ' ' . $dateFmt1 }}</b>
                              @else
                                  {{ $office->title . ' ' . $dateFmt1 }}
                              @endif
                          </a>
                      @endfor
                      @if($loop->first) <hr class="r1-hr"> @endif
                  @endforeach
              </div>
          </div>

          @if (isset($isEqual) && !$isEqual)
              <div class="apply-changes">
                  <a href="{{ route('saveTimeChanges') }}" id="save_changes"><div class="btn btn-danger">Saglabāt</div></a>
                  <a href="{{ route('cancelTimeChanges') }}" id="cancel_changes"><div class="btn btn-danger">Atcelt</div></a>
              </div>
          @endif
      </div>

  </div>
@endif
