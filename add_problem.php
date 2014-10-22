<html>
<head>
<meta http-equiv="charset=utf-8" />
<LINK rel="stylesheet" type="text/css" href="resources/add_problem.css">
</head>
<title>アップローダ</title>
<body>
<h1>追加してね!</h1>
<form action="add_problem.php" method="POST" enctype="multipart/form-data" id="my_form" align="center">
<br>
<br>
<br>
<A Href="index.php">ホームページに戻る</A>
<br>
<br>
<br> 
<p align="center">CSVを追加
<input type="file" name="CSV" size="30" accept="text/comma-separated-values"/>
PPMを追加
<input type="file" name="PPM" size="30" accept="image/x-portable-pixmap"/></p>
<br>
<br>
<br>
<input type="submit" value="アップロード" name = "upload"/>
</form>


<?php

// 画像を分割する
function splitImages($ppmname, $csvname, $id) {
  if (!file_exists($ppmname) || !file_exists($csvname)) {
    printf("not exist %s\n", $ppmname);
    return;
  }
  $fp = fopen($csvname, "r");
  $array = fgetcsv($fp);
  fclose($fp);
  $columns = $array[0];
  $rows = $array[1];
  
  $img = new Imagick($ppmname);
  $width = $img->getImageWidth(); 
  $height = $img->getImageHeight(); 
  printf("%dx%d %dx%d\n", $width, $height, $columns, $rows);

  $blk_w = $width / $columns;
  $blk_h = $height / $rows;

  mkdir(sprintf("imgs/%d", $id));
  for ($i = 0; $i < $columns; $i++) {
    for ($j = 0; $j < $rows; $j++) {
      $im = $img->getImage();
      $im->cropImage($blk_w, $blk_h, $blk_w * $i, $blk_h * $j);
      $im->writeImage(sprintf("imgs/%d/%X%X.png", $id, $i, $j));
    }
  }
}

// 横分割数,縦分割数,選択コストレート,交換コストレート,最大選択回数
function get_prob_info($filename) {
  $fp = fopen($filename, "r");
  if (!$fp) {
    return FALSE;
  } 
  $array = fscanf($fp, "%d,%d,%d,%d,%d");
  fclose($fp);
  return $array;
}
require 'info.php';
global $hostname,$user,$pass,$db_name;
$link = mysql_connect($hostname,$user,$pass);
if (!$link) {
  die('error');
}

$db_selected = mysql_select_db($db_name, $link);
if(!$db_selected){
  die('error');
}


$name_of_ppm;
$name_of_csv;
if (isset($_POST["upload"])){
  if(!(isset($_FILES["CSV"]["name"]) && isset($_FILES["PPM"]["name"]))) {
    echo "ファイルが足りない";
  }else{

 if(is_uploaded_file($_FILES["CSV"]["tmp_name"])){
  if(move_uploaded_file($_FILES["CSV"]["tmp_name"],"problems/csv/".$_FILES["CSV"]["name"])){
     chmod("problems/csv/".$_FILES["CSV"]["name"],0644);
     $name_of_csv = $_FILES["CSV"]["name"];
     echo $_FILES["CSV"]["name"]."をアップロードしました。<br>";
   } else {
     echo "ファイルをアップロードできません。";
   }
 } else {
   echo "ファイルが選択されていません。";
 }

 if(is_uploaded_file($_FILES["PPM"]["tmp_name"])){
   if(move_uploaded_file($_FILES["PPM"]["tmp_name"],"problems/ppm/".$_FILES["PPM"]["name"])){
     chmod("files/".$_FILES["PPM"]["name"],0644);
     # サムネイル作成
     $thumb_size = 160;
     $filename = $_FILES["PPM"]["name"];
     $im = new Imagick();
     $im->readImage("problems/ppm/".$filename);
     $geom = $im->getImageGeometry();
     $ratio = $thumb_size / max($geom['width'], $geom['height']); # 倍率 長辺を合わせる
     $thumb_width = min($geom['width'] * $ratio, $thumb_size);
     $thumb_height = min($geom['height'] * $ratio, $thumb_size);
     $im->resizeImage($thumb_width, $thumb_height, Imagick::FILTER_LANCZOS, 1);
     $name_of_thumb = basename($filename, ".ppm").".png";
     $im->writeImage("problems/thumb/".$name_of_thumb);
     
     $name_of_ppm = $_FILES["PPM"]["name"];
     echo $_FILES["PPM"]["name"]."をアップロードしました。<br>";
     $res = mysql_query(sprintf('INSERT INTO problems (image_path ,csv_path, thumb_path) VALUES ( "problems/ppm/%s" , "problems/csv/%s", "problems/thumb/%s");',mysql_real_escape_string($name_of_ppm),mysql_real_escape_string($name_of_csv),mysql_real_escape_string($name_of_thumb)));
     $res = mysql_query(sprintf('SELECT problem_id FROM problems WHERE csv_path="problems/csv/%s"', mysql_real_escape_string($name_of_csv)));
     $id = 99;
     while ($row = mysql_fetch_assoc($res)) {
       $id = $row["problem_id"];
     }
     $info = get_prob_info("problems/csv/".$name_of_csv);
     $res = mysql_query(sprintf('INSERT INTO problem_info (problem_id, columns, rows, selection_rate, exchange_rate, max_selection) VALUES (%d, %d, %d, %d, %d, %d);', $id, $info[0], $info[1], $info[2], $info[3], $info[4]));
     echo mysql_error();
     splitImages($name_of_ppm, $name_of_csv, $id);
   } else {
     echo "ファイルをアップロードできません。";
   }
 }else {
   echo "ファイルが選択されていません。";
 }
  }
}
      
?>

</body>
</html>
