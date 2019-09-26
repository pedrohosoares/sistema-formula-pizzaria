<?php

/**
 * ipi_rel_sabores_vendidos.php: Sabores Mais Vendidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Sabores Mais Vendidos');

$acao = validaVarPost('acao');

$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;

switch($acao) {
  case 'reimprimir':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET reimpressao = 1 WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('O(s) pedido(s) foram definidos como REIMPRESSÃO com sucesso!');
    else
      mensagemErro('Erro ao REIMPRIMIR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'impresso':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET situacao = 'IMPRESSO' WHERE $chave_primaria IN ($indicesSql) AND situacao = 'NOVO'";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('O(s) pedido(s) foram definidos como IMPRESSOS com sucesso!');
    else
      mensagemErro('Erro ao redefinir IMPRESSO o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'agendamento':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET agendado = 0, horario_agendamento = '00:00:00' WHERE $chave_primaria IN ($indicesSql) AND situacao = 'NOVO'";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('O agendamento do(s) pedido(s) foram apagados com sucesso!');
    else
      mensagemErro('Erro ao apagar agendamento do pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'cancelar':
    $codigo = validaVarPost($chave_primaria);
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET situacao = 'CANCELADO', data_hora_baixa = NOW() WHERE $chave_primaria IN ($indicesSql)";
    $SqlEstornoFidelidade = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, data_validade, pontos) (SELECT cod_clientes, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), pontos_fidelidade_total FROM $tabela WHERE $chave_primaria IN ($indicesSql))";
    
    if (mysql_query($SqlUpdate) && mysql_query($SqlEstornoFidelidade))
      mensagemOk('O pedido foi CANCELADO com sucesso!');
    else
      mensagemErro('Erro ao CANCELAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'detalhes':
    $codigo = validaVarPost($chave_primaria);
    
    $con = conectabd();
    
    $objBuscaDetalhamento = executaBuscaSimples("SELECT * FROM ipi_pedidos WHERE cod_pedidos = $codigo", $con);
    
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
    
    $num_pizza = 1;
    while($objBuscaPedidosPizzas = mysql_fetch_object($resBuscaPedidosPizzas)) {
      echo '<p><b>Pizza '.$num_pizza.'</b></p>';
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
       
      if($objBuscaPedidosAdicional->adicional) {
        if($objBuscaPedidosAdicional->promocional)
          $valorPedidosAdicional = 'GRÁTIS';
        else if($objBuscaPedidosAdicional->fidelidade)
          $valorPedidosAdicional = 'FIDELIDADE';  
        else
          $valorPedidosAdicional = 'R$'.bd2moeda($objBuscaPedidosAdicional->preco);
            
        echo '<td>'.$objBuscaPedidosAdicional->adicional.' ('.$valorPedidosAdicional.')</td>';
      }
      else {
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
    
    while($objBuscaPedidosBebidas = mysql_fetch_object($resBuscaPedidosBebidas)) {
      echo '<tr>';
      echo '<td>'.$objBuscaPedidosBebidas->quantidade.'</td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td>'.$objBuscaPedidosBebidas->bebida.'</td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td>'.$objBuscaPedidosBebidas->conteudo.'</td>';
      echo '</tr>';
      
      echo '<tr><td colspan="7"></td></tr>';
    }
    
    echo '</table>';
    
    echo '<br><br><h3><a href="ipi_rel_historico_pedidos.php">&laquo; Voltar</a></h3><br><br>';
    
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

function cancelar() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "cancelar";
    document.frmBaixa.submit();
  }
}

function reimprimir() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "reimprimir";
    document.frmBaixa.submit();
  }
}

function impresso() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "impresso";
    document.frmBaixa.submit();
  }
}

function agendamento() {
  if(verificaCheckbox(document.frmBaixa)) {
    document.frmBaixa.acao.value = "agendamento";
    document.frmBaixa.submit();
  }
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
$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('01/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('t/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$cod_tamanhos = validaVarPost('cod_tamanhos');
$cod_tipo_pizza = validar_var_post('cod_tipo_pizza');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
$promocional = validaVarPost('promocional');
$ordenacao = validaVarPost('ordenacao');
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <!-- 
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="pedido">Código do Pedido:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbt tdbr"><input class="requerido" type="text" name="pedido" id="pedido" size="60" value="<? echo $pedido ?>" onkeypress="return ApenasNumero(event)"></td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="cliente">Cliente:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="cliente" id="cliente" size="60" value="<? echo $cliente ?>"></td>
  </tr>
   -->
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="data_inicial">Data Inicial:</label></td>
    <td class="tdbt">&nbsp;</td>
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
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";

        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
          echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
          
          if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
        }
        
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_tamanhos">Tamanho:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_tamanhos" id="cod_tamanhos">
        <option value="">Todos os Tamanhos</option>
        <?
        $sql_busca_tamanhos = "SELECT * FROM ipi_tamanhos";
        $res_busca_tamanhos = mysql_query($sql_busca_tamanhos);
        while($obj_busca_tamanhos = mysql_fetch_object($res_busca_tamanhos)) 
        {
            echo '<option value="'.$obj_busca_tamanhos->cod_tamanhos.'" ';
            if($obj_busca_tamanhos->cod_tamanhos == $cod_tamanhos)
                echo 'selected';
            echo '>'.bd2texto($obj_busca_tamanhos->tamanho).'</option>';
        }
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_tipo_pizza">Tipo de Produto:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_tipo_pizza" id="cod_tipo_pizza">
        <option value="">Todos os Tipos</option>
        <?
        $sql_busca_tamanhos = "SELECT * FROM ipi_tipo_pizza WHERE situacao = 'ATIVO' ORDER BY tipo_pizza";
        $res_busca_tamanhos = mysql_query($sql_busca_tamanhos);
        while($obj_busca_tamanhos = mysql_fetch_object($res_busca_tamanhos)) 
        {
            echo '<option value="'.$obj_busca_tamanhos->cod_tipo_pizza.'" ';
            if($obj_busca_tamanhos->cod_tipo_pizza == $cod_tipo_pizza)
                echo 'selected';
            echo '>'.bd2texto($obj_busca_tamanhos->tipo_pizza).'</option>';
        }
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class="legenda tdbl" align="right"><label for="situacao">Situação:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">
    <?
    if (!$situacao)
        $situacao = "BAIXADO";
    ?>
      <select name="situacao" id=situacao>
        <option value="TODOS" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="CANCELADO" <? if($situacao == 'CANCELADO') echo 'selected' ?>>Cancelado</option>
        <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="origem">Origem:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="origem" id="origem">
        <option value="TODOS" <? if($origem == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NET" <? if($origem == 'NET') echo 'selected' ?>>Net</option>
        <option value="TEL" <? if($origem == 'TEL') echo 'selected' ?>>Tel</option>
      </select>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl sep" align="right"><label for="promocional">Promocional:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr">
      <select name="promocional" id="promocional">
        <option value="TODOS" <? if($promocional == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="Sim" <? if($promocional == 'Sim') echo 'selected' ?>>Sim</option>
        <option value="Não" <? if($promocional == 'Não') echo 'selected' ?>>Não</option>
      </select>
    </td>
  </tr>

     <tr>
    <td class="legenda tdbl sep" align="right"><label for="ordenacao">Ordenados por:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="ordenacao" id="ordenacao">
        <option value="pizza" <? if($ordenacao == 'bebida') echo 'selected' ?>>Alfabética</option>
        <option value="quantidade" <? if($ordenacao == 'quantidade') echo 'selected' ?>>Mais Vendidos</option>
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

$SqlBuscaRegistros = "SELECT p.pizza, p.tipo, tp.tipo_pizza, (SELECT sum(1/pp.quant_fracao) FROM ipi_pedidos_fracoes pf INNER JOIN ipi_pedidos_pizzas pp ON (pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas AND pf.cod_pedidos = pp.cod_pedidos) INNER JOIN ipi_pedidos pe ON (pp.cod_pedidos = pe.cod_pedidos) WHERE pf.cod_pizzas=p.cod_pizzas AND pe.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";

if($origem != 'TODOS')
    $SqlBuscaRegistros .= " AND pe.origem_pedido = '$origem'";
    
if($cod_pizzarias)
    $SqlBuscaRegistros .= " AND pe.cod_pizzarias = '$cod_pizzarias'";
    
if($cod_tamanhos)
    $SqlBuscaRegistros .= " AND pp.cod_tamanhos = '$cod_tamanhos'";

if($cod_tipo_pizza)
    $SqlBuscaRegistros .= " AND p.cod_tipo_pizza = '$cod_tipo_pizza'";
    
if(($data_inicial) && ($data_final)) 
{
    $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
    $data_final_sql = data2bd($data_final).' 23:59:59';
    $SqlBuscaRegistros .= " AND pe.data_hora_pedido >= '$data_inicial_sql' AND pe.data_hora_pedido <= '$data_final_sql'";
}

if($promocional == 'Sim')
  $SqlBuscaRegistros .= " AND pp.fidelidade = 1 AND pp.promocional = 1";
else if($promocional == 'Não')
  $SqlBuscaRegistros .= " AND pp.fidelidade = 0 AND pp.promocional = 0";

if($situacao != 'TODOS')
  $SqlBuscaRegistros .= " AND pe.situacao = '$situacao'";


$SqlBuscaRegistros .= " ) quantidade FROM ipi_pizzas p LEFT JOIN ipi_tipo_pizza tp ON (p.cod_tipo_pizza = tp.cod_tipo_pizza)";

if($cod_tipo_pizza)
    $SqlBuscaRegistros .= " WHERE p.cod_tipo_pizza = '$cod_tipo_pizza'";

if ($ordenacao=="")
{
  $ordenacao = "pizza";
}

$SqlBuscaRegistros .= " ORDER BY ".( $ordenacao =="pizza" ?  $ordenacao:  $ordenacao.' DESC'  );
// echo $SqlBuscaRegistros;
//$SqlBuscaRegistros = "SELECT p.*, c.*, pi.nome AS pi_nome, p.situacao AS pedidos_situacao FROM $tabela p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE 1=1 ";

/*
if($pedido > 0)
  $SqlBuscaRegistros .= " AND p.cod_pedidos = '$pedido'";
  
if($cliente != '')
  $SqlBuscaRegistros .= " AND c.nome LIKE '%$cliente%'";
  
if($cod_pizzarias > 0)
  $SqlBuscaRegistros .= " AND p.cod_pizzarias = '$cod_pizzarias'";

if($situacao != 'TODOS')
  $SqlBuscaRegistros .= " AND p.situacao = '$situacao'";
  
if($origem != 'TODOS')
  $SqlBuscaRegistros .= " AND p.origem_pedido = '$origem'";

if(($data_inicial) && ($data_final)) {
  $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
  $data_final_sql = data2bd($data_final).' 23:59:59';
  
  $SqlBuscaRegistros .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";
}
  
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

$SqlBuscaRegistros .= ' ORDER BY cod_pedidos LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);
*/



$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

//echo $SqlBuscaRegistros;

/*
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
*/
?>

<br>

<form name="frmBaixa" method="post">
<!-- 
  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Reimprimir" onclick="reimprimir()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Impresso" onclick="impresso()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Imprimir Agora" onclick="agendamento()"></td>
      <td align="left"><input class="botaoAzul" style="font-weight: bold; color: red;" type="button" value="Cancelar" onclick="cancelar()"></td>
    </tr>
  </table>
 -->
  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center">Sabor</td>
        <td align="center">Tipo de Produto</td>
        <td align="center">Tipo</td>
        <td align="center">Qtde</td>
      </tr>
    </thead>
    <tbody>
  
    <?
    $total_geral=0;
    while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
      echo '<tr>';
      
      //echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.sprintf('%08d', $objBuscaRegistros->$chave_primaria).'</a></td>';
      echo '<td align="center">'.bd2texto($objBuscaRegistros->pizza).'</td>';
      echo '<td align="center">'.bd2texto($objBuscaRegistros->tipo_pizza).'</td>';
      echo '<td align="center">'.bd2texto($objBuscaRegistros->tipo).'</td>';
      echo '<td align="center">'.number_format($objBuscaRegistros->quantidade, 0, ",", ".").'</td>';
      $total_geral += number_format($objBuscaRegistros->quantidade, 0);
      
      
      echo '</tr>';
    }
    
    desconectabd($con);
    
    ?>
    
      <tr>
        <td align="center" colspan="3"><b>Total</b></td>
        <td align="center"><b><? echo number_format($total_geral, 0, ",", "."); ?></b></td>
      </tr>
    </tbody>
  </table>

  <input type="hidden" name="acao" value="">
</form>

<? endif; ?>

<? rodape(); ?>
