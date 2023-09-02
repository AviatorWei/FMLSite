<?php
include_once "FML.php";

//输出积分榜
function printLeagueTable($conn,$name){
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><title>".$name."</title></head><body><h2>一线队积分榜</h2><table><div><code>");
		printWithFormat("Team",6,0);
		printWithFormat("Rank",5,0);
		printWithFormat("W",5,0);
		printWithFormat("D",5,0);
		printWithFormat("L",5,0);
		printWithFormat("GF",5,0);
		printWithFormat("GA",5,0);
		printWithFormat("Pts",5,0);
		printWithFormat("Form",6,0);
		printWithFormat("RC",3,0);
		echo("</code></div>");
		doPrintLeagueTable($conn);
	echo("</table><h2>预备队积分榜</h2><table><div><code>");
	printWithFormat("Team",6,0);
	printWithFormat("Rank",5,0);
	printWithFormat("W",5,0);
	printWithFormat("D",5,0);
	printWithFormat("L",5,0);
	printWithFormat("GF",5,0);
	printWithFormat("GA",5,0);
	printWithFormat("Pts",5,0);
	printWithFormat("Form",6,0);
	printWithFormat("RC",3,0);
	echo("</code></div>");
		doPrintLeagueTable($conn,"res");
	echo("</table></body></html>");
}
function printLeagueTableFMC($conn,$name){
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><title>".$name."</title></head><body>");
		doPrintLeagueTableFMC($conn);
	echo("</body></html>");
}
function doPrintLeagueTable($conn,$str=""){
	$result=mysqli_query($conn,"SELECT * FROM teams ORDER BY ".$str."Points DESC,".$str."Goalfor DESC,".$str."Goalagainst DESC");
		$n=1;
		while($row=mysqli_fetch_assoc($result)){
			if(strlen($row[$str."charResult"])<5){
				$charres=$row[$str.'charResult'];
			}
			else{
				$charres=substr($row[$str.'charResult'],-5);
				if(strspn($charres, "W")==5 || strspn($charres, "D")==5 || strspn($charres, "L")==5 ){
					$c=$charres[0];
					$num=0;
					$sum=strlen($row[$str.'charResult']);
					while($sum-1-$num>=0 && $row[$str.'charResult'][$sum-1-$num]==$c)
						$num=$num+1;
					if($num<10)
					$charres=$c." - ".$num;
					else
					$charres=$c." -".$num;
				}
			}
			echo("<div><code>");
			if($str=="")
			printWithFormat($row['Abbr'],6,0);
			else
			printWithFormat(strtolower($row['Abbr']),6,0);
			printWithFormat($n,5,0);
			printWithFormat($row[$str.'Win'],5,0);
			printWithFormat($row[$str.'Draw'],5,0);
			printWithFormat($row[$str.'Lose'],5,0);
			printWithFormat($row[$str.'Goalfor'],5,0);
			printWithFormat($row[$str.'Goalagainst'],5,0);
			printWithFormat($row[$str.'Points'],5,0);
			if(!fmlmatchon($conn)){
			printWithFormat($charres,6,0);
			$Teamrankchange=$row["pre".$str."Teamrank"]-$row[$str.'Teamrank'];
			if($Teamrankchange>0)
			echo("↑");
			elseif($Teamrankchange<0)
			echo("↓");
			echo(abs($Teamrankchange));
			}
			echo("</code></div>");
			$n=$n+1;
		}
}
function doPrintLeagueTableFMC($conn){
	$ingroup=array('A','B','C','D');
	for($i=0;$i<4;$i++){
		echo("<div><code>");
		echo("group ".$ingroup[$i]."</div><div>");
		printWithFormat("Team",5,0);
		printWithFormat("W",3,0);
		printWithFormat("D",3,0);
		printWithFormat("L",3,0);
		printWithFormat("GF",3,0);
		printWithFormat("GA",4,0);
		printWithFormat("Pts",3,0);
		echo("</code></div>");
		$result=mysqli_query($conn,"SELECT * FROM teamsFMC WHERE ingroup='".$ingroup[$i]."' ORDER BY Points DESC,Goalfor DESC,Goalagainst DESC");
		while($row=mysqli_fetch_assoc($result)){
			echo("<div><code>");
			printWithFormat($row['Abbr'],5,0);
			printWithFormat($row['Win'],3,0);
			printWithFormat($row['Draw'],3,0);
			printWithFormat($row['Lose'],3,0);
			printWithFormat($row['Goalfor'],3,0);
			printWithFormat($row['Goalagainst'],4,0);
			printWithFormat($row['Points']." ",3,1);
			echo("</code></div>");
		}
		echo("<p></p>");
	}
}
?>