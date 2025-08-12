<?php
/**
 * Admin options screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reviews_url = '';

?>

<h3><?php echo esc_html($this->method_title); ?></h3>

<?php
	if ( 'yes' == $this->get_option( 'enabled' ) ) {
		if ( ! $this->using_supported_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
			include 'notices/html-notice-currency-not-supported.php';
		}
	}
?>

<?php echo wp_kses_post( wpautop( $this->method_description ) ); ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
