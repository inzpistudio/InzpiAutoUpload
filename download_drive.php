<?php 

	require_once 'database.php';

	$file = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM drive WHERE type = 'drive' AND driveload = 0 ORDER BY date LIMIT 1"));

	if ($file['uniqfile']) {

		$drive = getVideoIDDrive($file['url']);

		$uniqfile = $file['uniqfile'];

		mysqli_query($con, "UPDATE drive SET driveload = 9 WHERE uniqfile = '$uniqfile'");
		$time = time();
		$url = 'https://www.googleapis.com/drive/v3/files/' . $drive . '?alt=media&key=AIzaSyAqezFD_QLEQqfuMT7WACZnGdG_glJsPp8';

		if (isVideo($drive)) {

			$fo = fopen($url, 'r');
			$filename = "temp_file/".slugify($file['title'])."-".$time.".mp4";
			if (file_put_contents($filename, $fo)) {
				$con = mysqli_connect('localhost', 'root', '1529900845866', 'drive');
				mysqli_query($con, "UPDATE drive SET driveload = 1, loadpath = '$filename' WHERE uniqfile = '$uniqfile'");
				echo "Download File : ".$filename."<br>";
			}else{
				$con = mysqli_connect('localhost', 'root', '1529900845866', 'drive');
				mysqli_query($con, "UPDATE drive SET driveload = 0 WHERE uniqfile = '$uniqfile'");
				echo "Error";
			}

		}else{

			$con = mysqli_connect('localhost', 'root', '1529900845866', 'drive');
			mysqli_query($con, "DELETE FROM drive WHERE uniqfile = '$uniqfile'");
			echo "File is not Video";

		}


	}else{
		echo "Not Found! File to download.";
	}

	function isVideo($driveid){

    	$d = file_get_contents('https://www.googleapis.com/drive/v3/files/'.$driveid.'?fields=mimeType&key=AIzaSyAqezFD_QLEQqfuMT7WACZnGdG_glJsPp8');
    	$json = json_decode($d, true);

		if (preg_match("/video\/([0-9a-zA-Z-_]+)/", $json['mimeType'])) {
			return true;
		}else{
			return false;
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

    function slugify($text){
	  // replace non letter or digits by -
	  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

	  // transliterate
	  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	  // remove unwanted characters
	  $text = preg_replace('~[^-\w]+~', '', $text);

	  // trim
	  $text = trim($text, '-');

	  // remove duplicate -
	  $text = preg_replace('~-+~', '-', $text);

	  // lowercase
	  $text = strtolower($text);

	  if (empty($text)) {
	    return 'n-a';
	  }

	  return $text;
	}