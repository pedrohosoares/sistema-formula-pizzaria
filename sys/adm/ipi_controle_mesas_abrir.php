<?php
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
cabecalho('Controle de Mesas');
?>

<br><br>
<center>
Abrir o sistema de <strong>Controle de Mesas</strong>, <? echo NOME_SITE; ?>, clique no botão abaixo:
<br><br>
<form method="post" action="ipi_controle_mesas.php" name="frmPedido" target="_blank">
<input type="submit" name="bt_abrir_controle_mesas" value="Abrir Controle Mesas" class="botao">
</form>
</center>

<?
rodape(); 
?>