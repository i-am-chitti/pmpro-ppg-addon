<?php
/**
 * UPI Payment Gateway Service
 *
 * @package upi-gateway-pmpro-addon
 */

// Bail if PMPro or the payppg add on is not active.
if ( ! defined( 'PMPRO_DIR' ) || ! defined( 'PMPRO_PPG_PATH' ) ) {
	exit;
}

$order_id = '';
if ( isset( $_GET['order_id'] ) ) {
	$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
} else {
	error_log( 'Order ID not found.' );
	wp_die( 'Order ID not found.' );
	exit;
}

$is_payment_successful = false;
$txn_id = '';

$payment_validator_url = get_option( 'pmpro_payppg_proxy_validate_url' );
if ( empty( $payment_validator_url ) ) {
	error_log( __( 'Payment validator URL is not set.', 'pmpro-ppg' ) );
	exit;
}
$api_key = get_option( 'pmpro_payppg_api_key' );
if ( empty( $api_key ) ) {
	error_log( __( 'API key is not set.', 'pmpro-ppg' ) );
	exit;
}

$final_url = add_query_arg( [
	'order_id' => $order_id,
	'api_key'  => $api_key,
], $payment_validator_url );
$response = wp_remote_get( $final_url);
$response_data = wp_remote_retrieve_body( $response );

if ( ! empty( $response_data ) ) {
	$response_data = json_decode( $response_data, true );
	if ( ! empty( $response_data['data'] ) && ! empty( $response_data['data']['success'] ) ) {
		$is_payment_successful = true;

		if ( ! empty( $response_data['data']['txn_id'] ) ) {
			$txn_id = $response_data['data']['txn_id'];
		}
	}
}

$morder = new MemberOrder( $order_id );
$membership_level = $morder->getMembershipLevel();
$user = $morder->getUser();
if ( $is_payment_successful ) {
	// Success.
	$changed_membership = pmpro_changeMembershipLevel( $morder->membership_id, $morder->user_id, 'active' );
	if ( $changed_membership ) {
		$morder->status = 'success';
		$morder->payment_transaction_id = $txn_id;
		$morder->saveOrder();

		wp_safe_redirect( pmpro_url( 'confirmation', '?level=' . $morder->membership_id ) );
		exit;
	} else {
		$morder->status = 'error';
		$morder->saveOrder();
		wp_die( 'Failed to change membership level. Try Again.');
		exit;
	}
} else {
	// Failed.
	wp_die( 'Payment failed. Try Again from start.' );
	exit;
}