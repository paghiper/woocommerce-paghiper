//=require functions/_copyPaghiperEmv.js

jQuery(document).ready( function($){
	
	// Deal with dismissable notices
	$( '.paghiper-dismiss-notice' ).on( 'click', '.notice-dismiss', function() {
		let noticeId = $(this).parent().data('notice-id');
		var data = {
			action: 'paghiper_dismiss_notice',
			notice: noticeId
		};
		
		$.post( notice_params.ajaxurl, data, function() {
		});
	});

	// Provides maskable fields for date operations
	$(".date").mask("00/00/0000", {placeholder: "__/__/____", clearIfNotMatch: true});

});