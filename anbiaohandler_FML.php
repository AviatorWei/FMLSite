<?php
include_once "method/FML.php";
include_once "./Classes/PHPExcel/IOFactory.php";
$conn=mysqli_connect($db_ip,$db_admin_username,$db_admin_password,$db_name,$db_port,$db_sock);
if(!$conn){
        die('Could not connect: ' . mysqli_error($conn));
}
$working_folder="./anbiao/";
$working_array_FML=array();
$working_array_release=array();
$position=array("G","D","M","F");
$suffix=$_GET['suffix'];
$teams=explode(' ',$_GET['turns']);

function my_array_search($str,$array){
    for($i=0;$i<count($array);$i++){
        if($array[$i]==$str)
        return $i;
    }
    return -1;
}

function print_team_array($array){
    echo("<div>");
    printWithFormat($array[7],4,0);
    printWithFormat($array[3],2,0);
    printWithFormat($array[0],3,0);
    printWithFormat($array[6],7,0);
    printWithFormat($array[2],19,0);    
    printWithFormat($array[4],20,0);
    printWithFormat($array[1],3,0);
    echo("</div>");
}

//根据球员价格-位置-顺位排序
function sortbyprice($array1,$array2){
    global $position;
    if($array1[1]<$array2[1])
    return -1;
    elseif($array1[1]>$array2[1])
    return 1;
    else{
        if(my_array_search($array1[3],$position)<my_array_search($array2[3],$position))
        return -1;
        elseif(my_array_search($array1[3],$position)>my_array_search($array2[3],$position))
        return 1;
        else{
            if($array1[0]<$array2[0])
            return 1;
            elseif($array1[0]>$array2[0])
            return -1;
        }
    }
}

//根据球员顺位排序
function sortbyorder($array1,$array2){
    if($array1[0]<$array2[0])
        return 1;
    else
        return -1;
}

function checkrelease($array,$team){
    global $conn;
    $r=array();
    for($i=0;$i<count($array);$i++){
        if(mysqli_fetch_assoc(mysqli_query($conn,"SELECT Team FROM current WHERE Name='".$array[$i][2]."'"))['Team']==$team)
        array_push($r,$array[$i]);
    }
    return $r;
}

function checkanbiao($array,$releasearray,$str=""){
    global $conn;
    $releasenum=count($releasearray);
    $r=array();
    if(count($array)==0)
    return $r;
    $team=$array[0][7];
    $gksign=10;
    $releasegknum=0;
    for($i=0;$i<$releasenum;$i++){
        if($releasearray[$i][3]=='G')
        $releasegknum+=1;
    }
    if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current".$str." WHERE Pos='G' AND Team='".$team."'"))==$releasegknum)
    $gksign=0;
    $pricesum=0;
    $money=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Money FROM teams".$str." WHERE Abbr='".$team."'"))['Money'];
    $playernum=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current".$str." WHERE Team='".$team."'"));
    $p=0;
    //需要检查的情况：球员名被改了，球队已经签约过该球员，该球员已经被签约过三次，没有足够的钱签门将/凑足人数下限，钱爆了，人爆了
    for(;$p<count($array);$p++){
        if($array[$p][3]=="G")
        $gksign=10;
    if(count($r)+$playernum-$releasenum>=22){
        echo("<div>".$team."从".$array[$p][2]."开始人数超标</div>");
    break;
    }
    if($pricesum+$array[$p][1]>$money-10+$gksign){
        echo("<div>".$team."从".$array[$p][2]."开始没有足够的钱签门将</div>");
    break;
    }
    if($pricesum+$array[$p][1]+(8-(count($r)+$playernum-$releasenum))*10>$money){
        echo("<div>".$team."从".$array[$p][2]."开始没有足够的钱凑足人数下限</div>");
    break;
    }
    //情况：这个编号不存在或已经有主；这个球员已经效力过3个球队；这个球员被这个球队买过；
    if($str==""){
        if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE KeyinFML='".$array[$p][6]."' AND Team=''"))==0 || mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE KeyinFML='".$array[$p][6]."' AND OwnerNum<3"))==0 || mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE KeyinFML='".$array[$p][6]."' AND (Owner1='".$team."' OR Owner2='".$team."' OR Owner3='".$team."')"))>0){
            echo("<div>".$array[$p][2]."不能签约</div>");
            continue;
        }
    }
    elseif($str=="FMC"){
        if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM currentFMC WHERE KeyinFML='".$array[$p][6]."' AND Team=''"))==0 || in_array($array[$p][7],explode(" ",mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM currentFMC WHERE KeyinFML='".$array[$p][6]."'"))["Owners"]))){
            echo("<div>".$array[$p][2]."不能签约</div>");
            continue;
        }
    }
    if($array[$p][1]<10){
        echo("<div>".$array[$p][2]."出价小于10m</div>");
        continue;
    }
        $pricesum+=$array[$p][1];
        array_push($r,$array[$p]);
    }
    return $r;
}

    ob_start();
    echo("
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <title>球员暗标结果</title>
    </head>
    <body>");
    //读取FML暗标
for($i=0;$i<16;$i++){
    $file=$working_folder.strtoupper($teams[$i]).$suffix.".xlsx";
    if(!file_exists($file)){
        continue;
    }
    $filetype=PHPExcel_IOFactory::identify($file);
    $reader=PHPExcel_IOFactory::createReader($filetype);
    $excel=$reader->load($file);
    $sheet=$excel->getSheet(0);
    $maxcol='G';
    $maxrow=$sheet->getHighestRow();

    $teamanbiaoFML=array();
    $release=array();
    $ordersFML=array();

    for($row=2;$row<=$maxrow;$row++){
        $tmp=array();
        for($col='A';$col<=$maxcol;$col++){
            $cell=$col.$row;
            $value=$sheet->getCell($cell)->getValue();
            if(empty($value)){
                if(count($tmp)==0)
                    break;
                else
                    array_push($tmp,0);
            }
            else
            {
                if(strpos($value, "_x000D_"))
                array_push($tmp,str_replace("_x000D_", "", $value));
                else
                array_push($tmp, trim($value));
            }
        }
        if(count($tmp)>0){
            array_push($tmp,$teams[$i]);
            if(in_array($tmp[3].$tmp[0],$ordersFML))
            $tmp[0]=23;
            array_push($ordersFML,$tmp[3].$tmp[0]);
            if($tmp[0]>0)
            array_push($teamanbiaoFML,$tmp);
            else
            array_push($release,$tmp);
        }
    }

    if(count($teamanbiaoFML)>0){
        //检查人员，钱数
        usort($teamanbiaoFML,"sortbyprice");
        usort($release,"sortbyorder");
        $release=checkrelease($release,$teams[$i]);
        $teamanbiaoFML=checkanbiao($teamanbiaoFML,$release);

        usort($teamanbiaoFML,"sortbyteam");
    }

    //输出每个队的暗标
    echo($teams[$i]." FML暗标");
    for($j=0;$j<count($teamanbiaoFML);$j++){
        print_team_array($teamanbiaoFML[$j]);
    }
    echo("<p></p>");
    echo("解约");
    for($j=0;$j<count($release);$j++){
        echo("<div>");
        echo($j+1);
        echo(" ");
        echo($release[$j][2]);
        echo("</div>");
    }
    echo("<p></p>");

    $working_array_FML=array_merge($working_array_FML,$teamanbiaoFML);
    if(count($release)>0){
    $working_array_release[$teams[$i]]=$release;
    }
}

echo("</body></html>");
$handle=fopen("History/team_anbiao_".$suffix.".html","w");
$ob=ob_get_contents();
fwrite($handle, $ob);
fclose($handle);
ob_end_clean();

//排序函数
function sortbykey_FML($array1,$array2){
    global $teams;
    if($array1[6]<$array2[6])
    return -1;
    elseif($array1[6]>$array2[6])
    return 1;
    else{
        if($array1[1]<$array2[1])
        return 1;
        elseif($array1[1]>$array2[1])
        return -1;
        else{
            if($array1[0]<$array2[0])
            return -1;
            elseif($array1[0]>$array2[0])
            return 1;
            else{
                if(my_array_search($array1[7],$teams)<my_array_search($array2[7],$teams))
                return -1;
                else
                return 1;
            }
        }
    }
}

//根据球队-位置-编号排序暗标
//打印用
function sortbyteam($array1,$array2){
    global $position;
    if($array1[7]<$array2[7])
    return -1;
    elseif($array1[7]>$array2[7])
    return 1;
    else{
        if(my_array_search($array1[3],$position)<my_array_search($array2[3],$position))
        return -1;
        elseif(my_array_search($array1[3],$position)>my_array_search($array2[3],$position))
        return 1;
        else{
            if($array1[0]<$array2[0])
            return -1;
            elseif($array1[0]>$array2[0])
            return 1;
        }
    }
}

function print_working_array($array){
    echo("<div>");
    printWithFormat($array[0],3,0);
    printWithFormat($array[1],4,0);
    printWithFormat($array[2],19,0);
    printWithFormat($array[3],2,0);    
    printWithFormat($array[4],20,0);
    printWithFormat($array[5],4,0);
    printWithFormat($array[6],7,0);
    printWithFormat($array[7],3,0);
    echo("</div>");
}

//根据球员姓名-价格-编号-发帖时间排序暗标
usort($working_array_FML,"sortbykey_FML");

//输出每个球员的暗标结果，并去除重复的球员
ob_start();
$result_array_FML=array();
$result_array_release=array();
$key="";
echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>球员暗标结果</title>
</head>
<body>");
for($i=0;$i<count($working_array_FML);$i++){
    if($key!=$working_array_FML[$i][6]){
        echo("<p></p>");
        $key=$working_array_FML[$i][6];
        array_push($result_array_FML,$working_array_FML[$i]);
    }
    print_working_array($working_array_FML[$i]);
}
echo("</body></html>");
$handle=fopen("History/player_result_".$suffix.".html","w");
$ob=ob_get_contents();
fwrite($handle, $ob);
fclose($handle);
ob_end_clean();

usort($result_array_FML,"sortbyteam");

//写入数据库
//FML
for($i=0;$i<count($result_array_FML);$i++){
    mysqli_query($conn,"UPDATE current SET Team='".$result_array_FML[$i][7]."',Price=".$result_array_FML[$i][1].",OwnerNum=OwnerNum+1 WHERE KeyinFML='".$result_array_FML[$i][6]."'");
	mysqli_query($conn,"UPDATE teams SET Money=Money-".$result_array_FML[$i][1]." WHERE Abbr='".$result_array_FML[$i][7]."'");//调整money
	$res=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Owner1,Owner2,Owner3 FROM current WHERE KeyinFML='".$result_array_FML[$i][6]."'"));
	if($res['Owner1']==""){
		mysqli_query($conn,"UPDATE current SET Owner1='".$result_array_FML[$i][7]."' WHERE KeyinFML='".$result_array_FML[$i][6]."'");
	}
	elseif($res['Owner2']==""){
		mysqli_query($conn,"UPDATE current SET Owner2='".$result_array_FML[$i][7]."' WHERE KeyinFML='".$result_array_FML[$i][6]."'");
	}
	elseif($res['Owner3']==""){
		mysqli_query($conn,"UPDATE current SET Owner3='".$result_array_FML[$i][7]."' WHERE KeyinFML='".$result_array_FML[$i][6]."'");
    }
    $player=mysqli_fetch_assoc(mysqli_query($conn,"SELECT Name FROM current WHERE KeyinFML='".$result_array_FML[$i][6]."'"))['Name'];
	//在日志中记录签约
    writeLog($result_array_FML[$i][7]." sign ".$player);
}

//解约球员
for($i=0;$i<count($teams);$i++){
    if(!array_key_exists($teams[$i],$working_array_release))
    continue;
    $team=$working_array_release[$teams[$i]][0][7];
    $teamrelease=array();
    $playernum=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Team='".$team."'"));
    $gknum=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM current WHERE Pos='G' AND Team='".$team."'"));
    $p=0;
    while($playernum>22 && $p<count($working_array_release[$teams[$i]])){
        if($working_array_release[$team][$p][3]=='G'){
            if($gknum==1){
                $p+=1;
                continue;
            }
            else
            $gknum-=1;
        }
        array_push($teamrelease,$working_array_release[$team][$p][2]);
        mysqli_query($conn,"UPDATE current SET Team='',Price=0 WHERE Name='".$working_array_release[$team][$p][2]."'");
        $p+=1;
        $playernum-=1;
    }
    /*if($playernum==23){
        mysqli_query($conn,"UPDATE current SET Team='',Price=0 WHERE Pos='G' AND Team='".$team."'");
    }*/
    if($playernum>22){
        echo($team."解约了门将但没签门将，请手动解决");
    }
    $result_array_release[$team]=$teamrelease;
}

function print_result_array($array){
    echo("<div>");
    printWithFormat($array[7],4,0);
    printWithFormat($array[3],2,0);
    printWithFormat($array[6],7,0);
    printWithFormat($array[2],19,0);    
    printWithFormat($array[4],20,0);
    printWithFormat($array[1],4,0);
    echo("</div>");
}

//打印暗标结果
ob_start();
echo("
<!DOCTYPE html>
<html>
<head>
	<meta charset='utf-8'>
	<title>FML暗标结果</title>
</head>
<body>
");
$nowteam=$result_array_FML[0][7];
for($i=0;$i<count($result_array_FML);$i++){
    if($nowteam!=$result_array_FML[$i][7]){
        $nowteam=$result_array_FML[$i][7];
        echo("<p></p>");
    }
    print_result_array($result_array_FML[$i]);
}
ksort($result_array_release);
foreach($result_array_release as $key => $val){
    echo("<p></p>");
    echo($key."解约");
    for($i=0;$i<count($val);$i++){
        echo("<div>".$val[$i]."</div>");
    }
}
echo("</body></html>");
$handle=fopen("History/team_result_".$suffix.".html","w");
$ob=ob_get_contents();
fwrite($handle, $ob);
fclose($handle);
ob_end_clean();

echo("
<!DOCTYPE html>
<html>
<head>
	<title>已完成导入</title>
</head>
<body>
	<p>球员投标情况已保存在<a href='History/player_result_".$suffix.".html'>链接</a></p>
	<p>球队暗标已保存在<a href='History/team_anbiao_".$suffix.".html'>链接</a></p>
	<p>暗标结果已保存在<a href='History/team_result_".$suffix.".html'>链接</a></p>
	<a href='index.php'>回到首页</a>
</body>
</html>");

mysqli_close($conn);
?>