jQuery(document).ready(function($) {
    $('#my-button').on('click', function() {
        $.ajax({
            url: pluginData.ajax_url,
            type: 'POST',
            data: {
                action: 'my_custom_action',
                name: 'Amin'
            },
            success: function(response) {
                if (response.success) {
                    console.log(response.data.message);
                } else {
                    console.error('Something went wrong');
                }
            },
            error: function(response) {
                console.log(response.responseText);
            }
        });
    });
});
