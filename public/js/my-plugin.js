( function($) {
    
    jQuery(document).ready(function() {


        jQuery('#my-plugin-form').on( 'submit', function(event) {
            event.preventDefault();

            let thisForm = jQuery(this);

            let formData = thisForm.serializeArray();

            jQuery.ajax({
                method: 'POST',
                url: myPluginScriptObj.ajaxURL,
                data: {
                    action: 'post_submission_action',
                    nonce: formData[1].value,
                    postTitle: formData[2].value,
                    postContent: formData[3].value,
                },
                success: function(response) {
                    jQuery('#form-message').html( response.message );
                    if ( response.success ) {
                        thisForm.trigger('reset');
                    }
                },
                error: function() {

                },
                always: function() {
                    
                }
            });
        } );
    });

} ) (jQuery);