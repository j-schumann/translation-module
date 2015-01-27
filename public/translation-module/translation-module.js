(function($) {
    window.TranslationModule = window.TranslationModule || {

    };


    // initialize ajax-forms on page load
    $(document).ready(function() {

        $("#translation-string").on('keyup change', 'textarea[id^=string-translations]', function(e) {
            if (e.currentTarget.value) {
                var id = e.currentTarget.id;
                id = id.substring(0, id.length - 11);
                $('#'+id+'isNull').attr('checked', false);
            }
        });

        $("#translation-string").on('change', 'input[id^=string-translations]', function(e) {
            var id = e.currentTarget.id;
            id = id.substring(0, id.length - 6);
            if (e.currentTarget.checked) {
                $('#'+id+'translation').attr('disabled', 'disabled');
            } else {
                $('#'+id+'translation').removeAttr('disabled');
            }
        });
    });
}(jQuery));
