<?php 

	require_once 'database.php';
	mysqli_set_charset($con, "utf8");
	function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	function get_client_ip() {
	    $ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	       $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';

	    $ip = explode(',', $ipaddress);
	    return $ip[0];
	}

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

	function checkfile($driveid){

    	$d = file_get_contents('https://www.googleapis.com/drive/v3/files/'.$driveid.'?fields=capabilities,name,mimeType&key=AIzaSyAqezFD_QLEQqfuMT7WACZnGdG_glJsPp8');
    	$json = json_decode($d, true);

		if ($json['capabilities']['canDownload'] and !empty($json['name']) and $json['mimeType'] != 'application/vnd.google-apps.folder') {
			return true;
		}else{
			return false;
		}

    }

    function checkLimit($ip){
    	$con = mysqli_connect('localhost', 'root', '1529900845866', 'drive');
    	$count = mysqli_fetch_assoc(mysqli_query($con, "SELECT COUNT(uniqfile) as cu FROM drive WHERE ip = '$ip'"));
    	if ($count['cu'] < 3) {
    		return true;
    	}else{
    		return false;
    	}
    }

   	function getSource($url){
   		if (strrpos($url, 'drive.google.com')) {
   			return 'drive';
   		}elseif (strrpos($url, 'rapidvideo')) {
   			return 'rapid';
   		}
   	}

    function getVideoIDDrive($url){
        if(preg_match("/file\/d\/([0-9a-zA-Z-_]+)\//", $url)){
            preg_match("/file\/d\/([0-9a-zA-Z-_]+)\//", $url, $mach);
            $docid = $mach[1];
        }
        else if(preg_match("/file\/d\/([0-9a-zA-Z-_]+)/", $url)){
            preg_match("/file\/d\/([0-9a-zA-Z-_]+)/", $url, $mach);
            $docid = $mach[1];
        }
        else if(preg_match("/id=([0-9a-zA-Z-_]+)/", $url)){
                preg_match("/id=([0-9a-zA-Z-_]+)/", $url, $mach);
                $docid = $mach[1];
        } else {
            $docid = $url;
        }
        return $docid;
    }

	if (isset($_POST['title']) and isset($_POST['url'])) {

		$uniqfile = generateRandomString();
		$title = $_POST['title'];
		$url = $_POST['url'];
		$time = time();

		mysqli_set_charset($con,"utf8");

		$type = getSource($url);

		$drive = getVideoIDDrive($_POST['url']);

		$ip = get_client_ip();

		if (checkLimit($ip)) {

			if ($type == 'drive') {

				if (checkfile($drive)) {
				
					$str = "INSERT INTO drive(uniqfile,title,url,openload,streamango,date,type,ip) VALUES ('$uniqfile', '$title', '$url', '', '', '$time', '$type', '$ip')";
					$add = mysqli_query($con, $str);

					if ($add) {
						header('location: https://lh3.inzpi.com/?file='.$uniqfile);
					}else{
						exit("Query Error ".$str);
					}

				}else{
					exit("Sorry! File not support.");
				}

			}else{

				$str = "INSERT INTO drive(uniqfile,title,url,openload,streamango,date,type,ip) VALUES ('$uniqfile', '$title', '$url', '', '', '$time', '$type', '$ip')";
				$add = mysqli_query($con, $str);

				if ($add) {
					header('location: https://lh3.inzpi.com/?file='.$uniqfile);
				}else{
					exit("Query Error ".$str);
				}
			}

		}else{
			exit('Sorry, You can only test 3 files.');
		}

	}elseif (isset($_GET['file'])){

		$uniqfile = $_GET['file'];
		$file = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM drive WHERE uniqfile = '$uniqfile'"));
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$file['title'];?> | Drive and Rapidvideo Mirror Upload</title>
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
		<h3 class="mt-5"><?=$file['title'];?></h3>
      	<p class="lead">Original URL : <small class="text-primary"><a target="_blank" href="<?=$file['url'];?>"><?=$file['url'];?></a></small></p>
      	<hr>
		<div class="row">
			<div class="col-lg-2"><p>Download Status : </p></div><div class="col-lg-10"><?=downloadString($file['driveload']);?></div>
			<div class="col-lg-12"><hr></div>
			<div class="col-lg-2"><p>Openload Status : </p></div><div class="col-lg-10"><?=uploadString($file['openload_log'], $file['openload']);?></div>
			<div class="col-lg-12"><hr></div>
			<div class="col-lg-2"><p>Streamango Status : </p></div><div class="col-lg-10"><?=uploadString($file['streamango_log'], $file['streamango']);?></div>
		</div>
	<?php if ($file['driveload'] == 0 or $file['driveload'] == 9 or $file['openload_log'] == 0 or $file['openload_log'] == 9 or $file['streamango_log'] == 0 or $file['streamango_log'] == 9) { ?>
	<hr>
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
<?php
	}else{

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inzpi Studio | Drive and Rapidvideo Mirror Upload</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/css/bootstrap.min.css" />
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
		<h1 class="mt-5">Drive and Rapidvideo Mirror Upload</h1>
      	<p class="lead">Auto Upload to Openload and Streamango <small class="text-danger">[Warning! File need allow to <b>download!</b>]</small></p>
      	
		<form action="?" method="post" accept-charset="utf-8">
			<div class="form-group">
			    <label>File Name</label>
			    <input type="text" class="form-control" name="title" placeholder="File Name.." required>
			</div>
			<div class="form-group">
			    <label>Drive URL</label>
			    <input type="text" class="form-control" name="url" placeholder="https://drive.google.com/file/d/1AKCCm_ANQNSI3J4lBZMzZdCvwfYuYcI5/view" required>
			</div>
			<button type="submit" class="btn btn-primary">Upload</button>
		</form>
	</div>

	<?php include_once 'footer.php'; ?>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.0/js/bootstrap.min.js"></script>
</body>
</html>

<?php 
	}
