<?php
/**
 * Plugin Name: UPI Gateway for Paid Memberships Pro
 * Description: UPI Gateway for Paid Memberships Pro plugin
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: pmpro-upi
 *
 * @package paytm-gateway-pmpro-addon
 */

define( 'PMPRO_UPIGATEWAY_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PMPRO_UPIGATEWAY_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// load payment gateway class.
require_once PMPRO_UPIGATEWAY_PATH . '/inc/classes/class-pmprogateway-upi.php';
