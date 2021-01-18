<?php
/**
 * Admin View: Notice - Multiple active instances. Some plugins deactivated.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( current_user_can( 'install_plugins' ) ) {
	$url = self_admin_url( 'plugins.php');
} else {
	$url = '#';
}
?>

<div class="error">
	<p><strong><?php __( 'WooCommerce PagHiper foi desativado!', 'woocommerce-boleto' ); ?></strong> <?php printf( __( 'Existem vários %s da Paghiper instalados. Um ou mais deles foram desativados. Remova-os para deixar de ver este aviso.', 'woocommerce-boleto' ), '<a href="' . esc_url( $url ) . '">' . __( 'plug-ins', 'woocommerce-boleto' ) . '</a>' ); ?></p>
</div>