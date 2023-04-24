<?php

/*
Plugin Name: SportButik Shipping Method
Description: SportButik shipping method plugin
Version: 1.0.0
Author: Therese Linden
Author URI: https://github.com/thereselinden
*/

/**
 * Check if WooCommerce plugin is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

  function tl_shipping_method_init()
  {
    if (!class_exists('WC_TL_Shipping_Method')) {
      class WC_TL_Shipping_Method extends WC_Shipping_Method
      {
        public $cost;
        /**
         * Constructor for your shipping class
         *
         * @access public
         * @return void
         */
        public function __construct($instance_id = 0)
        {
          $this->id                 = 'tl_shipping_method'; // id for shipping method
          $this->instance_id        = absint($instance_id);
          $this->method_title       = __('Therese Shipping Method'); // title shown in admin
          $this->method_description = __('Description of Thereses shipping method'); // description shown in admin

          $this->supports           = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
            // 'settings', // Slår på en extra sida i shipping fliken 
          );

          $this->cost = 0;
          $this->init();
        }


        /**
         * Init your settings
         *
         * @access public
         * @return void
         */
        function init()
        {
          // Load the settings API
          $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
          $this->init_instance_form_settings();
          $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

          // user defined values goes here, not in construct 
          $this->enabled = $this->get_option('enabled');
          $this->title   = $this->get_option('title');
          $this->cost = $this->get_option('cost');
          $this->tax_status = $this->get_option('tax_status');


          // Save settings in admin if you have any defined
          add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Initialise Gateway Settings Instance Form Fields
         */
        function init_instance_form_settings()
        {
          $this->instance_form_fields = array(
            'title' => array(
              'title' => __('Shipping Title', 'woocommerce'),
              'type' => 'text',
              'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
              'default' => __('Express', 'woocommerce'),
              'desc_tip'    => true, // gives question mark with description text on hover next to title admin view
            ),
            'description' => array(
              'title' => __('Description', 'woocommerce'),
              'type' => 'textarea',
              'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
              'default' => __("Express Delivery", 'woocommerce'),
              'desc_tip' => true // gives qustion mark next to decription and hides description if not hover over questionmark
            ),
            'tax_status' => array(
              'title'   => __('Tax status', 'woocommerce'),
              'type'    => 'select',
              'class'   => 'wc-enhanced-select',
              'default' => 'taxable',
              'options' => array(
                'taxable' => __('Taxable', 'woocommerce'),
                'none'    => _x('None', 'Tax status', 'woocommerce'),
              ),
            ),
            'cost'       => array(
              'title'       => __('Cost', 'woocommerce'),
              'type'        => 'number',
              'placeholder' => 0,
              'description' => __('Optional cost for shipping method.', 'woocommerce'),
              'default'     => 0,
              'desc_tip'    => true,
            ),
          );
        } // End instance_init_form_fields()

        /**
         * calculate_shipping function.
         *
         * @access public
         * @param array $package 
         * @return void
         */
        public function calculate_shipping($package = array())
        {

          $rate = array(
            'label' => $this->title,
            'cost' =>  $this->cost,
            'package' => $package,
          );
          $this->add_rate($rate);
        }
      }
    }
  }


  add_action('woocommerce_shipping_init', 'tl_shipping_method_init');


  function add_tl_shipping_method($methods)
  {
    $methods['tl_shipping_method'] = 'WC_TL_Shipping_Method';
    return $methods;
  }

  add_filter('woocommerce_shipping_methods', 'add_tl_shipping_method');
}
