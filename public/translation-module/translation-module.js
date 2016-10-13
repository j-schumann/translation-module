(function($) {
    window.TranslationModule = window.TranslationModule || {};

    // initialize forms on page load
    $(document).ready(function() {
        $("#translation-entry").on('keyup change', 'textarea[id^=entry-translations]', function(e) {
            if (e.currentTarget.value) {
                var id = e.currentTarget.id;
                id = id.substring(0, id.length - 11);
                $('#'+id+'isNull').attr('checked', false);
            }
        });

        $("#translation-entry").on('change', 'input[id^=entry-translations]', function(e) {
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
