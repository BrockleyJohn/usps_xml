<?php
/**
 * @version 1.0.2
 * @variant FROZEN  
 * 
  v1.0.2.add static method cfg_multi_select_unpack
  v1.0.1.check existence of function tep_get_order_status_name

  The code in this class is from
  
  CE Phoenix, E-Commerce made Easy
  https://phoenixcart.org

  Copyright (c) 2021 Phoenix Cart

  Released under the GNU General Public License

  edited for php 5.6 compatibility
*/

trait cartmart_234_compatibility { // (mostly) fake trait for easier inclusion 

  public static function cfg_multi_select_unpack($selections, $key_value, $key = '')
  // earlier core admin does not handle arrays in config vars
  {
    static $fcount = 0;
    $vals = [];
    foreach (explode(';', $key_value) as $val) {
      $vals[] = trim($val);
    }
    $return = '';
    for ($i = 0, $n = sizeof($selections); $i < $n; $i++) {
      $name = (tep_not_null($key) ? 'configuration_' . $key . '_' . $i : 'configuration_value_' . $fcount . '_' . $i );
      $class = (tep_not_null($key) ? $key : 'var_' . $fcount);
      $return .= '<input type="checkbox" name="' . $name . '" value="' . $selections[$i] . '"';
      if (is_array($vals) && in_array($selections[$i], $vals)) {
        $return .= ' checked="checked" ';
      }
      $return .= ' class="' . $class . '" /> ' . $selections[$i] . '<br />';
    }
    $name = (tep_not_null($key) ? 'configuration[' . $key . ']' : 'configuration_value');
    $return .= '<input type="hidden" name="' . $name . '" value="' . $key_value . '" />';
    $fcount++;
    if ($n > 1) {
    $return .= <<<EOS
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var checkboxes = document.querySelectorAll('input[type="checkbox"].{$class}');
    for (var i = 0; i < checkboxes.length; i++) {
      checkboxes[i].addEventListener('change', function() {
        var values = [];
        for (var i = 0; i < checkboxes.length; i++) {
          if (checkboxes[i].checked) {
            values.push(checkboxes[i].value);
          }
        }
        document.querySelector('input[name="{$name}"]').value = values.join(';');
      });
    }
  });
</script>
EOS;
    }
    return PHP_EOL . $return . PHP_EOL;

  }

}

if (! class_exists('Config')) {
  class Config {

    public static function select_geo_zone($id, $key = '') {
      return tep_cfg_pull_down_zone_classes($id, $key);
    }

    public static function select_one($select_options, $key_value, $key = '') {
      return tep_cfg_select_option($select_options, $key_value, $key);
    }

    public static function select_order_status($status_id, $key = '') {
      return tep_cfg_pull_down_order_statuses($status_id, $key);
    }
  }
}

if (! class_exists('Database')) {
  class Database {

    public function query($sql) {
      return tep_db_query($sql);
    }

    public function perform($table, $data, $action = 'insert', $parameters = '') {
      return tep_db_perform($table, $data, $action, $parameters);
    }

    public function real_escape_string($string) {
      return tep_db_input($string);
    }
  }
}

if (! isset($GLOBALS['db'])) {

  $GLOBALS['db'] = new Database();

}

if (! class_exists('order_status')) {
  class order_status {

    public static function fetch_name($id) {
      if (function_exists('tep_get_order_status_name')) {
        return tep_get_order_status_name($id);
      }
      if (mysqli_num_rows($status_query = tep_db_query("SELECT orders_status_name FROM orders_status WHERE orders_status_id = '" . (int)$id . "'"))) {
        return tep_db_fetch_array($status_query)['orders_status_name'];
      }
    }
  }
}

if (! class_exists('geo_zone')) {
  class geo_zone {

    public static function fetch_name($id) {
      return tep_get_zone_class_title($id);
    }
  }
}

if (! class_exists('Text')) {
  /*
    $Id$
  
    CE Phoenix, E-Commerce made Easy
    https://phoenixcart.org
  
    Copyright (c) 2021 Phoenix Cart
  
    Released under the GNU General Public License
  */
  
  class Text {
  
    /**
     * Break words longer than maximum.
     * @param string $s
     * @param int $maximum
     * @param string $break_marker
     */
    //public static function break( $s,  $maximum,  $break_marker = '-') { // reserved word in php5.6
    public static function breakwords( $s,  $maximum,  $break_marker = '-') {
      return array_reduce(
        explode(' ', $s),
        function ($carry, $word) use ($maximum, $break_marker) {
          return $carry . chunk_split($word, $maximum, $break_marker);
        }, '');
    }
  
    /**
     * Sanitize and normalize HTTP input.
     * @param string $s
     * @return string
     */
    public static function input( $s) {
      return trim(static::sanitize($s));
    }
  
    public static function is_empty( $s = null) {
      return is_null($s) || ('' === trim($s));
    }
  
    public static function is_prefixed_by( $s,  $prefix) {
      return (substr($s, 0, strlen($prefix)) === $prefix);
    }
  
    public static function is_suffixed_by( $s,  $suffix) {
      return (substr($s, -strlen($suffix)) === $suffix);
    }
  
    public static function ltrim_once( $s,  $prefix) {
      $length = strlen($prefix);
      if (substr($s, 0, $length) === $prefix) {
        return substr($s, $length);
      }
  
      return $s;
    }
  
    public static function output( $s, $translate = false) {
      return strtr(trim($s), $translate ?: ['"' => '&quot;']);
    }
  
    public static function prepare( $s) {
      return trim($s);
    }
  
    public static function rtrim_once( $s,  $suffix) {
      $displacement = -strlen($suffix);
      if (substr($s, $displacement) === $suffix) {
        $s = substr($s, 0, $displacement);
      }
  
      return $s;
    }
  
    public static function sanitize( $s) {
      return preg_replace(
        ['{ +}', '{[<>]}'],
        [' ', '_'],
        trim($s));
    }
  
  }
}
  
if (! class_exists('Tax')) {
  class Tax {
    public static function calculate($net, $tax) {
      return $net * $tax / 100;
    }
  }
}

if (! class_exists('Zone')) {
  class Zone {

    protected static function check_country($country_id = '') {
      $sql = " FROM zones WHERE";
      if ($country_id) {
        $sql .= " zone_country_id = " . (int)$country_id . " AND";
      }

      return "$sql zone_id = ";
    }

    public static function fetch_code($zone_id, $country_id, $default) {
      $zq = tep_db_query("SELECT zone_code" . static::check_country($country_id) . (int)$zone_id);
      if (tep_db_num_rows($zq)) {
        return tep_db_fetch_array($zq)['zone_code'];
      }

      return $default;
    }

  }
}