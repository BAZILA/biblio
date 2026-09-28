/* @Copyright ((c) plasma-web.ru
 */

jQuery(document).ready(function($) {
    function changeset() {
        if (!($('.qfmodtype input:checked').val()*1)) {
            $('.formfilds').closest('.control-group').show();
            $('input[name="jform[params][id]"]').attr('required', true);
        }
        else {
            $('.formfilds').closest('.control-group').hide();
            $('input[name="jform[params][id]"]').attr('required', false);
        }
    }
    changeset();

    $('.qfmodtype').on('change', function() {
        changeset();
    })
});
