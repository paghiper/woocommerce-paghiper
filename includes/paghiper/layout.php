<?php
// Template para checkout transparente PagHiper.com
// Desenvolvido por Henrique Cruz - Intelihost.com.br

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

function httpPost($url,
$dadosboleto,$data,$order,$settings,$novoVcto = NULL) {

    $wp_api_url = get_home_url().'/wc-api/'.'WC_Gateway_Paghiper/';
    $postData = '';
    $order_no = $order->id;
    if(!isset($novoVcto)) {
      $order_date = DateTime::createFromFormat('Y-m-d', strtok($order->order_date, ' '));
      $data_vencimento = DateTime::createFromFormat('Y-m-d', $dadosboleto["data_vencimento"]);
      $dias_vencimento = $order_date->diff($data_vencimento);
      $vencimentoBoleto = $dias_vencimento->days;
    } else {
      $vencimentoBoleto = $novoVcto;
    }

    $customer = new WC_Customer( $order_no );
    
    // Preparate data to send
    $paghiper_data = array(
       "email_loja" => $settings['email'],

       // Para controle interno apenas, favor não remover.
       "idPartners" => 'D1J0M5GD',

       // Informações opcionais
       "urlRetorno" => $wp_api_url,
       "vencimentoBoleto" => $vencimentoBoleto,

       // Dados do produto
       "id_plataforma" => $order_no,
       "produto_codigo_1" => $order_no,
       "produto_valor_1" => str_replace(',', '.', $dadosboleto["valor_boleto"]),
       "produto_descricao_1" => 'Fatura #'.$order_no.' emitida pelo site '. get_bloginfo('name'),
       "produto_qtde_1" => 1,

       // Dados do cliente
       "email"      => $order->billing_email,
       "nome"       => $data['sacado'],
       "endereco"   => $order->billing_address_1 . ', ' . $order->billing_number . $order->billing_address_2,
       "bairro"     => $order->billing_neighborhood,
       "cidade"     => $order->billing_city,
       "estado"     => $order->billing_state,
       "cep"        => str_replace('-', '', $customer->get_postcode())
    );


    // Checa se incluimos dados CPF ou CNPJ no post
        if(isset($data['cnpj']) && $data['cnpj'] !== '') {
            $paghiper_data["razao_social"] = $companyname;
            $paghiper_data["cnpj"] = substr(trim(str_replace(array('+','-'), '', filter_var($data['cnpj'], FILTER_SANITIZE_NUMBER_INT))), -15);
        } else {
            $paghiper_data["cpf"] = substr(trim(str_replace(array('+','-'), '', filter_var($data['cpf'], FILTER_SANITIZE_NUMBER_INT))), -14);
        }

    if($settings['exibir-frase-boleto'] == true) {
        $paghiper_data["frase_fixa_boleto"] = true;
    }

    $paghiper_data["api"] = "json";
    $paghiper_data["pagamento"]  = "pagamento";

    
   //create name value pairs seperated by &
   foreach($paghiper_data as $k => $v) 
   { 
      $postData .= $k . '='.urlencode($v).'&'; 
   }
   $postData = rtrim($postData, '&');
 
    $ch = curl_init();  
 
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_POST, count($postData));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

 
    $request=curl_exec($ch);
 
    curl_close($ch);


    $output = json_decode($request, true);
    $transacao = reset($output["transacao"]);

    if(!empty($transacao) && validate_transacion($transacao)) {
      $data = array(
          'idTransacao' => $transacao['idTransacao'],
          'dataTransacao' => $transacao['dataTransacao'],
          'valorTotal' => $transacao['valorTotal'],
          'status' => $transacao['status'],
          'idPlataforma' => $transacao['idPlataforma'],
          'urlPagamento' => $transacao['urlPagamento'],
          'urlPdfPagamento' => $transacao['urlPdfPagamento'],
          'linhaDigitavel' => $transacao['linhaDigitavel'],
          'dataVencimento' => $transacao['dataVencimento'],
          'data_vencimento' => $dadosboleto['data_vencimento']
      );
      update_post_meta( $order->id, 'wc_paghiper_data', $data );
    }

    echo get_remote_content($transacao['urlPagamento']);
 
}



if(empty($order_data["dataVencimento"])) {
    $order_date = DateTime::createFromFormat('Y-m-d', strtok($order->order_date, ' '));
    $data_vencimento = DateTime::createFromFormat('Y-m-d', $order_data["data_vencimento"]);
    $dias_vencimento = $order_date->diff($data_vencimento)->format("%r%a");
    $new_request = TRUE;
} else {
    $vctoBoleto = DateTime::createFromFormat('Y-m-d', $order_data["dataVencimento"]);
    $vctoBanco = DateTime::createFromFormat('Y-m-d', $order_data["data_vencimento"]);
    $todayDate = new \DateTime();
    $dias_vencimento = (int) $todayDate->diff($vctoBoleto)->format("%r%a");

    $different_total = ( $order->get_total() == $order_data['valorTotal'] ? NULL : TRUE );
    $different_due_date = ( $dadosboleto["dataVencimento"] == $dadosboleto["data_vencimento"] ? NULL : TRUE );
}



if($order->has_status( 'processing' )) {
  $ico = 'boleto-ok.png';
  $title = 'Este boleto ja foi pago!';
  $message = 'Recebemos seu pagamento! Você pode acompanhar a evolução do seu pedido pelo seu painel de cliente.';
  echo print_screen($ico, $title, $message);
  exit();
} elseif( !$different_due_date ) {
  if($dias_vencimento >= -3 && $dias_vencimento < 0) {
    $ico = 'boleto-waiting.png';
    $title = 'Por favor, aguarde!';
    $message = 'Este boleto venceu. Caso ja tenha efetuado o pagamento, aguarde o prazo de baixa bancária.';
    echo print_screen($ico, $title, $message);
    exit();
  } elseif( $dias_vencimento < -3 ) {
    $ico = 'boleto-cancelled.png';
    $title = 'Boleto Vencido!';
    $message = 'Este boleto venceu e foi cancelado. Por favor, efetue seu pedido novamente.';
    echo print_screen($ico, $title, $message);
    exit();
  }
}



if( $different_due_date === TRUE || $different_total === TRUE || $new_request === TRUE) {
    // Checa se data de vencimento é menor que hoje e se é possível solicitar boleto após o vencimento
    // Solicita um boleto novo caso a data do banco seja diferente da do boleto.
    echo httpPost("https://www.paghiper.com/checkout/",$dadosboleto,$data,$order,$settings);
} else {
    // Temos um boleto ja emitido com data de vencimento válida, só pegamos uma cópia
    echo get_remote_content($dadosboleto['urlPagamento']);
}




function get_remote_content($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, false); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}


function validate_transacion($array){
  foreach($array as $elm) {
    if(empty($elm)) return false;
  }
  return true;
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
    <img style="max-width: 200px;" src="'.wcpaghiper_assets_url().'images/'.$ico.'">
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