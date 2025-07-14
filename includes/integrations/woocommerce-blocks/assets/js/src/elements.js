import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { useElementOptions } from './use-element-options';
import { isCPF, isCNPJ } from 'validation-br';

const baseTextInputStyles = 'wc-block-gateway-input paghiper_tax_id p-Input-input Input p-Input-input--textRight';

/**
 * InlineTaxIdField component
 *
 * @param {Object} props Incoming props for the component.
 * @param {React.ReactElement} props.inputErrorComponent
 * @param {function(any):any} props.onChange
 */
export const InlineTaxIdField = ( {
	inputErrorComponent: ValidationInputError,
	onChange,
    gatewayName
} ) => {
	const [ isEmpty, setIsEmpty ] = useState( true );
	const [ isInvalid, setIsInvalid ] = useState( false );
    const [ isComplete, setIsComplete ] = useState( false );
	const [ fieldLabel, setFieldLabel ] = useState(__('CPF do Pagador', 'paghiper-payments'));
    const [ fieldInput, setFieldInput ] = useState('');
	const { options, isActive, isFocus, onActive, error, setError } = useElementOptions( {
		hideIcon: true,
	} );
	const errorCallback = ( event ) => {
		if ( event.error ) {
			setError( event.error.message );
		} else {
			setError( '' );
		}
		setIsEmpty( event.empty );
		onChange( event );

        if( !event.target.value ) {
            setIsEmpty( true );
        }
    }

    useEffect(() => {

        if(fieldInput.replace(/\D/g, '').length > 11) {
            setFieldLabel(__('CNPJ do Pagador', 'paghiper-payments'));
        } else {
            setFieldLabel(__('CPF do Pagador', 'paghiper-payments'));
        }

        if(!isEmpty) {
            if(!isFocus) {

                if(fieldInput.replace(/\D/g, '').length > 11 && fieldInput.replace(/\D/g, '').length < 14) {
                    setError(__('O número do seu CNPJ está incompleto.', 'paghiper-payments'));
                    setIsInvalid(true);
                } else if (fieldInput.replace(/\D/g, '').length < 11) {
                    setError(__('O número do seu CPF está incompleto.', 'paghiper-payments'));
                    setIsInvalid(true);
                }
            } else {
                if(fieldInput.replace(/\D/g, '').length == 11) {
                    // Valida CPF
                    if(!isCPF(fieldInput)) {
                        setError(__('O número do seu CPF está correto.', 'paghiper-payments'));
                        setIsInvalid(true);
                    } else {
                        setIsComplete(true);
                    }
                } else if(fieldInput.replace(/\D/g, '').length == 14) {
                    // Valida CNPJ
                    if(!isCNPJ(fieldInput)) {
                        setError(__('O número do seu CNPJ não está correto.', 'paghiper-payments'));
                        setIsInvalid(true);
                    } else {
                        setIsComplete(true);
                    }
                }
            }
        } else {
            setIsInvalid(false);
            setIsComplete(false);
        }


    }, [fieldInput, isFocus]);

    useEffect(() => {
        setIsInvalid(false);
        setIsComplete(false);
        setError('');
    }, [fieldInput]);

    const taxIdMaskBehavior = (val, e) => {
        return val.replace(/\D/g, '').length > 11 ? '00.000.000/0000-00' : '000.000.000-009';
    }

    // Initialize mask everytime we render the component
    useEffect(() => {

        if(typeof jQuery('.paghiper_tax_id').mask === "function") {

            jQuery('.paghiper_tax_id').mask(taxIdMaskBehavior, {
                onKeyPress: function(val, e, field, options) {
                    field.mask(taxIdMaskBehavior.apply({}, arguments), options);
                }
            });

        } else {
            console.log('Paghiper block failed to initialize TaxID mask')
        }
	
    }, [])

	return (
		<>
            <div className="wc-block-components-form">
                <div className={"wc-block-gateway-container wc-block-components-text-input wc-inline-tax-id-element paghiper-taxid-fieldset" + (isActive || !isEmpty ? ' is-active' : '')}>
                    <input 
                        type="text"
                        id="wc-paghiper-inline-tax-id-element"
                        name={"_" + gatewayName + "_cpf_cnpj"}
                        className={ baseTextInputStyles + (isEmpty ? ' empty Input--empty' : '') + (isInvalid ? ' invalid' : '') + (isComplete ? ' valid' : '')}
                        onBlur={ () => onActive( isEmpty, false ) }
                        onFocus={ () => onActive( isEmpty, true ) }
                        onChange={ errorCallback }
                        onInput={ e => setFieldInput(e.target.value) }
                        aria-label={ fieldLabel }
                        required
                        title
                    />
                    <label htmlFor="wc-paghiper-inline-tax-id-element">{ fieldLabel }</label>
                    <ValidationInputError errorMessage={ error } />
                </div>
            </div>
		</>
	);
};