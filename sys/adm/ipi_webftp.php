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
cabecalho('Web FTP');
?>

<br /><br />
<center>
Abrir o FTP, Os Muzzarellas, clique no botão abaixo:
<br /><br />

<form method="post" action="../../ftp" name="frmFTP" target="_blank">
<input type="submit" name="bt_abrir_ftp" value="Abrir FTP" class="botao">
</form>

</center>

<?
rodape(); 
?>
