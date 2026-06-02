<div style="width: 70%; margin: 20px auto; font-family: Arial, Helvetica, sans-serif;">
  <div style="display: flex;">
    <div style="margin: 10px 25px;">
      R1 Riepu serviss <br>
      Kalnciema iela 39 <br>
      Rīga, LV-1046 <br>
      Tālrunis: +37167910555 <br>
      E-pasts: <a href="mailto:info@r1riepas.lv">info@r1riepas.lv</a>
    </div>
    <div style="margin-left: auto;">
      <img src="https://r1riepas.lv/public/img/r1-riepas-logo-1515661637.jpg" alt="" style="height: 100px;">
    </div>
  </div>
  <div style="background-color: lightgrey; padding: 5px;">
    <b>Pasūtījuma informācija</b>
  </div>
  <div style="margin: 10px 25px;">
    Pasūtījuma Nr. {{ $details->id }}<br>
    Pasūtījuma datums: {{ $details->created_at }}<br>
    Pasūtījuma stāvoklis: <b>Pasūtījums tiek pārbaudīts.</b> <br> <br>
    Menedžeris ar Jums sazināsies tuvākajā laikā, lai informētu par pasūtījuma gaitu.
  </div>
  <div style="background-color: lightgrey; padding: 5px;">
    <b>Pasūtītāja dati</b>
  </div>
  <div>
    <div style="margin: 10px 25px;">
      E-pasts: {{ $details->info['email'] }}<br>
      Pasūtītājs: {{ $details->info['name'] . ' ' . $details->info['surname'] }} <br>
      Tālruņa numurs: {{ $details->info['phone_number'] }}<br>
      @if (isset($details->info['company_registration_number']))
      Reģistrācijas numurs: {{ $details->info['company_registration_number'] }}<br>
      @if (isset($details->info['company_pvn_number'])) PVN Numurs: {{ $details->info['company_pvn_number'] }}<br> @endif
      Uzņēmuma nosaukums: {{ $details->info['company_name'] }}<br>
      Juridiskā adrese: {{ $details->info['company_address'] }}<br>
      @endif

      @if (isset($details->info['fitting_address']) && $details->info['fitting_address'] == 1)
	      Saņemšanas vieta: Ulbroka, Institūta iela 1
      @elseif (isset($details->info['fitting_address']) && $details->info['fitting_address'] == 2)
	      Saņemšanas vieta: Rīga, Kalnciema iela 39
      @else
        @if (isset($details->info['shipping_city']) && $details->info['shipping_city'] == 1)
          Piegādes adrese: Rīga, {{ $details->info['shipping_address'] }}@if (isset($details->info['door_code'])), Durvju kods: {{ $details->info['door_code'] }} @endif
        @elseif (isset($details->info['shipping_city']) && $details->info['shipping_city'] == 2)
          Piegādes adrese: Salaspils, {{ $details->info['shipping_address'] }}@if (isset($details->info['door_code'])), Durvju kods: {{ $details->info['door_code'] }} @endif
        @else
          Piegādes adrese: Cits, {{ $details->info['shipping_address'] }}@if (isset($details->info['door_code'])), Durvju kods: {{ $details->info['door_code'] }} @endif
        @endif
      @endif
    </div>
    <div style="background-color: lightgrey; padding: 5px;">
      <b>Pasūtītās preces</b>
    </div>
    <div style="margin: 10px 25px;">
      <table style="border-collapse: collapse;
                    border-spacing: 0;
                    width: 100%;">
        <tr>
          <th style="text-align: left;">Nosaukums</th>
          <th style="text-align: center;">Skaits</th>
          <th style="text-align: center;">Cena</th>
          <th style="text-align: center;">Summa</th>
        </tr>
        @foreach ($details->cart as $item)
        <tr>
          <td>{{ $item->options->tireObj->fullName }}</td>
          <td style="text-align: center;">{{ $item->qty }}</td>
          <td style="text-align: center;">€ {{ $item->price }}</td>
          <td style="text-align: center;">€ {{ ($item->price * $item->qty) }}</td>
        </tr>
        @endforeach
        @if ($details->fit_price > 0)
        <tr>
          <td>Montāža</td>
          <td style="text-align: center;">1</td>
          <td style="text-align: center;">€ {{ substr($details->fit_price, 0, -2) }}</td>
          <td style="text-align: center;">€ {{ substr($details->fit_price, 0, -2) }}</td>
        </tr>
        @endif
        @if ($details->delivery_price > 0)
        <tr>
          <td>Piegāde</td>
          <td style="text-align: center;">1</td>
          <td style="text-align: center;">€ {{ substr($details->delivery_price, 0, -2) }}</td>
          <td style="text-align: center;">€ {{ substr($details->delivery_price, 0, -2) }}</td>
        </tr>
	      @endif
        <tr>
          <td></td>
          <td style="text-align: center;"></td>
          <td style="text-align: center;"><b>Kopā:</b></td>
          @if ($details->delivery_price > 0)
            <td style="text-align: center;">€ {{ (int) $details->price + (int) substr($details->delivery_price, 0, 2) }}</td>
          @elseif ($details->fit_price > 0)
            <td style="text-align: center;">€ {{ (int) $details->price + (int) substr($details->fit_price, 0, 2) }}</td>
          @else
            <td style="text-align: center;">€ {{ $details->price }}</td>
          @endif
        </tr>
      </table>
    </div>
  </div>
  <div style="background-color: lightgrey; padding: 5px;">
    <b>Pasūtītāja komentāri, piezīmes</b>
  </div>
  <div style="margin: 10px 25px;">
    @if (isset($details->info['notes'])) {{ $details->info['notes'] }}<br> @endif
    @if ($details->info['car_brand'] != '') Auto marka: {{ $details->info['car_brand'] }}<br> @endif
    @if ($details->info['car_model'] != '') Auto modelis: {{ $details->info['car_model'] }}<br> @endif
    @if ($details->info['car_release_year'] != '') Izlaiduma gads: {{ $details->info['car_release_year'] }}<br> @endif
    @if ($details->info['car_engine_size'] != '') Dzinēja tilpums: {{ $details->info['car_engine_size'] }}<br> @endif
  </div>
  <div style="background-color: lightgrey; padding: 5px;">
    <b>Apmaksas informācija</b>
  </div>
  <div style="margin: 10px 25px;">
    @if ($details->payment == 1) Apmaksa saņemšanas brīdī@elseif ($details->payment == 2) Bankas pārskaitījums @else Tiešsaistes apmaksa @endif<br>
  </div>
</div>
