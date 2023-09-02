<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>FML site</title>
	<script>var flag=0;</script>
</head>
<body>
	<!--a href="readme.html">页面使用帮助</a-->	
	<span id="welcome" style='float:right'>欢迎，admin	<input type="button" value="退出" onclick="logout()"></span>
	<span id="login" style='float:right'><a href='login.php'>登录</a></span>
	<?php
	include_once "method/FML.php";
	$conn=mysqli_connect($db_ip,$db_guest_username,$db_guest_password,$db_name,$db_port,$db_sock);
	if(!$conn){
		die('Could not connect: ' . mysqli_error($conn));
	}
	if(checkCookie()){
		echo("<script> flag=1;</script>");
	}
	$result=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM status WHERE Activity='FML'"));
	if($result['MATCH_ON']==1){
		$stamp=$result['LAST_MODIFIED'];
		$player=$result['LAST_SCORED_PLAYER'];
		$time=date('Y-m-d H:i:s',$stamp+8*3600);
		echo("<span style='float:middle;'>FML比赛正在进行，最后更新于");
		echo($time);
		echo("，进球者");
		printSingleName($conn,$player);
		echo("</span>");
	}
	else{
		echo("<span text-align=center;>FML比赛已结束</span>");
	}
	echo(" ");
	$result=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM status WHERE Activity='FMC'"));
	if($result['MATCH_ON']==1){
		$stamp=$result['LAST_MODIFIED'];
		$player=$result['LAST_SCORED_PLAYER'];
		$time=date('Y-m-d H:i:s',$stamp+8*3600);
		echo("<span style='float:middle;'>FMC比赛正在进行，最后更新于");
		echo($time);
		echo("，进球者");
		printSingleName($conn,$player);
		echo("</span>");
	}
	else{
		echo("<span text-align=center;>FMC比赛已结束</span>");
	}
	mysqli_close($conn);
	?>
	<span>查看第<input type="text" oninput="this.value=this.value.replace(/[^0-9]/g,'');" id="round">轮<select id="matchType"><option value="FML">FML</option><option value="FMC">FMC</option></select><select id="historyType"><option value="live_">直播帖</option><option value="league_table_">积分榜</option><option value="top_goalscorers_">射手榜</option></select><input type="button" value="查看" onclick="showHistory(document.getElementById('round').value,document.getElementById('matchType').options[document.getElementById('matchType').selectedIndex].value,document.getElementById('historyType').options[document.getElementById('historyType').selectedIndex].value)"></span>
	<div id="admin">
	<h2>暗标管理</h2>
	<p><span><a href="#" onclick="anbiaohandler(document.getElementById('anbiaotime').value,document.getElementById('turnsFML').value,document.getElementById('turnsFMC').value)">开标</a><input type='text' placeholder='暗标轮次，如l1,c1' id='anbiaotime'>，<input type='text' size='100' placeholder='联赛暗标顺序：先投标的放前面，球队间以空格隔开，没有联赛暗标请空着' id='turnsFML'>，<input type='text' size='100' placeholder='欧冠暗标顺序：先投标的放前面，球队间以空格隔开，没有欧冠暗标请空着' id='turnsFMC'></span></p>
	<h2>FML转会窗管理</h2>
	<p>转会请在此进行：<input type='text' id='Team1' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');" placeholder='请输入第一支球队'><input type='text' size='30' id='Player1' oninput="this.value=this.value.replace(/[^a-zA-Z,-.\']/g,'');" placeholder='请输入相应球员名，用“,”隔开'><input type='text' id='Team2' oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" placeholder='请输入第二支球队'><input type='text' id='Player2' size='30' oninput="this.value=this.value.replace(/[^a-zA-Z,-.\']/g,'');" placeholder='请输入相应球员名，用“,”隔开'><input type='text' id='transfermoney' size='25' oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder='请输入金额,无资金交换填0'><input type='button' value='提交' onclick="Transfer('FML',document.getElementById('Player1').value,document.getElementById('Team1').value,document.getElementById('Player2').value,document.getElementById('Team2').value,document.getElementById('transfermoney').value)">（其中第一支球队付出资金）</p>
	<p>自由签请在此进行：<input type='text' id='Teamfreesign' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');"  placeholder='请输入球队'><input type='text' id='Playerfreesign' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" placeholder='请输入相应球员名'><input type='text' id='PlayerMoney' oninput="this.value=this.value.replace(/[^0-9]/g,'');" value='10'><input type='button' value='提交' onclick="Playerfreesign('FML',document.getElementById('Playerfreesign').value,document.getElementById('Teamfreesign').value,document.getElementById('PlayerMoney').value)"></p>
	<p>解约请在此进行：<input type='text' id='Teamrelease' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');" placeholder='请输入球队'><input type='text' id='Playerrelease' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" placeholder='请输入相应球员名'><input type='button' value='提交' onclick="Playerrelease('FML',document.getElementById('Playerrelease').value,document.getElementById('Teamrelease').value)"></p>
	<p><form action="" method="POST">批量自由签/解约：<textarea cols='20' rows='5' name="batchtext" oninput="this.value=this.value.replace(/[^a-zA-Z-.\' \n\r']/g,'');" placeholder='请输入所有FML球员自由签及解约，格式为每个变动一行，每行内容为“球队三字母缩写 球员名”（中间空一格，内容两边无引号）'></textarea>
	<input type="submit" name="batch_FML" value="批量提交" formaction="batch_sign_or_rel.php"></form></p>
	<h2>FMC转会窗管理</h2>
	<p>转会请在此进行：<input type='text' id='Team1FMC' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');" placeholder='请输入第一支球队'><input type='text' size='30' id='Player1FMC' oninput="this.value=this.value.replace(/[^a-zA-Z,-.\']/g,'');" placeholder='请输入相应球员名，用“,”隔开'><input type='text' id='Team2FMC' oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" placeholder='请输入第二支球队'><input type='text' id='Player2FMC' size='30' oninput="this.value=this.value.replace(/[^a-zA-Z,-.\']/g,'');" placeholder='请输入相应球员名，用“,”隔开'><input type='text' id='transfermoneyFMC' size='25' oninput="this.value=this.value.replace(/[^0-9]/g,'');" placeholder='请输入金额,无资金交换填0'><input type='button' value='提交' onclick="Transfer('FMC',document.getElementById('Player1FMC').value,document.getElementById('Team1FMC').value,document.getElementById('Player2FMC').value,document.getElementById('Team2FMC').value,document.getElementById('transfermoneyFMC').value)">（其中第一支球队付出资金）</p>
	<p>自由签请在此进行：<input type='text' id='TeamfreesignFMC' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');"  placeholder='请输入球队'><input type='text' id='PlayerfreesignFMC' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" placeholder='请输入相应球员名'><input type='text' id='PlayerMoneyFMC' oninput="this.value=this.value.replace(/[^0-9]/g,'');" value='10'><input type='button' value='提交' onclick="Playerfreesign('FMC',document.getElementById('PlayerfreesignFMC').value,document.getElementById('TeamfreesignFMC').value,document.getElementById('PlayerMoneyFMC').value)"></p>
	<p>解约请在此进行：<input type='text' id='TeamreleaseFMC' oninput="this.value=this.value.replace(/[^a-zA-Z']/g,'');" placeholder='请输入球队'><input type='text' id='PlayerreleaseFMC' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" placeholder='请输入相应球员名'><input type='button' value='提交' onclick="Playerrelease('FMC',document.getElementById('PlayerreleaseFMC').value,document.getElementById('TeamreleaseFMC').value)"></p>
	<p><form action="" method="POST">批量自由签/解约：<textarea cols='20' rows='5' name="batchtext_FMC" oninput="this.value=this.value.replace(/[^a-zA-Z-.\' \n\r']/g,'');" placeholder='请输入FMC球员自由签及解约，格式为每个变动一行，每行内容为“球队三字母缩写 球员名”（中间空一格，内容两边无引号）'></textarea>
	<input type="submit" name="batch_FMC" value="批量提交" formaction="batch_sign_or_rel_FMC.php"></form></p>
	<h2>FML比赛管理</h2>
	<p><span><input type='button' value='生成FML直播帖' onclick="(function(){if(confirm('确认？')) window.open('broadcast_creator.php');})()">		
	<p><b>请在本轮所有比赛结束后执行！执行之前请确认已录入所有进球球员！此操作无法撤回！</b><span><input type='button' value='结束当前比赛' onclick="(function(){if(confirm('确认？')) window.open('submit_round.php');})()"></span></p>
	<h2>FMC比赛管理</h2>
	<p><div><input type='button' value='生成FMC直播帖' onclick="(function(){if(confirm('确认？')) window.open('broadcast_creator_FMC.php');})()"></div>	
	<div>更改FMC首发：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z]/g,'');" placeholder='请输入球队' id='changedteam'><input type='text' size='50' oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" placeholder='请输入修改后的首发' id='changedlineup'><input type='text' size='50' oninput="this.value=this.value.replace(/[^a-zA-Z0-9- \/.\']/g,'');" placeholder='请输入更改后的PK，若是首回合，请空着' id='changedpk'><input type='button' value='提交' onclick="changeFMClineup(document.getElementById('changedteam').value,document.getElementById('changedlineup').value,document.getElementById('changedpk').value)"></div>
	<div>撤销助攻球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" id='undoassistPlayer'><input type='button' value='撤销' onclick="undoPlayer('FMC','assist','助攻',document.getElementById('undoassistPlayer').value)"></div>
	<div>撤销黄牌球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" id='undoyellowPlayer'><input type='button' value='撤销' onclick="undoPlayer('FMC','yellow','黄牌',document.getElementById('undoyellowPlayer').value)"></div>
	<div>撤销红牌球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" id='undoredPlayer'><input type='button' value='撤销' onclick="undoPlayer('FMC','red','红牌',document.getElementById('undoredPlayer').value)"></div></p>
	<div>撤销失点球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" id='undomisspenPlayer'><input type='button' value='撤销' onclick="undoPlayer('FMC','misspen','失点',document.getElementById('undomisspenPlayer').value)"></div>
	<p><b>请在本轮所有比赛结束后执行！执行之前请确认已录入所有进球球员！此操作无法撤回！</b><span><input type='button' value='结束当前比赛' onclick="(function(){if(confirm('确认？')) window.open('submit_round_FMC.php');})()"></span></p>
	<p><a href='draw.php'>抽签测试</a></p>
	<input type="button" value="测试暗标" onclick="window.open('test_anbiao.php')"-->
	<h2>导出数据库</h2>
	<input type='button' value='导出FML球员数据库' onclick="window.open('export_player_database.php')"><input type='button' value='导出FML球队数据库' onclick="window.open('export_team_database.php')">
	<input type='button' value='导出FMC球员数据库' onclick="window.open('export_player_database_FMC.php')"><input type='button' value='导出FMC球队数据库' onclick="window.open('export_team_database_FMC.php')">
</div>
	<!--h1>最新放送</h1-->	
	<h2>实时赛况</h2>
	<p>	提交FML进球球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='scoredPlayerFML' placeholder='输入格式：球员+当前进球数，如Messi2' size='40'><input type='button' value='提交' onclick="submitPlayer('FML','scored','进球',document.getElementById('scoredPlayerFML').value)">
	<p>撤销进球球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" id='undoscoredPlayerFML'><input type='button' value='撤销' onclick="undoPlayer('FML','scored','进球',document.getElementById('undoscoredPlayerFML').value)"></span></p>
	<div><span><a href="broadcast_real_time.php">查看FML实时直播帖</a></span>		<span><a href="league_table_real_time.php">查看FML实时积分榜</a></span>		<span><a href="#" onclick="gettopGoalscorers('FML',document.getElementById('firstteamgoalnum').value,document.getElementById('subgoalnum').value,document.getElementById('playerPos').options[document.getElementById('playerPos').selectedIndex].value)">查看FML实时射手榜</a>（显示一线队进球超过<input type='number' value='0' id='firstteamgoalnum'>个，预备队进球超过<input type='number' value='0' id='subgoalnum'>个的<select id="playerPos"><option value="all">所有</option><option value="F">F</option><option value="M">M</option><option value="D">D</option><option value="G">G</option></select>球员）</span></div></p>
	<p>	提交FMC进球球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='scoredPlayerFMC' placeholder='输入格式：球员+当前进球数，如Messi2' size='40'><input type='button' value='提交' onclick="submitPlayer('FMC','scored','进球',document.getElementById('scoredPlayerFMC').value)">	
	<div>撤销进球球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z-.\']/g,'');" id='undoscoredPlayerFMC'><input type='button' value='撤销' onclick="undoPlayer('FMC','scored','进球',document.getElementById('undoscoredPlayerFMC').value)"></div>
	<div>提交FMC助攻球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='assistPlayer' placeholder='输入格式：球员+当前助攻数，如Messi2' size='40'><input type='button' value='提交' onclick="submitPlayer('FMC','assist','助攻',document.getElementById('assistPlayer').value)"></div>
	<div>提交FMC黄牌球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='yellowPlayer' placeholder='输入格式：球员+当前黄牌数，如Messi2' size='40'><input type='button' value='提交' onclick="submitPlayer('FMC','yellow','黄牌',document.getElementById('yellowPlayer').value)"></div>
	<div>提交FMC红牌球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='redPlayer' size='40'><input type='button' value='提交' onclick="submitPlayer('FMC','red','红牌',document.getElementById('redPlayer').value)"></div>
	<div>提交FMC失点球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='misspenPlayer' placeholder='输入格式：球员+当前失点数，如Messi2' size='40'><input type='button' value='提交' onclick="submitPlayer('FMC','misspen','失点',document.getElementById('misspenPlayer').value)"></div>
	<div>提交FMC未出场球员：<input type='text' oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" id='benchPlayer' size='40'><input type='button' value='提交' onclick="submitPlayer('FMC','bench','未出场',document.getElementById('benchPlayer').value)"></div>
	<span><a href="broadcast_real_time_FMC.php">查看FMC实时直播帖</a></span>		<span><a href="league_table_real_time_FMC.php">查看FMC实时积分榜</a></span>		<span><a href="#" onclick="gettopGoalscorers('FMC',document.getElementById('firstteamgoalnumFMC').value,document.getElementById('firstteamgoalnumFMC').value,document.getElementById('playerPosFMC').options[document.getElementById('playerPosFMC').selectedIndex].value)">查看FMC射手榜</a>（显示进球超过<input type='number' value='0' id='firstteamgoalnumFMC'>个的<select id="playerPosFMC"><option value="all">所有</option><option value="F">F</option><option value="M">M</option><option value="D">D</option><option value="G">G</option></select>球员）</span></p>
	<h2>查询</h2>
	<div>按<select id="searchType"><option value="Name">球员名</option><option value="KeyinFML">球员编号</option><option value="Club">球队名</option><option value="Team">FML球队名</option></select>查询：			<input size="100" type="text" id="searchName" oninput="this.value=this.value.replace(/[^a-zA-Z0-9-.\']/g,'');" placeholder="请输入球员名、球队名、编号等..."> <input type="button" onclick="getName(document.getElementById('searchType').options[document.getElementById('searchType').selectedIndex].value,document.getElementById('searchName').value)" value="查询"></div>
	<div id="showresult"></div>
	<div id="testresult"></div>
	<!--h2>精华文章</h2>
	<p>待添加</p-->
	<!--h2>玩家介绍</h2>
	<p>看大家有没有兴趣往这里面加内容吧……</p-->
	<h2>导出文件</h2>
	
	<input type="button" value="导出FML球员名单" onclick="window.open('export_current.php')"><input type="button" value="导出FMC球员名单" onclick="window.open('export_currentFMC.php')"><input type="button" value="查看玩家FML阵容" onclick="window.open('export_the_list.php')"><input type="button" value="查看玩家FMC阵容" onclick="window.open('export_the_list_FMC.php')">
	<h2>常用链接</h2>
	<a href="http://www.footballsquads.co.uk/squads.htm">Footballsquads</a>				<a href="https://www.whoscored.com/">Whoscored</a>				<a href="https://www.transfermarkt.co.uk/">Transfermarkt</a>				<a href="https://www.betinf.com">Sports Betting Information</a>				<a href="https://fantasy.premierleague.com/">英超Fantasy</a>
<script type="text/javascript">
	function showHistory(round,match,type){
		if(round=="")
			alert("请输入轮次");
		else{
			if(match=="FML")
				window.open("History/"+type+round+".html");
			else
				window.open("History/"+match+"_"+type+round+".html");
		}
	}
</script>
<script type="text/javascript">
        function gettopGoalscorers(activity,num1,num2,pos){
                if(num1=="" || num2=="")
                        alert("请输入数字");
                else{
				if(activity=="FML")
                window.location.href="shooters.php?num1="+num1+"&num2="+num2+"&pos="+pos;
				else if(activity=="FMC")
                window.location.href="shooters_FMC.php?num1="+num1+"&num2="+num2+"&pos="+pos;
				}
        }
</script>
<script type="text/javascript">
        function anbiaohandler(str,turns1,turns2){
			if(str.length==1)
                window.open("anbiaohandler.php?suffix="+str+"&turns1="+turns1+"&turns2="+turns2);
			else if(str[0]=="l")
				window.open("anbiaohandler_FML.php?suffix="+str+"&turns="+turns1);
			else if(str[0]=="c")
				window.open("anbiaohandler_FMC.php?suffix="+str+"&turns="+turns2);
        }
</script>
<script type="text/javascript">
	function logout(){
		document.cookie="us_ern-ame=";
		window.location.href="index.php";
	}
</script>
<script type="text/javascript">
		if(flag==1){
			document.getElementById("login").style.display="none";
			document.getElementById("guest").style.display="none";
		}
		else{
			document.getElementById("welcome").style.display="none";
			document.getElementById("admin").style.display="none";
		}
	</script>
	<script type="text/javascript">
	function changeFMClineup(team,lineup,pk){
		if(team==""){
				alert("输入球队为空。");
			}
			else if(lineup==""){
				alert("输入首发为空。");
			}
		else if(confirm("确定更改"+team+"的首发吗？")){
			var http;
			if(window.XMLHttpRequest){
				http=new XMLHttpRequest();
			}
			else{
				http=new ActiveXObject("Microsoft.XMLHTTP");
			}
			http.onreadystatechange=function(){
				if(http.readyState==4 && http.status==200){
					alert(http.responseText);
				}
			}
			http.open("GET","change_lineup_FMC.php?team="+team+"&lineup="+lineup+"&pk="+pk,true);
			http.send();
			}
	}
</script>
<script type="text/javascript">
	function undoPlayer(activity,event,eventchn,str){
		if(str==""){
				alert("输入为空。");
			}
		else if(confirm("确定撤销"+str+"的"+eventchn+"吗？")){
			var http;
			if(window.XMLHttpRequest){
				http=new XMLHttpRequest();
			}
			else{
				http=new ActiveXObject("Microsoft.XMLHTTP");
			}
			http.onreadystatechange=function(){
				if(http.readyState==4 && http.status==200){
					alert(http.responseText);
				}
			}
			if(activity=="FML")
			http.open("GET","undo"+event+"Player.php?str="+str,true);
			else if(activity=="FMC")
			http.open("GET","undo"+event+"Player_FMC.php?str="+str,true);
			http.send();
			}
	}
</script>

<script type="text/javascript">
	function submitPlayer(activity,event,eventchn,str){
		if(str==""){
				alert("输入为空。");
			}
		else if(confirm("确定添加"+str+"的"+eventchn+"吗？")){
			var http;
			if(window.XMLHttpRequest){
				http=new XMLHttpRequest();
			}
			else{
				http=new ActiveXObject("Microsoft.XMLHTTP");
			}
			http.onreadystatechange=function(){
				if(http.readyState==4 && http.status==200){
					alert(http.responseText);
					//location.reload();
				}
			}
			if(activity=="FML")
			http.open("GET","submit"+event+"Player.php?str="+str,true);
			else if(activity=="FMC")
			http.open("GET","submit"+event+"Player_FMC.php?str="+str,true);
			http.send();
			}
	}
</script>
<script>
	function getName(type,str){
		var http;
		if(str==""){
			document.getElementById("showresult").innerHTML="输入为空。";
			return;
		}
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				document.getElementById("showresult").innerHTML=http.responseText;
			}
		}
		http.open("GET","getName.php?type="+type+"&str="+str,true);
		http.send();
	}
</script>
<script type="text/javascript">
	function Playerrelease(activity,player,team){
		if(confirm("确认 "+team+" 解约 "+player+" 吗？")){
		var http;
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				alert(http.responseText);
			}
		}
		if(activity=="FML")
		http.open("GET","release.php?player="+player+"&team="+team,true);
		else if(activity=="FMC")
		http.open("GET","release_FMC.php?player="+player+"&team="+team,true);
		http.send();
	}
	}
</script>
<script type="text/javascript">
	function Playerfreesign(activity,player,team,money){
		if(confirm("确认 "+team+" 自由签入 "+player+" 吗？")){
		var http;
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				alert(http.responseText);
			}
		}
		if(activity=="FML")
		http.open("GET","freesign.php?player="+player+"&team="+team+"&money="+money,true);
		else if(activity=="FMC")
		http.open("GET","freesign_FMC.php?player="+player+"&team="+team+"&money="+money,true);
		http.send();
	}
	}
</script>
<script type="text/javascript">
	function Transfer(activity,player1,team1,player2,team2,money){
		if(confirm("确认要转会吗？")){
		var http;
		if(window.XMLHttpRequest){
			http=new XMLHttpRequest();
		}
		else{
			http=new ActiveXObject("Microsoft.XMLHTTP");
		}
		http.onreadystatechange=function(){
			if(http.readyState==4 && http.status==200){
				alert(http.responseText);
			}
		}
		if(activity=="FML")
		http.open("GET","transfer.php?player1="+player1+"&team1="+team1+"&player2="+player2+"&team2="+team2+"&money="+money,true);
		else if(activity=="FMC")
		http.open("GET","transfer_FMC.php?player1="+player1+"&team1="+team1+"&player2="+player2+"&team2="+team2+"&money="+money,true);
		http.send();
	}
	}
</script>
</body>
</html>
