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
		</strong>
		<?php esc_html_e( 'Sua versão do PHP é anterior a v5.6. Atualize para que o plugin da Paghiper funcione corretamente.', 'woo-boleto-paghiper' ); ?>
	</p>
</div>
