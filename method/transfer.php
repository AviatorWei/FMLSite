<?php
include_once "BBS.php";

function freesign($conn,$team,$player,$money){
	//自由签不合规的条件列举
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."'"))==0){
	echo("球队输入错误。");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."'"))==0){
	echo("查无此人".$player);
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND Team=''"))==0){
	echo("该球员已有主。");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Team='".$team."'"))>=22) {
 	echo($team."已满22人！");
}
elseif(!is_numeric($money)){
	echo("请输入正确的金额");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."' AND Money>=".$money))==0) {
 	echo($team."没有足够的资金！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND OwnerNum<3"))==0){
	echo($player."已经被签约三次！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND (Owner1='".$team."' OR Owner2='".$team."' OR Owner3='".$team."')"))>0){
	echo($player."已经被".$team."签约过！");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."' AND Money>=".($money+10)))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Team='".$team."' AND Pos='G'"))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND Pos='G'"))==0) {
	echo($team."没有足够的资金再签下门将！");
}
//否则，自由签有效，更新数据库
else{
	mysqli_query($conn,"UPDATE current SET Team='".$team."',Price=".$money.",OwnerNum=OwnerNum+1 WHERE Name='".$player."'");
	mysqli_query($conn,"UPDATE teams SET Money=Money-".$money." WHERE Abbr='".$team."'");//调整money
	$res=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owner1,Owner2,Owner3 FROM current WHERE Name='".$player."'"));
	if($res['Owner1']==""){
		mysqli_query($conn,"UPDATE current SET Owner1='".$team."' WHERE Name='".$player."'");
	}
	elseif($res['Owner2']==""){
		mysqli_query($conn,"UPDATE current SET Owner2='".$team."' WHERE Name='".$player."'");
	}
	elseif($res['Owner3']==""){
		mysqli_query($conn,"UPDATE current SET Owner3='".$team."' WHERE Name='".$player."'");
	}
	//在日志中记录签约
	writeLog($team." sign ".$player);
	if(check_stage($conn) && check_FMC_clubs(mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."'"))["Club"])){
		freesignFMCAfterFML($conn,$team,$player);
		//在日志中记录签约
		writeLog($team." sign ".$player." in FMC");
	}
	echo($player."已加入".$team."。");
}
}

function freesignFMCAfterFML($conn,$team,$player){
	//FML签约后的连带FMC签约
	if (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Team='".$team."'"))>=22) {
		echo($team."已满22人！");
    }
	elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE Abbr='".$team."' AND Money>=".($money+10)))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Team='".$team."' AND Pos='G'"))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$player."' AND Pos='G'"))==0) {
		if (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE Abbr='".$team."' AND Money>=".($money+10)))==0)
		echo($team."没有足够的资金再签下门将！");
	}
	$ownerstr=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owners FROM currentFMC WHERE Name='".$player."'"))["Owners"].$team." ";
	mysqli_query($conn,"UPDATE currentFMC SET Team='".$team."',Price=10,Owners='".$ownerstr."' WHERE Name='".$player."'");
}

function freesignFMC($conn,$team,$player,$money){
	//自由签不合规的条件列举
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE Abbr='".$team."'"))==0){
	echo("球队输入错误。");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$player."'"))==0){
	echo("查无此人");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$player."' AND Team=''"))==0){
	echo("该球员已有主。");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Team='".$team."'"))>=22) {
 	echo($team."已满22人！");
}
elseif(!is_numeric($money)){
	echo("请输入正确的金额");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE Abbr='".$team."' AND Money>=".$money))==0) {
 	echo($team."没有足够的资金！");
}
elseif(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$player."' AND Owners LIKE '%".$team."%'"))>0){
	echo($player."已经被".$team."签约过！");
}
elseif (mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE Abbr='".$team."' AND Money>=".($money+10)))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Team='".$team."' AND Pos='G'"))==0 && mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$player."' AND Pos='G'"))==0) {
	echo($team."没有足够的资金再签下门将！");
}
//否则，自由签有效，更新数据库
else{
    $ownerstr=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owners FROM currentFMC WHERE Name='".$player."'"))["Owners"].$team." ";
	mysqli_query($conn,"UPDATE teamsFMC SET Money=Money-".$money." WHERE Abbr='".$team."'");//调整money
	if($money==0)
	$money=10;
	mysqli_query($conn,"UPDATE currentFMC SET Team='".$team."',Price=".$money.",Owners='".$ownerstr."' WHERE Name='".$player."'");
	//在日志中记录签约
	writeLog($team." sign ".$player." in FMC");
	echo($player."已加入".$team."。");
}
}

function release($conn,$team,$player){
	//判断是否能解约
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."' AND Team='".$team."'"))==0){
	echo($player."不在".$team."!");
}
//能则更新数据库并写日志
else{
	mysqli_query($conn,"UPDATE current SET Team='',Price=0 WHERE Name='".$player."'");
	writeLog($player." in ".$team." released");
	if(check_stage($conn) && check_FMC_clubs(mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$player."'"))["Club"])){
		releaseFMC($conn,$team,$player);
	}
	else
	echo("操作成功");
}
}

function releaseFMC($conn,$team,$player){
	//判断是否能解约
if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$player."' AND Team='".$team."'"))==0){
	echo($player."不在".$team."!");
}
//能则更新数据库并写日志
else{
	mysqli_query($conn,"UPDATE currentFMC SET Team='',Price=0 WHERE Name='".$player."'");
	writeLog($player." in ".$team." released in FMC");
	echo("操作成功");
}
}

function edittransferinBBS($conn,$match){
	global $BBSFMCsquadid,$BBSFMLsquadid;
	$htmlroute="History/".$match."squad.html";
	$txtroute="History/tmp".$match."squad.txt";
	printFile("printSquads",$conn,$match,$htmlroute,"method/printsquads.php");
	html2txt($htmlroute,$txtroute);
	/*if($match==""){
	$postid=$BBSFMLsquadid;
	editBBS("twa",$postid,"[FML]最新大名单",$txtroute);
	}
	else{
	$postid=$BBSFMCsquadid;
	editBBS("twa",$postid,"[FMC]最新大名单",$txtroute);
	}*/
}
?>