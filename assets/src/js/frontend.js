jQuery( document ).ready(function($) {

	// Masking function for out TaxID field
	if(typeof $('.paghiper_tax_id').mask === "function") {

		function initializeMask() {
			var taxIdMaskBehavior = function (val) {
				return val.replace(/\D/g, '').length > 11 ? '00.000.000/0000-00' : '000.000.000-009';
			}
	
			$('.paghiper_tax_id').mask(taxIdMaskBehavior, {
				clearIfNotMatch: true,
				placeholder: "___.___.___-__",
				onKeyPress: function(val, e, field, options) {
					field.mask(taxIdMaskBehavior.apply({}, arguments), options);
				}
			});
		}

		$( document.body ).on('updated_checkout', function (event) {
			initializeMask();
		});

		initializeMask();
	}

	// Fallback for when AJAX cart update is not available.
	$( document.body ).on('updated_checkout', function (event) {
		checkForTaxIdFields()
	});

	checkForTaxIdFields();

});

function checkForTaxIdFields() {
	let otherTaxIdFields 		= document.querySelectorAll('[name="billing_cpf"], [name="billing_cnpj"]'),
		otherPayerNameFields 	= document.querySelectorAll('[name="billing_first_name"], [name="billing_company"]');

	let ownTaxIdField 		= document.querySelector('.paghiper-taxid-fieldset'),
		ownPayerNameField 	= document.querySelector('.paghiper-payername-fieldset');

	if(otherTaxIdFields.length > 0) {
		ownTaxIdField.classList.add('paghiper-hidden');
	} else {
		ownTaxIdField.classList.remove('paghiper-hidden');
	}

	if(otherPayerNameFields.length > 0) {
		ownPayerNameField.classList.add('paghiper-hidden');
	} else {
		ownPayerNameField.classList.remove('paghiper-hidden');
	}
}

function copyPaghiperEmv() {

	let paghiperEmvBlock = document.querySelector('.paghiper-pix-code');

	let targetPixCode = paghiperEmvBlock.querySelector('textarea');
	let targetButton = paghiperEmvBlock.querySelector('button');

	/* Select the text field */
	targetPixCode.select();
	targetPixCode.setSelectionRange(0, 99999); /* For mobile devices */
  
	/* Copy the text inside the text field */
	document.execCommand("copy");
	document.getSelection().collapseToEnd();

	targetButton.dataset.originalText = targetButton.innerHTML;
	targetButton.innerHTML = 'Copiado!';

	setTimeout(function(targetButton) {
		let originalText = targetButton.dataset.originalText;
		targetButton.innerHTML = originalText;
	}, 2000, targetButton);

}