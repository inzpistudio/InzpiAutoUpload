<?php 

	require_once 'database.php';

	$file = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM drive WHERE driveload = 1 AND openload_log = 1 AND streamango_log = 0 ORDER BY date LIMIT 1"));

	if ($file['uniqfile']) {

		$site = $file['site'];
		$path = $file['loadpath'];
		$uniqfile = $file['uniqfile'];

		mysqli_query($con, "UPDATE drive SET streamango_log = 9 WHERE uniqfile = '$uniqfile'");

		$url_upload = file_get_contents('https://api.fruithosted.net/file/ul?login=5uEkww5Frz&key=JUPinHLQ&folder=388261');

		$url = json_decode($url_upload);
		$url = $url->result->url;

		$file = $path;

		$curl = "curl -F file1=@" . $file . " " . $url;

		exec($curl, $output, $return);

		if ($return != 0) {
		    echo "Error";
		}else{
		    $json = json_decode($output[0]);
		    $streamango_url = $json->result->url;
		    $con = mysqli_connect('localhost', 'root', '1529900845866', 'drive');
		    $str = "UPDATE drive SET streamango_log = 1, streamango = '$streamango_url' WHERE uniqfile = '$uniqfile'";
		    $q = mysqli_query($con, $str);
		    if (!$q) {
		    	printf("error: %s\n", mysqli_error($con));
		    	echo $str;
		    }else{
		    	echo "Upload File : ".$file."<br>";
		    	echo "URL : ".$json->result->url;
		    	if ($site == '2') {
		    		file_get_contents('https://misa-anime.com/apis/api-video-insert.php?iden='.$uniqfile.'&backupurl='.$streamango_url);
		    	}elseif ($site == '3') {
		    		file_get_contents('https://series-thai.com/apis/api-video-insert.php?iden='.$uniqfile.'&backupurl='.$openload_url);
		    	}
		    }
		    unlink($file);
		}

	}else{
		$con = mysqli_connect('localhost', 'root', '1529900845866', 'drive');
		mysqli_query($con, "UPDATE drive SET streamango_log = 0 WHERE uniqfile = '$uniqfile'");
		echo "Not Found! File to upload.";
	}