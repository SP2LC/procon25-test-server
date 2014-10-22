<?php
require 'info.php';
extract($_GLOBALS);
function delete($problem_id){
  $link = mysql_connect($hostname,$user,$pass);
  if (!$link) {
    die('error');
  }

  $db_selected = mysql_select_db($db_name, $link);
  if(!$db_selected){
    die('error');
  }
  $sql = sprintf("SELECT image_path FROM problems WHERE problem_id = %s",$problem_id);
  $result = mysql_query($sql);
  $image_path = mysql_result($result,0);
  echo $image_path;
  $first_path = sprintf("%s",$image_path);
  $second_path = sprintf("escape/image_%s",$problem_id);
  echo ($first_path);
  echo ("   ");
  echo ($second_path);
  $res = rename($first_path, $second_path);
  if(!$res){
    echo ("error");
  }else{
    echo ("OK");
  }
  
  $sql = sprintf("SELECT csv_path FROM problems WHERE problem_id = %s",$problem_id);
  $result = mysql_query($sql);
  $csv_path = mysql_result($result,0);
  $first_path = sprintf("%s",$csv_path);
  $second_path = sprintf("escape/scv_%s",$problem_id);
  rename($first_path, $second_path);
  
  
  $sql = sprintf("SELECT thumb_path from problems where problem_id = %s",$problem_id);
  $result = mysql_query($sql);
  $thumb_path = mysql_result($result,0);
  $first_path = sprintf("%s",$thumb_path);
  $second_path = sprintf("escape/thumb_%s",$problem_id);
  rename($first_path, $second_path);


  $sql = sprintf("SELECT score_id FROM score WHERE problem_id = %s",$problem_id);
  $result = mysql_query($sql);
  if(mysql_num_rows($result)==0){
    echo ("なかった");
    problem_delete($problem_id);
  }else{
    while($score_id = mysql_fetch_array($result, MYSQL_NUM)){
      $sql = sprintf("DELETE from answers where score_id = %s",$score_id[0]); 
      mysql_query($sql);
    }
    echo ("あった");
    problem_delete($problem_id);
  }
  
  }
function problem_delete($problem_id){
  echo ("problem_delete");
  $link = mysql_connect($hostname,$user,$pass);
  if (!$link) {
    die('error');
  }

  $db_selected = mysql_select_db($db_name, $link);
  if(!$db_selected){
    die('error');
  }
  $sql = sprintf("DELETE FROM problem_info WHERE problem_id = %s",$problem_id);
  $res = mysql_query($sql);
  if($res) {
    echo ("ok");
  }else{
    echo ("NG");
  }
  $sql = sprintf("DELETE FROM score WHERE problem_id = %s",$problem_id);
  mysql_query($sql);
  $sql = sprintf("DELETE FROM problems WHERE problem_id = %s",$problem_id);
  mysql_query($sql);
}

function ans_delete($ans_id){
  $link = mysql_connect($hostname,$user,$pass);
  if (!$link) {
    die('error1');
  }

  $db_selected = mysql_select_db($db_name, $link); 
  if(!$db_selected){
    echo "error2";
    echo mysql_error($link);
    die();
  }
  
  $sql = sprintf("DELETE FROM answers WHERE score_id = %s",$ans_id); 
  $res = mysql_query($sql);
  if($res == true){
    $sql = sprintf("DELETE FROM score WHERE score_id = %s",$ans_id);
    $res = mysql_query($sql); 
    if($res == true){
      echo("ok");
    }
  }else{
    echo "error3";
    echo mysql_error($link);
  }
}
if (isset($_POST["problem_id"])){
  delete($_POST["problem_id"]);
}else if(isset($_POST["score_id"])){
  ans_delete($_POST["score_id"]);
}

?>
