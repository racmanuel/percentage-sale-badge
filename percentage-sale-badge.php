<?php
/**
 * Plugin Name:       Percentage to Sale Badge - WooCommerce
 * Description:       Change the default sale badge to a percentage of sale, works perfect with Hello Theme, Storefront.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Manuel Ramirez Coronel
 * Author URI:        https://github.com/racmanuel
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       percentage-to-sale-badge
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Percentage_Sale_Badge')) {
    class Percentage_Sale_Badge
    {

        public function __construct()
        {

        }

        public function init()
        {
            //Call the i18n function
            add_action('init', array($this, 'percentage_sale_badge_i18n'));
            //Add the WoooCommerce Percentage
            add_filter('woocommerce_sale_flash', array($this, 'percentage_sale_badge_filter'));
        }

        public function percentage_sale_badge_i18n()
        {
            //Load the plugin languages
            load_plugin_textdomain('percentage-to-sale-badge', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }

        public function percentage_sale_badge_filter()
        {
            global $product;
            global $html;
            global $post;

            if ($product->is_type('variable')) {
                $percentages = array();

                // Get all variation prices
                $prices = $product->get_variation_prices();

                // Loop through variation prices
                foreach ($prices['price'] as $key => $price) {
                    // Only on sale variations
                    if ($prices['regular_price'][$key] !== $price) {
                        // Calculate and set in the array the percentage for each variation on sale
                        $percentages[] = round(100 - (floatval($prices['sale_price'][$key]) / floatval($prices['regular_price'][$key]) * 100));
                    }
                }
                // We keep the highest value
                $percentage = max($percentages) . '%';

            } elseif ($product->is_type('grouped')) {
                $percentages = array();

                // Get all variation prices
                $children_ids = $product->get_children();

                // Loop through variation prices
                foreach ($children_ids as $child_id) {
                    $child_product = wc_get_product($child_id);

                    $regular_price = (float) $child_product->get_regular_price();
                    $sale_price = (float) $child_product->get_sale_price();

                    if ($sale_price != 0 || !empty($sale_price)) {
                        // Calculate and set in the array the percentage for each child on sale
                        $percentages[] = round(100 - ($sale_price / $regular_price * 100));
                    }
                }
                // We keep the highest value
                $percentage = max($percentages) . '%';

            } else {
                $regular_price = (float) $product->get_regular_price();
                $sale_price = (float) $product->get_sale_price();

                if ($sale_price != 0 || !empty($sale_price)) {
                    $percentage = round(100 - ($sale_price / $regular_price * 100)) . '%';
                } else {
                    return $html;
                }
            }
            return '<span class="onsale">' . esc_html__('Sale', 'percentage-to-sale-badge') . ' ' . $percentage . '</span>';
        }
    }
    $plugin = new Percentage_Sale_Badge();
    $plugin->init();
}
