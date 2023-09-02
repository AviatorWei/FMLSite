<?php
include_once "method/FML.php";
//用RSA解密，再与数据库储存的md5形式的密码对比
$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$str=mysqli_real_escape_string($conn,file_get_contents("php://input"));
$arr=explode("&",$str);
$arr1=explode("=",$arr[0]);
$arr2=explode("=",$arr[1]);
$username=$arr1[1];
$password=$arr2[1];
//$username=$_POST["user"];
//$password=$_POST['password'];
$password=base64_decode(str_replace(" ", "+", $password));
$query=mysqli_query($conn,"SELECT password FROM users WHERE username='".$username."'");
if(mysqli_num_rows($query)!=1){
	echo("用户名不存在");
	return;
}
$password_sql=mysqli_fetch_assoc($query)['password'];
$privatekey='-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAouq4laoTiTs0IWMeJ9t7C9Hxt6bIefYHyFUNzQN0nS/kPo3m
J3a5D/2/qfomCo7g3JCdUhD0z2NhDIJuKHgSvkRK8LpbT2sWy9lGH987M7HbRc0H
6Lw4refxXEPigkHbNIytMoC5bHKDQwOG2bkawI+0oOVv9vWXUfCZmsfJH4khn8Cd
n44oYCIKEUPupXq7B1lStaAASjUZVLv53yrWso02XRfxylW8kTFWFG226OeMxucn
RqB/2Ugv4uk8KwBmdXmDG/YdH6zbsAX6yQxCvPQOytskA7YjAY2uaN4+CKA/xtTt
rpVIlW6BVLCmVX6p9msbv5ROKEYxAYipSmIujwIDAQABAoIBAB9U/QLrnq450KDL
KzWHJHb1fYNQdCXgh0aj9O+ExpykZUUSjTzxvE2zA3VxQnetWtmQhnEOCccI2dVK
EF2AGjq1b6Q0cvMnKU8zDiV7DSu3/O/Dqs27xTrM7MJN2/YpLDtDAtw/nWmZ7Fyr
BPhCfT6jZJ29AneAbAW6PBBkc+24Z00ZXUWPG0vI8IPnfmxsanlf/YBNW7Sracl9
IIbO/4Q+CMJpHjzOxi8swq9dvXquZjlmgjeeCvxABKrFLMFP2jjMjYX2HtipIowW
sOZT2XrTc/9jKrdrLPEhaKLXE9p8KZdK7Z0ZgucxP+sKjXbH5KuYDUwY0sb7Wglh
ZLIQuKECgYEAzZbWZSDy8M1amvfYMqIpzHBMrHwYTS3dtIp51yCgvacKilp4mvN2
wCSNBpyfek2XJ1CoalzPXFtwSGiSMlztOmMLNntOkvJGgjuzpPOedxC4rxAVIZYg
6W7KlcTsfXBkUNOVFIMG9oHkwp8RWnx6qA7WgluKbutlpnewhQRLOFsCgYEAyt1F
+nIrFTrJdYAc9i90ig4FmpnT0z3+i0gL0uH4tHryHk/EIS3c5Oe/fUl85O+RDFdb
gY/f30f/sfZxiJORHCKKxNFTrfMRI/iP0SQ4bBNPykOa/Rg8/I2DxXECmKwOrc20
QKyrpk/mOAA8P+C0zj5qaljyQLwP4sEu3kCeGN0CgYEAv0JpN/GcMStJ9EtIZWp5
aki6++gCj6Jw0+nTibA/+c+xvZ6G/tgqjHw0eocw0g3m5dtionivLGOgpKwr/hB3
Xc8QemHialBcCgJSkM1XibsTpUFX90P8YE4Cx6xIujXaDVuZIFj5HFEhKXHkKgMN
9NTA+MsCkUgK7AADRuwEsmsCgYBsMOaJUyo3AEtBJzpK8bQtjJzgfvnBx2Zh0eK8
bVe+qXNHRzNKnrMYQaCWqQK69ildc5mR17GF21g7CTwzor/ZSXLI1PrT4rZZgM7y
s2aD/SiorrWSFcMwzihgvIyt79VETFi9xVkmMaaOZ1G6n5TOCeunHBjW/XVbcbwV
PFEU4QKBgGGCN4eaS6cNuiEGGx4ea4SGzfD9mQhSL71nayI/bl1mTE+QtHsK4FU1
7Z/ddMY/A6st6LoDlk0yGaYpxHG6NtEgdD1XWMpX56lG/NTTYkI6VCSIWSDyYBdY
BzWhYS5BHWKPOFIjO378VtY6zTv+zFa7h59CrpAzKDF2gjJaiUCk
-----END RSA PRIVATE KEY-----';
$privatekey=openssl_pkey_get_private($privatekey);
openssl_private_decrypt($password, $password_de, $privatekey);
if(md5($password_de)==$password_sql){
	setcookie('us_ern-ame',md5($username),time()+7200);
	echo("1");
}
else{
	echo("密码错误");
}
?>
