<?php
if(isset($_GET['acao']) and $_GET['acao'] == 'confirmar'){
	$ids = $_REQUEST['ids'];
	$ids = explode(',', $ids);
	require_once '../../bd.php';
	require_once '../lib/php/sessao.php';
	var_dump($_GET);
	//$con = conectabd();
	//$usuarios = $_SESSION['usuario']['cod_pizzarias'];
	var_dump($usuarios);
	/*$ids = array_merge($ids,$usuarios);
	$ids = array_unique($ids);
	$ids = implode(',', $ids);
	$sql = "UPDATE ipi_mensagens SET visualizados='".$ids."' WHERE status='1';";
	mysql_query($sql);
	desconectabd($con);
	*/
}