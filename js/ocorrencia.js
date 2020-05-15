$('[name="ANEXOOCORRENCIA[]"]').on('change', function() {
    arrFiles = $(this).prop('files');
    arrDesconsiderar = [];
    str = '';

    $.each(arrFiles, function(seq, arr) {
        if (arr['size'] > 5000000) {
            arrDesconsiderar.push(arr['name']);
        } else {
            arrAnexo.push(arr);

            str += "<li>";
                str += "<p>"+arr['name']+"</p>";
                str += "<i class='fas fa-times' onclick='RemoveAnexo($(this).parent())'></i>";
            str += "</li>";
        }
    });


    $(this).siblings('.dropdown-menu').append(str);
    if (arrAnexo.length > 0) {
        $(this).siblings('#btnDetalheAnexoOcorrencia').show('fast');
        $('#btnAnexoOcorrencia').css('border-radius', '');
    }

    // if (arrDesconsiderar.length > 0) {
    //     swal({
    //         title: 'Atenção',
    //         text: '<b>Os seguintes arquivos foram desconsiderados pois excedem o limite (5MB):<br>'+arrDesconsiderar.join('<br>')+'</b>',
    //         type: 'warning',
    //         html: true
    //     });
    // }
});

function RemoveAnexo(elmt) {
    swal({
        title: 'Atenção',
        text: 'Deseja excluir este anexo?',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    },
    function(isConfirm) {
        if (isConfirm) {
            index = arrAnexo.findIndex(x => x.name == $(elmt).find('p').text());
            arrAnexo.splice(index, 1);

            if (arrAnexo.length == 0) {
                $('#btnAnexoSolicitacao').css('border-radius', '3px');
                $(elmt).parent().siblings('#btnDetalheAnexoOcorrencia').hide('fast');
            }

            $(elmt).remove();
        }
    });
}