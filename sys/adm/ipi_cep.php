<?php

/**
 * ipi_cep.php: Cadastro de Range de Ceps
 * 
 * Índice: cod_cep
 * Tabela: ipi_cep
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de CEP de Entrega');

$acao = validaVarPost('acao');

$tabela = 'ipi_cep';
$chave_primaria = 'cod_cep';
$quant_pagina = 120;
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
switch($acao) {
  case 'distancia':
    $distancia = validaVarPost('distancia');
    $desassociar = validaVarPost('desassociar');
    $indicesSql = implode(',', $desassociar);
    
    $con = conectabd();
    
    $sql_atualizar_distancia = "UPDATE $tabela SET distancia = '$distancia' WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($sql_atualizar_distancia))
      mensagemOk('Os registros selecionados foram atualizados com sucesso!');
    else
      mensagemErro('Erro ao desassociar os registros', 'Por favor, comunique a equipe de suporte informando todos os ceps selecionados para desassociação.');
    
    desconectabd($con);
  break;
  case 'frete':
    $frete = moeda2bd(validaVarPost('frete'));
    $pedido_minimo = moeda2bd(validaVarPost("pedido_minimo"));
    $desassociar = validaVarPost('desassociar');
    $indicesSql = implode(',', $desassociar);
    
    $con = conectabd();
    
    $sql_atualizar_distancia = "UPDATE $tabela SET cod_taxa_frete = '$frete', cod_pedido_minimo = '$pedido_minimo' WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($sql_atualizar_distancia))
      mensagemOk('Os registros selecionados foram atualizados com sucesso!');
    else
      mensagemErro('Erro ao atualizar os registros', 'Por favor, comunique a equipe de suporte informando todos os ceps selecionados para desassociação.');
    
    desconectabd($con);
  break;
  case 'desassociar':
    $desassociar = validaVarPost('desassociar');
    $indicesSql = implode(',', $desassociar);
    
    $con = conectabd();
    // 
    $SqlInsert = "INSERT INTO ipi_cep_aprovacao (cep_inicial, cep_final, rua, ponto_referencia, condominio, bairro, regiao, cidade, estado, complemento) (SELECT cep_inicial, cep_final, rua,ponto_referencia, condominio, bairro, regiao, cidade, estado, complemento FROM $tabela WHERE $chave_primaria IN ($indicesSql))";
    $SqlDelete = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlInsert) && mysql_query($SqlDelete))
      mensagemOk('Os registros selecionados foram desassociados com sucesso!');
    else
      mensagemErro('Erro ao desassociar os registros', 'Por favor, comunique a equipe de suporte informando todos os ceps selecionados para desassociação.');
    
    desconectabd($con);
  break;
}

?>

<script>

function definir_distancia()
{
  form = document.getElementById('frmExcluir');
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
  
  if(cInput > 0) 
  {
    if(confirm('Deseja definir a distância para os itens selecionados?'))
    {
      document.frmExcluir.acao.value = 'distancia';
      document.frmExcluir.submit();
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja definir a distancia.');
     
    return false;
  }
}

function definir_frete()
{
  form = document.getElementById('frmExcluir');
    var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
  
  if(cInput > 0) 
  {
    if(confirm('Deseja definir o frete para os itens selecionados?'))
    {
      document.frmExcluir.acao.value = 'frete';
      document.frmExcluir.submit();
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja definir o frete.');
     
    return false;
  }
}

function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
  
  if(cInput > 0) {
    if (confirm('Deseja desassociar os registros selecionados?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja desassociar.');
     
    return false;
  }
}

function editar(cod) {
  var form = new Element('form', {
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

</script>

<? 

$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
$opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'bairro';
$filtro = validaVarPost('filtro');

$bairro = (validaVarPost('bairro')) ? validaVarPost('bairro') : 'TODOS';
$regiao = (validaVarPost('regiao')) ? validaVarPost('regiao') : 'TODOS';
$cidade = (validaVarPost('cidade')) ? validaVarPost('cidade') : 'TODOS';
$cod_pizzarias = (validaVarPost('cod_pizzarias')) ? validaVarPost('cod_pizzarias') : 'TODOS';
?>

<form name="frmFiltro" id="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  
  <tr>
    <td class="legenda tdbl tdbt sep" align="right">
      <select name="opcoes">
        <option value="rua" <? if($opcoes == 'rua') echo 'selected' ?>>Rua</option>
        <option value="bairro" <? if($opcoes == 'bairro') echo 'selected' ?>>Bairro</option>
        <option value="regiao" <? if($opcoes == 'regiao') echo 'selected' ?>>Região</option>
        <option value="cidade" <? if($opcoes == 'cidade') echo 'selected' ?>>Cidade</option>
      </select>
    </td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbt tdbr sep">
      <input type="text" name="filtro" size="60" value="<? echo $filtro ?>">
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA) ?>:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="cod_pizzarias" style="width: 250px;">
        <option value="TODOS" <? if($cod_pizzarias == 'TODOS') echo 'selected' ?>>Todos as <? echo ucfirst(TIPO_EMPRESAS) ?></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while($obsBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
          echo '<option value="'.$obsBuscaPizzarias->cod_pizzarias.'" ';
          
          if($cod_pizzarias == $obsBuscaPizzarias->cod_pizzarias)
            echo 'selected';
          
          echo '>'.bd2texto($obsBuscaPizzarias->nome).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="bairro">Bairro:</label></td>
    <td>&nbsp;</td>
    <td class="tdbr">
      <select name="bairro" style="width: 250px;">
        <option value="TODOS" <? if($bairro == 'TODOS') echo 'selected' ?>>Todos os Bairros</option>
        <?
        $con = conectabd();
        
        $SqlBuscaBairros = "SELECT DISTINCT bairro FROM $tabela ORDER BY bairro";
        $resBuscaBairros = mysql_query($SqlBuscaBairros);
        
        while($objBuscaBairros = mysql_fetch_object($resBuscaBairros)) {
          echo '<option value="'.$objBuscaBairros->bairro.'" ';
          
          if($bairro == $objBuscaBairros->bairro)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaBairros->bairro).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>
  
  <tr>
    <td class="legenda tdbl" align="right"><label for="regiao">Região:</label></td>
    <td class="">&nbsp;</td>
    <td class="tdbr">
      <select name="regiao" style="width: 250px;">
        <option value="TODOS" <? if($bairro == 'TODOS') echo 'selected' ?>>Todos as Regiões</option>
        <?
        $con = conectabd();
        
        $SqlBuscaRegioes = "SELECT DISTINCT regiao FROM $tabela ORDER BY regiao";
        $resBuscaRegioes = mysql_query($SqlBuscaRegioes);
        
        while($objBuscaRegioes = mysql_fetch_object($resBuscaRegioes)) {
          echo '<option value="'.$objBuscaRegioes->regiao.'" ';
          
          if($regiao == $objBuscaRegioes->regiao)
            echo 'selected';
          
          echo '>'.bd2texto($objBuscaRegioes->regiao).'</option>';
        }
        
        desconectabd($con);
        ?>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl sep" align="right"><label for="cidade">Cidade:</label></td>
    <td class="sep">&nbsp;</td>
    <td class="tdbr sep">
      <select name="cidade" style="width: 250px;">
        <option value="TODOS" <? if($cidade == 'TODOS') echo 'selected' ?>>Todas as Cidades</option>
        <?
        $con = conectabd();
            
        $SqlBuscaCidades = "SELECT DISTINCT ica.cidade FROM $tabela ica INNER JOIN ipi_pizzarias ip ON (ica.cidade = ip.cidade) WHERE ip.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY ica.cidade";
        $resBuscaCidades = mysql_query($SqlBuscaCidades);
            
        while($objBuscaCidades = mysql_fetch_object($resBuscaCidades)) {
          echo '<option value="'.$objBuscaCidades->cidade.'" ';
              
          if($cidade == $objBuscaCidades->cidade)
            echo 'selected';
              
            echo '>'.bd2texto($objBuscaCidades->cidade).'</option>';
          }
            
          desconectabd($con);
          ?>
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

$SqlBuscaRegistros = "SELECT c.*, p.nome,tax.descricao_taxa,pedmin.descricao, tax.valor_frete as tax_frete,pedmin.valor_pedido_minimo as ped_min, tax.tipo_frete FROM $tabela c INNER JOIN ipi_pizzarias p ON (c.cod_pizzarias = p.cod_pizzarias) left JOIN ipi_pedido_minimo pedmin on pedmin.cod_pedido_minimo = c.cod_pedido_minimo LEFT JOIN ipi_taxa_frete tax on tax.cod_taxa_frete = c.cod_taxa_frete WHERE c.$opcoes LIKE '%$filtro%' ";

if ($bairro != 'TODOS')
  $SqlBuscaRegistros .= " AND c.bairro = '$bairro'";  
  
if ($regiao != 'TODOS')
  $SqlBuscaRegistros .= " AND c.regiao = '$regiao'";

if ($cidade != 'TODOS')
  $SqlBuscaRegistros .= " AND c.cidade = '$cidade'";

if ($cod_pizzarias != 'TODOS')
  $SqlBuscaRegistros .= " AND p.cod_pizzarias = '$cod_pizzarias'";
  
$SqlBuscaRegistros .= " AND p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
  
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

$SqlBuscaRegistros .= '  ORDER BY c.rua, c.bairro, c.regiao, c.cidade, c.estado LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

//echo $SqlBuscaRegistros;

echo "<center><b>".$numBuscaRegistros." registro(s) encontrado(s)</center></b><br>";

if ((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir')) $pagina--;

echo '<center>';

$numpag = ceil(((int) $numBuscaRegistros) / ((int) $quant_pagina));

for ($b = 0; $b < $numpag; $b++) {
  echo '<form name="frmPaginacao'.$b.'" method="post">';
  echo '<input type="hidden" name="pagina" value="'.$b.'">';
  echo '<input type="hidden" name="filtro" value="'.$filtro.'">';
  echo '<input type="hidden" name="opcoes" value="'.$opcoes.'">';
  
  echo '<input type="hidden" name="bairro" value="'.$bairro.'">';
  echo '<input type="hidden" name="regiao" value="'.$regiao.'">';
  echo '<input type="hidden" name="cidade" value="'.$cidade.'">';
  echo '<input type="hidden" name="cod_pizzarias" value="'.$cod_pizzarias.'">';
  
  echo '<input type="hidden" name="acao" value="buscar">';
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

<table><tr>

<!-- Conteúdo -->
<td class="conteudo">

  <form name="frmExcluir" id="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
      <tr>
        <td width="100"><input class="botaoAzul" type="submit" value="Desassociar Selecionados"></td>

        <td width="300"><label>Definir Distância (Km):</label>&nbsp;<input type="text" size="5" maxlength="8" name="distancia" onkeypress="return ApenasNumero(event);">&nbsp;<input class="botaoAzul" type="button" value="Definir Distância" onclick="definir_distancia();"></td>
        
        <td><label>Grupo do Frete:</label>&nbsp;<select name='frete' id='frete'><option value=""></option>
        <? 
          $sql_buscar_fretes = "select * from ipi_taxa_frete where cod_pizzarias in ($cod_pizzarias_usuario) order by cod_pizzarias, descricao_taxa ASC";
          $res_buscar_fretes = mysql_query($sql_buscar_fretes);
          while($obj_buscar_fretes = mysql_fetch_object($res_buscar_fretes))
          {
            if ($obj_buscar_fretes->tipo_frete!="VARIAVEL")
            {
                echo "<option value='".$obj_buscar_fretes->cod_taxa_frete."'>".$obj_buscar_fretes->descricao_taxa." ( R$ ".bd2moeda($obj_buscar_fretes->valor_frete)." ) </option>";
            }
            else
            {
                 echo "<option value='".$obj_buscar_fretes->cod_taxa_frete."'>".$obj_buscar_fretes->descricao_taxa." </option>";
            }
            
          }


        ?></select></td>

        <td><label>Grupo do Pedido Minimo:</label>&nbsp;<select name='pedido_minimo' id='pedido_minimo'><option value=""></option>
        <? 
          $sql_buscar_fretes = "select * from ipi_pedido_minimo order by cod_pedido_minimo";
          $res_buscar_fretes = mysql_query($sql_buscar_fretes);
          while($obj_buscar_fretes = mysql_fetch_object($res_buscar_fretes))
          {
            echo "<option value='".$obj_buscar_fretes->cod_pedido_minimo."'>".$obj_buscar_fretes->descricao." ( R$ ".bd2moeda($obj_buscar_fretes->valor_pedido_minimo)." ) </option>";
          }


        ?></select>&nbsp;<input class="botaoAzul" type="button" value="Definir Frete" onclick="definir_frete();"></td>
      </tr>
    </table>
  
    <table class="listaEdicao" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
          <td align="center"><? echo ucfirst(TIPO_EMPRESA) ?></td>
          <td align="center">Rua</td>
          <td align="center">Ponto de Referência</td>
          <td align="center">Condominio</td>
          <td align="center">Bairro</td>
          <td align="center">Região</td>
          <td align="center">Cidade</td>
          <td align="center">Estado</td>
          <td align="center">Complemento</td>
          <td align="center" width="80">CEP Inicial</td>
          <td align="center" width="80">CEP Final</td>
          <td align="center" width="80">Distância (Km)</td>
          <td align="center" width="80">Taxa de Entrega</td>
          <td align="center" width="80">Pedido Minimo</td>
        </tr>
      </thead>
      <tbody>
      
      <?
      
      $con = conectabd();
      
      //$SqlBuscaRegistros = "SELECT c.*, p.nome FROM $tabela c INNER JOIN ipi_pizzarias p ON (c.cod_pizzarias = p.cod_pizzarias) ORDER BY c.rua, c.bairro, c.regiao";
      //$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
      
      while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
        echo '<tr>';
        
        $cep_inicial_inicio = substr($objBuscaRegistros->cep_inicial, 0, 5); 
        $cep_inicial_final = substr($objBuscaRegistros->cep_inicial, +5, 8);
        $cep_final_inicio = substr($objBuscaRegistros->cep_final, 0, 5); 
        $cep_final_final = substr($objBuscaRegistros->cep_final, +5, 8); 
        
        echo '<td align="center"><input type="checkbox" class="marcar excluir" name="desassociar[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->nome).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->rua).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->ponto_referencia).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->condominio).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->bairro).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->regiao).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->cidade).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->estado).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->complemento).'</td>';
        echo '<td align="center">'.$cep_inicial_inicio .'-'.$cep_inicial_final.'</td>';
        echo '<td align="center">'.$cep_final_inicio .'-'.$cep_final_final.'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->distancia).'</td>';
        echo '<td align="center">'.$objBuscaRegistros->descricao_taxa." <br/>". ($objBuscaRegistros->tipo_frete !="VARIAVEL" ?  "(R$ ".bd2moeda($objBuscaRegistros->tax_frete).")" : "")."  </td>";
        echo '<td align="center">'.$objBuscaRegistros->descricao." <br/>( R$ ".bd2moeda($objBuscaRegistros->ped_min)." ) </td>";
        echo '</tr>';
      }

      desconectabd($con);
      
      ?>
      
      </tbody>
    </table>
  
    <input type="hidden" name="acao" value="desassociar">
  </form>

</td>
<!-- Conteúdo -->

<!-- Barra Lateral -->
<td class="lateral">
  <div class="blocoNavegacao">
    <ul>
      <li><a href="ipi_pizzaria.php"><? echo ucfirst(TIPO_EMPRESAS) ?></a></li>
      <li><a href="ipi_entregador.php">Entregadores</a></li>
    </ul>
  </div>
</td>
<!-- Barra Lateral -->

</tr></table>

<? rodape(); ?>
