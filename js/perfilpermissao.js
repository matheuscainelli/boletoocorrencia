$(document).ready(function () {
    $('input[type="checkbox"]').on('click', function() {
        let checked = $(this).is(':checked');
        if ($(this).attr('data-marca') == 'FLPERMISSAO') {
            $(this).parents('tr').find('input[type="checkbox"]').prop('checked', checked);
        } else {
            $('[id^="'+$(this).attr('data-marca')+'"]').prop('checked', checked);
        }
    });
})