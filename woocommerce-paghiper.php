<?php
/**
 * Plugin Name: 			WooCommerce Boleto e PIX PagHiper
 * Plugin URI: 				https://github.com/paghiper/woocommerce-paghiper/
 * Description: 			Ofereça a seus clientes pagamento por PIX e boleto bancário com a PagHiper. Fácil, prático e rapido!
 * Author: 					PagHiper Pagamentos
 * Author URI: 				https://www.paghiper.com
 * Version: 				2.1.5
 * Tested up to: 			5.7
 * License: 				GPLv2 or later
 * Text Domain: 			woo-boleto-paghiper
 * Domain Path: 			/languages/
 * WC requires at least: 	3.5
 * WC tested up to: 		5.2.0
 */	

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PagHiper\PagHiper;

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
	const VERSION = '2.1.5';

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
			add_action( 'init', array( __CLASS__, 'maybe_deactivate_other_plugins' ) );
			add_action( 'admin_notices', array( __CLASS__, 'check_paghiper_credentials' ) );
			add_action( 'admin_init', array( __CLASS__, 'print_notices' ) );
			add_action( 'wp_ajax_paghiper_dismiss_notice', array( __CLASS__, 'dismiss_notices') );

			add_filter( 'template_include', array( $this, 'paghiper_template' ), 9999 );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			//add_filter( 'woocommerce_new_order', array($this, 'generate_transaction') );
			add_filter( 'woocommerce_email_attachments', array($this, 'attach_billet'), 10, 3 );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_plugin_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_assets' ) );

			// Migra configurações das chaves antigas ao atualizar
			add_action( 'init', array( $this, 'migrate_gateway_settings' ));
			
		}

		/* Print some notices */
		add_action( 'admin_notices', array( $this, 'print_requirement_notices' ) );

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

		load_textdomain( 'woo-boleto-paghiper', trailingslashit( WP_LANG_DIR ) . 'woo_paghiper/woo_paghiper-' . $locale . '.mo' );
		load_plugin_textdomain( 'woo-boleto-paghiper', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Includes.
	 */
	private function includes() {

		include_once 'includes/wc-paghiper-functions.php';
		include_once 'includes/wc-paghiper-notification.php';
		include_once 'includes/class-wc-paghiper-billet-gateway.php';
		include_once 'includes/class-wc-paghiper-pix-gateway.php';

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
		$methods[] = 'WC_Paghiper_Billet_Gateway';
		$methods[] = 'WC_Paghiper_Pix_Gateway';

		return $methods;
	}

	/**
	 * Created the paghiper endpoint.
	 */
	public static function add_paghiper_endpoint() {
		add_rewrite_endpoint( 'paghiper', EP_PERMALINK | EP_ROOT );
	}

	/**
	 * Check if other outdated or deprecated plug-ins are active
	 * 
	 * @return string
	 */

	public static function maybe_deactivate_other_plugins() {

		// Get all plugins
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$all_plugins = get_plugins();

		// Get active plugins
		$active_plugins = get_option('active_plugins');
		$multiple_instances = false;

		$current_plugin_path = plugin_dir_path( __FILE__ ).'woocommerce-paghiper.php';
		$other_plugins = ['woo-paghiper-pix.php', 'woocommerce-paghiper.php'];

		// Assemble array of name, version, and whether plugin is active (boolean)
		foreach ( $all_plugins as $key => $value ) {
			$is_active = ( in_array( $key, $active_plugins ) ) ? true : false;

			if(stripos($current_plugin_path, $key) === FALSE) {

				foreach($other_plugins as $to_be_deactivated) {
					if(stripos($key, $to_be_deactivated) !== FALSE) {

						$multiple_instances = true;
						deactivate_plugins($key);
					}
				}
			}
		}

		if($multiple_instances) {

			add_action( 'admin_notices', function() {
				include_once 'includes/views/notices/html-notice-multiple-instances.php';
			});
		}
	}

	/**
	 * Check saved credentials on admin space.
	 */
	public static function check_paghiper_credentials() {

		add_action( 'admin_notices', function() {

			// Include SDK for our call
			require_once WC_Paghiper::get_plugin_path() . 'includes/paghiper-php-sdk/build/vendor/scoper-autoload.php';
	
			$gateways = ['woocommerce_paghiper_pix_settings', 'woocommerce_paghiper_billet_settings'];
			foreach($gateways as $gateway) {
				$gateway_settings = get_option( $gateway );
				$is_pix = ($gateway == 'woocommerce_paghiper_pix_settings') ? true : false;
	
				$gateway_name = ($is_pix) ? 'PIX PagHiper' : 'Boleto PagHiper';
				$gateway_class = ($is_pix) ? 'wc_paghiper_pix_gateway' : 'wc_paghiper_billet_gateway';
	
				if(!array_key_exists('api_key', $gateway_settings) || empty($gateway_settings['api_key'])) {
					echo sprintf('<div class="error notice"><p><strong>%s: </strong>%s <a href="%s">%s</a></p></div>', __($gateway_name), __('Você ainda não configurou sua apiKey! Finalize a configuração do seu plug-in aqui:'), admin_url("admin.php?page=wc-settings&tab=checkout&section={$gateway_class}"), __("Configurações de integração {$gateway_name}"));
		
				}
	
			};

			if(!get_transient( 'woo_paghiper_apikey_valid' )) {

				$valid_gateway_apis = [];
				foreach($gateways as $gateway) {
					$gateway_settings = get_option( $gateway );
					$is_pix = ($gateway == 'woocommerce_paghiper_pix_settings') ? true : false;
	
					$gateway_name = ($is_pix) ? 'PIX PagHiper' : 'Boleto PagHiper';
					$gateway_class = ($is_pix) ? 'wc_paghiper_pix_gateway' : 'wc_paghiper_billet_gateway';
		
					try {
						$PagHiperAPI = new PagHiper($gateway_settings['api_key'], $gateway_settings['token']);
						$response = $PagHiperAPI->transaction()->status('0000000000000000');
						$valid_gateway_apis[] = $gateway;
					} catch(Exception $e) {
		
						if (strpos($e->getMessage(), 'apiKey') !== false) {
							echo sprintf('<div class="error notice"><p><strong>%s: </strong>%s <a href="%s">%s</a></p></div>', __($gateway_name), __('Sua apiKey é inválida! Confira novamente seus dados aqui:'), admin_url("admin.php?page=wc-settings&tab=checkout&section={$gateway_class}"), __("Configurações de integração {$gateway_name}"));
						}
					}
				}
	
				if(sizeof($valid_gateway_apis) == sizeof($gateways)) {
					set_transient( 'woo_paghiper_apikey_valid', 1, 12 * 60 * 60 );
				}
			}

		});

	}

	/**
	 * Print notices for the admin on the wp-admin section
	 */
	public static function print_notices() {

		$is_updated = get_transient( 'woo_paghiper_notice_2_1' );

		if($is_updated) {

			// Enqueue scripts
			add_action( 'admin_enqueue_scripts', function() {
				
				wp_localize_script( 'paghiper_admin_js', 'notice_params', array(
					'ajaxurl' => get_admin_url() . 'admin-ajax.php', 
				));
				
				wp_enqueue_script(  'paghiper_admin_js' );
			} );

			// Print notices
			add_action( 'admin_notices', function() {
				echo sprintf('<div class="error notice paghiper-dismiss-notice is-dismissible" data-notice-id="notice_2_1"><p><strong>%s: </strong>%s <a href="%s">%s</a></p></div>', __('PIX PagHiper'), __('Você ja pode receber pagamentos por PIX! Configure aqui:'), admin_url('admin.php?page=wc-settings&tab=checkout&section=wc_paghiper_pix_gateway'), __('Configurações do PIX PagHiper'));
			});
			
		}

		add_action( 'admin_notices', function() {

			$gateways = ['woocommerce_paghiper_pix_settings', 'woocommerce_paghiper_billet_settings'];
			foreach($gateways as $gateway) {

				$gateway_settings = get_option( $gateway );
				if(strpos($gateway_settings['set_status_when_waiting'], 'on-hold') === FALSE) {

					$is_pix = ($gateway == 'woocommerce_paghiper_pix_settings') ? true : false;
		
					$gateway_name = ($is_pix) ? 'PIX PagHiper' : 'Boleto PagHiper';
					$gateway_class = ($is_pix) ? 'wc_paghiper_pix_gateway' : 'wc_paghiper_billet_gateway';
	
					if(!apply_filters('hide_waiting_status_warning', false, $gateway_class)) {
						echo sprintf('<div class="error notice"><p><strong>%s: </strong>%s <a href="%s">%s</a></p></div>', __($gateway_name), __('O status após a emissão deve ser "Aguardando" ou equivalente. Caso contrário, o Woocommerce pode cancelar os pedidos antes do pagamento! <br>Finalize a configuração do seu plug-in aqui:'), admin_url("admin.php?page=wc-settings&tab=checkout&section={$gateway_class}"), __("Configurações de integração {$gateway_name}"));
					}
				}
			}

		});

	}

	/**
	 * Allow for notice dismissal via AJAX
	 */
	public static function dismiss_notices() {
		if(isset($_POST) && array_key_exists('notice', $_POST)) {

			$notice_name = str_replace('notice_', '', sanitize_text_field($_POST['notice']));
			$dismissal = delete_transient("woo_paghiper_notice_{$notice_name}");

			if(!$dismissal) {
				return false;
			}

		}
		return true;
	}

	/**
	 * Plugin activate method.
	 */
	public static function activate() {

		// Add our API endpoint for notifications and transactions
		self::add_paghiper_endpoint();
		flush_rewrite_rules();

		// Make sure we have our own dir at /wp-content/uploads so we can write our PDFs
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
	 * Migrate settings from old versions
	 */
	public function migrate_gateway_settings() {

		$plugin_db_version = (float) get_option( 'woocommerce_paghiper_db_version');
		if(get_option( 'woocommerce_paghiper_db_version' ) == 1.1) {
			return false;
		}

		$is_migrated = FALSE;

		// TODO: Check if there are old credentials that need to be migrated
		$legacy_gateway_settings = get_option( 'woocommerce_paghiper_settings' );
		if(!is_array($legacy_gateway_settings) || empty($legacy_gateway_settings)) {
			$legacy_gateway_settings = NULL;
		}

		// Maybe migrate old gateway settings for the new billet gateway
		$billet_gateway_settings = get_option( 'woocommerce_paghiper_billet_settings' );
		$billet_gateway_options = array('enabled', 'title', 'description', 'api_key', 'token', 'paghiper_time', 'debug', 'days_due_date', 'skip_non_workdays', 'open_after_day_due', 'replenish_stock', 'fixed_description', 'set_status_when_waiting', 'set_status_when_paid', 'set_status_when_cancelled');
		
		if(!$billet_gateway_settings && $legacy_gateway_settings) {
			$billet_gateway_settings = [];

			foreach($billet_gateway_options as $billet_gateway_option) {
				$billet_gateway_settings[$billet_gateway_option] = $legacy_gateway_settings[$billet_gateway_option];
			}

			add_option( 'woocommerce_paghiper_billet_settings', $billet_gateway_settings, '', 'yes' );
			$is_migrated = TRUE;
		}
	

		// Maybe migrate old gateway settings for the new PIX gateway
		$pix_gateway_settings = get_option( 'woocommerce_paghiper_pix_settings' );

		if(!$pix_gateway_settings && $legacy_gateway_settings) {
			$pix_gateway_options = $billet_gateway_options;
			$pix_gateway_settings = [];

			foreach($pix_gateway_options as $pix_gateway_option) {
				$pix_gateway_settings[$pix_gateway_option] = $legacy_gateway_settings[$pix_gateway_option];
			}

			unset($pix_gateway_settings['open_after_day_due']);
			$pix_gateway_settings['title'] = 'PIX';
			$pix_gateway_settings['description'] = 'Pague de maneira rápida e prática usando PIX';

			add_option( 'woocommerce_paghiper_pix_settings', $pix_gateway_settings, '', 'yes' );
			$is_migrated = TRUE;
		}

		if($is_migrated) {
			set_transient( 'woo_paghiper_notice_2_1', true, (5 * 24 * 60 * 60) );
		}

		$billet_gateway_settings = get_option( 'woocommerce_paghiper_billet_settings' );
		$pix_gateway_settings = get_option( 'woocommerce_paghiper_pix_settings' );

		if(!empty($billet_gateway_settings['api_key']) || !empty($pix_gateway_settings['api_key'])) {
			update_option( 'woocommerce_paghiper_db_version', 1.1, '', 'yes');
		}

		return $is_migrated;
	}

	/**
	 * Generate billet once a new order is placed.
	 * 
	 * @param	string $order_id
	 * 
	 * @return WC_PagHiper_Transaction
	 */
	public function generate_transaction( $order_id ) {
		return self::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';
	}

	/**
	 * Attach billet to the order e-mails
	 * 
	 * @param	array $attachments
	 * @param	string $email_id
	 * @param	object $order
	 */
	public function attach_billet( $attachments, $email_id, $order ) {
		
		// Simply bailout case target object is not an instance of WC_Order
		if ( ! is_a( $order, 'WC_Order' ) || ! isset( $email_id ) ) {
			return $attachments;
		}

		$payment_method = $order->get_payment_method();
		$order_status = (strpos($order->get_status(), 'wc-') === false) ? 'wc-'.$order->get_status() : $order->get_status();

		if ( in_array($payment_method, ['paghiper', 'paghiper_billet']) ) {

			// Initializes plug-in options
			if(!$this->gateway_settings) {
				$this->gateway_settings = get_option("woocommerce_{$payment_method}_settings");
			}

			// Inicializa logs, caso ativados
			if(!$this->log) {
				$this->log = wc_paghiper_initialize_log( $this->gateway_settings[ 'debug' ] );
			}

			if(apply_filters('woo_paghiper_pending_status', $this->gateway_settings['set_status_when_waiting'], $order) !== $order_status) {
				return;
			}

			if ( $this->log ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Enviando mail: %s', $email_id ) );
			}

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

		return apply_filters( 'woo_paghiper_url', $url, $code, $home );
	}

	/**
	 * Enqueue stylesheets and scripts for the front-end
	 */
	public function load_plugin_assets() {

		if( !wp_script_is( 'jquery-mask', 'registered' ) ) {
			wp_register_script( 'jquery-mask', wc_paghiper_assets_url() . 'js/libs/jquery.mask/jquery.mask.min.js', array( 'jquery' ), '1.14.16', false );
		}

		wp_register_script( 'paghiper_admin_js', wc_paghiper_assets_url() . 'js/admin.min.js', array('jquery', 'jquery-mask'),'1.0', false );
		wp_register_script( 'paghiper_frontend_js', wc_paghiper_assets_url() . 'js/frontend.min.js',array( 'jquery', 'jquery-mask' ),'1.0', false );
		wp_register_style( 'paghiper_frontend_css', wc_paghiper_assets_url() . 'css/frontend.min.css','','1.0', false );

		if(!is_admin()) {

			wp_enqueue_script(  'jquery-mask' );
			wp_enqueue_script(  'paghiper_frontend_js' );
			wp_enqueue_style( 'paghiper_frontend_css' );

		} else {

			
			global $current_screen;
			$req_action = empty( $_REQUEST[ 'action' ] ) ? false : $_REQUEST[ 'action' ];
			if ($current_screen->post_type =='shop_order' && $req_action == 'edit') {
	
				wp_enqueue_script(  'jquery-mask' );
				wp_enqueue_script(  'paghiper_admin_js' );
	
			}
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
			$billet_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_paghiper_billet_gateway' );
			$pix_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_paghiper_pix_gateway' );
		} else {
			$billet_settings_url = admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Paghiper_Billet_Gateway' );
			$pix_settings_url = admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=WC_Paghiper_Pix_Gateway' );
		}

		$plugin_links[] = '<a href="' . esc_url( $billet_settings_url ) . '">' . __( 'Opções de Boleto', 'woo-boleto-paghiper' ) . '</a>';
		$plugin_links[] = '<a href="' . esc_url( $pix_settings_url ) . '">' . __( 'Opções de PIX', 'woo-boleto-paghiper' ) . '</a>';

		return array_merge( $plugin_links, $links );
	}

	/**
	 * JSON missing notice.
	 *
	 * @return string
	 */
	public function print_requirement_notices() {

		/**
		 * Woocommerce missing notice.
		 * 
		 * @return string
		 */

		if ( !class_exists( 'WC_Payment_Gateway' ) ) {
			include_once 'includes/views/notices/html-notice-woocommerce-missing.php';
		}

		/**
		 * JSON missing notice.
		 *
		 * @return string
		 */
		if (!function_exists('json_decode')) {
			include_once 'includes/views/notices/html-notice-json-missing.php';
		}

		/**
		 * GD missing notice.
		 *
		 * @return string
		 */
		if (!function_exists('imagecreate')) {
			include_once 'includes/views/notices/html-notice-gd-missing.php';
		}

		/**
		 * Paghiper directory is not writable
		 * 
		 * @return string
		 */
		$uploads = wp_upload_dir();
		$upload_dir = $uploads['basedir'];
		$upload_dir = $upload_dir . '/paghiper';

		$test_filename = $upload_dir.'/'.time ();
        if (touch($test_filename)) {
            if (!chmod($test_filename, 0666)) {
				include_once 'includes/views/notices/html-notice-paghiper-folder-not-writable.php';
			}
		}

		if(file_exists($test_filename)) {
			unlink($test_filename);
		}

		/**
		 * PHP Version notices.
		 * 
		 * @return string
		 */
		if (version_compare(PHP_VERSION, '5.6.0', '<')) {
			include_once 'includes/views/notices/html-notice-min-php-version.php';
		}

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
