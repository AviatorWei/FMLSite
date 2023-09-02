<?php
include_once "FML.php";

//分配奖金，更改状态
function submit_stage($conn,$name){
	global $valid_FMC_clubs;
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><title>".$name."</title></head><body><h2>FMC奖金</h2><div><code>Qualified:</code></div>
<div><code>");
	printWithFormat("Club",8,0);
	printWithFormat("Weight",8,0);
	printWithFormat("%",8,0);
	printWithFormat("Budget",8,0);
	printWithFormat("Prize",10,0);
	echo("Total</code></div>");
	//处理晋级球队
	$weightsum=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(weight) AS weights FROM teamsFMC WHERE isalive=1"))['weights'];
	$moneysum=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(Money) AS moneys FROM teamsFMC WHERE isalive=0"))['moneys'];
	$query=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1");
	while($array=mysqli_fetch_assoc($query)){
		echo("<div><code>");
		printWithFormat($array["Abbr"],8,0);
		printWithFormat($array["weight"],8,0);
		printWithFormat(number_format($array["weight"]*100/$weightsum,2)."%",8,0);
		printWithFormat($array["Money"],8,0);
		printWithFormat((string)round($array["weight"]*$moneysum/$weightsum),4,0);
		printWithFormat("+ 80",6,0);
		$sum=round($array["weight"]*$moneysum/$weightsum)+$array["Money"]+80;
		echo($sum);
		echo("</code></div>");
		mysqli_query($conn,"UPDATE teamsFMC SET Money=".$sum." WHERE Abbr='".$array['Abbr']."'");
		$query2=mysqli_query($conn,"SELECT * FROM currentFMC WHERE Team='".$array['Abbr']."'");
		while($array2=mysqli_fetch_assoc($query2)){
			if(!in_array($array2["Club"],$valid_FMC_clubs))
			mysqli_query($conn,"UPDATE currentFMC SET Team='',Price=0 WHERE Name='".$array2['Name']."'");
		}
	}
	//处理被淘汰球队
	mysqli_query($conn,"UPDATE teamsFMC SET weight=0");
	echo("<p></p><div><code>Elimated:</div><div>");
	printWithFormat("Club",24,0);
	echo("Budget</code></div>");
	$query=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=0 AND Money>0");
	while($array=mysqli_fetch_assoc($query)){
		echo("<div><code>");
		printWithFormat($array["Abbr"],24,0);
		echo($array['Money']);
		echo("</code></div>");
		mysqli_query($conn,"UPDATE currentFMC SET Team='',Price=0 WHERE Team='".$array['Abbr']."'");
	}
	mysqli_query($conn,"UPDATE teamsFMC SET Money=0 WHERE isalive=0");
	//处理被淘汰真实球队球员
	$query=mysqli_query($conn,"SELECT * FROM currentFMC WHERE isvalid=1");
	while($array=mysqli_fetch_assoc($query)){
		if(!in_array($array['Club'],$valid_FMC_clubs)){
			mysqli_query($conn,"UPDATE currentFMC SET isvalid=0 WHERE KeyinFML='".$array['KeyinFML']."'");
		}
	}
	//改变阶段
	$stage=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM status WHERE Activity='FMC'"))['TRANSFER_STAGE']+1;
	mysqli_query($conn,"UPDATE status SET TRANSFER_STAGE=".$stage." WHERE Activity='FMC'");
	echo("</table></body></html>");
}
?>