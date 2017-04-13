<?php
/**
 * WooCommerce Boleto Template.
 */

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
	$order_id = woocommerce_get_order_id_by_order_key( $ref );

	if ( $order_id ) {
		// Gets the data saved from boleto.
		$order = new WC_Order( $order_id );
		$order_data = get_post_meta( $order_id, 'wc_paghiper_data', true );

		// Gets current bank.
		$settings = get_option( 'woocommerce_paghiper_settings' );
		$bank = 'paghiper';

		if ( $bank ) {

			// Sets the boleto details.
			$shop_name = get_bloginfo( 'name' );

			// Sets the boleto data.
			$data = array();
			foreach ( $order_data as $key => $value ) {
				$data[ $key ] = sanitize_text_field( $value );
			}

			// Set the ticket total.
			$data['valor_boleto'] = number_format( (float) $order->get_total(), 2, ',', '' );

			// Client data.
			if ( ! empty( $order->billing_cnpj ) ) {
				$data['razao_social'] = $order->billing_company;
			}
				$data['sacado'] = $order->billing_first_name . ' ' . $order->billing_last_name;

			// Formatted Addresses
			$address_fields = array(
				'first_name' => '',
				'last_name'  => '',
				'company'    => '',
				'address_1'  => $order->billing_address_1,
				'address_2'  => $order->billing_address_2,
				'city'       => $order->billing_city,
				'state'      => $order->billing_state,
				'postcode'   => $order->billing_postcode,
				'country'    => $order->billing_country
			);

			//print_r($order);

			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
				$address = WC()->countries->get_formatted_address( $address_fields );
			} else {
				global $woocommerce;
				$address = $woocommerce->countries->get_formatted_address( $address_fields );
			}

			// Get Extra Checkout Fields for Brazil options.
			$wcbcf_settings = get_option( 'wcbcf_settings' );
			$customer_document = '';
			if ( 0 != $wcbcf_settings['person_type'] ) {
				if ( ( 1 == $wcbcf_settings['person_type'] && 1 == $order->billing_persontype ) || 2 == $wcbcf_settings['person_type'] ) {
					$data['cpf'] = $order->billing_cpf;
				}

				if ( ( 1 == $wcbcf_settings['person_type'] && 2 == $order->billing_persontype ) || 3 == $wcbcf_settings['person_type'] ) {
					$data['cnpj'] = $order->billing_cnpj;
				}
			}

			// Set the customer data.
			if ( '' != $customer_document ) {
				$data['endereco1'] = $customer_document;
				$data['endereco2'] = sanitize_text_field( str_replace( array( '<br />', '<br/>' ), ', ', $address ) );
			} else {
				$data['endereco1'] = sanitize_text_field( str_replace( array( '<br />', '<br/>' ), ', ', $address ) );
				$data['endereco2'] = '';
			}

			$dadosboleto = apply_filters( 'wcboleto_data', $data, $order );

			// Include bank templates.
			include WC_Paghiper::get_plugin_path() . 'includes/paghiper/layout.php';

			exit;
		}
	}
}

// If an error occurred is redirected to the homepage.
wp_redirect( home_url() );
exit;
