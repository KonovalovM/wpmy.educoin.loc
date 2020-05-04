
function edcEnableForm() {

    $( '.mce-btn-edit' ).remove()
    
    $( 'button[type=submit]' )
        .removeAttr( 'disabled' )
        .removeClass( 'btn-secondary' )
        .addClass( 'btn-primary' )
        .text( 'Выслать на проверку' )
}