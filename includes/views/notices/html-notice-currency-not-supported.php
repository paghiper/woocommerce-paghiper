<?php
/**
 * Admin View: Notice - Currency not supported.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
	<p><strong><?php _e( 'Temos um problema!', 'paghiper-payments' ); ?></strong> <?php printf( __( 'O Paghiper não dá suporte a moeda <code>%s</code>. Só é possível receber em Real Brasileiro (R$).', 'paghiper-payments' ), get_woocommerce_currency() ); ?>
	</p>
</div>
