jQuery( document ).ready( function( $ ) { 
    function ccpp_setCookie() {
        const cookieAcceptButton = $('#cookie-accept-button');
        if (cookieAcceptButton !== null) {
            cookieAcceptButton.on('click', function(e) {
                e.preventDefault();
                $.ajax({
                url     :   ccpp_script_ajax_object.ajax_url,   // wp_localize_script -> jlplg_lovecoding_script_ajax_object.ajax_url
                method  :   'post',
                dataType:   'json',
                data    :   { action: 'set_cookie_ajax' },      // action: function, that is invoked by ajax (full name: jlplg_lovecoding_set_cookie_ajax)
                success :   function(response) {
                                if (response === 'cookies-added') {
                                    $('#ccpp-cookie-info-container').css('display', 'none');
                                }
                },
                error   :   function(){
                                console.log('connection error ');
                }
                })
                // 
            });
        }
    }
    ccpp_setCookie();
});
