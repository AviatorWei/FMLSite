<?php
$db_ip="127.0.0.1";
$db_admin_username="pes";
$db_admin_password="LRLDdoushiguanjun!1";
$db_guest_username="pes";
$db_guest_password="LRLDdoushiguanjun!1";
$db_name="fml";
$db_port="3306";
$db_sock="";

$methodpath="./method";
$rootpath=".";

$tmpfile="./History/tmpfile.txt";
$BBSFMLsquadid=23382765;
$BBSFMCsquadid=23382770;

$FMC_clubs_in_FML=array('ManCity', 'Liverpool', 'Chelsea', 'Tottenham', 'Barcelona', 'AtleticoMadrid', 'RealMadrid', 'Sevilla', 'Juventus', 'Milan', 'Napoli', 'Inter', 'BayernMunich', 'Dortmund', 'Leipzig', "Leverkusen", "Frankfurt");
$titlenamearray=array("1/4决赛首回合","1/4决赛次回合","半决赛首回合","半决赛次回合","决赛首回合","决赛次回合");
$valid_FMC_clubs=array('ManCity', 'RealMadrid', 'Milan', 'Inter');

//通用方法
function checkCookie(){
	if(isset($_COOKIE['us_ern-ame']) && $_COOKIE['us_ern-ame']==md5('admin'))
		return true;
	return false;
}
function check_stage($conn){
	if(mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM status WHERE Activity='FMC'"))["TRANSFER_STAGE"]<3)
	return true;
	return false;
}
function check_FMC_clubs($str){
	global $FMC_clubs_in_FML;
	if(in_array($str,$FMC_clubs_in_FML))
	return true;
	return false;
}
function printFile($func,$conn,$str,$filename,$inclde){
	include_once $inclde;
	ob_start();
	$func($conn,$str);
	$handle=fopen($filename,'w');
	$ob=ob_get_contents();
	fwrite($handle, $ob);
	fclose($handle);
	ob_end_clean();
}
function writeLog($str){
	$file=fopen("logs.txt", "a");
	fwrite($file,$str." at ".date('Y-m-d H:i:s',time()+8*3600)."\n");
	fclose($file);
}
function printSingleName($conn,$name){
	$name=str_replace("'","\'",$name);
	$query=mysqli_query($conn,"SELECT * FROM current WHERE Name='".$name."'");
	if(mysqli_num_rows($query)==0){
		$query=mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$name."'");
	}
	$array=mysqli_fetch_assoc($query);
	echo("<a href='https://www.transfermarkt.co.uk/");
	echo(str_replace(" ","-",str_replace(array('"',"'"),"",strtolower($array['tmName'])))."/profil/spieler/".$array['KeyinFML']."'>".$array['Name']."</a>");
}

function printWithFormat($str,$num,$sign){
	$len=strlen($str);
	if($len>$num){
		echo(substr($str,0,$num));
	}
	else{
		if($sign==0)//左对齐
		echo($str);
		for($i=$len;$i<$num;$i++)
			echo("&ensp;");
		if($sign==1)//右对齐
			echo($str);
	}
}

function inFirstTeam($player,$lineup){
	$array=explode(' ',str_replace("'","\\'",str_replace("\"","",str_replace("/", " ", strtolower($lineup)))));
	if(in_array(strtolower($player), $array))
		return true;
	return false;
}

function fmlmatchon($conn){
	if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teams WHERE tmpCode>0"))>0)
	return true;
	return false;
}
?>