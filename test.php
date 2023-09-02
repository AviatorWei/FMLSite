<?php
$str = "Neuer/Stones S.Ramos Kim/Kimmich Verratti J.MarioNaval Mount/Kane Taremi Dzeko";
$array = array_reverse(explode("/", strtolower(trim($str))));
echo(explode(" ", "".implode(" ", $array)));
?>
