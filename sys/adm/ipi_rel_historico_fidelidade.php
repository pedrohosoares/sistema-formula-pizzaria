<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Fidelide
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Histórico de Pedidos com Fidelidade');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;

switch($acao) {
  case 'detalhes':
    $codigo = validaVarPost($chave_primaria);
    
    $con = conectabd();
    
    $objBuscaDetalhamento = executaBuscaSimples("SELECT * FROM ipi_pedidos WHERE cod_pedidos = $codigo", $con);
    //echo "<br>SELECT * FROM ipi_pedidos WHERE cod_pedidos = $codigo";
    
    $objBuscaDetalhamentoCPFPaulista = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = $codigo AND chave = 'CPF_NOTA_PAULISTA'", $con);
    
    echo sprintf('<h1 align="center"><b>Pedido nº %08d</b></h1>', $codigo);
    
    echo '<br><br>';
    
    echo '<p><b>Cliente</b></p>';
    echo '<hr noshade="noshade" color="#1c4b93"/>';
    echo '<br>';
    
    $objBuscaCliente = executaBuscaSimples("SELECT * FROM ipi_clientes WHERE cod_clientes = ".$objBuscaDetalhamento->cod_clientes, $con);
    
    echo '<table>';
    
    echo '<tr>';
    echo '<td><b>Nome:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>CPF:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>CPF Paulista:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>Celular:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaCliente->nome).'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.$objBuscaCliente->cpf.'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.$objBuscaDetalhamentoCPFPaulista->conteudo.'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.$objBuscaCliente->celular.'</td>';
    echo '</tr>';
    
    echo '</table>';
    
    echo '<br><br>';
    
    //////////////////////////////
    
    echo '<p><b>Entrega</b></p>';
    echo '<hr noshade="noshade" color="#1c4b93"/>';
    echo '<br>';
    
    echo '<table>';
    
    echo '<tr>';
    echo '<td><b>Endereço:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>Número:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>Complemento:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->endereco).'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->numero).'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->complemento).'</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    echo '<tr>';
    echo '<td><b>Bairro:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>Cidade:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>Estado:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->bairro).'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->cidade).'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.$objBuscaDetalhamento->estado.'</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    echo '<tr>';
    echo '<td colspan="5"><b>CEP:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.$objBuscaDetalhamento->cep.'</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    echo '<tr>';
    echo '<td colspan="5"><b>Destino:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->tipo_entrega).'</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    $objBuscaEntregador = executaBuscaSimples("SELECT * FROM ipi_entregadores WHERE cod_entregadores = ".$objBuscaDetalhamento->cod_entregadores, $con);
    
    echo '<tr>';
    echo '<td colspan="5"><b>Entregador:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaEntregador->nome).'</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    echo '<tr>';
    echo '<td colspan="5"><b>Situação:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->situacao).'</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    echo '<tr>';
    echo '<td colspan="5"><b>Forma de Pagamento:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaDetalhamento->forma_pg).'</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    $objBuscaTroco = executaBuscaSimples("SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = ".$objBuscaDetalhamento->cod_pedidos." AND chave = 'TROCO'", $con);
    
    echo '<tr>';
    echo '<td colspan="5"><b>Troco:</b></td>';
    echo '</tr>';
    echo '<tr>';
    
    if($objBuscaTroco->conteudo != '')
      echo '<td>'.bd2texto($objBuscaTroco->conteudo).'</td>';
    else
      echo '<td>Não</td>';
      
    echo '</tr>';
    
    echo '<tr><td colspan="5">&nbsp;</td></tr>';
    
    echo '<tr>';
    echo '<td colspan="5"><b>Valor do Pedido:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2moeda($objBuscaDetalhamento->valor_total).'</td>';
    echo '</tr>';
    
    echo '</table>';
    
    echo '<br><br>';
    
    //////////////////////////////
    
    $SqlBuscaPedidosPizzas = 'SELECT * FROM ipi_pedidos_pizzas p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) LEFT JOIN ipi_tipo_massa m ON (p.cod_tipo_massa = m.cod_tipo_massa) WHERE p.cod_pedidos = ' . $objBuscaDetalhamento->cod_pedidos . ' ORDER BY cod_pedidos_pizzas';
    $resBuscaPedidosPizzas = mysql_query($SqlBuscaPedidosPizzas);
    //echo $SqlBuscaPedidosPizzas;
    $num_pizza = 1;
    while($objBuscaPedidosPizzas = mysql_fetch_object($resBuscaPedidosPizzas)) 
    {
        echo '<p><b>';
        echo 'Pizza '.$num_pizza;
        if($objBuscaPedidosPizzas->promocional)
            echo ' - (GRÁTIS)';
        else if($objBuscaPedidosPizzas->fidelidade)
            echo ' - (FIDELIDADE)';  
        echo '</b></p>';
            
      
      echo '<hr noshade="noshade" color="#1c4b93"/>';
      echo '<br>';
      
      echo '<table>';
    
      echo '<tr>';
      echo '<td><b>Tamanho da Pizza:</b></td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td><b>Quantidade de Sabores:</b></td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td><b>Recheio da Borda:</b></td>';
      echo '</tr>';
        
      echo '<tr>';
      echo '<td>'.$objBuscaPedidosPizzas->tamanho.'</td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td>'.$objBuscaPedidosPizzas->quant_fracao.' '.$valorQuantFracao.'</td>';
      echo '<td width="50">&nbsp;</td>';
        
      $objBuscaPedidosBorda = executaBuscaSimples("SELECT * FROM ipi_pedidos_bordas p INNER JOIN ipi_bordas b ON (p.cod_bordas = b.cod_bordas) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
        
      if($objBuscaPedidosBorda->borda) {
        if($objBuscaPedidosBorda->promocional)
          $valorPedidosBorda = 'GRÁTIS';
        else if($objBuscaPedidosBorda->fidelidade)
          $valorPedidosBorda = 'FIDELIDADE';  
        else
          $valorPedidosBorda = 'R$'.bd2moeda($objBuscaPedidosBorda->preco);
          
        echo '<td>'.$objBuscaPedidosBorda->borda.' ('.$valorPedidosBorda.')</td>';
      }
      else {
        echo '<td>Não</td>';
      }
      echo '</tr>';
        
      echo '<tr><td colspan="5">&nbsp;</td></tr>';
        
      echo '<tr>';
      echo '<td><b>Borda salpicada com Gergelim:</b></td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td colspan="3"><b>Tipo da Massa:</b></td>';
      echo '</tr>';
    
      echo '<tr>';
        
      $objBuscaPedidosAdicional = executaBuscaSimples("SELECT * FROM ipi_pedidos_adicionais p INNER JOIN ipi_adicionais a ON (p.cod_adicionais = a.cod_adicionais) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
       
      if($objBuscaPedidosAdicional->adicional) 
      {
        if($objBuscaPedidosAdicional->promocional)
          $valorPedidosAdicional = 'GRÁTIS';
        else if($objBuscaPedidosAdicional->fidelidade)
          $valorPedidosAdicional = 'FIDELIDADE';  
        else
          $valorPedidosAdicional = 'R$'.bd2moeda($objBuscaPedidosAdicional->preco);
            
        echo '<td>'.$objBuscaPedidosAdicional->adicional.' ('.$valorPedidosAdicional.')</td>';
      }
      else 
      {
            echo '<td>Não</td>';
      }
        
      echo '<td width="50">&nbsp;</td>';
        
      echo '<td colspan="3">'.$objBuscaPedidosPizzas->tipo_massa;
                
      if($objBuscaPedidosPizzas->preco_massa > 0)
      {
        echo '&nbsp;(' . bd2moeda($objBuscaPedidosPizzas->preco_massa) . ')';   
      }
        
      echo '</td>';
        
      echo '</tr>';
      echo '</table>';
      
      $SqlBuscaPedidosFracoes = "SELECT * FROM ipi_pedidos_fracoes fr INNER JOIN ipi_pizzas p ON (fr.cod_pizzas = p.cod_pizzas) WHERE fr.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND fr.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas." ORDER BY fracao";
      $resBuscaPedidosFracoes = mysql_query($SqlBuscaPedidosFracoes);
      
      while($objBuscaPedidosFracoes = mysql_fetch_object($resBuscaPedidosFracoes)) {
        echo '<br><br><b class="laranja">'.$objBuscaPedidosFracoes->fracao.'º sabor:</b> <b>'.$objBuscaPedidosFracoes->pizza.'</b>';
        
        echo '<br><br><b>Ingredientes Retirados:</b>';
        
        // Consulta que retorna todos os ingredientes da pizza
        //$SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 1 AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosFracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$objBuscaPedidosFracoes->cod_pedidos." AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';
        
        // Consulta que retorna todos os ingredientes retirados
        $SqlBuscaPedidosIngredientes = "SELECT * FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_pizzas p ON (i.cod_ingredientes = p.cod_ingredientes) WHERE p.cod_ingredientes NOT IN (SELECT pi.cod_ingredientes FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_pedidos_fracoes pf ON (pi.cod_pedidos_fracoes = pf.cod_pedidos_fracoes AND pi.cod_pedidos_pizzas = pf.cod_pedidos_pizzas AND pi.cod_pedidos = pf.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON(pf.cod_pedidos = pp.cod_pedidos AND pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas) WHERE pi.cod_pedidos = ".$objBuscaDetalhamento->cod_pedidos." AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas." AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes." AND pi.ingrediente_padrao = 1) AND p.cod_pizzas = ".$objBuscaPedidosFracoes->cod_pizzas.' ORDER BY ingrediente';
        $resBuscaPedidosIngredientes = mysql_query($SqlBuscaPedidosIngredientes);
        
        echo '<ol style="margin-bottom: 10px; margin-top: 10px; margin-left: 40px;">';
        
        while($objBuscaPedidosIngredientes = mysql_fetch_object($resBuscaPedidosIngredientes)) {
          echo '<li>'.$objBuscaPedidosIngredientes->ingrediente.'</li>';
        }
        
        echo '</ol>';
        
        echo '<b>Ingredientes Adicionados:</b>';
        
        $SqlBuscaPedidosExtra = "SELECT * FROM ipi_pedidos_ingredientes pi INNER JOIN ipi_ingredientes ig ON (pi.cod_ingredientes = ig.cod_ingredientes) WHERE pi.ingrediente_padrao = 0 AND pi.cod_pedidos_pizzas = ".$objBuscaPedidosFracoes->cod_pedidos_pizzas." AND pi.cod_pedidos = ".$objBuscaPedidosFracoes->cod_pedidos." AND pi.cod_pedidos_fracoes = ".$objBuscaPedidosFracoes->cod_pedidos_fracoes.' ORDER BY ingrediente';
        $resBuscaPedidosExtra = mysql_query($SqlBuscaPedidosExtra);
        
        echo '<ol style="margin-bottom: 10px; margin-top: 10px; margin-left: 40px;">';
        
        while($objBuscaPedidosExtra = mysql_fetch_object($resBuscaPedidosExtra)) {
          echo '<li>'.$objBuscaPedidosExtra->ingrediente.'</li>';
        }
        
        echo '</ol>';
      }
      
      $num_pizza++;
    }
    
    echo '<br><br>';
    
    /////////////////////////
    
    $SqlBuscaPedidosBebidas = "SELECT *, p.preco AS pedidos_preco FROM ipi_pedidos_bebidas p INNER JOIN ipi_bebidas_ipi_conteudos bc ON (p.cod_bebidas_ipi_conteudos = bc.cod_bebidas_ipi_conteudos) INNER JOIN ipi_bebidas b ON (bc.cod_bebidas = b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos = c.cod_conteudos) WHERE cod_pedidos = ".$objBuscaDetalhamento->cod_pedidos;
    $resBuscaPedidosBebidas = mysql_query($SqlBuscaPedidosBebidas);
    
    echo '<p><b>Bebida</b></p>';
    echo '<hr noshade="noshade" color="#1c4b93"/>';
    echo '<br>';
  
    echo '<table>';
    echo '<tr>';
    echo '<td><b>Quantidade:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>Bebida:</b></td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td><b>Conteúdo:</b></td>';
    echo '</tr>';
    
    while($objBuscaPedidosBebidas = mysql_fetch_object($resBuscaPedidosBebidas)) 
    {
        echo '<tr>';
        echo '<td>'.$objBuscaPedidosBebidas->quantidade.'</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>'.$objBuscaPedidosBebidas->bebida.'</td>';
        echo '<td width="50">&nbsp;</td>';
        echo '<td>'.$objBuscaPedidosBebidas->conteudo;
        if($objBuscaPedidosBebidas->promocional)
            echo ' - (GRÁTIS)';
        else if($objBuscaPedidosBebidas->fidelidade)
            echo ' - (FIDELIDADE)';        
        echo '</td>';
        echo '</tr>';
        echo '<tr><td colspan="7"></td></tr>';
    }
    
    echo '</table>';
    
    echo '<br><br><h3><a href="ipi_rel_historico_fidelidade.php">&laquo; Voltar</a></h3><br><br>';
    
    desconectabd($con);
  break;
}

?>

<? if($acao != 'detalhes'): ?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>

<script>

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

window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 

</script>

<?
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : '';
$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td class=" tdbt">&nbsp;</td>
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
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
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
    <td class="legenda tdbl sep" align="right"><label for="origem">Origem:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="origem" id="origem">
        <option value="TODOS" <? if($origem == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NET" <? if($origem == 'NET') echo 'selected' ?>>Net</option>
        <option value="TEL" <? if($origem == 'TEL') echo 'selected' ?>>Tel</option>
      </select>
    </td>
  </tr>
  
  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
$con = conectabd();

$SqlBuscaRegistros = "SELECT p.*, c.*, pi.nome AS pi_nome, p.situacao AS pedidos_situacao FROM $tabela p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE pontos_fidelidade_total<>'' AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";

if($pedido > 0)
  $SqlBuscaRegistros .= " AND p.cod_pedidos = '$pedido'";
  
if($cliente != '')
  $SqlBuscaRegistros .= " AND c.nome LIKE '%$cliente%'";
  
if($cod_pizzarias > 0)
  $SqlBuscaRegistros .= " AND p.cod_pizzarias = '$cod_pizzarias'";

if($origem != 'TODOS')
  $SqlBuscaRegistros .= " AND p.origem_pedido = '$origem'";
  
if(($data_inicial) && ($data_final)) {
  $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
  $data_final_sql = data2bd($data_final).' 23:59:59';
  
  $SqlBuscaRegistros .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";
}
  
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

$SqlBuscaRegistros .= ' ORDER BY nome LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

//echo $SqlBuscaRegistros;

echo "<center><b>".$numBuscaRegistros." registro(s) encontrado(s)</center></b><br>";

if ((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir')) $pagina--;

echo '<center>';

$numpag = ceil(((int) $numBuscaRegistros) / ((int) $quant_pagina));

for ($b = 0; $b < $numpag; $b++) {
  echo '<form name="frmPaginacao'.$b.'" method="post">';
  echo '<input type="hidden" name="pagina" value="'.$b.'>';
  echo '<input type="hidden" name="acao" value="buscar">';
  
  echo '<input type="hidden" name="cod_pedidos" value="'.$cod_pedidos.'">';
  echo '<input type="hidden" name="cliente" value="'.$cliente.'">';
  echo '<input type="hidden" name="data_inicial" value="'.$data_inicial.'">';
  echo '<input type="hidden" name="data_final" value="'.$data_final.'">';
  echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
  echo '<input type="hidden" name="situacao" value="'.$situacao.'">';
  echo '<input type="hidden" name="origem" value="'.$origem.'">';
  
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

?>

<br>

<form name="frmBaixa" method="post">
  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center" width="70">Pedido</td>
        <td align="center">Cliente</td>
        <td align="center" width="100">Bairro</td>
        <td align="center" width="70">Agendado</td>
        <td align="center" width="80"><? echo ucfirst(TIPO_EMPRESA)?></td>
        <td align="center">Horário do Pedido</td>
        <td align="center" width="70">Valor Total</td>
        <td align="center" width="70">Pontos Fidelidade</td>
        <td align="center" width="70">Origem</td>
      </tr>
    </thead>
    <tbody>
  
    <?
  
    while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) 
    {
        echo '<tr>';
        
        echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.sprintf('%08d', $objBuscaRegistros->$chave_primaria).'</a></td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->nome).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->bairro).'</td>';
        if($objBuscaRegistros->agendado == '1')
        {
            echo '<td align="center">'.bd2texto($objBuscaRegistros->horario_agendamento).'</td>';    
        }
        else
        {
            echo '<td align="center">NÃO</td>';
        }
        echo '<td align="center">'.bd2texto($objBuscaRegistros->pi_nome).'</td>';
        echo '<td align="center">'.bd2datahora($objBuscaRegistros->data_hora_pedido).'</td>';
        echo '<td align="center">'.bd2moeda($objBuscaRegistros->valor_total).'</td>';
        echo '<td align="center">'.$objBuscaRegistros->pontos_fidelidade_total.'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->origem_pedido).'</td>';
        echo '</tr>';
    }
    
    desconectabd($con);
    
    ?>
    
    </tbody>
  </table>

  <input type="hidden" name="acao" value="">
</form>

<? endif; ?>

<? rodape(); ?>
