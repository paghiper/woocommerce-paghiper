<?php
/**
 * Admin View: Notice - Currency not supported.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
	<p><strong><?php _e( 'WooCommerce Boleto PagHiper foi desativado!', 'woocommerce-boleto' ); ?></strong>: <?php printf( __( 'Este plugin não da suporte a moeda <code>%s</code>. Só funciona com Real Brasileiro.', 'woocommerce-boleto' ), get_woocommerce_currency() ); ?>
	</p>
</div>
