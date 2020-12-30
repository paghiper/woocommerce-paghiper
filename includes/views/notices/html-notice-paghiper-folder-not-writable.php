<?php
/**
 * Admin View: Notice - Paghiper folder unwritable missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="error">
	<p><strong><?php _e( 'Temos um problema!', 'woocommerce-boleto' ); ?></strong> <?php echo __( 'O plug-in Paghiper precisa salvar arquivos para envio de boletos anexos. <br>Verifique com seu provedor se a pasta <strong>/wp-content/uploads/paghiper</strong> tem permissões de escrita e leitura.', 'woocommerce-boleto' ); ?></p>
</div>
