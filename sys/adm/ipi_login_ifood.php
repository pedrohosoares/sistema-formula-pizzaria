<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';

header('Content-Type: text/html; charset=utf-8');

cabecalho('Login no Ifood');

$acao = validaVarPost('acao');
$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;

$notas_ativas = nota_ativa();
foreach($notas_ativas as $i=>$v){
	if($v['nota_ativa'] == 3){

		echo "<pre>";
		var_dump($v['nota_ativa']).'<br />';
		echo CAMINHO_DESPACHO_PEDIDOS_NOTA_FISCAL;
		echo "</pre>";		

	}
}
?>
<br>
<? rodape(); ?>
