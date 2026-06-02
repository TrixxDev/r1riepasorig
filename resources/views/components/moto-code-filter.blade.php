<section class="facet clearfix facet--code">
  <h1 class="h6 facet-title hidden-sm-down facet-hover code-dropdown-btn"><b>Kods</b></h1>
  <div class="title hidden-md-up" data-target="#facet_code" data-toggle="collapse">
    <h1 class="h6 facet-title">Kods</h1>
    <span class="float-xs-right">
      <span class="navbar-toggler collapse-icons">
        <i class="material-icons add"></i>
        <i class="material-icons remove"></i>
      </span>
    </span>
  </div>

  @php
    $motoFilterCodeRows = [['F', 'R'], ['TL', 'WW']];
    $motoFilterCodeLabels = [
      'F' => 'Priekša',
      'R' => 'Aizmugure',
    ];
  @endphp

  <ul id="facet_code" class="collapse">
    @foreach ($motoFilterCodeRows as $row)
      <div class="row">
        @foreach ($row as $codeOption)
          @php
            $codeInputId = strtolower($codeOption);
            $codeColClass = $loop->first ? 'col-md-5' : 'col-md-7';
            $codeLabel = $motoFilterCodeLabels[$codeOption] ?? $codeOption;
          @endphp
          <div class="{{ $codeColClass }}">
            <li data-label="{{ $codeOption }}">
              <label class="facet-label" for="facet_for_{{ $codeInputId }}">
                <span class="custom-checkbox">
                  <input id="facet_for_{{ $codeInputId }}" data-search-url=""
                         @if (in_array($codeOption, $code, true)) checked="" @endif
                         value="{{ $codeOption }}"
                         data-for="prod-code"
                         data-value="{{ $codeOption }}"
                         type="checkbox">
                  <span class="ps-shown-by-js">
                    <i class="material-icons checkbox-checked"></i>
                  </span>
                </span>
                @if (isset($code_array[$codeOption]))
                  <span class="tippy lisi-tooltip" data-tippy-content="<div style='padding: 5px; text-align: left;'><span style='color: black; font-size: 15px;'>{{ $code_array[$codeOption] }}</span></div>">
                    <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">{{ $codeLabel }}</a>
                  </span>
                @else
                  <a href="javascript:;" class="_gray-darker search-link js-search-link" rel="nofollow">{{ $codeLabel }}</a>
                @endif
              </label>
            </li>
          </div>
        @endforeach
      </div>
    @endforeach
  </ul>
</section>
