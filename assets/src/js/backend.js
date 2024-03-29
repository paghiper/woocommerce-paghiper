//=include functions/_copyPaghiperEmv.js

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

	$( '.paghiper-notice' ).on( 'click', '.ajax-action', function() {

		let noticeId 		= $(this).data('notice-key'),
			noticeAction 	= $(this).data('action');

		var data = {
			'action'	: 'paghiper_answer_notice',
			'noticeId'	: noticeId,
			'userAction': noticeAction
		};
		
		$.post( notice_params.ajaxurl, data, function() {
		});

		$(".paghiper-review-nag").hide();

	});

	// Provides maskable fields for date operations
	if(typeof $.fn.mask === 'function') {
		$(".date").mask("00/00/0000", {placeholder: "__/__/____", clearIfNotMatch: true});
	}

});