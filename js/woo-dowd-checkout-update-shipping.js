/**
 * This function triggers the update of the shipping cost when the shipping day has changed.
 *
 * @namespace woo_dowd
 * @since 1.0 
*/
// Watch the date field for a change and invoke the shipping updates if it changes
jQuery(document).ready(function($){
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
 
	$('#h_deliverydate').watch('value', function(propName, oldVal, newVal){
		setTimeout(
			function() 
			{
				jQuery('body').trigger("update_checkout");
				console.log('triggered checkout page shipping update event');
			}, 2000);
	});
});
