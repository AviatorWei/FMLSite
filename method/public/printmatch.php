<?php
include_once "FML.php";
include_once "pk.php";

//输出直播帖相关
function printBroadcast($conn,$name){
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no'/><title>".$name."</title><style type='text/css'>
html { font-size:50px; }
 .title{ font-size:.42rem; }
 .live { font-size:.28rem; }
@media(max-width:540px){ html { font-size:36px; } }
@media(max-width:480px){ html { font-size:32px; } }
@media(max-width:450px){ html { font-size:30px; } }
@media(max-width:405px){ html { font-size:27px; } }
@media(max-width:360px){ html { font-size:24px; } }
@media(max-width:330px){ html { font-size:22px; } }
@media(max-width:300px){ html { font-size:20px; } }
code{ hyphens:none;}
p{ margin:.28rem; }
</style></head><body><div class='title'>一线队</div>");
//开始输出一线队直播帖
		doPrintBroadcast($conn,"",16,"Teamrank");
		//开始输出首发阵容
		echo("<div class='title'>首发阵容</div>");
		printLineups($conn,"",16);
		//开始输出预备队比分，格式和一线队完全相同，除了队名小写以外
		echo("<div class='title'>预备队</div>");
		doPrintBroadcast($conn,"",16,"Teamrank","res");
	echo("</body></html>");
}
function printBroadcastFMC($conn,$name){
	echo("<!DOCTYPE html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no'/><title>".$name."</title><style type='text/css'>
html { font-size:50px; }
 .title{ font-size:.42rem; }
 .live { font-size:.28rem; }
@media(max-width:540px){ html { font-size:36px; } }
@media(max-width:480px){ html { font-size:32px; } }
@media(max-width:450px){ html { font-size:30px; } }
@media(max-width:405px){ html { font-size:27px; } }
@media(max-width:360px){ html { font-size:24px; } }
@media(max-width:330px){ html { font-size:22px; } }
@media(max-width:300px){ html { font-size:20px; } }
code{ hyphens:none;}
p{ margin:.28rem; }
</style></head><body>");
//开始输出一线队直播帖
	$teamnum=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1"));
	if($teamnum==16)
		doPrintBroadcast($conn,"FMC",$teamnum,"prePoint");
	else
		doPrintBroadcast($conn,"FMC",$teamnum,"firstleggoal");
	$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC"))['Round'];
	if($teamnum!=16 && $round%2==1)
	printpk();
		//开始输出首发阵容
		echo("<div class='title'>首发阵容</div>");
		printLineups($conn,"FMC",$teamnum);
		printother($conn,"misspen");
		printother($conn,"red");
		printother($conn,"assist");
		printotherscored($conn);
	echo("</body></html>");
}
function doPrintBroadcast($conn,$match,$teamnum,$needle,$str=""){
	//输出格式：球队1(排名)-空格*4-进球-空格*1-“-”号-空格*1-球队2进球-空格*4-球队2(排名)-空格*8-球队3(排名)-空格*4-进球-空格*1-“-”号-空格*1-球队4进球-空格*4-球队4(排名)-后面空一行
	if($str!="" && $str!="res"){
		echo("参数不对！");
		return;
	}
	for($i=0;$i<$teamnum/4;$i++){
			echo("<div class='live'><code>");
			$info1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode=".($i*4+1)));
			$info2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode=".($i*4+2)));
			printMatch($str,$info1,$info2,$needle);
			if($teamnum!=2){
			printWithFormat("",13,0);
			$info3=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode=".($i*4+3)));
			$info4=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode=".($i*4+4)));
			printMatch($str,$info3,$info4,$needle);
			}
			echo("</code></div>");
			//打印进球球员
			//待写，大概思路是用四个变量表示每个球队的进球球员数量，然后for循环打印球员，进球数及空格，直到所有数字都<=0
			printScoredPlayers($str,$conn,$match,$i);
			echo("<p></p>");
		}
}
function printMatch($str,$info1,$info2,$needle){
	printAbbr($str,$info1["Abbr"]);
			echo("(");
			if($info1[$str.$needle]<10)
				echo("0");
			echo($info1[$str.$needle]);
			echo(")");
			echo("&ensp;&ensp;&ensp;&ensp;");
			echo($info1["tmp".$str."Goal"]);
			echo("&ensp;-&ensp;");
			echo($info2["tmp".$str."Goal"]);
			echo("&ensp;&ensp;&ensp;&ensp;");
			printAbbr($str,$info2['Abbr']);
			echo("(");
			if($info2[$str.$needle]<10)
				echo("0");
			echo($info2[$str.$needle]);
			echo(")");
}
function printAbbr($str,$echostr){
	if($str=="")
				echo($echostr);
			else
				echo(strtolower($echostr));
}
function printScoredPlayers($str,$conn,$match,$i){//按照格式打印直播帖中的球员进球部分
	$array = array(array(),array(),array(),array());
	$maxlen=0;
	$clubnum=4;
	for($j=1;$j<=4;$j++){
		if(mysqli_num_rows(mysqli_query($conn,"SELECT Abbr FROM teams".$match." WHERE tmpCode=".($i*4+$j)))==0){
			$clubnum=2;
		break;
		}
		$team=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Abbr FROM teams".$match." WHERE tmpCode=".($i*4+$j)))['Abbr'];
		$query=mysqli_query($conn,"SELECT * FROM current".$match." WHERE Team='".$team."' AND tmp".$str."Goal>0 ORDER BY ScoreTime");
		while($row=mysqli_fetch_assoc($query)){
			if($match=="" || $row["infirstteam"]==1)
			array_push($array[$j-1],$row['Name'],$row["tmp".$str."Goal"]);
		}
		if($maxlen<count($array[$j-1]))
			$maxlen=count($array[$j-1]);
	}
	for($j=0;$j<$maxlen;$j+=2){
		echo("<div class='live'><code>");
		for($k=0;$k<$clubnum;$k++){
			$printsize=20;
			if($j>=count($array[$k]))
				$tmpstr="";
			else{
				$tmpstr=$array[$k][$j];
				printSingleName($conn,$tmpstr);
				$printsize=$printsize-strlen($tmpstr);
				if($array[$k][$j+1]>1)
					$tmpstr="*".$array[$k][$j+1];
				else
					$tmpstr="";
			}
			printWithFormat($tmpstr,$printsize,0);
		}
		echo("</code></div>");
	}
	echo("<p></p>");
}
function printpk($conn,$teamnum){
	for($i=1;$i<$teamnum;$i+=2){
		$array1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=".$i));
		$array2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode=".($i+1)));
		if($array1['firstleggoal']+$array1['tmpGoal']==$array2['firstleggoal']+$array2['tmpGoal']){
			$pkarray1=explode(" ",trim($array1['pkturn']));
			$pkvalue1=array(0,0,0,0,0,0,0,0,0,0,0);
			$pksum1=0;
			for($j=0;$j<5;$j++){
				$pkvalue1[$j]=PK_calculate($conn,$pkarray1[$j]);
				$pksum1+=$pkvalue1[$j];
			}
			$pkarray2=explode(" ",trim($array2['pkturn']));
			$pkvalue2=array(0,0,0,0,0,0,0,0,0,0,0);
			$pksum2=0;
			for($j=0;$j<5;$j++){
				$pkvalue2[$j]=PK_calculate($conn,$pkarray2[$j]);
				$pksum2+=$pkvalue2[$j];
			}
			$j=5;
			while($pksum1==$pksum2){
				if($j==count($pkarray1)){
					$pkvalue1[$j]=-6.6;
				}
				else
				$pkvalue1[$j]=PK_calculate($conn,$pkarray1[$j]);
				$pksum1+=$pkvalue1[$j];
				if($j==count($pkarray2)){
					$pkvalue2[$j]=-6.7;
				}
				else
				$pkvalue2[$j]=PK_calculate($conn,$pkarray2[$j]);
				$pksum2+=$pkvalue2[$j];
				$j+=1;
			}
			echo("<div class='live'><code>");
			echo($array1['Abbr'].": ");
			for($k=0;$k<$j-1;$k++){
				echo($pkvalue1[$k]);
				echo("+");
			}
			echo($pkvalue1[$j-1]);
			echo("=");
			echo(array_sum($pkvalue1));
			echo("</code></div>");

			echo("<div class='live'><code>");
			echo($array2['Abbr'].": ");
			for($k=0;$k<$j-1;$k++){
				echo($pkvalue2[$k]);
				echo("+");
			}
			echo($pkvalue2[$j-1]);
			echo("=");
			echo(array_sum($pkvalue2));
			echo("</code></div>");

			echo("<p></p>");
		}
	}
}
function printLineups($conn,$match,$teamnum){
	$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode=1"))['Round'];
	for($i=0;$i<$teamnum/2;$i++){
			$info1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode='".($i*2+1)."'"));
			printOneLineup($info1);
			if($teamnum!=16 && $round%2==1)
			printpklineup($info1);
			$info2=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match." WHERE tmpCode='".($i*2+2)."'"));
			printOneLineup($info2);
			if($teamnum!=16 && $round%2==1)
			printpklineup($info2);
			echo("<p></p>");
		}
}
function printOneLineup($info1){
	//格式：球队名-空格或*-球队阵容
			echo("<div class='live'><code>");
			echo($info1['Abbr']);
			if($info1['isOldLineup']==1){//判断球队本轮有没有发阵容，没发则沿用上一轮阵容且在阵容前加*号
				echo("*");
			}
			else{//否则加空格
				echo(" ");
			}
			echo(str_replace("\"","",$info1['Lineup']));
			echo("</code></div>");
}
function printpklineup($info1){
	echo("<div class='live'><code>PK: ");
	echo($info1['pkturn']);
	echo("</code></div>");
}
function printother($conn,$item){
	echo("<div class='title'><code>".$item."</code></div>");
	$query=mysqli_query($conn,"SELECT * from currentFMC WHERE tmp".$item.">0 ORDER BY Club");
	while($row=mysqli_fetch_assoc($query)){
		echo("<div class='live'><code>");
		printSingleName($conn,$row["Name"]);
		if($row["tmp".$item]>1){
			echo("*".$row["tmp".$item]);
		}
		echo("(".$row["Club"].")");
		if($row["Team"]!=""){
			echo("[");
			if($row["infirstteam"]==1)
			echo($row["Team"]);
			else
			echo(strtolower($row["Team"]));
			echo("]");
		}
		echo("</code></div>");
	}
	echo("<p></p>");
}
function printotherscored($conn){
	echo("<div class='title'><code>Free Agents</code></div>");
	$query=mysqli_query($conn,"SELECT * from currentFMC WHERE tmpGoal>0 AND Team='' ORDER by Club");
	while($row=mysqli_fetch_assoc($query)){
		echo("<div class='live'><code>");
		printSingleName($conn,$row["Name"]);
		if($row["tmpGoal"]>1){
			echo("*".$row["tmpGoal"]);
		}
		echo("(".$row["Club"].")");
		echo("</code></div>");
	}
	echo("<p></p>");

	echo("<div class='title'><code>Miss</code></div>");
	$query=mysqli_query($conn,"SELECT * from currentFMC WHERE tmpGoal>0 And Team<>'' AND infirstteam=0 ORDER BY Team");
	while($row=mysqli_fetch_assoc($query)){
		echo("<div class='live'><code>");
		printSingleName($conn,$row["Name"]);
		if($row["tmpGoal"]>1){
			echo("*".$row["tmpGoal"]);
		}
		echo("(".$row["Club"].")");
		if($row["Team"]!=""){
			echo("[");
			echo(strtolower($row["Team"]));
			echo("]");
		}
		echo("</code></div>");
	}
	echo("<p></p>");
}
?>