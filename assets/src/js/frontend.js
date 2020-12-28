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