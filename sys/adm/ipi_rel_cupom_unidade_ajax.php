<?php

/**
 * ipi_cupom.php: Cadastro de Cupom
 * 
 * Índice: cod_cupons
 * Tabela: ipi_cupons
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

$acao = validaVarPost('acao');


$tabela = 'ipi_cupons';
$chave_primaria = 'cod_cupons';
$quant_pagina = 70;

$cod_cupons = validaVarPost("c");
$cod_pizzarias = validaVarPost("p");
$situacao = validaVarPost("s");
$cliente = validaVarPost("cli");

if ($acao=='carregar_detalhes')
{
  $con = conectabd();
  
  $filtro_clientes =  "";
  $$filtro_situacao= "";

  if($cod_pizzarias=="")
    {
      $cod_pizzarias = implode(',', $_SESSION['usuario']['cod_pizzarias']);
    }

  if($cliente)
  {
    $arr_clientes = array();
    $sql_buscar_cods_clientes = "SELECT cod_clientes from ipi_clientes where nome like '%".$cliente."%'";
   // echo $sql_buscar_cods_clientes."<br/><br/>";
    $res_buscar_cods_clientes = mysql_query($sql_buscar_cods_clientes);
    $qtd_clientes = mysql_num_rows($res_buscar_cods_clientes);

    if($qtd_clientes>0)
    {
      while($obj_buscar_cods_clientes = mysql_fetch_object($res_buscar_cods_clientes))
      {
        $arr_clientes[] = $obj_buscar_cods_clientes->cod_clientes;
      }
      $filtro_clientes = "and p.cod_clientes in (".implode(',',$arr_clientes).") ";
    }
    else
    {
      $filtro_clientes =  "and p.cod_clientes != p.cod_clientes";//se caso não encontrou um cliente, não realizar a busca (vai retornar nada)
    }
  }
  
  if($situacao!="TODOS")
  {  
    $filtro_situacao = " and p.situacao='".$situacao."'";
  }

  
  

  $SqlBuscaPedidos = "SELECT pc.cod_pedidos,p.data_hora_pedido,c.nome,c.cod_clientes FROM ipi_pedidos_ipi_cupons pc INNER JOIN ipi_pedidos p ON (pc.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.cod_pizzarias IN (".$cod_pizzarias.") AND cod_cupons = ".$cod_cupons." $filtro_situacao $filtro_clientes ORDER BY pc.cod_pedidos";
  $resBuscaPedidos = mysql_query($SqlBuscaPedidos);
  
  //echo $SqlBuscaPedidos;
  

  while($objBuscaPedidos = mysql_fetch_object($resBuscaPedidos)) {
    echo '<tr>';
    echo '<td align="center"><a href="ipi_rel_historico_pedidos.php?p='.$objBuscaPedidos->cod_pedidos.'">'.sprintf('%08d', $objBuscaPedidos->cod_pedidos).'</a></td>';
    echo '<td align="center">'.bd2datahora($objBuscaPedidos->data_hora_pedido).'</td>';
    echo utf8_encode('<td align="center"><a href="ipi_clientes_franquia.php?cc='.$objBuscaPedidos->cod_clientes.'">'.bd2texto($objBuscaPedidos->nome).'</a></td>');
    echo '</tr>';
    $arr_pedidos[] = $objBuscaPedidos->cod_pedidos;
  }

  
  //echo "<br/><br/><br/>i: (".implode(",",$arr_pedidos).")";
  
  desconectabd($con);
}