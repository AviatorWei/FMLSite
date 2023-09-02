<?php
include_once "method/transfer.php";
if(checkCookie()){
	$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
//球队1，球队2，球员字符串1，球员字符串2，钱
$team1=mysqli_real_escape_string($conn,strtoupper($_GET['team1']));
$team2=mysqli_real_escape_string($conn,strtoupper($_GET['team2']));
$playerstr1=mysqli_real_escape_string($conn,$_GET['player1']);
$playerstr2=mysqli_real_escape_string($conn,$_GET['player2']);
$money=mysqli_real_escape_string($conn,$_GET['money']);

$sign=0;
//制作球员列表
$playerarray1=explode(",", $playerstr1);
$playerarray2=explode(",", $playerstr2);
if($playerstr1==""){
	$len1=0;
}
else{
$len1=count($playerarray1);
}
if($playerstr2==""){
	$len2=0;
}
else{
$len2=count($playerarray2);
}
//判断各种交易不成立的条件
if(!is_numeric($money)){
	echo("请输入正确的金额");
	$sign=1;
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Team='".$team1."'"))-$len1+$len2>22){
	echo("交易后".$team1."人数超过限制，交易失败。");
	$sign=1;
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Team='".$team2."'"))+$len1-$len2>22){
	echo("交易后".$team2."人数超过限制，交易失败。");
	$sign=1;
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE Abbr='".$team1."' AND Money>=".(int)$money.""))==0){
	echo($team1."资金不足，交易失败。");
	$sign=1;
}
//进入列表，对每一名球员分别判断
if($sign==0){
for($i=0;$i<$len1;$i++){
	if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$playerarray1[$i]."' AND Team='".$team1."'"))==0){
		echo($playerarray1[$i]."不在".$team1."，交易失败。");
		$sign=1;
		break;
	}
	if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$playerarray1[$i]."' AND Owners LIKE '%".$team2."%'"))>0){
		echo($playerarray1[$i]."已经被".$team2."签约过，交易失败。");
		$sign=1;
		break;
	}
}
}
if($sign==0){
for($i=0;$i<$len2;$i++){
	if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$playerarray2[$i]."' AND Team='".$team2."'"))==0){
		echo($playerarray2[$i]."不在".$team2."，交易失败。");
		$sign=1;
		break;
	}
	if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$playerarray2[$i]."' AND Owners LIKE '%".$team."%'"))>0){
		echo($playerarray2[$i]."已经被".$team1."签约过，交易失败。");
		$sign=1;
		break;
	}
}
}
//没问题，则开始更新数据库
if($sign==0){
	mysqli_query($conn,"START TRANSACTION");
	for($i=0;$i<$len1;$i++){
        //更新球员数据库的owner相关字段
        $ownerstr=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owners FROM currentFMC WHERE Name='".$player."'"))["Owners"].$team2." ";
		mysqli_query($conn,"UPDATE currentFMC SET Team='".$team2."',Owners='".$ownerstr."' WHERE Name='".$playerarray1[$i]."'");
	}
	for($i=0;$i<$len2;$i++){
        $ownerstr=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owners FROM currentFMC WHERE Name='".$player."'"))["Owners"].$team1." ";
		mysqli_query($conn,"UPDATE currentFMC SET Team='".$team1."',Owners='".$ownerstr."' WHERE Name='".$playerarray2[$i]."'");
	}
	//更新球队的金钱
	mysqli_query($conn,"UPDATE teamsFMC SET Money=Money-".(int)$money." WHERE Abbr='".$team1."'");
	mysqli_query($conn,"UPDATE teamsFMC SET Money=Money+".(int)$money." WHERE Abbr='".$team2."'");
	if($len1==0 && $len2==1){//假如涉及交易的球员只有1个，则可以知道他的最新价格
		mysqli_query($conn,"UPDATE currentFMC SET Price=".$money." WHERE Name='".$playerarray2[0]."'");
	}
	mysqli_query($conn,"COMMIT");
	writeLog("Deal between ".$team1." (".$playerstr1.",".$money.") and ".$team2." (".$playerstr2.") made in FMC");
	echo("操作成功");
}
edittransferinBBS($conn,"FMC");
mysqli_close($conn);
}
else{
	echo("没有权限");
}
?>
