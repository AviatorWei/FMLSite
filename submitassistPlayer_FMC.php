<?php
include_once "method/FML.php";
//if(checkCookie()){
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$str=mysqli_real_escape_string($conn,$_GET["str"]);
$len=strlen($str);
$num=$str[$len-1];
if(is_numeric($num) && (int)$num>0){
	$str=substr($str,0,$len-1);
	$num=(int)$num;
}
else{
	$num=1;
}
//判断一下条件
$res=mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$str."'");
$resfetch=mysqli_fetch_assoc($res);
$team=$resfetch['Team'];
$tmpassist=$resfetch['tmpassist'];
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=1"))==0){
	echo("现在不是比赛时间！");
}
elseif(mysqli_num_rows($res)==0){
	echo("查无此人！");
}
elseif($num!=$tmpassist+1){
	echo("输入的助攻数不对。");
}
//没问题，则开始更新数据库
else{
        $tmpassist=mysqli_fetch_assoc(mysqli_query($conn,"SELECT tmpassist FROM currentFMC WHERE Name='".$str."'"))['tmpassist']+1;
		mysqli_query($conn,"UPDATE currentFMC SET tmpassist=".$tmpassist." WHERE Name='".$str."'");
		writeLog("Add ".$str."'s assist");
		echo("已添加".$str."的助攻");
}
mysqli_close($conn);
/*}
else{
	echo("没有权限");
}*/
?>
