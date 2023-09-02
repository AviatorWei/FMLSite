<?php
include_once "method/printsquads.php";
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
die('Could not connect: ' . mysqli_error($conn));
}
printSquads($conn,"FMC");
?>
