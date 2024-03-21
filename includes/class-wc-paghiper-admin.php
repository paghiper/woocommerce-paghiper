<?php
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

	/**
	 * Initialize the admin.
	 */
	public function __construct() {
		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );

		// Save Metabox.
		add_action( 'save_post', array( $this, 'save' ) );

		// Update.
		//add_action( 'admin_init', array( $this, 'update' ), 5 );

		// Define our default offset
		$this->timezone = new DateTimeZone('America/Sao_Paulo');

		// Enqueue styles and assets
		add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_assets' ) );
	}

	/**
	 * Register paghiper metabox.
	 */
	public function register_metabox() {

		global $post;

		if(!$post || $post->post_type !== 'shop_order') {
			return;
		}
		
		$order = wc_get_order( $post->ID );
		$payment_method = $order->get_payment_method();
		
		if(in_array($payment_method, ['paghiper', 'paghiper_billet', 'paghiper_pix'])) {

			$method_title = ($payment_method == 'paghiper_pix') ? "PIX" : "Boleto";

			$target_screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
				? wc_get_page_screen_id( 'shop-order' )
				: 'shop_order';

			add_meta_box(
				'paghiper-boleto',
				__( "Configurações do {$method_title}", 'woo_paghiper' ),
				array( $this, 'metabox_content' ),
				$target_screen,
				'side',
				'default'
			);

		}
		
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

			// Compatibility with pre v2.1 keys
			if( isset($paghiper_data['order_billet_due_date']) && !isset($paghiper_data['order_transaction_due_date']) ) {
				$paghiper_data['order_transaction_due_date'] = $paghiper_data['order_billet_due_date'];
			}

			// Save the ticket data if don't have.
			if ( !isset($paghiper_data['order_transaction_due_date']) ) {

				// Pega a configuração atual do plug-in.
				$settings = ($gateway_name == 'paghiper_pix') ? get_option( 'woocommerce_paghiper_pix_settings' ) : get_option( 'woocommerce_paghiper_billet_settings' );

				$order_transaction_due_date			= isset( $settings['days_due_date'] ) ? absint( $settings['days_due_date'] ) : 5;
				$data                   			= array();
				$data['order_transaction_due_date']	= date( 'Y-m-d', time() + ( $order_transaction_due_date * 86400 ) );
				
				$order->update_meta_data( 'wc_paghiper_data', $data );
				$order->save();
				
				if(function_exists('update_meta_cache'))
					update_meta_cache( 'shop_order', $order->get_id() );

				$paghiper_data['order_transaction_due_date'] = $data['order_transaction_due_date'];
			}

			require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';

			$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );
			$html = $paghiperTransaction->printBarCode(false, true, ['code', 'digitable']);

			$order_transaction_due_date = DateTime::createFromFormat('Y-m-d', $paghiper_data['order_transaction_due_date'], $this->timezone);
			$formatted_due_date = ($order_transaction_due_date) ? $order_transaction_due_date->format('d/m/Y') : sprintf(__("%s indisponível"), (($gateway_name == 'paghiper_pix') ? __("PIX") : __("Boleto")));

			$html .= '<p><strong>' . __( 'Data de Vencimento:', 'woo_paghiper' ) . '</strong> ' . $formatted_due_date . '</p>';

			if($gateway_name !== 'paghiper_pix')
			$html .= '<p><strong>' . __( 'URL:', 'woo_paghiper' ) . '</strong> <a target="_blank" href="' . esc_url( wc_paghiper_get_paghiper_url( $order->get_order_key() ) ) . '">' . __( 'Visualizar boleto', 'woo_paghiper' ) . '</a></p>';

			$html .= '<p style="border-top: 1px solid #ccc;"></p>';

			$html .= '<label for="woo_paghiper_expiration_date">' . __( 'Digite uma nova data de vencimento:', 'woo_paghiper' ) . '</label><br />';
			$html .= '<input type="text" id="woo_paghiper_expiration_date" name="woo_paghiper_expiration_date" class="date" style="width: 100%;" />';
			$html .= '<span class="description">' . sprintf(__( 'Ao configurar uma nova data de vencimento, o %s é re-enviado ao cliente por e-mail.', 'woo_paghiper' ), (($gateway_name !== 'paghiper_pix') ? 'boleto' : 'PIX')) . '</span>';

			// Show errors related to user input (invalid or past inputted dates)
			if ( $error = get_transient( "woo_paghiper_save_order_errors_{$order->get_id()}" ) ) {

				$html .= sprintf('<div class="error"><p>%s</p></div>', $error); 
				delete_transient("woo_paghiper_save_order_errors_{$order->get_id()}");

			}

			
			// Show due date errors (set on weekend, skipped to monday)
			if ( $error = get_transient( "woo_paghiper_due_date_order_errors_{$order->get_id()}" ) ) {

				$html .= sprintf('<div class="error"><p>%s</p></div>', $error); 

			}


		} else {
			$html = '<p>' . __( 'Este pedido não foi efetuado ou pago com boleto.', 'woo_paghiper' ) . '</p>';
			$html .= '<style>#woo_paghiper.postbox {display: none;}</style>';
		}

		echo $html;

	}

	/**
	 * Save metabox data.
	 *
	 * @param int $post_id Current post type ID.
	 */
	public function save( $post_id ) {
		// Verify nonce.
		if ( ! isset( $_POST['woo_paghiper_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['woo_paghiper_metabox_nonce'], basename( __FILE__ ) ) ) {
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

		if ( isset( $_POST['woo_paghiper_expiration_date'] ) && ! empty( $_POST['woo_paghiper_expiration_date'] ) ) {

			// Store our input on a var for later use
			$input_date = sanitize_text_field( trim($_POST['woo_paghiper_expiration_date']) );

			$today_date = new \DateTime();
			$today_date->setTimezone($this->timezone);

			$order = wc_get_order( $post_id );
			$paghiper_data = $order->get_meta( 'wc_paghiper_data' ) ;
			$new_due_date = DateTime::createFromFormat('d/m/Y', $input_date, $this->timezone);

			$formatted_date = ($new_due_date) ? $new_due_date->format('d/m/Y') : NULL ;

			if(!$new_due_date || $formatted_date !== $input_date) {

				$error = __( '<strong>Boleto PagHiper</strong>: Data de vencimento inválida!', 'woo_paghiper' );
				set_transient("woo_paghiper_save_order_errors_{$post_id}", $error, 45);

				return $post_id;

			} elseif($new_due_date && $today_date->diff($new_due_date)->format("%r%a") < 0) {

				$error = __( '<strong>Boleto PagHiper</strong>: A data de vencimento não pode ser anterior a data de hoje!', 'woo_paghiper' );
				set_transient("woo_paghiper_save_order_errors_{$post_id}", $error, 45);

				return $post_id;

			}

			// Update ticket data.
			$paghiper_data['order_transaction_due_date'] = $new_due_date->format('Y-m-d');
			$order->update_meta_data( 'wc_paghiper_data', $paghiper_data );
			$order->save();

			if(function_exists('update_meta_cache'))
				update_meta_cache( 'shop_order', $post_id );
				
			// Delete notification if order due date has been modified
			delete_transient("woo_paghiper_due_date_order_errors_{$post_id}");

			// Add order note.
			$order->add_order_note( sprintf( __( 'Data de vencimento alterada para %s', 'woo_paghiper' ), $formatted_date ) );

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

		$gateway_name = $order->get_payment_method();
		$billing_email = (property_exists($order, "get_billing_email")) ? $order->get_billing_email : $order->get_billing_email();

		if(!$billing_email)
			return;

		$subject = sprintf( __( 'O %s do seu pedido foi atualizado (%s)', 'woo_paghiper' ), (($gateway_name !== 'paghiper_pix') ? 'boleto' : 'PIX'), $order->get_order_number() );

		// Mail headers.
		$headers = array();
		$headers[] = "Content-Type: text/html\r\n";

		// Billet re-emission
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';

		$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );

		// Body message.
		$main_message = '<p>' . sprintf( __( 'A data de vencimento do seu %s foi atualizada para: %s', 'woo_paghiper' ), ((($gateway_name !== 'paghiper_pix') ? 'boleto' : 'PIX')), '<code>' . $expiration_date . '</code>' ) . '</p>';
		$main_message .= $paghiperTransaction->printBarCode();
		$main_message .= '<p>' . sprintf( '<a class="button" href="%s" target="_blank">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->get_order_key() ) ), __( 'Pagar o boleto &rarr;', 'woo_paghiper' ) ) . '</p>';

		// Sets message template.
		$message = $mailer->wrap_message( sprintf(__( 'Nova data de vencimento para o seu %s', 'woo_paghiper' ), ((($gateway_name !== 'paghiper_pix') ? 'boleto' : 'PIX'))), $main_message );

		// Send email.
		$mailer->send( $billing_email, $subject, $message, $headers, '' );
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
			
			global $current_screen;
			$req_action = empty( $_REQUEST[ 'action' ] ) ? false : $_REQUEST[ 'action' ];
			if ($current_screen->post_type =='shop_order' && $req_action == 'edit') {
	
				wp_enqueue_script(  'jquery-mask' );
				wp_enqueue_script( 'paghiper-backend-js' );
	
			}
			
			wp_enqueue_style( 'paghiper-backend-css' );
			
		}
	}
}

new WC_Paghiper_Admin();
