<?php

use PagHiper\PagHiper;

// Include SDK for our call
require_once WC_Paghiper::get_plugin_path() . 'includes/paghiper-php-sdk/build/vendor/scoper-autoload.php';

$wp_api_url = add_query_arg( 'wc-api', 'WC_Gateway_Paghiper', home_url( '/' ) );
add_action( 'woocommerce_api_wc_gateway_paghiper', 'woocommerce_paghiper_check_ipn_response' );

// Fallback suport for older PIX module endpoint notifications
add_action('wp_ajax_woo_paghiper_pix_webhook', 'woocommerce_paghiper_check_ipn_response');
add_action('wp_ajax_nopriv_woo_paghiper_pix_webhook','woocommerce_paghiper_check_ipn_response');

$paghiper_log = false;

function woocommerce_paghiper_valid_ipn_request($return, $order_no, $settings) {

    global $paghiper_log;

    $order          = new WC_Order($order_no);
    $order_status   = $order->get_status();

    // Trata os retornos

    // Primeiro checa se o pedido ja foi pago.
    $statuses = ((strpos($order_status, 'wc-') === FALSE) ? array('processing', 'completed') : array('wc-processing', 'wc-completed'));
    $already_paid = (in_array( $order_status, $statuses )) ? true : false;

    if($already_paid) {
        // Se sim, os próximos Status só podem ser Completo, Disputa ou Estornado
        switch ( $return['status'] ) {
            case "completed" :
                $order->add_order_note( __( 'PagHiper: Pagamento completo. O valor ja se encontra disponível para saque.' , 'woo_paghiper' ) );
                break;
            case "processing" :
                $order->update_status( $settings['set_status_when_waiting'], __( 'PagHiper: Pagamento em disputa. Para responder, faça login na sua conta Paghiper e procure pelo número da transação.', 'woo_paghiper' ) );
                paghiper_increase_order_stock( $order, $settings );
                break;
        }

    } else {

        // Se não, os status podem ser Cancelado, Aguardando ou Aprovado
        switch ( $return['status'] ) {
            case "pending" :

                /*if($order_status == $settings['set_status_when_cancelled']) {
                    $waiting_status = (!empty($settings['set_status_when_waiting'])) ? $settings['set_status_when_waiting'] : 'on-hold';
                    $order->update_status( $waiting_status, __( 'Boleto PagHiper: Novo boleto gerado. Aguardando compensação.', 'woo_paghiper' ) );
                } else {
                    $order->add_order_note( __( 'PagHiper: Post de notificação recebido. Aguardando compensação do boleto.' , 'woo_paghiper' ) );
                }*/
                $order->add_order_note( __( 'PagHiper: Novo boleto emitido. Aguardando compensação.' , 'woo_paghiper' ) );
                break;

            case "reserved" :

                $order->add_order_note( __( 'PagHiper: Pagamento pré-compensado (reservado). Aguarde confirmação.' , 'woo_paghiper' ) );
                break;
            case "canceled" :

                    // Se data do pedido for maior que a do boleto cancelado, não cancelar pedido
			        $paghiper_data = get_post_meta( $order_no, 'wc_paghiper_data', true );

                    if($return['transaction_id'] !== $paghiper_data['transaction_id']) {
                        $order->add_order_note( __( 'PagHiper: Um boleto emitido para este pedido foi cancelado. Como não era o boleto mais atual, o pedido permanece aguardando pagamento.' , 'woo_paghiper' ) );
                        return;
                    }

                    $cancelled_status = (!empty($settings['set_status_when_cancelled'])) ? $settings['set_status_when_cancelled'] : 'cancelled';
                    
                    $order->update_status( $cancelled_status, __( 'PagHiper: Boleto Cancelado.', 'woo_paghiper' ) );
                    paghiper_increase_order_stock( $order, $settings );
                break;
            case "paid" :

                // For WooCommerce 2.2 or later.
                add_post_meta( $order_no, '_transaction_id', (string) $return['transaction_id'], true );

                // Changing the order for processing and reduces the stock.
                $order->payment_complete();

                if(strpos('paid', $settings['set_status_when_paid']) === FALSE) {
                    $order->update_status( $settings['set_status_when_paid'], __( 'PagHiper: Boleto Pago.', 'woo_paghiper' ) );
                } else {
                    $order->add_order_note( __( 'PagHiper: Pagamento compensado.', 'woo_paghiper' ) );
                }

                break;
            case "refunded" :
                $order->update_status( 'refunded', __( 'PagHiper: Pagamento estornado. O valor foi ja devolvido ao cliente. Para mais informações, entre em contato com a equipe de atendimento Paghiper.' , 'woo_paghiper' ) );
                break;
        }
    }
}

function woocommerce_paghiper_check_ipn_response() {

    global $paghiper_log;

    $transaction_type = (isset($_GET) && array_key_exists('gateway', $_GET)) ? sanitize_text_field($_GET['gateway']) : 'billet';
    if (defined('DOING_AJAX') && DOING_AJAX) { 
        $transaction_type = 'pix'; 
    }

    $settings = ($transaction_type == 'pix') ? get_option( 'woocommerce_paghiper_pix_settings' ) : get_option( 'woocommerce_paghiper_billet_settings' );

    if(!$paghiper_log) {
        $paghiper_log = wc_paghiper_initialize_log( $settings[ 'debug' ] );
    }

    $token 			= $settings['token'];
    $api_key 		= $settings['api_key'];

    $PagHiperAPI 	= new PagHiper($api_key, $token);
    $response 		= $PagHiperAPI->transaction()->process_ipn_notification($_POST['notification_id'], $_POST['transaction_id'], $transaction_type);

    if($response['result'] == 'success') {

        if ( $log ) {
            wc_paghiper_add_log( $paghiper_log, sprintf('Pedido #%s: Post de retorno da PagHiper confirmado.', $response['order_id']) );
        }

        // Print a 200 HTTP code for the notification engine
        header( 'HTTP/1.1 200 OK' );

        // Carry on with the operation
        woocommerce_paghiper_valid_ipn_request( $response, $response['order_id'], $settings );


    } else {

        if ( $paghiper_log ) {
            $error = $response->get_error_message();
            wc_paghiper_add_log( $paghiper_log, sprintf( 'Erro: não foi possível checar o post de retorno da PagHiper. Mensagem: %s', $response ) );
        }

        wp_die( esc_html__( 'Solicitação PagHiper Não Autorizada', 'woo_paghiper' ), esc_html__( 'Solicitação PagHiper Não Autorizada', 'woo_paghiper' ), array( 'response' => 401 ) );
    }

} 


/**
 * Increase order stock.
 *
 * @param int $order_id Order ID.
 */
function paghiper_increase_order_stock( $order, $settings ) {

    global $paghiper_log;

    if(!$paghiper_log) {
        $paghiper_log = wc_paghiper_initialize_log( $settings[ 'debug' ] );
    }

    /* Changing setting keys from Woo-Boleto-Paghiper 1.2.6.1 */
    $replenish_stock = ($settings['replenish_stock'] !== '') ? $settings['replenish_stock'] : $settings['incrementar-estoque'];
    $order_id = $order->get_id();

    // Locks this action for misfiring when order is placed with other gateways
    if(!in_array($order->get_payment_method(), ['paghiper', 'paghiper_billet', 'paghiper_pix'])) {
        return;
    }
    
    if ( 'yes' === get_option( 'woocommerce_manage_stock' ) && $replenish_stock == 'yes' && $order && 0 < count( $order->get_items() ) ) {
        if ( apply_filters( 'woocommerce_payment_complete_reduce_order_stock', $order, $order_id ) ) {
            if ( function_exists( 'wc_maybe_increase_stock_levels' ) ) {
                wc_maybe_increase_stock_levels( $order_id );
            } else {
                wc_paghiper_add_log( $paghiper_log, sprintf( 'Pedido #%s: Incremento de estoque automático cancelado. Função de devolução ao estoque está indisponível no momento', $order_id ) );
            }
        } else {
            wc_paghiper_add_log( $paghiper_log, sprintf( 'Pedido #%s: Incremento de estoque automático cancelado. Um filtro impediu a execução do procesos.', $order_id ) );
        }
    } else {
        wc_paghiper_add_log( $paghiper_log, sprintf( 'Pedido #%s: Gestão de estoque não habilitada. Itens não serão readicionados ao inventário.', $order_id ) );
    }
}