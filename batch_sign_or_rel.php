<?php
include_once "method/FML.php";
include_once "method/transfer.php";
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
if(checkCookie()){
    $data=$_POST['batchtext'];
    $array=explode("\r\n",trim($data));
    foreach ($array as $value) {
        $tmp=explode(" ",trim($value));
        $team=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM current WHERE Name='".$tmp[1]."'"))['Team'];
        if($team==$tmp[0])
        release($conn,$tmp[0],$tmp[1]);
        elseif($team=="")
        freesign($conn,$tmp[0],$tmp[1],10);
        else
        echo("请检查输入：".$tmp[0]." ".$tmp[1]."</br>");
    }
}
mysqli_close($conn);
?>