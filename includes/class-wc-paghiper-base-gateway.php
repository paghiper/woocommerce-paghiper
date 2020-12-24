<?php

class WC_Paghiper_Base_Gateway {

	private $log;
	private $timezone;

	public function __construct($gateway) {

		$this->gateway = $gateway;

		// Define as variáveis que vamos usar e popula com os dados de configuração
		$this->title       				= $this->gateway->get_option( 'title' );
		$this->description 				= $this->gateway->get_option( 'description' );
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
     * Adds error message when an unsupported currency is used. 
     * 
     * @return string 
     */ 
    public function currency_not_supported_message() { 
		echo sprintf('<div class="error notice"><p><strong>%s: </strong>%s <a href="%s">%s</a></p></div>', __(($this->gateway->id == 'paghiper_pix') ? 'PIX Paghiper' : 'Boleto Paghiper'), __('A moeda-padrão do seu Woocommerce não é o R$. Ajuste suas configurações aqui:'), admin_url('admin.php?page=wc-settings&tab=general'), __('Configurações de moeda'));
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
		$default_description	= ($this->gateway->id == 'paghiper_pix') ? 'Pague rapidamente com PIX' : 'Pagar com Boleto Bancário';

		$first = array(
			'enabled' => array(
				'title'   => __( $enabled_label, 'woo-boleto-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Ativar/Desativar', 'woo-boleto-paghiper' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Título', 'woo-boleto-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Esse campo controla o título da seção que o usuário vê durante o checkout.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( $enabled_title, 'woo-boleto-paghiper' )
			),
			'description' => array(
				'title'       => __( 'Descrição', 'woo-boleto-paghiper' ),
				'type'        => 'textarea',
				'description' => __( 'Esse campo controla o texto da seção que o usuário vê durante o checkout.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( $default_description, 'woo-boleto-paghiper' )
			),
			'paghiper_details' => array(
				'title' => __( 'Configurações do PagHiper Boleto Bancário', 'woo-boleto-paghiper' ),
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
				'title'	  => __('Mudar status após emissão do boleto para:', 'woo-boleto-paghiper'),
				'type'	  => 'select',
				'options' => $this->get_available_status(),
				'default'  => $this->get_available_status('on-hold'),

			),
			'set_status_when_paid' => array(
				'title'	  => __('Mudar status após pagamento do boleto para:', 'woo-boleto-paghiper'),
				'type'	  => 'select',
				'options' => $this->get_available_status(),
				'default'  => $this->get_available_status('processing'),

			),
			'set_status_when_cancelled' => array(
				'title'	  => __('Mudar status após cancelamento do boleto para:', 'woo-boleto-paghiper'),
				'type'	  => 'select',
				'options' => $this->get_available_status(),
				'default'  => $this->get_available_status('cancelled'),

			),
			'debug' => array(
				'title'       => __( 'Log de depuração', 'woo-boleto-paghiper' ),
				'type'        => 'checkbox',
				'label'       => __( 'Ativa o log de erros', 'woo-boleto-paghiper' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Armazena eventos e erros, como chamadas API e exibições, dentro do arquivo %s Ative caso enfrente problemas.', 'woo-boleto-paghiper' ), $this->get_log_view() ),
			),
		);

		if($this->gateway->id == 'paghiper_pix') {
			unset($first['open_after_day_due']);
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

	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		// Reduce stock levels.
		// Support for WooCommerce 2.7.
		if ( $this->set_status_when_waiting !== $order->status) {
			if( !$order->get_data_store()->get_stock_reduced( $order_id ) ) {
				if ( function_exists( 'wc_reduce_stock_levels' ) ) {
					wc_reduce_stock_levels( $order_id );
				} else {
					$order->reduce_order_stock();
				}
	
				if ( 'yes' === $this->debug ) {
					wc_paghiper_add_log( $this->log, sprintf( 'Pedido %s: Itens do pedido retirados do estoque com sucesso', $order_id ) );
				}
			}

		}

		// Generates ticket data.
		$this->populate_initial_billet_date( $order );

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			WC()->cart->empty_cart();

			$url = $order->get_checkout_order_received_url();
		} else {
			global $woocommerce;

			$woocommerce->cart->empty_cart();

			$url = add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) );
		}

		// Gera um boleto e guarda os dados, pra reutilizarmos.
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-billet.php';


		$paghiperBoleto = new WC_PagHiper_Boleto( $order_id );
		$billet = $paghiperBoleto->create_billet();

		if($billet) {
			// Mark as on-hold (we're awaiting the ticket).
			$waiting_status = (!empty($this->set_status_when_waiting)) ? $this->set_status_when_waiting : 'on-hold';
			$order->update_status( $waiting_status, __( 'Boleto PagHiper: Boleto gerado e enviado por e-mail.', 'woo-boleto-paghiper' ) );

		} else {

			if ( 'yes' === $this->debug ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Pedido %s: Não foi possível gerar o boleto. Detalhes: %s', var_export($billet, true) ) );
			}

		}

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $url
		);
	}

	/**
	 * Output for the order received page.
	 *
	 * @return string Thank You message.
	 */
	public function thankyou_page($order_id) {

		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-billet.php';

		$paghiperBoleto = new WC_PagHiper_Boleto( $order_id );
		$paghiperBoleto->printBarCode(true);

		$html = '<div class="woocommerce-message">';
		$html .= sprintf( '<a class="button" href="%s" target="_blank" style="display: block !important; visibility: visible !important;">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $_GET['key'] ) ), __( 'Pagar o Boleto &rarr;', 'woo-boleto-paghiper' ) );

		$message = sprintf( __( '%sAtenção!%s Você NÃO vai receber o boleto pelos Correios.', 'woo-boleto-paghiper' ), '<strong>', '</strong>' ) . '<br />';
		$message .= __( 'Clique no link abaixo e pague o boleto pelo seu aplicativo de Internet Banking .', 'woo-boleto-paghiper' ) . '<br />';
		$message .= __( 'Se preferir, você pode imprimir e pagar o boleto em qualquer agência bancária ou lotérica.', 'woo-boleto-paghiper' ) . '<br />';

		$html .= apply_filters( 'woo_paghiper_thankyou_page_message', $message );

		$html .= '<strong style="display: block; margin-top: 15px; font-size: 0.8em">' . sprintf( __( 'Data de vencimento do Boleto: %s.', 'woo-boleto-paghiper' ), date( 'd/m/Y', time() + ( absint( $this->days_due_date ) * 86400 ) ) ) . '</strong>';

		$html .= '</div>';

		echo $html;
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
		
		$billet_due_date = new DateTime;
		$billet_due_date->setTimezone($this->timezone);
		if($due_date_config > 0)
			$billet_due_date->modify( "+{$due_date_config} days" );

		// Maybe skip non-workdays as per configuration
		$billet_due_date = wc_paghiper_add_workdays($billet_due_date, $order, $this->skip_non_workdays, 'date');
		
		$data['order_billet_due_date'] = $billet_due_date->format('Y-m-d');

		update_post_meta( $order->id, 'wc_paghiper_data', $data );
		if(function_exists('update_meta_cache'))
			update_meta_cache( 'shop_order', $order->id );

		return;
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
		if ( $sent_to_admin || apply_filters('woo_paghiper_pending_status', $this->set_status_when_waiting, $order) !== $order->status || 'paghiper' !== $order->payment_method ) {
			return;
		}

		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-billet.php';
		$paghiperBoleto = new WC_PagHiper_Boleto( $order->id );

		$html = '<div class="woo-paghiper-boleto-details">';
		$html .= '<h2>' . __( 'Pagamento', 'woo-boleto-paghiper' ) . '</h2>';

		$html .= '<p class="order_details">';

		$message = $paghiperBoleto->printBarCode();

		$message .= sprintf( __( '%sAtenção!%s Você NÃO vai receber o boleto pelos Correios.', 'woo-boleto-paghiper' ), '<strong>', '</strong>' ) . '<br />';
		$message .= __( 'Se preferir, você pode imprimir e pagar o boleto em qualquer agência bancária ou lotérica.', 'woo-boleto-paghiper' ) . '<br />';

		$html .= apply_filters( 'woo_paghiper_email_instructions', $message );

		$html .= '<br />' . sprintf( '<a class="button alt" href="%s" target="_blank">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->order_key ) ), __( 'Veja o boleto completo &rarr;', 'woo-boleto-paghiper' ) ) . '<br />';

		$html .= '<strong style="font-size: 0.8em">' . sprintf( __( 'Data de Vencimento: %s.', 'woo-boleto-paghiper' ), date( 'd/m/Y', time() + ( absint( $this->days_due_date ) * 86400 ) ) ) . '</strong>';

		$html .= '</p>';
		$html .= '</div>';

		echo $html;
	}
}