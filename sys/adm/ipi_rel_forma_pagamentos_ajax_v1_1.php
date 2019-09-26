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
  $hora_inicial = (str_replace("X", ":", $param[4]));
  $hora_final = (str_replace("X", ":", $param[5]));

  if(validar_hora($hora_inicial))
  {
    $hora_inicial_sql = $hora_inicial.':00'; 
  }
  else
  {
    $hora_inicial_sql = '00:00:00'; 
  }

  if(validar_hora($hora_final))
  {
    $hora_final_sql = $hora_final.':59'; 
  }
  else
  {
    $hora_final_sql = '23:59:59'; 
  }

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

  $total_mesa = 0;
  $total_net = 0;
  $total_tel = 0;
  $total_geral = 0;
  $arr_totais = array();
  for ($a = 0; $a < $num_formas_pg; $a++)
  {
    $obj_formas_pg = mysql_fetch_object($res_formas_pg);

    $arr_totais[$a][0] = $obj_formas_pg->forma_pg;

    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_mesa FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'MESA' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
    $soma_mesa = $objBuscaPedidosSoma->soma_mesa;
    $arr_totais[$a][1] = $soma_mesa;
    $total_mesa += $soma_mesa;

    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
    $soma_tel = $objBuscaPedidosSoma->soma_tel;
    $arr_totais[$a][2] = $soma_tel;
    $total_tel += $soma_tel;

    $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND pfp.cod_formas_pg = '".$obj_formas_pg->cod_formas_pg."' AND p.origem_pedido = 'NET' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
    $soma_net = $objBuscaPedidosSoma->soma_net;
    $arr_totais[$a][3] = $soma_net;
    $total_net += $soma_net;
    
    $total_geral += ($soma_mesa + $soma_tel + $soma_net);
    $arr_totais[$a][4] = $soma_mesa + $soma_tel + $soma_net;

    $arr_totais[$a][5] = 0; // Achar o Maior
  }

// Deixar o maior valor descolado automaticamente
  $cont_array = count($arr_totais);
  $maior_valor = $arr_totais[$a][4];
  for ($a=0; $a<$cont_array; $a++)
  {
    if ($arr_totais[$a][4] > $maior_valor)
    {
      $maior_valor = $arr_totais[$a][4];
    }
  }

  for ($a=0; $a<$cont_array; $a++)
  {
    if ($arr_totais[$a][4] == $maior_valor)
    {
      $arr_totais[$a][5] = 1;
    }
  }

  ?>
  <chart caption="Formas de Pagamento" subcaption="<? echo bd2data($data_inicial).' - '.bd2data($data_final).($obj_pizzaria->nome!=''?' - '.$obj_pizzaria->nome:''); ?>" xAxisName="Month" yAxisName="Sales" numberPrefix="R$">
    <?
    for ($a=0; $a<$cont_array; $a++)
    {
      echo "<set label='".str_replace(utf8_decode("CARTÃO"), "", $arr_totais[$a][0])."' value='".($arr_totais[$a][4]==""?"0":$arr_totais[$a][4])."' ".($arr_totais[$a][5]=="1"?"isSliced='1'":"")." />";
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
  $hora_inicial = (str_replace("X", ":", $param[4]));
  $hora_final = (str_replace("X", ":", $param[5]));

  if(validar_hora($hora_inicial))
  {
    $hora_inicial_sql = $hora_inicial.':00'; 
  }
  else
  {
    $hora_inicial_sql = '00:00:00'; 
  }

  if(validar_hora($hora_final))
  {
    $hora_final_sql = $hora_final.':59'; 
  }
  else
  {
    $hora_final_sql = '23:59:59'; 
  }

  if($cod_pizzarias > 0)
  {
    $obj_pizzaria = executaBuscaSimples("SELECT nome FROM ipi_pizzarias WHERE cod_pizzarias = '".$cod_pizzarias."'", $conexao);
    $SqlCodPizzarias = "AND p.cod_pizzarias = ".$cod_pizzarias;
  }
  else
  {
    $SqlCodPizzarias = '';
  }
  $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_mesa FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND p.origem_pedido = 'MESA' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
  $soma_mesa = $objBuscaPedidosSoma->soma_mesa;

  $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_tel FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND p.origem_pedido = 'TEL' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
  $soma_tel = $objBuscaPedidosSoma->soma_tel;

  $objBuscaPedidosSoma = executaBuscaSimples("SELECT SUM(pfp.valor) AS soma_net FROM ipi_pedidos p LEFT JOIN ipi_pedidos_formas_pg pfp ON (p.cod_pedidos = pfp.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial $hora_inicial_sql' AND '$data_final $hora_final_sql' AND p.origem_pedido = 'NET' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND p.situacao = 'BAIXADO' $SqlCodPizzarias", $conexao);
  $soma_net = $objBuscaPedidosSoma->soma_net;
?>
  <chart caption="Entrada dos Pedidos" subcaption="<? echo bd2data($data_inicial).' - '.bd2data($data_final).($obj_pizzaria->nome!=''?' - '.$obj_pizzaria->nome:''); ?>" xAxisName="Month" yAxisName="Sales" numberPrefix="R$">
    <set label="Mesa" value="<? echo $soma_mesa; ?>"/>
    <set label="Loja" value="<? echo $soma_tel; ?>"/>
    <set label="Internet" value="<? echo $soma_net; ?>"/>
  </chart>
<?
}

desconectabd($conexao); 
?>
