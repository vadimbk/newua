function getURLVar(key) {
  var value = [];

  var query = String(document.location).split('?');

  if (query[1]) {
    var part = query[1].split('&');

    for (i = 0; i < part.length; i++) {
      var data = part[i].split('=');

      if (data[0] && data[1]) {
        value[data[0]] = data[1];
      }
    }

    if (value[key]) {
      return value[key];
    } else {
      return '';
    }
  }
}
      
$(document).ready(function() {
    // Override summernotes image manager
    $('[data-toggle=\'summernote\'], .summernote').each(function() {
        var element = this;

        // CKEditor */
        if (typeof window.ckeditorStatus != 'undefined' && window.ckeditorStatus === 'on') {

          var elementId = $(element).attr('id');
          
          if (typeof elementId == 'undefined' || elementId == '') {
            $(element).attr('id', 'ckeditor-textarea-' + Math.random(1,100));

            elementId = $(element).attr('id');
          }
          ckeditorInit(elementId);
        } else {
      

            if ($(this).attr('data-lang')) {
                $('head').append('<script type="text/javascript" src="view/javascript/summernote/lang/summernote-' + $(this).attr('data-lang') + '.js"></script>');
            }

            $(element).summernote({
                lang: $(this).attr('data-lang'),
                disableDragAndDrop: true,
                height: 300,
                emptyPara: '',
                codemirror: { // codemirror options
                    mode: 'text/html',
                    htmlMode: true,
                    lineNumbers: true,
                    theme: 'monokai'
                },          
                fontsize: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '30', '36', '48' , '64'],
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'image', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                popover: {
                    image: [
                        ['custom', ['imageAttributes']],
                        ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                        ['float', ['floatLeft', 'floatRight', 'floatNone']],
                        ['remove', ['removeMedia']]
                    ],
                },          
                buttons: {
                    image: function() {
                        var ui = $.summernote.ui;
                                
                        // create button
                        var button = ui.button({
                            contents: '<i class="note-icon-picture" />',
                            tooltip: $.summernote.lang[$.summernote.options.lang].image.image,
                            click: function () {
                                $('#modal-image').remove();
                                
                                if (window.ckeditorToken == 'user_token') {
                                    var action = 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token')
                                } else {
                                    var action = 'index.php?route=common/filemanager&token=' + getURLVar('token')
                                }

                                $.ajax({
                                    url: action,
                                    dataType: 'html',
                                    beforeSend: function() {
                                        $('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
                                        $('#button-image').prop('disabled', true);
                                    },
                                    complete: function() {
                                        $('#button-image i').replaceWith('<i class="fa fa-upload"></i>');
                                        $('#button-image').prop('disabled', false);
                                    },
                                    success: function(html) {
                                        $('body').append('<div id="modal-image" class="modal">' + html + '</div>');
                                        
                                        $('#modal-image').modal('show');
                                        
                                        $('#modal-image').delegate('a.thumbnail', 'click', function(e) {
                                            e.preventDefault();
                                            
                                            $(element).summernote('insertImage', $(this).attr('href'));
                                                                        
                                            $('#modal-image').modal('hide');
                                        });
                                    }
                                });                     
                            }
                        });
                    
                        return button.render();
                    }
                }
            });

        }
    });
});