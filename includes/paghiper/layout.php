<?php
// Template para checkout transparente PagHiper.com
// Desenvolvido por Henrique Cruz - Intelihost.com.br

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
?>
<?php
function httpPost($url,
$dadosboleto,$data,$order,$settings) {

    $wp_api_url = get_home_url().'/wc-api/'.'WC_Gateway_Paghiper/';
    $postData = '';
    $order_no = $order->id;
    $order_date = DateTime::createFromFormat('Y-m-d H:i:s', $order->order_date);
    $data_vencimento = DateTime::createFromFormat('d/m/Y', $dadosboleto["data_vencimento"]);
    $dias_vencimento = $data_vencimento->diff($order_date);
    $vencimentoBoleto = $dias_vencimento->d;
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

    $paghiper_data["pagamento"]  = "pagamento";

    //print_r($paghiper_data);

   //create name value pairs seperated by &
   foreach($paghiper_data as $k => $v) 
   { 
      $postData .= $k . '='.urlencode($v).'&'; 
   }
   $postData = rtrim($postData, '&');
   //print_r($postData);
 
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

echo httpPost("https://www.paghiper.com/checkout/",$dadosboleto,$data,$order,$settings);