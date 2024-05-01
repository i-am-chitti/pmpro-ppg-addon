<?php
/**
 * UPI Payment Gateway
 *
 * @package upi-gateway-pmpro-addon
 */

require_once PMPRO_UPIGATEWAY_PATH . '/inc/classes/class-pmprogateway-upi.php';

/**
 * Bootstrap function.
 *
 * @return void
 */
function bootstrap() {
	add_action( 'init', array( 'PMProGateway_UPI', 'init' ) );
}

// Kick off.
bootstrap();
