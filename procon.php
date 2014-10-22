<?php
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

//$ans_csv = array();


if ($_GET["probID"]){
  $image_ID = $_GET["probID"];
  $path = mysql_fetch_array(mysql_query(sprintf('SELECT image_path FROM problems WHERE problem_id = %s;',$image_ID)));
  readfile($path[0]);
  // MySQLに対する処理
  $close_flag = mysql_close($link);
}

//print_r($_POST);
if ($_POST["answer_string"]){
  $now_time = time();  // 1970/1/1 00:00:00からの経過秒数
  $answer = $_POST["answer_string"];
  $time = $_POST["time"];
  //echo $time;
  $version = $_POST["version"];
  $probID = $_POST["probID"];
  list($ans_csv,$x_len,$y_len,$sel_rate,$cha_rate) = csv_read($probID);
  $make_array = make_array($x_len,$y_len);
  list($num_sel,$num_cha,$mis) = move($answer, $make_array,$ans_csv,$x_len,$y_len,$sel_rate,$cha_rate);
  $time = (int)$time * 100;
  $total = (int)$num_sel + (int)$num_cha + $time;
  echo "selection=".$num_sel;
  echo "change=".$num_cha;
  echo "time=".$time;
  echo "total=".$total;
  echo "missmatch=".$mis;
  $reslut = mysql_query(sprintf('INSERT INTO score(problem_id,version,mismatch_number,time_cost,exchange_cost,selection_cost,total_cost, time) VALUES (%s,"%s",%s,%d,%s,%s,%d, FROM_UNIXTIME(%d))',$probID,$version,$mis,$time,$num_cha,$num_sel,$total, $now_time));

  $recode_num = mysql_insert_id();
  $ansreslut=mysql_query(sprintf('INSERT INTO answers(score_id,answer_string) VALUES (%d,"%s");' ,$recode_num,$answer));
    
  if(!$ansreslut){
    die('anserror');
  }else{
    echo "OK";
  }

  if(!$reslut){
    die('error');
  }else{
    echo "OK";
  }
}

function csv_read($ID){
  $x = 0;
  $y = 0;
  $ans_csv = array();
  $num = 0;
  $file = mysql_fetch_array(mysql_query(sprintf('SELECT csv_path FROM problems WHERE problem_id = %s;',$ID)));
  if($file[0] == FALSE){
    die('error');
  }
  $fp = fopen($file[0],"r");
  if($fp == FALSE){
    die('error');
  }
  $date = fgetcsv($fp,",");
  $x_len = $date[0];
  $y_len = $date[1];
  $sel_rate = $date[2];
  $cha_rate = $date[3];
  while(($date = fgetcsv($fp,",")) !== FALSE){
    for($y = 0;$y < count($date);$y++ ){
      $ans_csv[$y][$x] = $date[$y];
    }
    $x++;
  }
  /*
  for($i = 0;$i < count($ans_csv);$i++){
    print_r($ans_csv[$i]);
  }
  */
  return array($ans_csv,$x_len,$y_len,$sel_rate,$cha_rate);
}
function make_array($x_len,$y_len){
  $num_array = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
  $ploblem = array(); 
  for($i = 0;$i < $x_len;$i++){
    for($f = 0;$f < $y_len;$f++){
      $ploblem[$i][$f] = $num_array[$i].''.$num_array[$f];
    }
  }
  return $ploblem;
}
function move($answer,$ploblem,$ans_csv,$x_len,$y_len,$sel_cost,$cha_cost){
  $num_cha = 0;
  $nam_sel;
  $num = 0;
  $mis = 0;
  $str = '';
  $contents = explode("\n",$answer);
  $cnt = count( $contents );
  for( $i = 0;$i < $cnt ;$i++ ){
    for($f = 0;$f < strlen($contents[$i]);$f++){
      if($num == 0){
	$str = $str.''.$contents[$i][$f];
      }
      elseif($num == 1){
	$str = $contents[$i][$f];
	if($f == 0){
	  $y = (int)$str;
	}
	elseif($f == 1){
	  $x = (int)$str;
	}
      }
      elseif($num == 2){
	$str = $str.''.$contents[$i][$f];
      }
      elseif($num == 3){
	$str = $contents[$i][$f];
	//echo $str;
	if($str === 'U'){
	  $tmp = $ploblem[$y][$x];
	  //echo 'tmp = '.$tmp;
	  $ploblem[$y][$x] = $ploblem[$y][$x-1];
	  //echo 'ans_csv[y][x] = '.$ploblem[$y][$x];
	  $ploblem[$y][$x-1] = $tmp;
	  //echo 'ans_csv[y][x-1] = '.$ploblem[$y][$x-1];
	  $x -= 1;
	}
	elseif($str === 'D'){
	  $tmp =$ploblem[$y][$x];
	  $ploblem[$y][$x] = $ploblem[$y][$x+1];
	  $ploblem[$y][$x+1] = $tmp;
	  $x += 1;
	}
	elseif($str === 'R'){
	  $tmp =$ploblem[$y][$x];
	  $ploblem[$y][$x] = $ploblem[$y+1][$x];
	  $ploblem[$y+1][$x] = $tmp;
	  $y += 1;
	}
	elseif($str === 'L'){
	  $tmp =$ploblem[$y][$x];
	  $ploblem[$y][$x] = $ploblem[$y-1][$x];
      	  $ploblem[$y-1][$x] = $tmp;
	  $y -= 1;
	}
      }
    }
    
    if($num == 0){
      $num_sel = (int) $str;
      $num_sel *= $sel_cost;
      $num +=1;
    }
    elseif($num == 1){
      $num += 1;
    }
    elseif($num == 2){
      $token = (int)$str;
      $token *= $cha_cost;
      $num_cha += $token;
      $num += 1;
    }
    elseif($num == 3){
      $num = 1;
    }
    $str = '';
  }
  
  for($i = 0;$i < $x_len;$i++){
    for($f = 0; $f < $y_len; $f++){
      if($ans_csv[$i][$f] === $ploblem[$i][$f]){
      }
      else{
	$mis += 1;
      }
    }
  }
  /*
  for($i = 0;$i < count($ans_csv);$i++){
    print_r($ploblem[$i]);
  }
  */
  return array($num_sel,$num_cha,$mis);
}
?>
