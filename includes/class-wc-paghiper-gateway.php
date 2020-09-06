<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Boleto Gateway Class.
 *
 * Built the Boleto method.
 */
class WC_Paghiper_Gateway extends WC_Payment_Gateway {

	private $log;
	private $timezone;

	/**
	 * Construtor do gateway. Inicializamos via __construct()
	 */
	public function __construct() {
		$this->id                 = 'paghiper';
		$this->icon               = apply_filters( 'wcpaghiper_icon', plugins_url( 'assets/images/boleto.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = false;
		$this->method_title       = __( 'Boleto PagHiper', 'woo-boleto-paghiper' );
		$this->method_description = __( 'Ativa a emissão e recebimento de boletos via PagHiper.', 'woo-boleto-paghiper' );

		// Carrega as configurações
		$this->init_form_fields();
		$this->init_settings();

		// Define as variáveis que vamos usar e popula com os dados de configuração
		$this->title       		= $this->get_option( 'title' );
		$this->description 		= $this->get_option( 'description' );
		$this->days_due_date 	= $this->get_option( 'days_due_date' );
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
			return '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woo-boleto-paghiper' ) . '</a>';
		}
		return '<code>woocommerce/logs/' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.txt</code>';
	}

	/**
	 * Gateway options.
	 */
	public function init_form_fields() {
		$shop_name = get_bloginfo( 'name' );

		$first = array(
			'enabled' => array(
				'title'   => __( 'Ativar Boleto PagHiper', 'woo-boleto-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Ativar/Desativar', 'woo-boleto-paghiper' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Título', 'woo-boleto-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Esse campo controla o título da seção que o usuário vê durante o checkout.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( 'Boleto Bancário', 'woo-boleto-paghiper' )
			),
			'description' => array(
				'title'       => __( 'Descrição', 'woo-boleto-paghiper' ),
				'type'        => 'textarea',
				'description' => __( 'Esse campo controla o texto da seção que o usuário vê durante o checkout.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( 'Pagar com Boleto Bancário', 'woo-boleto-paghiper' )
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
				'description' => __( 'Número de dias para calcular a data de vencimento do boleto. Caso a data de vencimento não seja útil, o sistema bancário considera o dia útil seguinte como data de vencimento.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => 5
			),
			'open_after_day_due' => array(
				'title'       => __( 'Dias de tolerância para pagto. do boleto', 'woo-boleto-paghiper' ),
				'type'        => 'number',
				'description' => __( 'Ao configurar este item, será possível pagar o boleto por uma quantidade de dias após o vencimento. O mínimo é de 5 dias e máximo de 30 dias.', 'woo-boleto-paghiper' ),
				'desc_tip'    => true,
				'default'     => 5
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
				'title'   => __( 'Exibir frase customizada no boleto?', 'woo-boleto-paghiper' ),
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

		$this->form_fields = array_merge( $first, $last );
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
		if ( 'on-hold' !== $order->status) {
			if ( function_exists( 'wc_reduce_stock_levels' ) ) {
				wc_reduce_stock_levels( $order_id );
			} else {
				$order->reduce_order_stock();
			}

			if ( 'yes' === $this->debug ) {
				wc_paghiper_add_log( $this->log, sprintf( 'Pedido %s: Itens do pedido retirados do estoque com sucesso', $order_id ) );
			}

		}

		// Generates ticket data.
		$this->populate_initial_billet_date( $order );

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			WC()->cart->empty_cart();

			$url = $order->get_checkout_order_received_url();
		} else {
			global $woocommerce;

			//$woocommerce->cart->empty_cart();

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

		$html .= apply_filters( 'wcpaghiper_thankyou_page_message', $message );

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

		$data['order_billet_due_date'] = $billet_due_date->format( 'Y-m-d' );		

		update_post_meta( $order->id, 'wc_paghiper_data', $data );
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
		if ( $sent_to_admin || apply_filters('woo_paghiper_pending_status', 'on-hold', $order) !== $order->status || 'paghiper' !== $order->payment_method ) {
			return;
		}

		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-billet.php';
		$paghiperBoleto = new WC_PagHiper_Boleto( $order->id );

		$html = '<h2>' . __( 'Pagamento', 'woo-boleto-paghiper' ) . '</h2>';

		$html .= '<p class="order_details">';

		$message = $paghiperBoleto->printBarCode();

		$message .= sprintf( __( '%sAtenção!%s Você NÃO vai receber o boleto pelos Correios.', 'woo-boleto-paghiper' ), '<strong>', '</strong>' ) . '<br />';
		$message .= __( 'Se preferir, você pode imprimir e pagar o boleto em qualquer agência bancária ou lotérica.', 'woo-boleto-paghiper' ) . '<br />';

		$html .= apply_filters( 'wcpaghiper_email_instructions', $message );

		$html .= '<br />' . sprintf( '<a class="button" href="%s" target="_blank">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->order_key ) ), __( 'Veja o boleto completo &rarr;', 'woo-boleto-paghiper' ) ) . '<br />';

		$html .= '<strong style="font-size: 0.8em">' . sprintf( __( 'Data de Vencimento: %s.', 'woo-boleto-paghiper' ), date( 'd/m/Y', time() + ( absint( $this->days_due_date ) * 86400 ) ) ) . '</strong>';

		$html .= '</p>';

		echo $html;
	}


}
