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
// CKEditor */
function ckeditorInit(node) {
  CKEDITOR.replace(node);
  CKEDITOR.on('dialogDefinition', function (ev) {
    for (i = 0; i < ev.data.definition.contents.length; i++) {
      var button = ev.data.definition.contents[i].get('browse');
      if (button !== null) {
        button.hidden = false;
        button.onClick = function() {
          $('#modal-image').remove();

          if (window.ckeditorToken == 'user_token') {
            var action = 'index.php?route=common/filemanager&cke=' + this.filebrowser.target+'&user_token=' + getURLVar('user_token')
          } else {
            var action = 'index.php?route=common/filemanager&cke=' + this.filebrowser.target+'&token=' + getURLVar('token')
          }

          $.ajax({
            url: action,
            dataType: 'html',
            success: function(html) {
              $('body').append('<div id="modal-image" class="modal ckeditor">' + html + '</div>');

              $('#modal-image').modal('show');
            }
          });
        }
      }
    }
  });
}
