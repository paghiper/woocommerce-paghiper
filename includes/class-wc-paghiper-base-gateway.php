<?php

class WC_Paghiper_Base_Gateway {

	private $log;
	private $timezone;

	public function __construct($gateway) {

		$this->gateway = $gateway;
		$this->order = null;

		// Define as variáveis que vamos usar e popula com os dados de configuração
		$this->days_due_date 			= $this->gateway->get_option( 'days_due_date' );
		$this->skip_non_workdays		= $this->gateway->get_option( 'skip_non_workdays' );
		$this->set_status_when_waiting 	= $this->gateway->get_option( 'set_status_when_waiting' );

		// Ativa os logs
		$this->log = wc_paghiper_initialize_log( $this->gateway->get_option( 'debug' ) );

		// Definimos o offset a ser utilizado para as operações de data
		$this->timezone = new DateTimeZone('America/Sao_Paulo');
 
        // Checamos se a moeda configurada é suportada pelo gateway
        if ( ! $this->using_supported_currency() ) { 
            add_action( 'admin_notices', array( $this, 'currency_not_supported_message' ) ); 
		} 
		
		// Show our payment details inside the order page
		if(empty( is_wc_endpoint_url('order-received') )) {
			add_action( 'woocommerce_order_details_before_order_table', array( $this, 'show_payment_instructions' ), 10, 1 );
		}
	}

	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	public function using_supported_currency() {
		return ( 'BRL' == get_woocommerce_currency() );
	}
 
    /** 
     * Adds error message when an unsupported currency is used. 
     * 
     * @return string 
     */ 
    public function currency_not_supported_message() { 
		echo sprintf('<div class="error notice"><p><strong>%s: </strong>%s <a href="%s">%s</a></p></div>', __(($this->gateway->id == 'paghiper_pix') ? 'PIX Paghiper' : 'Boleto Paghiper'), __('A moeda-padrão do seu Woocommerce não é o R$. Ajuste suas configurações aqui:'), admin_url('admin.php?page=wc-settings&tab=general'), __('Configurações de moeda'));
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
		$available = ( 'yes' == $this->gateway->get_option( 'enabled' ) ) && $this->using_supported_currency();
		$has_met_min_amount = false;
		$has_met_max_amount = false;

		$total = 0;

		if ( WC()->cart ) {
			$cart = WC()->cart;
			$total = $this->gateway->retrieve_order_total();

			$min_value = apply_filters( "woo_{$this->gateway->id}_max_value", 3, $cart );
			$max_value = apply_filters( "woo_{$this->gateway->id}_max_value", PHP_INT_MAX, $cart );

			if ( $total >= $min_value ) {
				$has_met_min_amount = true;
			}
	
			if ( $total >= $max_value ) {
				$has_met_max_amount = true;
			}
	
			if($available && $has_met_min_amount && !$has_met_max_amount) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Admin Panel Options.
	 *
	 * @return string Admin form.
	 */
	public function admin_options() {
		include 'views/html-admin-page.php';
	}

	/**
	 * Get log.
	 *
	 * @return string
	 */
	protected function get_log_view() {
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
			return '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->gateway->id ) . '-' . sanitize_file_name( wp_hash( $this->gateway->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woo-boleto-paghiper' ) . '</a>';
		}
		return '<code>woocommerce/logs/' . esc_attr( $this->gateway->id ) . '-' . sanitize_file_name( wp_hash( $this->gateway->id ) ) . '.txt</code>';
	}

	/**
	 * Gateway options.
	 */
	public function init_form_fields() {
		$shop_name = get_bloginfo( 'name' );

		$default_label 			= ($this->gateway->id == 'paghiper_pix') ? 'Ativar PIX PagHiper' : 'Ativar Boleto PagHiper';
		$default_title 			= ($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'Boleto Bancário';
		$default_description	= ($this->gateway->id == 'paghiper_pix') ? 'Pague de maneira rápida e segura com PIX' : 'Pagar com Boleto Bancário';

		$first = array(
			'enabled' => array(
				'title'   => __( $default_label, 'woo-boleto-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Ativar/Desativar', 'woo-boleto-paghiper' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Título', 'woo-boleto-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Esse campo controla o título da seção que o usuário vê durante o checkout.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( $default_title, 'woo-boleto-paghiper' )
			),
			'description' => array(
				'title'       => __( 'Descrição', 'woo-boleto-paghiper' ),
				'type'        => 'textarea',
				'description' => __( 'Esse campo controla o texto da seção que o usuário vê durante o checkout.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( $default_description, 'woo-boleto-paghiper' )
			),
			'paghiper_details' => array(
				'title' => __( 'Configurações do PagHiper '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'Boleto bancário'), 'woo-boleto-paghiper' ),
				'type'  => 'title'
			),
			'api_key' => array(
				'title'       => __( 'API Key', 'woo-boleto-paghiper' ),
				'type'        => 'text',
				'placeholder' => 'apk_',
				'description' => __( 'Chave de API para integração com a PagHiper', 'woo-boleto-paghiper' ),
			),
			'token' => array(
				'title'       => __( 'Token PagHiper', 'woo-boleto-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Extremamente importante, você pode gerar seu token em nossa pagina: Painel > Ferramentas > Token.', 'woo-boleto-paghiper' ),
			),
			'days_due_date' => array(
				'title'       => __( 'Dias corridos para o vencimento', 'woo-boleto-paghiper' ),
				'type'        => 'number',
				'description' => __( 'Número de dias para calcular a data de vencimento do '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').'. Caso a data de vencimento não seja útil, o sistema bancário considera o dia útil seguinte como data de vencimento.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => 2
			),
			'open_after_day_due' => array(
				'title'       => __( 'Dias de tolerância para pagto. do '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto'), 'woo-boleto-paghiper' ),
				'type'        => 'number',
				'description' => __( 'Ao configurar este item, será possível pagar o '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').' por uma quantidade de dias após o vencimento. O mínimo é de 5 dias e máximo de 30 dias.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => 0
			),
			'skip_non_workdays' => array(
				'title'       => __( 'Ajustar data de vencimento dos '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').' para dias úteis', 'woo-boleto-paghiper' ),
				'type'    	  => 'checkbox',
				'label'   	  => __( 'Ativar/Desativar', 'woo-boleto-paghiper' ),
				'description' => __( 'Ative esta opção para evitar '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').' com vencimento aos sábados ou domingos.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default' 	  => 'yes'
			)
		);

		$last = array(
			'extra_details' => array(
				'title' => __( 'Configurações extra', 'woo-boleto-paghiper' ),
				'type'  => 'title'
			),
			'replenish_stock' => array(
				'title'   => __( 'Restituir estoque, caso o pedido seja cancelado?', 'woo-boleto-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'O plug-in subtrai os itens comprados no pedido por padrão. Essa opção os incrementa de volta, caso o pedido seja cancelado. Ativar/Desativar', 'woo-boleto-paghiper' ),
				'default' => 'yes'
			),
			'fixed_description' => array(
				'title'   => __( 'Exibir frase customizada no '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').'?', 'woo-boleto-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Ativar/Desativar', 'woo-boleto-paghiper' ),
				'default' => 'yes'
			),
			'set_status_when_waiting' => array(
				'title'	  => __('Mudar status após emissão do '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').' para:', 'woo-boleto-paghiper'),
				'type'	  => 'select',
				'options' => $this->get_available_status(),
				'default'  => $this->get_available_status('on-hold'),

			),
			'set_status_when_paid' => array(
				'title'	  => __('Mudar status após pagamento do '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').' para:', 'woo-boleto-paghiper'),
				'type'	  => 'select',
				'options' => $this->get_available_status(),
				'default'  => $this->get_available_status('processing'),

			),
			'set_status_when_cancelled' => array(
				'title'	  => __('Mudar status após cancelamento do '.(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto').' para:', 'woo-boleto-paghiper'),
				'type'	  => 'select',
				'options' => $this->get_available_status(),
				'default'  => $this->get_available_status('cancelled'),

			),
			'debug' => array(
				'title'       => __( 'Log de depuração', 'woo-boleto-paghiper' ),
				'type'        => 'checkbox',
				'label'       => __( 'Ativa o log de erros', 'woo-boleto-paghiper' ),
				'default'     => 'yes',
				'description' => sprintf( __( 'Armazena eventos e erros, como chamadas API e exibições, dentro do arquivo %s Ative caso enfrente problemas.', 'woo-boleto-paghiper' ), $this->get_log_view() ),
			),
		);

		if($this->gateway->id == 'paghiper_pix') {
			unset($first['skip_non_workdays'], $first['open_after_day_due']);
		}

		return array_merge( $first, $last );
	}

	/**
	 * Get a list of Woocommerce status available at the installation
	 * 
	 * @return array List of status
	 */
	public function get_available_status( $needle = NULL ) {

		$order_statuses = wc_get_order_statuses();

		if($needle) {

			foreach($order_statuses as $key => $value) {
				if(strpos($key, $needle) !== FALSE) {
					return $key;
				}
			}
		}

		return ($needle) ? array_shift(array_filter($order_statuses, $needle)) : $order_statuses;

	}

	public function is_order() {

		if($this->order) {
			return $this->order;
		}

		if(absint( get_query_var( 'order-pay' ) )) {
			$order_id = absint( get_query_var( 'order-pay' ) );
			$this->order = ($order_id > 0) ? wc_get_order($order_id) : null;
			
		}

		return $this->order;

	}

	public function has_taxid_fields() {
		$order = $this->is_order();
		if(!is_null($order)) {
			return (!empty($order->get_meta( '_billing_cpf' )) || !empty($order->get_meta( '_billing_cnpj' )) || !empty($order->{'_'.$order->get_payment_method().'_cpf_cnpj'}));
		}

		return false;
	}

	public function has_payer_fields() {
		$order = $this->is_order();
		if(!is_null($order)) {
			$has_payer_fields = ($order && (!empty($order->get_billing_first_name()) || !empty($order->get_billing_company()) || !empty($order->{'_'.$order->get_payment_method().'_payer_name'})));
			
			if( (strlen($payer_cpf_cnpj) > 11 && ( empty($order->get_billing_company()) && empty($order->{'_'.$order->get_payment_method().'_payer_name'})) ) ) {
				$has_payer_fields = false;
			}

			return $has_payer_fields;
		}

		return false;
	}

	public function payment_fields() {

		echo wpautop( wp_kses_post( $this->gateway->description ) );
	 
		echo '<fieldset id="wc-' . esc_attr( $this->gateway->id ) . '-form" class="wc-paghiper-form wc-payment-form" style="background:transparent;">';
	 
		// Add this action hook if you want your custom payment gateway to support it
		do_action( 'woocommerce_paghiper_taxid_form_start', $this->gateway->id );
	 
		// Print fields only if there are no fields for the same purpose on the checkout
		$has_taxid_fields = $this->has_taxid_fields();
		if(!$has_taxid_fields && isset($_POST) && array_key_exists('post_data', $_POST)) {
			parse_str( $_POST['post_data'], $post_data );
			$has_taxid_fields = (array_key_exists('billing_cpf', $post_data) || array_key_exists('billing_cnpj', $post_data)) ? TRUE : FALSE;
		}

		if(!$has_taxid_fields) {
			echo '<div class="form-row form-row-wide paghiper-taxid-fieldset">
				<label>Número de CPF/CNPJ <span class="required">*</span></label>
				<input id="'.$this->gateway->id.'_cpf_cnpj" name="_'.$this->gateway->id.'_cpf_cnpj" class="paghiper_tax_id" type="text" autocomplete="off">
				</div>
				<div class="clear"></div>';
		}

		if(array_key_exists('_'.$this->gateway->id.'_cpf_cnpj', $_POST)) {
			$payer_cpf_cnpj_value = $_POST['_'.$this->gateway->id.'_cpf_cnpj'];
		} elseif(isset($post_data) && is_array($post_data) && array_key_exists('billing_cpf', $post_data)) {
			$payer_cpf_cnpj_value = $post_data['billing_cnpj'];
		} else {
			$payer_cpf_cnpj_value = NULL;
		}

		$payer_cpf_cnpj = preg_replace('/\D/', '', sanitize_text_field($payer_cpf_cnpj_value));

		$has_payer_fields = $this->has_payer_fields();
		if(!$has_payer_fields) {
			$has_payer_fields = ((!is_null($payer_cpf_cnpj) && strlen($payer_cpf_cnpj) > 11 && !isset($post_data['billing_company'])) || !isset($post_data['_'.$this->gateway->id.'_payer_name']));
		}

		if(!$has_payer_fields) {
				
			echo '<div class="form-row form-row-wide paghiper-payername-fieldset">
				<label>Nome do pagador <span class="required">*</span></label>
				<input id="'.$this->gateway->id.'_payer_name" name="_'.$this->gateway->id.'_payer_name" type="text" autocomplete="off">
				</div>
				<div class="clear"></div>';
		}
	 
		do_action( 'woocommerce_paghiper_taxid_form_end', $this->gateway->id );
	 
		echo '<div class="clear"></div></fieldset>';
	}

	public function validate_fields() {

		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-validation.php';
		$validateAPI = new WC_PagHiper_Validation;

		$taxid_post_keys = ['_'.$this->gateway->id.'_cpf_cnpj', 'billing_cpf', 'billing_cnpj'];
		$not_empty_keys = [];
		$valid_keys = [];
		$current_taxid = null;

		if(!$this->has_taxid_fields()) {

			foreach($taxid_post_keys as $taxid_post_key) {
				if( (array_key_exists($taxid_post_key, $_POST) && !empty($_POST[$taxid_post_key])) ) {
					$not_empty_keys[] = $taxid_post_key;
	
					$maybe_valid_taxid = preg_replace('/\D/', '', sanitize_text_field($_POST[$taxid_post_key]));
					
					// TODO: Check if item key exists in order meta too
					if($validateAPI->validate_taxid($maybe_valid_taxid)) {
						$valid_keys[] = $taxid_post_key;

						if( isset($current_taxid) ) {
							$current_taxid = (strlen($current_taxid) < strlen($maybe_valid_taxid)) ? $maybe_valid_taxid : $current_taxid;
						} else {
							$current_taxid = $maybe_valid_taxid;
						}
						
					}
				}
			}
 
			if( empty($not_empty_keys) ) {
				wc_clear_notices();
				wc_add_notice(  '<strong>Número de CPF</strong> não informado!', 'error' );
			}
		}

		if( empty($valid_keys) ) {
			if(strlen($current_taxid) > 11) {
				wc_clear_notices();
				wc_add_notice(  '<strong>Número de CNPJ</strong> inválido!', 'error' );

				$taxid_payer_keys 	= ['_'.$this->gateway->id.'_payer_name', 'company_name', 'billing_first_name', 'billing_last_name'];
				$taxid_payer 		= null;
				foreach($taxid_payer_keys as $taxid_payer_key) {
					// TODO: Check if item key exists in order meta too
					if( (array_key_exists($taxid_payer_key, $_POST) && !empty($_POST[$taxid_payer_key])) ) {
						$taxid_payer = sanitize_text_field($_POST[$taxid_payer_key]);
					}
				}

				if(!$taxid_payer || empty($taxid_payer)) {
					wc_clear_notices();
					wc_add_notice(  'Ops! Precisamos também do seu <strong>nome</strong>.', 'error' );
				}
			} else {
				if(!empty($not_empty_keys)) {
					wc_clear_notices();

					if(strlen($maybe_valid_taxid) > 11) {
						wc_add_notice(  '<strong>Número de CNPJ</strong> inválido!', 'error' );
					} else {
						wc_add_notice(  '<strong>Número de CPF</strong> inválido!', 'error' );
					}
				}
			}
		}

		if( empty($not_empty_keys) || empty($valid_keys) ) {
			return false;
		}

		return true;

	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id, $is_frontend = true ) {

		$order = wc_get_order( $order_id );
		$taxid_keys = ["_{$this->gateway->id}_cpf_cnpj", "_{$this->gateway->id}_payer_name"];

		foreach($taxid_keys as $taxid_key) {
			if(isset($_POST[$taxid_key]) && !empty($_POST[$taxid_key])) {

				$taxid_value = sanitize_text_field($_POST[$taxid_key]);
				$order->update_meta_data( $taxid_key, $taxid_value );
				$order->save();
			}
		}

		// Generates ticket data.
		$this->populate_initial_billet_date( $order );

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			if ( $is_frontend ) {
				WC()->cart->empty_cart();
			}

			$url = $order->get_checkout_order_received_url();
		} else {
			global $woocommerce;

			$woocommerce->cart->empty_cart();

			$url = add_query_arg( 'key', $order->get_order_key(), add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) );
		}

		// Gera um boleto e guarda os dados, pra reutilizarmos.
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';

		$paghiperTransaction = new WC_PagHiper_Transaction( $order_id );
		$transaction = $paghiperTransaction->create_transaction();

		if($transaction) {

			// Mark as on-hold (we're awaiting the ticket).
			$waiting_status = (!empty($this->set_status_when_waiting)) ? $this->set_status_when_waiting : 'on-hold';
			$order->update_status( $waiting_status, __( 'PagHiper: '. ($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'Boleto' .' gerado e enviado por e-mail.', 'woo-boleto-paghiper' ) );

		} else {

			// Prints a notice, case order total surpasses our normal commercial limits
			$order_total = round(floatval($order->get_total()));
			if($order_total > 9000) {
				$order->add_order_note( sprintf( __( 'Atenção! Total da transação excede R$ 9.000. Caso ainda não o tenha feito, entre em contato com nossa equipe comercial para liberação através do e-mail <a href="comercial@paghiper.com" target="_blank">comercial@paghiper.com</a>', 'woo_paghiper' ) ) );
			}

			if ( $this->log ) {
				wc_paghiper_add_log( 
					$this->log, 
					sprintf( 'Pedido #%s: Não foi possível gerar o %s. Detalhes: %s', 
						$order_id, 
						(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto'), 
						var_export($transaction, true) 
					) 
				);
			}
			
			wc_add_notice( 'Não foi possível gerar o seu '. (($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto'), 'error' );
			return;

		}

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $url
		);
	}

	/**
	 * Generate ticket data.
	 *
	 * @param  object $order Order object.
	 */
	public function populate_initial_billet_date( $order ) {

		//TODO
		// Ticket data.
		$data				= array();
		$due_date_config 	= absint( $this->days_due_date );
		$gateway_name 		= $order->get_payment_method();
		
		$transaction_due_date = new DateTime;
		$transaction_due_date->setTimezone($this->timezone);
		if($due_date_config > 0)
			$transaction_due_date->modify( "+{$due_date_config} days" );

		// Maybe skip non-workdays as per configuration
		$maybe_skip_non_workdays = ($gateway_name == 'paghiper_pix') ? null : $this->skip_non_workdays;
		$transaction_due_date = wc_paghiper_add_workdays($transaction_due_date, $order, 'date', $maybe_skip_non_workdays);
		$data['order_transaction_due_date'] = $transaction_due_date->format('Y-m-d');
		$data['transaction_type'] = ($gateway_name == 'paghiper_pix') ? 'pix' : 'billet';

		if ( $this->log ) {
			wc_paghiper_add_log( 
				$this->log, 
				sprintf( 'Pedido #%s: Dados iniciais para o %s preparados. Detalhes: %s', 
					$order->get_id(), 
					(($this->gateway->id == 'paghiper_pix') ? 'PIX' : 'boleto'), 
					var_export($data, true) 
				) 
			);
		}

		$order->update_meta_data( 'wc_paghiper_data', $data );
		$order->save();

		if(function_exists('update_meta_cache'))
			update_meta_cache( 'shop_order', $order->get_id() );

		return;
	}

	/**
	 * Output for the order received page.
	 *
	 * @return string Thank You message.
	 */
	public function show_payment_instructions($order) {

		$order 		= (is_numeric($order)) ? wc_get_order($order) : $order;
		$order_id 	= $order->get_id();
		$order_payment_method = $order->get_payment_method();
		$order_status = (strpos($order->get_status(), 'wc-') === false) ? 'wc-'.$order->get_status() : $order->get_status();

		// Locks this action for misfiring when order is placed with other gateways
		if(!in_array($order_payment_method, ['paghiper', 'paghiper_billet', 'paghiper_pix'])) {
			return;
		}

		// Fallback for old billet transactions
		if($order->get_payment_method() !== $this->gateway->id && $order->get_payment_method() !== 'paghiper') {
			return;
		}

		// Breaks execution if order is not in the right state
		if(apply_filters('woo_paghiper_pending_status', $this->set_status_when_waiting, $order) !== $order_status) {
			return;
		}

		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';

		$paghiperTransaction = new WC_PagHiper_Transaction( $order_id );
		$paghiperTransaction->printBarCode(true);

		if($order->get_payment_method() !== 'paghiper_pix') {

			$html = '<div class="woocommerce-message">';
			$html .= sprintf( '<a class="button button-primary wc-forward" href="%s" target="_blank" style="display: block !important; visibility: visible !important;">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->get_order_key() ) ), __( 'Pagar o Boleto', 'woo-boleto-paghiper' ) );
	
			$message = sprintf( __( '%sAtenção!%s Você NÃO vai receber o boleto pelos Correios.', 'woo-boleto-paghiper' ), '<strong>', '</strong>' ) . '<br />';
			$message .= __( 'Clique no link abaixo e pague o boleto pelo seu aplicativo de Internet Banking .', 'woo-boleto-paghiper' ) . '<br />';
			$message .= __( 'Se preferir, você pode imprimir e pagar o boleto em qualquer agência bancária ou lotérica.', 'woo-boleto-paghiper' ) . '<br />';
	
			$html .= apply_filters( 'woo_paghiper_thankyou_page_message', $message );
	
			$html .= '<strong style="display: block; margin-top: 15px; font-size: 0.8em">' . sprintf( __( 'Data de vencimento do Boleto: %s.', 'woo-boleto-paghiper' ), date( 'd/m/Y', time() + ( absint( $this->days_due_date ) * 86400 ) ) ) . '</strong>';
	
			$html .= '</div>';
	
			echo $html;

		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param  object $order         Order object.
	 * @param  bool   $sent_to_admin Send to admin.
	 *
	 * @return string                Billet instructions.
	 */
	function email_instructions( $order, $sent_to_admin ) {

		$order_status = (strpos($order->get_status(), 'wc-') === false) ? 'wc-'.$order->get_status() : $order->get_status();
		$order_payment_method = $order->get_payment_method();

		if ( $sent_to_admin || apply_filters('woo_paghiper_pending_status', $this->set_status_when_waiting, $order) !== $order_status || strpos($order_payment_method, 'paghiper') === false || $order_payment_method !== $this->gateway->id) {
			return;
		}

		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';
		$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );

		$html = '<div class="woo-paghiper-boleto-details" style="text-align: center;">';
		$html .= '<h2>' . __( 'Pagamento', 'woo-boleto-paghiper' ) . '</h2>';

		$html .= '<p class="order_details">';

		$message = $paghiperTransaction->printBarCode();

		if($order_payment_method !== 'paghiper_pix') {

			$message .= sprintf( __( '%sAtenção!%s Você NÃO vai receber o boleto pelos Correios.', 'woo-boleto-paghiper' ), '<strong>', '</strong>' ) . '<br />';
			$message .= __( 'Se preferir, você pode imprimir e pagar o boleto em qualquer agência bancária ou lotérica.', 'woo-boleto-paghiper' ) . '<br />';
	
			$html .= apply_filters( 'woo_paghiper_email_instructions', $message );
	
			$html .= '<br />' . sprintf( '<a class="button alt" href="%s" target="_blank">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->get_order_key() ) ), __( 'Veja o boleto completo &rarr;', 'woo-boleto-paghiper' ) ) . '<br />';
	
			$html .= '<strong style="font-size: 0.8em">' . sprintf( __( 'Data de Vencimento: %s.', 'woo-boleto-paghiper' ), date( 'd/m/Y', time() + ( absint( $this->days_due_date ) * 86400 ) ) ) . '</strong>';
	
			$html .= '</p>';
			$html .= '</div>';

		} else {
			$html .= apply_filters( 'woo_paghiper_email_instructions', $message );
		}

		echo $html;
	}
}