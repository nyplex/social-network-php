
$(function() {
    $('.upload-video-file2').on('change', function(){
      if (isVideo($(this).val())){
        $('.video-preview2').attr('src', URL.createObjectURL(this.files[0]));
        $('.video-prev2').show();
        $('.upload-image-file2').val(null);
        $('.image-prev2').hide();
      }
      else
      {
        $('.upload-video-file2').val('');
        $('.video-prev2').hide();
        alert("Only video files are allowed to upload.")
      }
    });
});
// If user tries to upload videos other than these extension , it will throw error.
function isVideo(filename) {
    var ext = getExtension(filename);
    switch (ext.toLowerCase()) {
    case 'm4v':
    case 'avi':
    case 'mp4':
    case 'mov':
    case 'mpg':
    case 'mpeg':
        // etc
        return true;
    }
    return false;
}
function getExtension(filename) {
    var parts = filename.split('.');
    return parts[parts.length - 1];
}
