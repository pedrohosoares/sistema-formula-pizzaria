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

$param = explode(',', validaVarGet('grafico'));

$tipo_grafico = $param[0];
$cod_disparo = $param[1];

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

if($tipo_grafico!="")
{
  ?>
  <?php if($tipo_grafico == 1):
  $con = conectabd();

  $sql_buscar_dias_lidos = "SELECT COUNT(*) total_dia, day(data_hora_leitura) AS dia, month(data_hora_leitura) AS mes,year(data_hora_leitura) AS ano FROM ine_disparo_mensagens_emails WHERE  cod_disparo_mensagens = '$cod_disparo' GROUP BY dia,mes,ano ORDER BY ano asc,mes ASC,dia ASC";
  $res_buscar_dias_lidos = mysql_query($sql_buscar_dias_lidos);
  $num_buscar_dias_lidos = mysql_num_rows($res_buscar_dias_lidos);
  $categorias = '';
  $valores = '';
  for ($x=0; $x<$num_buscar_dias_lidos; $x++)
  {
    $obj_buscar_dias_lidos = mysql_fetch_object($res_buscar_dias_lidos);
    $arr_total_horas[$obj_buscar_dias_lidos->mes][$obj_buscar_dias_lidos->dia]=$obj_buscar_dias_lidos->total_dia;
    $categorias .='<category label="'.$obj_buscar_dias_lidos->dia.'/'.$obj_buscar_dias_lidos->mes.'/'.$obj_buscar_dias_lidos->ano.'"/>';

    $valores .= '<set value="';
    //$arr_total_horas[$obj_buscar_dias_lidos->mes][$obj_buscar_dias_lidos->dia]=$obj_buscar_dias_lidos->total_dia;
    if ($obj_buscar_dias_lidos->total_dia!='')
    {
      $valores .= $obj_buscar_dias_lidos->total_dia;
    }
    else 
    {
      $valores .= '0'; 
    }
    $valores .= '"/>';
  } 

echo '<chart caption="Leituras deste email por dia" lineThickness="1" showValues="0" labelStep="1" formatNumberScale="1" anchorRadius="2" divLineAlpha="20" divLineColor="CC3300" divLineIsDashed="1" showAlternateHGridColor="1" alternateHGridColor="CC3300" shadowAlpha="40" numvdivlines="5" chartRightMargin="35" bgColor="FFFFFF,CC3300" bgAngle="270" bgAlpha="10,10">';
  
  echo'<categories>';
  
    echo $categorias;
  
  echo'</categories>';
  

  
  echo'<dataset seriesName="Dia/Mes/Ano" color="1D8BD1" anchorBorderColor="1D8BD1" anchorBgColor="1D8BD1">';

      echo $valores;
  
    /*for ($x=0; $x<=23; $x++)
    {
      echo '<set value="';
      if ($arr_total_horas[$x]!='')
      {
        echo $arr_total_horas[$x];
      }
      else 
      {
        echo '0'; 
      }
      echo '"/>';
    } */
    
  echo'</dataset>';
  
echo'</chart>'; 
  desconectabd($con);
endif; 

}