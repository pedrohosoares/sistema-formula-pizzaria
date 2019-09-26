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

cabecalho('Vendas por Atendentes');

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
    
    $sql_verificar = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao='BAIXADO'";
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
        //FUSO HORARIO NECESSITA DE CONEXAO COM O BANCO E A VARIAVEL COD_PIZZARIAS    
    $sql_pizzarias = "SELECT cod_pizzarias FROM $tabela WHERE $chave_primaria IN ($indicesSql) LIMIT 1";
    $res_pizzarias = mysql_query($sql_pizzarias);
    $obj_pizzarias = mysql_fetch_object($res_pizzarias);
    $cod_pizzarias = $obj_pizzarias->cod_pizzarias;
    require_once("../../pub_req_fuso_horario1.php"); 
    
    $SqlUpdate = "UPDATE $tabela SET situacao = 'CANCELADO', data_hora_baixa = NOW(), data_hora_cancelamento = NOW(), cod_usuarios_cancelamento='".$_SESSION['usuario']['codigo']."' WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO'";
    $SqlEstornoFidelidade = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, data_validade, pontos) (SELECT cod_clientes, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), pontos_fidelidade_total FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO')";
    
    $sql_inserir_relatorio = sprintf("INSERT into ipi_impressao_relatorio (cod_pedidos,cod_usuarios,cod_pizzarias,relatorio,data_hora_inicial,situacao) (select p.cod_pedidos,".$_SESSION['usuario']['codigo'].",p.cod_pizzarias,'CANCELAMENTO',NOW(),'NOVO' from ipi_pedidos p WHERE $chave_primaria IN ($indicesSql))");
    //$res_inserir_relatorio = mysql_query($sql_inserir_relatorio);
    //echo $sql_inserir_relatorio;
    if (mysql_query($SqlUpdate) && mysql_query($SqlEstornoFidelidade) && mysql_query($sql_inserir_relatorio))
      mensagemOk('O pedido foi CANCELADO com sucesso!');
    else
      mensagemErro('Erro ao CANCELAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    
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

<?
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : (validaVarGet('p', '/[0-9]+/') ? (int) validaVarGet('p', '/[0-9]+/') : '');

$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
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

window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
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
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as Pizzarias</option>
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
  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>

<?
if($acao!="")
{
  $con = conectabd();

  $SqlBuscaRegistros = "SELECT count(p.cod_pedidos) as qtd_pedidos,usu.cod_usuarios, usu.nome as nome_atendente,(SELECT data_hora_pedido from ipi_pedidos where cod_pedidos = p.cod_pedidos ORDER BY cod_pedidos DESC LIMIT 1) as data_hora_ultimo_pedido FROM $tabela p INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) inner join nuc_usuarios usu on usu.cod_usuarios = p.cod_usuarios_pedido WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") and p.situacao='BAIXADO' ";

    //$SqlBuscaRegistros = "SELECT count(p.cod_pedidos) as qtd_pedidos, usu.nome as nome_atendente FROM $tabela p INNER JOIN ipi_clientes c ON (p.cod_clientes = c.cod_clientes) INNER JOIN ipi_pizzarias pi ON (p.cod_pizzarias = pi.cod_pizzarias) inner join nuc_usuarios usu on usu.cod_usuarios = p.cod_usuarios_pedido INNER JOIN ipi_pedidos_pizzas pp on pp.cod_pedidos = p.cod_pedidos WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";

  if($cod_pizzarias > 0)
  {
    $SqlBuscaRegistros .= " AND p.cod_pizzarias = '$cod_pizzarias'";
  }

/*  if($situacao != 'TODOS')
  {
    $SqlBuscaRegistros .= " AND p.situacao = '$situacao'";
  }*/
    


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
}

?>

<br>

<form name="frmBaixa" method="post">
<!--   <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
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
        <td align="center" width="70">Detalhamento</td>
        <td align="center" width="70">Quantidade de Pedidos</td>
        <td align="center">Atendente</td>
        <td align="center">Data e hora do ultimo pedido</td>
      </tr>
    </thead>
    <tbody>
  
    <?
    if($acao!="")
    {
            //echo "";//<td colspan='1' style='background-color:#E5E5E5'>&nbsp;</td>
      $filtro = '';
              if($cod_pizzarias > 0)
        {
          $filtro .= " AND p.cod_pizzarias = '$cod_pizzarias'";
        }

        if($entrega!= 'TODOS')
        {
          $filtro .= " AND p.tipo_entrega = '$entrega'";
        }

        if(($data_inicial) && ($data_final)) {
          $data_inicial_sql = data2bd($data_inicial).' 00:00:00'; 
          $data_final_sql = data2bd($data_final).' 23:59:59';
          $filtro .= " AND p.data_hora_pedido >= '$data_inicial_sql' AND p.data_hora_pedido <= '$data_final_sql'";
        }
      while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) 
      {
        echo '<tr>';
        echo "<td align='center'><a href='javascript:void(0)' onclick='alterar_visibilidade(\"resumo_".$objBuscaRegistros->cod_usuarios."\")'>Ver mais</a></td>";
        echo "<td align='right'>".$objBuscaRegistros->qtd_pedidos."</td>";
        echo "<td align='center'>".$objBuscaRegistros->nome_atendente."</td>";
        echo "<td align='center'>".$objBuscaRegistros->data_hora_ultimo_pedido."</td>";
        echo '</tr>';
        echo "<tr style='display:none' id='resumo_".$objBuscaRegistros->cod_usuarios."'><td colspan='4'><br/>";//

        //pizzas vendidas por tamanho

        $sql_buscar_total_pizzas = "SELECT count(pp.cod_pedidos_pizzas) as total_vendidas,t.tamanho,t.cod_tamanhos from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos where p.cod_usuarios_pedido = '".$objBuscaRegistros->cod_usuarios."' and p.situacao='BAIXADO'";

        $sql_buscar_total_pizzas .= $filtro." group by pp.cod_tamanhos ORDER BY total_vendidas desc";
        // echo "".$sql_buscar_total_pizzas."<br/><br/>";
        $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
        
        $tabela_pizzas = "";
        $cont_pizzas_vendidas = 0;
        while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
        {
          $tabela_pizzas .= '<table id="pizzastable_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" class="listaEdicao" cellpadding="0" cellspacing="0"><thead>';
          $tabela_pizzas .= "<tr><td align='center'><a href='javascript:void(0)' onclick='carregar_informacoes(\"pizzasconteudo_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\",\"carregar_pizzas\");alterar_visibilidade(\"pizzastr_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\")'>".$obj_buscar_total_pizzas->total_vendidas." x ".$obj_buscar_total_pizzas->tamanho."</a></td></tr>";
          $tabela_pizzas .= '</thead><tbody><tr id="pizzastr_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" style="display:none" ><td id="pizzasconteudo_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" >Carregando...</td></tr></tbody></table>';
          // echo $obj_buscar_total_pizzas->total_vendidas."<br/>";
          $cont_pizzas_vendidas += $obj_buscar_total_pizzas->total_vendidas;

        }
         echo '<table class="listaEdicao" cellpadding="0" cellspacing="0"><thead><tr></tr><td align="center" style="background-color:MediumPurple;color:white" ><a style="color:white" href="javascript:void(0)" onclick="alterar_visibilidade(\'grupo_pizzas'.$objBuscaRegistros->cod_usuarios.'\')"> '.$cont_pizzas_vendidas.' Pizzas Vendidas</a></td></tr></thead><tbody><tr id="grupo_pizzas'.$objBuscaRegistros->cod_usuarios.'" style="display:none"><td>';
        echo $tabela_pizzas;
        echo "</td></tr></tbody></table><br/>";

        //pizzas vendidas por tamanho SALGADAS

        $sql_buscar_total_pizzas = "SELECT codigo_tamanho as cod_tamanhos,tamanho_texto as tamanho,quantidade_fracao as q_fracao, total_vendidas as total_vendidas_correta from (SELECT count(pp.cod_pedidos_pizzas) as total_vendidas,sum(pp.quant_fracao) as qtd_fracoes, sum(pf.fracao) as soma_fracoes,t.tamanho as tamanho_texto,t.cod_tamanhos as codigo_tamanho,pp.quant_fracao as quantidade_fracao from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos INNER JOIN ipi_pedidos_fracoes pf ON pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas
INNER JOIN ipi_pizzas pizza ON pizza.cod_pizzas = pf.cod_pizzas where p.cod_usuarios_pedido = '".$objBuscaRegistros->cod_usuarios."' and p.situacao='BAIXADO' and (pizza.tipo='Salgada' OR pizza.tipo='Salgado' )";

        $sql_buscar_total_pizzas .= $filtro." group by pp.cod_tamanhos) q group by cod_tamanhos ORDER BY total_vendidas_correta desc";
        // echo "sald".$sql_buscar_total_pizzas."<br/><br/>";
        $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
        
        $tabela_pizzas = "";
        $cont_pizzas_vendidas = 0;
        while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
        {
          $tabela_pizzas .= '<table id="pizzastable_salgada_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" class="listaEdicao" cellpadding="0" cellspacing="0"><thead>';
          $tabela_pizzas .= "<tr><td align='center'><a href='javascript:void(0)' onclick='carregar_informacoes(\"pizzasconteudo_salgada_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\",\"carregar_pizzas\");alterar_visibilidade(\"pizzastr_salgada_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\")'>".number_format($obj_buscar_total_pizzas->total_vendidas_correta,0)." x ".$obj_buscar_total_pizzas->tamanho."</a></td></tr>";
          $tabela_pizzas .= '</thead><tbody><tr id="pizzastr_salgada_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" style="display:none" ><td id="pizzasconteudo_salgada_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" >Carregando...</td></tr></tbody></table>';
          // echo($obj_buscar_total_pizzas->total_vendidas_correta."<br/>");
          $cont_pizzas_vendidas += ($obj_buscar_total_pizzas->total_vendidas_correta);

        }
         echo '<table class="listaEdicao" cellpadding="0" cellspacing="0"><thead><tr></tr><td align="center" style="background-color:MediumPurple;color:white" ><a style="color:white" href="javascript:void(0)" onclick="alterar_visibilidade(\'grupo_pizzas_salgada'.$objBuscaRegistros->cod_usuarios.'\')"> '.$cont_pizzas_vendidas.' Sabores Salgadas Vendidas</a></td></tr></thead><tbody><tr id="grupo_pizzas_salgada'.$objBuscaRegistros->cod_usuarios.'" style="display:none"><td>';
        echo $tabela_pizzas;
        echo "</td></tr></tbody></table><br/>";

        //pizzas vendidas por tamanho doc

        //count( pp.cod_pedidos_pizzas ) AS total_vendidas, sum( pp.quant_fracao ) AS qtd_fracoes, sum( pf.fracao )

        $sql_buscar_total_pizzas = "SELECT codigo_tamanho as cod_tamanhos,tamanho_texto as tamanho,quantidade_fracao as q_fracao, total_vendidas as total_vendidas_correta from (SELECT count(pp.cod_pedidos_pizzas) as total_vendidas,sum(pp.quant_fracao) as qtd_fracoes, sum(pf.fracao) as soma_fracoes,t.tamanho as tamanho_texto,t.cod_tamanhos as codigo_tamanho,pp.quant_fracao as quantidade_fracao from ipi_pedidos_pizzas pp inner join ipi_pedidos p on p.cod_pedidos = pp.cod_pedidos inner join ipi_tamanhos t on t.cod_tamanhos = pp.cod_tamanhos INNER JOIN ipi_pedidos_fracoes pf ON pf.cod_pedidos_pizzas = pp.cod_pedidos_pizzas
INNER JOIN ipi_pizzas pizza ON pizza.cod_pizzas = pf.cod_pizzas where p.cod_usuarios_pedido = '".$objBuscaRegistros->cod_usuarios."' and p.situacao='BAIXADO' and pizza.tipo='Doce'";

        $sql_buscar_total_pizzas .= $filtro." group by pp.cod_tamanhos) q group by cod_tamanhos ORDER BY total_vendidas_correta desc";
        // echo "doc".$sql_buscar_total_pizzas."<br/><br/>";
        $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
        
        $tabela_pizzas = "";
        $cont_pizzas_vendidas = 0;
        while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
        {
          $tabela_pizzas .= '<table id="pizzastable_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" class="listaEdicao" cellpadding="0" cellspacing="0"><thead>';
          $tabela_pizzas .= "<tr><td align='center'><a href='javascript:void(0)' onclick='carregar_informacoes(\"pizzasconteudo_doce_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\",\"carregar_pizzas\");alterar_visibilidade(\"pizzastr_doce_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_tamanhos."\")'>".number_format($obj_buscar_total_pizzas->total_vendidas_correta,0)." x ".$obj_buscar_total_pizzas->tamanho."</a></td></tr>";
          $tabela_pizzas .= '</thead><tbody><tr id="pizzastr_doce_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" style="display:none" ><td id="pizzasconteudo_doce_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_tamanhos.'" >Carregando...</td></tr></tbody></table>';
          $cont_pizzas_vendidas += ($obj_buscar_total_pizzas->total_vendidas_correta);

        }
         echo '<table class="listaEdicao" cellpadding="0" cellspacing="0"><thead><tr></tr><td align="center" style="background-color:MediumPurple;color:white" ><a style="color:white" href="javascript:void(0)" onclick="alterar_visibilidade(\'grupo_pizzas_doce_'.$objBuscaRegistros->cod_usuarios.'\')"> '.$cont_pizzas_vendidas.' Sabores Doces Vendidas</a></td></tr></thead><tbody><tr id="grupo_pizzas_doce_'.$objBuscaRegistros->cod_usuarios.'" style="display:none"><td>';
        echo $tabela_pizzas;
        echo "</td></tr></tbody></table><br/>";

        //adicionais venmdidos por tamanho

        $sql_buscar_total_pizzas = "SELECT coco from privada where tamanho=grande";
        //$res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
        //$obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas);

        //adicionais venmdidos geral

        //bebidas vendidas

        $sql_buscar_total_pizzas = "SELECT sum(pb.quantidade) as total_vendidas,bc.cod_bebidas_ipi_conteudos,c.conteudo,b.bebida from ipi_pedidos_bebidas pb inner join ipi_pedidos p on p.cod_pedidos = pb.cod_pedidos inner join ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos = pb.cod_bebidas_ipi_conteudos inner join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos inner join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas where p.cod_usuarios_pedido = '".$objBuscaRegistros->cod_usuarios."' and p.situacao='BAIXADO' ";

        $sql_buscar_total_pizzas .= $filtro." group by pb.cod_bebidas_ipi_conteudos ORDER BY total_vendidas desc";
        //echo $sql_buscar_total_pizzas."<br/><br/>";
        $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
        
        $tabela_bebidas = "";
        $cont_bebidas_vendidas = 0;
        while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
        {
          $tabela_bebidas .= '<table id="bebidastable_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_bebidas_ipi_conteudos.'" class="listaEdicao" cellpadding="0" cellspacing="0"><thead>';
          $tabela_bebidas .= "<tr><td align='center'><a href='javascript:void(0)' onclick='carregar_informacoes(\"bebidasconteudo_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_bebidas_ipi_conteudos."\",\"carregar_bebidas\");alterar_visibilidade(\"bebidastr_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_bebidas_ipi_conteudos."\")'>".$obj_buscar_total_pizzas->total_vendidas." x ".$obj_buscar_total_pizzas->bebida." ".$obj_buscar_total_pizzas->conteudo."</a></td></tr>";
          $tabela_bebidas .= '</thead><tbody><tr id="bebidastr_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_bebidas_ipi_conteudos.'" style="display:none" ><td id="bebidasconteudo_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_bebidas_ipi_conteudos.'">Carregando...</td></tr></tbody></table>';
          $cont_bebidas_vendidas += $obj_buscar_total_pizzas->total_vendidas;
        }

        echo '<table class="listaEdicao" cellpadding="0" cellspacing="0"><thead><tr></tr><td align="center" style="background-color:MediumPurple;color:white" ><a style="color:white" href="javascript:void(0)" onclick="alterar_visibilidade(\'grupo_bebidas_'.$objBuscaRegistros->cod_usuarios.'\')"> '.$cont_bebidas_vendidas.' Bebidas Vendidas</a></td></tr></thead><tbody><tr id="grupo_bebidas_'.$objBuscaRegistros->cod_usuarios.'" style="display:none"><td>';
        echo $tabela_bebidas;
        echo "</td></tr></tbody></table><br/>";
        //bebidas vendidas

        $sql_buscar_total_pizzas = "SELECT count(pi.cod_pedidos_ingredientes) as total_vendidas,sum(pi.preco) as total_faturamento,i.ingrediente,i.cod_ingredientes from ipi_pedidos_ingredientes pi inner join ipi_pedidos p on p.cod_pedidos = pi.cod_pedidos inner join ipi_ingredientes i on i.cod_ingredientes = pi.cod_ingredientes  where p.cod_usuarios_pedido = '".$objBuscaRegistros->cod_usuarios."' and p.situacao='BAIXADO' and ingrediente_padrao = 0 ";

        $sql_buscar_total_pizzas .= $filtro." group by pi.cod_ingredientes ORDER BY total_vendidas desc";
        //SELECT p.cod_pedidos,pi.cod_pedidos_pizzas,count(pi.cod_pedidos_ingredientes) as total_vendidas,sum(pi.preco) as total_faturamento,i.ingrediente,i.cod_ingredientes from ipi_pedidos_ingredientes pi inner join ipi_pedidos p on p.cod_pedidos = pi.cod_pedidos inner join ipi_ingredientes i on i.cod_ingredientes = pi.cod_ingredientes where p.cod_usuarios_pedido = '11' and p.situacao='BAIXADO' and ingrediente_padrao = 0 AND p.data_hora_pedido >= '2012-07-01 00:00:00' AND p.data_hora_pedido <= '2013-07-19 23:59:59' group by pi.cod_ingredientes,pi.cod_pedidos_pizzas ORDER BY `total_vendidas` DESC

        //fazer iner com pedido pizza e pegar o quant fracao (e por ele no group) para saber se eh comic fracoes = 4 (por regra na loja, comic só sai 4 fracoes)
        //se for comic, a cada 2 adicionais conta como 1, caso contrario 1adicional conta como 1 ponto
        //echo $sql_buscar_total_pizzas."<br/><br/>";
        $res_buscar_total_pizzas = mysql_query($sql_buscar_total_pizzas);
        //id="grupoadicionais'.$objBuscaRegistros->cod_usuarios.'"
        $tabela_adicionais = "";
        $cont_vendidos = 0;
        while($obj_buscar_total_pizzas = mysql_fetch_object($res_buscar_total_pizzas))
        {
          $tabela_adicionais .= '<table id="adicionaistable_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_ingredientes.'" class="listaEdicao" cellpadding="0" cellspacing="0"><thead>';
          $tabela_adicionais .= "<tr><td align='center'><a href='javascript:void(0)' onclick='carregar_informacoes(\"adicionaisconteudo_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_ingredientes."\",\"carregar_adicionais\");alterar_visibilidade(\"adicionaistr_".$objBuscaRegistros->cod_usuarios."_".$obj_buscar_total_pizzas->cod_ingredientes."\")'>".$obj_buscar_total_pizzas->total_vendidas." x ".$obj_buscar_total_pizzas->ingrediente." - R$ ".bd2moeda($obj_buscar_total_pizzas->total_faturamento)."</a></td></tr>";
          $tabela_adicionais .= '</thead><tbody><tr id="adicionaistr_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_ingredientes.'" style="display:none" ><td id="adicionaisconteudo_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_buscar_total_pizzas->cod_ingredientes.'">Carregando...</td></tr></tbody></table>';
          $cont_vendidos += $obj_buscar_total_pizzas->total_vendidas;
        }
        
        
        echo '<table class="listaEdicao" cellpadding="0" cellspacing="0"><thead><tr></tr><td align="center" style="background-color:MediumPurple;color:white" ><a style="color:white" href="javascript:void(0)" onclick="alterar_visibilidade(\'grupoadicionais_'.$objBuscaRegistros->cod_usuarios.'\')"> '.$cont_vendidos.' Adicionais Vendidos</a></td></tr></thead><tbody><tr id="grupoadicionais_'.$objBuscaRegistros->cod_usuarios.'" style="display:none"><td>';
        echo $tabela_adicionais;
        echo "</td></tr></tbody></table><br/>";
        //

         $sql_buscar_total_bordas = "SELECT pb.cod_bordas, b.borda, COUNT(pb.cod_bordas) AS quantidade FROM ipi_pedidos p INNER JOIN ipi_pedidos_bordas pb ON (p.cod_pedidos = pb.cod_pedidos) INNER JOIN ipi_pedidos_pizzas pp ON (pp.cod_pedidos_pizzas = pb.cod_pedidos_pizzas) INNER JOIN ipi_bordas b ON (pb.cod_bordas = b.cod_bordas) WHERE p.situacao = 'BAIXADO' $filtro AND p.cod_usuarios_pedido = $objBuscaRegistros->cod_usuarios GROUP BY b.borda ORDER BY borda ";
         // echo $sql_buscar_total_bordas;
         $res_buscar_total_bordas = mysql_query($sql_buscar_total_bordas);
         $tabela_bordas = "";
        $cont_vendidos = 0;

        while($obj_res_buscar_total_bordas = mysql_fetch_object($res_buscar_total_bordas))
        {
          $tabela_bordas .= '<table id="bordastable_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_res_buscar_total_bordas->cod_bordas.'" class="listaEdicao" cellpadding="0" cellspacing="0"><thead>';
          $tabela_bordas .= "<tr><td align='center'><a href='javascript:void(0)'>".$obj_res_buscar_total_bordas->quantidade." x ".$obj_res_buscar_total_bordas->borda."</a></td></tr>";
          $tabela_bordas .= '</thead><tbody><tr id="bordastr_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_res_buscar_total_bordas->cod_bordas.'" style="display:none" ><td id="bordasconteudo_'.$objBuscaRegistros->cod_usuarios.'_'.$obj_res_buscar_total_bordas->cod_bordas.'">Carregando...</td></tr></tbody></table>';
          $cont_vendidos += $obj_res_buscar_total_bordas->quantidade;
        }
        echo '<table class="listaEdicao" cellpadding="0" cellspacing="0"><thead><tr></tr><td align="center" style="background-color:MediumPurple;color:white" ><a style="color:white" href="javascript:void(0)" onclick="alterar_visibilidade(\'grupobordas_'.$objBuscaRegistros->cod_usuarios.'\')"> '.$cont_vendidos.' Bordas Vendidas</a></td></tr></thead><tbody><tr id="grupobordas_'.$objBuscaRegistros->cod_usuarios.'" style="display:none"><td>';
        echo $tabela_bordas;
        echo "</td></tr></tbody></table>";

        echo "</td></tr>";
      }

      desconectabd($con);
    }
    

    
    ?>
    
    </tbody>
  </table>

  <input type="hidden" name="acao" value="">
</form>
<? endif; ?>

<? rodape(); ?>
