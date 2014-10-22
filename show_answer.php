<html>
  <head>
<script src ="http://code.jquery.com/jquery-1.11.1.js"></script>
    <title>procon25 program test page </title>
<link rel="stylesheet" href="resources/index.css">
  </head>
  <body>    
    <A Href="add_problem.php">問題を追加</A></br>
    <?php
// MySQLに接続
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
if (!isset($_GET["score_id"])) {
  die('error');
}
$sql = <<<EOS
SELECT answer_string FROM answers WHERE score_id=%d
EOS;
$res = mysql_query(sprintf($sql, intval($_GET["score_id"])));
while ($row = mysql_fetch_assoc($res)) {
  echo "<pre>";
  //echo $row["answer_string"];
  echo "</pre>";;
  $answer_string = $row["answer_string"];
}

$sql_str = "SELECT problem_id FROM score WHERE score_id=%d";
$respo = mysql_query(sprintf($sql_str,$_GET["score_id"]));
$id_str = mysql_result($respo,0);
$sql_splits = "SELECT columns,rows FROM problem_info WHERE problem_id=%d";
$respon = mysql_query(sprintf($sql_splits,$id_str));
$res_arr = mysql_fetch_array($respon,MYSQL_NUM);
$id_columns = $res_arr[0];
$id_rows = $res_arr[1];
$path = sprintf("imgs/%d/",$id_str);
list($w,$h) = getimagesize($path."00.png");
for($x=0;$x<$id_columns;$x++)
  {
    for($y=0;$y<$id_rows;$y++)
      {
        $position = sprintf("%X%X.png",$x,$y);
        $img_path=sprintf("%s%s",$path,$position);
        $img_id = sprintf("%d_%d",$x,$y);
        echo sprintf("<Img Src='%s' id ='%s'  style ='position: absolute; top: %d; left: %d;'>",$img_path,$img_id,$h*$y,$w*$x);
        $blc_num1 = substr($answer_string, 2, 3);
      }
  }
$ans_str = json_encode($answer_string);
echo $row["answer_string"];
$version = $row["version"];
echo sprintf("<input style='position: absolute; top: 0; left: %d; 'type='button' name='spd_down' value='加速' onClick='spd_down()'>",50 + $w * $x);
echo sprintf("<input style='position: absolute; top: 0; left: %d; 'type='button' name='spd_up' value='減速' onClick='spd_up()'>",10 + $w * $x);
echo sprintf("<input style='position: absolute; top: 0; left: %d; 'type='button' name='reset' value='リセット' onClick='reset()'>",90 + $w * $x);
echo sprintf("");
echo sprintf("<pre style='position: absolute; top: 70; left: %d;'>", 10 + $w * $x);
echo $answer_string;
echo $version;
echo "</pre>"
?>
<script style ="text/javascript">
  
  var spd = 250;
function spd_up() {
  
    spd += 50;
}
function spd_down() {

  spd -= 50;
}
function reset() {

  window.location.reload();

}
  
  function parse(answer) {
  var lines = answer.split("\r\n"); // 行ごとに切り分ける
  var array = []; // 書き出し先の配列
  var i = 1; // 行番号 最初の行は飛ばす
  // すべての行を読み終えるまで
  while (i < lines.length) {
    // 選択位置を読む
    var sel = lines[i];
    var x = parseInt(sel.charAt(0), 16);  // 1文字目を16進数として読む
    var y = parseInt(sel.charAt(1), 16);  // 2文字目を16進数として読む
    i++;
    //array.push(["S", x, y]);  // 選択を追加
    array.push(["S"]);
    array.push([x+"_"+y]);
    i++; // 交換の数は読み飛ばす
    // 交換操作を読む
    var exchanges = lines[i].split(""); // 1文字ごとに切り分ける
    // 交換操作を1つずつ配列に入れる
    for (var j = 0; j < exchanges.length; j++) {
      array.push([exchanges[j]]);
    }
    i++;
  }
  return array;
}
var len = <?php echo $h; ?>;
var wid = <?php echo $w; ?>;

function read_id(str){
  var array = str.split("_");
  var x_str = array[0];
  var y_str = array[1];
  var x = parseInt(x_str, 10);
  var y = parseInt(y_str, 10);
  return [x,y];
}

function write_id(x,y){
  return "" + x + "_" + y;
}

function up(position){
  $("#"+position).animate({top:"-="+len},spd);
   console.log("up"+position);
}

function right(position){
  $("#"+position).animate({left:"+="+wid},spd);
   console.log("right"+position);
}

function left(position){
  $("#"+position).animate({left:"-="+wid},spd);
   console.log("left"+position);
}
function down(position){
  $("#"+position).animate({top:"+="+len},spd);
  console.log("down"+position);
}
//var img_id = "<?php echo $img_id; ?>";

function exchange_id(position1,position2){
  $("#"+position1).attr('id',position2 + "_");
  $("#"+position2).attr('id',position1);
  $("#"+position2 + "_").attr('id', position2);
}

var i = 0;
var position;
var flag = 0;
var change_flg = 0;

function command_controll(){
  if(ans_spl[i] == "U"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0],pos_id[1]-1);
    $.when(up(position),down(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
    });
  }
  if(ans_spl[i] == "R"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0]+1,pos_id[1]);
    $.when(right(position),left(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
    });
  }
  if(ans_spl[i] == "L"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0]-1,pos_id[1]);
    $.when(left(position),right(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
      });
  }
  if(ans_spl[i] == "D"){
    var pos_id = read_id(""+position);
    var position2 = write_id(pos_id[0],pos_id[1]+1);
    $.when(down(position),up(position2)).then(function () {
	exchange_id(position,position2);
	position = position2;
      });
  }  

  if(flag > 0){
    position = ans_spl[i];
    flag-=1;
    change_flg+=1;
  }
  if(ans_spl[i] == "S"){
    flag+=1;
    change_flg=0;
  }
  i++;
  setTimeout(command_controll, spd);
}

window.onload = function(ans_str)
    {
      alert("画像に合わせて拡大または縮小してください");
      var blc_num = <?php echo $ans_str; ?>;
      ans_spl= parse(blc_num);
      //setInterval(command_controll,spd);
      command_controll();
    }
</script>
  </body>
</html>

