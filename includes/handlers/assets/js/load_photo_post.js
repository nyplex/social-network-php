
$(function() {
    $('.upload-image-file').on('change', function(){
      if (isImage($(this).val())){
        $('.image-preview').attr('src', URL.createObjectURL(this.files[0]));
        $('.image-prev').show();
        $('.upload-video-file').val(null);
        $('.video-prev').hide();
      }
      else
      {
        $('.upload-image-file').val('');
        $('.image-prev').hide();
        alert("Only image files are allowed to upload.")
      }
    });
});
// If user tries to upload videos other than these extension , it will throw error.
function isImage(filename) {
    var ext = getExtension1(filename);
    switch (ext.toLowerCase()) {
    case 'jpeg':
    case 'jpg':
    case 'JPG':
    case 'Pjpeg':
    case 'png':

        // etc
        return true;
    }
    return false;
}
function getExtension1(filename) {
    var parts = filename.split('.');
    return parts[parts.length - 1];
}
