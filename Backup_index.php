<html>
  <head>
    <title>procon25 program test page </title>
<link rel="stylesheet" href="resources/index.css">
  </head>
  <body>    
    <A Href="add_problem.php">問題を追加</A></br>
    <?php
   include("./extension.php");
function show_problem_info($id = NULL) {
  //$res = mysql_query(sprintf("SELECT problem_id, image_path, thumb_path FROM problems"));
  $sql = <<<EOS
SELECT
  problems.problem_id,
  problems.image_path,
  problems.thumb_path,
  problem_info.columns,
  problem_info.rows,
  problem_info.selection_rate,
  problem_info.exchange_rate,
  problem_info.max_selection
FROM problems
INNER JOIN problem_info ON problems.problem_id = problem_info.problem_id
EOS;
  if ($id == NULL) {
    $res = mysql_query(sprintf($sql));
  } else {
    $res = mysql_query(sprintf($sql . " WHERE problems.problem_id = %d", $id));
  }
  if(!$res){
    die('error');
  }else{
    echo "<table>";
    echo "<caption>問題画像</caption>";
    echo "<tr>";
    echo "<th>問題ID</th>";
    echo "<th>画像名</th>";
    echo "<th>分割数</th>";
    echo "<th>選択コスト</th>";
    echo "<th>交換コスト</th>";
    echo "<th>最大選択回数</th>";
    echo "<th>サムネイル</th>";
   // echo "<th>削除</th>"
    echo "</tr>";
    while ($row = mysql_fetch_assoc($res)) {
      echo "<tr>";
      echo('<td>'.$row['problem_id'].'</td>');
      echo('<td><A Href="index.php?see_prob='.$row['problem_id'].'">'.$row['image_path'].'</A></td>');
      echo(sprintf('<td>%dx%d</td>', $row['columns'], $row['rows']));
      echo(sprintf('<td>%d</td>', $row['selection_rate']));
      echo(sprintf('<td>%d</td>', $row['exchange_rate']));
      echo(sprintf('<td>%d</td>', $row['max_selection']));
      echo('<td><img src="'.$row['thumb_path'].'"></img></td>');
      // echo('<td><input type="button"name="削除">value="削除"onClick="delete('.$row['problemid'].')"')
      echo "</tr>";
    }
  }
  echo "</table>";
}

function html_escape($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
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
if(isset($_GET["see_prob"])){
  $res = mysql_query(sprintf("SELECT *FROM score WHERE problem_id = %d ORDER BY mismatch_number ASC, total_cost ASC",intval($_GET["see_prob"])));
  if(!$res){
    die('error');
  }else{
    //$image_path_res = mysql_fetch_array(mysql_query(sprintf("SELECT thumb_path FROM problems WHERE problem_id = %s",$_GET["see_prob"])));
    //echo ('<img src="'.$image_path_res[0].'"></img>');
    show_problem_info(intval($_GET["see_prob"]));
    echo "<table>";
    echo "<caption>スコア表</caption>";
    echo "<tr>";
    echo "<th>スコアID</th>";
    echo "<th>問題ID</th>";
    echo "<th>プログラムバージョン</th>";
    echo "<th>不一致画像数</th>";
    echo "<th>時間コスト</th>";
    echo "<th>交換コスト</th>";
    echo "<th>選択コスト</th>";
    echo "<th>総コスト</th>";
    echo "<th>解答時刻</th>";
    echo "</tr>";
    while ($row = mysql_fetch_assoc($res)) {
      echo "<tr>";
      #echo('<td>'.$row['score_id'].'</td>');
      echo(sprintf('<td><a href="show_answer.php?score_id=%d">%d</a></td>', intval($row['score_id']), intval($row['score_id'])));
      echo('<td>'.$row['problem_id'].'</td>');
      echo('<td><A Href="index.php?see_version='.urlencode(html_escape($row['version'])).'">'.html_escape($row['version']).'</A></td>');
      echo('<td>'.$row['mismatch_number'].'</td>');
      echo('<td>'.$row['time_cost'].'</td>');
      echo('<td>'.$row['exchange_cost'].'</td>');
      echo('<td>'.$row['selection_cost'].'</td>');
      echo('<td>'.$row['total_cost'].'</td>');
      echo('<td>'.$row['time'].'</td>');
      echo "</tr>";
    }
    echo "</table>";
  }
}elseif($_GET["see_version"]){
  $res = mysql_query(sprintf("SELECT *FROM score WHERE version = '%s' ORDER BY score_id ASC",mysql_real_escape_string($_GET["see_version"])));
  if(!$res){
    die('error');
  }else{
    echo "<table>";
    echo "<caption>スコア表</caption>";
    echo "<tr>";
    echo "<th>スコアID</th>";
    echo "<th>問題ID</th>";
    echo "<th>プログラムバージョン</th>";
    echo "<th>不一致画像数</th>";
    echo "<th>時間コスト</th>";
    echo "<th>交換コスト</th>";
    echo "<th>選択コスト</th>";
    echo "<th>総コスト</th>";
    echo "<th>解答時刻</th>";
    echo "<th>画像</th>";
    echo "</tr>";
    while ($row = mysql_fetch_assoc($res)) {
      echo "<tr>";
      $thumb_path_res = mysql_fetch_array(mysql_query(sprintf("SELECT thumb_path FROM problems WHERE problem_id = %d",intval($row['problem_id']))));
      //echo('<td>'.$row['score_id'].'</td>');
      echo(sprintf('<td><a href="show_answer.php?score_id=%d">%d</a></td>', intval($row['score_id']), intval($row['score_id'])));
      echo('<td><A Href="index.php?see_prob='.$row['problem_id'].'">'.$row['problem_id'].'</A></td>');
      echo('<td>'.html_escape($row['version']).'</td>');
      echo('<td>'.$row['mismatch_number'].'</td>');
      echo('<td>'.$row['time_cost'].'</td>');
      echo('<td>'.$row['exchange_cost'].'</td>');
      echo('<td>'.$row['selection_cost'].'</td>');
      echo('<td>'.$row['total_cost'].'</td>');
      echo('<td>'.$row['time'].'</td>');
      echo('<td><img src="'.$thumb_path_res[0].'" height="50" wight="50"></img></td>');
      echo "</tr>";
    }
    echo "</table>";
  }
}else{
  show_problem_info();
}

?>
  </body>
</html>
