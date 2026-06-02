<?php

  namespace App\Models;

  use Illuminate\Database\Eloquent\Factories\HasFactory;
  use Illuminate\Database\Eloquent\Model;
  use Illuminate\Support\Facades\Auth;

  class Quadrim extends Model
  {
    use HasFactory;

    protected $primaryKey = 'rim_id';

    public $_includeStock = true;

    public function setIncludeStockAttribute($value)
    {
      return $this->_includeStock = $value;
    }

    public function getLinkAttribute()
    {
      $rim = Quadrimmake::selectRaw('quadrim_makes.*, quadrim_makes.t_title as tread_title')
        ->selectRaw('quadrim_brands.*, quadrim_brands.b_title as brand_title')
        ->leftJoin('quadrim_brands', 'quadrim_makes.brand_id', '=', 'quadrim_brands.brand_id')
        ->where('quadrim_makes.make_id', $this->make_id)
        ->first();
      if (!isset($rim->brand_title) || !isset($rim->tread_title)) {
        return false;
      } else {
        $brandSlug = \Illuminate\Support\Str::slug((string) $rim->brand_title);
        $treadSegment = strtolower(str_replace('/', '_', (string) $rim->tread_title));

        return route('kvadracikla-disks', [$brandSlug, $treadSegment, $this->rim_id]);
      }
    }

    public function getAvailableAttribute()
    {
      switch ($this->quantity) {
        case 1: {
          return 'Pēdējā';
        }
        case 2: {
          return 'Pēdējās 2';
        }
        case 3: {
          return 'Pēdējās 3';
        }
        case -1:
        case 0: {
          if ($this->_includeStock) {
            $count = $this->getStockCount();
            switch ($count){
              case 1: {
                return 'Pēdējā';
              }
              case 2: {
                return 'Pēdējās 2';
              }
              case 3: {
                return 'Pēdējās 3';
              }
              case -1:
              case 0:{
                return 'Zvaniet!';
              }
              default:{
                return 'Pieejams';
              }
            }
          } else {
            return 'Zvaniet!';
          }
        }
        default: {
          return 'Pieejams';
        }
      }
    }

    public function getDotAvailableAttribute()
    {

      if ($this->urs_quantity > 0 && $this->krs_quantity <= 0) {
        $this->quantity = $this->urs_quantity;
      } else if ($this->urs_quantity <= 0 && $this->krs_quantity > 0) {
        $this->quantity = $this->krs_quantity;
      } else if ($this->urs_quantity <= 0 && $this->krs_quantity <= 0) {
        $this->quantity = 0;
      }

      if ($this->quantity < 0 && $this->getStockCount() > 0) {
        if ($this->_includeStock) {
          $count = $this->getStockCount();
          switch ($count){
            case -1:
            case 0: {
              return 'red';
            }
            case 1:
            case 2:
            case 3: {
              return 'half-yellow';
            }
            default:{
              return 'yellow';
            }
          }
        } else {
          return 'red';
        }
      }
      switch ($this->quantity) {
        case 1:
        case 2:
        case 3: {
          return 'half-green';
        }
        case -1:
        case 0: {
          if ($this->_includeStock) {
            $count = $this->getStockCount();
            switch ($count){
              case -1:
              case 0: {
                return 'red';
              }
              case 1:
              case 2:
              case 3: {
                return 'half-yellow';
              }
              default:{
                return 'yellow';
              }
            }
          } else {
            return 'red';
          }
        }
        default: {
          return 'green';
        }
      }

    }

    public function getStockCount()
    {
      $main = max(0, (int) ($this->quantity ?? 0));
      if ($main > 0) {
        return $main;
      }

      return max(0, (int) ($this->urs_quantity ?? 0) + (int) ($this->krs_quantity ?? 0));
    }

    public function getStockAvailabilityAttribute()
    {
      $rim = Quadrim::where('rim_id', $this->rim_id)->first();
//    $stocks = Rimstock::where('tire_id', $this->tire_id)->get();

      $stock_names = [
        'i3' => 'I3',
      ];

      if ($rim->urs_quantity >= 4) {
        $availability = '<p>Ulbrokā: 4 un vairāk</p><br>';
      } else {
        $availability = '<p>Ulbrokā: ' . $rim->urs_quantity . '</p><br>';
      }
      if ($rim->krs_quantity >= 4) {
        $availability .= '<p>Kalnciema ielā: 4 un vairāk</p>';
      } else {
        $availability .= '<p>Kalnciema ielā: ' . $rim->krs_quantity . '</p>';
      }

      if (Auth::check() && Auth::user()->hasRole(['administrators', 'moderators'])) {
        $availability = '<p>Ulbrokā: ' . $rim->urs_quantity . '</p><br>';
        $availability .= '<p>Kalnciema ielā: ' . $rim->krs_quantity . '</p>';
//      foreach ($stock_names as $key => $stock_name) {
//        $stock = Autostock::where('itype', $key)->where('tire_id', $tire->tire_id)->first();
//        if ($stock && $stock->quantity > 0) {
//          $availability .= '<br><p>' . $stock_name . ': ' . $stock->quantity . '</p>';
//        } else {
//          $availability .= '<br><p>' . $stock_name . ': 0</p>';
//        }
//      }
        if ($rim->acomment !== null) {
          $availability .= '<br><hr class="admin-comments"><p><b>Piezīmes:</b> </p><br><p>' . $rim->acomment . '</p>';
        }
      } else {
        $dot = $this->getDotAvailableAttribute();
        if ($dot === 'red') {
          $availability = '<p style="text-align: center;">Nepieciešams<br>pārbaudīt pieejamību.</p>';
        } else if ($dot === 'yellow' || $dot === 'half-yellow') {
          $availability = '<p style="text-align: center;">Riepas pieejamas partneru noliktavās<br>Piegāde 1 darbadienas laikā.</p>';
        }
      }
      $availability .= '';

      return $availability;
    }

    public function getBrandTitleAttribute()
    {
      $tread = Quadrimmake::where('make_id', $this->make_id)->first();
      if (!$tread) {
        return false;
      } else {
        $brand = Quadrimbrand::where('brand_id', $tread->brand_id)->first();
      }

      return $brand->b_title;
    }

    public function getTreadTitleAttribute()
    {
      $tread = Quadrimmake::where('make_id', $this->make_id)->first();

      if ($tread) return $tread->t_title;
      return false;
    }

    function getFullNameAttribute()
    {
      return $this->getBrandTitleAttribute() . ' ' . $this->getTreadTitleAttribute() . ' ' . $this->skr . 'x' . $this->pcd . ' R' . $this->d3 . ' ' . $this->d1 . 'J et' . $this->et . ' ' . $this->color;
    }

    public function getFullTitleAttribute()
    {
      return $this->getBrandTitleAttribute() . ' ' . $this->getTreadTitleAttribute();
    }

    public function getBrandCommentAttribute()
    {
      $tread = Quadrimmake::where('make_id', $this->make_id)->first();
      $brand = Quadrimbrand::where('brand_id', $tread->brand_id)->first();

      return $brand->b_comment;
    }

    public function getTreadCommentAttribute()
    {
      $tread = Quadrimmake::where('make_id', $this->make_id)->first();

      return $tread->t_comment;
    }

    public function tread()
    {
      return $this->hasOne('App\Models\Quadrimmake', 'make_id', 'make_id');
    }

  }
