<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets URL.
 *
 * @return string
 */
function wc_paghiper_assets_url() {
	return plugin_dir_url( dirname( __FILE__ ) ) . 'assets/';
}

/**
 * Get paghiper URL from order key.
 *
 * @param  string $code
 *
 * @return string
 */
function wc_paghiper_get_paghiper_url( $code ) {
	return WC_Paghiper::get_paghiper_url( $code );
}

/**
 * Get paghiper URL from order key.
 *
 * @param  int $order_id
 *
 * @return string
 */
function wc_paghiper_get_paghiper_url_by_order_id( $order_id ) {
	$order_id = trim(str_replace('#', '', $order_id ));
	$order    = new WC_Order( $order_id );

	if ( isset( $order->order_key ) ) {
		return wc_paghiper_get_paghiper_url( $order->order_key );
	}

	return '';
}

/**
 * Activate logs, if enabled from config
 *
 * @param  int $order_id
 *
 * @return string
 */
function wc_paghiper_initialize_log( $debug_settings ) {
	return ( 'yes' == $debug_settings ) ? ((function_exists( 'wc_get_logger' )) ? wc_get_logger() : new WC_Logger()) : NULL;
}

/**
 * Adds an item do log, if enabled from config
 *
 * @return object
 */
function wc_paghiper_add_log( $log, $message ) {
	$gateway_id = 'paghiper';
	return ($log && $log->add( $gateway_id, $message )) ? TRUE : FALSE;
}

/**
 * Adds extra days to the billet due date, if option is properly enabled
 * 
 * @return object
 */
function wc_paghiper_add_workdays( $due_date, $order, $workday_settings = NULL, $format) {

	if($due_date && $workday_settings == 'yes') {

		$due_date_weekday = ($due_date)->format('N');

		if ($due_date_weekday >= 6) {
			$date_diff = (8 - $due_date_weekday);
			$due_date->modify( "+{$date_diff} days" );
			
			$paghiper_data_query = get_post_meta( $order->id, 'wc_paghiper_data', true );

			$paghiper_data = (is_array($paghiper_data_query)) ? $paghiper_data_query : [];
			$paghiper_data['order_transaction_due_date'] = $due_date->format( 'Y-m-d' );

			$update = update_post_meta( $order->id, 'wc_paghiper_data', $paghiper_data );
			if(function_exists('update_meta_cache'))
				update_meta_cache( 'shop_order', $order->id );

			if($update) {
				$order->add_order_note( sprintf( __( 'Data de vencimento ajustada para %s', 'woo_paghiper' ), $due_date->format('d/m/Y') ) );
			} else {
				$log = wc_paghiper_initialize_log( 'yes' );
				wc_paghiper_add_log( $log, sprintf( 'Pedido #%s: Erro ao salvar data de vencimento: .', $order->id, var_export($update, TRUE) ) );

				$order->add_order_note( sprintf( __( 'Data de vencimento deveria ser ajustada para %s mas houve um erro ao salvar a nova data.', 'woo_paghiper' ), $due_date->format('d/m/Y') ) );
			}
		}

	}

	if($format == 'days') {

		$today_date = new \DateTime();
		$today_date->setTimezone(new DateTimeZone('America/Sao_Paulo'));
		$return = (int) $today_date->diff($due_date)->format("%r%a");
	} else {
		$return = $due_date;
	}

	return apply_filters('woo_paghiper_due_date', $return, $order);
}

/**
 * Checks if an autoload include is performed successfully. If not, include necessary files
 * 
 * @return boolean
 */

function wc_paghiper_check_sdk_includes( $log = false ) {

	if (!\function_exists('PagHiperSDK\\GuzzleHttp\\uri_template') || !\function_exists('PagHiperSDK\\GuzzleHttp\\choose_handler')) {

		if($log) {
			wc_paghiper_add_log( $log, sprintf( 'Erro: O PHP SDK não incluiu todos os arquivos necessários por alguma questão relacionada a PSR-4 ou por configuração de ambiente.' ) );
		}

		require_once WC_Paghiper::get_plugin_path() . '/ralouphie/getallheaders/src/getallheaders.php';
		require_once WC_Paghiper::get_plugin_path() . '/guzzlehttp/promises/src/functions_include.php';
		require_once WC_Paghiper::get_plugin_path() . '/guzzlehttp/psr7/src/functions_include.php';
		require_once WC_Paghiper::get_plugin_path() . '/guzzlehttp/guzzle/src/functions_include.php';

		if($log) {
			wc_paghiper_add_log( $log, sprintf( 'Erro contornado: O plug-in se recuperou do erro mas talvez você queira verificar questões relacionadas a compilação ou configuração da sua engine PHP.' ) );
		}

	}

	return true;
}