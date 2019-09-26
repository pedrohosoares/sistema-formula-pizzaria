<?php

/**
 * ipi_rel_quant_vendidas.php: Relatório de Quantidades Vendidas
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Quantidades Vendidas');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>

<script type="text/javascript" src="../lib/js/calendario.js"></script>

<script>

window.addEvent('domready', function() {
  new vlaDatePicker('data_inicial', {prefillDate: false});
  new vlaDatePicker('data_final', {prefillDate: false});
});

</script>

<form name="frmFiltro" method="post">

  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="150">
        <select name="cod_pizzarias" style="width: 150px;">
          <option value="TODOS">Todas Pizzarias</option>
          <?
          $con = conectabd();
          
          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias ORDER BY nome";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
            
            if(validaVarPost('cod_pizzarias') == $objBuscaPizzarias->cod_pizzarias)
              echo 'selected';
            
            echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
          }
          
          desconectabd($con);
          ?>
        </select>
      </td>
      <td width="140">
        <label for="data_inicial">Data Inicial:</label>
        <input type="text" name="data_inicial" id="data_inicial" size="8" value="<? echo (validaVarPost('data_inicial') != '') ? validaVarPost('data_inicial') : date('d/m/Y') ?>">
      </td>
      <td width="135">
        <label for="data_final">Data Final:</label>
        <input type="text" name="data_final" id="data_final" size="8" value="<? echo (validaVarPost('data_final') != '') ? validaVarPost('data_final') : date('d/m/Y') ?>">
      </td>
      <td><input class="botaoAzul" type="submit" value="Filtrar"></td>
    </tr>
  </table>

  <? 
  
  $con = conectabd(); 
  $data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
  $data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
  
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  
  if($cod_pizzarias > 0)
    $SqlCodPizzarias = "AND p.cod_pizzarias = ".$cod_pizzarias;
  else
    $SqlCodPizzarias = '';
      
  ?>

  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center">Pizza</td>
        <td align="center" width="200">Tamanho</td>
        <td align="center" width="100">Quantidade</td>
        <td align="center" width="100">Valor</td>
      </tr>
    </thead>
    <tbody>
    
    <?
    $quantidade_total_pizza = 0;
    $valor_total_pizza = 0;
    $quantidade_total_bebida = 0;
    $valor_total_bebida = 0;
    
    $SqlBuscaPizzas = "SELECT * FROM ipi_pizzas ORDER BY pizza";
    $resBuscaPizzas = mysql_query($SqlBuscaPizzas);
    
    $SqlBuscaPedidosFracoes = "SELECT DISTINCT pz.pizza, t.tamanho FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_tamanhos t ON (pp.cod_tamanhos = t.cod_tamanhos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao = 'BAIXADO' $SqlCodPizzarias ORDER BY tamanho, pizza";
    $resBuscaPedidosFracoes = mysql_query($SqlBuscaPedidosFracoes);
    
    while ($objBuscaPedidosFracoes = mysql_fetch_object($resBuscaPedidosFracoes)) {
      //$SqlBuscaPedidosFracoes = "SELECT pf.fracao / pp.quant_fracao AS quantidade FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
      //$objBuscaPedidosFracoes = executaBuscaSimples($SqlBuscaPedidosFracoes, $con);
      //$quantidade = ($objBuscaPedidosFracoes->quantidade > 0) ? $objBuscaPedidosFracoes->quantidade : 0;
      
      // 08/05/09 Dia da entrega dos Muzza: Foi retirado o calculo da soma dos ingredientes, pois o mesmo invalida o INNER JOIN caso nao peça pizza sem ingredientes extras
      //$SqlBuscaPedidosValor = "SELECT SUM(pi.preco) + SUM(pf.preco) + SUM(pp.preco) AS valor FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = ".$objBuscaPizzas->cod_pizzas." AND pi.ingrediente_padrao = 0 AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
      
      //$SqlBuscaPedidosValor = "SELECT SUM(pf.preco) AS valor FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN $tabela p ON (pp.cod_pedidos = p.cod_pedidos) INNER JOIN ipi_pizzas pz ON (pz.cod_pizzas = pf.cod_pizzas) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pf.cod_pizzas = ".$objBuscaPizzas->cod_pizzas." AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
      
      //$objBuscaPedidosValor = executaBuscaSimples($SqlBuscaPedidosValor, $con);
      //$valor = ($objBuscaPedidosValor->valor > 0) ? $objBuscaPedidosValor->valor : '0.00';
      
      //$quantidade_total_pizza += $quantidade;
      //$valor_total_pizza += $valor;
      
      echo '<tr>';
      
      echo '<td align="center">'.$objBuscaPedidosFracoes->pizza.'</td>';
      echo '<td align="center">'.$objBuscaPedidosFracoes->tamanho.'</td>';
      echo '<td align="center">'.$quantidade.'</td>';
      echo '<td align="center">'.bd2moeda($valor).'</td>';
      
      echo '</tr>';
    }
    ?>
    
    </tbody>
  </table>
  
  <br><br>
  
  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center">Bordas</td>
        <td align="center" width="100">Quantidade</td>
        <td align="center" width="100">Valor</td>
      </tr>
    </thead>
    <tbody>
      <?
      $SqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) ORDER BY bebida";
      $resBuscaBebidas = mysql_query($SqlBuscaBebidas);
      
      while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas)) {
        $SqlBuscaQuantidade = "SELECT SUM(quantidade) AS quantidade FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = ".$objBuscaBebidas->cod_bebidas_ipi_conteudos." AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(preco) AS valor FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = ".$objBuscaBebidas->cod_bebidas_ipi_conteudos." AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_bebida += $quantidade;
        $valor_total_bebida += $valor;
        
        echo '<tr>';
        
        echo '<td align="center">'.$objBuscaBebidas->bebida.' '.$objBuscaBebidas->conteudo.'</td>';
        echo '<td align="center">'.$quantidade.'</td>';
        echo '<td align="center">'.bd2moeda($valor).'</td>';
        
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>
  
  <br><br>
  
  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center">Adicionais</td>
        <td align="center" width="100">Quantidade</td>
        <td align="center" width="100">Valor</td>
      </tr>
    </thead>
    <tbody>
      <?
      $SqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) ORDER BY bebida";
      $resBuscaBebidas = mysql_query($SqlBuscaBebidas);
      
      while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas)) {
        $SqlBuscaQuantidade = "SELECT SUM(quantidade) AS quantidade FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = ".$objBuscaBebidas->cod_bebidas_ipi_conteudos." AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(preco) AS valor FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = ".$objBuscaBebidas->cod_bebidas_ipi_conteudos." AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_bebida += $quantidade;
        $valor_total_bebida += $valor;
        
        echo '<tr>';
        
        echo '<td align="center">'.$objBuscaBebidas->bebida.' '.$objBuscaBebidas->conteudo.'</td>';
        echo '<td align="center">'.$quantidade.'</td>';
        echo '<td align="center">'.bd2moeda($valor).'</td>';
        
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>
  
  <br><br>
  
  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center">Bebida</td>
        <td align="center" width="100">Quantidade</td>
        <td align="center" width="100">Valor</td>
      </tr>
    </thead>
    <tbody>
      <?
      $SqlBuscaBebidas = "SELECT * FROM ipi_bebidas_ipi_conteudos bc INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) ORDER BY bebida";
      $resBuscaBebidas = mysql_query($SqlBuscaBebidas);
      
      while ($objBuscaBebidas = mysql_fetch_object($resBuscaBebidas)) {
        $SqlBuscaQuantidade = "SELECT SUM(quantidade) AS quantidade FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = ".$objBuscaBebidas->cod_bebidas_ipi_conteudos." AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
        $objBuscaQuantidade = executaBuscaSimples($SqlBuscaQuantidade, $con);
        $quantidade = ($objBuscaQuantidade->quantidade > 0) ? $objBuscaQuantidade->quantidade : 0;
        
        $SqlBuscaValor = "SELECT SUM(preco) AS valor FROM ipi_pedidos_bebidas pb INNER JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE p.data_hora_pedido BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59' AND pb.cod_bebidas_ipi_conteudos = ".$objBuscaBebidas->cod_bebidas_ipi_conteudos." AND p.situacao = 'BAIXADO' $SqlCodPizzarias";
        $objBuscaValor = executaBuscaSimples($SqlBuscaValor, $con);
        $valor = ($objBuscaValor->valor > 0) ? $objBuscaValor->valor : '0.00';
        
        $quantidade_total_bebida += $quantidade;
        $valor_total_bebida += $valor;
        
        echo '<tr>';
        
        echo '<td align="center">'.$objBuscaBebidas->bebida.' '.$objBuscaBebidas->conteudo.'</td>';
        echo '<td align="center">'.$quantidade.'</td>';
        echo '<td align="center">'.bd2moeda($valor).'</td>';
        
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>
  
  <?
  
  echo '<br><p><b>Quantidade Total de Pizzas:</b> '.$quantidade_total_pizza.'</p>';
  echo '<p><b>Valor Total de Pizzas:</b> '.bd2moeda($valor_total_pizza).'</p>';
  
  echo '<br><p><b>Quantidade Total de Bordas:</b> '.$quantidade_total_bebida.'</p>';
  echo '<p><b>Valor Total de Bordas:</b> '.bd2moeda($valor_total_bebida).'</p>';
  
  echo '<br><p><b>Quantidade Total de Adicionais:</b> '.$quantidade_total_bebida.'</p>';
  echo '<p><b>Valor Total de Adicionais:</b> '.bd2moeda($valor_total_bebida).'</p>';
  
  echo '<br><p><b>Quantidade Total de Bebidas:</b> '.$quantidade_total_bebida.'</p>';
  echo '<p><b>Valor Total de Bebidas:</b> '.bd2moeda($valor_total_bebida).'</p>';
  
  desconectabd($con);
  ?>
  
  <br><br>
  
</form>

<? rodape(); ?>