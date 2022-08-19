//=include functions/_copyPaghiperEmv.js

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

	let paghiperFieldsetContainers = document.querySelectorAll('.wc-paghiper-form');

	[].forEach.call(paghiperFieldsetContainers, (paghiperFieldsetContainer) => {

		let ownTaxIdField 		= paghiperFieldsetContainer.querySelector('.paghiper-taxid-fieldset'),
			ownPayerNameField 	= paghiperFieldsetContainer.querySelector('.paghiper-payername-fieldset');

		let hasTaxField = false,
			hasPayerNameField = false;

			if(ownTaxIdField) {
				if(otherTaxIdFields.length > 0) {
					ownTaxIdField.classList.add('paghiper-hidden');
				} else {
					ownTaxIdField.classList.remove('paghiper-hidden');	
					hasTaxField = true;
				}
			}

			if(ownPayerNameField) {
				if(otherPayerNameFields.length > 0) {
					ownPayerNameField.classList.add('paghiper-hidden');
				} else {
					ownPayerNameField.classList.remove('paghiper-hidden');
					hasPayerNameField = true;
				}
			}

		if(!hasTaxField && !hasPayerNameField) {
			paghiperFieldsetContainer.classList.add('paghiper-hidden');
		} else {
			paghiperFieldsetContainer.classList.remove('paghiper-hidden');
		}
	});

}