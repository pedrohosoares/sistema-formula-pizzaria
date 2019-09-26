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
  $data_final = validaVarPost('data_final');//https://www.osmuzzarellas.com.br/sys/adm/ipi_central_tickets_is.php
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  $cod_ingredientes = validaVarPost('cod_ingredientes');
  $nome_ingrediente = validaVarPost('nome_ingrediente');
  $dia_filtro = validaVarPost('dia_filtro');
  $tipo = validaVarPost('tipo');
  $unidade = validaVarPost('unidade');
  $divisor = validaVarPost('divisor');

  if(!is_numeric($pagina))
    $pagina = 1;

  $qtd_por_pagina = 20;


  $tamanhos = array();
  $arr_gastos = array();
  $total_gasto = 0;

  $sql_buscar_qtd_pags = "SELECT count(e.cod_estoque_entrada_itens) as qtd_items from ipi_estoque e where e.cod_pizzarias in ($cod_pizzarias_usuario) and e.cod_pizzarias in($cod_pizzarias) and e.data_hora_lancamento between '$data_inicial 00:00:00' and '$data_final 23:59:59' and day(data_hora_lancamento) = day('$dia_filtro') and month(data_hora_lancamento) = month('$dia_filtro') and year(data_hora_lancamento) = year('$dia_filtro') and e.quantidade < 0";
    if($tipo=="ingrediente")
  {
    $sql_buscar_qtd_pags .= " and e.cod_ingredientes = '$cod_ingredientes' LIMIT 50";
  }
  else
  {
    $sql_buscar_qtd_pags .= " and e.cod_bebidas_ipi_conteudos = '$cod_ingredientes' LIMIT 50";
  }
  $res_buscar_qtd_pags = mysql_query($sql_buscar_qtd_pags);
  $obj_buscar_qtd_pags = mysql_fetch_object($res_buscar_qtd_pags);

  $num_pags = ceil($obj_buscar_qtd_pags->qtd_items / $qtd_por_pagina);

  $sql_buscar_tamanhos = "SELECT cod_tamanhos,tamanho from ipi_tamanhos";
  $res_buscar_tamanhos = mysql_query($sql_buscar_tamanhos);
  while($obj_buscar_tamanhos = mysql_fetch_object($res_buscar_tamanhos))
  {
    $tamanhos[$obj_buscar_tamanhos->cod_tamanhos] = $obj_buscar_tamanhos->tamanho;
  }

  $sql_buscar_movimentacoes = "SELECT e.*,bc.cod_bebidas_ipi_conteudos,c.conteudo,b.bebida,pf.cod_pizzas,pp.cod_tamanhos,pp.quant_fracao,piz.pizza,bor.borda from ipi_estoque e left join ipi_ingredientes i on i.cod_ingredientes = e.cod_ingredientes left join ipi_pedidos p on p.cod_pedidos = e.cod_pedidos left join ipi_pedidos_fracoes pf on (pf.cod_pedidos = p.cod_pedidos and pf.cod_pedidos_fracoes and e.cod_pedidos_fracoes) left join ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos = e.cod_bebidas_ipi_conteudos left join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas left join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos left join ipi_pedidos_pizzas pp on (pp.cod_pedidos_pizzas = e.cod_pedidos_pizzas and pp.cod_pedidos = e.cod_pedidos) left join ipi_pizzas piz on piz.cod_pizzas = pf.cod_pizzas left join ipi_pedidos_bordas pb on pb.cod_pedidos_bordas = e.cod_pedidos_bordas left join ipi_bordas bor on bor.cod_bordas = pb.cod_bordas where e.cod_pizzarias in ($cod_pizzarias_usuario) and e.cod_pizzarias in($cod_pizzarias) and e.data_hora_lancamento between '$data_inicial 00:00:00' and '$data_final 23:59:59' and day(data_hora_lancamento) = day('$dia_filtro') and month(data_hora_lancamento) = month('$dia_filtro') and year(data_hora_lancamento) = year('$dia_filtro') and e.quantidade < 0";

  //if($ingrediente_filtro)
  if($tipo=="ingrediente")
  {
    $sql_buscar_movimentacoes .= " and e.cod_ingredientes = '$cod_ingredientes' LIMIT ".($pagina*$qtd_por_pagina).",".$qtd_por_pagina;
  }
  else
  {
    $sql_buscar_movimentacoes .= " and e.cod_bebidas_ipi_conteudos = '$cod_ingredientes' LIMIT ".($pagina*$qtd_por_pagina).",".$qtd_por_pagina;
  }

  //echo $sql_buscar_movimentacoes."<br/><br/>";
  $res_buscar_movimentacoes = mysql_query($sql_buscar_movimentacoes);

  $pizza_anterior = "";
  while($obj_buscar_movimentacoes = mysql_fetch_object($res_buscar_movimentacoes))
  {
    if($pizza_anterior!=$obj_buscar_movimentacoes->cod_pedidos_pizzas)
    {
      $fracao = 0;
      $pizza_anterior = $obj_buscar_movimentacoes->cod_pedidos_pizzas;
    }
    else
      $fracao++;

    $total_gasto += $obj_buscar_movimentacoes->quantidade;
    if($obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos>0)
    {
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_bebidas][$fracao]['tipo'] = 'bebida';
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_bebidas][$fracao]['tamanho'] = $obj_buscar_movimentacoes->conteudo;
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_bebidas][$fracao]['sabor'] = $obj_buscar_movimentacoes->bebida;
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_bebidas][$fracao]['fracao'] = "N/A";
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_bebidas][$fracao]['quantidade'] = abs($obj_buscar_movimentacoes->quantidade);
    }
    else
    {
        $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['tamanho'] = $obj_buscar_movimentacoes->cod_tamanhos;
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['tipo'] = ($obj_buscar_movimentacoes->pizza ? 'fracao' : 'borda');

      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['sabor'] = ($obj_buscar_movimentacoes->pizza ? $obj_buscar_movimentacoes->pizza : $obj_buscar_movimentacoes->borda);
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['fracao'] = $obj_buscar_movimentacoes->quant_fracao;
      $arr_gastos[$obj_buscar_movimentacoes->cod_pedidos][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['quantidade'] = abs($obj_buscar_movimentacoes->quantidade);
    }

  }
  $total = 0;
  // 
  // cod,nome_ingrediente,unidade,divisor,dia,tipo,pagina
  //onclick='detalhes_ingrediente(\"".$cod."\",\"".$nome_exibicao."\",\"".$unidade_padrao["abr"]."\",\"".$unidade_padrao["divisor"]."\",\"".$diamov."\",\"".($ing=="INGREDIENTE" ? 'ingrediente' : 'bebida')."\",\"".$pagina."\")'

  echo "<div id='conteudo_modal' style='width:900px;height:500px'>";
  echo "<table style='border:1px solid black;width:900px;' class='listaEdicao'><thead>";
  echo "<tr class='sem_hover'><td colspan = '5' align='center'>";

  for($i = 1;$i<=$num_pags ; $i++)
  {
    if($i==$pagina)
    {
      echo "<b>".$i."</b>";
    }
    else  
    {
      echo "<a href='javascript:void(0)' onclick='detalhes_ingrediente_sem_abrir(\"".$cod_ingredientes."\",\"".$nome_ingrediente."\",\"".$unidade."\",\"".$divisor."\",\"".$dia_filtro."\",\"".$tipo."\",\"".$i."\")'>".$i."</a>";
    } 
       echo "&nbsp &nbsp";
  }
 // 'acao=explodir_ingrediente&tipo='+tipo+'&dia_filtro='+dia+'&nome_ingrediente='+nome_ingrediente+'&unidade='+unidade+'&divisor='+divisor+'&pagina='+pagina+'&cod_ingredientes='+cod echo $filtros 
 // (cod,nome_ingrediente,unidade,divisor,dia,tipo,pagina)
  echo utf8_encode("<tr><td align='center'>Pedido</td><td align='center'>Pizza / Produto</td><td align='center'>Tamanho</td><td align='center'>Quant. Fraçoes</td><td align='center'>Quantidade</td></tr></thead><tbody>");
  foreach ($arr_gastos as $pedido => $pizza_array) {
    $p = 1;
    foreach ($pizza_array as $pizza => $fracoes) {
      $f = 1;
      foreach ($fracoes as $infos => $dados) {
        
        if($dados['tipo']=="bebida")
        {
            $nome = $dados['sabor'];
            $quantidade = $dados['quantidade'];
            $quant_frac =  "N/A";
            $tamanho = $dados['tamanho'];
        }
        else
          if($dados['tipo']=='borda')
          {
            $nome = "Borda de ".$dados['sabor'];
            $quantidade = $dados['quantidade'];
            $quantidade = round(($quantidade/$divisor),2);
            $quant_frac =  "N/A";
            $tamanho = $tamanhos[$dados['tamanho']];
          }
          else
          {
            $nome = "Pizza $p ($f/".$dados['fracao']."):".$dados['sabor'];
            $quantidade = $dados['quantidade']/($dados["fracao"]>0 && $dados["fracao"]!="" ? $dados["fracao"] : 1);
            $quantidade = round(($quantidade/$divisor),2);
            $quant_frac =  $dados["fracao"]." Sabor".($dados["fracao"]>1 ? 'es' : '' );
            $tamanho = $tamanhos[$dados['tamanho']];
          }


       echo utf8_encode("<tr><td align='center'>".$pedido."</td><td align='center'>".$nome."</td><td align='center'>".$tamanho."</td><td align='right'>".$quant_frac."</td><td align='right'>".bd2moeda($quantidade)."</td></tr>");


        $total += $quantidade;
        $f++;
      }
      $p++;

    }
  }
  /*echo "<pre>";
  print_r($arr_gastos);
  echo "</pre>";*/
 
  echo "<tr><td colspan='3'></td><td colspan='2' align='right'>Total = ".$total." ".utf8_encode($unidade)."</td></tbody></table>";
  echo "</div>";
  desconectar_bd($con);
  break;
case 'explodir_compra':

  $con = conectar_bd();
  $data_inicial = validaVarPost('data_inicial');
  $data_final = validaVarPost('data_final');
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  $cod_ingredientes = validaVarPost('cod_ingredientes');
  $nome_ingrediente = validaVarPost('nome_ingrediente');
  $dia_filtro = validaVarPost('dia_filtro');
  $tipo = validaVarPost('tipo');
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

  $sql_buscar_movimentacoes = "SELECT e.*,t.numero_nota_fiscal,c.conteudo,b.bebida,eti.preco_unitario_entrada,eti.quantidade_embalagem_entrada, eti.quantidade_entrada from ipi_estoque e left join ipi_estoque_entrada_itens eti on eti.cod_estoque_entrada_itens = e.cod_estoque_entrada_itens left join ipi_titulos t on t.cod_estoque_entrada = eti.cod_estoque_entrada left join ipi_ingredientes i on i.cod_ingredientes = e.cod_ingredientes left join ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos = e.cod_bebidas_ipi_conteudos left join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas left join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos where e.cod_pizzarias in ($cod_pizzarias_usuario) and e.cod_pizzarias in($cod_pizzarias) and e.data_hora_lancamento between '$data_inicial 00:00:00' and '$data_final 23:59:59' and e.cod_estoque_entrada_itens > 0 and day(data_hora_lancamento) = day('$dia_filtro') and month(data_hora_lancamento) = month('$dia_filtro') and year(data_hora_lancamento) = year('$dia_filtro') and e.quantidade > 0 ";

  //if($ingrediente_filtro)
  if($tipo=="ingrediente")
  {
    $sql_buscar_movimentacoes .= " and e.cod_ingredientes = '$cod_ingredientes' LIMIT 50";
  }
  else
  {
    $sql_buscar_movimentacoes .= " and e.cod_bebidas_ipi_conteudos = '$cod_ingredientes'  LIMIT 50";
  }

  //echo $sql_buscar_movimentacoes."<br/><br/>";
  $res_buscar_movimentacoes = mysql_query($sql_buscar_movimentacoes);

  $pizza_anterior = "";
  while($obj_buscar_movimentacoes = mysql_fetch_object($res_buscar_movimentacoes))
  {
    if($pizza_anterior!=$obj_buscar_movimentacoes->cod_pedidos_pizzas)
    {
      $fracao = 0;
      $pizza_anterior = $obj_buscar_movimentacoes->cod_pedidos_pizzas;
    }
    else
      $fracao++;

    $total_gasto += $obj_buscar_movimentacoes->quantidade;
    if($obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos>0)
    {
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos][$fracao]['tipo'] = 'bebida';
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos][$fracao]['tamanho'] = $obj_buscar_movimentacoes->conteudo;
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos][$fracao]['sabor'] = $obj_buscar_movimentacoes->bebida;
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos][$fracao]['nota_fiscal'] = $obj_buscar_movimentacoes->numero_nota_fiscal;
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos][$fracao]['quantidade'] = abs($obj_buscar_movimentacoes->quantidade);
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos][$fracao]['preco_unitario'] = ($obj_buscar_movimentacoes->preco_unitario_entrada)/$obj_buscar_movimentacoes->quantidade_embalagem_entrada;

      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos][$fracao]['preco_total'] = $obj_buscar_movimentacoes->quantidade_entrada*$obj_buscar_movimentacoes->preco_unitario_entrada;
    }
    else
    {
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['tamanho'] = $obj_buscar_movimentacoes->cod_tamanhos;

      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['tipo'] = 'ingrediente';

      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['sabor'] =$nome_ingrediente;

      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['nota_fiscal'] = $obj_buscar_movimentacoes->numero_nota_fiscal;
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['quantidade'] = abs($obj_buscar_movimentacoes->quantidade);
      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['preco_unitario'] = ($obj_buscar_movimentacoes->preco_unitario_entrada)/($obj_buscar_movimentacoes->quantidade_embalagem_entrada/$divisor);

      $arr_gastos[$obj_buscar_movimentacoes->cod_estoque_entrada_itens][$obj_buscar_movimentacoes->cod_pedidos_pizzas][$fracao]['preco_total'] = $obj_buscar_movimentacoes->quantidade_entrada*$obj_buscar_movimentacoes->preco_unitario_entrada;
    }

  }
  $total = 0;
  $total_valor = 0;
  // onclick='detalhes_ingrediente(\"".$cod."\",\"".$nome_exibicao."\",\"".$unidade_padrao["abr"]."\",\"".$unidade_padrao["divisor"]."\",\"".$diamov."\",\"".($ing=="INGREDIENTE" ? 'ingrediente' : 'bebida')."\")'


  echo "<div style='width:900px;height:500px'>";
  echo "<table style='border:1px solid black;width:900px;' class='listaEdicao'>";
  echo utf8_encode("<thead><tr><td align='center'>Produto</td>".($tipo=="bebida" ? "<td align='center'>Tamanho</td>" : "" )."<td align='center'>Nota Fiscal</td><td align='center'>Quantidade</td><td align='center'>Preço Unitario</td></tr></thead><tbody>");
  foreach ($arr_gastos as $pedido => $pizza_array) {
    $p = 1;
    foreach ($pizza_array as $pizza => $fracoes) {
      $f = 1;
      foreach ($fracoes as $infos => $dados) {
        
        if($dados['tipo']=="bebida")
        {
            $nome = $dados['sabor'];
            $quantidade = $dados['quantidade'];
            $quant_frac = $dados['nota_fiscal'];
            $tamanho = $dados['tamanho'];
            $preco = bd2moeda($dados['preco_unitario']);
        }
        else
          if($dados['tipo']=='ingrediente')
          {
            $nome = $dados['sabor'];
            $quantidade = $dados['quantidade'];
            $quantidade = round(($quantidade/$divisor),2);
            $quant_frac =  $dados['nota_fiscal'];
            $tamanho = $tamanhos[$dados['tamanho']];
            $preco = bd2moeda($dados['preco_unitario']);
          }
          /*
          if($dados['tipo']=='borda')
          {
            $nome = "Borda de ".$dados['sabor'];
            $quantidade = $dados['quantidade'];
            $quantidade = round(($quantidade/$divisor),2);
            $quant_frac =  $dados['nota_fiscal'];
            $tamanho = $tamanhos[$dados['tamanho']];
          }
          else
          {
            $nome = "Pizza $p ($f/".$dados['fracao']."):".$dados['sabor'];
            $quantidade = $dados['quantidade']/($dados["fracao"]>0 && $dados["fracao"]!="" ? $dados["fracao"] : 1);
            $quantidade = round(($quantidade/$divisor),2);
            $quant_frac =  $dados['nota_fiscal'];
            $tamanho = $tamanhos[$dados['tamanho']];
          }*/


       echo utf8_encode("<tr><td align='center'>".$nome."</td>".($tipo=="bebida" ? "<td align='center'>".$tamanho."</td>" : "" )."<td align='center'>".$quant_frac."</td><td align='right'>".bd2moeda($quantidade)."</td><td align='right'>".$preco."</td></tr>");


        $total += $quantidade;
        $total_valor += $dados['preco_total'];
        $f++;
      }
      $p++;

    }
  }
  /*echo "<pre>";
  print_r($arr_gastos);
  echo "</pre>";*/
  
  echo "<tr><td colspan='".($tipo=="ingrediente" ? '2' : '3' )."'></td><td colspan='1' align='right'>Total = ".bd2moeda($total)." ".$unidade."</td><td colspan='1' align='right'>Total = R$ ".bd2moeda($total_valor)."</td></tbody></table>";
  echo "</div>";
  desconectar_bd($con);
  break;
}

?>