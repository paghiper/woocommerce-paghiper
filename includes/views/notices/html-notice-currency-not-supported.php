<?php
/**
 * Admin View: Notice - Currency not supported.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error">
	<p>
		<strong>
			<?php esc_html_e( 'Temos um problema!', 'woo-boleto-paghiper' ); ?>
		</strong>
		<?php 
		printf( 
			wp_kses(
				// translators: %s: Currency code
				esc_html__( 'O Paghiper não dá suporte a moeda <code>%s</code>. Só é possível receber em Real Brasileiro (R$).', 'woo-boleto-paghiper' ), 
				['code'=>[]]
			), 
			esc_html(get_woocommerce_currency()) ); ?>
	</p>
</div>
