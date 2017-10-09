<?php
/**
 * Plugin Name: WooCommerce Boleto PagHiper
 * Plugin URI: https://github.com/paghiper/WooCommerce
 * Description: PagHiper é um gateway de pagamentos brasileiro. Este plugin o integra ao WooCommerce.
 * Author: PagHiper
 * Author URI: https://www.paghiper.com
 * Version: 1.2.4
 * License: GPLv2 or later
 * Text Domain: woocommerce-paghiper
 * Domain Path: /languages/
 * GitHub Plugin URI: paghiper/woocommerce-paghiper/
 * GitHub Plugin URI: https://github.com/paghiper/woocommerce-paghiper/
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
	const VERSION = '1.2.4';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

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
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}

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
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-paghiper' );

		load_textdomain( 'woocommerce-paghiper', trailingslashit( WP_LANG_DIR ) . 'woocommerce-paghiper/woocommerce-paghiper-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce-paghiper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once 'includes/wc-paghiper-functions.php';
		include_once 'includes/class-wc-paghiper-gateway.php';
		include_once 'includes/wc-retorno-paghiper.php';
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


		if ( is_admin() ) {		
			require 'plugin-update-checker/plugin-update-checker.php';
			$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
				'https://github.com/paghiper/woocommerce-paghiper/',
				__FILE__,
				'woocommerce-paghiper'
			);
		}


	}

	/**
	 * Plugin activate method.
	 */
	public static function activate() {
		self::add_paghiper_endpoint();

		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivate method.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
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
	 * Gets the paghiper URL.
	 *
	 * @param  string $code Boleto code.
	 *
	 * @return string       Boleto URL.
	 */
	public static function get_paghiper_url( $code ) {
		$home = home_url( '/' );

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
			$html = '<div class="woocommerce-info">';
			$html .= sprintf( '<a class="button" href="%s" target="_blank" style="display: block !important; visibility: visible !important;">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->order_key ) ), __( 'Pay the Ticket &rarr;', 'woocommerce-paghiper' ) );

			$message = sprintf( __( '%sAttention!%s Not registered the payment the docket for this product yet.', 'woocommerce-paghiper' ), '<strong>', '</strong>' ) . '<br />';
			$message .= __( 'Please click the following button and pay the Ticket in your Internet Banking.', 'woocommerce-paghiper' ) . '<br />';
			$message .= __( 'If you prefer, print and pay at any bank branch or lottery retailer.', 'woocommerce-paghiper' ) . '<br />';
			$message .= __( 'Ignore this message if the payment has already been made​​.', 'woocommerce-paghiper' ) . '<br />';

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

		$plugin_links[] = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'woocommerce-paghiper' ) . '</a>';

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
