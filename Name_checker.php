<?php
include_once "method/FML.php";
//这个函数得用guest账号，因为未来可能需要对大家开放check姓名的功能
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
$sign=0;
//这个版本是为提交直播帖页面写的，所以要检查16支球队的拼写是否正确。仍然是把首发的字符串打成array然后遍历
for($i=1;$i<=16;$i++){
	if(empty($_POST["team".($i)]))
break;
	$team=mysqli_real_escape_string($conn,$_POST["team".($i)]);
	$squad=mysqli_real_escape_string($conn,$_POST["squad".($i)]);
	if(strlen($squad)==0)
		continue;
	$array_total=explode("/",$squad);
	$array_num=array(0,0,0,0);
	for ($j=0;$j<count($array_total);$j++) {
		$array=explode(" ", trim($array_total[$j]));
		$array_num[$j]=count($array);
		foreach ($array as $value) {
			$query=mysqli_query($conn,"SELECT * FROM current WHERE Name='".$value."' AND Team='".$team."'");
			if(mysqli_num_rows($query)==0){
				echo("<script> alert('".$team."的球员".$value."可能拼写错误！'); </script>");
				$sign=1;//这里不能跳出，得提示所有的拼写错误
			}
		}
	}
	if($array_num[0]!=1)
	echo("<script> alert('".$team."门将数量错误！'); </script>");
	if($array_num[3]>3)
	echo("<script> alert('".$team."上了超过3名前锋！'); </script>");
	if($array_num[2]+$array_num[3]>7)
	echo("<script> alert('".$team."上了超过7名中前场球员！'); </script>");
}
if($sign==0){
	echo("<script> alert('没有发现拼写错误。'); </script>");
}
echo("<script> history.back(); </script>");
mysqli_close($conn);
?>
