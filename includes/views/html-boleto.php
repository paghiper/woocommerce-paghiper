<?php
// Template para checkout transparente PagHiper.com
// Desenvolvido por Henrique Cruz - Intelihost.com.br


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@ob_start();

global $wp_query;

// Support for plugin older versions.
$boleto_code = isset( $_GET['ref'] ) ? $_GET['ref'] : $wp_query->query_vars['paghiper'];

// Test if exist ref.
if ( isset( $boleto_code ) ) {
	
	// Sanitize the ref.
	$ref = sanitize_title( $boleto_code );

	// Gets Order id.
	$order_id = wc_get_order_id_by_order_key( $ref );

	if ( $order_id ) {

		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';

		$paghiperTransaction = new WC_PagHiper_Transaction( $order_id );
		$paghiperTransaction->has_issued_valid_transaction();

		$order = $paghiperTransaction->_get_order();
		$dias_vencimento = $paghiperTransaction->_get_past_due_days();

		// Pega a configuração atual do plug-in.
		$payment_method = $order->get_payment_method();
		$settings = get_option("woocommerce_{$payment_method}_settings");;
		
		// Inicializa logs, caso ativados
		$log = wc_paghiper_initialize_log( $settings[ 'debug' ] );

		// Somamos os dias de tolerância para evitar bloqueios na retirada de segunda via.
		$dias_vencimento += (!empty($settings['open_after_day_due']) && $settings['open_after_day_due'] >= 5 && $settings['open_after_day_due'] < 31) ? $settings['open_after_day_due'] : '0';


		// Check if a new billet should be generated
		if($order->is_paid()) {

			$ico = 'billet-ok.png';
			$title = 'Este boleto ja foi pago!';
			$message = 'Recebemos seu pagamento! Você pode acompanhar a evolução do seu pedido pelo seu painel de cliente.';
			echo print_screen($ico, $title, $message);
		
			if ( $log ) {
				wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Tela de boleto pago exibida.', $order->get_id() ) );
			}
		
			exit();
		
		} else {
		
			if($dias_vencimento >= -3 && $dias_vencimento < 0) {
		
				$ico = 'billet-waiting.png';
				$title = 'Por favor, aguarde!';
				$message = 'Este boleto venceu. Caso ja tenha efetuado o pagamento, aguarde o prazo de baixa bancária.';
				echo print_screen($ico, $title, $message);
			
				if ( $log ) {
					wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Tela de boleto aguardando compensação exibida.', $order->get_id() ) );
				}
			
				exit();
		
			} elseif( $dias_vencimento < -3 ) {
		
				$ico = 'billet-cancelled.png';
				$title = 'Boleto Vencido!';
				$message = 'Este boleto venceu e foi cancelado. Por favor, efetue seu pedido novamente.';
				echo print_screen($ico, $title, $message);
			
				if ( $log ) {
					wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Tela de boleto cancelado compensação exibida.', $order->get_id() ) );
				}
			
				exit();
		
			}
		
		}


		$paghiperTransaction->printToScreen();

		exit();
	}
}

function print_screen($ico, $title, $message) {
	$code = '
		<!DOCTYPE html>
		<html>
			<head>
				<meta charset="utf-8">
				<title></title>
				<meta name="author" content="">
				<meta name="description" content="">
				<meta name="viewport" content="width=device-width, initial-scale=1">
			</head>
			<body>
				<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,600" rel="stylesheet"> 
				<div class="container">
					<div>
						<img style="max-width: 200px;" src="'.wc_paghiper_assets_url().'images/'.$ico.'">
						<h3>'.$title.'</h3>
						<p>'.$message.'</p>
					</div>
				</div>
				<style type="text/css">
				html, body {
					width: 100%;
					height: 100%;
					overflow: hidden;
				}
				* {
					font-family: Open Sans;
				}
				.container {
					display: table;
					width: 100%;
					height: 100%;
				}
				.container div {
					display: table-cell;
					vertical-align: middle;
					text-align: center;
				}
				.container div * {
					max-width: 90%;
					margin: 0px auto;
				}
				</style>
			</body>
		</html>';
	return $code;
  
}



// If an error occurred is redirected to the homepage.
if ( $log ) {
	wc_paghiper_add_log( $log, 'Erro geral: cliente redirecionado para a homepage.' );
}
wp_redirect( home_url() );
exit;