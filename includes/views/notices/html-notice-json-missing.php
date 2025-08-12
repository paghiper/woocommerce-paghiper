<?php
/**
 * Admin View: Notice - WooCommerce missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="error">
	<p>
		<strong>
			<?php esc_html_e( 'Temos um problema!', 'woo-boleto-paghiper' ); ?>
		</strong>: <?php esc_html_e( 'Este plugin precisa que seu PHP esteja compilado com suporte a JSON! Entre em contato com seu provedor de hospedagem, informando o problema.', 'woo-boleto-paghiper' ); ?></p>
</div>
