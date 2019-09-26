<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';

cabecalho('Vendas por Atendentens');

$acao = validaVarPost('acao');
$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;
?>

<? if($acao != 'detalhes'): ?>

<?
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : (validaVarGet('p', '/[0-9]+/') ? (int) validaVarGet('p', '/[0-9]+/') : '');

$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_colaboradores = validaVarPost('cod_colaboradores');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
$entrega = validaVarPost('entrega');
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>

<script>
var carregados = new Array();
var qtd_carregados = 0;
function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('situacao')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
   
  if(cInput > 0) {
    if (confirm('Deseja mudar de situação o(s) pedido(s) selecionado(s)?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja mudar de situação (BAIXAR / CANCELAR).');
     
    return false;
  }
}

function editar(cod) {
  var form = new Element('form', {
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input1 = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'acao',
    'value': 'detalhes'
  });
  
  input1.inject(form);
  input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function alterar_visibilidade(id)
{
  if (document.getElementById(id).style.display == 'none')
  {
    document.getElementById(id).style.display = 'table-row';
  }
  else
  {
    document.getElementById(id).style.display = 'none';
  }
}

function carregar_informacoes(id,acao)
{
  
  var cod_pizzarias = '';
  var tipo_entrega = '';
  var data_inicial = '';
  var data_final = '';
  <?
    if($cod_pizzarias > 0)
    {
      echo "cod_pizzarias='$cod_pizzarias';";
    }
    if($entrega!= 'TODOS')
    {
        echo "tipo_entrega='$entrega';";
    }

    if(($data_inicial) && ($data_final)) {
      $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
      $data_final_sql = data2bd($data_final).' 23:59:59';

      echo "data_inicial='$data_inicial_sql';";
      echo "data_final='$data_final_sql';";
  }
 
  ?>

  var url = 'acao='+acao+'&id=' + id+'&cod_pizzarias=' + cod_pizzarias+'&entrega=' + tipo_entrega+'&data_inicial=' + data_inicial+'&data_final=' + data_final;

  if(carregados.indexOf(id) == -1)
  {
    new Request.HTML(
      {
          url: 'ipi_rel_vendas_atendente_ajax.php',
          update: id,
          onComplete: function()
          {
              carregados[qtd_carregados] = id;
              qtd_carregados +=1;
          }
      }).send(url);
  }
}

function carregar_informacoes(id,acao)
{
  var url = 'acao='+acao+'&id=' + id+'&cod_pizzarias=' + cod_pizzarias.value+'&cod_colaboradores=<? echo $cod_colaboradores; ?>';
  new Request.HTML(
  {
      url: 'ipi_rel_vendas_garcom_ajax.php',
      update: id,
  }).send(url);
}

window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
  <?
  if ($cod_pizzarias)
  {
    echo "carregar_informacoes('cod_colaboradores','carregar_colaboradores')";
  }
  ?>
}); 

</script>
<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  
 
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr tdbt"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><?php echo TIPO_EMPRESAS ?>:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_pizzarias" id="cod_pizzarias" onchange="javascript:carregar_informacoes('cod_colaboradores','carregar_colaboradores')">
        <option value="">Todas as <?php echo TIPO_EMPRESAS ?></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
          echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
          
          if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_colaboradores">Colaborador:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_colaboradores" id="cod_colaboradores">
        <option value="">Selecione a <?php echo TIPO_EMPRESAS ?></option>
      </select>
    </td>
  </tr>

<!-- 
  <tr>
    <td class="legenda tdbl" align="right"><label for="situacao">Situação:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">
      <select name="situacao" id=situacao>
        <option value="TODOS" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NOVO" <? if($situacao == 'NOVO') echo 'selected' ?>>Novo</option>
        <option value="IMPRESSO" <? if($situacao == 'IMPRESSO') echo 'selected' ?>>Impresso</option>
        <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
        <option value="CANCELADO" <? if($situacao == 'CANCELADO') echo 'selected' ?>>Cancelado</option>
        <option value="ENVIADO" <? if($situacao == 'ENVIADO') echo 'selected' ?>>Enviado</option>
      </select>
    </td>
  </tr>
   -->

  <!--
  <tr>
    <td class="legenda tdbl sep" align="right"><label for="entrega">Entrega:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="entrega" id="entrega">
        <option value="TODOS" <? if($entrega == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="Entrega" <? if($entrega == 'Entrega') echo 'selected' ?>>Entrega</option>
        <option value="Balcão" <? if($entrega == 'Balcão') echo 'selected' ?>>Balcão</option>
      </select>
    </td>
  </tr>
  -->

  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
/*
  [0][nome]
  [0][bebida]
  [0][produtos]
  [0][taxas]
  [0][ingredientes]
  [0][total]
*/

if($acao!="")
{
  $con = conectabd();
  $arr_colaboradores = array();
  $arr_colaboradores_distintos = array();

  if(($data_inicial) && ($data_final)) {
    $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
    $data_final_sql = data2bd($data_final).' 23:59:59';
    
    $SqlBuscaRegistros .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";

  }
  // BEBIDAS
  $sql_total = "SELECT pb.cod_colaboradores_inclusao, SUM(pb.preco_inteiro) total FROM ipi_pedidos_bebidas pb LEFT JOIN ipi_pedidos p ON (pb.cod_pedidos = p.cod_pedidos) WHERE data_hora_inclusao >= '".$data_inicial_sql."' AND data_hora_inclusao <= '".$data_final_sql."' AND p.situacao != 'CANCELADO' AND pb.situacao_pedidos_bebidas != 'CANCELADO' ";
  if ($cod_colaboradores)
  {
    $sql_total .= " AND pb.cod_colaboradores_inclusao = '".$cod_colaboradores."'";
  }    
  $sql_total .= " GROUP BY pb.cod_colaboradores_inclusao";
  $res_total = mysql_query($sql_total);
  while($obj_total = mysql_fetch_object($res_total)) 
  {
    $arr_colaboradores[$obj_total->cod_colaboradores_inclusao]["bebidas"] = $obj_total->total;
    
    if ( in_array($obj_total->cod_colaboradores_inclusao, $arr_colaboradores_distintos) == false )
    {
      $arr_colaboradores_distintos[] = $obj_total->cod_colaboradores_inclusao;
    }
  }

  //PRODUTOS
  $sql_total = "SELECT pp.cod_colaboradores_inclusao, SUM(pf.preco) total FROM ipi_pedidos_pizzas pp LEFT JOIN ipi_pedidos p ON (pp.cod_pedidos = p.cod_pedidos) LEFT JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) WHERE pp.data_hora_inclusao >= '".$data_inicial_sql."' AND data_hora_inclusao <= '".$data_final_sql."' AND p.situacao != 'CANCELADO' AND pp.situacao_pedidos_pizzas != 'CANCELADO' ";
  if ($cod_colaboradores)
  {
    $sql_total .= " AND pp.cod_colaboradores_inclusao = '".$cod_colaboradores."'";
  }    
  $sql_total .= " GROUP BY pp.cod_colaboradores_inclusao";
  $res_total = mysql_query($sql_total);
  while($obj_total = mysql_fetch_object($res_total)) 
  {
    $arr_colaboradores[$obj_total->cod_colaboradores_inclusao]["produtos"] = $obj_total->total;
    if ( in_array($obj_total->cod_colaboradores_inclusao, $arr_colaboradores_distintos) == false )
    {
      $arr_colaboradores_distintos[] = $obj_total->cod_colaboradores_inclusao;
    }
  }

  //INGREDIENTES
  $sql_total = "SELECT pp.cod_colaboradores_inclusao, SUM(pi.preco) total FROM ipi_pedidos_pizzas pp LEFT JOIN ipi_pedidos p ON (pp.cod_pedidos = p.cod_pedidos) LEFT JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) LEFT JOIN ipi_pedidos_ingredientes pi ON (pf.cod_pedidos_fracoes = pi.cod_pedidos_fracoes) WHERE pp.data_hora_inclusao >= '".$data_inicial_sql."' AND data_hora_inclusao <= '".$data_final_sql."' AND p.situacao != 'CANCELADO' AND pp.situacao_pedidos_pizzas != 'CANCELADO' ";
  if ($cod_colaboradores)
  {
    $sql_total .= " AND pp.cod_colaboradores_inclusao = '".$cod_colaboradores."'";
  }    
  $sql_total .= " GROUP BY pp.cod_colaboradores_inclusao";
  $res_total = mysql_query($sql_total);
  while($obj_total = mysql_fetch_object($res_total)) 
  {
    $arr_colaboradores[$obj_total->cod_colaboradores_inclusao]["ingredientes"] = $obj_total->total;
    if ( in_array($obj_total->cod_colaboradores_inclusao, $arr_colaboradores_distintos) == false )
    {
      $arr_colaboradores_distintos[] = $obj_total->cod_colaboradores_inclusao;
    }
  }

  //TAXAS
  $sql_total = "SELECT pt.cod_colaboradores_inclusao, SUM(pt.preco_total) total FROM ipi_pedidos_taxas pt LEFT JOIN ipi_pedidos p ON (pt.cod_pedidos = p.cod_pedidos) WHERE data_hora_inclusao >= '".$data_inicial_sql."' AND data_hora_inclusao <= '".$data_final_sql."' AND p.situacao != 'CANCELADO' AND pt.situacao_pedidos_taxas != 'CANCELADO' ";
  if ($cod_colaboradores)
  {
    $sql_total .= " AND pt.cod_colaboradores_inclusao = '".$cod_colaboradores."'";
  }    
  $sql_total .= " GROUP BY pt.cod_colaboradores_inclusao";

  $res_total = mysql_query($sql_total);
  //echo $sql_total;
  while($obj_total = mysql_fetch_object($res_total)) 
  {
    $arr_colaboradores[$obj_total->cod_colaboradores_inclusao]["taxas"] = $obj_total->total;
    //echo "<br />X: ".$arr_colaboradores[$obj_total->cod_colaboradores_inclusao]["taxas"];
    if ( in_array($obj_total->cod_colaboradores_inclusao, $arr_colaboradores_distintos) == false )
    {
      $arr_colaboradores_distintos[] = $obj_total->cod_colaboradores_inclusao;
    }
  }

  if (count($arr_colaboradores_distintos) > 0)
  {
    //NOMES
    $sql_colaboradores = "SELECT c.cod_colaboradores, c.nome FROM ipi_colaboradores c WHERE c.cod_tipo_colaboradores = 1 AND c.cod_colaboradores IN (".implode(",", $arr_colaboradores_distintos).")";
    //echo $sql_colaboradores;
    $res_colaboradores = mysql_query($sql_colaboradores);
    $num_colaboradores = mysql_num_rows($res_colaboradores);

    while ( $obj_colaboradores = mysql_fetch_object($res_colaboradores) )
    {
      $arr_colaboradores[$obj_colaboradores->cod_colaboradores]["nome"] = $obj_colaboradores->nome;
    }


    //TOTAL
    foreach ($arr_colaboradores as $key => $col)
    {
      //echo "<Br>Y: ".$col." - ".$key;
      $arr_colaboradores[$key]["total"] = $arr_colaboradores[$key]["bebidas"] + $arr_colaboradores[$key]["produtos"] + $arr_colaboradores[$key]["ingredientes"] + $arr_colaboradores[$key]["taxas"];
    }
  }

/*
  echo "<pre>";
  print_r($arr_colaboradores);
  echo "</pre>";

  $SqlBuscaRegistros = "SELECT count(p.cod_pedidos) as qtd_pedidos,usu.cod_usuarios, usu.nome as nome_atendente,(SELECT data_hora_pedido from ipi_pedidos where cod_pedidos = p.cod_pedidos ORDER BY cod_pedidos DESC LIMIT 1) as data_hora_ultimo_pedido FROM $tabela p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) inner join nuc_usuarios usu on usu.cod_usuarios = p.cod_usuarios_pedido WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") and p.situacao='BAIXADO' ";

  if($cod_pizzarias > 0)
  {
    $SqlBuscaRegistros .= " AND p.cod_pizzarias = '$cod_pizzarias'";
  }

  if($situacao != 'TODOS')
  {
    $SqlBuscaRegistros .= " AND p.situacao = '$situacao'";
  }
    


  if($entrega!= 'TODOS')
  {
    $SqlBuscaRegistros .= " AND p.tipo_entrega = '$entrega'";
   
  }

  if(($data_inicial) && ($data_final)) {
    $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
    $data_final_sql = data2bd($data_final).' 23:59:59';
    
    $SqlBuscaRegistros .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";

  }
  $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
  $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

  $SqlBuscaRegistros .= ' GROUP BY usu.cod_usuarios ORDER BY qtd_pedidos DESC,usu.nome LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
  $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
  $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

  //echo $SqlBuscaRegistros;

  echo "<center><b>".$numBuscaRegistros." atendente(s) encontrado(s)</center></b><br>";

  if ((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir')) $pagina--;

  echo '<center>';

  $numpag = ceil(((int) $numBuscaRegistros) / ((int) $quant_pagina));

  for ($b = 0; $b < $numpag; $b++) {
    echo '<form name="frmPaginacao'.$b.'" method="post">';
    echo '<input type="hidden" name="pagina" value="'.$b.'">';
    echo '<input type="hidden" name="acao" value="buscar">';
    
    echo '<input type="hidden" name="cod_pedidos" value="'.$cod_pedidos.'">';
    echo '<input type="hidden" name="cliente" value="'.$cliente.'">';
    echo '<input type="hidden" name="data_inicial" value="'.$data_inicial.'">';
    echo '<input type="hidden" name="data_final" value="'.$data_final.'">';
    echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
    echo '<input type="hidden" name="situacao" value="'.$situacao.'">';
    echo '<input type="hidden" name="origem" value="'.$origem.'">';
    echo '<input type="hidden" name="entrega" value="'.$entrega.'">';
    
    echo "</form>";
  }

  if ($pagina != 0)
    echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.($pagina - 1).'.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
  else
    echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';

  for ($b = 0; $b < $numpag; $b++) {
    if ($b != 0)
      echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
    
    if ($pagina != $b)
      echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.$b.'.submit();">'.($b + 1).'</a>';
    else
      echo '<span><b>'.($b + 1).'</b></span>';
  }

  if (($quant_pagina == $linhasBuscaRegistros) && ((($quant_pagina * $pagina) + $quant_pagina) != $numBuscaRegistros))
    echo '<a href="javascript:;" onclick="javascript:frmPaginacao'.($pagina + 1).'.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
  else
    echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';

  echo '</center>';
*/

}
?>
  <table class="listaEdicao" cellpadding="0" cellspacing="0" width="900">
    
    <thead>
      <tr>
        <td align="center" width="16%">Garçom</td>
        <td align="center" width="16%">Bebidas</td>
        <td align="center" width="16%">Produtos</td>
        <td align="center" width="16%">Ingredientes Extras</td>
        <td align="center" width="16%">Taxas</td>
        <td align="center" width="16%">Total</td>
      </tr>
    </thead>

    <tbody>
    <?
    if($acao!="")
    {
      foreach ($arr_colaboradores as $key => $col)
      {
        echo '<tr>';
        echo "<td>".(isset($arr_colaboradores[$key]["nome"]) ? $arr_colaboradores[$key]["nome"] : "Colaborador Excluído" )."</td>";
        echo "<td align='right'>".bd2moeda($arr_colaboradores[$key]["bebidas"])."</td>";
        echo "<td align='right'>".bd2moeda($arr_colaboradores[$key]["produtos"])."</td>";
        echo "<td align='right'>".bd2moeda($arr_colaboradores[$key]["ingredientes"])."</td>";
        echo "<td align='right'>".bd2moeda($arr_colaboradores[$key]["taxas"])."</td>";
        echo "<td align='right'>".bd2moeda($arr_colaboradores[$key]["total"])."</td>";
        echo '</tr>';
        $total_bebidas += $arr_colaboradores[$key]["bebidas"];
        $total_produtos += $arr_colaboradores[$key]["produtos"];
        $total_ingredientes += $arr_colaboradores[$key]["ingredientes"];
        $total_taxas += $arr_colaboradores[$key]["taxas"];
        $total_total += $arr_colaboradores[$key]["total"];
      }
    }

    ?>
    </tbody>

    <tfoot>
      <?php
      echo '<tr>';
      echo "<td align='right'><strong>Total:</strong></td>";
      echo "<td align='right'>".bd2moeda($total_bebidas)."</td>";
      echo "<td align='right'>".bd2moeda($total_produtos)."</td>";
      echo "<td align='right'>".bd2moeda($total_ingredientes)."</td>";
      echo "<td align='right'>".bd2moeda($total_taxas)."</td>";
      echo "<td align='right'>".bd2moeda($total_total)."</td>";
      echo '</tr>';
      ?>
    </tfoot>

  </table>


<? endif; ?>

<? rodape(); ?>
