<?php
/**
 * UPI Payment Gateway
 *
 * @package upi-gateway-pmpro-addon
 */

/**
 * PmProGateway_UPI Class
 *
 * Handles UPI integration.
 */
class PMProGateway_UPI extends PMProGateway {

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
		add_filter( 'pmpro_gateways', array( 'PMProGateway_UPI', 'pmpro_gateways' ) );

		add_filter( 'pmpro_payment_options', array( 'PMProGateway_UPI', 'pmpro_payment_options' ) );

		add_filter( 'pmpro_payment_option_fields', array( 'PMProGateway_UPI', 'pmpro_payment_option_fields' ), 10, 2 );
	}

	/**
	 * Make sure this gateway is in the gateways list
	 *
	 * @param array $gateways List of gateways.
	 *
	 * @since 1.8
	 */
	public static function pmpro_gateways( $gateways ) {
		if ( empty( $gateways['payupi'] ) ) {
			$gateways['payupi'] = __( 'Pay By UPI', 'pmpro-upi' );
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
			'payupi_debug',
			'payupi_api_key',
			'currency',
			'use_ssl',
			'tax_state',
			'tax_rate',
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
		<tr class="pmpro_settings_divider gateway gateway_payupi" 
		<?php
		if ( 'payupi' !== $gateway ) {
			?>
			style="display: none;"<?php } ?>>
			<td colspan="2">
				<hr />
				<h2 class="title"><?php esc_html_e( 'UPI Payment Settings', 'pmpro' ); ?></h2>
			</td>
		</tr>
		<tr class="gateway gateway_payupi" 
		<?php
		if ( 'payupi' !== $gateway ) {
			?>
			style="display: none;"<?php } ?>>
			<th scope="row" valign="top">
				<label for="payupi_api_key"><?php esc_html_e( 'PayUPI API Key', 'pmpro-upi' ); ?></label>
			</th>
			<td>
				<input type="text" id="payupi_api_key" name="payupi_api_key" value="<?php echo esc_attr( $values['payupi_api_key'] ); ?>" class="regular-text code" />
			</td>
		</tr>
		<?php
	}
}
