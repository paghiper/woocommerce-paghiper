import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { ValidationInputError } from '@woocommerce/blocks-checkout';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import { InlineTaxIdField } from './elements';

// PIX Gateway
const pixSettings 		= getSetting( 'paghiper_pix_data', {} );
const defaultPixLabel 	= __( 'PIX Paghiper', 'woo-boleto-paghiper' )
const label 			= decodeEntities( pixSettings.title ) || defaultPixLabel

const Content = ( props ) => {

	const { eventRegistration, emitResponse } = props;
	const { onPaymentSetup } = eventRegistration;

	
    const [ taxID, setTaxID ] = useState('');
    const [ payerName, setPayerName ] = useState('');

	useEffect( () => {
		const unsubscribe = onPaymentSetup( async () => {
			// Here we can do any processing we need, and then emit a response.
			// For example, we might validate a custom field, or perform an AJAX request, and then emit a response indicating it is valid or not.

			const paghiperTaxId = taxID;
			const paghiperTaxIdIsValid = !! paghiperTaxId.length;
			const paghiperTaxIdFieldName = "_" + props.gatewayName + "_cpf_cnpj";

			if ( paghiperTaxIdIsValid ) {
				return {
					type: emitResponse.responseTypes.SUCCESS,
					meta: {
						paymentMethodData: {
							[ paghiperTaxIdFieldName ]: paghiperTaxId,
						},
					},
				};
			}

			return {
				type: emitResponse.responseTypes.ERROR,
				message: 'There was an error',
			};
		} );
		// Unsubscribes when this component is unmounted.
		return () => {
			unsubscribe();
		};
	}, [
		taxID,
		emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentSetup,
	] );

	const onChange = ( paymentEvent ) => {
		if ( paymentEvent.error ) {
			console.log('Paghiper: Payment Error');
		}

		setTaxID(paymentEvent.target.value.replace(/\D/g, ''));
	}

	return (
		<>
			<>{decodeEntities( props.gatewayDescription || '' )}</>
			<InlineTaxIdField 
				gatewayName={ props.gatewayName }
				onChange={ onChange } 
				inputErrorComponent={ ValidationInputError }
			/>
		</>)
}

const Label = ( props ) => {
	const { PaymentMethodLabel } = props.components

	return <PaymentMethodLabel text={ label } />
}

const PaghiperPix = {
	name: "paghiper_pix",
	label: <Label />,
	content: <Content gatewayName="paghiper_pix" gatewayDescription={ pixSettings.description } />,
	edit: <Content gatewayName="paghiper_pix" gatewayDescription={ pixSettings.description } />,
	canMakePayment: () => true,
	ariaLabel: label,
	paymentMethodId: "paghiper_pix",
	supports: {
		features: pixSettings.supports,
	}
}

registerPaymentMethod( PaghiperPix );

// Billet
const billetSettings 		= getSetting( 'paghiper_billet_data', {} )
const defaultBilletLabel 	= __( 'Boleto Paghiper', 'woo-boleto-paghiper' )
const billetLabel 			= decodeEntities( billetSettings.title ) || defaultBilletLabel

const BilletLabel = ( props ) => {
	const { PaymentMethodLabel } = props.components
	return <PaymentMethodLabel text={ billetLabel } />
}

registerPaymentMethod( {
	name: "paghiper_billet",
	label: <BilletLabel />,
	content: <Content gatewayName="paghiper_billet" gatewayDescription={ billetSettings.description } />,
	edit: <Content gatewayName="paghiper_billet" gatewayDescription={ billetSettings.description } />,
	canMakePayment: () => true,
	ariaLabel: billetLabel,
	supports: {
		features: billetSettings.supports,
	}
} );