<?php

/**
 * ipi_sol_baixa.php: Solicitação de Baixa
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Captura de Pagmentos VISANET dos Pedidos');

$acao = validaVarPost('acao');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';

function geraCupom($tam = 10) {
  $cupom = "";
  $caracteres = "123456789ABCDEFGHIJKLMNPQRSTUWXYZ";
  
  $i = 0;
  
  while ( $i < $tam ) {
    $char = substr($caracteres, mt_rand(0, strlen($caracteres) - 1 ), 1);
    
    if (! strstr ( $cupom, $char )) {
      $cupom .= $char;
      $i ++;
    }
  }
  
  return $cupom;
}

switch($acao) {
  case 'capturar':
	echo "captura antiga...";
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
  case 'alterar_pg':
    $codigo = validaVarPost($chave_primaria);
    $forma_pg = validaVarPost('forma_pg');
    
    $indicesSql = implode(',', $codigo);
    
    $con = conectabd();
    
    $SqlUpdate = "UPDATE $tabela SET forma_pg = '$forma_pg' WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlUpdate))
      mensagemOk('Forma de pagamento alterado com sucesso!');
    else
      mensagemErro('Erro ao alterar a forma de pagamento o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
    desconectabd($con);
  break;
  case 'detalhes':
    $codigo = validaVarPost($chave_primaria);
    
    $con = conectabd();
    
    $objBuscaDetalhamento = executaBuscaSimples("SELECT * FROM ipi_pedidos WHERE cod_pedidos = $codigo", $con);
    
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
    echo '<td><b>Celular:</b></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<td>'.bd2texto($objBuscaCliente->nome).'</td>';
    echo '<td width="50">&nbsp;</td>';
    echo '<td>'.$objBuscaCliente->cpf.'</td>';
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
    
    $SqlBuscaPedidosPizzas = 'SELECT * FROM ipi_pedidos_pizzas p INNER JOIN ipi_tamanhos t ON (p.cod_tamanhos = t.cod_tamanhos) WHERE p.cod_pedidos = '.$objBuscaDetalhamento->cod_pedidos.' ORDER BY cod_pedidos_pizzas';
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
      echo '</tr>';
      echo '<tr>';
      echo '<td>'.$objBuscaPedidosPizzas->tamanho.'</td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td>'.$objBuscaPedidosPizzas->quant_fracao.'</td>';
      echo '</tr>';
      
      echo '<tr><td colspan="3">&nbsp;</td></tr>';
      
      echo '<td><b>Recheio da Borda:</b></td>';
      echo '<td width="50">&nbsp;</td>';
      echo '<td><b>Gergelim:</b></td>';
      echo '</tr>';
      echo '<tr>';
      
      $objBuscaPedidosBorda = executaBuscaSimples("SELECT * FROM ipi_pedidos_bordas p INNER JOIN ipi_bordas b ON (p.cod_bordas = b.cod_bordas) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
      
      if($objBuscaPedidosBorda->borda)
        echo '<td>'.$objBuscaPedidosBorda->borda.'</td>';
      else
        echo '<td>Não</td>';
      
      echo '<td width="50">&nbsp;</td>';
      
      $objBuscaPedidosAdicional = executaBuscaSimples("SELECT * FROM ipi_pedidos_adicionais p INNER JOIN ipi_adicionais a ON (p.cod_adicionais = a.cod_adicionais) WHERE p.cod_pedidos = ".$objBuscaPedidosPizzas->cod_pedidos." AND p.cod_pedidos_pizzas = ".$objBuscaPedidosPizzas->cod_pedidos_pizzas, $con);
      
      if($objBuscaPedidosAdicional->adicional)
        echo '<td>'.$objBuscaPedidosAdicional->adicional.'</td>';
      else
        echo '<td>Não</td>';
      
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
    
    echo '<br><br><h3><a href="ipi_sol_baixa.php">&laquo; Voltar</a></h3><br><br>';
    
    desconectabd($con);
  break;
}

?>

<? if($acao != 'detalhes'): ?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

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

function baixar() 
{
  if(verificaCheckbox(document.frmBaixa)) 
  {
    if(document.frmBaixa.cod_entregador.value > 0) 
    {
      document.frmBaixa.acao.value = "baixar";
      document.frmBaixa.submit();
    }
    else 
    {
      alert('Por favor, defina o entregador.');
    }
  }
}

function cancelar()
{
  if(verificaCheckbox(document.frmBaixa)) 
  {
    document.frmBaixa.acao.value = "cancelar";
    document.frmBaixa.submit();
  }
}

function capturar(cod)
{
	window.open("visa_retorno_captura.php?cod="+cod, "Pedido"+cod, "height = 300, width = 400");
}

function alterar_pg() 
{
  if(verificaCheckbox(document.frmBaixa)) 
  {
    if(document.frmBaixa.forma_pg.value != "") 
    {
      document.frmBaixa.acao.value = "alterar_pg";
      document.frmBaixa.submit();
    }
    else 
    {
      alert('Por favor, defina a forma de pagamento.');
    }
  }
}

</script>

<form name="frmFiltroPizzaria" method="post">
  Filtrar baixa para a pizzaria:       
  <select name="cod_pizzarias" id="cod_pizzarias">
    <option value="">Todas as Pizzarias</option>
    <?
    $con = conectabd();
    
    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias ORDER BY nome";
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
  <input type="submit" name="btnEnviar" value="Filtrar" >
</form>

<form name="frmCaptura" method="post">

  <!--table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="240">
        <select name="cod_entregador" id="cod_entregador" style="width: 240px">
          <option value=""></option>
          
          <?
          $con = conectabd();
          
          if ($cod_pizzarias)
          		$SqlBuscaEntregadores = "SELECT * FROM ipi_entregadores WHERE cod_pizzarias='".$_POST['cod_pizzarias']."' ORDER BY nome";
          else
          		$SqlBuscaEntregadores = "SELECT * FROM ipi_entregadores ORDER BY nome";
                    $resBuscaEntregadores = mysql_query($SqlBuscaEntregadores);
          
          while($objBuscaEntregadores = mysql_fetch_object($resBuscaEntregadores)) {
            echo '<option value="'.$objBuscaEntregadores->cod_entregadores.'">'.$objBuscaEntregadores->nome.'</option>';
          }
          
          desconectabd($con);
          ?>
          
        </select>
      </td>
      <td width="40"><input class="botaoAzul" style="font-weight: bold; color: green;" type="button" value="Baixar e Definir Entregador" onclick="baixar()"></td>
      <td width="50"><input class="botaoAzul" style="font-weight: bold; color: red;" type="button" value="Cancelar" onclick="cancelar()"></td>
      <td width="30">&nbsp;</td>
      <td width="100">
        <select name="forma_pg" id="forma_pg" style="width: 100px">
          <option value=""></option>
          
          <?
          $con = conectabd();
          
          $SqlBuscaFormaPg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
          $resBuscaFormaPg = mysql_query($SqlBuscaFormaPg);
          
          while($objBuscaFormaPg = mysql_fetch_object($resBuscaFormaPg)) 
          {
            echo '<option value="'.$objBuscaFormaPg->forma_pg.'">'.$objBuscaFormaPg->forma_pg.'</option>';
          }
          
          desconectabd($con);
          ?>
          
        </select>
      </td>
      <td><input class="botaoAzul" type="button" value="Alterar Forma Pg" onclick="alterar_pg()"></td>
    </tr>
  </table-->

  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center" width="20">&nbsp;</td>
        <td align="center" width="80">Pedido</td>
        <td align="center">Pizzaria</td>
        <td align="center">Cliente</td>
        <td align="center">Endereço</td>
        <td align="center" width="60">Número</td>
        <td align="center">Complemento</td>
        <td align="center">Bairro</td>
        <td align="center" width="70">CEP</td>
        <td align="center" width="70">Forma Pg.</td>
      </tr>
    </thead>
    <tbody>
    
    <?
    
    $con = conectabd();
    $SqlBuscaPedidos = "SELECT pi.nome nome_pizzaria, p.*,c.* FROM $tabela p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.forma_pg='VISANET' AND p.situacao = 'IMPRESSO' AND p.cod_pizzarias > 0 AND p.origem_pedido = 'NET' ";
    
    if ($cod_pizzarias)
    	$SqlBuscaPedidos .= " AND pi.cod_pizzarias='".$cod_pizzarias."'";
    
    $SqlBuscaPedidos .= " ORDER BY cod_pedidos";
    $resBuscaPedidos = mysql_query($SqlBuscaPedidos);
    
    while ($objBuscaPedidos = mysql_fetch_object($resBuscaPedidos))
    {
        echo '<tr>';
        //echo '<td align="center"><input type="checkbox" class="situacao" name="'.$chave_primaria.'[]" value="'.$objBuscaPedidos->$chave_primaria.'"></td>';
        echo '<td align="center"><input type="button" class="botaoAzul" name="btCaptura" value="Capturar" onClick="capturar('.$objBuscaPedidos->$chave_primaria.')"></td>';
        echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaPedidos->$chave_primaria.')">'.sprintf('%08d', $objBuscaPedidos->$chave_primaria).'</a></td>';
        echo '<td align="center">'.bd2texto($objBuscaPedidos->nome_pizzaria).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaPedidos->nome).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaPedidos->endereco).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaPedidos->numero).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaPedidos->complemento).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaPedidos->bairro).'</td>';
        echo '<td align="center">'.$objBuscaPedidos->cep.'</td>';
        echo '<td align="center">'.$objBuscaPedidos->forma_pg.'</td>';
        echo '</tr>';
    }
    
    desconectabd($con);
    
    ?>
    
    </tbody>
  </table>
  <input type="hidden" name="cod" value="">
  <input type="hidden" name="acao" value="capturar">
</form>

<? endif; ?>

<? rodape(); ?>