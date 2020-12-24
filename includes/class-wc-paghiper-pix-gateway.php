<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woo Paghiper Boleto Methods.
 *
 * Built the Boleto method.
 */
class WC_Paghiper_Pix_Gateway extends WC_Payment_Gateway {

	private $log;
	private $timezone;

	/**
	 * Construtor do gateway. Inicializamos via __construct()
	 */
	public function __construct() {
		$this->id                 = 'paghiper_pix';
		$this->icon               = apply_filters( 'woo_paghiper_icon', plugins_url( 'assets/images/boleto.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = false;
		$this->method_title       = __( 'PagHiper PIX', 'woo-boleto-paghiper' );
		$this->method_description = __( 'Receba pagamentos por PIX usando PagHiper.', 'woo-boleto-paghiper' );

		require_once('class-wc-paghiper-base-gateway.php');
		$paghiper_gateway = new WC_Paghiper_Base_Gateway($this);

		// Carrega as configurações
		$this->form_fields = $paghiper_gateway->init_form_fields();
		$this->init_settings();

		// Ações
		add_action( 'woocommerce_thankyou_paghiper', array( $paghiper_gateway, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $paghiper_gateway, 'email_instructions' ), 10, 2 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $paghiper_gateway, 'process_admin_options' ) );
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		// Test if is valid for use.
		$available = ( 'yes' == $this->get_option( 'enabled' ) ) && $paghiper_gateway->using_supported_currency();

		return $available;
	}

}
