<?php 
	
	require_once 'database.php';

	$file = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM drive WHERE type = 'rapid' AND driveload = 0 ORDER BY date LIMIT 1"));

	if ($file['uniqfile']) {

		$id = getVideoID($file['url']);

		$uniqfile = $file['uniqfile'];

		mysqli_query($con, "UPDATE drive SET driveload = 9 WHERE uniqfile = '$uniqfile'");
		
		$url = 'https://www.rapidvideo.com/d/'.$id;
		$source = curl($url);

		preg_match_all('/https:\/\/([a-z0-9]{7})\.playercdn\.net\/p-dl\/([a-zA-Z0-9\/\-\_\.]+)/', $source, $rst);

		$c = sizeof($rst[0]);

		if ($c > 2) {
			$url = $rst[0][2];
		}elseif ($c == 2) {
			$url = $rst[0][1];		
		}elseif ($c == 1) {
			$url = $rst[0][0];		
		}else{
			$time = time();
			$con = mysqli_connect('localhost', 'root', '1529900845866', 'drive');
			mysqli_query($con, "UPDATE drive SET driveload = 0, date = '$time' WHERE uniqfile = '$uniqfile'");
			exit;
		}

		$fo = fopen($url, 'r');
		$time = time();
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
		echo "Not Found! File to download.";
	}

	function curl($url){
		$ch = @curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		$head[] = "Connection: keep-alive";
		$head[] = "Keep-Alive: 300";
		$head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$head[] = "Accept-Language: en-us,en;q=0.5";
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36');
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Expect:'
		));
		$page = curl_exec($ch);
		curl_close($ch);
		return $page;
	}

	function readString($string, $findStart, $findEnd){
		$start = stripos($string, $findStart);
		if($start === false) return false;
		$length = strlen($findStart);
		$end = stripos(substr($string, $start+$length), $findEnd);
		if($end !== false) {
			$rs = substr($string, $start+$length, $end);
		} else {
			$rs = substr($string, $start+$length);
		}
		if($rs){
			$rs = trim($rs);
			return $rs;
		}else{
			return false;
		}
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

	function getVideoID($url){
        if(preg_match("/v\/([0-9a-zA-Z-_]+)/", $url)){
            preg_match("/v\/([0-9a-zA-Z-_]+)/", $url, $mach);
            $docid = $mach[1];
        }
        else if(preg_match("/d\/([0-9a-zA-Z-_]+)/", $url)){
            preg_match("/d\/([0-9a-zA-Z-_]+)/", $url, $mach);
            $docid = $mach[1];
        }
        else if(preg_match("/e\/([0-9a-zA-Z-_]+)/", $url)){
                preg_match("/e\/([0-9a-zA-Z-_]+)/", $url, $mach);
                $docid = $mach[1];
        }
        else {
            $docid = $url;
        }
        return $docid;
    }