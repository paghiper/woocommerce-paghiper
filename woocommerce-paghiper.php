<?php
/**
 * Plugin Name: WooCommerce Boleto PagHiper
 * Plugin URI: https://github.com/paghiper/woocommerce-paghiper/
 * Description: PagHiper é um gateway de pagamentos brasileiro. Este plugin o integra ao WooCommerce.
 * Author: PagHiper, Henrique Cruz
 * Author URI: https://www.paghiper.com
 * Version: 2.0.1
 * Tested up to: 5.5
 * License: GPLv2 or later
 * Text Domain: woo-boleto-paghiper
 * Domain Path: /languages/
 * WC requires at least: 3.5
 * WC tested up to: 4.4.1
 */	

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Paghiper' ) ) :

/**
 * WooCommerce Boleto main class.
 */
class WC_Paghiper {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '2.0.1';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	private $gateway_settings;
	private $log;

	/**
	 * Initialize the plugin actions.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			// Public includes.
			$this->includes();

			// Admin includes.
			if ( is_admin() ) {
				$this->admin_includes();
			}

			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_action( 'init', array( __CLASS__, 'add_paghiper_endpoint' ) );
			add_filter( 'template_include', array( $this, 'paghiper_template' ), 9999 );
			add_action( 'woocommerce_view_order', array( $this, 'pending_payment_message' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			add_filter( 'woocommerce_new_order', array($this, 'generate_billet') );
			add_filter( 'woocommerce_email_attachments', array($this, 'attach_billet'), 10, 3 );
			

			/* */
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}

		if (!function_exists('json_decode')) {
			add_action( 'admin_notices', array( $this, 'json_missing_notice' ) );
		}

		// Ativa os logs

		// Pega a configuração atual do plug-in.
		$this->gateway_settings = get_option( 'woocommerce_paghiper_settings' );

		// Inicializa logs, caso ativados
		$this->log = wc_paghiper_initialize_log( $this->gateway_settings[ 'debug' ] );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Get plugin path.
	 *
	 * @return string
	 */
	public static function get_plugin_path() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woo-boleto-paghiper' );

		load_textdomain( 'woo-boleto-paghiper', trailingslashit( WP_LANG_DIR ) . 'woocommerce-paghiper/woocommerce-paghiper-' . $locale . '.mo' );
		load_plugin_textdomain( 'woo-boleto-paghiper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {

		include_once 'includes/wc-paghiper-functions.php';
		include_once 'includes/wc-paghiper-notification.php';
		include_once 'includes/class-wc-paghiper-gateway.php';

	}

	/**
	 * Includes.
	 */
	private function admin_includes() {
		require_once 'includes/class-wc-paghiper-admin.php';
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param  array $methods WooCommerce payment methods.
	 *
	 * @return array          Payment methods with Boleto.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_Paghiper_Gateway';

		return $methods;
	}

	/**
	 * Created the paghiper endpoint.
	 */
	public static function add_paghiper_endpoint() {
		add_rewrite_endpoint( 'paghiper', EP_PERMALINK | EP_ROOT );
	}

	/**
	 * Plugin activate method.
	 */
	public static function activate() {
		self::add_paghiper_endpoint();

		flush_rewrite_rules();

		$uploads = wp_upload_dir();
		$upload_dir = $uploads['basedir'];
		$paghiper_dir = $upload_dir . '/paghiper';

		if (!is_dir($paghiper_dir)) {
			mkdir( $paghiper_dir, 0700 );
		}
	}

	/**
	 * Plugin deactivate method.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Generate billet once a new order is placed.
	 * 
	 * @param	string $order_id
	 * 
	 * @return WC_PagHiper_Boleto
	 */
	public function generate_billet( $order_id ) {
		return self::get_plugin_path() . 'includes/class-wc-paghiper-billet.php';
	}

	/**
	 * Attach billet to the order e-mails
	 * 
	 * @param	array $attachments
	 * @param	string $email_id
	 * @param	object $order
	 */
	public function attach_billet( $attachments, $email_id, $order ) {

		if ( $this->log ) {
			wc_paghiper_add_log( $this->log, sprintf( 'Enviando mail: %s', $email_id ) );
		}

		if ( apply_filters('woo_paghiper_pending_status', 'on-hold', $order) === $order->status && 'paghiper' == $order->payment_method ) {
		//if( in_array($email_id, array('new_order', 'customer_invoice')) && 'paghiper' == $order->payment_method ){

			try {

				$order_data = get_post_meta( $order->get_id(), 'wc_paghiper_data', true );

				$transaction_id = 'Boleto bancário - '.$order_data['transaction_id'];
				$billet_url		= $order_data['url_slip_pdf'];

				$uploads = wp_upload_dir();
				$upload_dir = $uploads['basedir'];
				$upload_dir = $upload_dir . '/paghiper';

				$billet_pdf_file = $upload_dir.'/'.$transaction_id.'.pdf';

				if(file_exists($billet_pdf_file)) {
					$attachments[] = $billet_pdf_file;
				}

			} catch(Exception $e) {

				if ( $this->log ) {
					wc_paghiper_add_log( $this->log, sprintf( 'Erro: %s', $e->getMessage() ) );
				}

			}

		}

		return $attachments;

	}

	/**
	 * Add custom template page.
	 *
	 * @param  string $template
	 *
	 * @return string
	 */
	public function paghiper_template( $template ) {
		global $wp_query;

		if ( isset( $wp_query->query_vars['paghiper'] ) ) {

			return self::get_plugin_path() . 'includes/views/html-boleto.php';

		}

		return $template;
	}

	/**
	 * Gets the base-permalink.
	 * Just in case we're dealing with a pathinfo configured WP installation
	 * 
	 * @return string		Base rewrite URL.
	 */

	 public static function get_base_url() {

		$wordpress_permalink_config		= get_option('permalink_structure');
		$woocommerce_permalink_config 	= maybe_unserialize(get_option( 'woocommerce_permalinks' ));

		if(
			strpos($wordpress_permalink_config, 'index.php') === FALSE &&
			(
				is_array($woocommerce_permalink_config) && 
				array_key_exists('product_base', $woocommerce_permalink_config) && 
				strpos($woocommerce_permalink_config['product_base'], 'index.php') === FALSE
			)
		) {
			$base_url = '/';
		} else {
			$base_url = '/index.php/';
		}

		return $base_url;
	 }

	/**
	 * Gets the paghiper URL.
	 *
	 * @param  string $code Boleto code.
	 *
	 * @return string       Boleto URL.
	 */
	public static function get_paghiper_url( $code ) {

		$base_url = WC_Paghiper::get_base_url();

		$home = home_url( $base_url );

		if ( get_option( 'permalink_structure' ) ) {
			$url = trailingslashit( $home ) . 'paghiper/' . $code;
		} else {
			$url = add_query_arg( array( 'paghiper' => $code ), $home );
		}

		return apply_filters( 'woocommerce_paghiper_url', $url, $code, $home );
	}

	/**
	 * Display pending payment message in order details.
	 *
	 * @param  int $order_id Order id.
	 *
	 * @return string        Message HTML.
	 */
	public function pending_payment_message( $order_id ) {
		$order = new WC_Order( $order_id );

		if ( 'on-hold' === $order->status && 'paghiper' == $order->payment_method ) {

			require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-billet.php';
	
			$paghiperBoleto = new WC_PagHiper_Boleto( $order_id );
			$paghiperBoleto->printBarCode(true);

			$html = '<div class="woocommerce-info">';
			$html .= sprintf( '<a class="button" href="%s" target="_blank" style="display: block !important; visibility: visible !important;">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->order_key ) ), __( 'Visualizar boleto &rarr;', 'woo-boleto-paghiper' ) );

			$message = sprintf( __( '%sAtenção!%s Ainda não registramos o pagamento deste pedido.', 'woo-boleto-paghiper' ), '<strong>', '</strong>' ) . '<br />';
			$message .= __( 'Por favor clique no botão ao lado e pague o boleto pelo seu Internet Banking.', 'woo-boleto-paghiper' ) . '<br />';
			$message .= __( 'Caso preferir, você pode imprimir e pagá-lo em qualquer agência bancária ou casa lotérica.', 'woo-boleto-paghiper' ) . '<br />';
			$message .= __( 'Ignore esta mensagem caso ja tenha efetuado o pagamento. O pedido será atualizado assim que houver a compensação.', 'woo-boleto-paghiper' ) . '<br />';

			$html .= apply_filters( 'wcpaghiper_pending_payment_message', $message, $order );

			$html .= '</div>';

			echo $html;
		}
	}

	/**
	 * Action links.
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array();

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_paghiper_gateway' );
		} else {
			$settings_url = admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Paghiper_Gateway' );
		}

		$plugin_links[] = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'woo-boleto-paghiper' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return string
	 */
	public function woocommerce_missing_notice() {
		include_once 'includes/views/html-notice-woocommerce-missing.php';
	}

	/**
	 * JSON missing notice.
	 *
	 * @return string
	 */
	public function json_missing_notice() {
		include_once 'includes/views/html-notice-json-missing.php';
	}
}

/**
 * Plugin activation and deactivation methods.
 */
register_activation_hook( __FILE__, array( 'WC_Paghiper', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Paghiper', 'deactivate' ) );

/**
 * Initialize the plugin.
 */
add_action( 'plugins_loaded', array( 'WC_Paghiper', 'get_instance' ) );

endif;
