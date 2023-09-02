<?php
include_once "FML.php";

//输出大名单
function printSquads($conn,$match){
    echo("<!DOCTYPE html><html><head><meta charset='utf-8'><title>最新".$match."大名单</title></head><body>");
    //格式：球队名3+空格1+位置1+空格1+(4+1*2)+1+18+1+19+1+3
    $teams=mysqli_query($conn,"SELECT Abbr,Money,Managers FROM teams".$match." ORDER BY Abbr");
    while($row=mysqli_fetch_assoc($teams)){//共16支球队，循环16次
        $team=$row['Abbr'];
        $query=mysqli_query($conn,"SELECT Team,Pos,KeyinFML,Name,Club,Price FROM current".$match." WHERE Team='".$team."' ORDER BY field(Pos,'G','D','M','F')");
        printOneSquadHeader($row,mysqli_num_rows($query));
        while($player=mysqli_fetch_assoc($query))
            printOneSquadPlayer($player);
        //两个球队之间空一行
        echo("<p></p>");
    }
    echo("</body></html>");
    }
    function printOneSquadHeader($team,$num){
        //第一行，格式：球队名3+空格1+（人数+“人”字+空格）39+（“剩余资金”+空格）9+资金3
        echo("<div><code>".$team['Abbr']." ");
        printWithFormat($num."人",42,0);
        echo("剩余资金 ");
        printWithFormat($team['Money'],3,1);
        //玩家ID占一行
        echo("</code></div><div><code>".$team['Managers']."</code></div>");
    }
    function printOneSquadPlayer($player){
        //输出该玩家拥有的球员，//格式：球队名3+空格1+位置1+空格1+游戏中编号4+“号”字2+空格1+（球员姓名+空格）19+（俱乐部名+空格）20+（价格+空格）3
        echo("<div><code>".$player['Team']." ".$player['Pos']." ");
        printWithFormat($player['KeyinFML'],6,1);
        echo("号 ");
        printWithFormat($player['Name'],19,0);
        printWithFormat($player['Club'],20,0);
        printWithFormat($player['Price'],3,1);
        echo("</code></div>");
    }
?>