<?php
/**
 * UPI Payment Gateway
 *
 * @package upi-gateway-pmpro-addon
 */

/**
 * Bootstrap function.
 *
 * @return void
 */
function bootstrap() {
	add_action( 'init', array( 'PMProGateway_payppg', 'init' ) );
}

// Kick off.
bootstrap();
