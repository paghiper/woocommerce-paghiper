<?php
/* * PagHiper Admin Class
 *
 * @package PagHiper for WooCommerce
 */

// For the WP team: error_log() is used only on emergency type of errors.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Boleto Admin.
 */
class WC_Paghiper_Admin {

	private $timezone;
	private $log;

	/**
	 * Initialize the admin.
	 */
	public function __construct() {

		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );

		// Save Metabox.
		add_action( 'save_post', array( $this, 'save' ) );
		add_action( 'woocommerce_update_order', array( $this, 'save' ) );

		// Update.
		//add_action( 'admin_init', array( $this, 'update' ), 5 );

		// Define our default offset
		$this->timezone = new DateTimeZone('America/Sao_Paulo');

		// Enqueue styles and assets
		add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_assets' ) );
	}

	private function is_target_screen() {

		global $post;
		if($post && $post->post_type == 'shop_order') {

			$order = wc_get_order( $post->ID );

		} else {

			// In case any of the variables are missing, bail out
			// For the WP team: We don't check for nonce because this is not a form submission, only a check for the GET request to the admin page.
			if(!array_key_exists('page', $_GET) || !array_key_exists('action', $_GET) || !array_key_exists('id', $_GET))
				return false;

			$current_page 	= sanitize_key( wp_unslash($_GET['page']) );
			$current_action = sanitize_key( wp_unslash($_GET['action']) );

			if( $current_page == 'wc-orders' && $current_action == 'edit' ) {
				$order_id = (int) $_GET['id'];
				$order = wc_get_order( $order_id );

			} else {
				return false;
			}

		}
		
		if(!$order) {
			return false;
		}

		return $order;
	}

	/**
	 * Register paghiper metabox.
	 */
	public function register_metabox() {

		$order = $this->is_target_screen();

		if( !$order )
			return;

		$payment_method = $order->get_payment_method();
		
		if(!in_array($payment_method, ['paghiper', 'paghiper_billet', 'paghiper_pix'])) {
			return;
		}

		$method_title = ($payment_method == 'paghiper_pix') ? "PIX" : "Boleto";

		$target_screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) &&
			wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? 
			wc_get_page_screen_id( 'shop-order' ) : 
			'shop_order';

		add_meta_box(
			'paghiper-boleto',
      // translators: %s: method payments name.
      sprintf(__( "Configurações do %s", 'woo-boleto-paghiper' ),$method_title),
			array( $this, 'metabox_content' ),
			$target_screen,
			'side',
			'high'
		);
		
	}

	/**
	 * Banking Ticket metabox content.
	 *
	 * @param  object $post order_shop data.
	 *
	 * @return string       Metabox HTML.
	 */
	public function metabox_content( $post_or_order_object ) {

		// Get order data.
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		// Use nonce for verification.
		wp_nonce_field( basename( __FILE__ ), 'woo_paghiper_metabox_nonce' );
		$gateway_name = $order->get_payment_method();

		if ( in_array($gateway_name, ['paghiper', 'paghiper_pix', 'paghiper_billet']) ) {
			$paghiper_data = $order->get_meta( 'wc_paghiper_data' ) ;

			if(!is_array($paghiper_data)) {
				$paghiper_data = [];
			}

			// Compatibility with pre v2.1 keys
			if( isset($paghiper_data['order_billet_due_date']) && !isset($paghiper_data['order_transaction_due_date']) ) {
				$paghiper_data['order_transaction_due_date'] = $paghiper_data['order_billet_due_date'];
			}

			// Save the ticket data if don't have.
			if( !array_key_exists('order_transaction_due_date', $paghiper_data) || empty($paghiper_data['order_transaction_due_date']) ) {

				$data = [];

				// Pega a configuração atual do plug-in.
				$settings = ($gateway_name == 'paghiper_pix') ? get_option( 'woocommerce_paghiper_pix_settings' ) : get_option( 'woocommerce_paghiper_billet_settings' );

				// Inicializa logs, caso ativados
				if(!$this->log) {
					$this->log = wc_paghiper_initialize_log( $settings[ 'debug' ] );
				}

				// Define o número de dias para a data de vencimento da transação
				$order_transaction_due_date	= isset( $settings['days_due_date'] ) ? absint( $settings['days_due_date'] ) : 5;

				// Cria um objeto DateTime para o momento atual
				$transaction_due_date = new DateTime('now', $this->timezone);

				// Adiciona o número de dias de forma segura e legível
				$transaction_due_date->modify("+$order_transaction_due_date days");

				// Formata a data resultante no formato desejado
				$data['order_transaction_due_date']	= $transaction_due_date->format('Y-m-d');
				
				$order->update_meta_data( 'wc_paghiper_data', $data );
				$order->save();

				// TODO: Re-send order mail with payment info on this case, if it ever happens.
				if ( $this->log ) {
					wc_paghiper_add_log( $this->log, sprintf( 'Pedido #%s: Dados da PagHiper indisponíveis para esse pedido. Nova transação foi gerada.', esc_html($order->get_id()) ), [], WC_Log_Levels::ALERT );
				}
				
				if(function_exists('update_meta_cache'))
					update_meta_cache( 'shop_order', $order->get_id() );

				$paghiper_data['order_transaction_due_date'] = $data['order_transaction_due_date'];
			}

			require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';

			$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );

			$order_transaction_due_date = DateTime::createFromFormat('Y-m-d', $paghiper_data['order_transaction_due_date'], $this->timezone);
			/* translators: %s: Transaction type. May be PIX or billet, for an example. */
			$formatted_due_date = ($order_transaction_due_date) ? $order_transaction_due_date->format('d/m/Y') : sprintf(__("%s indisponível", 'woo-boleto-paghiper'), (($gateway_name == 'paghiper_pix') ? __("PIX", 'woo-boleto-paghiper') : __("Boleto", 'woo-boleto-paghiper')));

			?>

			<?php $paghiperTransaction->printBarCode(true, true, ['code', 'digitable']); ?>

			<p><strong><?php esc_html_e( 'Data de Vencimento:', 'woo-boleto-paghiper' ); ?></strong> <?php echo esc_html ($formatted_due_date); ?></p>

			<?php if($gateway_name !== 'paghiper_pix') { ?>
				<?php /* translators: Description for display right before billet URL on admin panel. */ ?>
			<p>
				<strong><?php esc_html_e( 'URL:', 'woo-boleto-paghiper' ); ?></strong>
				<a target="_blank" href="<?php echo esc_url( wc_paghiper_get_paghiper_url( $order->get_order_key() ) ); ?>">
					<?php esc_html_e( 'Visualizar boleto', 'woo-boleto-paghiper' ); ?>
				</a>
			</p>
			<?php } ?>

			<p style="border-top: 1px solid #ccc;"></p>

			<label for="woo_paghiper_expiration_date"><?php esc_html_e( 'Digite uma nova data de vencimento:', 'woo-boleto-paghiper' ); ?></label><br />
			<input type="text" id="woo_paghiper_expiration_date" name="woo_paghiper_expiration_date" class="date" style="width: 100%;" />
			<span class="description">
				<?php printf(
					// translators: %s: Transaction type. May be PIX or billet, for an example.
					esc_html__( 'Ao configurar uma nova data de vencimento, o %s é re-enviado ao cliente por e-mail.', 'woo-boleto-paghiper' ),
					(($gateway_name !== 'paghiper_pix') ? esc_html__('boleto', 'woo-boleto-paghiper') : esc_html__('PIX', 'woo-boleto-paghiper') )); ?>
			</span>

			<?php // Show errors related to user input (invalid or past inputted dates)
			if ( $error = get_transient( "woo_paghiper_save_order_errors_{$order->get_id()}" ) ) {

				printf('<div class="error"><p>%s</p></div>', esc_html($error)); 
				delete_transient("woo_paghiper_save_order_errors_{$order->get_id()}");

			}

			
			// Show due date errors (set on weekend, skipped to monday)
			if ( $error = get_transient( "woo_paghiper_due_date_order_errors_{$order->get_id()}" ) ) {

				printf('<div class="error"><p>%s</p></div>', esc_html($error)); 

			}


		} else { ?>
			<p><?php esc_html_e( 'Este pedido não foi efetuado nem pago com PIX ou boleto da Paghiper.', 'woo-boleto-paghiper' ); ?></p>
		<?php }

	}

	/**
	 * Save metabox data.
	 *
	 * @param int $post_id Current post type ID.
	 */
	public function save( $post_id ) {

		// Sanitize nonce field before processing any form data
		$wc_paghiper_metabox_nonce = '';
		if ( array_key_exists( 'woo_paghiper_metabox_nonce', $_POST ) ) {
			$wc_paghiper_metabox_nonce = sanitize_text_field( wp_unslash( $_POST['woo_paghiper_metabox_nonce'] ) );
		}
		
		// Verify nonce.
		if ( empty( $wc_paghiper_metabox_nonce ) || !wp_verify_nonce( $wc_paghiper_metabox_nonce, basename( __FILE__ ) ) ) {
			return $post_id;
		}

		// Verify if this is an auto save routine.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check permissions.
		if ( OrderUtil::is_order( $post_id, wc_get_order_types() ) && ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}

		// Sanitize expiration date
		$wc_paghiper_expiration_date = '';
		if ( array_key_exists( 'woo_paghiper_expiration_date', $_POST ) ) {
			$wc_paghiper_expiration_date = sanitize_text_field( wp_unslash( $_POST['woo_paghiper_expiration_date'] ) );
		}

		if ( !empty( $wc_paghiper_expiration_date ) ) {

			$today_date = new \DateTime();
			$today_date->setTimezone($this->timezone);

			$order = wc_get_order( $post_id );
			$paghiper_data = $order->get_meta( 'wc_paghiper_data' ) ;
			$new_due_date = DateTime::createFromFormat('d/m/Y', $wc_paghiper_expiration_date, $this->timezone);

			$formatted_date = ($new_due_date) ? $new_due_date->format('d/m/Y') : NULL ;

			if(!$new_due_date || $formatted_date !== $wc_paghiper_expiration_date) {

				$error = __( '<strong>Boleto PagHiper</strong>: Data de vencimento inválida!', 'woo-boleto-paghiper' );
				set_transient("woo_paghiper_save_order_errors_{$post_id}", $error, 45);

				return $post_id;

			} elseif($new_due_date && $today_date->diff($new_due_date)->format("%r%a") < 0) {

				$error = __( '<strong>Boleto PagHiper</strong>: A data de vencimento não pode ser anterior a data de hoje!', 'woo-boleto-paghiper' );
				set_transient("woo_paghiper_save_order_errors_{$post_id}", $error, 45);

				return $post_id;

			}

			// Update ticket data.
			$paghiper_data['order_transaction_due_date'] = $new_due_date->format('Y-m-d');
			$order->update_meta_data( 'wc_paghiper_data', $paghiper_data );

			if(function_exists('update_meta_cache'))
				update_meta_cache( 'shop_order', $post_id );
				
			// Delete notification if order due date has been modified
			delete_transient("woo_paghiper_due_date_order_errors_{$post_id}");

			// Add order note.
			
			/* translators: %s: Transaction due date. */
			$order->add_order_note( sprintf( __( 'PagHiper: Data de vencimento alterada para %s', 'woo-boleto-paghiper' ), $formatted_date ) );
			remove_action( 'woocommerce_update_order', array( $this, 'save' ) ); // Prevent infinite loop.
			$order->save(); // Save order data.

			// Send email notification.
			$this->email_notification( $order, $new_due_date->format('d/m/Y') );

			return $post_id;

		}
	}

	/**
	 * New expiration date email notification.
	 *
	 * @param object $order           Order data.
	 * @param string $expiration_date Ticket expiration date.
	 */
	protected function email_notification( $order, $expiration_date ) {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$mailer = WC()->mailer();
		} else {
			global $woocommerce;
			$mailer = $woocommerce->mailer();
		}

		$gateway_id = $order->get_payment_method();
		$billing_email = (property_exists($order, "get_billing_email")) ? $order->get_billing_email : $order->get_billing_email();

		if(!$billing_email)
			return;

		if($gateway_id == 'paghiper_pix') {
			$gateway_name = __('PIX', 'woo-boleto-paghiper');
		} else {
			$gateway_name = __('boleto', 'woo-boleto-paghiper');
		}

		/* translators: %1$s: Transaction type. %2$s: Order number. */
		$subject = sprintf( __( 'O %1$s do seu pedido foi atualizado (Pedido #%2$s)', 'woo-boleto-paghiper' ), $gateway_name, $order->get_order_number() );

		// Mail headers.
		$headers = array();
		$headers[] = "Content-Type: text/html\r\n";

		// Billet re-emission
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';

		$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );

		// Body message.
		/* translators: %1$s: Transaction type. %2$s: Newly defined transaction due date. */
		$main_message = '<p>' . sprintf( __( 'A data de vencimento do seu %1$s foi atualizada para: %2$s', 'woo-boleto-paghiper' ), $gateway_name, '<code>' . $expiration_date . '</code>' ) . '</p>';
		$main_message .= $paghiperTransaction->printBarCode();
		/* translators: %1$s: Billet URL. %2$s: Pay billet button text. */
		$main_message .= '<p>' . sprintf( '<a class="button" href="%1$s" target="_blank">%2$s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->get_order_key() ) ), __( 'Pagar o boleto &rarr;', 'woo-boleto-paghiper' ) ) . '</p>';

		try {
			// Sets message template.
			/* translators: %s: Transaction due date. */
			$message = $mailer->wrap_message( sprintf(__( 'Nova data de vencimento para o seu %s', 'woo-boleto-paghiper' ), $gateway_name ), $main_message );

			// Send email.
			$mailer->send( $billing_email, $subject, $message, $headers, '' );
			$order->add_order_note( __( 'PagHiper: E-mail de data de vencimento alterada enviada ao cliente', 'woo-boleto-paghiper' ) );
			$order->save();

		} catch (Exception $e) {
			// If the email fails to send, we can log the error or handle it accordingly.
			error_log( sprintf( 'Failed to send PagHiper expiration date email for order %d: %s', $order->get_id(), $e->getMessage() ) );
		}
	}

	/**
	 * Register and enqueue assets
	 */

	public function load_plugin_assets() {

		if( !wp_script_is( 'jquery-mask', 'registered' ) ) {
			wp_register_script( 'jquery-mask', wc_paghiper_assets_url() . 'js/libs/jquery.mask/jquery.mask.min.js', array( 'jquery' ), '1.14.16', false );
		}

		if( !wp_script_is( 'paghiper-backend-js', 'registered' ) ) {
			wp_register_script( 'paghiper-backend-js', wc_paghiper_assets_url() . 'js/backend.min.js', array( 'jquery' ),'1.1', false );
		}

        wp_register_style( 'paghiper-backend-css', wc_paghiper_assets_url() . 'css/backend.min.css', false, '1.0.0' );

		if(is_admin()) {

			// For the WP Team: We don't check for nonce because this is not a form submission, only a check for the GET request to the admin page.
			if( array_key_exists( 'action', $_REQUEST ) ) {
				$req_action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
			
				global $current_screen;
				if ($current_screen->post_type =='shop_order' && $req_action == 'edit') {
		
					wp_enqueue_script(  'jquery-mask' );
					wp_enqueue_script( 'paghiper-backend-js' );
		
				}
			}
			
			wp_enqueue_style( 'paghiper-backend-css' );
			
		}
	}
}

new WC_Paghiper_Admin();
