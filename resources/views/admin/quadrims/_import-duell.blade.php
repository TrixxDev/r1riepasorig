<div class="card mt-4">
  <div class="card-header">Kvadraciklu diskas — Duell imports (tab-separated)</div>
  <div class="card-body">
    <p class="text-muted small">
      Kolonnas ir provizorisks kartējums: article, brends, modelis, d1, d3, skrūvju skaits, PCD (attālums), ET,
      veikala cena, akcijas cena, daudzums (opt.), Duell kods (opt.). Kad būs Duell ATS specifikācija, lūdzu
      pieslēdziet to <code>AtvRimImportController</code>.
    </p>
    <form class="form-horizontal" action="{{ route('admin.quadrims.import.duell') }}" method="post">
      @csrf
      <textarea class="form-control" name="rows" rows="8" placeholder="Ielīmējiet rindas"></textarea>
      @if (session('out'))
        <div class="mt-3">{!! session('out') !!}</div>
      @endif
      <button type="submit" class="btn btn-primary mt-3">Importēt Duell CSV / tab</button>
    </form>
  </div>
</div>
