<?php
include_once "method/FML.php";
if(checkCookie()){
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$str=mysqli_real_escape_string($conn,$_GET["str"]);

$res=mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$str."'");
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=1"))==0){
	echo("现在不是比赛时间！");
}
elseif(mysqli_num_rows($res)==0){
	echo("查无此人！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$str."' AND tmpmisspen>0"))==0){
	echo("本轮未提交此人失点。");
}
else{
	$tmpmisspen=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$str."'"))['tmpmisspen']-1;
	mysqli_query($conn,"UPDATE currentFMC SET tmpmisspen=".$tmpmisspen." WHERE Name='".$str."'");
	writeLog("Delete ".$str."'s misspen");
	echo("已撤销".$str."的失点");
}
mysqli_close($conn);
}
else{
	echo("没有权限");
}
?>

