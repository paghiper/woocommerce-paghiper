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
	<p>
		<strong>
			<?php __( 'WooCommerce PagHiper foi desativado!', 'woo-boleto-paghiper' ); ?>
		</strong>
		<?php /* translators: %s: Term used to denote plug-in. That term may be translated/changed as well */ ?>
		<?php printf( esc_html__( 'Existem vários %s da Paghiper instalados. Um ou mais deles foram desativados. Remova-os para deixar de ver este aviso.', 'woo-boleto-paghiper' ), '<a href="' . esc_url( $url ) . '">' . esc_html__( 'plug-ins', 'woo-boleto-paghiper' ) . '</a>' ); ?></p>
</div>