<?php
require_once 'database.php';
mysqli_set_charset($con,"utf8");

function getSource($url){
        if (strrpos($url, 'drive.google.com')) {
            return 'drive';
        }elseif (strrpos($url, 'rapidvideo')) {
            return 'rapid';
        }
    }
function generateRandomString($length = 10){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
}

    function checkfile($driveid){

        $d = file_get_contents('https://www.googleapis.com/drive/v3/files/'.$driveid.'?fields=capabilities,name&key=AIzaSyAqezFD_QLEQqfuMT7WACZnGdG_glJsPp8');
        $json = json_decode($d, true);

        if ($json['capabilities']['canDownload'] and !empty($json['name'])) {
            return true;
        }else{
            return false;
        }

    }

function getFolder($link)
{
    $result   = array();
    $folderID = getDriveFolderID($link);
    if ($folderID) {
        $result = getFolderInfo($folderID);
        if (!isset($result[0]['items'])) {
            $page   = curl($link);
            $result = array(
                0 => array(
                    'id' => $folderID,
                    'title' => trim(readString($page, '<title>', '- Google Drive</title>')),
                    'url' => 'https://drive.google.com/drive/folders/' . $folderID,
                    'items' => $result
                )
            );
        }
    }
    return $result;
}

function getFolderInfo($folderID)
{
    $result               = array();
    $resultLink['other']  = array();
    $resultLink['folder'] = array();
    $resultLink['video']  = array();
    $apiURL               = 'https://www.googleapis.com/drive/v2/files?q=%27' . $folderID . '%27%20in%20parents&maxResults=9999&orderBy=title&key=AIzaSyDtV2YN9J2TYCIvO688nsToWj7LtJvqLyo';
    $page                 = curl($apiURL);
    $data                 = json_decode($page);
    if ($data) {
        if (isset($data->items) && $data->items) {
            foreach ($data->items as $key => $value) {
                $var['id']    = $value->id;
                $value->title = str_replace('.mp4', '', $value->title);
                $var['title'] = $value->title;
                if ($value->mimeType == 'application/vnd.google-apps.folder') {
                    $var['url'] = 'https://drive.google.com/drive/folders/' . $value->id;
                    array_push($resultLink['folder'], $var);
                }
                // elseif($value->mimeType == 'video/mp4')
                // {
                //  $var['url'] = 'https://drive.google.com/file/d/'.$value->id.'/view';
                //  array_push($resultLink['video'], $var);
                // }
                else {
                    $var['url'] = 'https://drive.google.com/file/d/' . $value->id . '/view';
                    array_push($resultLink['video'], $var);
                }
            }
        }
    }
    if ($resultLink['video']) {
        $result = $resultLink['video'];
    }
    if ($resultLink['folder'] && !$resultLink['other'] && !$resultLink['video']) {
        $resultFolder = array();
        foreach ($resultLink['folder'] as $key => $value) {
            $var['id']    = $value['id'];
            $var['title'] = $value['title'];
            $var['url']   = $value['url'];
            $var['items'] = $this->getFolderInfo($value['id']);
            array_push($resultFolder, $var);
        }
        $result = $resultFolder;
    }
    return $result;
}

function getDriveFolderID($link)
{
    return readString($link, 'folders/', '');
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

function getDriveID($link)
{
    preg_match('/d\/(.*)\//U', $link, $id);
    if (empty($id[1])) {
        $id = explode('d/', $link);
    }
    if (empty($id[1])) {
        $id = explode('open?id=', $link);
    }
    if (empty($id[1])) {
        $id[1] = $link;
    }
    if (isset($id[1])) {
        $this->file = 'https://docs.google.com/feeds/get_video_info?formats=ios&mobile=true&docid=' . $id[1];
        return $id[1];
    } else {
        return false;
    }
}

function readString($string, $findStart, $findEnd)
{
    $start = stripos($string, $findStart);
    if ($start === false)
        return false;
    $length = strlen($findStart);
    $end    = stripos(substr($string, $start + $length), $findEnd);
    if ($end !== false) {
        $rs = substr($string, $start + $length, $end);
    } else {
        $rs = substr($string, $start + $length);
    }
    return $rs ? $rs : false;
}

function curl($url)
{
    $ch = @curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    $head[] = "Connection: keep-alive";
    $head[] = "Keep-Alive: 300";
    $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $head[] = "Accept-Language: en-us,en;q=0.5";
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    $page = curl_exec($ch);
    curl_close($ch);
    return $page;
}

if (isset($_POST['id']) && $_POST['id']) {
    $url = 'https://drive.google.com/drive/folders/'.$_POST['id'];
    $data = getFolder($url); //echo "<pre>"; print_r($data);

    $title = $data[0]['title'];
    $date = time();
    @mysqli_query($con, "INSERT INTO folder(title,date) VALUES ('$title', '$date')");
    $fid = mysqli_insert_id($con);

    $count = sizeof($data[0]['items']);

    if ($count > 3) {
        exit('Sorry, Can test up to 3 files.');
    }

    
    foreach ($data[0]['items'] as $items) {
        $uniqfile = generateRandomString();
        $title = $items['title'];
        $url = $items['url'];
        $time = time();
        $type = getSource($url);
        $ip = get_client_ip();
        $str = "INSERT INTO drive(uniqfile,fid,title,url,openload,streamango,date,type,ip) VALUES ('$uniqfile', '$fid', '$title', '$url', '', '', '$time', '$type', '$ip')";
        mysqli_query($con, $str);
    }
    header('location: https://lh3.inzpi.com/folder-view.php?fid='.$fid);
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
        <h1 class="mt-5">Folder Drive Mirror Upload</h1>
        <p class="lead">Auto Upload to Openload and Streamango <small class="text-danger">[Warning! File need allow to <b>download!</b>]</small></p>
        
        <form action="?" method="post" accept-charset="utf-8">
            <div class="form-group">
                <label>Folder ID</label>
                <input type="text" class="form-control" name="id" placeholder="1HnH9b9WXSvOH-BBQ45Mk3hEjIxfNIAIe" required>
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