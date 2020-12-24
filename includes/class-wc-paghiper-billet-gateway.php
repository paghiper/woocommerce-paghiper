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
		$this->icon               = apply_filters( 'woo_paghiper_icon', plugins_url( 'assets/images/boleto.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = false;
		$this->method_title       = __( 'PagHiper Boleto', 'woo-boleto-paghiper' );
		$this->method_description = __( 'Ativa a emissão e recebimento de boletos via PagHiper.', 'woo-boleto-paghiper' );

		// Carrega as configurações
		$this->init_form_fields();
		$this->init_settings();

		// Define as variáveis que vamos usar e popula com os dados de configuração
		$this->title       			= $this->get_option( 'title' );
		$this->description 			= $this->get_option( 'description' );
		$this->days_due_date 		= $this->get_option( 'days_due_date' );
		$this->skip_non_workdays	= $this->get_option( 'skip_non_workdays' );
		$this->set_status_when_waiting = $this->get_option( 'set_status_when_waiting' );

		// Ativa os logs
		$this->log = wc_paghiper_initialize_log( $this->get_option( 'debug' ) );

		// Ações
		add_action( 'woocommerce_thankyou_paghiper', array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 2 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Definimos o offset a ser utilizado para as operações de data
		$this->timezone = new DateTimeZone('America/Sao_Paulo');
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	protected function using_supported_currency() {
		return ( 'BRL' == get_woocommerce_currency() );
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
		$available = ( 'yes' == $this->get_option( 'enabled' ) ) && $this->using_supported_currency();

		return $available;
	}

}
