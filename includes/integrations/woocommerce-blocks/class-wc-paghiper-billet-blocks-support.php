<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Paghiper_Billet_Gateway_Blocks_Support extends AbstractPaymentMethodType {
	
	private $gateway;
	
	protected $settings;
	protected $name = 'paghiper_billet';

	public function initialize() {
		$this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	public function is_active() {
		return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
	}

	/*public function get_payment_method_script_handles() {

		wp_register_script(
			'wc-paghiper-billet-blocks-integration',
			plugin_dir_url( __DIR__ ) . '/woocommerce-blocks/assets/js/build/paghiper-billet.js',
			array(
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
			),
			null,
			true
		);

		return array( 'wc-paghiper-billet-blocks-integration' );

	}*/

	public function get_payment_method_data() {
		return [
			'title'        => $this->get_setting( 'title' ),
			'description'  => $this->get_setting( 'description' ),
			'supports'    	=> array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}

}