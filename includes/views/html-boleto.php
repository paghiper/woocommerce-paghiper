<?php
// Template para checkout transparente PagHiper.com
// Desenvolvido por Henrique Cruz - Intelihost.com.br


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@ob_start();

global $wp_query;

$boleto_code = '';

if(array_key_exists('paghiper', $wp_query->query_vars)) {
	$boleto_code = sanitize_key(wp_unslash( $wp_query->query_vars['paghiper'] ));
}

// Test if exist ref.
if ( !empty( $boleto_code ) ) {

	// Gets Order id.
	$order_id = wc_get_order_id_by_order_key( $boleto_code );

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


		// Checamos se o pedido não é um PIX
		if($payment_method == 'paghiper_pix') {

			$ico = 'billet-cancelled.png';
			$title = 'Este pedido não foi feito com boleto!';
			$message = 'A forma de pagamento deste pedido é PIX. Cheque seu e-mail ou sua área de pedidos para informações sobre como pagar.';
			echo wc_print_transaction_screen($ico, $title, $message);
		
			if ( $log ) {
				wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Endpoint de boleto foi acessado mas o método de pagamento é PIX.', $order->get_id() ) );
			}

			exit();

		}

		// Check if a new billet should be generated
		if($order->is_paid()) {

			$ico = 'billet-ok.png';
			$title = 'Este boleto ja foi pago!';
			$message = 'Recebemos seu pagamento! Você pode acompanhar a evolução do seu pedido pelo seu painel de cliente.';
			wc_print_transaction_screen($ico, $title, $message);
		
			if ( $log ) {
				wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Tela de boleto pago exibida.', $order->get_id() ) );
			}
		
			exit();
		
		} else {
		
			if($dias_vencimento >= -3 && $dias_vencimento < 0) {
		
				$ico = 'billet-waiting.png';
				$title = 'Por favor, aguarde!';
				$message = 'Este boleto venceu. Caso ja tenha efetuado o pagamento, aguarde o prazo de baixa bancária.';
				wc_print_transaction_screen($ico, $title, $message);
			
				if ( $log ) {
					wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Tela de boleto aguardando compensação exibida.', $order->get_id() ) );
				}
			
				exit();
		
			} elseif( $dias_vencimento < -3 ) {
		
				$ico = 'billet-cancelled.png';
				$title = 'Boleto Vencido!';
				$message = 'Este boleto venceu e foi cancelado. Por favor, efetue seu pedido novamente.';
				wc_print_transaction_screen($ico, $title, $message);
			
				if ( $log ) {
					wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Tela de boleto cancelado compensação exibida.', $order->get_id() ) );
				}
			
				exit();
		
			}
		
		}


		$paghiperTransaction->printToScreen();

		exit();
	}
} else {
		
	$ico = 'billet-cancelled.png';
	$title = 'Boleto indisponível';
	$message = 'Houve um problema ao emitir o seu boleto. Por favor entre em contato com a nossa equipe de suporte.';
	wc_print_transaction_screen($ico, $title, $message);

	if ( $log ) {
		wc_paghiper_add_log( $log, 'Não foi possível resgatar a chave do pedido para exibição do boleto.', ['wp_query' => $wp_query]);
	}

	exit();
}

function wc_print_transaction_screen($ico, $title, $message) { 
	$image_url = wc_paghiper_assets_url('images/' . $ico);
	?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo esc_html($title); ?></title>
		<meta name="author" content="">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>
	<body>
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,600" rel="stylesheet"> 
		<div class="container">
			<div>
				<img style="max-width: 200px;" src="<?php echo esc_url($image_url); ?>">
				<h3><?php echo esc_html($title); ?></h3>
				<p><?php echo esc_html($message); ?></p>
			</div>
		</div>
		<style type="text/css">
		html, body {
			width: 100%;
			height: 100%;
			overflow: hidden;
		}
		* {
			font-family: 'Open Sans', sans-serif;
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
</html>
  
<?php }



// If an error occurred is redirected to the homepage.
if ( $log ) {
	wc_paghiper_add_log( $log, 'Erro geral: cliente redirecionado para a homepage.' );
}
wp_redirect( home_url() );
exit();