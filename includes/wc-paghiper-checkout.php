<?php

// Trata o valor para envio
function money_format( $value ) {
    return number_format( $value, 2, ',', '' );
}

// Faz o envio dos dados a PagHiper
function valid_paghiper_ipn_request($url,$params)
{
  $postData = '';
   //create name value pairs seperated by &
   foreach($params as $k => $v) 
   { 
      $postData .= $k . '='.$v.'&'; 
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

 
    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
 
}

// Pega número do Pedido
$order_no = $order->get_order_number();
$description = $this->sanitize_description( sprintf( __( 'Pedido %s', 'woocommerce-pagseguro' ), $order_no ) );
$amount = $this->money_format( $order->get_total() );
$quantity = 1;

$params = array(
   "email_loja" => $acc_email,
   "urlRetorno" => $wp_api_url,
   "tipoBoleto" => "paghiperA4",
   "vencimentoBoleto" => $dadospaghiper["data_vencimento"],
   "id_plataforma" => $order_no,
   "produto_codigo_1" => $order_no,
   "produto_valor_1" => $amount,
   "produto_descricao_1" => $description,
   "produto_qtde_1" => 1,
   "email" => $order->billing_email,
   "nome" => $order->billing_first_name . ' ' . $order->billing_last_name,
   "cpf" => $order->billing_cpf,
   "frase_fixa_paghiper" => true,
   "endereco" => $order->billing_address_1,
   "bairro" => $order->billing_address_2,
   "cidade" => $order->billing_city,
   "estado" => $order->billing_state,
   "cep" => $order->billing_postcode,
   "pagamento" => "pagamento"
);
 
echo httpPost("https://www.paghiper.com/checkout/",$params);
?>