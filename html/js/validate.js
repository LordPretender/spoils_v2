$(function() {

    $('input#comment-submit').click(function() {

        var name    = $('input#name').val();
        var email   = $('input#email').val();
		var website = $('input#website').val();
        var comment = $('textarea#comment').val();

		// AJAX Call
        $.ajax({
            type: 'post',
            url:  'sendEmail.php',
            data: 'name=' + name + '&email=' + email + '&website' + website + '&comment=' + comment,

            success: function(results) {
                $('div#outcome').html(results);
            }
        });
		return false;
    });
});	