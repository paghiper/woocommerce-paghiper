<?php
/**
 * Admin View: Review nag.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<?php echo sprintf('<div class="paghiper-notice paghiper-review-nag success updated">
		<p><strong>%s</strong> %s</p>
		<p>
			<a data-action="set" data-notice-key="review_done" href="%s" class="ajax-action button button-primary" target="_blank"><span class="dashicons dashicons-yes-alt"></span> %s</a>
			<a data-action="set" data-notice-key="review_done" href="%s" class="ajax-action button"><span class="dashicons dashicons-thumbs-up"></span> %s</a>
			<a data-action="delete" data-notice-key="install_date" href="%s" class="ajax-action button"><span class="dashicons dashicons-clock"></span> %s</a>
			<a data-action="set" data-notice-key="review_ignore" href="%s" class="ajax-action button"><span class="dashicons dashicons-dismiss"></span> %s</a>
		</p>
	</div>', 
__('Queremos saber sua opinião!', 'woo-boleto-paghiper'),
__('Você tem recebido seus pagamentos com a Paghiper a alguns dias. Conte pra nós como tem sido usar o plugin da Paghiper! Leva só 2 minutinhos.', 'woo-boleto-paghiper'), 
'https://wordpress.org/support/plugin/woo-boleto-paghiper/reviews/#new-post', __('Claro, agora!', 'woo-boleto-paghiper'),
'#', __('Ja fiz isso', 'woo-boleto-paghiper'),
'#', __('Talvez depois', 'woo-boleto-paghiper'),
'#', __('Deixa pra lá', 'woo-boleto-paghiper')
) ?>