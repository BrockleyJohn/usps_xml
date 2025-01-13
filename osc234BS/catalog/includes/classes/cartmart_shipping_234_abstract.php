<?php
/*
  (most of) The code in this class is from
  
  CE Phoenix, E-Commerce made Easy
  https://phoenixcart.org

  Copyright (c) 2021 Phoenix Cart

  Released under the GNU General Public License
*/

require_once __DIR__ . '/cartmart_234_abstract_module.php';

abstract class cartmart_shipping_234_abstract extends cartmart_234_abstract_module { // abstract class to replace phoenix hierarchy

  public $tax_class;
  protected $icon = '';
  public $quotes;
  protected $country;

  public function __construct() {
    parent::__construct();

    $this->tax_class = $this->base_constant('TAX_CLASS') ?? 0;
  }

  public function update_status() {
    if ($this->enabled && isset($GLOBALS['order']->delivery['country']['id'])) {
      $this->update_status_by($GLOBALS['order']->delivery);
    }
  }

  public function update_status_by($address) {
    if (!$this->enabled) {
      return;
    }

    $geo_zone_id = !is_null($this->base_constant('ZONE')) ? $this->base_constant('ZONE') : 0;
    if (0 >= (int)$geo_zone_id) {
      return;
    }

    if (!isset($address['zone_id'])) {
      $this->enabled = false;
      return;
    }

    if (isset($address['country']['id'])) {
      $check_query = tep_db_query("SELECT zone_id FROM zones_to_geo_zones WHERE geo_zone_id = " . (int)$geo_zone_id . " AND zone_country_id = " . (int)$address['country']['id'] . " ORDER BY zone_id");
      while ($check = tep_db_fetch_array($check_query)) {
        if (($check['zone_id'] < 1) || ($check['zone_id'] == $address['zone_id'])) {
          return;
        }
      }

      $this->enabled = false;
    }
  }

  public function quote_common() {
    global $order;

    if ($this->tax_class > 0) {
      $this->quotes['tax'] = Tax::get_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
    }

    if (!Text::is_empty($this->icon) && ('True' === ($this->base_constant('DISPLAY_ICON') ?? 'True'))) {
      $this->quotes['icon'] = new Image($this->icon, [], htmlspecialchars($this->title));
    }
  }

  public function calculate_handling() {
    return (float)($this->base_constant('HANDLING') ?? 0);
  }

}