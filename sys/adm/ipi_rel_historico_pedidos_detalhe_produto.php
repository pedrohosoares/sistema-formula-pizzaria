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

cabecalho('Histórico de Pedidos com Detalhes de Produtos');

$acao = validaVarPost('acao');
$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;
if($acao=="" && validaVarGet("p")!="")
$acao= "detalhes";
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
    
    $sql_verificar = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao IN ('BAIXADO', 'CANCELADO')";
    $res_verificar = mysql_query($sql_verificar);
    $num_verificar = mysql_num_rows($res_verificar);
    if ($num_verificar>0)
    {
      echo "<div style='color: #FF0000; font-weight: bold; font-size: 14px; text-align: center;'>";
      echo "Os pedidos não podem ser cancelados, pois já foram BAIXADOS: ";
      while ($obj_verificar = mysql_fetch_object($res_verificar))
      {
        echo $obj_verificar->$chave_primaria.", ";
      }
      echo "</div><br /><br />";
    }
    else
    {
        //FUSO HORARIO NECESSITA DE CONEXAO COM O BANCO E A VARIAVEL COD_PIZZARIAS    
      $sql_pizzarias = "SELECT cod_pizzarias FROM $tabela WHERE $chave_primaria IN ($indicesSql) LIMIT 1";
      $res_pizzarias = mysql_query($sql_pizzarias);
      $obj_pizzarias = mysql_fetch_object($res_pizzarias);
      $cod_pizzarias = $obj_pizzarias->cod_pizzarias;
      require("../../pub_req_fuso_horario1.php"); 
      
      $SqlUpdate = "UPDATE $tabela SET situacao = 'CANCELADO', data_hora_baixa = NOW(), data_hora_cancelamento = NOW(), cod_usuarios_cancelamento='".$_SESSION['usuario']['codigo']."' WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO'";
      //echo "<Br>3: ".$SqlUpdate;

      $SqlEstornoFidelidade = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, data_validade, pontos) (SELECT cod_clientes, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), pontos_fidelidade_total FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO')";
      //echo "<Br>3: ".$SqlEstornoFidelidade;

      $sql_inserir_relatorio = sprintf("INSERT into ipi_impressao_relatorio (cod_pedidos,cod_usuarios,cod_pizzarias,relatorio,data_hora_inicial,situacao) (select p.cod_pedidos,".$_SESSION['usuario']['codigo'].",p.cod_pizzarias,'CANCELAMENTO',NOW(),'NOVO' from ipi_pedidos p WHERE $chave_primaria IN ($indicesSql))");
      //$res_inserir_relatorio = mysql_query($sql_inserir_relatorio);
      
      //echo "<Br>3: ".$sql_inserir_relatorio;
      if (mysql_query($SqlUpdate) && mysql_query($SqlEstornoFidelidade) && mysql_query($sql_inserir_relatorio))
        mensagemOk('O pedido foi CANCELADO com sucesso!');
      else
        mensagemErro('Erro ao CANCELAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    }
    
    desconectabd($con);
  break;
  case 'detalhes':
    $codigo = (validaVarPost($chave_primaria) ? validaVarPost($chave_primaria) : (validaVarGet("p") ? validaVarGet("p") : 0 )) ;
    
    $pedido = new Pedido();
    echo utf8_decode($pedido->retornar_resumo_pedido_sys($codigo,"h1"));

    $con = conectabd();

    $objBuscaCartao = executaBuscaSimples("SELECT cod_pedidos,forma_pg FROM ipi_pedidos WHERE cod_pedidos = $codigo", $con);
    if ( ($objBuscaCartao->forma_pg=="VISANET") || ($objBuscaCartao->forma_pg=="MASTERCARDNET") || ($objBuscaCartao->forma_pg=="VISANET-CIELO") )
    {
      echo '<br/>';
      echo '<br/>';
      echo '<p><b>Detalhes Operação Cartão Crédito</b></p>';
      echo '<hr noshade="noshade" color="#D44E08"/>';
      $sql_detalhes_cc = "SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = ".$objBuscaCartao->cod_pedidos;
      $res_detalhes_cc = mysql_query($sql_detalhes_cc);
      while ( $obj_detalhes_cc = mysql_fetch_object($res_detalhes_cc) )
      {
          echo "<br /><strong>".$obj_detalhes_cc->chave."</strong>: ".$obj_detalhes_cc->conteudo;
      }
    }

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

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : (validaVarGet('p', '/[0-9]+/') ? (int) validaVarGet('p', '/[0-9]+/') : '');

$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$telefone = (validaVarPost('telefone')) ? validaVarPost('telefone') : '';

$con = conectabd();

$cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];
require("../../pub_req_fuso_horario1.php"); 

$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
$entrega = validaVarPost('entrega');
$tempo_envio = validaVarPost('tempo_envio');

$hora_final = validaVarPost('hora_final');
$hora_inicial = validaVarPost('hora_inicial');

$busca_telefone = substr($telefone, -4);
?>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  
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

  <tr>
    <td class="legenda tdbl" align="right"><label for="telefone">Telefone:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="telefone" id="telefone" size="12" value="<? echo $telefone ?>" onKeyPress="return MascaraTelefone(this,event)"></td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_inicial">Data e Hora Inicial:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
    &nbsp;
    <input type="text" name="hora_inicial" id="hora_inicial" size="3" value="<? echo $hora_inicial ?>" onkeypress="return MascaraHora(this, event)">
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="data_final">Data e Hora Final:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
    <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
    &nbsp;
    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
    &nbsp;
    <input type="text" name="hora_final" id="hora_final" size="3" value="<? echo $hora_final ?>" onkeypress="return MascaraHora(this, event)">
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
        <?
        
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
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="origem">Origem:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr ">
      <select name="origem" id="origem">
        <option value="TODOS" <? if($origem == 'TODOS') echo 'selected' ?>>Todas</option>
        <option value="NET" <? if($origem == 'NET') echo 'selected' ?>>Net</option>
        <option value="TEL" <? if($origem == 'TEL') echo 'selected' ?>>Tel</option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl" align="right"><label for="entrega">Entrega:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">

      <select name="entrega" id="entrega">
        <option value="TODOS">Todos</option>
        <option value="Balcão a Distancia" <? if($entrega == "Balcão a Distancia") echo "selected='selected'" ?>><?php echo traduzir_tipo_entrega("Balcão a Distancia"); ?></option>
        <option value="Balcão" <? if($entrega == "Balcão") echo "selected='selected'" ?>><?php echo traduzir_tipo_entrega("Balcão"); ?></option>
        <option value="Entrega" <? if($entrega == "Entrega") echo "selected='selected'" ?>><?php echo traduzir_tipo_entrega("Entrega"); ?></option>
        <option value="Mesa" <? if($entrega == "Mesa") echo "selected='selected'" ?>><?php echo traduzir_tipo_entrega("Mesa"); ?></option>
      </select>     

    </td>

    <tr>
    <td class="legenda tdbl sep" align="right"><label for="tempo_envio">Tempo de Envio:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="tempo_envio" id="tempo_envio">
        <option value="TODOS" <? if($tempo_envio == 'TODOS') echo 'selected' ?>>Todos</option>
        <option value="Branco" <? if($tempo_envio == 'Branco') echo 'selected' ?>>Dentro do Prazo</option>
        <option value="Amarelo" <? if($tempo_envio == 'Amarelo') echo 'selected' ?>>No Limite do Prazo</option>
        <option value="Vermelho" <? if($tempo_envio == 'Vermelho') echo 'selected' ?>>Atrasado</option>
        <option value="Amarelo_Vermelho" <? if($tempo_envio == 'Amarelo_Vermelho') echo 'selected' ?>>No Limite do Prazo e Atrasado</option>
      </select>
    </td>
  </tr>
  </tr>
  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
if($acao!="")
{
  $con = conectabd();

  $SqlBuscaRegistros = "SELECT p.*, c.*, pi.nome AS pi_nome, p.situacao AS pedidos_situacao, piz.pizza, tp.tipo_pizza, u.nome usuario_atendente, pp.quant_fracao, pf.fracao,
    (SELECT CASE WHEN unix_timestamp(p.data_hora_envio) >= (unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))-600) and unix_timestamp(p.data_hora_envio) <= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE)) THEN 'amarelo' WHEN unix_timestamp(p.data_hora_envio) >= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE)) THEN 'vermelho' ELSE 'branco' END ) AS tempo_entrega  
    FROM $tabela p 
    LEFT JOIN nuc_usuarios u ON (u.cod_usuarios = p.cod_usuarios_pedido)
    INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) 
    INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) 
    INNER JOIN ipi_pedidos_pizzas pp ON (p.cod_pedidos = pp.cod_pedidos) 
    INNER JOIN ipi_pedidos_fracoes pf ON (pp.cod_pedidos_pizzas = pf.cod_pedidos_pizzas) 
    INNER JOIN ipi_pizzas piz ON (piz.cod_pizzas = pf.cod_pizzas) 
    INNER JOIN ipi_tipo_pizza tp ON (piz.cod_tipo_pizza = tp.cod_tipo_pizza) 
    WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";
//echo $SqlBuscaRegistros;

/*    if(strtotime($objBuscaRegistros->data_hora_envio)>=strtotime($objBuscaRegistros->tempo_entrega))
    {
      $cor = "style='background-color:lightCoral'";
    }
    if(strtotime($objBuscaRegistros->data_hora_envio)>=(strtotime($objBuscaRegistros->tempo_entrega)-600) && strtotime($objBuscaRegistros->data_hora_envio)<=strtotime($objBuscaRegistros->tempo_entrega))
    {
      $cor = "style='background-color:orange'";
    }*/

    $SqlSomaTotal = "SELECT sum(p.valor_total) as total_geral FROM $tabela p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")  ";

  if($pedido > 0)
  {
    $SqlBuscaRegistros .= " AND p.cod_pedidos = '$pedido'";
    $SqlSomaTotal .= " AND p.cod_pedidos = '$pedido'";
  }
    //AND p.situacao not in ('CANCELADO')
  if($cliente != '')
  {
    $SqlBuscaRegistros .= " AND c.nome LIKE '%$cliente%'";
    $SqlSomaTotal .= " AND c.nome LIKE '%$cliente%'";
  }

  if ($telefone != '')
  {
    $SqlBuscaRegistros .= " AND p.telefone_1 LIKE '%$busca_telefone'";
    $SqlSomaTotal .= " AND p.telefone_1 LIKE '%$busca_telefone'";
  }
    
  if($cod_pizzarias > 0)
  {
    $SqlBuscaRegistros .= " AND p.cod_pizzarias = '$cod_pizzarias'";
    $SqlSomaTotal .= " AND p.cod_pizzarias = '$cod_pizzarias'";
  }

  if($situacao != 'TODOS')
  {
    $SqlBuscaRegistros .= " AND p.situacao = '$situacao'";
    //$SqlSomaTotal .= " AND p.situacao = '$situacao'";
  }
    
  if($origem != 'TODOS')
  {
    $SqlBuscaRegistros .= " AND p.origem_pedido = '$origem'";
    $SqlSomaTotal .= " AND p.origem_pedido = '$origem'";
  }

  if($entrega!= 'TODOS')
  {
    $SqlBuscaRegistros .= " AND p.tipo_entrega = '$entrega'";
    $SqlSomaTotal .= " AND p.tipo_entrega = '$entrega'";
  }



  if(($data_inicial) && ($data_final)) 
  {
    $data_inicial_sql = data2bd($data_inicial); 
    $data_final_sql = data2bd($data_final);

    if(validar_hora($hora_inicial))
    {
      $data_inicial_sql .= ' '.$hora_inicial.':00'; 
    }
    else
    {
      $data_inicial_sql .= ' 00:00:00'; 
    }

    if(validar_hora($hora_final))
    {
      $data_final_sql .= ' '.$hora_final.':59'; 
    }
    else
    {
      $data_final_sql .= ' 23:59:59'; 
    }

    $SqlBuscaRegistros .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";
    $SqlSomaTotal .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";
  }

  $sql_having_soma = '';

  if($tempo_envio!= 'TODOS')
  {
    if($tempo_envio=="Amarelo_Vermelho")
    {
      $SqlBuscaRegistros .= " HAVING (tempo_entrega = 'Amarelo' or tempo_entrega = 'Vermelho') ";
      $sql_having_soma .= " AND ((unix_timestamp(p.data_hora_envio) >= (unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))-600) and unix_timestamp(p.data_hora_envio) <= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE)) ) OR ( unix_timestamp(p.data_hora_envio) >= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))))";
    }
    else
    {
      $SqlBuscaRegistros .= " HAVING tempo_entrega = '$tempo_envio'";

      if($tempo_envio=="Amarelo")
      {
        $sql_having_soma .= " AND unix_timestamp(p.data_hora_envio) >= (unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))-600) and unix_timestamp(p.data_hora_envio) <= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))";
      }
      elseif($tempo_envio=="Vermelho")
      {
        $sql_having_soma .= " AND unix_timestamp(p.data_hora_envio) >= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))";
      }
      else
      {
        $sql_having_soma .= " AND ( ( NOT(unix_timestamp(p.data_hora_envio) >= (unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))-600) and unix_timestamp(p.data_hora_envio) <= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE)) ) AND  NOT( unix_timestamp(p.data_hora_envio) >= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE)))) or p.data_hora_envio is NULL)";
      }
    }
    
  }
/* (SELECT CASE WHEN unix_timestamp(p.data_hora_envio) >= (unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE))-600) and unix_timestamp(p.data_hora_envio) <= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE)) THEN 'amarelo' WHEN unix_timestamp(p.data_hora_envio) >= unix_timestamp(DATE_ADD(p.data_hora_pedido, INTERVAL 30 MINUTE)) THEN 'vermelho' ELSE 'branco' END ) AS tempo_entrega*/


  $sql_soma_total_todos = "SELECT ($SqlSomaTotal AND p.situacao in('CANCELADO') $sql_having_soma) as total_cancelados, ($SqlSomaTotal AND p.situacao in('BAIXADO') $sql_having_soma) as total_baixados, ($SqlSomaTotal AND p.situacao not in('CANCELADO') $sql_having_soma) as total_sem_cancelados";
  //echo $SqlBuscaRegistros;
  $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
  $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
  //echo "<br/><br/>".$sql_soma_total_todos;
  $res_soma_total_todos = mysql_query($sql_soma_total_todos);
  $obj_total_geral = mysql_fetch_object($res_soma_total_todos);
  $valor_total_geral =  $obj_total_geral->total_sem_cancelados;
  $valor_total_cancelados =  $obj_total_geral->total_cancelados;
  $valor_total_baixados =  $obj_total_geral->total_baixados;

  $SqlBuscaRegistros .= ' ORDER BY cod_pedidos LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
  $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
  $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

  //echo $SqlBuscaRegistros;

  echo "<center><b>".$numBuscaRegistros." pedido(s) encontrado(s)</center></b><br>";

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
    echo '<input type="hidden" name="tempo_envio" value="'.$tempo_envio.'">';
    
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
}

?>

<br>

<form name="frmBaixa" method="post">
  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Reimprimir" onclick="reimprimir()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Impresso" onclick="impresso()"></td>
      <td width="50" align="left"><input class="botaoAzul" type="button" value="Imprimir Agora" onclick="agendamento()"></td>
      <td align="left"><input class="botaoAzul" style="font-weight: bold; color: red;" type="button" value="Cancelar" onclick="cancelar()"></td>
    </tr>
  </table>

  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <?
/*
      echo "<tr><td colspan='3' style='background-color:#E5E5E5;font-weight:bold'>Totalizadores de todas as paginas :</td><td colspan='2' style='background-color:#E5E5E5;font-weight:bold'>Total dos pedidos cancelados R$ ".($valor_total_cancelados>0 ? bd2moeda($valor_total_cancelados) : '0,00')."</td><td colspan='4' style='background-color:#E5E5E5;font-weight:bold'>Total dos pedidos baixados R$ ".($valor_total_baixados>0 ? bd2moeda($valor_total_baixados) : '0,00')."</td><td colspan='4' style='background-color:#E5E5E5;font-weight:bold'>Total dos pedidos não cancelados R$ ".($valor_total_geral>0 ? bd2moeda($valor_total_geral) : '0,00')."</td></tr>";
*/      
      ?>
      <tr>
        <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
        <td align="center" width="70">Pedido</td>
        <td align="center" width="70">Horário do Pedido</td>
        <td align="center" width="130"><? echo ucfirst(TIPO_EMPRESA)?></td>
        <td align="center" width="130">Atendente</td>
        <td align="center" width="70">Fração</td>
        <td align="center">Tipo Produto</td>
        <td align="center">Produto</td>
        <td align="center" width="120">Tipo de Entrega</td>
<!-- 

        <td align="center" width="70">Agendado</td>
        <td align="center" width="70">Horário da Expedição</td>
        <td align="center" width="70">Horário da Baixa</td>
        <td align="center" width="70">Valor Total</td>
 -->
        <td align="center" width="70">Situação</td>
      </tr>
    </thead>
    <tbody>
  
    <?
    if($acao!="")
    {
            //echo "";//<td colspan='1' style='background-color:#E5E5E5'>&nbsp;</td>

      while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
        $cor = '';
        if($objBuscaRegistros->data_hora_envio!="")
        {
          if($objBuscaRegistros->tempo_entrega=="vermelho")
          {
            $cor = "style='background-color:lightCoral'";
          }
          if($objBuscaRegistros->tempo_entrega=="amarelo")
          {
            $cor = "style='background-color:orange'";
          }
        }

        echo '<tr>';
        
        echo '<td align="center" '.$cor.'><input type="checkbox" class="marcar situacao" name="'.$chave_primaria.'[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
        echo '<td align="center" '.$cor.'><a href="ipi_rel_historico_pedidos.php?p='.$objBuscaRegistros->$chave_primaria.'">'.sprintf('%08d', $objBuscaRegistros->$chave_primaria).'</a></td>';
        echo '<td align="center" '.$cor.'>'.bd2datahora($objBuscaRegistros->data_hora_pedido).'</td>';
        echo '<td align="center" '.$cor.'>'.bd2texto($objBuscaRegistros->pi_nome).'</td>';
        echo '<td align="center" '.$cor.'>'.bd2texto($objBuscaRegistros->usuario_atendente).'</td>';
        
        echo '<td align="center" '.$cor.'>'.bd2texto($objBuscaRegistros->quant_fracao)."/".$objBuscaRegistros->fracao.'</td>';

        echo '<td align="center" '.$cor.'>'.bd2texto($objBuscaRegistros->tipo_pizza).'</td>';
        echo '<td align="center" '.$cor.'>'.bd2texto($objBuscaRegistros->pizza).'</td>';
        echo '<td align="center" '.$cor.'>'.traduzir_tipo_entrega($objBuscaRegistros->tipo_entrega).'</td>';

        /*
        echo '<td align="center" '.$cor.'>'.$objBuscaRegistros->forma_pg.'</td>';
        if($objBuscaRegistros->agendado == '1')
        {
            echo '<td align="center" '.$cor.'>'.bd2texto($objBuscaRegistros->horario_agendamento).'</td>';    
        }
        else
        {
            echo '<td align="center" '.$cor.'>NÃO</td>';
        }
        echo '<td align="center" '.$cor.'>'.bd2datahora($objBuscaRegistros->data_hora_envio).'</td>';
        echo '<td align="center" '.$cor.'>'.bd2datahora($objBuscaRegistros->data_hora_baixa).'</td>';
        echo '<td align="center" '.$cor.'>'.bd2moeda($objBuscaRegistros->valor_total).'</td>';
        echo '<td align="center" '.$cor.'>'.bd2texto($objBuscaRegistros->origem_pedido).'</td>';
        */
        echo '<td align="center" '.$cor.'>'.$objBuscaRegistros->pedidos_situacao.'</td>';
        
        echo '</tr>';
      }
      //  $valor_total_geral =  $obj_total_geral->total_geral;
  //$valor_total_cancelados =  $obj_total_geral->total_cancelados;
 // $valor_total_baixados =  $obj_total_geral->total_baixados;

      desconectabd($con);
    }
    

    
    ?>
    
    </tbody>
  </table>
  <div style="padding-left:35%" align="center"><br/><div style='background-color:orange;width:220px;float:left'>Tempo de Envio Maior que <b>20</b> minutos&nbsp;</div>
      &nbsp;&nbsp;<div style='background-color:lightCoral;width:220px;float:left'>&nbsp;Tempo de Envio Maior que <b>30</b> minutos</div><div style="clear:both"></div></div>

  <input type="hidden" name="acao" value="">
</form>
<? endif; ?>

<? rodape(); ?>
