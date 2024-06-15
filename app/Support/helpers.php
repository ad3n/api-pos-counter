<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;

/**
 * Get application name
 *
 * @return string
 */
if ( ! function_exists('appname') )
{
  function appname()
  {
    return env('APP_NAME', 'Pink Cell POS');
  }
}

/**
 * Load img src url
 *
 * @return string image src
 */
if ( ! function_exists('load_img') )
{
  function load_img($key, $file = '')
  {
    if( $file == '' )
      return '';

    $file = esc_attr($file);
    return asset("img/{$key}/$file");
  }
}

/**
 * Load img src url
 *
 * @return string image src
 */
if ( ! function_exists('render_img') )
{
  function render_img($path, $width = 120, $title = '')
  {
    if( $path == '' )
      return '';

    return '<img class="" src="'. asset("storage/{$path}") . '" title="'. $title . '" width="'. $width . '" />';
  }
}


if ( ! function_exists('rounded_img') )
{
  /**
   * Get Rounded image tag
   *
   * @return string path src
   */
  function rounded_img($path, $size = "small", $title = '')
  {
    if( $path == '' )
      return '';

    return '<img class="rounded-img ' . $size . '" src="'. asset("storage/{$path}") . '" title="'. $title . '" />';
  }
}

/**
 * LOad CSS files
 *
 * @return string css tag
 */
if ( ! function_exists('load_css') )
{
  function load_css($data = [], $echo = true)
  {
    if( empty($data) )
      return '';

    $output = '';
    foreach ($data as $item) {
      if (preg_match_all("#(^|\s|\()((http(s?)://)|(www\.))(\w+[^\s\)\<]+)#i", $item, $matches)) {
        $output .= '<link href="'. esc_attr($item)  .'" type="text/css" rel="stylesheet" media="screen,projection">' . "\n";
      } else {
        $output .= '<link href="'. asset('css/'. esc_attr($item))  .'" type="text/css" rel="stylesheet" media="screen,projection">' . "\n";
      }
    }

    if ($echo) {
      echo $output;
    } else {
      return $output;
    }
  }
}


/**
 * Load JS Files
 *
 * @return string js tag
 */
if ( ! function_exists('load_js') )
{
  function load_js($data = [], $echo = true)
  {
    if( empty($data) )
      return '';

    $output = '';
    foreach ($data as $item) {
      if (preg_match_all("#(^|\s|\()((http(s?)://)|(www\.))(\w+[^\s\)\<]+)#i", $item, $matches)) {
        $output .= '<script type="text/javascript" src="' . esc_attr($item) . '"></script>';
      } else {
        $output .= '<script type="text/javascript" src="' . asset('js/' . $item ) . '"></script>';
      }
    }

    if ($echo) {
      echo $output;
    } else {
      return $output;
    }
  }
}


if ( ! function_exists('generate_alphanumeric') )
{
  /**
   * Generate alphanumeric
   *
   * @param integer $max
   * @return string
   */
  function generate_alphanumeric( $max = 50 )
  {
    return Str::random($max);
  }
}



if ( ! function_exists('generate_number') )
{
  /**
   * Generate number
   *
   * @param integer $min
   * @param integer $max
   * @return string
   */
  function generate_number( $prefix = "#0", $min = 10000, $max = 99999 )
  {
    $res = $prefix . mt_rand($min, $max);
    return $res;
  }
}

if ( ! function_exists('generate_order_no') )
{
  /**
   * Generate number
   *
   * @param integer $min
   * @param integer $max
   * @return string
   */
  function generate_order_no()
  {
    $prefix = config('global.prefixes.transaction');
    $min = 1000000;
    $max = 9999999;
    $res = generate_number($prefix, $min, $max);
    return $res;
  }
}

if ( ! function_exists('generate_receipt_no') )
{
  /**
   * Generate number
   *
   * @param integer $min
   * @param integer $max
   * @return string
   */
  function generate_receipt_no()
  {
    $prefix = config('global.prefixes.saldo');
    $min = 1000000;
    $max = 9999999;
    $res = generate_number($prefix, $min, $max);
    return $res;
  }
}


/**
 * Fetch Conuntries from County model
 *
 * @return array
 */
if ( ! function_exists('fetch_countries') )
{
  function fetch_countries( $key = null, $string = false )
  {
    $countries = new \App\Models\Country;

    if( $string ) {
      if( ! empty($key) ) {
        return isset($countries->dropdown()[$key]) ? $countries->dropdown()[$key] : '&nbsp;';
      } else {
        return '&nbsp;';
      }
    }

    return $countries->dropdown();
  }
}

if ( ! function_exists('fetch_provinces') )
{
/**
 * Fetch Provinces from model
 *
 * @return mixed
 */
  function fetch_provinces( $key = null, $string = false )
  {
    $provinces = new \App\Models\Province;

    if( $string ) {
      if( ! empty($key) ) {
        return isset($provinces->dropdown()[$key]) ? $provinces->dropdown()[$key] : '&nbsp;';
      } else {
        return '&nbsp;';
      }
    }

    return $provinces->dropdown();
  }
}


if ( ! function_exists('fetch_regencies') )
{
  /**
   * Fetch Provinces from model
   *
   * @return mixed
   */
  function fetch_regencies( $key = null, $string = false )
  {
    $regencies = new \App\Models\Regency;

    if( $string ) {
      if( ! empty($key) ) {
        return isset($regencies->dropdown()[$key]) ? $regencies->dropdown()[$key] : '&nbsp;';
      } else {
        return '&nbsp;';
      }
    }

    return $regencies->dropdown();
  }
}


if ( ! function_exists('resolve_input') )
{
  /**
  * Resolve input value
  *
  * @return string
  */
  function resolve_input($object = null, $key, $optional_db_key = '')
  {
    if( old($key) ) {
      return old($key);
    }

    if( $object && is_object($object) ) {

      if( $optional_db_key != '' ) {

        if( method_exists($object, $optional_db_key) ) {
          return call_user_func(array($object, $optional_db_key));
        }

        if( property_exists($object, $optional_db_key) ) {
          return $object->$optional_db_key;
        }

      }

      if( isset($object->$key) ) {

        return $object->$key;

      }
    }

    return '';
  }
}

if ( ! function_exists('format_date') ) {
  /**
   * Format date
   *
   * @param string $date
   * @param string $format
   * @return string
   */
  function format_date($date, $format = 'Y-m-d')
  {
    if( empty($date) ) {
      $date = date('Y-m-d');
    }

    Carbon::setLocale( get_locale() );
    $carbondate =  Carbon::parse($date)->setTimezone(get_timezone());
    return $carbondate->format($format);
  }
}

if ( ! function_exists('format_datetime') ) {
  /**
   * Format date time
   *
   * @param string $datetime
   * @param string $format
   * @return string
   */
  function format_datetime($datetime, $format = 'Y-m-d H:i:s')
  {
    Carbon::setLocale( get_locale() );
    $carbondate =  Carbon::parse($datetime)->setTimezone(get_timezone());
    return $carbondate->format($format);
  }
}


if ( ! function_exists('display_datetime') ) {
  /**
   * Display date time
   *
   * @param string $date
   * @return string
   */
  function display_datetime($date = null) {
    if( ! $date )
      return '';

    return format_date($date, 'd F Y H:i');
  }
}


if ( ! function_exists('display_date') ) {
  /**
   * Display date time
   *
   * @param string $date
   * @return string
   */
  function display_date($date = null) {
    if( ! $date )
      return '';

    return format_date($date, 'd F Y');
  }
}

if ( ! function_exists('local_month') ) {
  /**
   * Display local month
   *
   * @param string $date
   * @return string
   */
  function local_month($date = null, $short = false) {
    Carbon::setLocale( get_locale() );
    if( $date ) {
      $carbon = Carbon::parse($date)->setTimezone(get_timezone());
    } else {
      $carbon = Carbon::now()->setTimezone(get_timezone());
    }

    if( $short ) {
      //$format = '%b %Y';
      $format = "{$carbon->shortMonthName} {$carbon->year}";
    } else {
      //$format = '%B %Y';
      $format = "{$carbon->monthName} {$carbon->year}";
    }

    return $format;
  }
}

if ( ! function_exists('local_date') ) {

  /**
   * Display local date
   *
   * @param string $date
   * @return string
   */
  function local_date($date = null, $short = false, $day_name = false) {

    Carbon::setLocale( get_locale() );

    if( $date ) {
      $carbon = Carbon::parse($date)->locale("id")->setTimezone( get_timezone() );
    } else {
      $carbon = Carbon::now()->locale("id")->setTimezone(get_timezone());
    }

    if( $short ) {
        $format = "{$carbon->day} {$carbon->shortMonthName} {$carbon->year}";
        if( $day_name ) {
            $format =  "{$carbon->shortDayName}, {$carbon->day} {$carbon->shortMonthName} {$carbon->year}";
        }
    } else {
        $format = "{$carbon->day} {$carbon->monthName} {$carbon->year}";
        if( $day_name ) {
          $format = "{$carbon->dayName}, {$carbon->day} {$carbon->monthName} {$carbon->year}";
        }
    }

    return $format;
  }
}

if ( ! function_exists('local_datetime') ) {
  /**
   * Display local date
   *
   * @param string $date
   * @return string
   */
  function local_datetime($date = null, $short = false) {

    Carbon::setLocale( get_locale() );

    if( $date ) {
      $carbon = Carbon::parse($date)->setTimezone( get_timezone() );
    } else {
      $carbon = Carbon::now()->setTimezone(get_timezone());
    }

    $format = "{$carbon->day} {$carbon->monthName} {$carbon->year} {$carbon->format('H:i')}";

    return $format;
  }
}

if ( ! function_exists('format_local_date') ) {
  /**
   * Display local date
   *
   * @param string $date
   * @return string
   */
  function format_local_date($date = null, $format = '%d %B %Y') {

    Carbon::setLocale( get_locale() );

    if( $date ) {
      $carbon = Carbon::parse($date)->setTimezone( get_timezone() );
    } else {
      $carbon = Carbon::now()->setTimezone(get_timezone());
    }

    return $carbon->isoFormat($format);
  }
}


if ( ! function_exists('diff_hours') ) {
  /**
   * Get different hourse by start date and end date
   *
   * @author Dian Afrial
   * @return string
   */
  function diff_hours($start_date = null, $end_date)
  {
      if( $start_date ) {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $start_date);
      } else {
        $start = Carbon::now();
      }

      $end = Carbon::createFromFormat('Y-m-d H:i:s', $end_date);

      return $end->diffInHours( $start );
  }
}

if ( ! function_exists('is_date_gt') ) {
  /**
   * Get different hourse by start date and end date
   *
   * @author Dian Afrial
   * @return string
   */
  function is_date_gt($start_date, $end_date = null)
  {
    $st = Carbon::parse($start_date);

    if( $end_date ) {
      $ed = Carbon::parse($end_date)->setTimezone( get_timezone() );
    } else {
      $ed = Carbon::now()->setTimezone( get_timezone() );
    }

    return $st->gt($ed);
  }
}

if ( ! function_exists('day_of_week') ) {
  /**
   * Get different hourse by start date and end date
   *
   * @author Dian Afrial
   * @return string
   */
  function day_of_week($start_date)
  {
    $dt = Carbon::parse($start_date)->setTimezone( get_timezone() );

    return $dt->dayOfWeek;
  }
}

if ( ! function_exists('next_date') ) {
  /**
   * Get next date
   *
   * @author Dian Afrial
   * @return string
   */
  function next_date($date, $days)
  {
      try {
          $date = Carbon::createFromFormat('Y-m-d H:i:s', $date)->setTimezone( get_timezone() );
      } catch( \Carbon\Exceptions\InvalidDateException $exp ) {
          $date =  Carbon::now()->setTimezone( get_timezone() );
      }

      return $date->addDays($days)->toDateTimeString();
  }
}

if ( ! function_exists('current_datetime') ) {
  /**
  * Get formatted date time from passed current date using timezone
  *
  * @return string
  */
  function current_datetime()
  {
      return Carbon::now('UTC')->format('Y-m-d H:i:s');
  }
}

if ( ! function_exists('current_date') ) {
  /**
  * Get formatted date from passed current date using timezone
  *
  * @return string
  */
  function current_date()
  {
      return Carbon::now('UTC')->format('Y-m-d');
  }
}


if( ! function_exists('time_elapsed_string') ) {

  function time_elapsed_string($datetime, $full = false)
  {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' lalu' : 'baru saja';
  }

}

if( ! function_exists('menu_activated') ) {
  /**
   * State of menu activated
   *
   * @param $menu
   * @param string $name
   * @return string
   */
  function menu_activated($menu = null, $name = '')
  {
    if( $menu ) {
      return $menu == $name ? 'active' : '';
    }

    return '';
  }
}


if( ! function_exists('selected') ) {
  /**
   * State of option selected
   *
   * @param $value
   * @param string $key
   * @return string
   */
  function selected($value = '', $key = '')
  {
    if( $value != '' ) {
      return $value == $key ? 'selected' : '';
    }

    return '';
  }
}

if( ! function_exists('checked') ) {
  /**
   * State of option checked
   *
   * @param $value
   * @param string $key
   * @return string
   */
  function checked($value = '', $key = '')
  {
    if( $value != '' ) {
      return $value == $key ? 'checked' : '';
    }

    return '';
  }
}

if( ! function_exists('request_get') )
{
  /**
   * Request get query
   *
   * @param string $key
   * @return string
   */
  function request_get($key = '')
  {
    if( $key != '' ) {
      if( Request::has($key) ) {
        return Request::query($key);
      }
    } else {
      return Request::query();
    }

    return '';
  }
}

if( ! function_exists('fetch_years_increment') ) {
  /**
   * Fetch years
   *
   * @author Dian Afrial
   * @return array
   */
  function fetch_years_increment( $date = null ) {

    $arr        = [];
    $start_year = '2018';

    if( $date ) {
      $date = Carbon::parse($date);
      $year = $date->format("Y");
      $start_year = $year;
    }

    if ( date('Y') > $start_year ) {
        $y = date('Y');
        while ($y > $start_year) {
            $arr[$y] = $y;
            $y       = $y - 1;
        }
    }

    $arr[$start_year] = $start_year;

    return $arr;
  }
}

if( ! function_exists('fetch_months') ) {
  /**
   * Fetch months
   *
   * @author Dian Afrial
   * @return array
   */
  function fetch_months($aggregator = null, $year = null, $method = null) {

    $adata = [
      '01'  => 'Januari',
      '02'  => 'Februari',
      '03'  => 'Maret',
      '04'  => 'April',
      '05'  => 'Mei',
      '06'  => 'Juni',
      '07'  => 'Juli',
      '08'  => 'Agustus',
      '09'  => 'September',
      '10'  => 'Oktober',
      '11'  => 'November',
      '12'  => 'Desember'
    ];

    if( $aggregator ) {
      $index = 1;
      return $aggregator->$method( request_get('year') ? request_get('year') : date('Y') )->mapWithKeys( function($item) use ($adata){
        if( key($adata) ) {
          return [key($adata) => current($adata) . " ( " . $item . " )" ];
          next($adata);
        }

      });
    }

    return $adata;

  }
}

if( ! function_exists('denominator') ) {
  /**
   * Denominator of currency
   */
  function denominator($value) {

    $nilai = abs($value);
    $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";

    if ($value < 12) {
      $temp = " ". $huruf[$value];
    } else if ($value <20) {
      $temp = denominator($value - 10). " belas";
    } else if ($value < 100) {
      $temp = denominator($value/10)." puluh". denominator($value % 10);
    } else if ($value < 200) {
      $temp = " seratus" . denominator($value - 100);
    } else if ($value < 1000) {
      $temp = denominator($value/100) . " ratus" . denominator($value % 100);
    } else if ($value < 2000) {
      $temp = " seribu" . denominator($value - 1000);
    } else if ($value < 1000000) {
      $temp = denominator($value/1000) . " ribu" . denominator($value % 1000);
    } else if ($value < 1000000000) {
      $temp = denominator($value/1000000) . " juta" . denominator($value % 1000000);
    } else if ($value < 1000000000000) {
      $temp = denominator($value/1000000000) . " milyar" . denominator(fmod($value,1000000000));
    } else if ($value < 1000000000000000) {
      $temp = denominator($value/1000000000000) . " trilyun" . denominator(fmod($value,1000000000000));
    }
    return $temp;

  }
}

if( ! function_exists('terbilang') ) {

  function terbilang($value) {
    if( $value < 0 ) {
			$hasil = "minus ". trim(denominator($value));
		} else {
			$hasil = trim(denominator($value));
    }

		return $hasil;
  }
}

if( ! function_exists('type_label') ) {

  function type_label($key = '') {
    if( empty($key) ) {
      return '';
    }

    return "profile.{$key}";
  }
}

if( ! function_exists('setting_label') ) {
  /**
   * Get setting label
   *
   * @param string $key
   * @param string $default
   * @return string
   */
  function setting_label($key = '', $default = '') {
    if( empty($key) ) {
      return '';
    }

    $prefix = 'settings';
    if( trans("$prefix.$key") == "$prefix.$key") {
        return $default;
    }

    return trans_choice("$prefix.$key");
  }
}

if( ! function_exists('currency') ) {
  /**
   * Undocumented function
   *
   * @return string
   */
  function currency($total)
  {
    return "Rp " . number_format($total, 2, ',', '.');
  }
}

if( ! function_exists('get_locale') ) {
  /**
   * Get locale by merchant, if no exists return default ID
   *
   * @return string
   */
  function get_locale()
  {
    $merchant = auth("employee")->user();

    if( optional($merchant)->country ) {
      return optional($merchant)->country->locale;
    }

    return 'id';
  }
}

if( ! function_exists('get_timezone') ) {
  /**
   * Get locale by merchant, if no exists return default ID
   *
   * @return string
   */
  function get_timezone()
  {
    $merchant = auth("employee")->user();

    if( optional($merchant)->country ) {
      return optional($merchant)->country->timezone;
    }

    return 'Asia/Jakarta';
  }
}

if( ! function_exists('is_image') ) {
  /**
   * Check whether is image or not
   *
   * @author Dian Afrial
   * @return boolean
   */
  function is_image($path)
  {
      $a = getimagesize($path);
      $image_type = $a[2];

      if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
      {
          return true;
      }
      return false;
  }
}


if( ! function_exists('render_avatar') ) {
  /**
   * Customer Avatar
   *
   * @return string
   */
  function render_avatar($path, $size = "small", $title = '')
  {
    if( $path ) {
      return asset("storage/$path");
    } else {
      return "data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTguMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4PSIwcHgiIHk9IjBweCIgdmlld0JveD0iMCAwIDE4OC4xNDkgMTg4LjE0OSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTg4LjE0OSAxODguMTQ5OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgd2lkdGg9IjUxMnB4IiBoZWlnaHQ9IjUxMnB4Ij4KPGc+Cgk8Zz4KCQk8ZGVmcz4KCQkJPGNpcmNsZSBpZD0iU1ZHSURfMV8iIGN4PSI5NC4wNzQiIGN5PSI5NC4wNzUiIHI9Ijk0LjA3NCIvPgoJCTwvZGVmcz4KCQk8dXNlIHhsaW5rOmhyZWY9IiNTVkdJRF8xXyIgc3R5bGU9Im92ZXJmbG93OnZpc2libGU7ZmlsbDojRTZFN0UyOyIvPgoJCTxjbGlwUGF0aCBpZD0iU1ZHSURfMl8iPgoJCQk8dXNlIHhsaW5rOmhyZWY9IiNTVkdJRF8xXyIgc3R5bGU9Im92ZXJmbG93OnZpc2libGU7Ii8+CgkJPC9jbGlwUGF0aD4KCQk8cGF0aCBzdHlsZT0iY2xpcC1wYXRoOnVybCgjU1ZHSURfMl8pO2ZpbGw6I0VDQzE5QzsiIGQ9Ik0xMjYuNzA4LDE1My45NDZoLTAuMDJjLTIuMDQxLTEuNTQ1LTQuMTc4LTIuOTE5LTYuNDI5LTQuMTU5ICAgIGMtMC4wNTgtMC4wMzgtMC4xMTUtMC4wNzYtMC4xOTEtMC4wOTVjLTEwLjY0Ni01Ljg3Ni0xNy44NTctMTcuMjA5LTE3Ljg1Ny0zMC4yMzlsLTE2LjEyMS0wLjA3NyAgICBjMCwxMy4wNjktNy4yNjksMjQuNDU5LTE4LjAxLDMwLjMxNWMwLDAtMC4wMTksMC0wLjAzOCwwLjAxOWMtMi4yNzEsMS4yNC00LjQ0NSwyLjYzMy02LjUwNiw0LjE1OSAgICBjLTEzLjM1NSw5Ljk0LTIxLjk5NywyNS44MzItMjEuOTk3LDQzLjc2NmgxMDkuMDdDMTQ4LjYxLDE3OS43NCwxNDAuMDA2LDE2My44ODUsMTI2LjcwOCwxNTMuOTQ2eiIvPgoJCTxwYXRoIHN0eWxlPSJjbGlwLXBhdGg6dXJsKCNTVkdJRF8yXyk7ZmlsbDojMTY4RUY3OyIgZD0iTTE0OC42MDksMTk3LjYyOUgzOS41MzhjMC0xNy45MzQsOC42NDItMzMuODI2LDIxLjk5Ny00My43NjYgICAgYzIuMDYxLTEuNTI2LDQuMjM1LTIuOTE5LDYuNTA1LTQuMTU5YzAuMDItMC4wMTksMC4wMzktMC4wMTksMC4wMzktMC4wMTljMS43NTUtMC45NzMsMy40MzQtMi4wOCw0Ljk3OS0zLjMzOSAgICBjNS4zNDIsNS40NzYsMTIuODAyLDguODcyLDIxLjA2Myw4Ljg3MmM4LjI0MiwwLDE1LjY4My0zLjM5NiwyMS4wMjQtOC44NTNjMS41MjYsMS4yNTksMy4xODcsMi4zNjYsNC45MjIsMy4zMiAgICBjMC4wNzYsMC4wMTksMC4xMzQsMC4wNTcsMC4xOTEsMC4wOTVjMi4yNTEsMS4yNCw0LjM4OCwyLjYxNCw2LjQyOSw0LjE1OWgwLjAyQzE0MC4wMDUsMTYzLjg3OSwxNDguNjA5LDE3OS43MzMsMTQ4LjYwOSwxOTcuNjI5eiAgICAiLz4KCQk8cGF0aCBzdHlsZT0iY2xpcC1wYXRoOnVybCgjU1ZHSURfMl8pO2ZpbGw6I0VDQzE5QzsiIGQ9Ik01Mi4yMTcsMzguMDkxdjQyLjgzNmMwLDI4Ljk3NiwyNS40MzcsNTIuNDY1LDQxLjg1OCw1Mi40NjUgICAgYzE2LjQxOSwwLDQxLjg1OC0yMy40ODksNDEuODU4LTUyLjQ2NVYzOC4wOTFINTIuMjE3eiIvPgoJCTxwYXRoIHN0eWxlPSJjbGlwLXBhdGg6dXJsKCNTVkdJRF8yXyk7ZmlsbDojNDk0ODQ2OyIgZD0iTTEyOS4xMTQsMzAuMjA3Yy05LjEyMy0xMS40MjMtMjIuOTcyLTE4LjcyNi0zOC40NjMtMTguNzI2ICAgIGMtMjcuNTIxLDAtNDkuODEsMjIuOTcyLTQ5LjgxLDUxLjMwMWMwLDE1LjAzNiw2LjI2NywyOC41NTYsMTYuMjc0LDM3LjkzMmMtMi41NzgtNi40Ny00LjAxOC0xMy43MjItNC4wMTgtMjEuMzggICAgYzAtMTIuMzA3LDMuNzQtMjMuNTc4LDkuOTU3LTMyLjI0NmM2LjU5NiwyLjkzMiwxNy4yODYsMy45OTMsMjkuMDExLDIuMzc2YzExLjYyNS0xLjU5MiwyMS41MzEtNS40MzMsMjcuMTE2LTEwLjAwNyAgICBjMTAuMTg1LDguOTk2LDE2LjgwNiwyMy41MDIsMTYuODA2LDM5Ljg3N2MwLDguMzktMS43MTksMTYuMjc1LTQuODAyLDIzLjE5OWM5LjgzLTQuMDY5LDE3LjA1OC0xOC41NzQsMTcuMDU4LTM1LjgzNSAgICBDMTQ4LjI0Myw0OC4yMjUsMTM5Ljk1NCwzMi45ODcsMTI5LjExNCwzMC4yMDd6Ii8+Cgk8L2c+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==";
    }
  }
}
