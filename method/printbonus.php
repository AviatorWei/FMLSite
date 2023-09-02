<?php
include_once "FML.php";

//输出奖金页面
function printbonus($array,$name){
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><title>".$name."</title></head><body><div><code>");
	printWithFormat("Team",5,0);
	printWithFormat("Assist",7,0);
	printWithFormat("RC",4,0);
	printWithFormat("LR",4,0);
	printWithFormat("GA",4,0);
	printWithFormat("PM",4,0);
	printWithFormat("Sum",5,0);
	printWithFormat("OB",4,0);
	echo("Total</code></div>");
	for($i=0;$i<count($array);$i++){
		echo("<div><code>");
		printWithFormat($array[$i][0],5,0);
		printWithFormat($array[$i][1],7,0);
		printWithFormat($array[$i][2],4,0);
		printWithFormat($array[$i][3],4,0);
		printWithFormat($array[$i][4],4,0);
		printWithFormat($array[$i][5],4,0);
		$sum=$array[$i][1]+$array[$i][2]+$array[$i][3]+$array[$i][4]+$array[$i][5];
		printWithFormat($sum,5,0);
		printWithFormat($array[$i][6],4,0);
		echo($sum+$array[$i][6]);
		echo("</code></div>");
	}
	echo("</body></html>");
}
?>