<?php
include_once "FML.php";

//更新或删除进球
function updateGoals($conn,$match,$player,$n,$str=''){
	$team=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM current".$match." WHERE Name='".$player."'"))['Team'];
	$resultf=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE Abbr='".$team."'"));
	//通过tmpCode找到本轮对手
	$tmpCode=$resultf['tmpCode'];
	$resulta=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode=".($tmpCode+1-2*(($tmpCode-1)%2))));
	$team2=$resulta['Abbr'];
	/*要更新的字段：
	teams:
	胜/平/负，积分，本轮进球
	current:
	本轮进球
	*/
		//判断比赛结果
		//除了之前进球差为0或-1外，其余情况不会导致胜负发生变化
	if($match=="" || mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM status WHERE Activity='".$match."'"))['TRANSFER_STAGE']<3){
		if($resultf['tmp'.$str.'Goal']+($n-1)/2==$resulta['tmp'.$str.'Goal']){
			mysqli_query($conn,"UPDATE teams".$match." SET ".$str."Win=".($resultf[$str.'Win']+$n).",".$str."Draw=".($resultf[$str.'Draw']-$n).",".$str."Points=".($resultf[$str.'Points']+2*$n).",tmp".$str."Goal=".($resultf["tmp".$str."Goal"]+$n).",".$str."Goalfor=".($resultf[$str."Goalfor"]+$n)." WHERE Abbr='".$team."'");
			mysqli_query($conn,"UPDATE teams".$match." SET ".$str."Lose=".($resulta[$str.'Lose']+$n).",".$str."Draw=".($resulta[$str.'Draw']-$n).",".$str."Points=".($resulta[$str.'Points']-$n).",".$str."Goalagainst=".($resulta[$str."Goalagainst"]+$n)." WHERE Abbr='".$team2."'");
		}
		elseif($resultf["tmp".$str."Goal"]+($n+1)/2==$resulta["tmp".$str."Goal"]){
			mysqli_query($conn,"UPDATE teams".$match." SET ".$str."Lose=".($resultf[$str.'Lose']-$n).",".$str."Draw=".($resultf[$str.'Draw']+$n).",".$str."Points=".($resultf[$str.'Points']+$n).",tmp".$str."Goal=".($resultf["tmp".$str."Goal"]+$n).",".$str."Goalfor=".($resultf[$str."Goalfor"]+$n)." WHERE Abbr='".$team."'");
			mysqli_query($conn,"UPDATE teams".$match." SET ".$str."Win=".($resulta[$str.'Win']-$n).",".$str."Draw=".($resulta[$str.'Draw']+$n).",".$str."Points=".($resulta[$str.'Points']-2*$n).",".$str."Goalagainst=".($resulta[$str."Goalagainst"]+$n)." WHERE Abbr='".$team2."'");
		}
		else{
			mysqli_query($conn,"UPDATE teams".$match." SET tmp".$str."Goal=".($resultf["tmp".$str."Goal"]+$n).",".$str."Goalfor=".($resultf[$str."Goalfor"]+$n)." WHERE Abbr='".$team."'");
			mysqli_query($conn,"UPDATE teams".$match." SET ".$str."Goalagainst=".($resulta[$str."Goalagainst"]+$n)." WHERE Abbr='".$team2."'");
		}
	}
	else{
		mysqli_query($conn,"UPDATE teams".$match." SET tmp".$str."Goal=".($resultf["tmp".$str."Goal"]+$n)." WHERE Abbr='".$team."'");
	}
	$nowgoal=mysqli_fetch_assoc(mysqli_query($conn,"SELECT tmp".$str."Goal FROM current".$match." WHERE Name='".$player."'"))["tmp".$str."Goal"];
	if($nowgoal==0)
	mysqli_query($conn,"UPDATE current".$match." SET ScoreTime=".time()." WHERE Name='".$player."'");
	elseif($nowgoal+$n==0)
	mysqli_query($conn,"UPDATE current".$match." SET ScoreTime=0 WHERE Name='".$player."'");
	mysqli_query($conn,"UPDATE current".$match." SET tmp".$str."Goal=".($nowgoal+$n)." WHERE Name='".$player."'");
}
?>