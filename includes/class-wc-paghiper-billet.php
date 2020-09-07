<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PagHiper\PagHiper;

class WC_PagHiper_Boleto {

	private $order;
	private $order_id;
	private $order_data;
	private $gateway_settings;
	private $invalid_reason;
	private $past_due_days;
	private $log;
	private $timezone;

	public function __construct($order_id) {

		global $wp_query;

		// Pega a configuração atual do plug-in.
		$this->gateway_settings = get_option( 'woocommerce_paghiper_settings' );

		// Inicializa logs, caso ativados
		$this->log = wc_paghiper_initialize_log( $this->gateway_settings[ 'debug' ] );

		// Pega a referência do pedido
		$this->order_id = $order_id;

		// Pegamos o pedido completo
		$this->order = new WC_Order( $order_id );

		// Pegamos a meta do pedido
		$this->order_data = get_post_meta( $order_id, 'wc_paghiper_data', true );

		// Formulamos a URL-base a ser utilizada
		$this->base_url = WC_Paghiper::get_base_url();

		// Definimos o offset a ser utilizado para as operações de data
		$this->timezone = new DateTimeZone('America/Sao_Paulo');

	}
	
	public function has_issued_valid_billet() {

		$order_date = DateTime::createFromFormat('Y-m-d', strtok($this->order->order_date, ' '));

		// Define data de vencimento, caso exista
		if(empty($this->order_data["order_billet_due_date"]) || empty($this->order_data["current_billet_due_date"])) {

			$new_request = TRUE;

			if ( $this->log ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Pedido #%s: Data de vencimento não presente no banco.', $this->order_id ) );
			}

		} else {

			$original_due_date = DateTime::createFromFormat('Y-m-d', $this->order_data["order_billet_due_date"]);
			$current_billet_due_date = DateTime::createFromFormat('Y-m-d', $this->order_data["current_billet_due_date"]);

			$different_total = ( $this->order->get_total() == $this->order_data['value_cents'] ? NULL : TRUE );
			$different_due_date = ( $this->order_data["order_billet_due_date"] == $this->order_data["current_billet_due_date"] ? NULL : TRUE );

			$today_date = new \DateTime();
			$today_date->setTimezone($this->timezone);
			$this->past_due_days = (int) $today_date->diff($original_due_date)->format("%r%a");

			if($different_due_date) {

				$this->invalid_reason = 'different_due_date';

				if ( $this->log ) {
					$log_message = 'Pedido #%s: Data de vencimento do boleto não bate com a informada no pedido. Um novo boleto será gerado.';
					wc_paghiper_add_log( $this->log, sprintf( $log_message, $this->order_id ) );
				}

			} elseif($different_total) {

				$this->invalid_reason = 'different_total';

				if ( $this->log ) {
					$log_message = 'Pedido #%s: Valor total não bate com o informada no boleto gerado. Um novo boleto será gerado.';
					wc_paghiper_add_log( $this->log, sprintf( $log_message, $this->order_id ) );
				}
			}

		}

		// Lógica de solicitação/resgate de boleto ja emitido
		if( $different_due_date === TRUE || $different_total === TRUE || $new_request === TRUE) {
			return false;
		} else {
			return true;
		}

	}

	public function determine_due_date() {
		$order_due_date 	= $this->order_data["order_billet_due_date"];
		$billet_days_due	= (!empty($this->gateway_settings['days_due_date'])) ? $this->gateway_settings['days_due_date'] : 5;

		$today_date = new \DateTime();
		$today_date->setTimezone($this->timezone);

		// TODO: Implement better logic here
		if(!empty($order_due_date)) {

			// Calcular dias de diferença entre a data de vencimento e a data atual
			$original_due_date = DateTime::createFromFormat('Y-m-d', $order_due_date, $this->timezone);
			$billet_due_date = $today_date->diff($original_due_date);

		} else {

			$order_data = get_post_meta( $this->order_id, 'wc_paghiper_data', true );

			// Calcular dias entre a data do pedido e os dias para vencimento na configuração
			$billet_due_date = $today_date;
			$billet_due_date->modify( "+{$billet_days_due} days" );

			$order_data['order_billet_due_date'] = $billet_due_date->format( 'Y-m-d' );		
			update_post_meta( $this->order_id, 'wc_paghiper_data', $order_data );

		}

		$billet_due_date = wc_paghiper_add_workdays($billet_due_date, $this->order, $this->gateway_settings['skip_non_workdays']);

		return $billet_due_date->days;
	}

	public function prepare_data_for_billet() {

		if ( empty( $this->order ) ) {
			return false;
		}

		// Sets the boleto details.
		$shop_name = get_bloginfo( 'name' );

		// Set the ticket total.
		$order_line_total = 0;

		// Client data.
		
		// TODO: Implement a filter here, so we don't need these info
		// Get Extra Checkout Fields for Brazil options.
		$wcbcf_settings = get_option( 'wcbcf_settings' );
		if(!empty($this->order->billing_persontype)) {
			$data['payer_name'] = ($this->order->billing_persontype == 2 && !empty($this->order->billing_company)) ? $this->order->billing_company : $this->order->billing_first_name . ' ' . $this->order->billing_last_name ;
			$data['payer_cpf_cnpj'] = ($this->order->billing_persontype == 1) ? $this->order->billing_cpf : $this->order->billing_cnpj ;
		}

		// Address
		$data['payer_email']		= $this->order->billing_email;
		$data['payer_street']  		= $this->order->billing_address_1;
		$data['payer_complement']  	= $this->order->billing_address_2;
		$data['payer_district']		= $this->order->billing_neighborhood;
		$data['payer_number']	 	= $this->order->billing_number;
		$data['payer_city']       	= $this->order->billing_city;
		$data['payer_state']      	= $this->order->billing_state;
		$data['payer_zip_code']   	= $this->order->billing_postcode;

		// Cart items
		$data['items'] = array();
		foreach ( $this->order->get_items() as $item_id => $item ) {

			$product_id 		= ($item->is_type( 'variable' )) ? $item->get_variation_id() : $item->get_product_id() ;
			$product_name		= $item->get_name();
			$product_quantity	= $item->get_quantity();
			$individual_price	= $item->get_subtotal() / $product_quantity;
			$product_price		= $this->_convert_to_cents($individual_price);

			$data['items'][] = array(
				'item_id'		=> $product_id,
				'description'	=> $product_name,
				'quantity'		=> $product_quantity,
				'price_cents'	=> $product_price
			);

			$order_line_total += $individual_price * $product_quantity;

		}

		// Shipping data
		$shipping_method = '';
		foreach( $this->order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
			$order_item_name             = $shipping_item_obj->get_name();
			$order_item_type             = $shipping_item_obj->get_type();
			$shipping_method_title       = $shipping_item_obj->get_method_title();
			$shipping_method_id          = $shipping_item_obj->get_method_id(); // The method ID
			$shipping_method_instance_id = $shipping_item_obj->get_instance_id(); // The instance ID
			$shipping_method_total       = $shipping_item_obj->get_total();
			$shipping_method_total_tax   = $shipping_item_obj->get_total_tax();
			$shipping_method_taxes       = $shipping_item_obj->get_taxes();

			$shipping_method = (empty($shipping_method) && !empty($shipping_method_title)) ? $shipping_method_title : '';
		}


		$order_shipping					= $this->order->get_total_shipping();
		$data['shipping_methods']		= $shipping_method;
		$data['shipping_price_cents']	= $this->_convert_to_cents($order_shipping);

		// Discount data
		$order_discount					= $this->order->get_total_discount();
		$order_discount_cents			= $this->_convert_to_cents($order_discount);

		// Taxes and additional costs
		$order_taxes 		= $this->order->get_total_tax();
		$taxes_description 	= 'Taxas e impostos';

		// Conciliate order, in order to avoind conflict with third-party plugins and custom solutions
		// We do this to facilitate integration, even when users implement stuff using unorthodox methos
		$order_total 		= round(floatval($this->order->get_total()), 2);
		$simulated_total	= round(($order_line_total + $order_shipping + $order_taxes) - $order_discount, 2);

		// If our sum is higher than the order total:
		if($order_total < $simulated_total) {

			$order_discount = $simulated_total - $order_total;
			$order_discount_cents = $this->_convert_to_cents($order_discount);

		// If our sum is lower than the order total:
		} elseif($order_total > $simulated_total) {

			$order_taxes 	= $order_total - $simulated_total;

		}

		$data['discount_cents']	= $this->_convert_to_cents($order_discount);
		$data['discount_cents']	= $order_discount_cents;
		if($order_taxes > 0) {
			$data['items'][] = array(
				'item_id'		=> 1,
				'description'	=> apply_filters('woo_paghiper_taxes_description', $taxes_description, $this->order_id),
				'quantity'		=> 1,
				'price_cents'	=> $this->_convert_to_cents($order_taxes)
			);
		}

		// Due date for the billet
		$data['order_id'] 		= $this->order_id;
		$data['days_due_date'] 	= $this->determine_due_date();

		// Seller/Order variable description
		$billet_description = sprintf("Referente a pedido #%s na loja %s", $this->order_id, $shop_name);
		$data['seller_description'] = apply_filters('woo_paghiper_billet_description', $billet_description, $this->order_id);

		// Fixed data (doesn't change per request)
		$data['type_bank_slip']					= 'boletoA4';
		$data['open_after_day_due'] 			= $this->gateway_settings['open_after_day_due'];
		$data['early_payment_discounts_cents'] 	= $this->gateway_settings['early_payment_discounts_cents'];
		$data['early_payment_discounts_days'] 	= $this->gateway_settings['early_payment_discounts_days'];

		$data['notification_url']				= get_site_url(null, $this->base_url.'wc-api/WC_Gateway_Paghiper/');

		$data = apply_filters( 'paghiper_billet_data', $data, $this->order_id );

		if ( $this->log ) {
			wc_paghiper_add_log( $this->log, sprintf( 'Dados preparados para envio: %s', var_export($data, true) ) );
		}

		return $data;
	}

	public function create_billet() {

		if($this->has_issued_valid_billet()) {
			return false;
		}

		// Include SDK for our call
		require_once WC_Paghiper::get_plugin_path() . 'includes/paghiper-php-sdk/vendor/autoload.php';
		
		$transaction_data = $this->prepare_data_for_billet();

		$token 			= $this->gateway_settings['token'];
		$api_key 		= $this->gateway_settings['api_key'];

		$PagHiperAPI 	= new PagHiper($api_key, $token);
		$response 		= $PagHiperAPI->transaction()->create($transaction_data);

		try {

			$billet_data = get_post_meta( $this->order_id, 'wc_paghiper_data', true );

			$current_bilet = array(
				'transaction_id'			=> $response['transaction_id'],
				'value_cents'				=> $this->_convert_to_currency($response['value_cents']),
				'status'					=> $response['status'],
				'order_id'					=> $response['order_id'],
				'current_billet_due_date'	=> $response['due_date'],
				'digitable_line'			=> $response['bank_slip']['digitable_line'],
				'url_slip'					=> $response['bank_slip']['url_slip'],
				'url_slip_pdf'				=> $response['bank_slip']['url_slip_pdf'],
				'barcode'					=> $response['bank_slip']['bar_code_number_to_image']
			);

			// Define a due date for storing on the order, for future reference
			if(!array_key_exists('order_billet_due_date', $this->order_data)) {
				$current_bilet['order_billet_due_date'] = $response['due_date'];
			}

			$data = array_merge($this->order_data, $current_bilet);
			update_post_meta($this->order_id, 'wc_paghiper_data', $data);

			// Download the attachment to our storage directory
			$transaction_id = 'Boleto bancário - '.$response['transaction_id'];
			$billet_url		= $response['bank_slip']['url_slip_pdf'];

			$uploads = wp_upload_dir();
			$upload_dir = $uploads['basedir'];
			$upload_dir = $upload_dir . '/paghiper';

			$billet_pdf_file = $upload_dir.'/'.$transaction_id.'.pdf';

			if(!file_exists($billet_pdf_file)) {

				global $wp_filesystem;
				require_once ABSPATH . 'wp-admin/includes/file.php'; // for get_filesystem_method(), request_filesystem_credentials()

				if ( empty( $wp_filesystem ) ) {
					$creds = request_filesystem_credentials( admin_url(), '', FALSE, FALSE, NULL ); // @since 2.5.0
					
					// initialize the API @since 2.5.0
					WP_Filesystem( $creds );
				}
				
				$billet_download = wp_remote_get($billet_url);
				$billet_content = $billet_download['body'];

				if(get_filesystem_method() == 'direct') {
					file_put_contents( $billet_pdf_file, $billet_content, LOCK_EX );
				} else {
					$wp_filesystem->put_contents($billet_pdf_file, $billet_content);
				}

			}

			return true;
			
		} catch (\Exception $e) {
			if ( $this->log ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Erro: %s', $e->getMessage() ) );
			}
		}

		return false;

	}

	public function print_billet_html() {
		
		// Temos um boleto ja emitido com data de vencimento válida, só pegamos uma cópia
		$response = wp_remote_get($this->order_data['url_slip']);

		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$headers = $response['headers']; // array of http header lines
			$body    = $response['body']; // use the content

			echo $body;

			if ( $this->log ) {
			wc_paghiper_add_log( $this->log, sprintf( 'Boleto resgatado com sucesso para o pedido #%s.', $this->order_id ) );
			}
		} elseif( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
			if ( $this->log ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Erro: %s', $error ) );
			}
		} else {
			if ( $this->log ) {
				wc_paghiper_add_log( $this->log, 'Erro geral. Por favor, entre em contato com o suporte.' );
			}
		}

	}

	public function print_billet_barcode($print = FALSE) {

		$digitable_line = $this->_get_digitable_line();
		$barcode_number = $this->_get_barcode();

		$html = '<div class="woo_paghiper_digitable_line" style="margin-bottom: 40px;">';

		$html .= "<p style='width: 100%; text-align: center;'>Pague seu boleto usando o código de barras ou a linha digitável, se preferir:</p>";

		$barcode_url = plugins_url( "assets/php/barcode.php?codigo={$barcode_number}", plugin_dir_path( __FILE__ ) );
		$html .= ($barcode_number) ? "<img src='{$barcode_url}' title='Código de barras do boleto deste pedido.' style='max-width: 100%;'>" : '';
		$html .= ($print) ? "<strong style='font-size: 18px;'>" : "";
		$html .= "<p style='width: 100%; text-align: center;'>{$digitable_line}</p>";
		$html .= ($print) ? "</strong>" : "";

		$html .= '</div>';

		return $html;

	}

	public function printToScreen() {
		$this->create_billet();
		$this->print_billet_html();
	}

	public function printBarCode($print = FALSE) {
		$this->create_billet();
		$barcode = $this->print_billet_barcode($print);

		if($print) 
			echo $barcode;

		return $barcode;

	}

	public function _get_digitable_line() {
		return $this->order_data['digitable_line'];
	}

	public function _get_barcode() {
		return (array_key_exists('barcode', $this->order_data)) ? $this->order_data['barcode'] : NULL;
	}

	public function _get_past_due_days() {
		return $this->past_due_days;
	}

	public function _get_invalid_reason() {
		return $this->invalid_reason;
	}

	public function _get_order() {
		return $this->order;
	}

	public function _convert_to_numeric($str) {
		return preg_replace('/\D/', '', $str);
	}

	public function _convert_to_cents($str) {
		return preg_replace( '/\D/', '', number_format($str, 2, '.', ''));
	}

	public function _convert_to_currency($str) {
		return number_format ( (float) $str / 100, 2, '.', '' );
	}
}