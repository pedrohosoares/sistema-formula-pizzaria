<?php
#Script só executa se
#o post do cupom (url) existir
#o post do cupom deve ser um link html com o cupom fiscal
#e se o cod_pedidos existir
#Ele salva em pdf o arquivo, nomeando-o para o cod_pedidos
if(empty($_POST['cupom']) or !isset($_POST['cupom'])){exit;}
if(empty($_POST['cod_pedidos']) or !isset($_POST['cod_pedidos'])){exit;}
if(!file_exists('cupons_fiscais')){
  mkdir('cupons_fiscais');
}
//Executa script para testes
//$arquivo = shell_exec('wkhtmltopdf '.$_POST['cupom'].' cupons_fiscais/'.$_POST['cod_pedidos'].'.pdf');
//Executa com retorno de sucesso ou fracasso
$comando = 'wkhtmltopdf '.$_POST['cupom'].' cupons_fiscais/'.$_POST['cod_pedidos'].'.pdf 2>&1';
//exec($comando,$resultado,$falha);
//$resultado = shell_exec($comando);
var_dump(basename(__DIR__));
