<?php
include_once "method/transfer.php";
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
if(checkCookie()){
$team=mysqli_real_escape_string($conn,strtoupper($_GET['team']));
$player=mysqli_real_escape_string($conn,$_GET['player']);
$money=mysqli_real_escape_string($conn,$_GET['money']);

freesignFMC($conn,$team,$player,$money);
edittransferinBBS($conn,"FMC");
mysqli_close($conn);
}
else{
	echo("没有权限");
	mysqli_close($conn);
}
?>
