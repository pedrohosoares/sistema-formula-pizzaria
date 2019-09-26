<?php

/**
 * Mapa de estoque (ajax).
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR            DESCRIÇÃO 
 * ======    ==========   ==============   =============================================================
 *
 * 1.0       04/03/2013   FilipeGranato     Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validaVarPost('acao');
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
switch ($acao)
{
  case 'explodir_ingrediente':

  $con = conectar_bd();
  $data_inicial = validaVarPost('data_inicial');
  $data_final = validaVarPost('data_final');
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  $cod_ingredientes = validaVarPost('cod_ingredientes');
  $nome_ingrediente = validaVarPost('nome_ingrediente');
  $unidade = validaVarPost('unidade');
  $divisor = validaVarPost('divisor');
  $tamanhos = array();
  $arr_gastos = array();
  $total_gasto = 0;
  $sql_buscar_tamanhos = "SELECT cod_tamanhos,tamanho from ipi_tamanhos";
  $res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);
  while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
  {
    $tamanhos[$obj_buscar_tamanhos->cod_tamanhos] = $obj_buscar_tamanhos->tamanho;
  }

  $sql_buscar_pizzas = "SELECT pf.cod_pizzas,pp.cod_tamanhos,piz.pizza,pp.quant_fracao,ie.cod_ingredientes,ie.quantidade_estoque_ingrediente,pp.cod_pedidos_pizzas,p.cod_pedidos from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_estoque ie on ie.cod_pizzas = pf.cod_pizzas inner join ipi_pizzas piz on piz.cod_pizzas = pf.cod_pizzas where ie.cod_tamanhos = pp.cod_tamanhos and ie.cod_ingredientes = '$cod_ingredientes' and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias_usuario))";

  $res_buscar_pizzas = mysql_query($sql_buscar_pizzas);
  //echo "<br/>".$sql_buscar_pizzas;
  $pizza_anterior = "";
  while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas))
  {
    if($pizza_anterior!=$obj_buscar_pizzas->cod_pedidos_pizzas)
    {
      $fracao = 0;
      $pizza_anterior = $obj_buscar_pizzas->cod_pedidos_pizzas;
    }
    else
      $fracao++;

    $total_gasto += ($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);

    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas][$fracao]['tamanho'] = $obj_buscar_pizzas->cod_tamanhos;
    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas][$fracao]['sabor'] = $obj_buscar_pizzas->pizza;
    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas][$fracao]['fracao'] = $obj_buscar_pizzas->quant_fracao;
    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas][$fracao]['quantidade'] = $obj_buscar_pizzas->quantidade_estoque_ingrediente;
    //echo "<br/>pedido= ".$obj_buscar_pizzas->cod_pedidos_pizzas." tamanho = ".$obj_buscar_pizzas->cod_tamanhos." fracao=".$obj_buscar_pizzas->quant_fracao." qtd=".($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);
  }
  $obj_buscar_pizzas = "";
  $sql_buscar_pizzas2 = "SELECT pf.cod_pizzas,pp.cod_tamanhos,piz.pizza,pp.quant_fracao,it.cod_ingredientes,it.quantidade_estoque_extra,pp.cod_pedidos_pizzas,p.cod_pedidos from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_pedidos_fracoes pf on pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_pedidos_ingredientes pe on pe.cod_pedidos_pizzas = pp.cod_pedidos_pizzas inner join ipi_ingredientes_ipi_tamanhos it on it.cod_ingredientes = pe.cod_ingredientes inner join ipi_pizzas piz on piz.cod_pizzas = pf.cod_pizzas where it.cod_pizzarias = 1 and it.cod_tamanhos = pp.cod_tamanhos and pe.ingrediente_padrao = 0 and it.cod_ingredientes = '$cod_ingredientes' and p.cod_pedidos in (SELECT p.cod_pedidos from ipi_pedidos p inner join ipi_pizzarias pi on pi.cod_pizzarias = p.cod_pizzarias where p.cod_pizzarias in ($cod_pizzarias) AND p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao IN ('BAIXADO') AND p.cod_pizzarias IN ($cod_pizzarias_usuario))";

  $res_buscar_pizzas2 = mysql_query($sql_buscar_pizzas2);
  //echo "<br/>".$sql_buscar_pizzas2;
  while($obj_buscar_pizzas = mysql_fetch_object($res_buscar_pizzas2))
  {
    $total_gasto += ($obj_buscar_pizzas->quantidade_estoque_ingrediente / $obj_buscar_pizzas->quant_fracao);

    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas]['tamanho'] = $obj_buscar_pizzas->cod_tamanhos;
    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas]['sabor'] = $obj_buscar_pizzas->pizza;
    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas]['fracao'] = $obj_buscar_pizzas->quant_fracao;
    $arr_gastos[$obj_buscar_pizzas->cod_pedidos][$obj_buscar_pizzas->cod_pedidos_pizzas]['quantidade'] = $obj_buscar_pizzas->quantidade_estoque_ingrediente;
  }

  $total = 0;
  echo "<div style='width:900px;height:500px;overflow:auto'>";
  echo "<table style='border:1px solid black;width:1000px;height:500px' class='listaEdicao'>";
  echo utf8_encode("<thead><tr><td>Pedido</td><td>Pizza</td><td>Tamanho</td><td>Quant. Fraçoes</td><td>Quantidade</td></tr></thead><tbody>");
  foreach ($arr_gastos as $pedido => $pizza_array) {
    $p = 1;
    foreach ($pizza_array as $pizza => $fracoes) {
      $f = 1;
      foreach ($fracoes as $infos => $dados) {
       echo utf8_encode("<tr><td>".$pedido."</td><td>Pizza $p ($f/".$dados['fracao']."):".$dados['sabor']."</td><td>Tamanho: ".$tamanhos[$dados['tamanho']]."</td><td>".$dados["fracao"]." Sabores</td><td>".round((($dados['quantidade']/$dados['fracao'])/$divisor),2)." ".$unidade."</td></tr>");
        $total += $dados['quantidade']/$dados["fracao"];
        $f++;
      }
      $p++;

    }
  }
 // echo "<pre>";
  //print_r($arr_gastos);
  //echo "</pre>";
 
  echo "<tr><td colspan='3'></td><td colspan='2'>Total = ".round(($total/$divisor),2)." ".$unidade."</td></tbody></table>";
  echo "</div>";
  desconectar_bd($con);
  break;
}

?>