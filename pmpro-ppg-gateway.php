<?php
/**
 * Plugin Name: PP Gateway for Paid Memberships Pro
 * Description: PP Gateway for Paid Memberships Pro plugin
 * Version: 1.0.0
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: pmpro-ppg
 *
 * @package ppg-pmpro-addon
 */

define( 'PMPRO_PPG_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PMPRO_PPG_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

// load payment gateway class.
require_once PMPRO_PPG_PATH . '/inc/plugin.php';

// Require the default PMPro Gateway Class.
require_once PMPRO_DIR . '/classes/gateways/class.pmprogateway.php';

require_once PMPRO_PPG_PATH . '/inc/classes/class-pmprogateway-payppg.php';
