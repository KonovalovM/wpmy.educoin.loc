
function edcGetOptionsTinyMce() {

    var options =  { 
            
        selector: '',
        readonly: 0,

        language: 'ru',
        plugins: [
            'autolink lists link image charmap print preview textcolor',
            'searchreplace visualblocks code fullscreen',
            'table paste code help autoresize hr table codesample code'
        ],
        menubar: false,
        toolbar: 'insert | undo redo | formatselect | bold italic backcolor  | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | edc_emojis myinsertfile table codesample code',

        default_link_target: "_blank",
        link_title: false,

        codesample_languages: [
            {text: 'HTML/XML', value: 'markup'},
            {text: 'JavaScript', value: 'javascript'},
            {text: 'CSS', value: 'css'},
            {text: 'PHP', value: 'php'}
        ],

        image_description: false,

        setup: function ( editor ) {
            editor.addButton( 'myinsertfile', {
                text: '',
                icon: 'newdocument',
                tooltip: 'Добавить файл',
                onclick: function () {
                    editor.notificationManager.open({
                      text: 'Для вставки файла загрузите его сначала в DropBox/Google Drive, а затем просто добавьте ссылку на него в редакторе.',
                      type: 'info'
                    });
                }
            }),
            editor.addButton( 'edc_emojis', {
                type: 'menubutton',
                text: '😊',
                icon: false,
                tooltip: 'Emoji',
                menu: [
                  {text: 'Палец вверх 👍', onclick: function() {editor.insertContent('👍');}}, 
                  {text: 'Подмигивание 😉', onclick: function() {editor.insertContent('😉');}},
                  {text: 'Улыбка 😊', onclick: function() {editor.insertContent('😊');}},
                  {text: 'Смех до слез 😂', onclick: function() {editor.insertContent('😂');}},
                  {text: 'Бум 💥', onclick: function() {editor.insertContent('💥');}},
                  {text: 'Круть 🤘', onclick: function() {editor.insertContent('🤘');}},
                  {text: 'Огонь 🔥', onclick: function() {editor.insertContent('🔥');}},
                  {text: 'Хлопушка с конфети 🎉', onclick: function() {editor.insertContent('🎉');}},
                  {text: 'Шар из конфети 🎊', onclick: function() {editor.insertContent('🎊');}},
                  {text: 'Танцор 🕺', onclick: function() {editor.insertContent('🕺');}},
                  {text: 'Хлопки в ладоши 👏', onclick: function() {editor.insertContent('👏');}},
                  {text: 'Бицепс 💪', onclick: function() {editor.insertContent('💪');}},
                  {text: 'Секундомер ⏱', onclick: function() {editor.insertContent('⏱');}},
                  {text: 'Звезда ⭐', onclick: function() {editor.insertContent('⭐');}},
                  {
                    text: 'Остальное', 
                    onclick: function() {
                      editor.notificationManager.open({
                        text: 'Остальное по <a href="https://www.emojicopy.com/" target="_blank">ссылке</a>.',
                        type: 'info'
                      });
                    }},
                ]
            });
        }
    };
    
    return options;
}

function edcCreateTinyMce( selector ) {
    
    var options = edcGetOptionsTinyMce();
    
    options['selector'] = selector;
    
    tinymce.init( options );
}

function edcCreateDisabledTinyMce( selector ) {

    var options = edcGetOptionsTinyMce();
    
    options['selector'] = selector;
    options['readonly'] = 1;
    
    tinymce.init( options );
}

function edcEnableTinyMce( selector ) {
    
    $( selector ).removeAttr( 'disabled' )

    var options = edcGetOptionsTinyMce();
    options['selector'] = selector;

    tinymce.remove( selector );
    tinymce.init( options );
}

( function ( $, root, undefined ) {
	
	$( function () {
		
		'use strict';
		
        $( document ).ready( function() {
            
            // create TinyMCE
            edcCreateTinyMce( 'textarea.advanced:not([disabled])' );
            // create disabled TinyMCE
            edcCreateDisabledTinyMce( 'textarea.advanced[disabled]' );
        } );
      
    } );

} )( jQuery, this );