<html>
<head>
</head>
<body>

<form action="extension.php" method="POST">
<p align="center">本当に削除してよろしいですか?
<br>
<br>
<input type="submit" value="YES"/>
</p>
<?php
   if (isset($_GET["problem_id"])){
     echo ('<input type="hidden" name="problem_id" value="'.$_GET["problem_id"].'">');
   }else if (isset($_GET["score_id"])){
     echo ('<input type="hidden" name="score_id" value="'.$_GET["score_id"].'">');
   }
?>
</form>
<p align ="center"><A Href="index.php">戻る</A></p>
</body>
</html>
