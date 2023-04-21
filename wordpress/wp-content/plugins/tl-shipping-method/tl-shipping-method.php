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

          // $shipping_classes = WC()->shipping()->get_shipping_classes();
          // $cost_desc = 'Enter cost for this shipping class';


          // if (!empty($shipping_classes)) {
          //   $this->instance_form_fields['class_costs'] = array(
          //     'title'       => __('Shipping class costs', 'woocommerce'),
          //     'type'        => 'title',
          //     'default'     => '',
          //     /* translators: %s: URL for link. */
          //     'description' => sprintf(__('These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'woocommerce'), admin_url('admin.php?page=wc-settings&tab=shipping&section=classes')),
          //   );
          //   foreach ($shipping_classes as $shipping_class) {
          //     if (!isset($shipping_class->term_id)) {
          //       continue;
          //     }
          //     $this->instance_form_fields['class_cost_' . $shipping_class->term_id] = array(
          //       /* translators: %s: shipping class name */
          //       'title'             => sprintf(__('"%s" shipping class cost', 'woocommerce'), esc_html($shipping_class->name)),
          //       'type'              => 'text',
          //       'placeholder'       => __('N/A', 'woocommerce'),
          //       'description'       => $cost_desc,
          //       'default'           => $this->get_option('class_cost_' . $shipping_class->slug), // Before 2.5.0, we used slug here which caused issues with long setting names.
          //       'desc_tip'          => true,
          //       //'sanitize_callback' => array($this, 'sanitize_cost'),
          //     );
          //   }

          //   $this->instance_form_fields['no_class_cost'] = array(
          //     'title'             => __('No shipping class cost', 'woocommerce'),
          //     'type'              => 'text',
          //     'placeholder'       => __('N/A', 'woocommerce'),
          //     'description'       => $cost_desc,
          //     'default'           => '',
          //     'desc_tip'          => true,
          //     // 'sanitize_callback' => array($this, 'sanitize_cost'),
          //   );

          //   $this->instance_form_fields['type'] = array(
          //     'title'   => __('Calculation type', 'woocommerce'),
          //     'type'    => 'select',
          //     'class'   => 'wc-enhanced-select',
          //     'default' => 'class',
          //     'options' => array(
          //       'class' => __('Per class: Charge shipping for each shipping class individually', 'woocommerce'),
          //       'order' => __('Per order: Charge shipping for the most expensive shipping class', 'woocommerce'),
          //     ),
          //   );
          // }
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
            //'calc_tax' => 'per_item'
          );
          print_r($package['contents']);

          // // Register the rate
          $this->add_rate($rate);

          // $rate = array(
          //   'id'      => $this->get_rate_id(),
          //   'label'   => $this->title,
          //   'cost'    => $this->get_option('cost'),
          //   'package' => $package,
          // );

          // // Calculate the costs.
          // $has_costs = false; // True when a cost is set. False if all costs are blank strings.
          // $cost      = $this->get_option('cost');

          // if ('' !== $cost) {
          //   $has_costs    = true;
          // }

          // // Add shipping class costs.
          // $shipping_classes = WC()->shipping()->get_shipping_classes();

          // if (!empty($shipping_classes)) {
          //   $found_shipping_classes = $this->find_shipping_classes($package);
          //   $highest_class_cost     = 0;

          //   foreach ($found_shipping_classes as $shipping_class => $products) {
          //     // Also handles BW compatibility when slugs were used instead of ids.
          //     $shipping_class_term = get_term_by('slug', $shipping_class, 'product_shipping_class');
          //     $class_cost_string   = $shipping_class_term && $shipping_class_term->term_id ? $this->get_option('class_cost_' . $shipping_class_term->term_id, $this->get_option('class_cost_' . $shipping_class, '')) : $this->get_option('no_class_cost', '');

          //     if ('' === $class_cost_string) {
          //       continue;
          //     }

          //     $has_costs  = true;
          //     $class_cost = $this->evaluate_cost(
          //       $class_cost_string,
          //       array(
          //         'qty'  => array_sum(wp_list_pluck($products, 'quantity')),
          //         'cost' => array_sum(wp_list_pluck($products, 'line_total')),
          //       )
          //     );

          //     if ('class' === $this->type) {
          //       $rate['cost'] += $class_cost;
          //     } else {
          //       $highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
          //     }
          //   }

          //   if ('order' === $this->type && $highest_class_cost) {
          //     $rate['cost'] += $highest_class_cost;
          //   }
          // }

          // if ($has_costs) {
          //   $this->add_rate($rate);
          // }
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
