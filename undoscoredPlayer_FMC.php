<?php
include_once "method/renew.php";
include_once "method/BBS.php";
// if(checkCookie()){
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$str=mysqli_real_escape_string($conn,$_GET["str"]);

//整个文档完全就是把submitscoredPlayer.php做的事反过来做一遍，结构相同
$res=mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$str."'");
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=1"))==0){
	echo("现在不是比赛时间！");
}
elseif(mysqli_num_rows($res)==0){
	echo("查无此人！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$str."' AND tmpGoal>0"))==0){
	echo("本轮未提交此人进球。");
}
else{
	$team=mysqli_fetch_assoc($res)['Team'];
	if(mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$str."'"))['infirstteam']==1){
		updateGoals($conn,"FMC",$str,-1,"");
	}
	else{
		$tmpGoal=mysqli_fetch_assoc(mysqli_query($conn,"SELECT tmpGoal FROM currentFMC WHERE Name='".$str."'"))['tmpGoal']-1;
		mysqli_query($conn,"UPDATE currentFMC SET tmpGoal=".$tmpGoal." WHERE Name='".$str."'");
	}
	$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1"))['Round'];
	if($round<6)
	editliveinBBS($conn,"FMC","小组赛第".($round+1)."轮直播帖");
	else
	editliveinBBS($conn,"FMC",$titlenamearray[$round-6]."直播帖");
	writeLog("Delete ".$str."'s goal");
	echo("已撤销".$str."的进球");
}
mysqli_close($conn);
// }
// else{
// 	echo("没有权限");
// }
?>

