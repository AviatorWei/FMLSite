<?php
include_once "method/FML.php";
if(checkCookie()){
		echo("<script>window.open('index.php','_self');</script>");
	}
else{
	echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>FML site login</title>
</head>
<body>
	用户名：<input type='text' name='user' id='user'>
	密码：<input type='password' name='password' id='password'>
	<input type='button' value='登录' onclick=\"login_handler(document.getElementById('user').value,document.getElementById('password').value)\">
	<a href='index.php'>游客</a>
<script type='text/javascript' src='jsencrypt.min.js'></script>
<script type='text/javascript'>
	function login_handler(username,password){
		var encrypt=new JSEncrypt();
		var PUBLIC_KEY='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAouq4laoTiTs0IWMeJ9t7C9Hxt6bIefYHyFUNzQN0nS/kPo3mJ3a5D/2/qfomCo7g3JCdUhD0z2NhDIJuKHgSvkRK8LpbT2sWy9lGH987M7HbRc0H6Lw4refxXEPigkHbNIytMoC5bHKDQwOG2bkawI+0oOVv9vWXUfCZmsfJH4khn8Cdn44oYCIKEUPupXq7B1lStaAASjUZVLv53yrWso02XRfxylW8kTFWFG226OeMxucnRqB/2Ugv4uk8KwBmdXmDG/YdH6zbsAX6yQxCvPQOytskA7YjAY2uaN4+CKA/xtTtrpVIlW6BVLCmVX6p9msbv5ROKEYxAYipSmIujwIDAQAB';
		encrypt.setPublicKey('-----BEGIN PUBLIC KEY-----'+PUBLIC_KEY+'-----END PUBLIC KEY-----');
		password=encrypt.encrypt(password);
		var http=new XMLHttpRequest();
		http.onreadystatechange=function(){
				if(http.readyState==4 && http.status==200){
					if(http.responseText=='1'){
						/*var datenow=new Date();
						datenow.setTime(datenow.getTime()+1000*60*20);
						document.cookie='us_ern-ame='+username+';expires='+datenow.toGMTString();*/
						window.open('index.php','_self');
					}
					else
						alert(http.responseText);
				}
			}
		http.open('POST','login_handler.php',true);
		http.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		http.send('user='+username+'&password='+password);
	}
</script>
</body>
</html>
");
}
?>
