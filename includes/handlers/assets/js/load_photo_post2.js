
$(function() {
    $('.upload-image-file2').on('change', function(){
      if (isImage($(this).val())){
        $('.image-preview2').attr('src', URL.createObjectURL(this.files[0]));
        $('.image-prev2').show();
        $('.upload-video-file2').val(null);
        $('.video-prev2').hide();
      }
      else
      {
        $('.upload-image-file2').val('');
        $('.image-prev2').hide();
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
