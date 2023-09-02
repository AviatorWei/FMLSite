<?php
include_once "FML.php";

//输出射手榜
function printTopGoalscorers($conn,$name,$num1=0,$num2=0,$pos="all"){
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><title>".$name."</title></head><body><h2>一线队射手榜</h2>");
	printWithFormat("球员",23,0);
	printWithFormat("球队",15,0);
	echo("进球");
	doPrintTopGoalScorers($conn,"",$num1,$pos);
	echo("</table><p></p><h2>预备队射手榜</h2>");
	printWithFormat("球员",23,0);
	printWithFormat("球队",15,0);
	echo("进球");
	doPrintTopGoalScorers($conn,"res",$num2,$pos);
	echo("</body></html>");
}
function printTopGoalscorersFMC($conn,$name,$num1=0,$pos="all"){
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><title>".$name."</title></head><body><h2>FMC射手榜</h2>");
	printWithFormat("球员",23,0);
	printWithFormat("球队",15,0);
	echo("进球");
	doPrintTopGoalScorersFMC($conn,$num1,$pos);
	echo("</table></body></html>");
}
function doPrintTopGoalscorers($conn,$str="",$num=0,$pos){
	if($pos=="all")
	$result=mysqli_query($conn,"SELECT Name,OwnerNum,Owner1,Owner2,Owner3,".$str."Goal,tmp".$str."Goal FROM current WHERE ".$str."Goal+tmp".$str."Goal>".$num." ORDER BY ".$str."Goal+tmp".$str."Goal DESC");
	else
	$result=mysqli_query($conn,"SELECT Name,OwnerNum,Owner1,Owner2,Owner3,".$str."Goal,tmp".$str."Goal FROM current WHERE ".$str."Goal+tmp".$str."Goal>".$num." AND Pos='".$pos."' ORDER BY ".$str."Goal+tmp".$str."Goal DESC");
		while($row=mysqli_fetch_assoc($result)){
			echo("<div><code>");
			printSingleName($conn,$row['Name']);
			printWithFormat("",21-strlen($row['Name']),0);
			if($str==""){
				if($row['OwnerNum']==3){
					echo($row['Owner1']."&".$row['Owner2']."&".$row['Owner3']);
					echo("&ensp;");
				}
				elseif ($row['OwnerNum']==2) {
					echo($row['Owner1']."&".$row['Owner2']);
					echo("&ensp;&ensp;&ensp;&ensp;&ensp;");
				}
				else{
					echo($row['Owner1']);
					echo("&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;");
				}
			}
			else{
				if($row['OwnerNum']==3){
					echo(strtolower($row['Owner1']."&".$row['Owner2']."&".$row['Owner3']));
					echo("&ensp;");
				}
				elseif ($row['OwnerNum']==2) {
					echo(strtolower($row['Owner1']."&".$row['Owner2']));
					echo("&ensp;&ensp;&ensp;&ensp;&ensp;");
				}
				else{
					echo(strtolower($row['Owner1']));
					echo("&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;&ensp;");
				}
			}
			printWithFormat($row[$str.'Goal']+$row['tmp'.$str.'Goal'],2,1);
			echo("</code></div>");
		}
}
function doPrintTopGoalscorersFMC($conn,$num=0,$pos){
	if($pos=="all")
	$result=mysqli_query($conn,"SELECT * FROM currentFMC WHERE Goal>".$num." ORDER BY Goal DESC");
	else
	$result=mysqli_query($conn,"SELECT * FROM currentFMC WHERE Goal>".$num." AND Pos='".$pos."' ORDER BY Goal DESC");
		while($row=mysqli_fetch_assoc($result)){
			echo("<div><code>");
			printSingleName($conn,$row['Name']);
			printWithFormat("",21-strlen($row['Name']),0);
			$array=explode(" ",trim($row["Owners"]));
			printWithFormat(implode("&",$array),13,0);
			printWithFormat($row['Goal'],2,1);
			echo("</code></div>");
		}
}
?>