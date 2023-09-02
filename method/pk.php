<?php
include_once "FML.php";

function PK($conn,$array1,$array2){
	$point1=0;
	$point2=0;
	$i=0;
	for(;$i<5;$i++){
		$point1+=PK_calculate($conn,$array1[$i]);
		$point2+=PK_calculate($conn,$array2[$i]);
	}
	while($i<count($array1) && $i<count($array2)){
		if($point1>$point2)
		return true;
		elseif($point1<$point2)
		return false;
		else{
			$i=$i+1;
			$point1+=PK_calculate($conn,$array1[$i]);
			$point2+=PK_calculate($conn,$array2[$i]);
		}
	}
	if($i==count($array2))
	return true;
	return false;
}
function PK_calculate($conn,$player){
	global $valid_FMC_clubs;
	$player1=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM currentFMC WHERE Name='".$player."'"));
	if($player1['infirstteam']==0)
		return 0;
	$point=$player1['tmpGoal']*2+$player1['tmpassist']-$player1['tmpyellow']*0.3-$player1['tmpred'];
	if(in_array($player1['Club'],$valid_FMC_clubs))
	$point=$point+0.5;
	return $point;
}
?>
