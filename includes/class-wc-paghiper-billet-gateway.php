<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woo Paghiper Boleto Methods.
 *
 * Built the Boleto method.
 */
class WC_Paghiper_Billet_Gateway extends WC_Payment_Gateway {

	private $log;
	private $timezone;

	/**
	 * Construtor do gateway. Inicializamos via __construct()
	 */
	public function __construct() {
		$this->id                 = 'paghiper_billet';
		$this->icon               = apply_filters( 'woo_paghiper_billet_icon', plugins_url( 'assets/images/billet.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = true;
		$this->method_title       = __( 'PagHiper Boleto', 'woo-boleto-paghiper' );
		$this->method_description = __( 'Ativa a emissão e recebimento de boletos via PagHiper.', 'woo-boleto-paghiper' );

		// Define as variáveis que vamos usar e popula com os dados de configuração
		$this->title       				= $this->get_option( 'title' );
		$this->description 				= $this->get_option( 'description' );

		// Carrega as configurações
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-base-gateway.php';
		$this->paghiper_gateway = new WC_Paghiper_Base_Gateway($this);

		// Carrega as configurações
		$this->form_fields = $this->paghiper_gateway->init_form_fields();
		$this->init_settings();

		// Ações
		if ( is_checkout() && !empty( is_wc_endpoint_url('order-received') ) ) {
			add_action( 'woocommerce_thankyou_paghiper', array( $this->paghiper_gateway, 'show_payment_instructions' ) );
			add_action( 'woocommerce_thankyou_paghiper_billet', array( $this->paghiper_gateway, 'show_payment_instructions' ) );
		}
		
		add_action( 'woocommerce_email_after_order_table', array( $this->paghiper_gateway, 'email_instructions' ), 10, 2 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Returns a value indicating the the Gateway is available or not. It's called
	 * automatically by WooCommerce before allowing customers to use the gateway
	 * for payment.
	 *
	 * @return bool
	 */
	public function is_available() {
		return $this->paghiper_gateway->is_available();
	}

	public function payment_fields() {
		return $this->paghiper_gateway->payment_fields();
	}

	public function validate_fields() {
		return $this->paghiper_gateway->validate_fields();
	}

	public function retrieve_order_total() {
		return $this->get_order_total();
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id, $is_frontend = true ) {
		return $this->paghiper_gateway->process_payment( $order_id, $is_frontend = true );
	}

}
