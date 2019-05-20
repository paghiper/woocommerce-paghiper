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

	/**
	 * Construtor do gateway. Inicializamos via __construct()
	 */
	public function __construct() {
		$this->id                 = 'paghiper';
		$this->icon               = apply_filters( 'wcpaghiper_icon', plugins_url( 'assets/images/boleto.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields         = false;
		$this->method_title       = __( 'Boleto PagHiper', 'woocommerce-paghiper' );
		$this->method_description = __( 'Ativa a emissão e recebimento de boletos via PagHiper.', 'woocommerce-paghiper' );

		// Carrega as configurações
		$this->init_form_fields();
		$this->init_settings();

		// Define as variáveis que vamos usar e popula com os dados de configuração
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->paghiper_time = $this->get_option( 'paghiper_time' );

		// Ativa os logs
		$this->log = wc_paghiper_initialize_log( $this->get_option( 'debug' ) );

		// Ações
		add_action( 'woocommerce_thankyou_paghiper', array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 2 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
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
			return '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woocommerce-paghiper' ) . '</a>';
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
				'title'   => __( 'Ativar Boleto PagHiper', 'woocommerce-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Ativar/Desativar', 'woocommerce-paghiper' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Título', 'woocommerce-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Esse campo controla o título da seção que o usuário vê durante o checkout.', 'woocommerce-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( 'Boleto Bancário', 'woocommerce-paghiper' )
			),
			'description' => array(
				'title'       => __( 'Descrição', 'woocommerce-paghiper' ),
				'type'        => 'textarea',
				'description' => __( 'Esse campo controla o texto da seção que o usuário vê durante o checkout.', 'woocommerce-paghiper' ),
				'desc_tip'    => true,
				'default'     => __( 'Pagar com Boleto Bancário', 'woocommerce-paghiper' )
			),
			'paghiper_details' => array(
				'title' => __( 'Configurações do PagHiper Boleto Bancário', 'woocommerce-paghiper' ),
				'type'  => 'title'
			),
			'email' => array(
				'title'       => __( 'E-mail', 'woocommerce-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Email da conta PagHiper que irá receber', 'woocommerce-paghiper' ),
			),
			'token' => array(
				'title'       => __( 'Token PagHiper', 'woocommerce-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Extremamente importante, você pode gerar seu token em nossa pagina: Painel > Ferramentas > Token.', 'woocommerce-paghiper' ),
			),
			'paghiper_time' => array(
				'title'       => __( 'Dias úteis para o vencimento', 'woocommerce-paghiper' ),
				'type'        => 'text',
				'description' => __( 'Número de dias úteis para calcular a data de vencimento do boleto.', 'woocommerce-paghiper' ),
				'desc_tip'    => true,
				'default'     => 5
			)
		);

		$last = array(
			'extra_details' => array(
				'title' => __( 'Configurações extra', 'woocommerce-paghiper' ),
				'type'  => 'title'
			),
			'checkout-transparente' => array(
				'title'   => __( 'Habilitar checkout transparente?', 'woocommerce-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Exibe o boleto bancário dentro do seu site, ao invés de redirecionar ao site da PagHiper. Ativar/Desativar', 'woocommerce-paghiper' ),
				'default' => 'yes'
			),
			'incrementar-estoque' => array(
				'title'   => __( 'Restituir estoque, caso o pedido seja cancelado?', 'woocommerce-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'O plug-in subtrai os itens comprados no pedido por padrão. Essa opção os incrementa de volta, caso o pedido seja cancelado. Ativar/Desativar', 'woocommerce-paghiper' ),
				'default' => 'yes'
			),
			'exibir-frase-boleto' => array(
				'title'   => __( 'Exibir frase customizada no boleto?', 'woocommerce-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Ativar/Desativar', 'woocommerce-paghiper' ),
				'default' => 'yes'
			),
			'cancelar-pedidos' => array(
				'title'   => __( 'Cancelar pedidos quando o boleto expirar?', 'woocommerce-paghiper' ),
				'type'    => 'checkbox',
				'label'   => __( 'Ativar/Desativar', 'woocommerce-paghiper' ),
				'default' => 'yes'
			),
			'debug' => array(
				'title'       => __( 'Log de depuração', 'woocommerce-paghiper' ),
				'type'        => 'checkbox',
				'label'       => __( 'Ativa o log de erros', 'woocommerce-paghiper' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Armazena eventos e erros, como chamadas API e exibições, dentro do arquivo %s Ative caso enfrente problemas.', 'woocommerce-paghiper' ), $this->get_log_view() ),
			),
		);

		$this->form_fields = array_merge( $first, $last );
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

		// TODO: Chamar boleto aqui para usar no processo de validação do pedido.
		// Mark as on-hold (we're awaiting the ticket).
		$order->update_status( 'on-hold', __( 'Boleto PagHiper: Aguardando cliente acessar o boleto.', 'woocommerce-paghiper' ) );

		// Gera um boleto e guarda os dados, pra reutilizarmos.

		// Generates ticket data.
		$this->generate_paghiper_data( $order );

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			WC()->cart->empty_cart();

			$url = $order->get_checkout_order_received_url();
		} else {
			global $woocommerce;

			$woocommerce->cart->empty_cart();

			$url = add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order_id, get_permalink( woocommerce_get_page_id( 'thanks' ) ) ) );
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
	public function thankyou_page() {
		$html = '<div class="woocommerce-message">';
		$html .= sprintf( '<a class="button" href="%s" target="_blank" style="display: block !important; visibility: visible !important;">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $_GET['key'] ) ), __( 'Pagar o Boleto &rarr;', 'woocommerce-paghiper' ) );

		$message = sprintf( __( '%sAtenção!%s Você NÃO vai receber o boleto pelos Correios.', 'woocommerce-paghiper' ), '<strong>', '</strong>' ) . '<br />';
		$message .= __( 'Clique no link abaixo e pague o boleto pelo seu aplicativo de Internet Banking .', 'woocommerce-paghiper' ) . '<br />';
		$message .= __( 'Se preferir, você pode imprimir e pagar o boleto em qualquer agência bancária ou lotérica.', 'woocommerce-paghiper' ) . '<br />';

		$html .= apply_filters( 'wcpaghiper_thankyou_page_message', $message );

		$html .= '<strong style="display: block; margin-top: 15px; font-size: 0.8em">' . sprintf( __( 'Data de vencimento do Boleto: %s.', 'woocommerce-paghiper' ), date( 'd/m/Y', time() + ( absint( $this->paghiper_time ) * 86400 ) ) ) . '</strong>';

		$html .= '</div>';

		echo $html;
	}

	/**
	 * Generate ticket data.
	 *
	 * @param  object $order Order object.
	 */
	public function generate_paghiper_data( $order ) {

		//TODO
		// Emitir boleto aqui e inserir filtro para anexar boleto PDF
		// Ticket data.
		$data                       = array();
		$data['data_vencimento']    = date( 'Y-m-d', time() + ( absint( $this->paghiper_time ) * 86400 ) );

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
		if ( $sent_to_admin || 'on-hold' !== $order->status || 'paghiper' !== $order->payment_method ) {
			return;
		}

		$html = '<h2>' . __( 'Pagamento', 'woocommerce-paghiper' ) . '</h2>';

		$html .= '<p class="order_details">';

		$message = sprintf( __( '%sAtenção!%s Você NÃO vai receber o boleto pelos Correios.', 'woocommerce-paghiper' ), '<strong>', '</strong>' ) . '<br />';
		$message .= __( 'Clique no link abaixo e pague o boleto pelo seu aplicativo de Internet Banking .', 'woocommerce-paghiper' ) . '<br />';
		$message .= __( 'Se preferir, você pode imprimir e pagar o boleto em qualquer agência bancária ou lotérica.', 'woocommerce-paghiper' ) . '<br />';

		$html .= apply_filters( 'wcpaghiper_email_instructions', $message );

		$html .= '<br />' . sprintf( '<a class="button" href="%s" target="_blank">%s</a>', esc_url( wc_paghiper_get_paghiper_url( $order->order_key ) ), __( 'Pagar o Boleto &rarr;', 'woocommerce-paghiper' ) ) . '<br />';

		$html .= '<strong style="font-size: 0.8em">' . sprintf( __( 'Data de Vencimento: %s.', 'woocommerce-paghiper' ), date( 'd/m/Y', time() + ( absint( $this->paghiper_time ) * 86400 ) ) ) . '</strong>';

		$html .= '</p>';

		echo $html;
	}


}
