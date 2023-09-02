<?php
include_once "method/FML.php";
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$team=mysqli_real_escape_string($conn,$_GET["team"]);
$lineup=mysqli_real_escape_string($conn,$_GET["lineup"]);
$pk=mysqli_real_escape_string($conn,$_GET["pk"]);

if($lineup!="")
mysqli_query($conn,"UPDATE teamsFMC SET Lineup='".$lineup."',isOldLineup=0 WHERE Abbr='".$team."'");
mysqli_query($conn,"UPDATE currentFMC SET infirstteam=0 WHERE Team='".$team."'");
$array=explode(' ',str_replace("\"","",str_replace("/", " ", strtolower($lineup))));
			foreach ($array as $key => $value) {
				$value=str_replace("'","\'",$value);
				mysqli_query($conn,"UPDATE currentFMC SET infirstteam=1 WHERE Name='".$value."'");
			}
if($pk!="")
mysqli_query($conn,"UPDATE teamsFMC SET pkturn='".$pk."' WHERE Abbr='".$team."'");

echo("已更改");
mysqli_close($conn);
?>
