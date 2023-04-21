<?php
function tl_add_text_before_product_sorting()
{
  echo '<span>Sort:</span>';
}


function tl_change_sorting_block_for_products()
{
  remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10);
  remove_action('woocommerce_before_shop_loop', 'storefront_woocommerce_pagination', 30);
  add_action('woocommerce_before_shop_loop', 'tl_add_text_before_product_sorting', 9);
}
add_action('after_setup_theme', 'tl_change_sorting_block_for_products');



/**
 * Hide shipping rates when free shipping is available.
 * Updated to support WooCommerce 2.6 Shipping Zones.
 *
 * @param array $rates Array of rates found for the package.
 * @return array
 */
function tl_hide_shipping_when_free_is_available($rates)
{
  $free = array();

  foreach ($rates as $rate_id => $rate) {
    if ('free_shipping' === $rate->method_id) {
      $free[$rate_id] = $rate;
      break;
    }
  }
  return !empty($free) ? $free : $rates;
}
add_filter('woocommerce_package_rates', 'tl_hide_shipping_when_free_is_available', 100);
