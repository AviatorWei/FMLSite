<?php
	//include_once "method/FML.php";
	include_once "method/printmatch.php";
	$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
	$round=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Round FROM teamsFMC WHERE isalive=1"))['Round'];
	//非比赛时间所有tmpcode都是0
	if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM teamsFMC WHERE tmpCode>0"))==0){
		echo("<script> location.href='History/FMC_live_".$round.".html'; </script>");
	}
	else{
		printBroadcastFMC($conn,"FMC实时直播贴");
}
	mysqli_close($conn);
	?>
