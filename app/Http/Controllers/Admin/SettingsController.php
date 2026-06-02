<?php

  namespace App\Http\Controllers\Admin;

  use App\Http\Controllers\Controller;
  use App\Models\Code;
  use App\Models\User;
  use App\Services\ComparisonFeedXmlService;
  use App\Services\SiteSeasonService;
  use Illuminate\Http\Request;
  use App\Models\Service;
  use Illuminate\Support\Facades\DB;
  use Illuminate\Support\Facades\File;
  use Illuminate\Support\Facades\Hash;
  use Illuminate\Support\Str;
  use Illuminate\Testing\Fluent\Concerns\Has;
  use Spatie\Permission\Models\Role;

  class SettingsController extends Controller
  {

    public function services()
    {
      $services = Service::orderBy('service_id', 'DESC')->get();

      return view('admin.settings.services', compact('services'));
    }

    public function services_store(Request $request)
    {
      $service = new Service;
      $service->timestamps = false;
      $service->title = $request->title;
      $service->pdf_title = $request->pdf_title;
      if ($request->f_save == 'on') $service->f_save = 1;
      if ($service->save()) {
        return json_encode(['success' => 'Pakalpojums veiksmīgi izveidots!', 'service_id' => $service->service_id, 'service_title' => $request->title]);
      }

    }

    public function services_enable(Request $request, $id)
    {
      $service = Service::where('service_id', $id)->first();
      $service->timestamps = false;
      $service->enabled = $request->enabled;
      if (!$service->save()) {
        echo json_encode(['error' => 'Notika kļūda']);
      }
    }

    public function services_active(Request $request, $id)
    {
      $service = Service::where('service_id', $id)->first();
      $service->timestamps = false;
      if ($service->f_ac !== NULL) {
        $service->f_ac = $request->enabled;
      }
      if ($service->f_moto !== NULL) {
        $service->f_moto = $request->enabled;
      }
      if (!$service->save()) {
        echo json_encode(['error' => 'Notika kļūda']);
      }
    }

    public function services_edit(Request $request, $id)
    {
      $service = Service::find($id);
      $service->timestamps = false;
      $service->title = $request->title;
      $service->pdf_title = $request->pdf_title;
      $service->f_save = (isset($request->f_save)) ? 1 : null;
      if ($service->save()) {
        echo json_encode(['success' => 'Informācija veiksmīgi izlabota', 'f_save' => $service->f_save]);
      } else {
        echo json_encode(['error' => 'Notika kļūda']);
      }
    }

    public function services_destroy($id)
    {
      $service = Service::findOrFail($id);
      $service->delete();
      return redirect(route('admin.settings.services'))->with('success', 'Pakalpojums veiksmīgi dzēsts!');
    }

    // Administratori

    public function users()
    {

      $users = User::all();

      return view('admin.settings.users', compact('users'));
    }

    public function users_create()
    {
      $roles = Role::all();

      return view('admin.settings.users_create', compact('roles'));
    }

    public function users_store(Request $request)
    {

      $user = new User;
      $user->name = strip_tags($request->name);
      $user->surname = strip_tags($request->surname);
      $user->username = strip_tags($request->username);
      $user->password = Hash::make($request->password);
      $user->email = strip_tags($request->email);

      foreach ($request->status as $role) {
        $user->assignRole($role);
      }

      $user->save();

//      dd(Hash::make($request->password));

      return redirect(route('admin.settings.users'))->withSuccess('Lietotājs veiksmīgi izveidots!');

    }

    public function users_edit($id, Request $request)
    {
      $user = User::findOrFail($id);
      $roles = Role::all();

      return view('admin.settings.users_edit', compact('user', 'roles'));
    }

    public function users_update(Request $request, $id)
    {

      $user = User::findOrFail($id);
      $user->name = strip_tags($request->name);
      $user->surname = strip_tags($request->surname);
      $user->username = strip_tags($request->username);
      $user->password = Hash::make($request->password);
      $user->email = strip_tags($request->email);

      foreach ($request->status as $role) {
        $user->assignRole($role);
      }

      $user->save();

//      dd(Hash::make($request->password));

      return redirect(route('admin.settings.users'))->withSuccess('Lietotājs veiksmīgi izlabots!');

    }

    public function user_pwdChange($user, Request $request)
    {
      if ($request->post()) {
        if ($request->password == $request->password_again) {
          $user = User::findOrFail($user);
          $user->password = Hash::make($request->password);
          $user->save();
          return redirect()->back()->with('success', 'Parole veiksmīgi nomainīta!');
        } else {
          return redirect()->back()->with('danger', 'Paroles nesakrīt');
        }
      }

      return view('admin.settings.pwdChange');
    }

    public function users_destroy($id)
    {
      $user = User::findOrFail($id);
      $user->delete();

      return redirect(route('admin.settings.users'))->withSuccess('Lietotājs veiksmīgi dzēsts!');
    }

    public function users_stateChange($id)
    {
      $user = User::findOrFail($id);
      $user->enabled = ($user->enabled == 1) ? 0 : 1;
      $user->save();

      if ($user->enabled == 1) {
        return redirect(route('admin.settings.users'))->withSuccess('Lietotājs aktivizēts!');
      } else {
        return redirect(route('admin.settings.users'))->withSuccess('Lietotājs deaktivizēts!');
      }
    }

    // Lapas

    public function pages()
    {
      $pages = DB::table('pages')->get();

      return view('admin.settings.pages', compact('pages'));
    }

    public function pages_create()
    {
      return view('admin.settings.pages_create');
    }

    public function pages_store(Request $request)
    {

      $title = $request->name;
      $slug = Str::slug($title);

      $file = $slug . '.blade.php';

      DB::table('pages')->insert([
        'title' => $title,
        'route' => $slug,
      ]);

      $html = "@extends('layouts.app')

@section('content')
<div class='container'>
    <div class='row'>
        <div class='main-content clearfix col-md-12 col-xl-10'>
            <div id='content-wrapper' class='right-column col-lg-12'>
                <section id='main'>
                    <header class='page-header'>
                        <h1>
                            $title
                        </h1>
                    </header>
                    <section id='content' class='page-content page-cms'>

                        @include('pages.components.$slug')

                    </section>
                    <footer class='page-footer'>
                        <!-- Footer content -->
                    </footer>
                </section>
                <script>
                    $('section#content').find('table').each(function() {
                        if ($(this).parent().is('div')) {
                            $(this).parent().addClass('pak-table');
                        }
                    })
                </script>
            </div>
        </div>
        @include('components.right-sidebar')
    </div>
</div>

@endsection";

      File::put(dirname(__DIR__, 4) . '/resources/views/pages/' . $file, $html);
      File::put(dirname(__DIR__, 4) . '/resources/views/pages/components/' . $file, '');

      return redirect(route('admin.settings.pages'))->withSuccess('Lapa veiksmīgi izveidota!');
    }

    public function pages_edit($id)
    {
      $page = DB::table('pages')->where('id', $id)->first();

      $file = $page->route . '.blade.php';

      $component_file = dirname(__DIR__, 4) . '/resources/views/pages/components/' . $file;

      if (File::exists($component_file)) {
        $doc = File::get($component_file);
      } else {
        $doc = '';
      }

      return view('admin.settings.pages_edit', compact('page', 'doc'));
    }

    public function pages_update(Request $request, $id)
    {
      $page = DB::table('pages')->where('id', $id)->first();

      $title = $request->name;
      $file = $page->route . '.blade.php';
      $slug = $page->route;

      if ($request->uri && !empty($request->uri)) {
        if ($page->route !== $request->uri) {
          $file = $request->uri . '.blade.php';
          $slug = Str::slug($request->uri);

          File::delete(dirname(__DIR__, 4) . '/resources/views/pages/' . $page->route . '.blade.php');
          File::delete(dirname(__DIR__, 4) . '/resources/views/pages/components/' . $page->route . '.blade.php');

          $html = "@extends('layouts.app')

@section('content')
<div class='container'>
    <div class='row'>
        <div class='main-content clearfix col-md-12 col-xl-10'>
            <div id='content-wrapper' class='right-column col-lg-12'>
                <section id='main'>
                    <header class='page-header'>
                        <h1>
                            $title
                        </h1>
                    </header>
                    <section id='content' class='page-content page-cms'>

                        @include('pages.components.$slug')

                    </section>
                    <footer class='page-footer'>
                        <!-- Footer content -->
                    </footer>
                </section>
                <script>
                    $('section#content').find('table').each(function() {
                        if ($(this).parent().is('div')) {
                            $(this).parent().addClass('pak-table');
                        }
                    })
                </script>
            </div>
        </div>
        @include('components.right-sidebar')
    </div>
</div>

@endsection";

          File::put(dirname(__DIR__, 4) . '/resources/views/pages/' . $file, $html);
          File::put(dirname(__DIR__, 4) . '/resources/views/pages/components/' . $file, html_entity_decode($request->page_content));
          DB::table('pages')->where('id', $id)->update(['route' => $request->uri]);
        }
      }

      $component_file = dirname(__DIR__, 4) . '/resources/views/pages/components/' . $file;

      $content = html_entity_decode($request->page_content);

      if (File::exists($component_file)) {
        File::delete($component_file);
        File::put($component_file, $content);
      } else {
        return back()->withError('Tāds fails neeksistē - ' . $component_file);
      }

      return redirect()->back()->withSuccess('Lapa veiksmīgi labota!');
    }

    public function pages_destroy($id)
    {
      $page = DB::table('pages')->where('id', $id)->first();

      $file = $page->route . '.blade.php';

      $view_file = dirname(__DIR__, 4) . '/resources/views/pages/' . $file;
      $component_file = dirname(__DIR__, 4) . '/resources/views/pages/components/' . $file;

      if (File::exists($view_file)) {
        File::delete($view_file);
      } else {
        return back()->withError('Tāds fails neeksistē - ' . $view_file);
      }

      if (File::exists($component_file)) {
        File::delete($component_file);
      } else {
        return back()->withError('Tāds fails neeksistē - ' . $component_file);
      }
      DB::table('pages')->where('id', $id)->delete();

      return redirect(route('admin.settings.pages'))->withSuccess('Lapa veiksmīgi dzēsta!');
    }

    // Salidzini.lv / Kurpirkt.lv XML Ģenerācijas

    public function salidzini(Request $request, ComparisonFeedXmlService $feeds)
    {
      return $feeds->serveSalidzini($request);
    }

    public function kurpirkt(Request $request, ComparisonFeedXmlService $feeds)
    {
      return $feeds->serveKurpirkt($request);
    }

    // Sinhronizācijas

    public function syncs()
    {

      $accrual_last_time = DB::table('sync_times')->where('name', 'accrual')->first()->updated_at;
      $i3_auto = DB::table('sync_times')->where('name', 'i3-auto')->first()->updated_at;
      $i3_alloy_rims = DB::table('sync_times')->where('name', 'i3-alloy-rims')->first()->updated_at;
      $gy_auto = DB::table('sync_times')->where('name', 'gy-auto')->first()->updated_at;
      $rz_auto = DB::table('sync_times')->where('name', 'rz-auto')->first()->updated_at;
      $rg_auto = optional(DB::table('sync_times')->where('name', 'rg-auto')->first())->updated_at ?? '—';
      $i3_moto = DB::table('sync_times')->where('name', 'i3-moto')->first()->updated_at;
      $duell_moto = DB::table('sync_times')->where('name', 'duell-moto')->first()->updated_at;
      $i3_quadr = DB::table('sync_times')->where('name', 'i3-quadr')->first()->updated_at;
      $duell_quadr = DB::table('sync_times')->where('name', 'duell-quadr')->first()->updated_at;
      $i3_big = DB::table('sync_times')->where('name', 'i3-big')->first()->updated_at;
      $i3_agro = DB::table('sync_times')->where('name', 'i3-agro')->first()->updated_at;
      $starco_big = DB::table('sync_times')->where('name', 'starco-big')->first()->updated_at;

      return view('admin.settings.syncs',
              compact('accrual_last_time',
                'i3_auto',
                'i3_alloy_rims',
                'gy_auto',
                'rz_auto',
                'rg_auto',
                'i3_moto',
                'duell_moto',
                'i3_quadr',
                'duell_quadr',
                'i3_big',
                'i3_agro',
                'starco_big'
              )
            );
    }

    public function codes()
    {
      $codes = Code::all();

      return view('admin.settings.codes', compact('codes'));
    }

    public function codes_create()
    {
      return view('admin.settings.codes_create');
    }

    public function codes_store(Request $request)
    {
      $request->validate([
        'name' => 'required|max:50',
        'explanation' => 'required|max:150',
      ]);

      $code = new Code;
      $code->name = $request->name;
      $code->explanation = $request->explanation;
      $code->save();

      return redirect()->route('admin.settings.codes')
                       ->with('success', 'Kods ' . $code->name . ' pievienots veiksmīgi!');

    }

    public function codes_edit($id)
    {
      $code = Code::findOrFail($id);

      return view('admin.settings.codes_edit',compact('code'));
    }

    public function codes_update(Request $request, $code)
    {
      $request->validate([
        'name' => 'required',
        'explanation' => 'required',
      ]);

      $code = Code::findOrFail($code);
      $code->name = $request->name;
      $code->explanation = $request->explanation;
      $code->save();

      //$code->update($request->all());

      return redirect()
        ->route('admin.settings.codes')
        ->withSuccess('Kods tika veiksmīgi labots!');
    }

    public function codes_destroy($id)
    {
      $code = Code::findOrFail($id);
      $code->delete();

      return redirect()->route('admin.settings.codes')
        ->with('success','Kods - ' . $code->name . ' tika veiksmīgi dzēsts!');

    }

    // Cenas

    public function prices() {

      $prices = DB::table('cart_config')->get();

      return view('admin.settings.prices', compact('prices'));

    }

    public function price_update(Request $request, $id) {

      $price = DB::table('cart_config')->where('id', $id)->first();

      $update = DB::table('cart_config')->where('id', $id)->update([
						'name' => $request->inputs['name'],
						'abbr' => $request->inputs['text'],
						'value' => $request->inputs['price']
						]);

	//dd($update, $request->inputs);

      if ($update == 1) {
	return json_encode(['success' => 'Cena veiksmīgi izlabota!']);
      } elseif ($update == 0 && $price->name == $request->inputs['name'] && $price->abbr == $request->inputs['text'] && $price->value == $request->inputs['price']) {
	return json_encode(['warning' => 'Nav labojumu!']);
      } else {
	return json_encode(['error' => 'Notika kļūda']);
      }

    }

    public function changeSeason(Request $request, SiteSeasonService $siteSeasonService)
    {
      $validated = $request->validate([
        'season' => 'required|in:1,2',
      ]);

      $siteSeasonService->set((int) $validated['season']);

      return response()->json(['success' => true]);
    }

}
