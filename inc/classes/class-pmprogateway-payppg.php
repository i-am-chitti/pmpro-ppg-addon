<?php
/**
 * UPI Payment Gateway
 *
 * @package upi-gateway-pmpro-addon
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

/**
 * PmProGateway_UPI Class
 *
 * Handles UPI integration.
 */
class PMProGateway_payppg extends PMProGateway {

	/**
	 * Gateway Object.
	 *
	 * @var PmProGateway
	 */
	public $gateway;

	/**
	 * Set gateway
	 *
	 * @param PMProGateway $gateway Gateway name.
	 * @return PMProGateway
	 */
	public function PMProGateway( $gateway = null ) {
		$this->gateway = $gateway;
		return $this->gateway;
	}

	/**
	 * Run on WP init
	 */
	public static function init() {
		add_filter( 'pmpro_gateways', array( 'PMProGateway_payppg', 'pmpro_gateways' ) );

		add_filter( 'pmpro_payment_options', array( 'PMProGateway_payppg', 'pmpro_payment_options' ) );

		add_filter( 'pmpro_payment_option_fields', array( 'PMProGateway_payppg', 'pmpro_payment_option_fields' ), 10, 2 );

		if ( 'payppg' === get_option( 'pmpro_gateway' ) ) {
			add_filter( 'pmpro_include_billing_address_fields', '__return_false' );
			add_filter( 'pmpro_include_payment_information_fields', '__return_false' );
			add_filter( 'pmpro_billing_show_payment_method', '__return_false' );
			add_action( 'pmpro_billing_before_submit_button', array( 'PMProGateway_payppg', 'pmpro_billing_before_submit_button' ) );
		}

		add_filter( 'pmpro_required_billing_fields', array( 'PMProGateway_payppg', 'pmpro_required_billing_fields' ) );
		add_filter( 'pmpro_checkout_before_submit_button', array( 'PMProGateway_payppg', 'pmpro_checkout_before_submit_button' ) );
		add_filter( 'pmpro_checkout_before_change_membership_level', array( 'PMProGateway_payppg', 'pmpro_checkout_before_change_membership_level' ), 10, 2 );

		add_filter( 'pmpro_gateways_with_pending_status', array( 'PMProGateway_payppg', 'pmpro_gateways_with_pending_status' ) );

		add_filter(
			'pmpro_is_spammer',
			function () {
				return false;
			},
			20
		);

		add_action( 'wp_ajax_nopriv_pmpro_payppg_handler', array( 'PMProGateway_payppg', 'wp_ajax_pmpro_payppg_handler' ) );
		add_action( 'wp_ajax_pmpro_payppg_handler', array( 'PMProGateway_payppg', 'wp_ajax_pmpro_payppg_handler' ) );
	}

	/**
	 * Make sure this gateway is in the gateways list
	 *
	 * @param array $gateways List of gateways.
	 *
	 * @since 1.8
	 */
	public static function pmpro_gateways( $gateways ) {
		if ( empty( $gateways['payppg'] ) ) {
			$gateways['payppg'] = __( 'Pay By PPG', 'pmpro-ppg' );
		}

		return $gateways;
	}

	/**
	 * Get a list of payment options that the this gateway needs/supports.
	 *
	 * @since 1.8
	 */
	public static function getGatewayOptions() {
		$options = array(
			'payppg_debug',
			'payppg_api_key',
			'currency',
			'use_ssl',
			'tax_state',
			'tax_rate',
			'payppg_proxy_url',
			'payppg_proxy_validate_url',
		);

		return $options;
	}

	/**
	 * Set payment options for payment settings page.
	 *
	 * @param array $options Array of options.
	 *
	 * @since 1.8
	 */
	public static function pmpro_payment_options( $options ) {
		// get UPI options.
		$upi_options = self::getGatewayOptions();

		// merge with others.
		$options = array_merge( $upi_options, $options );

		return $options;
	}

	/**
	 * Display fields for paytm options on settings page.
	 * Added Merchant UPI and merchant id.
	 *
	 * @param array  $values Values.
	 * @param string $gateway Gateway name.
	 */
	public static function pmpro_payment_option_fields( $values, $gateway ) {
		?>
		<tr class="pmpro_settings_divider gateway gateway_payppg" 
		<?php
		if ( 'payppg' !== $gateway ) {
			?>
			style="display: none;"<?php } ?>>
			<td colspan="2">
				<hr />
				<h2 class="title"><?php esc_html_e( 'UPI Payment Settings', 'pmpro' ); ?></h2>
			</td>
		</tr>
		<tr class="gateway gateway_payppg" 
		<?php
		if ( 'payppg' !== $gateway ) {
			?>
			style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="payppg_api_key"><?php esc_html_e( 'API Key', 'pmpro-ppg' ); ?></label>
			</th>
			<td>
				<input type="text" id="payppg_api_key" name="payppg_api_key" value="<?php echo esc_attr( $values['payppg_api_key'] ); ?>" class="regular-text code" />
			</td>
		</tr>
		<tr class="gateway gateway_payppg"
		<?php 'payppg' !== $gateway ? 'style="display: none;"' : ''; ?>>
			<th scope="row" valign="top">
				<label for="payppg_debug"><?php esc_html_e( 'Debug Mode', 'pmpro-ppg' ); ?></label>
			</th>
			<td>
				<select id="payppg_debug" name="payppg_debug">
					<option value="0" <?php selected( $values['payppg_debug'], 0 ); ?>><?php esc_html_e( 'No', 'pmpro-ppg' ); ?></option>
					<option value="1" <?php selected( $values['payppg_debug'], 1 ); ?>><?php esc_html_e( 'Yes', 'pmpro-ppg' ); ?></option>
				</select>
		</tr>
		<tr class="gateway gateway_payppg"
		<?php 'payppg' !== $gateway ? 'style="display: none;"' : ''; ?>>
			<th scope="row" valign="top">
				<label for="payppg_proxy_url"><?php esc_html_e( 'Proxy URL', 'pmpro-ppg' ); ?></label>
			</th>
			<td>
				<input type="url" id="payppg_proxy_url" name="payppg_proxy_url" value="<?php echo esc_attr( $values['payppg_proxy_url'] ); ?>" class="regular-text code" />
		</tr>
		<tr class="gateway gateway_payppg"
		<?php 'payppg' !== $gateway ? 'style="display: none;"' : ''; ?>>
			<th scope="row" valign="top">
				<label for="payppg_proxy_validate_url"><?php esc_html_e( 'Proxy Validator URL', 'pmpro-ppg' ); ?></label>
			</th>
			<td>
				<input type="url" id="payppg_proxy_validate_url" name="payppg_proxy_validate_url" value="<?php echo esc_attr( $values['payppg_proxy_validate_url'] ); ?>" class="regular-text code" />
		</tr>
		<?php
	}

	/**
	 * Show a notice on the Update Billing screen.
	 *
	 * @since 1.0.0
	 */
	public static function pmpro_billing_before_submit_button() {
		if ( apply_filters( 'pmpro_payppg_hide_update_billing_button', true ) ) {
			?>
		<script>
			jQuery(document).ready(function(){
				jQuery('.pmpro_submit').hide();
			});
		</script>
			<?php
		}
	}

	/**
	 * Remove required billing fields
	 *
	 * @param array $fields Array of fields.
	 *
	 * @since 1.8
	 */
	public static function pmpro_required_billing_fields( $fields ) {

		unset( $fields['bfirstname'] );
		unset( $fields['blastname'] );
		unset( $fields['baddress1'] );
		unset( $fields['bcity'] );
		unset( $fields['bstate'] );
		unset( $fields['bzipcode'] );
		unset( $fields['bphone'] );
		unset( $fields['bemail'] );
		unset( $fields['bcountry'] );
		unset( $fields['CardType'] );
		unset( $fields['AccountNumber'] );
		unset( $fields['ExpirationMonth'] );
		unset( $fields['ExpirationYear'] );
		unset( $fields['CVV'] );

		return $fields;
	}

	/**
	 * Show information before PMPro's checkout button.
	 *
	 * @since 1.8
	 */
	public static function pmpro_checkout_before_submit_button() {
		global $gateway, $pmpro_requirebilling;

		if ( 'payppg' !== $gateway ) {
			return;
		}

		?>
		<div id="pmpro_payppg_before_checkout" style="text-align:center;">
			<span id="pmpro_payppg_checkout" 
			<?php
			if ( 'payppg' !== $gateway || ! $pmpro_requirebilling ) {
				?>
				style="display: none;"
				<?php
			}
			?>
			>
				<input type="hidden" name="submit-checkout" value="1" />
					<?php echo '<strong>' . esc_html_e( 'NOTE:', 'pmpro-ppg' ) . '</strong> ' . esc_html_e( 'if changing a subscription it may take a minute or two to reflect.', 'pmpro-ppg' ); ?>
			</span>
		</div>
		<?php
	}

	/**
	 * Add payppg to the list of allowed gateways.
	 *
	 * @param array $gateways List of gateways.
	 *
	 * @return array
	 */
	public static function pmpro_gateways_with_pending_status( $gateways ) {
		$gateways[] = 'payppg';

		return $gateways;
	}

	/**
	 * Instead of change membership levels, send users to PayFast to pay.
	 *
	 * @param int    $user_id User ID.
	 * @param object $morder Order object.
	 *
	 * @since 1.8
	 */
	public static function pmpro_checkout_before_change_membership_level( $user_id, $morder ) {
		global $discount_code_id, $wpdb;

		if ( empty( $morder ) ) {
			return;
		}

		if ( 'payppg' !== $morder->gateway ) {
			return;
		}

		$morder->user_id = $user_id;
		$morder->saveOrder();

		// if global is empty by query is available.
		if ( empty( $discount_code_id ) && isset( $_REQUEST['discount_code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$discount_code_id = $wpdb->get_var( "SELECT id FROM $wpdb->pmpro_discount_codes WHERE code = '" . esc_sql( sanitize_text_field( wp_unslash( $_REQUEST['discount_code'] ) ) ) . "'" ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Save discount code use.
		if ( ! empty( $discount_code_id ) ) {
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO $wpdb->pmpro_discount_codes_uses 
					(code_id, user_id, order_id, timestamp) 
					VALUES( %d , %d, %d, %s )",
					$discount_code_id,
					$user_id,
					$morder->id,
					current_time( 'mysql' )
				)
			);
		}

		do_action( 'pmpro_before_send_to_payppg', $user_id, $morder );

		$morder->Gateway->send_to_pay_ppg( $morder );
	}

	/**
	 * Send to payppg
	 *
	 * @param MemberOrder $order Order object.
	 *
	 * @return void
	 */
	public function send_to_pay_ppg( &$order ) {
		if ( empty( $order->code ) ) {
			$order->code = $order->getRandomCode();
		}

		$order->payment_type     = 'payppg';
		$order->CardType         = '';
		$order->cardtype         = '';
		$order->ProfileStartDate = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );

		$amount = $order->InitialPayment;

		$api_key = get_option( 'pmpro_payppg_api_key' );

		if ( empty( $api_key ) ) {
			wp_die( 'API Key is missing' );
		}

		$payload_data = array(
			'api_key'         => $api_key,
			'order_id'        => $order->code,
			'amount'          => $amount,
			'remark1'         => 'Membership',
			'redirect_url'    => urlencode( admin_url( 'admin-ajax.php' ) . '?action=pmpro_payppg_handler&order_id=' . $order->code ),
		);

		$ppg_url = get_option( 'pmpro_payppg_proxy_url' );
		if ( empty( $ppg_url ) ) {
			wp_die( 'Proxy URL is missing' );
		}

		$final_ppg_url = add_query_arg(
			$payload_data,
			$ppg_url
		);
		wp_redirect( $final_ppg_url );
		exit;
	}

	/**
	 * Process checkout.
	 *
	 * @param MemberOrder $order Order object.
	 *
	 * @return bool
	 */
	public function process( &$order ) {
		if ( empty( $order->code ) ) {
			$order->code = $order->getRandomCode();
		}

		$order->payment_type = 'payppg';
		$order->status       = 'review';

		$order->saveOrder();
		$this->send_to_pay_ppg( $order );
		return false;
	}

	/**
	 * Send traffic to wp-admin/admin-ajax.php?action=pmpro_payppg_handler
	 *
	 * @return void
	 */
	public static function wp_ajax_pmpro_payppg_handler() {
		require_once PMPRO_PPG_PATH . '/inc/services/payppg_handler.php';
		exit;
	}
}
