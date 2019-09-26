<?php
/**
 * ipi_clientes.php: Cadastro de Clientes
 * 
 * Índice: cod_clientes
 * Tabela: ipi_clientes
 */
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once 'ipi_req_quant_vendidas.php';
cabecalho('Pedir Pizza');
?>

<br><br>
<center>
Abrir o sistema de pedidos, <? echo NOME_SITE; ?>, clique no botão abaixo:
<br><br>
<form method="post" action="ipi_caixa.php" name="frmPedido" target="_blank">
<input type="submit" name="bt_abrir_pdv" value="Abrir PDV" class="botao">
</form>
</center>

<?
rodape(); 
?>