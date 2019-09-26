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

cabecalho('Expedir Pedidos');
?>

<br><br>
<center>
Abrir o sistema de expedição pedidos, <? echo NOME_SITE; ?>, clique no botão abaixo:
<br><br>
<form method="post" action="ipi_despacho_pedidos.php" name="frmPedido" target="_blank">
<input type="submit" name="bt_abrir_pdv" value="Abrir Expedição" class="botao">
</form>
</center>

<?
rodape(); 
?>