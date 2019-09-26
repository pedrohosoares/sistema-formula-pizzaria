<?php
/**
 * Formas de Pagamento.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       05/07/2011   Thiago        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';

$param = explode(',', validaVarGet('param'));
$tipo_grafico = $param[0];

$conexao = conectabd();

if ($tipo_grafico == 1)
{
  $data_inicial = data2bd($param[1]);
  $data_final = data2bd($param[2]);
  $cod_pizzarias = $param[3];

  if($cod_pizzarias > 0)
  {
    $obj_pizzaria = executaBuscaSimples("SELECT nome FROM ipi_pizzarias WHERE cod_pizzarias = '".$cod_pizzarias."'", $conexao);
    $SqlCodPizzarias = "AND p.cod_pizzarias = ".$cod_pizzarias;
  }
  else
  {
    $SqlCodPizzarias = '';
  }

  $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
  $res_formas_pg = mysql_query($sql_formas_pg);
  $num_formas_pg = mysql_num_rows($res_formas_pg);

  $total_net = 0;
  $total_tel = 0;
  $total_geral = 0;
  $arr_totais = array();
  for ($a = 0; $a < $num_formas_pg; $a++)
  {
    $obj_formas_pg = mysql_fetch_object($res_formas_pg);
    $arr_totais[$a][0] = $obj_formas_pg->forma_pg;

    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
    $soma_tel = $objBuscaPedidosSoma->soma_tel;
    $arr_totais[$a][1] = $soma_tel;
    $total_tel += $soma_tel;
    
    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
    $soma_net = $objBuscaPedidosSoma->soma_net;
    $arr_totais[$a][2] = $soma_net;
    $total_net += $soma_net;
    
    $total_geral += ($soma_tel+$soma_net);
    $arr_totais[$a][3] = $soma_tel+$soma_net;

    $arr_totais[$a][4] = 0; // Achar o Maior
  }

// Deixar o maior valor descolado automaticamente
  $cont_array = count($arr_totais);
  $maior_valor = $arr_totais[$a][3];
  for ($a=0; $a<$cont_array; $a++)
  {
    if ($arr_totais[$a][3] > $maior_valor)
    {
      $maior_valor = $arr_totais[$a][3];
    }
  }

  for ($a=0; $a<$cont_array; $a++)
  {
    if ($arr_totais[$a][3] == $maior_valor)
    {
      $arr_totais[$a][4] = 1;
    }
  }

  ?>
  <chart caption="Formas de Pagamento" subcaption="<? echo bd2data($data_inicial).' - '.bd2data($data_final).($obj_pizzaria->nome!=''?' - '.$obj_pizzaria->nome:''); ?>" xAxisName="Month" yAxisName="Sales" numberPrefix="R$">
    <?
    for ($a=0; $a<$cont_array; $a++)
    {
      echo "<set label='".str_replace(utf8_decode("CARTÃO"), "", $arr_totais[$a][0])."' value='".($arr_totais[$a][3]==""?"0":$arr_totais[$a][3])."' ".($arr_totais[$a][4]=="1"?"isSliced='1'":"")." />";
    } 
    ?>
  </chart>
  <?
}


if ($tipo_grafico == 2)
{
  $data_inicial = data2bd($param[1]);
  $data_final = data2bd($param[2]);
  $cod_pizzarias = $param[3];

  if($cod_pizzarias > 0)
  {
    $obj_pizzaria = executaBuscaSimples("SELECT nome FROM ipi_pizzarias WHERE cod_pizzarias = '".$cod_pizzarias."'", $conexao);
    $SqlCodPizzarias = "AND p.cod_pizzarias = ".$cod_pizzarias;
  }
  else
  {
    $SqlCodPizzarias = '';
  }


  $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
  $soma_tel = $objBuscaPedidosSoma->soma_tel;
  
  $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.origem_pedido = 'NET' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
  $soma_net = $objBuscaPedidosSoma->soma_net;
?>
  <chart caption="Entrada dos Pedidos" subcaption="<? echo bd2data($data_inicial).' - '.bd2data($data_final).($obj_pizzaria->nome!=''?' - '.$obj_pizzaria->nome:''); ?>" xAxisName="Month" yAxisName="Sales" numberPrefix="R$">
    <set label="Loja" value="<? echo $soma_tel; ?>"/>
    <set label="Internet" value="<? echo $soma_net; ?>"/>
  </chart>
<?
}

desconectabd($conexao); 
?>
