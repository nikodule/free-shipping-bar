<?php
/**
 * Plugin Name: Remaining Amount For Free Shipping
 * Plugin URI: 
 * Description: An addon plugin for the mini-cart, cart, and checkout page that informs the left total amount for free shipping.
 * Version: 1.0.0
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Author: Niko Ntoule
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pdev
 * Domain Path: /public/lang
 */

function goya_custom_bar_free_shipping()
{
    $current = WC()->cart->get_cart_contents_total();
    $packages = WC()->cart->get_shipping_packages();
    $package = reset($packages);
    $zone = wc_get_shipping_zone($package);
    $limit = 0;

    foreach ($zone->get_shipping_methods(true) as $method) {
        if ('free_shipping' === $method->id) {
            $limit = $method->get_option('min_amount');
        }
    }

    // If the cart is empty
    if ($current <= 0) {
        $notice = "<span class='minicart-message'>Προσθέστε προϊόντα αξίας 30€ για να λάβετε Δωρεάν Μεταφορικά!</span>";
        wc_print_notice($notice, 'notice');
    } elseif ($current < $limit) {
        // If there is a remaining amount for free shipping
        $remaining = $limit - $current;
        $notice = "<span class='minicart-message'> Υπολείπονται " . wc_price($remaining, array('decimals' => 2)) . " για να λάβετε <strong>Δωρεάν Μεταφορικά!</span>";
        wc_print_notice($notice, 'notice');
    } else {
        // If free shipping is available
        $notice = "<span class='minicart-message'>Συμπληρώσατε το ποσό για Δωρεάν Μεταφορικά!</span>";
        wc_print_notice($notice, 'success');
    }
}

add_action('woocommerce_before_cart_contents', 'goya_custom_bar_free_shipping');
add_action('woocommerce_after_mini_cart', 'goya_custom_bar_free_shipping');

add_filter('woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 999);

function my_hide_shipping_when_free_is_available($rates)
{
    $free_shipping_rates = array();

    // Loop through all shipping rates
    foreach ($rates as $rate_id => $rate) {
        // Check if the method ID contains 'free_shipping'
        if (strpos($rate->method_id, 'free_shipping') !== false) {
            $free_shipping_rates[$rate_id] = $rate;
            break; // Exit loop once free shipping is found
        }
    }

    // Return only free shipping rates if available, otherwise return all rates
    return !empty($free_shipping_rates) ? $free_shipping_rates : $rates;
}