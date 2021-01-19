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

jQuery( document ).ready(function($) {

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
});