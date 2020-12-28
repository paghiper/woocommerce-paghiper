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

		$due_date_weekday = $due_date->format('N');

		if ($due_date_weekday >= 6) {
			$date_diff = (8 - $due_date_weekday);
			$due_date->modify( "+{$date_diff} days" );
			
			$paghiper_data = get_post_meta( $order->id, 'wc_paghiper_data', true );
			$paghiper_data['order_transaction_due_date'] = $due_date->format( 'Y-m-d' );

			$update = update_post_meta( $order->id, 'wc_paghiper_data', $paghiper_data );
			if(function_exists('update_meta_cache'))
				update_meta_cache( 'shop_order', $order->id );

			if($update) {
				$order->add_order_note( sprintf( __( 'Data de vencimento ajustada para %s', 'woo_paghiper' ), $due_date->format('d/m/Y') ) );
			} else {
				var_dump($update);
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