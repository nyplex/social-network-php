<?php

include("includes/header.php");

?>
<html lang="en">
<head>
  <title>PHP - jquery ajax crop image before upload using croppie plugins</title>
  <script src="http://demo.itsolutionstuff.com/plugin/jquery.js"></script>
  <script src="http://demo.itsolutionstuff.com/plugin/croppie.js"></script>
  <link rel="stylesheet" href="http://demo.itsolutionstuff.com/plugin/bootstrap-3.min.css">
  <link rel="stylesheet" href="http://demo.itsolutionstuff.com/plugin/croppie.css">
</head>
<body>

<div class="container">
	<div class="panel panel-default">
	  <div class="panel-heading">Image Upluad</div>
	  <div class="panel-body">
	  	<div class="row">
	  		<div class="col-md-4 text-center">
				<div id="upload-demo" style="width:350px"></div>
	  		</div>
	  		<div class="col-md-4" style="padding-top:30px;" id="col-md-5">
				<strong>Select Image:</strong>
				<br/>
				<input type="file" id="upload">
				<br/>
				<button class="btn btn-success upload-result">Upload Image</button>
	  		</div>
	  	</div>
	  </div>
	</div>
</div>



<script type="text/javascript">
$uploadCrop = $('#upload-demo').croppie({
    enableExif: true,
    viewport: {
        width: 200,
        height: 200,
        type: 'square'
    },
    boundary: {
        width: 250,
        height: 250
    }
});
$('#upload').on('change', function () {
	var reader = new FileReader();
    reader.onload = function (e) {
    	$uploadCrop.croppie('bind', {
    		url: e.target.result
    	}).then(function(){
    	});
    }
    reader.readAsDataURL(this.files[0]);
});
$('.upload-result').on('click', function (ev) {
	$uploadCrop.croppie('result', {
		type: 'canvas',
		size: 'viewport'
	}).then(function (resp) {
		$.ajax({
			url: "ajaxpro.php",
			type: "POST",
			data: {"image":resp},
			success: function (data) {
				html = '<img src="' + resp + '" />';
				$("#upload-demo-i").html(html);
        window.location.href = "<?php echo $userLoggedIn; ?>";
			}
		});
	});
});
</script>


</body>
</html>
