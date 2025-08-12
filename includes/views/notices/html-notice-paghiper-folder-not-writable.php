<?php
/**
 * Admin View: Notice - Paghiper folder unwritable missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="error">
	<p><strong><?php esc_html_e( 'Temos um problema!', 'woo-boleto-paghiper' ); ?></strong> <?php esc_html_e( 'O plug-in Paghiper precisa salvar arquivos para envio de boletos anexos. <br>Verifique com seu provedor se a pasta <strong>/wp-content/uploads/paghiper</strong> tem permissões de escrita e leitura.', 'woo-boleto-paghiper' ); ?></p>
</div>
