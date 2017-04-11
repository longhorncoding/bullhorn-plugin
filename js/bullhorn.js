(function($) {	

	$(window).load(function(){
		$('.bullhorn-wrapper .viewDetails').click(function(){
			var current = $(this).siblings('.bullhorn-description');
			$('.bullhorn-description').not(current).slideUp();
			current.slideToggle();
		});
		$('.cv-form #cv-form').submit(function(){
			if ($('.cv-form #firstName').val() != '' &&
				$('.cv-form #lastName').val() != '' &&
				$('.cv-form #email').val() != '' &&
				validateEmail($('.cv-form #email').val()) &&
				$('.cv-form #phone').val() != '' &&
				$('.cv-form #street1').val() != '' &&
				$('.cv-form #city').val() != '' &&
				$('.cv-form #state').val() != '' &&
				$('.cv-form #zip').val() != '' &&
				$('.cv-form #fileToUpload').get(0).files.length !== 0 )
			{				
				return true;
			}
			$('.cv-form #msg').html('Invalid required fields').fadeIn();
			return false;
		});
		function validateEmail(email) {
		  var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		  return re.test(email);
		}
	});

})(jQuery);