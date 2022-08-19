function copyPaghiperEmv() {

	// Start with objects to be selected
	let paghiperEmvBlock 	= document.querySelector('.paghiper-pix-code');
	let targetPixCode 		= paghiperEmvBlock.querySelector('textarea');
	let targetButton 		= paghiperEmvBlock.querySelector('button');

	// Select the text field
	targetPixCode.select();
	targetPixCode.setSelectionRange(0, 99999); /* For mobile devices */
  
	// Copy the text inside the text field
	navigator.clipboard.writeText(targetPixCode.value);

	// Store selection range insie button dataset
	targetButton.dataset.originalText = targetButton.innerHTML;
	targetButton.innerHTML = 'Copiado!';

	setTimeout(function(targetButton) {

		// Restore original text from dataset store value
		let originalText = targetButton.dataset.originalText;
		targetButton.innerHTML = originalText;

		// Remove selection range
		document.getSelection().removeAllRanges();
	}, 2000, targetButton,);

}