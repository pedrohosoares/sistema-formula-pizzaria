<?php

  /**
   * Relatório CMV
   *
   * @version 1.0
   * @package osmuzzarellas
   * 
   * LISTA DE MODIFICAÇÕES:
   *
   * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
   * ======    ==========   ===========   =============================================================
   *
   * 1.0       17/09/2012   PEDRO H       Criado. 
   *
   */ 

  require_once '../../bd.php';
  require_once '../lib/php/formatacao.php';
  require_once '../lib/php/formulario.php';
  require_once '../lib/php/mensagem.php';

  cabecalho('Relatório CMV');

  $acao = validaVarPost('acao');

  $exibir_barra_lateral = false;

  $tabela = '';
  $chave_primaria = '';

  $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
  if(validaVarGet("cod_filt", '/[0-9]+/')!="")
  {
    $cod_pizzaria_filt = $cod_filt;

  }

  if(validaVarPost("cod_filt", '/[0-9]+/')!="")
  {
    $cod_pizzaria_filt = $cod_filt;

  }

  if(validaVarPost('cod_pizzarias_filt')!="")
  {
    $cod_pizzaria_filt = validaVarPost('cod_pizzarias_filt');
  }

  switch($acao) 
  {

  }
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<?
  $cod_pizzarias = validaVarPost('cod_pizzarias');
  $mes = validaVarPost('mes');
?>

<div>
  <form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbr tdbt sep">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
        <?
          $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
          $con = conectabd();
          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
          while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
          {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
            if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            {
              echo 'selected';
            }
            echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
          }
        ?>
      </select>
    </td>
  </tr>



  <tr>
    <td class="legenda tdbl sep" align="right"><label for="mes">Mês:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="mes" id="mes">
        <option value=""></option>
        <option value="1" <? if ($mes=="1") echo 'selected="selected"'; ?>>Janeiro</option>
        <option value="2" <? if ($mes=="2") echo 'selected="selected"'; ?>>Fevereiro</option>
        <option value="3" <? if ($mes=="3") echo 'selected="selected"'; ?>>Março</option>
        <option value="4" <? if ($mes=="4") echo 'selected="selected"'; ?>>Abril</option>
        <option value="5" <? if ($mes=="5") echo 'selected="selected"'; ?>>Maio</option>
        <option value="6" <? if ($mes=="6") echo 'selected="selected"'; ?>>Junho</option>
        <option value="7" <? if ($mes=="7") echo 'selected="selected"'; ?>>Julho</option>
        <option value="8" <? if ($mes=="8") echo 'selected="selected"'; ?>>Agosto</option>
        <option value="9" <? if ($mes=="9") echo 'selected="selected"'; ?>>Setembro</option>
        <option value="10" <? if ($mes=="10") echo 'selected="selected"'; ?>>Outubro</option>
        <option value="11" <? if ($mes=="11") echo 'selected="selected"'; ?>>Novembro</option>
        <option value="12" <? if ($mes=="12") echo 'selected="selected"'; ?>>Dezembro</option>
      </select>
    </td>
  </tr>



  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
  </form>

  <?
    if ( ($cod_pizzarias) || ($mes) )
    {
      $custo_bebidas = 0;
      $custo_ingredientes = 0;
      $cmv_porc = 0;
      $custo_total = 0;
      $arr_estoque = array();
      $arr_estoque_bebida = array();
      $con = conectar_bd();


      $sql_buscar_ingredientes = "SELECT cod_ingredientes, ingrediente FROM ipi_ingredientes WHERE ativo = '1' ORDER BY ingrediente";
      $res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);

      while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
      {
        $cod_ingredientes = $obj_buscar_ingredientes->cod_ingredientes;
        $ingrediente = $obj_buscar_ingredientes->ingrediente;

        $arr_estoque[$cod_ingredientes]['Nome'] = $ingrediente;

        $sql_buscar_ingredientes_estoque = "SELECT SUM(quantidade) AS anterior FROM ipi_estoque WHERE MONTH(data_hora_lancamento) < '".$mes."' AND YEAR(data_hora_lancamento) < '".(date("Y") + 1)."' AND cod_ingredientes = '".$cod_ingredientes."' AND cod_pizzarias = '$cod_pizzarias'";
        $res_buscar_ingredientes_estoque = mysql_query($sql_buscar_ingredientes_estoque);
        $obj_buscar_ingredientes_estoque = mysql_fetch_object($res_buscar_ingredientes_estoque);
        $arr_estoque[$cod_ingredientes]['qtd_estoque_ini'] = $arr_estoque[$cod_ingredientes]['qtd_estoque_ini'] + $obj_buscar_ingredientes_estoque->anterior;

        
        $sql_buscar_ingredientes_estoque_compra = "SELECT SUM(quantidade) as compra FROM ipi_estoque WHERE quantidade > 0 AND MONTH(data_hora_lancamento) = '".$mes."' AND YEAR(data_hora_lancamento) = '".(date("Y"))."' AND cod_ingredientes = '".$cod_ingredientes."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_ingredientes_estoque_compra = mysql_query($sql_buscar_ingredientes_estoque_compra);
        $obj_buscar_ingredientes_estoque_compra = mysql_fetch_object($res_buscar_ingredientes_estoque_compra);

        $arr_estoque[$cod_ingredientes]['qtd_estoque_compra'] = $arr_estoque[$cod_ingredientes]['qtd_estoque_compra'] + $obj_buscar_ingredientes_estoque_compra->compra;


        $sql_buscar_ingredientes_estoque_consumo = "SELECT SUM(quantidade) as consumo FROM ipi_estoque WHERE quantidade < 0 AND MONTH(data_hora_lancamento) = '".$mes."' AND YEAR(data_hora_lancamento) = '".(date("Y"))."' AND cod_ingredientes = '".$cod_ingredientes."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_ingredientes_estoque_consumo = mysql_query($sql_buscar_ingredientes_estoque_consumo);
        $obj_buscar_ingredientes_estoque_consumo = mysql_fetch_object($res_buscar_ingredientes_estoque_consumo);

        $arr_estoque[$cod_ingredientes]['qtd_estoque_consumo'] = $arr_estoque[$cod_ingredientes]['qtd_estoque_consumo'] + abs($obj_buscar_ingredientes_estoque_consumo->consumo);
        

        $arr_estoque[$cod_ingredientes]['qtd_estoque_final'] = $arr_estoque[$cod_ingredientes]['qtd_estoque_ini'] + $arr_estoque[$cod_ingredientes]['qtd_estoque_compra'] - $arr_estoque[$cod_ingredientes]['qtd_estoque_consumo'];

        $arr_estoque[$cod_ingredientes]['total_gramas'] = $arr_estoque[$cod_ingredientes]['qtd_estoque_ini'] + $arr_estoque[$cod_ingredientes]['qtd_estoque_compra'] - $arr_estoque[$cod_ingredientes]['qtd_estoque_final'];
      }

      echo '<div align="center">';

      echo '<br />';

      echo ' <table class="listaEdicao" style="width:1000px;" align="center" cellpadding="0" cellspacing="0">';
      echo '  <thead>';
      echo '   <tr>';
      echo '    <td align="center" width="100">Ingrediente</td>';
      echo '    <td align="center" width="50">Estoque inicial</td>';
      echo '    <td align="center" width="50">Comprado</td>';
      echo '    <td align="center" width="50">Consumido</td>';
      echo '    <td align="center" width="50">Estoque final</td>';
      echo '    <td align="center" width="50">Preço Un.</td>';
      echo '    <td align="center" width="50">Qtd. Embalagem</td>';
      echo '    <td align="center" width="50">Preço da Embalagem</td>';
      echo '    <td align="center" width="50">Data de compra</td>';
      echo '    <td align="center" width="50">Custo do ingrediente</td>';
      echo '   </tr>';
      echo '  </thead>';

      echo '  <tbody>';


      foreach($arr_estoque as $indice => $valor)
      {
        $sql_preco_ingrediente = "SELECT (preco_unitario_entrada/quantidade_embalagem_entrada) as preco_unidade, preco_unitario_entrada, (quantidade_embalagem_entrada*1000) as qtd_embalagem, iee.data_hota_entrada_estoque FROM ipi_estoque_entrada iee INNER JOIN ipi_estoque_entrada_itens ieei ON (iee.cod_estoque_entrada = ieei.cod_estoque_entrada) WHERE iee.data_hota_entrada_estoque < '".date("Y")."-".($mes+1)."-01' AND ieei.cod_ingredientes = '".$indice."' ORDER BY iee.data_hota_entrada_estoque DESC LIMIT 1";
        //echo $sql_preco_ingrediente.'<br>';
        $res_preco_ingrediente = mysql_query($sql_preco_ingrediente);
        $obj_preco_ingrediente = mysql_fetch_object($res_preco_ingrediente);

        $arr_estoque[$indice]['Preco un'] = ($obj_preco_ingrediente->preco_unidade ? $obj_preco_ingrediente->preco_unidade : 0);
        $arr_estoque[$indice]['qtd_embalagem'] = ($obj_preco_ingrediente->qtd_embalagem ? $obj_preco_ingrediente->qtd_embalagem : 0);
        $arr_estoque[$indice]['preco_unitario_entrada'] = ($obj_preco_ingrediente->preco_unitario_entrada ? $obj_preco_ingrediente->preco_unitario_entrada : 0);
        $date = strtotime($obj_preco_ingrediente->data_hota_entrada_estoque); 
        $arr_estoque[$indice]['data'] = ($date ? date('d/m/Y', $date) : '-');
        $arr_estoque[$indice]['preco_grama'] = ($arr_estoque[$indice]['preco_unitario_entrada']/1000);
        $arr_estoque[$indice]['qtd_estoque_ini_valor'] = $arr_estoque[$indice]['Preco un']*($arr_estoque[$indice]['qtd_estoque_ini']/1000);
        $arr_estoque[$indice]['qtd_estoque_compra_valor'] = $arr_estoque[$indice]['Preco un']*($arr_estoque[$indice]['qtd_estoque_compra']/1000);
        $arr_estoque[$indice]['qtd_estoque_consumo_valor'] = $arr_estoque[$indice]['Preco un']*($arr_estoque[$indice]['qtd_estoque_consumo']/1000);
        $arr_estoque[$indice]['qtd_estoque_final_valor'] = $arr_estoque[$indice]['Preco un']*($arr_estoque[$indice]['qtd_estoque_final']/1000);
        $arr_estoque[$indice]['custo'] = $arr_estoque[$indice]['qtd_estoque_ini_valor'] + $arr_estoque[$indice]['qtd_estoque_compra_valor'] - $arr_estoque[$indice]['qtd_estoque_final_valor'];

        echo '   <tr>';
        echo '    <td><strong>'.$arr_estoque[$indice]['Nome'].'</strong></td>';
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$indice]['qtd_estoque_ini']) ? bd2moeda($arr_estoque[$indice]['qtd_estoque_ini']) : "0,00").'</td>';        
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$indice]['qtd_estoque_compra']) ? bd2moeda($arr_estoque[$indice]['qtd_estoque_compra']) : "0,00").'</td>';        
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$indice]['qtd_estoque_consumo']) ? bd2moeda($arr_estoque[$indice]['qtd_estoque_consumo']) : "0,00").'</td>';        
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$indice]['qtd_estoque_final']) ? bd2moeda($arr_estoque[$indice]['qtd_estoque_final']) : "0,00").'</td>';
        echo '    <td align="right">R$ '.(bd2moeda($arr_estoque[$indice]['preco_grama']) ? bd2moeda($arr_estoque[$indice]['preco_grama']) : "0,00").'</td>';
        echo '    <td align="right">'.(bd2moeda($arr_estoque[$indice]['qtd_embalagem']) ? bd2moeda($arr_estoque[$indice]['qtd_embalagem']) : "0,00").'</td>';
        echo '    <td align="right">R$ '.(bd2moeda($arr_estoque[$indice]['Preco un']) ? bd2moeda($arr_estoque[$indice]['Preco un']) : "0,00").'</td>';
        echo '    <td align="right">'.$arr_estoque[$indice]['data'].'</td>';
        echo '    <td align="right">R$ '.(bd2moeda($arr_estoque[$indice]['custo']) ? bd2moeda($arr_estoque[$indice]['custo']) : "0,00").'</td>';
        echo '   </tr>';

        $custo_ingredientes += $arr_estoque[$indice]['custo'];
      }


      echo '   <tr>';
        echo '    <td colspan = "9"  align="right" style="background-color: #E5E5E5;"><strong>Custo total dos ingredientes</strong></td>';
        echo '    <td align="right"><strong>R$ '.(bd2moeda($custo_ingredientes) ? bd2moeda($custo_ingredientes) : "0,00").'</strong></td>';
      echo '   </tr>';

      echo '  </tbody>';    
      echo ' </table>';
      echo '<br />';

      ///////////////////

      $sql_buscar_bebidas = "SELECT ib.cod_bebidas, ib.bebida, ic.cod_conteudos, ic.conteudo, ibic.cod_bebidas_ipi_conteudos FROM ipi_bebidas ib INNER JOIN ipi_bebidas_ipi_conteudos ibic ON (ib.cod_bebidas = ibic.cod_bebidas) INNER JOIN ipi_conteudos ic ON (ibic.cod_conteudos = ic.cod_conteudos) ORDER BY ib.bebida, ic.conteudo";
      $res_buscar_bebidas = mysql_query($sql_buscar_bebidas);
      while($obj_buscar_bebidas = mysql_fetch_object($res_buscar_bebidas))
      {
        $cod_bebidas_ipi_conteudos = $obj_buscar_bebidas->cod_bebidas_ipi_conteudos;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['cod_bebida'] = $obj_buscar_bebidas->cod_bebidas;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['Bebida'] = $obj_buscar_bebidas->bebida;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['cod_conteudos'] = $obj_buscar_bebidas->cod_conteudos;
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['Conteudo'] = $obj_buscar_bebidas->conteudo;


        $sql_buscar_bebidas_estoque = "SELECT SUM(quantidade) AS anterior FROM ipi_estoque WHERE MONTH(data_hora_lancamento) < '".$mes."' AND YEAR(data_hora_lancamento) < '".(date("Y") + 1)."' AND cod_bebidas_ipi_conteudos = '".$cod_bebidas_ipi_conteudos."' AND cod_pizzarias = '$cod_pizzarias'";
        $res_buscar_bebidas_estoque = mysql_query($sql_buscar_bebidas_estoque);
        $obj_buscar_bebidas_estoque = mysql_fetch_object($res_buscar_bebidas_estoque);
        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_ini'] = $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_ini'] + $obj_buscar_bebidas_estoque->anterior;


        $sql_buscar_bebidas_estoque_compra = "SELECT SUM(quantidade) as compra FROM ipi_estoque WHERE quantidade > 0 AND MONTH(data_hora_lancamento) = '".$mes."' AND YEAR(data_hora_lancamento) = '".(date("Y"))."' AND cod_bebidas_ipi_conteudos = '".$cod_bebidas_ipi_conteudos."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_bebidas_estoque_compra = mysql_query($sql_buscar_bebidas_estoque_compra);
        $obj_buscar_bebidas_estoque_compra = mysql_fetch_object($res_buscar_bebidas_estoque_compra);

        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_compra'] = $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_compra'] + $obj_buscar_bebidas_estoque_compra->compra;


        $sql_buscar_bebidas_estoque_consumo = "SELECT SUM(quantidade) as consumo FROM ipi_estoque WHERE quantidade < 0 AND MONTH(data_hora_lancamento) = '".$mes."' AND YEAR(data_hora_lancamento) = '".(date("Y"))."' AND cod_bebidas_ipi_conteudos = '".$cod_bebidas_ipi_conteudos."' AND cod_pizzarias = '$cod_pizzarias'";

        $res_buscar_bebidas_estoque_consumo = mysql_query($sql_buscar_bebidas_estoque_consumo);
        $obj_buscar_bebidas_estoque_consumo = mysql_fetch_object($res_buscar_bebidas_estoque_consumo);

        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo'] = $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo'] + abs($obj_buscar_bebidas_estoque_consumo->consumo);


        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_final'] = $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_ini'] + $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_compra'] - $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_consumo'];

        $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['total_bebidas'] = $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_ini'] + $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_compra'] - $arr_estoque_bebida[$cod_bebidas_ipi_conteudos]['qtd_estoque_final'];
      }

      echo '<br />';

      echo ' <table class="listaEdicao" style="width:1000px;" align="center" cellpadding="0" cellspacing="0">';
      echo '  <thead>';
      echo '   <tr>';
      echo '    <td align="center" width="100">Bebida</td>';
      echo '    <td align="center" width="50">Estoque inicial</td>';
      echo '    <td align="center" width="50">Comprado</td>';
      echo '    <td align="center" width="50">Consumido</td>';
      echo '    <td align="center" width="50">Estoque final</td>';
      echo '    <td align="center" width="50">Preço Un.</td>';
      echo '    <td align="center" width="50">Qtd. Embalagem</td>';
      echo '    <td align="center" width="50">Data de compra</td>';
      echo '    <td align="center" width="50">Custo da bebida</td>';
      echo '   </tr>';
      echo '  </thead>';

      echo '  <tbody>';

      foreach($arr_estoque_bebida as $indice => $valor)
      {
        $sql_preco_bebida = "SELECT (preco_unitario_entrada/quantidade_embalagem_entrada) as preco_unidade, preco_unitario_entrada, (quantidade_embalagem_entrada) as qtd_embalagem, iee.data_hota_entrada_estoque FROM ipi_estoque_entrada iee INNER JOIN ipi_estoque_entrada_itens ieei ON (iee.cod_estoque_entrada = ieei.cod_estoque_entrada) WHERE iee.data_hota_entrada_estoque < '".date("Y")."-".($mes+1)."-01' AND ieei.cod_bebidas_ipi_conteudos = '".$indice."' ORDER BY iee.data_hota_entrada_estoque DESC LIMIT 1";
        //echo $sql_preco_ingrediente.'<br>';
        $res_preco_bebida = mysql_query($sql_preco_bebida);
        $obj_preco_bebida = mysql_fetch_object($res_preco_bebida);
        $arr_estoque_bebida[$indice]['Preco un'] = ($obj_preco_bebida->preco_unidade ? $obj_preco_bebida->preco_unidade : 0);
        $arr_estoque_bebida[$indice]['qtd_embalagem'] = ($obj_preco_bebida->qtd_embalagem ? $obj_preco_bebida->qtd_embalagem : 0);
        $arr_estoque_bebida[$indice]['preco_unitario_entrada'] = ($obj_preco_bebida->preco_unitario_entrada ? $obj_preco_bebida->preco_unitario_entrada : 0);
        $date = strtotime($obj_preco_bebida->data_hota_entrada_estoque); 
        $arr_estoque_bebida[$indice]['data'] = ($date ? date('d/m/Y', $date) : '-');
        $arr_estoque_bebida[$indice]['qtd_estoque_ini_valor'] = $arr_estoque_bebida[$indice]['Preco un']*($arr_estoque_bebida[$indice]['qtd_estoque_ini']);
        $arr_estoque_bebida[$indice]['qtd_estoque_compra_valor'] = $arr_estoque_bebida[$indice]['Preco un']*($arr_estoque_bebida[$indice]['qtd_estoque_compra']);
        $arr_estoque_bebida[$indice]['qtd_estoque_consumo_valor'] = $arr_estoque_bebida[$indice]['Preco un']*($arr_estoque_bebida[$indice]['qtd_estoque_consumo']);
        $arr_estoque_bebida[$indice]['qtd_estoque_final_valor'] = $arr_estoque_bebida[$indice]['Preco un']*($arr_estoque_bebida[$indice]['qtd_estoque_final']);
        $arr_estoque_bebida[$indice]['custo'] = $arr_estoque_bebida[$indice]['qtd_estoque_ini_valor'] + $arr_estoque_bebida[$indice]['qtd_estoque_compra_valor'] - $arr_estoque_bebida[$indice]['qtd_estoque_final_valor'];



        echo '   <tr>';
        echo '    <td><strong>'.$arr_estoque_bebida[$indice]['Bebida'].' - '.$arr_estoque_bebida[$indice]['Conteudo'].'</strong></td>';
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_ini']) ? bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_ini']) : "0,00").'</td>';        
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_compra']) ? bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_compra']) : "0,00").'</td>';        
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_consumo']) ? bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_consumo']) : "0,00").'</td>';        
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_final']) ? bd2moeda($arr_estoque_bebida[$indice]['qtd_estoque_final']) : "0,00").'</td>';
        echo '    <td align="right">R$ '.(bd2moeda($arr_estoque_bebida[$indice]['Preco un']) ? bd2moeda($arr_estoque_bebida[$indice]['Preco un']) : "0,00").'</td>';
        echo '    <td align="right">'.(bd2moeda($arr_estoque_bebida[$indice]['qtd_embalagem']) ? bd2moeda($arr_estoque_bebida[$indice]['qtd_embalagem']) : "0,00").'</td>';
        echo '    <td align="right">'.$arr_estoque_bebida[$indice]['data'].'</td>';
        echo '    <td align="right">R$ '.(bd2moeda($arr_estoque_bebida[$indice]['custo']) ? bd2moeda($arr_estoque_bebida[$indice]['custo']) : "0,00").'</td>';
        echo '   </tr>';


        $custo_bebidas += $arr_estoque_bebida[$indice]['custo'];
      }


      echo '   <tr>';
        echo '    <td colspan = "8"  align="right" style="background-color: #E5E5E5;"><strong>Custo total das bebidas</strong></td>';
        echo '    <td align="right"><strong>R$ '.(bd2moeda($custo_bebidas) ? bd2moeda($custo_bebidas) : "0,00").'</strong></td>';
      echo '   </tr>';

      echo '  </tbody>';    
      echo ' </table>';

      echo '<br />';

      $sql_buscar_faturamento = "SELECT SUM(valor_total) as faturamento FROM ipi_pedidos WHERE MONTH(data_hora_pedido) = '".$mes."' AND YEAR(data_hora_pedido) = '".date('Y')."' AND situacao = 'BAIXADO' AND cod_pizzarias = '".$cod_pizzarias."'";
      $res_buscar_faturamento = mysql_query($sql_buscar_faturamento);
      $obj_buscar_faturamento = mysql_fetch_object($res_buscar_faturamento);

      $custo_total = $custo_ingredientes + $custo_bebidas;

      $cmv_porc = ($custo_total/$obj_buscar_faturamento->faturamento)*100;

      echo ' <table class="listaEdicao" style="width:300px;" align="center" cellpadding="0" cellspacing="0">';
      echo '  <tr>';
      echo '   <td align="right" style="background-color: #E5E5E5;" width="100"><strong>CMV</strong></td>';
      echo '   <td align="left"><strong>R$ '.(bd2moeda($custo_total) ? bd2moeda($custo_total) : 0).'</strong></td>';     
      echo '  </tr>';
      echo '  <tr>';
      echo '   <td align="right" style="background-color: #E5E5E5;" width="100"><strong>Faturamento</strong></td>'; 
      echo '   <td align="left"><strong>R$ '.(bd2moeda($obj_buscar_faturamento->faturamento) ? bd2moeda($obj_buscar_faturamento->faturamento) : "0,00").'</strong></td>';      
      echo '  </tr>';
      echo '  <tr>';
      echo '   <td align="right" style="background-color: #E5E5E5;" width="100"><strong>% sobre Fatu.</strong></td>'; 
      echo '   <td align="left" style="color: #F00;"><strong>'.(bd2moeda($cmv_porc) ? bd2moeda($cmv_porc) : "0,00").' %</strong></td>';
      echo '  </tr>';  
      echo ' </table>';
      echo '</div>';
      echo '<br />';



      /*echo '<pre>';
      print_r($arr_estoque_bebida);
      echo '<br ><br >//////////////////////////////////////////////<br ><br >';
      print_r($arr_estoque);
      echo '<pre>';*/


      desconectar_bd($con);
    }
    else
      echo 'Selecione a '.TIPO_EMPRESA.' e o mês!';
  ?>
</div>

<? rodape(); ?>
