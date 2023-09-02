<?php
include_once "FML.php";
//include_once "printmatch.php";

function html2txtcore($str){
	$str=preg_replace("/<style[\s\S]*<\/style>/","",$str);
	$previous_str=array("&nbsp;","&ensp;","</div>","</p>","</h2>","</html>","</head>","</title>");
	$now_str=array(" "," ","</div>\n","</p>\n","</h2>\n","</html>\n","</head>\n","</title>\n");
	return strip_tags(str_replace($previous_str,$now_str,$str));
}

function html2txt($file,$txtroute){
	$text=html2txtcore(file_get_contents($file));
	$handle=fopen($txtroute,"w");
	fwrite($handle,$text);
	fclose($handle);
}

function editBBS($userid,$postid,$title,$file){
	//system("whoami",$ret);
	//echo($ret);
	//system("ls -l");
	//chdir("./method");
	//echo(getcwd());
	//system('./method/editbbs.sh',$ret);
	//print_r($ret);
	//system('echo $?');
	exec("./method/BDWM_cli.py edit --id=".$userid." --password-file='./method/".$userid."password' --board='Sports_Game' --postid=".$postid." --title='".$title."' --content-file='./".$file."'",$ret);
	//echo($ret);
	//system("pwd");
}


function editliveinBBS($conn,$match,$name){
	if($match==""){
		$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams"))['Round'];
		$match="FML";
	}
	else
		$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM teams".$match))['Round'];
	$htmlroute="History/".$match."live_".($round+1).".html";
	$txtroute="History/tmp".$match."live.txt";
	if($match=="FML")
		printFile("printBroadcast",$conn,$name,$htmlroute,"method/printmatch.php");
	else
		printFile("printBroadcast".$match,$conn,$name,$htmlroute,"method/printmatch.php");
	html2txt($htmlroute,$txtroute);
	$postid=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM status WHERE Activity='".$match."'"))['POSTID'];
	editBBS("PES",$postid,"[".$match."]".$name,$txtroute);
}

function postBBS($userid,$title,$file){
	exec("./method/BDWM_cli.py post --id=".$userid." --password-file='./method/".$userid."password' --board='Sports_Game' --title='".$title."' --content-file='".$file."'",$execret);
	// exec("./method/BDWM_cli.py post --id=".$userid." --password-file='./method/".$userid."password' --board='Sports_Game' --title='".$title."' --content-file='".$file."'",$execret);
	return substr($execret[0],strripos($execret[0],"=")+1);
}

function importcollectionBBS($title,$idarray){
	exec();
}

function postinBBS($userid,$name,$file,$tmpfile){
	html2txt($file,$tmpfile);
	return postBBS($userid,$name,$tmpfile);
}
?>