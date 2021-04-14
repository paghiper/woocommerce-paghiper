<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PagHiper\PagHiper;

class WC_PagHiper_Transaction {

	private $order;
	private $order_id;
	private $order_data;
	private $gateway_id;
	private $gateway_settings;
	private $invalid_reason;
	private $past_due_days;
	private $log;
	private $timezone;

	public function __construct($order_id) {

		global $wp_query;

		// Pega a referência do pedido
		$this->order_id = $order_id;

		// Pegamos o pedido completo
		$this->order = new WC_Order( $order_id );
		$this->order_status = (strpos($this->order->get_status(), 'wc-') === false) ? 'wc-'.$this->order->get_status() : $this->order->get_status();

		// Pega a configuração atual do plug-in.
		$this->gateway_id = $this->order->get_payment_method();
		$this->gateway_settings = ($this->gateway_id == 'paghiper_pix') ? get_option( 'woocommerce_paghiper_pix_settings' ) : get_option( 'woocommerce_paghiper_billet_settings' );

		// Inicializa logs, caso ativados
		$this->log = wc_paghiper_initialize_log( $this->gateway_settings[ 'debug' ] );

		// Pegamos a meta do pedido
		if(function_exists('update_meta_cache'))
			update_meta_cache( 'shop_order', $this->order_id );

		$order_meta_data = get_post_meta( $this->order_id, 'wc_paghiper_data', true );
		$this->order_data = (is_array($order_meta_data)) ? $order_meta_data : [];

		// Compatibility with pre v2.1 keys
		if(array_key_exists('order_billet_due_date', $this->order_data) && !array_key_exists('order_transaction_due_date', $this->order_data)) {
			$this->order_data['order_transaction_due_date'] = $this->order_data['order_billet_due_date'];
			$this->order_data['current_transaction_due_date'] = $this->order_data['current_billet_due_date'];
			$this->order_data['transaction_type'] = 'billet';
		}

		// Formulamos a URL-base a ser utilizada
		$this->base_url = WC_Paghiper::get_base_url();

		// Definimos o offset a ser utilizado para as operações de data
		$this->timezone = new DateTimeZone('America/Sao_Paulo');

	}
	
	public function has_issued_valid_transaction() {

		$order_date = DateTime::createFromFormat('Y-m-d', strtok($this->order->order_date, ' '), $this->timezone);
		$new_request = FALSE;

		// Novo request caso o método de pagamento tenha mudado
		if( isset($this->order_data['transaction_type']) && ($this->order_data['transaction_type'] == 'pix' && $this->gateway_id !== 'paghiper_pix') ) {

			$new_request = TRUE;

			if ( $this->log ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Pedido #%s: Método de pagamento é PIX mas a transação gerada não é.', $this->order_id ) );
			}
		}

		// Define data de vencimento, caso exista
		if(empty($this->order_data['order_transaction_due_date']) || empty($this->order_data['current_transaction_due_date'])) {

			$new_request = TRUE;

			if ( $this->log ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Pedido #%s: Data de vencimento não presente no banco.', $this->order_id ) );
			}

		} else {

			$original_due_date = DateTime::createFromFormat('Y-m-d', $this->order_data['order_transaction_due_date'], $this->timezone);
			$current_billet_due_date = DateTime::createFromFormat('Y-m-d', $this->order_data['current_transaction_due_date'], $this->timezone);

			$different_total = ( $this->order->get_total() == $this->order_data['value_cents'] ? NULL : TRUE );
			$different_due_date = ( $this->order_data['order_transaction_due_date'] == $this->order_data['current_transaction_due_date'] ? NULL : TRUE );

			$today_date = new \DateTime();
			$today_date->setTimezone($this->timezone);
			$this->past_due_days = ($original_due_date && $current_billet_due_date) ? (int) $today_date->diff($original_due_date)->format("%r%a") : NULL ;

			if($different_due_date) {

				// Check if date is different
				$due_date_weekday = $current_billet_due_date->format('N');

				if ($current_billet_due_date->format('N') == 1 && $original_due_date->format('N') > 5) {
					
					$paghiper_data = get_post_meta( $this->order_id, 'wc_paghiper_data', true );
					$paghiper_data['order_transaction_due_date'] = $current_billet_due_date->format( 'Y-m-d' );

					$update = update_post_meta( $this->order_id, 'wc_paghiper_data', $paghiper_data );
					if(function_exists('update_meta_cache'))
						update_meta_cache( 'shop_order', $this->order_id );

					$this->order_data = $paghiper_data;

					if($update) {
						$this->order->add_order_note( sprintf( __( 'Data de vencimento ajustada para %s', 'woo_paghiper' ), $current_billet_due_date->format('d/m/Y') ) );
					} else {
						$this->order->add_order_note( sprintf( __( 'Data de vencimento deveria ser ajustada para %s mas houve um erro ao salvar a nova data.', 'woo_paghiper' ), $current_billet_due_date->format('d/m/Y') ) );
					}

					$log_message = 'Pedido #%s: Data de vencimento do boleto não bate com a informada no pedido. Cheque a opção "Vencimento em finais de semana" no <a href="https://www.paghiper.com/painel/prazo-vencimento-boleto/" target="_blank">Painel da PagHiper</a>.';
					wc_paghiper_add_log( $this->log, sprintf( $log_message, $this->order_id ) );


					$error = __( '<strong>Boleto PagHiper</strong>: 
					A data de vencimento do boleto foi configurada para um final de semana mas o boleto foi emitido para segunda-feira. 
					Cheque a opção "Vencimento em finais de semana" no <a href="https://www.paghiper.com/painel/prazo-vencimento-boleto/" target="_blank">Painel da PagHiper</a> ou 
					ative nas configurações do plugin a correção de datas para que o vencimento não caia em finais de semana', 'woo_paghiper' );
					set_transient("woo_paghiper_due_date_order_errors_{$this->order_id}", $error, 0);

					$different_due_date = NULL;

				} else {

					$this->invalid_reason = 'different_due_date';
	
					if ( $this->log ) {
						$log_message = 'Pedido #%s: Data de vencimento da transação não bate com a informada no pedido. Uma nova transação será gerado.';
						wc_paghiper_add_log( $this->log, sprintf( $log_message, $this->order_id ) );
					}
				}



			}
			
			if($different_total) {

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
		$order_due_date 	= $this->order_data['order_transaction_due_date'];
		$transaction_days_due	= (!empty($this->gateway_settings['days_due_date'])) ? $this->gateway_settings['days_due_date'] : 5;

		$today_date = new \DateTime();
		$today_date->setTimezone($this->timezone);

		// TODO: Implement better logic here
		if(!empty($order_due_date)) {

			// Calcular dias de diferença entre a data de vencimento e a data atual
			$original_due_date = DateTime::createFromFormat('Y-m-d', $order_due_date, $this->timezone);
			$transaction_due_days = ($today_date && $original_due_date) ? $today_date->diff($original_due_date) : NULL;

			$transaction_due_date = $original_due_date;

		} else {

			$order_data = get_post_meta( $this->order_id, 'wc_paghiper_data', true );
			$order_data = (is_array($order_data)) ? $order_data : array();

			// Calcular dias entre a data do pedido e os dias para vencimento na configuração
			$transaction_due_date = $today_date;
			$transaction_due_date->modify( "+{$billet_days_due} days" );

			$transaction_due_days = $billet_due_date->days;

			$order_data['order_transaction_due_date'] = $transaction_due_date->format( 'Y-m-d' );		
			$this->order_data = $order_data;

			$update = update_post_meta( $this->order_id, 'wc_paghiper_data', $order_data );
			if(function_exists('update_meta_cache'))
				update_meta_cache( 'shop_order', $this->order_id );

			if(!$update) {
				if ( $this->log ) {
					wc_paghiper_add_log( $this->log, sprintf( 'Não foi possível guardar a data de vencimento: %s', var_export( $update, true) ) );
					wc_paghiper_add_log( $this->log, sprintf( 'Dados a guardar: %s', var_export( $order_data, true) ) );
				}
			}

			
		}

		$transaction_due_days = wc_paghiper_add_workdays($transaction_due_date, $this->order, $this->gateway_settings['skip_non_workdays'], 'days');

		return $transaction_due_days;
	}

	public function prepare_data_for_transaction() {

		if ( empty( $this->order ) || !in_array($this->order->get_payment_method(), ['paghiper', 'paghiper_billet', 'paghiper_pix']) ) {
			return false;
		}

		// Sets the boleto details.
		$shop_name = get_bloginfo( 'name' );

		// Set the ticket total.
		$order_line_total = 0;

		// Client data.
		if(!empty($this->order->billing_persontype)) {
			$data['payer_name'] = ($this->order->billing_persontype == 2 && !empty($this->order->billing_company)) ? $this->order->billing_company : $this->order->billing_first_name . ' ' . $this->order->billing_last_name;
			$data['payer_cpf_cnpj'] = ($this->order->billing_persontype == 1) ? $this->order->billing_cpf : $this->order->billing_cnpj ;
		} else {
			// Get default field options if not using Brazilian Market on WooCommerce
			if(!empty($this->order->billing_cnpj) && !empty($this->order->billing_company)) {
				$data['payer_name'] = $this->order->billing_company;
				$data['payer_cpf_cnpj'] = $this->order->billing_cnpj;
			} else {

				// Get default field options if not using Brazilian Market on WooCommerce
				if(!empty($this->order->billing_cnpj) && !empty($this->order->billing_company)) {
					$data['payer_name'] = $this->order->billing_company;
					$data['payer_cpf_cnpj'] = $this->order->billing_cnpj;
				} else {
					$data['payer_name'] = $this->order->billing_first_name . ' ' . $this->order->billing_last_name;
					$data['payer_cpf_cnpj'] = $this->order->billing_cpf;
				}
			}
		}

		// Override data with our gateway fields
		$checkout_payer_cpf_cnpj = get_post_meta($this->order_id, '_'.$this->gateway_id.'_cpf_cnpj', true);
		if(!empty($checkout_payer_cpf_cnpj)) {
			$data['payer_cpf_cnpj'] = $checkout_payer_cpf_cnpj;
		}

		$checkout_payer_name = get_post_meta($this->order_id, '_'.$this->gateway_id.'_payer_name', true);
		if(!empty($checkout_payer_name)) {
			$data['payer_name'] = $checkout_payer_name;
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
			$product_quantity	= (is_int($item->get_quantity())) ? $item->get_quantity() : 1;
			$individual_price	= $item->get_subtotal() / $product_quantity;
			$product_price		= $this->_convert_to_cents(($individual_price > 0) ? $individual_price : 0);

			$data['items'][] = array(
				'item_id'		=> $product_id,
				'description'	=> $product_name,
				'quantity'		=> $product_quantity,
				'price_cents'	=> $product_price
			);

			$item_total = $individual_price * $product_quantity;
			$order_line_total += ($item_total > 0) ? $item_total : 0;

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
		$order_discount	= 0;

		// Taxes and additional costs
		$order_taxes 		= $this->order->get_total_tax();
		$taxes_description 	= 'Taxas e impostos';

		// Conciliate order, in order to avoind conflict with third-party plugins and custom solutions
		// We do this to facilitate integration, even when users implement stuff using unorthodox methods
		$order_total 		= round(floatval($this->order->get_total()), 2);
		$simulated_total	= round(($order_line_total + $order_shipping + $order_taxes), 2);

		// If our sum is lower than the order total:
		if($order_total > $simulated_total) {

			$order_taxes 	= $order_total - $simulated_total;

		// If our sum is higher than the order total:
		} elseif($order_total < $simulated_total) {

			$order_discount = $simulated_total - $order_total;

		}

		if($order_discount > 0) {
			$data['discount_cents']	= $this->_convert_to_cents($order_discount);
		}

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
		$data['seller_description'] = apply_filters('woo_paghiper_transaction_description', $billet_description, $this->order_id);

		// Fixed data (doesn't change per request)
		$data['type_bank_slip']					= 'boletoA4';
		$data['open_after_day_due'] 			= $this->gateway_settings['open_after_day_due'];
		$data['early_payment_discounts_cents'] 	= $this->gateway_settings['early_payment_discounts_cents'];
		$data['early_payment_discounts_days'] 	= $this->gateway_settings['early_payment_discounts_days'];

		$data['notification_url']				= get_site_url(null, $this->base_url.'wc-api/WC_Gateway_Paghiper/?gateway=').(($this->gateway_id == 'paghiper_pix') ? 'pix' : 'billet');
		$data['transaction_type']				= ($this->gateway_id == 'paghiper_pix') ? 'pix' : 'billet';

		$data = apply_filters( 'paghiper_transaction_data', $data, $this->order_id );

		if ( $this->log ) {
			wc_paghiper_add_log( $this->log, sprintf( 'Dados preparados para envio: %s', var_export($data, true) ) );
		}

		return $data;
	}

	public function create_transaction() {

		if($this->has_issued_valid_transaction()) {
			return false;
		}

		if(apply_filters('woo_paghiper_pending_status', $this->gateway_settings['set_status_when_waiting'], $this->order) !== $this->order_status && !in_array($this->order_status, ['wc-pending', 'pending'])) {
			return false;
		}

		// Include SDK for our call
		require_once WC_Paghiper::get_plugin_path() . 'includes/paghiper-php-sdk/build/vendor/scoper-autoload.php';
		
		$transaction_data = $this->prepare_data_for_transaction();
		if(!$transaction_data) {
			return false;
		}

		$token 			= $this->gateway_settings['token'];
		$api_key 		= $this->gateway_settings['api_key'];

		try {

			$PagHiperAPI 	= new PagHiper($api_key, $token);
			$response 		= $PagHiperAPI->transaction()->create($transaction_data);

			$billet_data = get_post_meta( $this->order_id, 'wc_paghiper_data', true );

			$transaction_base_data = array(
				'transaction_id'				=> $response['transaction_id'],
				'value_cents'					=> $this->_convert_to_currency($response['value_cents']),
				'status'						=> $response['status'],
				'order_id'						=> $response['order_id'],
				'current_transaction_due_date'	=> $response['due_date'],
			);


			if($this->gateway_id == 'paghiper_pix') {
				$transaction = array(
					'qrcode_base64'		        => $response['pix_code']['qrcode_base64'],
					'qrcode_image_url'	        => $response['pix_code']['qrcode_image_url'],
					'emv'				        => $response['pix_code']['emv'],
					'bacen_url'			        => $response['pix_code']['bacen_url'],
					'pix_url'			        => $response['pix_code']['pix_url'],
					'transaction_type'			=> 'pix'
				);
			} else {
				$transaction = array(
					'digitable_line'			=> $response['bank_slip']['digitable_line'],
					'url_slip'					=> $response['bank_slip']['url_slip'],
					'url_slip_pdf'				=> $response['bank_slip']['url_slip_pdf'],
					'barcode'					=> $response['bank_slip']['bar_code_number_to_image'],
					'transaction_type'			=> 'billet'
				);
			}

			$current_billet = array_merge($transaction_base_data, $transaction);

			// Define a due date for storing on the order, for future reference
			if(!array_key_exists('order_transaction_due_date', $this->order_data)) {
				$current_billet['order_transaction_due_date'] = $response['due_date'];
			}

			$order_data = (is_array($this->order_data)) ? $this->order_data : array();
			$data = array_merge($this->order_data, $current_billet);

			$update = update_post_meta($this->order_id, 'wc_paghiper_data', $data);
			if(function_exists('update_meta_cache'))
				update_meta_cache( 'shop_order', $this->order_id );

			$this->order_data = $data;

			if(!$update) {
				if ( $this->log ) {
					wc_paghiper_add_log( $this->log, sprintf( 'Não foi possível guardar os dados do boleto: %s', var_export( $update, true) ) );
					wc_paghiper_add_log( $this->log, sprintf( 'Dados a guardar: %s', var_export( $data, true) ) );
				}
			}

			// Download the attachment to our storage directory
			$transaction_id = 'Boleto bancário - '.$response['transaction_id'];
			$billet_url		= $response['bank_slip']['url_slip_pdf'];

			$uploads = wp_upload_dir();
			$upload_dir = $uploads['basedir'];
			$upload_dir = $upload_dir . '/paghiper';

			$billet_pdf_file = $upload_dir.'/'.$transaction_id.'.pdf';

			// Don't try downloading PDF files for PIX transacitons
			if(in_array($this->gateway_id, ['paghiper_billet', 'paghiper']) && !file_exists($billet_pdf_file)) {

				global $wp_filesystem;
				require_once ABSPATH . 'wp-admin/includes/file.php'; // for get_filesystem_method(), request_filesystem_credentials()

				if ( empty( $wp_filesystem ) ) {
					$creds = request_filesystem_credentials( admin_url(), '', FALSE, FALSE, NULL ); // @since 2.5.0
					
					// initialize the API @since 2.5.0
					WP_Filesystem( $creds );
				}
				
				$billet_download = wp_remote_get($billet_url);

				if ( is_array( $billet_download ) && ! is_wp_error( $billet_download ) ) {

					$billet_content = $billet_download['body'];
	
					if(get_filesystem_method() == 'direct') {
						file_put_contents( $billet_pdf_file, $billet_content, LOCK_EX );
					} else {
						$wp_filesystem->put_contents($billet_pdf_file, $billet_content);
					}

				} elseif( is_wp_error( $billet_download ) ) {
					if ( $this->log ) {
						wc_paghiper_add_log( $this->log, sprintf( 'Erro: %s', $billet_download->get_error_message() ) );
					}
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

	public function print_transaction_html() {

		// Checamos se o pedido não é um PIX
		if($this->order_data['transaction_type'] == 'pix') {

			$ico = 'billet-cancelled.png';
			$title = 'Este pedido não foi feito com boleto!';
			$message = 'A forma de pagamento deste pedido é PIX. Cheque seu e-mail ou sua área de pedidos para informações sobre como pagar.';
			echo print_screen($ico, $title, $message);

		}
		
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

	public function print_transaction_barcode($print = FALSE) {

		$digitable_line = $this->_get_digitable_line();

		if(!$digitable_line)
			return false;

		$due_date = (DateTime::createFromFormat('Y-m-d', $this->order_data['order_transaction_due_date']))->format('d/m/Y');

		$html = '<div class="woo_paghiper_digitable_line" style="margin-bottom: 40px;">';
		$assets_url = wc_paghiper_assets_url().'images';

		if($this->gateway_id !== 'paghiper_pix') :
			
			$barcode_number = $this->_get_barcode();
			if(!$barcode_number) {

				$html .= "<img style='max-width: 200px;' src='{$assets_url}/pix-cancelled.png'>";
				$html .= sprintf('<p><strong>%s</strong></p>', 'Não foi possível emitir seu PIX');
				$html .= sprintf('<p>%s</p>', 'Entre em contato com o suporte informando o erro 0x00007b');

				wc_paghiper_add_log( $this->log, sprintf( 'PIX não disponível para exibição do código de barras. Pedido #%s', $order_id ) );
				
			} else {

				$barcode_url = plugins_url( "assets/php/barcode.php?codigo={$barcode_number}", plugin_dir_path( __FILE__ ) );
				$html .= "<p style='width: 100%; text-align: center;'>Pague seu boleto usando o código de barras ou a linha digitável, se preferir:</p>";
				$html .= ($barcode_number) ? "<img src='{$barcode_url}' title='Código de barras do boleto deste pedido.' style='max-width: 100%;'>" : '';
				$html .= ($print) ? "<strong style='font-size: 18px;'>" : "";
				$html .= "<p style='width: 100%; text-align: center;'>{$digitable_line}</p>";
				$html .= ($print) ? "</strong>" : "";

			}

		else :

			$barcode_url = $this->_get_barcode();
			if(!$barcode_url) {

				$html .= "<img style='max-width: 200px;' src='{$assets_url}/billet-cancelled.png'>";
				$html .= sprintf('<p><strong>%s</strong></p>', 'Não foi possível exibir o seu boleto');
				$html .= sprintf('<p>%s</p>', 'Entre em contato com o suporte informando o erro 0x0000e9');

				wc_paghiper_add_log( $this->log, sprintf( 'Boleto não disponível para exibição do código de barras. Pedido #%s', $order_id ) );
				
			} else {

				$html .= "<p style='width: 100%; text-align: center;'>Efetue o pagamento PIX usando o <strong>código de barras</strong> ou usando <strong>PIX copia e cola</strong>, se preferir:</p>";
	
				if($print) {
					$html .= '<div class="pix-container">';
					$html .= ($barcode_url) ? "<div class='qr-code'><img src='{$barcode_url}' title='Código de barras do PIX deste pedido.'><br>Data de vencimento: <strong>{$due_date}</strong></div>" : '';
					$html .= '<div class="instructions"><ul>
						<li><span>Abra o app do seu banco ou instituição financeira e <strong>entre no ambiente Pix</strong>.</span></li>
						<li><span>Escolha a opção <strong>Pagar com QR Code</strong> e escanele o código ao lado.</span></li>
						<li><span>Confirme as informações e finalize o pagamento.</span></li>
					</ul></div>';
					$html .= '</div>';
					$html .= sprintf('<div class="paghiper-pix-code" onclick="copyPaghiperEmv()"><p>Pagar com PIX copia e cola - <button>Clique para copiar</button></p><div class="textarea-container"><textarea readonly rows="3">%s</textarea></div></div>', $digitable_line);
				} else {
					$html .= ($barcode_url) ? "<p style='text-align: center;'><img src='{$barcode_url}' title='Código de barras do PIX deste pedido.' style='max-width: 100%; margin: 0 auto;'></p>" : '';
					$html .= "<p style='width: 100%; text-align: center;'>Data de vencimento: <strong>{$due_date}</strong></p>";
					$html .= "<p style='width: 100%; text-align: center;'>Seu código PIX: {$digitable_line}</p>";
				}
	
				$html .= "<p style='width: 100%; text-align: center; margin-top: 20px;'>Após o pagamento, podemos levar alguns segundos para confirmar o seu pagamento.<br>Você será avisado(a) assim que isso ocorrer!</p>";
			
			}
		endif;

		$html .= '</div>';

		return $html;

	}

	public function printToScreen() {
		$this->create_transaction();
		$this->print_transaction_html();
	}

	public function printBarCode($print = FALSE) {
		$this->create_transaction();
		$barcode = $this->print_transaction_barcode($print);

		if($print) 
			echo $barcode;

		return $barcode;

	}

	public function _get_digitable_line() {
		return ($this->gateway_id == 'paghiper_pix') ? $this->order_data['emv'] : $this->order_data['digitable_line'];
	}

	public function _get_barcode() {
		return (
			($this->gateway_id == 'paghiper_pix') ? $this->order_data['qrcode_image_url'] :
				((array_key_exists('barcode', $this->order_data)) ? $this->order_data['barcode'] : NULL)
		);
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