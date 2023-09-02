<?php
include_once "method/FML.php";
include_once "./Classes/PHPExcel/IOFactory.php";

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

$file="./anbiao/MTRl1.xlsx";
    $filetype=PHPExcel_IOFactory::identify($file);
    $reader=PHPExcel_IOFactory::createReader($filetype);
    $excel=$reader->load($file);
    $sheet=$excel->getSheet(0);
    $maxcol='G';
    $maxrow=$sheet->getHighestRow();

    $teamanbiaoFML=array();
    $ordersFML=array();

    for($row=2;$row<=$maxrow;$row++){
        $tmp=array();
        for($col='A';$col<=$maxcol;$col++){
            $cell=$col.$row;
            $value=$sheet->getCell($cell)->getValue();
            if($value=="0"){
            break;
            }
            array_push($tmp,$value);
        }
        if(count($tmp)>0){
            array_push($tmp,"AAA");
            if(in_array($tmp[3].$tmp[0],$ordersFML))
            $tmp[0]=23;
            array_push($ordersFML,$tmp[3].$tmp[0]);
            array_push($teamanbiaoFML,$tmp);
        }
    }

    echo(" FML暗标");
    for($j=0;$j<count($teamanbiaoFML);$j++){
        print_team_array($teamanbiaoFML[$j]);
    }
    echo("<p></p>");
?>
