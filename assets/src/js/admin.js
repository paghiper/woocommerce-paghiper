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

});