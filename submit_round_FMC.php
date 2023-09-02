<?php
include_once "method/FML.php";
include_once "method/BBS.php";
include_once "method/pk.php";
if(checkCookie()){
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
	die('Could not connect: ' . mysqli_error($conn));
}
//检查是否正在进行该轮
$match_on=mysqli_fetch_assoc(mysqli_query($conn,"SELECT MATCH_ON FROM status WHERE Activity='FMC'"))['MATCH_ON'];
if($match_on==0){
	echo("<script>alert('没有FMC比赛正在进行！');window.close();</script>");
	return;
}

/*要做的事情：
teams：
将所有球队的一线队进球和其对手进球比较，得到临时积分和结果。		更新直播帖，并另存为文件。
更新积分，轮次，战绩字符串。将积分排序，得到排名。			更新积分榜并另存为文件。
给每个球队发钱。
将临时进球，预备队进球，tmpcode，临时积分清零。
判断是否更改状态。
current:
更新每个球员的一线队/预备队进球。		更新射手榜，并另存为文件。	输出所有助攻，黄牌，红牌，奖金。
将临时进球清零。
status:
结束这一轮。
判断是否更改状态
*/

function getbonusarray($array){
	global $conn;
	$tmpcode=$array["tmpCode"];
	$tmpcode2=$tmpcode+1-2*(($tmpcode-1)%2);
	$array2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=".$tmpcode2));
	//主场丢球奖
	$homeconcede=0;
	if($tmpcode%2==1)
	$homeconcede=$array2['tmpGoal']*5;
	//lrld奖
	$lrld=0;
	if($array2['tmpGoal']>3 && $array['tmpGoal']+2<$array2['tmpGoal'])
	$lrld=2*$array2['tmpGoal']-$array['tmpGoal'];
	//助攻奖
	$assist=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(tmpassist) AS assists FROM currentFMC WHERE team='".$array['Abbr']."' AND infirstteam=1"))['assists']*5;
	//红牌奖
	$red=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(tmpred) AS reds FROM currentFMC WHERE team='".$array['Abbr']."' AND infirstteam=1"))['reds']*5;
	//misspen奖
	$misspen=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(tmpmisspen) AS misspens FROM currentFMC WHERE team='".$array['Abbr']."' AND infirstteam=1"))['misspens']*3;
	//初始资金
	$original=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Money FROM teamsFMC WHERE Abbr='".$array['Abbr']."'"))['Money'];
	return array($array['Abbr'],$assist,$red,$lrld,$homeconcede,$misspen,$original);
}

$ingroup=array('A','B','C','D');
$money_array=array();
//输出直播帖
$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Round FROM teamsFMC WHERE isalive=1"))['Round']+1;
//editliveinBBS($conn,"FMC","小组赛第".$round."轮直播帖");
printFile("printBroadcastFMC",$conn,"FMC直播帖&首发阵容","./History/FMC_live_".$round.".html","method/printmatch.php");
//处理球队数据库
if($round<=6){//小组赛
//算钱并打印结果
for($i=0;$i<4;$i++){
	$teams=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE ingroup='".$ingroup[$i]."'");
	while($array=mysqli_fetch_assoc($teams)){
		$tmp_array=getbonusarray($array);
		array_push($money_array,$tmp_array);
		mysqli_query($conn,"UPDATE teamsFMC SET Money=".$array['Money']."+".$tmp_array[1]."+".$tmp_array[2]."+".$tmp_array[3]."+".$tmp_array[4]."+".$tmp_array[5]." WHERE Abbr='".$array['Abbr']."'");
	}
}
//以下清空teamsFMC数据库的临时数据，并更新排名（小组赛）
for($i=0;$i<4;$i++){
	$result=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE ingroup='".$ingroup[$i]."' ORDER BY Points DESC,Goalfor DESC,Goalagainst DESC");
	$n=1;
	while($array=mysqli_fetch_assoc($result)){
		if($round==6){
			if($n>2)
			mysqli_query($conn,"UPDATE teamsFMC SET isalive=0 WHERE Abbr='".$array['Abbr']."'");
			else
			mysqli_query($conn,"UPDATE teamsFMC SET weight=".($array["Points"]*2+$array["Goalfor"])." WHERE Abbr='".$array['Abbr']."'");
		}
		mysqli_query($conn,"UPDATE teamsFMC SET Teamrank=".$n.",prePoint=".$array["Points"].",tmpCode=0,tmpGoal=0,isoldLineup=0,Round=".$round." WHERE Abbr='".$array['Abbr']."'");
		$n=$n+1;
	}
}
//输出本轮积分榜（小组赛）
printFile("printLeagueTableFMC",$conn,"FMC第".$round."轮积分榜","./History/FMC_league_table_".$round.".html","method/printtable.php");
postinBBS("PES","[FMC]第".$round."轮积分榜","./History/FMC_league_table_".$round.".html","./History/FMC_league_table_".$round.".txt");
}
elseif($round%2==1){//淘汰赛首回合
	//发钱并记录首回合进球
	$teams=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1");
	while($array=mysqli_fetch_assoc($teams)){
		$tmp_array=getbonusarray($array);
		array_push($money_array,$tmp_array);
		mysqli_query($conn,"UPDATE teamsFMC SET Money=".$array['Money']."+".$tmp_array[1]."+".$tmp_array[2]."+".$tmp_array[3]."+".$tmp_array[4]."+".$tmp_array[5].",firstleggoal=".$array['tmpGoal']." WHERE Abbr='".$array['Abbr']."'");
	}
	//清空临时数据
	$teams=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1");
	while($array=mysqli_fetch_assoc($teams)){
		mysqli_query($conn,"UPDATE teamsFMC SET tmpCode=0,tmpGoal=0,isoldLineup=0,Round=".$round." WHERE Abbr='".$array['Abbr']."'");
	}
}
else{//淘汰赛次回合
	//发钱
	$teams=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1");
	while($array=mysqli_fetch_assoc($teams)){
		$tmp_array=getbonusarray($array);
		array_push($money_array,$tmp_array);
		mysqli_query($conn,"UPDATE teamsFMC SET Money=".$array['Money']."+".$tmp_array[1]."+".$tmp_array[2]."+".$tmp_array[3]."+".$tmp_array[4]."+".$tmp_array[5]." WHERE Abbr='".$array['Abbr']."'");
	}
	//判断谁被淘汰了，并清理临时数据
	for($i=1;$i<8;$i+=2){
		$array1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=".$i));
		$array2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=".($i+1)));
		if($array1['firstleggoal']+$array1['tmpGoal']<$array2['firstleggoal']+$array2['tmpGoal']){# || ($array1['firstleggoal']+$array1['tmpGoal']==$array2['firstleggoal']+$array2['tmpGoal'] && $array1['firstleggoal']<$array2['tmpGoal'])){
			mysqli_query($conn,"UPDATE teamsFMC SET isalive=0 WHERE tmpCode=".$i);
			mysqli_query($conn,"UPDATE teamsFMC SET weight=".($array2['firstleggoal']+$array2['tmpGoal']-$array1['firstleggoal']-$array1['tmpGoal']+1)." WHERE tmpCode=".($i+1));
		}
		elseif($array1['firstleggoal']+$array1['tmpGoal']==$array2['tmpGoal']+$array2['firstleggoal']){
			$array1['pkturn'] = explode(' ', trim(strtolower($array1['pkturn'])));
			$array2['pkturn'] = explode(' ', trim(strtolower($array2['pkturn'])));
			if(sizeof($array1['pkturn']) < 10)
			{
				$tmp = array_reverse(explode("/", str_replace("\"","",strtolower(trim($array1["Lineup"])))));
                                $array1['pkturn'] = explode(" ", "".implode(" ",$tmp));
			}
			if(sizeof($array2['pkturn']) < 10)
			{
				$tmp = array_reverse(explode("/", str_replace("\"","",strtolower(trim($array2["Lineup"])))));
				$array2['pkturn'] = explode(" ", "".implode(" ",$tmp));
			}
			if(!PK($conn,$array1['pkturn'],$array2['pkturn'])){
			mysqli_query($conn,"UPDATE teamsFMC SET isalive=0 WHERE tmpCode=".$i);
			mysqli_query($conn,"UPDATE teamsFMC SET weight=1 WHERE tmpCode=".($i+1));
			}
			else{
			mysqli_query($conn,"UPDATE teamsFMC SET isalive=0 WHERE tmpCode=".($i+1));
			mysqli_query($conn,"UPDATE teamsFMC SET weight=1 WHERE tmpCode=".$i);
			}
		}
		else{
		mysqli_query($conn,"UPDATE teamsFMC SET isalive=0 WHERE tmpCode=".($i+1));
		mysqli_query($conn,"UPDATE teamsFMC SET weight=".($array1['firstleggoal']+$array1['tmpGoal']-$array2['firstleggoal']-$array2['tmpGoal']+1)." WHERE tmpCode=".$i);
		}
		mysqli_query($conn,"UPDATE teamsFMC SET tmpCode=0,tmpGoal=0,isoldLineup=0,pkturn='',firstleggoal=0,Round=".$round." WHERE tmpCode=".$i." OR tmpCode=".($i+1));
	}
}
printFile("printbonus",$money_array,"FMC本轮奖金统计","./History/FMC_bonus_".$round.".html","method/printbonus.php");
postinBBS("PES","[FMC]本轮奖金统计","./History/FMC_bonus_".$round.".html","./History/FMC_bonus_".$round.".txt");
//下面更新currentFMC数据库并输出射手榜
$tmpscoredplayer=mysqli_query($conn,"SELECT * FROM currentFMC WHERE tmpGoal>0 AND infirstteam=1");
//对每个球员，更新进球
while($res=mysqli_fetch_assoc($tmpscoredplayer)){
		mysqli_query($conn,"UPDATE currentFMC SET Goal=".$res['Goal']."+".$res['tmpGoal']." WHERE Name='".$res['Name']."'");
}
//将球员所有数据清零
$players=mysqli_query($conn,"SELECT * FROM currentFMC WHERE tmpGoal+tmpassist+tmpyellow+tmpred+tmpmisspen+infirstteam>0");
while($res=mysqli_fetch_assoc($players)){
	$res['Name']=str_replace("'","\'",$res['Name']);
	mysqli_query($conn,"UPDATE currentFMC SET tmpGoal=0,tmpassist=0,tmpyellow=0,tmpred=0,tmpmisspen=0,infirstteam=0,ScoreTime=0 WHERE Name='".$res['Name']."'");
}
//导出射手榜
printFile("printTopGoalscorersFMC",$conn,"FMC射手榜","./History/FMC_top_goalscorers_".$round.".html","method/printgoalscorers.php");
//假如有球队被淘汰，改变阶段
if($round>=6 && $round%2==0){
	printfile("submit_stage",$conn,"[FMC]晋级奖金公示","./History/FMC_bonus_allocation_".(($round-4)/2).".html","method/changestage.php");
	postinBBS("PES","FMC晋级奖金公示","./History/FMC_bonus_allocation_".(($round-4)/2).".html","./History/FMC_bonus_allocation_".(($round-4)/2).".txt");
}
//设置status数据库的状态，表示比赛结束
mysqli_query($conn,"UPDATE status SET MATCH_ON=0,LAST_SCORED_PLAYER=NULL WHERE Activity='FMC'");
mysqli_close($conn);
writeLog("Submit FMC round");
echo("<script> alert('已完成！'); </script>");
//输出一个很简单的网页，供查看结果
echo("
<!DOCTYPE html>
<html>
<head>
	<title>已完成导入</title>
</head>
<body>");
	echo("<p>直播帖已保存在<a href='History/FMC_live_".$round.".html'>链接</a></p>");
	if($round<=6)
	echo("<p>积分榜已保存在<a href='History/FMC_league_table_".$round.".html'>链接</a></p>");
	echo("<p>奖金统计已保存在<a href='History/FMC_bonus_".$round.".html'>链接</a></p>");
	echo("<p>射手榜已保存在<a href='History/FMC_top_goalscorers_".$round.".html'>链接</a></p>");
	if($round>=6 && $round%2==0)
	echo("<p>晋级奖金分配已保存在<a href='History/FMC_bonus_allocation_".(($round-4)/2).".html'>链接</a></p>");
	echo("<a href='index.php'>回到首页</a>
</body>
</html>");
}
else{
	echo("<script>alert('没有权限！');window.close();</script>");
}
?>
