<?php

/**
 * Add the custom variable to the list
 */
add_filter( 'automatewoo/variables', 'paghiper_automatewoo_variables' );

/**
 * @param $variables array
 * @return array
 */
function paghiper_automatewoo_variables( $variables ) {

	$variables['order']['paghiper_digitable_line'] 	= new Paghiper_Variable_Order_DigitableLine();
	$variables['order']['paghiper_barcode'] 		= new Paghiper_Variable_Order_Barcode();
	$variables['order']['paghiper_due_date'] 		= new Paghiper_Variable_Order_DueDate();

	return $variables;
}

class Paghiper_Variable_Order_DigitableLine extends AutomateWoo\Variable {

	/** @var bool - whether to allow setting a fallback value for this variable  */
	public $use_fallback = false;

	public function load_admin_details() {
		$this->description = __( "Displays the PIX digitable for a Paghiper transaction", 'paghiper');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	public function get_value( $order, $parameters ) {

		// Billet re-emission
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';
		$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );

		if($paghiperTransaction)
			return $paghiperTransaction->_get_digitable_line();

		return false;
	}

}

class Paghiper_Variable_Order_Barcode extends AutomateWoo\Variable {

	/** @var bool - whether to allow setting a fallback value for this variable  */
	public $use_fallback = false;

	public function load_admin_details() {
		$this->description = __( "Displays the scanable code for a Paghiper transaction", 'paghiper');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	public function get_value( $order, $parameters ) {

		// Billet re-emission
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';
		$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );

		if($paghiperTransaction)
			return sprintf("<img src='%s' title='Código de barras para pagamento deste pedido.'>", $paghiperTransaction->_get_barcode());

		return false;
	}

}

class Paghiper_Variable_Order_DueDate extends AutomateWoo\Variable {

	/** @var bool - whether to allow setting a fallback value for this variable  */
	public $use_fallback = false;

	public function load_admin_details() {
		$this->description = __( "Displays the due date for a Paghiper transaction", 'paghiper');
	}

	/**
	 * @param $order WC_Order
	 * @param $parameters array
	 * @return string
	 */
	public function get_value( $order, $parameters ) {

		// Billet re-emission
		require_once WC_Paghiper::get_plugin_path() . 'includes/class-wc-paghiper-transaction.php';
		$paghiperTransaction = new WC_PagHiper_Transaction( $order->get_id() );

		if($paghiperTransaction)
			return $paghiperTransaction->_get_due_date();

		return false;
	}

}