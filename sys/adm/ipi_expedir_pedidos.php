<?php
/**
 * ipi_clientes.php: Cadastro de Clientes
 * 
 * �ndice: cod_clientes
 * Tabela: ipi_clientes
 */
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Expedir Pedidos');
?>

<br><br>
<center>
Abrir o sistema de expedi��o pedidos, <? echo NOME_SITE; ?>, clique no bot�o abaixo:
<br><br>
<form method="post" action="ipi_despacho_pedidos.php" name="frmPedido" target="_blank">
<input type="submit" name="bt_abrir_pdv" value="Abrir Expedi��o" class="botao">
</form>
</center>

<?
rodape(); 
?>