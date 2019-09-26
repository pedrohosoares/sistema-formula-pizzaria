<?php
function filterGet($i){
	return preg_replace('/[^[:alnum:]_]/', '',$i);
}
$_GET['token'] = filterGet($_GET['token']);
$_GET['cod_pizzarias'] = filterGet($_GET['cod_pizzarias']);
$_GET['abrir'] = filterGet($_GET['abrir']);
if(empty($_GET['cod_pizzarias']) or empty($_GET['token']) or empty($_GET['abrir'])){
	exit;
}
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
header('Content-Type: text/html; charset=utf-8');
$con = conectabd();
if($_GET['abrir'] == 'sim'){
	$_GET['abrir'] =1;
}else{
	$_GET['abrir']=2;
}
$sql = "UPDATE ipi_pizzarias SET ifood_ligado='".$_GET['abrir']."' WHERE cod_pizzarias='".$_GET['cod_pizzarias']."'";
mysql_query($sql);
desconectabd($con);
