<?php

$wp_api_url = str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_Gateway_Paghiper', home_url( '/' ) ) );
add_action( 'woocommerce_api_wc_gateway_paghiper', 'check_ipn_response' );


function valid_paghiper_ipn_request($return, $order_no) {

    $order                  = new WC_Order($order_no);
    $array                  = array($return, $order_no, $order);
    $settings               = get_option( 'woocommerce_paghiper_settings', array() );
    $order_status           = $order->get_status();
    $creditDate             = (string) $return['dataCredito'];
    $formattedCreditDate    = date("d/m/Y", strtotime($creditDate));

    // Trata os retornos

    // Primeiro checa se o pedido ja foi pago.
    $statuses = array('wc-processing', 'wc-completed');
    $already_paid = (in_array( $order_status, $statuses )) ? true : false;

    if($already_paid) {
        // Se sim, os próximos Status só podem ser Completo, Disputa ou Estornado
        switch ( $return['status'] ) {
            case "Completo" :
                $order->add_order_note( __( 'PagHiper: Pagamento completo. O valor ja se encontra disponível para saque.' , 'woocommerce-paghiper' ) );
                break;
            case "Disputa" :
                $order->update_status( 'on-hold', __( 'PagHiper: Pagamento em disputa. Para responder, faça login na sua conta Paghiper e procure pelo número da transação.', 'woocommerce-paghiper' ) );
                break;
        }
    } else {
        // Se não, os status podem ser Cancelado, Aguardando ou Aprovado
        switch ( $return['status'] ) {
            case "Aguardando" :
                $order->update_status( 'on-hold', __( 'PagHiper: Boleto gerado, aguardando pagamento.', 'woocommerce-paghiper' ) );
                add_post_meta( $order_no, 'PaghiperidTransacao', (string) $return['idTransacao'], true );
                add_post_meta( $order_no, 'PaghiperurlBoleto', (string) $return['urlPagamento'], true );
                break;
            case "Cancelado" :
                    if($settings['cancelar-pedidos'] == true) {
                        $order->update_status( 'cancelled', __( 'PagHiper: Boleto Cancelado.', 'woocommerce-paghiper' ) );
                    } else {
                        $order->update_status( 'pending', __( 'PagHiper: Boleto Cancelado.', 'woocommerce-paghiper' ) );
                    }
                break;
            case "Aprovado" :
                $order->add_order_note( sprintf( __( 'PagHiper: Pagamento compensado. O valor estará disponível no dia <strong>%s</strong>.', 'woocommerce-paghiper' ), (string) $formattedCreditDate ) );

                // For WooCommerce 2.2 or later.
                add_post_meta( $order_no, '_transaction_id', (string) $return['idTransacao'], true );

                // Changing the order for processing and reduces the stock.
                $order->payment_complete();
                break;
            case "Estornado" :
                $order->update_status( 'refunded', __( 'PagHiper: Pagamento estornado. O valor foi ja devolvido ao cliente. Para mais informações, entre em contato com a equipe de atendimento Paghiper.' , 'woocommerce-paghiper', 'woocommerce-paghiper' ) );
                break;
        }
    }

    switch ( $return['status'] ) {
        case "Aguardando" :
            $order->update_status( 'on-hold', __( 'PagHiper: Boleto gerado, aguardando pagamento.', 'woocommerce-paghiper' ) );
            add_post_meta( $order_no, 'PaghiperidTransacao', (string) $return['idTransacao'], true );
            add_post_meta( $order_no, 'PaghiperurlBoleto', (string) $return['urlPagamento'], true );
            break;
        case "Cancelado" :
                if($settings['cancelar-pedidos'] == true) {
                    $order->update_status( 'cancelled', __( 'PagHiper: Boleto Cancelado.', 'woocommerce-paghiper' ) );
                } else {
                    $order->update_status( 'pending', __( 'PagHiper: Boleto Cancelado.', 'woocommerce-paghiper' ) );
                }
            break;
        case "Aprovado" :
            $order->add_order_note( sprintf( __( 'PagHiper: Pagamento compensado. O valor estará disponível no dia <strong>%s</strong>.', 'woocommerce-paghiper' ), (string) $formattedCreditDate ) );

            // For WooCommerce 2.2 or later.
            add_post_meta( $order_no, '_transaction_id', (string) $return['idTransacao'], true );

            // Changing the order for processing and reduces the stock.
            $order->payment_complete();
            break;
        case "Completo" :
            $order->add_order_note( __( 'PagHiper: Pagamento completo. O valor ja se encontra disponível para saque.' , 'woocommerce-paghiper' ) );
            break;
        case "Disputa" :
            $order->update_status( 'on-hold', __( 'PagHiper: Pagamento em disputa. Para responder, faça login na sua conta Paghiper e procure pelo número da transação.', 'woocommerce-paghiper' ) );
            break;
        case "Estornado" :
            $order->update_status( 'refunded', __( 'PagHiper: Pagamento estornado. O valor foi ja devolvido ao cliente. Para mais informações, entre em contato com a equipe de atendimento Paghiper.' , 'woocommerce-paghiper', 'woocommerce-paghiper' ) );
            break;
    }
}

function check_ipn_response() {

    //TOKEN gerado no painel do PAGHIPER = TOKEN SECRETO
    $settings = get_option( 'woocommerce_paghiper_settings' );
    $token = $settings['token'];
    if(isset($_POST['idTransacao']) && isset($_POST['valorTotal'])) {

        // Trata os dados do Post Recebido do Paghiper
        $idTransacao = $_POST['idTransacao'];
        $dataTransacao = $_POST['dataTransacao'];
        $dataCredito = $_POST['dataCredito'];
        $valorOriginal = $_POST['valorOriginal'];
        $valorLoja = $_POST['valorLoja'];
        $valorTotal = $_POST['valorTotal'];
        $numeroParcelas = $_POST['numeroParcelas'];
        $status = $_POST['status'];
        $nomeCliente = $_POST['nomeCliente'];
        $emailCliente = $_POST['emailCliente'];
        $rgCliente = $_POST['rgCliente'];
        $cpfCliente = $_POST['cpfCliente'];
        $sexoCliente =$_POST['sexoCliente'];
        $razaoSocialCliente =$_POST['razaoSocialCliente'];
        $cnpjCliente =$_POST['cnpjCliente'];
        $notaFiscal =$_POST['notaFiscal'];
        $fraseFixa =$_POST['fraseFixa'];
        $enderecoCliente = $_POST['enderecoCliente'];
        $complementoCliente = $_POST['complementoCliente'];
        $bairroCliente = $_POST['bairroCliente'];
        $cidadeCliente = $_POST['cidadeCliente'];
        $estadoCliente = $_POST['estadoCliente'];
        $cepCliente = $_POST['cepCliente'];
        $frete = $_POST['frete'];
        $tipoFrete = $_POST['tipoFrete'];
        $vendedorEmail = $_POST['vendedorEmail'];
        $numItem = $_POST['numItem'];
        $idPlataforma = $_POST['idPlataforma'];
        $codRetorno = $_POST['codRetorno'];  
        $tipoPagamento = $_POST['tipoPagamento'];  
        $codPagamento = $_POST['codPagamento'];  
        $urlPagamento = $_POST['urlPagamento'];  
        $linhaDigitavel = $_POST['linhaDigitavel'];  

        $post_completo = array();
        foreach($_POST as $k => $v) {
            $post_completo[$k] = $v;
        }

        //Serialize the array.
        $serialized = serialize($post_completo);
         
        //Save the serialized array to a text file.
        file_put_contents('serialized.txt', $serialized);

        //For para receber os produtos
        for ($x=1; $x <= $numItem; $x++) {
            $produto_codigo = $_POST['produto_codigo_'.$x];
            $produto_descricao = $_POST['produto_descricao_'.$x];
            $produto_qtde = $_POST['produto_qtde_'.$x];
            $produto_valor = $_POST['produto_valor_'.$x];
            /* Após obter as variáveis dos produtos, grava no banco de dados.
            Se produto já existe, atualiza os dados, senão cria novo pedido. */
        }

        //PREPARA O POST A SER ENVIADO AO PAGHIPER PARA CONFIRMAR O RETORNO
        //INICIO - NAO ALTERAR//
        //Não realizar alterações no script abaixo//
        $post = "idTransacao=$idTransacao" .
        "&status=$status" .
        "&codRetorno=$codRetorno" .
        "&valorOriginal=$valorOriginal" .
        "&valorLoja=$valorLoja" .
        "&token=$token";
        $enderecoPost = "https://www.paghiper.com/checkout/confirm/"; 

        ob_start();
        $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $enderecoPost);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_HEADER, false);
         curl_setopt($ch, CURLOPT_TIMEOUT, 30);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         $resposta = curl_exec($ch);
         curl_close($ch);

         $confirmado = (strcmp ($resposta, "VERIFICADO") == 0);

    }
    //FIM - NAO ALTERAR//


     if (isset($confirmado) && false !== $confirmado) {
        
        //SE O POST FOR CONFIRMADO, ESSA AREA SERA HABILITADA.
        header( 'HTTP/1.1 200 OK' );
        // Guarda itens que vamos usar em um array para passar a função
        $data = array (
            'urlPagamento' => $urlPagamento, 
            'idTransacao' => $idTransacao,
            'dataTransacao' => $dataTransacao,
            'dataCredito' => $dataCredito,
            'status' => $status
            );
        valid_paghiper_ipn_request( $data, intval( $idPlataforma ) );
        //Executa a query para armazenar as informações no banco de dados
        
    } else {
        
        // SE O POST FOR NEGADO, ESSA AREA SERA HABILITADA    
        wp_die( esc_html__( 'Solicitação PagHiper Não Autorizada', 'woocommerce-paghiper' ), esc_html__( 'Solicitação PagHiper Não Autorizada', 'woocommerce-paghiper' ), array( 'response' => 401 ) );
        

    }
} ?>
