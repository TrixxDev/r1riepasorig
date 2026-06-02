<!DOCTYPE html>
<html lang="lv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rēķins Nr. {{ $pznr }}</title>
<style>
  * { box-sizing: border-box; }
  html, body { margin: 0; background: #fff; color: #000; font-family: Tahoma, Arial, sans-serif; }
  body { display: flex; justify-content: center; }
  .page { position: relative; width: 595.2pt; min-height: 842pt; background: #fff; }
  .title { position: absolute; top: 30.1pt; left: 36.8pt; font-size: 15.4pt; line-height: 15.4pt; font-weight: 700; }
  .date { position: absolute; top: 46.2pt; left: 36.8pt; font-size: 13.7pt; line-height: 13.7pt; }
  .number { position: absolute; top: 46.2pt; left: 36.7pt; width: 510pt; text-align: right; font-size: 13.7pt; line-height: 13.7pt; }
  .box { position: absolute; left: 36.7pt; width: 510pt; border: 1.4pt solid #000; }
  .parties { top: 70.8pt; height: 54.1pt; display: grid; grid-template-columns: 1fr 1fr; }
  .party { padding-left: 2.4pt; font-size: 8.2pt; line-height: 10pt; }
  .party .label { display: block; margin-bottom: 1.6pt; font-weight: 700; }
  .party .indent { display: block; padding-left: 11.9pt; }
  .terms { top: 130.7pt; height: 14.4pt; padding: 2.2pt 2.4pt 0; font-size: 8.2pt; line-height: 8.2pt; }
  .terms strong { display: inline-block; width: 136.5pt; font-weight: 700; }
  .items-wrap { position: absolute; top: 150.7pt; left: 36.7pt; width: 510pt; overflow: hidden; }
  .items { width: 100%; margin: 0; border: 0; border-collapse: collapse; table-layout: fixed; font-size: 7.7pt; line-height: 1; }
  .items tbody tr.product-row { border-left: 2px solid black; border-right: 2px solid black; }
  .items tbody tr.product-row td { border: 1px solid black; }
  .items th, .items td { height: 12.48pt; padding: 2.05pt 2.25pt 0; overflow: hidden; white-space: nowrap; vertical-align: top; font-weight: 400; }
  .items thead, .items tfoot { border: 2px solid black; }
  .items thead th { font-size: 6pt; text-align: left; border: 2px solid black; }
  .items tbody.product-row td { border-width: 0.57pt; border-style: solid; border-color: #000; border-top: 0; }
  .items tbody.product-row:last-child td { border-bottom: 0; }
  .items .right { text-align: right; font-variant-numeric: tabular-nums; }
  .items .bold { font-weight: 700; }
  .items tfoot td { font-size: 8.2pt; padding-top: 2pt; border: 2px solid black; }
  .items tfoot .qty-total { font-size: 7.3pt; }
  .totals { height: 24.4pt; padding: 2px 3px; font-size: 8.2pt; line-height: 9.8pt; }
  .totals-row { display: grid; grid-template-columns: 292.7pt 35.4pt 39.7pt 1fr; align-items: baseline; }
  .totals .label, .totals .amount { font-weight: 700; }
  .totals .amount { text-align: right; font-variant-numeric: tabular-nums; }
  .words { height: 14.4pt; padding: 3px; font-size: 8.2pt; line-height: 8.2pt; }
  .notes { height: 27.2pt; padding: 0 3px; font-size: 8.2pt; line-height: 12.4pt; }
  .notes strong { font-weight: 700; }
  @media print { @page { size: A4; margin: 0; } }
</style>
</head>
<body>
  <main class="page">
    <div class="title">RĒĶINS PRIEKŠAPMAKSAI</div>
    <div class="date">{{ $dateText }}</div>
    <div class="number">Nr. {{ $pznr }}</div>

    <section class="box parties">
      <div class="party">
        <span class="label">Piegādātājs:</span>
        <span class="indent">{{ $supplier['line1'] }}</span>
        <span class="indent">{{ $supplier['line2'] }}</span>
        <span class="indent">{{ $supplier['line3'] }}</span>
        <span class="indent">{{ $supplier['line4'] }}</span>
      </div>
      <div class="party">
        <span class="label">Maksātājs:</span>
        <span class="indent">{{ $payerLine1 }}</span>
        @if(!empty($payerLine2))
          <span class="indent">{{ $payerLine2 }}</span>
        @endif
      </div>
    </section>

    <section class="box terms">
      <strong>Samaksas noteikumi:</strong>
      <span>{{ $paymentTerms }}</span>
    </section>

    <div class="items-wrap">
      <table class="items" aria-label="Preces">
        <colgroup>
          <col style="width:4.19%">
          <col style="width:57.48%">
          <col style="width:6.66%">
          <col style="width:7.22%">
          <col style="width:8.89%">
          <col style="width:7.79%">
          <col style="width:7.77%">
        </colgroup>
        <thead>
          <tr>
            <th>Nr.</th>
            <th>Nosaukums</th>
            <th>Mērv.</th>
            <th>Daudz.</th>
            <th>Cena</th>
            <th>Cena ar PVN</th>
            <th>Summa</th>
          </tr>
        </thead>
        <tbody>
          @foreach($lines as $line)
            <tr class="product-row">
              <td>{{ $line['nr'] }}</td>
              <td>{{ $line['name'] }}</td>
              <td>{{ $line['unit'] }}</td>
              <td class="right">{{ $line['qty'] }}</td>
              <td class="right">{{ $line['price'] }}</td>
              <td class="right">{{ $line['priceWithVat'] }}</td>
              <td class="right">{{ $line['sum'] }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td class="bold" colspan="3">Kopā izsniegts</td>
            <td class="right bold qty-total">{{ $totalQty }}</td>
            <td colspan="3" class="right bold">{{ $totalSum }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <section class="box totals" style="top: {{ $totalsTop }}pt;">
      <div class="totals-row">
        <span class="label">Pievienotās vērtības nodoklis:</span>
        <span>{{ $vatRate }}%</span>
        <span>EUR</span>
        <span class="amount">{{ $vatSum }}</span>
      </div>
      <div class="totals-row">
        <span class="label">Pavisam apmaksai:</span>
        <span></span>
        <span>EUR</span>
        <span class="amount">{{ $grandTotal }}</span>
      </div>
    </section>

    <section class="box words" style="top: {{ $wordsTop }}pt;">{{ $amountWords }}</section>

    <section class="box notes" style="top: {{ $notesTop }}pt;">
      @if(!empty($notes))
        <div><strong>Piezīmes:</strong> {{ $notes }}</div>
      @endif
      <div>Šis rēķins ir sagatavots elektroniski un ir derīgs bez paraksta un zīmoga.</div>
    </section>
  </main>
</body>
</html>
