<?php
/**
 * Admin View: Notice - WooCommerce missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="error">
	<p><strong><?php esc_html_e( 'Temos um problema!', 'woo-boleto-paghiper' ); ?></strong> <?php esc_html_e( 'O Paghiper precisa que seu PHP tenha a extensão GD disponível, caso contrário, não será possível exibir códigos de barras dos seus boletos nos e-mails. <br>Entre em contato com seu provedor de hospedagem, informando o problema.', 'woo-boleto-paghiper' ); ?></p>
</div>
