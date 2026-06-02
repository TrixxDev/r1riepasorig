<?php

namespace App\Http\Livewire\Auto;

use Livewire\Component;
use App\Models\Autotire;
use App\Helper\Tires;
use DB;

class VasarasRiepas extends Component
{

    public $tire_brand;
    public $d1;
    public $d2;
    public $d3;
    public $tires;

    public function mount() {
        $this->tire_brand = 'Visi';
        $this->d1 = 205;
        $this->d2 = 55;
        $this->d3 = 16;
        $this->tires = Autotire::all();
    }

    public function changeBrand($value)
    {
        if ($value == '') {
            $this->tire_brand = 'Visi';
        } else {
            $this->tire_brand = $value;
        }
    }

    public function changeD1($value)
    {
        if ($value == '') {
            $this->d1 = 'Visi';
        } else {
            $this->d1 = $value;
        }
    }

    public function changeD2($value)
    {
        if ($value == '') {
            $this->d2 = 'Visi';
        } else {
            $this->d2 = $value;
        }
    }

    public function changeD3($value)
    {
        $this->d3 = $value;
    }

    public function search()
    {
        ($this->tire_brand == 'Visi') ? $brand = 'Visi' : $brand = $this->tire_brand;
        ($this->d1 == 'Visi') ? $d1 = '' : $d1 = $this->d1;
        ($this->d2 == 'Visi') ? $d2 = '' : $d2 = $this->d2;
        $d3 = $this->d3;
        $sql = DB::table('auto_treads')->selectRaw('auto_treads.*, auto_treads.title as tread_title')
                                            ->selectRaw('auto_brands.*, auto_brands.title as brand_title')
                                            ->leftJoin('auto_brands', 'auto_treads.brand_id', '=', 'auto_brands.brand_id')
                                            ->where('auto_brands.title', $brand)
                                            ->get();
        $makes = [];
        foreach ($sql as $make) {
            $makes[] = $make->tread_id;
        }
        $this->tires = Autotire::when($makes, function($query) use ($makes) {
            $query->whereIn('make_id', $makes);
        })->when($d1, function($query) use ($d1) {
            $query->where('make_id', $d1);
        })->when($d2, function($query) use ($d2) {
            $query->where('make_id', $d2);
        })->where('d3', $d3)
            ->orderBy('price2', 'DESC')
            ->get();
    }

    public function render()
    {
        $tires = $this->tires;

        $brands = Tires::getAllAutoBrands();

        $autoTiresD1 = Tires::getAutoTiresD1();
        $autoTiresD2 = Tires::getAutoTiresD2();
        $autoTiresD3 = Tires::getAutoTiresD3();

        $currBrand = $this->tire_brand;
        $d1 = $this->d1;
        $d2 = $this->d2;
        $d3 = $this->d3;

        return view('livewire.auto.vasaras-riepas',
                    compact('currBrand', 'd1','d2', 'd3', 'tires', 'brands', 'autoTiresD1', 'autoTiresD2', 'autoTiresD3')
               );
    }
}
