<?php
include_once "method/BBS.php";
if(checkCookie()){
	//这个文件的任务有两个：填写数据库中所有和一轮比赛相关的字段（清理上一轮的信息已经在submit_round文件中做了），和输出BBS风格的直播帖以便复制粘贴到版面
	$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
		//要写入数据库的信息：每个球队的对手，每个球队的首发，每个球队的当轮编号，输出BBS风格的直播帖
        //开始更新球队数据
        $teamnum=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1"));
		mysqli_query($conn,"START TRANSACTION");
		for($i=1;$i<=$teamnum;$i++){
			$team1=mysqli_real_escape_string($conn,$_POST["team".$i]);
			//初始化teams数据库信息，目前所有球队都是0-0平局，平局数+1，积分+1
			//更新本轮对手，临时号
			//临时号标定了球队在直播帖上的位置，之后会用来确定球队本轮对手
			$draw1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE Abbr='".$team1."'"));
			mysqli_query($conn,"UPDATE teamsFMC SET tmpCode=".$i.",Draw=".($draw1['Draw']+1).",Points=".($draw1['Points']+1)." WHERE Abbr='".$team1."'");
			//开始更新首发阵容
			$lineup1=mysqli_real_escape_string($conn,$_POST["squad".$i]);
			if($lineup1==""){//判断球队本轮有没有发阵容，没发则字符串为空，那么沿用上一轮阵容
				mysqli_query($conn,"UPDATE teamsFMC SET isOldLineup=1 WHERE Abbr='".$team1."'");
			}
			else{//否则更新数据库中的阵容
				mysqli_query($conn,"UPDATE teamsFMC SET Lineup='".$lineup1."',isOldLineup=0 WHERE Abbr='".$team1."'");
			}
			$lineup1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE Abbr='".$team1."'"))["Lineup"];
			$array=explode(' ',str_replace("\"","",str_replace("/", " ", strtolower($lineup1))));
			foreach ($array as $key => $value) {
				$value=str_replace("'","\'",$value);
				mysqli_query($conn,"UPDATE currentFMC SET infirstteam=1 WHERE Name='".$value."'");
			}
            if($teamnum<16){
                $pk1=$_POST["pk".$i];
                mysqli_query($conn,"UPDATE teamsFMC SET pkturn='".$pk1."' WHERE Abbr='".$team1."'");
            }
		}
		//在版面发直播帖
		$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE isalive=1"))['Round'];
		if($round<6)
		$postid=postBBS("PES","[FMC]小组赛第".($round+1)."轮直播帖","./test.txt");
        else
		$postid=postBBS("PES",$titlenamearray[$round-6]."直播帖","./test.txt");
		///更新status数据库，宣布比赛开始
		mysqli_query($conn,"UPDATE status SET LAST_MODIFIED=".time().",MATCH_ON=1,POSTID=".$postid." WHERE Activity='FMC'");
		mysqli_query($conn,"COMMIT");
		mysqli_close($conn);
	//在logs中写入比赛开始信息
	writeLog("Start round");
}
else{
	echo("没有权限");
}
?>
