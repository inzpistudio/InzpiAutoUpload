<?php 
	require_once 'database.php';
	mysqli_set_charset($con,"utf8");
	
	$fid = 0;
	$fid = $_GET['fid'];
	$folder = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM folder WHERE fid = '$fid'"));
	$list = mysqli_query($con, "SELECT * FROM drive WHERE fid = '$fid'");

	function downloadString($n){
		switch ($n) {
			case '0':
				return '<span class="badge badge-warning"><i class="fa fa-hourglass-half"></i> Waiting...</span>';
				break;
			case '1':
				return '<span class="badge badge-success"><i class="fa fa-check-circle"></i> Complete</span>';
				break;
			default:
				return '<span class="badge badge-info"><i class="fa fa-spinner fa-spin"></i> Downloading...</span> <small>(rapidvideo download is slow)</small>';
				break;
		}
	}

	function uploadString($n, $url=null){
		switch ($n) {
			case '0':
				return '<span class="badge badge-warning"><i class="fa fa-hourglass-half"></i> Waiting...</span>';
				break;
			case '1':
				return '<span class="badge badge-success"><i class="fa fa-check-circle"></i> Complete</span> <input class="form-control form-control-sm" type="text" readonly value="'.$url.'">';
				break;
			default:
				return '<span class="badge badge-info"><i class="fa fa-spinner fa-spin"></i> Uploading...</span>';
				break;
		}
	}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=($folder['title']!=NULL?$folder['title']:'Untitle');?> | Drive and Rapidvideo Mirror Upload</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" />
    <style type="text/css">
    	/* Sticky footer styles
		-------------------------------------------------- */
		html {
		  position: relative;
		  min-height: 100%;
		}
		body {
		  margin-bottom: 60px; /* Margin bottom by footer height */
		}
		.footer {
		  position: absolute;
		  bottom: 0;
		  width: 100%;
		  height: 60px; /* Set the fixed height of the footer here */
		  line-height: 60px; /* Vertically center the text there */
		  background-color: #f5f5f5;
		}
		#warpper { margin-top: 100px; }
    </style>
</head>
<body>
	<?php include_once 'navbar.php'; ?>
	<div id="warpper" class="container">
		<h3 class="mt-5"><?=($folder['title']!=NULL?$folder['title']:'Untitle');?></h3>
      	<hr>
		<div class="row">
			<?php 
			$ope = 0;
			$stm = 0;
			while ($file = mysqli_fetch_assoc($list)) {
				$ope = $ope+$file['openload_log'];
				$stm = $stm+$file['streamango_log'];
			?>
			<div class="col-lg-12">
				<div class="row">
					<div class="col-lg-12"><h5><a target="_blank" href="<?=$file['url'];?>"><?=$file['title'];?></a></h5></div>
					<div class="col-lg-2">Download Status : </div><div class="col-lg-10"><?=downloadString($file['driveload']);?></div>
					<div class="col-lg-2">Openload Status : </div><div class="col-lg-10"><?=uploadString($file['openload_log'], $file['openload']);?></div>
					<div class="col-lg-2">Streamango Status : </div><div class="col-lg-10"><?=uploadString($file['streamango_log'], $file['streamango']);?></div>
				</div>
				<hr>
			</div>
			<?php } ?>
		</div>

	<?php if ($ope/$stm != 1) { ?>
	<small>this page auto refresh every 10 second</small>
	<script type="text/javascript">
		setInterval(function(){
			location.reload();
		},10000)
	</script>
	<?php } ?>
	</div>

	<?php include_once 'footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/js/bootstrap.min.js"></script>
</body>
</html>