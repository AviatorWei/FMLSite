<?php
include_once "method/FML.php";
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
$sign=mysqli_fetch_assoc(mysqli_query($conn,"SELECT MATCH_ON FROM status WHERE Activity='FMC'"))['MATCH_ON'];
if($sign==1){
	echo("<script>alert('比赛正在进行！');history.back();</script>");
	return;
}
$teamnum=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1"));

echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset=\"utf-8\">
	<title>FMC直播贴生成器</title>
</head>
<body>
<h1>输入以下信息</h1>
<form action=\"\" method=\"POST\">
");
for($i=1;$i<$teamnum;$i+=2){
echo("<p>球队".$i."：  <input type=\"text\" oninput=\"this.value=this.value.replace(/[^a-zA-Z]/g,'');\" name=\"team".$i."\">    阵容".$i."： <input type=\"text\" oninput=\"this.value=this.value.replace(/[^a-zA-Z- \/.\']/g,'');\" name=\"squad".$i."\">    球队".($i+1)."： <input type=\"text\" oninput=\"this.value=this.value.replace(/[^a-zA-Z]/g,'');\" name=\"team".($i+1)."\">    阵容".($i+1)."： <input type=\"text\" oninput=\"this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');\" name=\"squad".($i+1)."\"></p>");
if($teamnum!=16)
echo("<p>PK顺序：   球队".$i."：    <input type=\"text\" oninput=\"this.value=this.value.replace(/[^a-zA-Z- \/.\']/g,'');\" name=\"pk".$i."\">  球队".($i+1)."：    <input type=\"text\" oninput=\"this.value=this.value.replace(/[^a-zA-Z \/.\']/g,'');\" name=\"pk".($i+1)."\"></p>");
}
echo("
<input type=\"submit\" name=\"check\" value=\"检查拼写\" formaction=\"Name_checker_FMC.php\"><input type=\"submit\" name=\"submit\" value=\"提交直播帖\" formaction=\"broadcast_submit_FMC.php\" >
</form>
</body>
</html>");
?>
