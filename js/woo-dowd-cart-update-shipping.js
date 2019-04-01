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
		setTimeout(
			function() 
			{
				jQuery("[name='calc_shipping']").removeAttr('disabled').trigger("click");
				console.log('triggered cart page shipping update event');
			}, 2000);
	});
});
