/**
 * This function triggers the update of the shipping cost when the shipping day has changed.
 *
 * @namespace woo_dowd
 * @since 1.0 
*/
// Watch the date field for a change and invoke the shipping updates if it changes
jQuery(document).ready(function($){
	console.log('my document ready');

	jQuery.fn.watch = function( id, fn ) {
		return this.each(function(){
			var self = this;
			var oldVal = self[id];
			$(self).data(
				'watch_timer',
				setInterval(function(){
					if (self[id] !== oldVal) {
						fn.call(self, id, oldVal, self[id]);
						oldVal = self[id];
					}
				}, 100)
			);

		});

	};
 
	$('#e_deliverydate').watch('value', function(propName, oldVal, newVal){
		console.log('cart update process initiated');
		var dateObject = $( '#e_deliverydate' ).datepicker( "getDate" );
		var deliverydate = $.datepicker.formatDate( "yy-mm-dd", dateObject );
		
		var data = {
			'action':       'woo_dowd_delivery_date_capture',
			'_ajax_nonce':  woo_dowd_ajax_object.nonce,
			'deliverydate': deliverydate
		};
		
		$.post( woo_dowd_ajax_object.ajax_url , data, function( response ) {
			console.log('cart update ajax call about to start');
			$("[name='calc_shipping']").removeAttr('disabled').trigger("click");
		});
	});
});
