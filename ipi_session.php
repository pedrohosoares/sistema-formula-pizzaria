<?php
session_start();
$pagina = str_replace('/', '', $_GET['pagina']);

if ( ($_SESSION['ipi_cliente']['autenticado'] != true) && ( ($pagina == "alterar_senha") || ($pagina == "meus_dados") || ($pagina == "meus_enderecos") || ($pagina == "meus_pontos") ||  ($pagina == "meu_home") || ($pagina == "repetir_pedido") || ($pagina == "meus_pedidos") || ($pagina == "usar_fidelidade") ) )
{
  header("Location: home&erro=2");
}
