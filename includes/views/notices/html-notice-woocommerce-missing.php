<?php
/**
 * Admin View: Notice - WooCommerce missing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_slug = 'woocommerce';

if ( current_user_can( 'install_plugins' ) ) {
	$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ), 'install-plugin_' . $plugin_slug );
} else {
	$url = 'http://wordpress.org/plugins/' . $plugin_slug;
}
?>

<div class="error">
	<p><strong><?php _e( 'WooCommerce PagHiper foi desativado!', 'woocommerce-boleto' ); ?></strong> <?php printf( __( 'Este plugin precisa do %s para funcionar. Instale ou ative para receber pedidos.', 'woocommerce-boleto' ), '<a href="' . esc_url( $url ) . '">' . __( 'WooCommerce', 'woocommerce-boleto' ) . '</a>' ); ?></p>
</div>
