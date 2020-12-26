jQuery(document).ready( function($){
	
	// Deal with dismissable notices
	$( document ).on( 'click', '.paghiper-dismiss-notice .notice-dismiss', function() {
		let noticeId = $(this).parent().data('notice-id');
		var data = {
			action: 'paghiper_dismiss_notice',
			notice: noticeId
		};
		
		$.post( notice_params.ajaxurl, data, function() {
		});
	});

});