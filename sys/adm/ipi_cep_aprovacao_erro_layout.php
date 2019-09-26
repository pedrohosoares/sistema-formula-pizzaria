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

cabecalho('Cadastro de CEP para Aprovação');

$acao = validaVarPost('acao');

$tabela = 'ipi_cep_aprovacao';
$chave_primaria = 'cod_cep_aprovacao';
$quant_pagina = 120;

switch($acao) {
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $rua = texto2bd(validaVarPost('ruaed'));
    $bairro = texto2bd(validaVarPost('bairroed'));
    $regiao = texto2bd(validaVarPost('regiaoed'));
    $cidade = texto2bd(validaVarPost('cidadeed'));
    $estado = texto2bd(validaVarPost('estadoed'));
    $complemento = texto2bd(validaVarPost('complementoed'));
    $cep_inicial = validaVarPost('cep_inicialed');
    $cep_final = validaVarPost('cep_finaled');
    
    $cep_inicial = str_replace('-', '', $cep_inicial);
    $cep_final = str_replace('-', '', $cep_final);
    
    $con = conectabd();
    
    if($codigo <= 0) {
      $SqlEdicao = sprintf("INSERT INTO $tabela (rua, bairro, regiao, cep_inicial, cep_final, cidade, estado, complemento) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
                           $rua, $bairro, $regiao, $cep_inicial, $cep_final, $cidade, $estado, $complemento);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET rua = '%s', bairro = '%s', regiao = '%s', cep_inicial = '%s', cep_final = '%s', cidade = '%s', estado = '%s', complemento = '%s'  WHERE $chave_primaria = $codigo", 
                           $rua, $bairro, $regiao, $cep_inicial, $cep_final, $cidade, $estado, $complemento);

      if(mysql_query($SqlEdicao))
        mensagemOk('Registro adicionado com êxito!');
      else
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
    }
    
    desconectabd($con);
  break;
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);  
  break;
  case 'associar':
    $cod_pizzarias = validaVarPost('cod_pizzarias');
    $associar = validaVarPost('excluir');
    $indicesSql = implode(',', $associar);
    
    $con = conectabd();
    
    $SqlInsert = "INSERT INTO ipi_cep (cod_pizzarias, cep_inicial, cep_final, rua, bairro, regiao, cidade, estado, complemento) (SELECT $cod_pizzarias, cep_inicial, cep_final, rua, bairro, regiao, cidade, estado, complemento FROM $tabela WHERE $chave_primaria IN ($indicesSql))";
    $SqlDelete = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlInsert) && mysql_query($SqlDelete))
      mensagemOk('Os registros selecionados foram associados com sucesso!');
    else
      mensagemErro('Erro ao associar os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para associação.');
    
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

<script>

function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
  
  if(cInput > 0) {
    if (confirm('Deseja associar/excluir os registros selecionados?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja associar/excluir.');
     
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

function associar() {
  if((document.frmExcluir.cod_pizzarias.value))
  {
    if(verificaCheckbox(document.frmExcluir)) 
    {
      document.frmExcluir.acao.value = "associar";
      document.frmExcluir.submit();
    }
  }
  else
  {
    alert('Selecione a pizzaria a qual o cep será associado.');
  }
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
        
      document.frmIncluir.ruaed.value = '';
      document.frmIncluir.bairroed.value = '';
      document.frmIncluir.regiaoed.value = '';
      document.frmIncluir.cidadeed.value = '';
      document.frmIncluir.estadoed.value = '';
      document.frmIncluir.complementoed.value = '';
      document.frmIncluir.cep_inicialed.value = '';
      document.frmIncluir.cep_finaled.value = '';
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Editar</a></li>
       <li><a href="javascript:;">Incluir</a></li>
    </ul>
  </div>
  
  <!-- Tab Editar -->
  <div class="painelTab">

    <? 
    
    $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 
    $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'bairro';
    $filtro = validaVarPost('filtro');
    
    $bairro = (validaVarPost('bairro')) ? validaVarPost('bairro') : 'TODOS';
    $regiao = (validaVarPost('regiao')) ? validaVarPost('regiao') : 'TODOS';
    $cidade = (validaVarPost('cidade')) ? validaVarPost('cidade') : 'TODOS';

    $cod_pizzarias = validaVarPost('cod_pizzarias');
    ?>
    
    <form name="frmFiltro" method="post">
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
        <td class="legenda tdbl" align="right"><label for="cidade">Cidade:</label></td>
        <td class="">&nbsp;</td>
        <td class="tdbr">
          <select name="cidade" style="width: 250px;">
            <option value="TODOS" <? if($cidade == 'TODOS') echo 'selected' ?>>Todas as Cidades</option>
            <?
            $con = conectabd();
            //INNER JOIN ipi_cep ic ON (ic.cep_inicial = ica.cep_inicial AND ic.cep_final = ica.cep_final)
            $SqlBuscaCidades = "SELECT DISTINCT ica.cidade FROM $tabela ica INNER JOIN ipi_pizzarias ip ON (ica.cidade = ip.cidade) WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY ica.cidade";
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
      
      <tr>
        <td class="legenda tdbl" align="right"><label for="regiao">Região:</label></td>
        <td class="">&nbsp;</td>
        <td class="tdbr">
          <select name="regiao" style="width: 250px;">
            <option value="TODOS" <? if($regiao == 'TODOS') echo 'selected' ?>>Todos as Regiões</option>
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

      <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
      
      </table>
      
      <input type="hidden" name="acao" value="buscar">
    </form>
    
    <br>
    </div>
    <?
    $acao = validar_var_post('acao');
    if($acao == "buscar"):
      $con = conectabd();
      
      $SqlBuscaRegistros = "SELECT ica.cod_cep_aprovacao, ica.rua, ica.bairro, ica.regiao, ica.cidade, ica.estado, ica.cep_inicial, ica.cep_final, ica.complemento FROM $tabela ica INNER JOIN ipi_pizzarias ip ON (ica.cidade = ip.cidade) WHERE ica.$opcoes LIKE '%$filtro%' AND ip.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ";

      //Descomentar essa query quando tentar associar uma pizzaria de uma cidade diferente da cidade da matriz
      //$SqlBuscaRegistros = "SELECT * FROM $tabela ica WHERE ica.$opcoes LIKE '%$filtro%' ";

      
      if ($bairro != 'TODOS')
        $SqlBuscaRegistros .= " AND ica.bairro = '".texto2bd($bairro)."'";  
        
      if ($regiao != 'TODOS')
        $SqlBuscaRegistros .= " AND ica.regiao = '".texto2bd($regiao)."'";
      
      if ($cidade != 'TODOS')
        $SqlBuscaRegistros .= " AND ica.cidade = '".texto2bd($cidade)."'";
   
      $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
      $numBuscaRegistros = mysql_num_rows($resBuscaRegistros);
      
      $SqlBuscaRegistros .= ' GROUP BY ica.cod_cep_aprovacao  ORDER BY ica.rua, ica.bairro, ica.regiao, ica.cidade, ica.estado LIMIT '.($quant_pagina * $pagina).', '.$quant_pagina;
      $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
      $linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);
      
      //echo $SqlBuscaRegistros;
      
      echo "<center><b>".$numBuscaRegistros." registro(s) encontrado(s)</b></center><br />";
      
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
    
      <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td style="width: 250px;">
              <select name="cod_pizzarias" style="width: 250px;">
                <option value=""></option>
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
            <td width="50"><input class="botaoAzul" type="button" value="Associar Selecionados" onclick="associar();"></td>
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">Rua</td>
              <td align="center">Bairro</td>
              <td align="center">Região</td>
              <td align="center">Cidade</td>
              <td align="center">Estado</td>
              <td align="center">Complemento</td>
              <td align="center" width="80">CEP Inicial</td>
              <td align="center" width="80">CEP Final</td>
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          while ($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros)) {
            echo '<tr>';
            
            $cep_inicial_inicio = substr($objBuscaRegistros->cep_inicial, 0, 5); 
            $cep_inicial_final = substr($objBuscaRegistros->cep_inicial, +5, 8);
            $cep_final_inicio = substr($objBuscaRegistros->cep_final, 0, 5); 
            $cep_final_final = substr($objBuscaRegistros->cep_final, +5, 8); 
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->rua).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->bairro).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->regiao).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->cidade).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->estado).'</a></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->complemento).'</a></td>';
            echo '<td align="center">'.$cep_inicial_inicio.'-'.$cep_inicial_final.'</td>';
            echo '<td align="center">'.$cep_final_inicio.'-'.$cep_final_final.'</td>';
            
            echo '</tr>';
          }
          
          desconectabd($con);
          
          ?>
          
          </tbody>
        </table>
      
        <input type="hidden" name="acao" value="excluir">
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="ipi_pizzaria.php">Pizzarias</a></li>
          <li><a href="ipi_entregador.php">Entregadores</a></li>
        </ul>
      </div>
    </td>
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Editar -->
  <? endif; ?>
  
  
  <!-- Tab Incluir -->
  <div class="painelTab">

    <? 
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if($codigo > 0) {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }

    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">

    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="ruaed">Rua</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" class="requerido" name="ruaed" id="ruaed" maxlength="80" size="50" value="<? echo texto2bd($objBusca->rua) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="bairroed">Bairro</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" class="requerido" name="bairroed" id="bairroed" maxlength="80" size="50" value="<? echo texto2bd($objBusca->bairro) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="regiaoed">Região</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" name="regiaoed" id="regiaoed" maxlength="80" size="50" value="<? echo texto2bd($objBusca->regiao) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cidadeed">Cidade</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="cidadeed" id="cidadeed" maxlength="80" size="50" value="<? echo texto2bd($objBusca->cidade) ?>"></td></tr>

    <tr><td class="legenda tdbl tdbr"><label for="complementoed">Complemento</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" name="complementoed" id="complementoed" maxlength="80" size="50" value="<? echo texto2bd($objBusca->complemento) ?>"></td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="estadoed">Estado</label></td></tr>
    <tr><td class="tdbl tdbr sep">

    <select name="estadoed" class="requerido">
	    <option value=""></option>
			<option value="AC" <? if ($objBusca->estado=='AC') echo 'selected'?>>AC</option>
			<option value="AL" <? if ($objBusca->estado=='AL') echo 'selected'?>>AL</option>
			<option value="AM" <? if ($objBusca->estado=='AM') echo 'selected'?>>AM</option>
			<option value="AP" <? if ($objBusca->estado=='AP') echo 'selected'?>>AP</option>
			<option value="BA" <? if ($objBusca->estado=='BA') echo 'selected'?>>BA</option>
			<option value="CE" <? if ($objBusca->estado=='CE') echo 'selected'?>>CE</option>
			<option value="DF" <? if ($objBusca->estado=='DF') echo 'selected'?>>DF</option>
			<option value="ES" <? if ($objBusca->estado=='ES') echo 'selected'?>>ES</option>
			<option value="GO" <? if ($objBusca->estado=='GO') echo 'selected'?>>GO</option>
			<option value="MA" <? if ($objBusca->estado=='MA') echo 'selected'?>>MA</option>
			<option value="MG" <? if ($objBusca->estado=='MG') echo 'selected'?>>MG</option>
			<option value="MS" <? if ($objBusca->estado=='MS') echo 'selected'?>>MS</option>
			<option value="MT" <? if ($objBusca->estado=='MT') echo 'selected'?>>MT</option>
			<option value="PA" <? if ($objBusca->estado=='PA') echo 'selected'?>>PA</option>
			<option value="PB" <? if ($objBusca->estado=='PB') echo 'selected'?>>PB</option>
			<option value="PE" <? if ($objBusca->estado=='PE') echo 'selected'?>>PE</option>
			<option value="PI" <? if ($objBusca->estado=='PI') echo 'selected'?>>PI</option>
			<option value="PR" <? if ($objBusca->estado=='PR') echo 'selected'?>>PR</option>
			<option value="RJ" <? if ($objBusca->estado=='RJ') echo 'selected'?>>RJ</option>
			<option value="RN" <? if ($objBusca->estado=='RN') echo 'selected'?>>RN</option>
			<option value="RO" <? if ($objBusca->estado=='RO') echo 'selected'?>>RO</option>
			<option value="RR" <? if ($objBusca->estado=='RR') echo 'selected'?>>RR</option>
			<option value="RS" <? if ($objBusca->estado=='RS') echo 'selected'?>>RS</option>
			<option value="SC" <? if ($objBusca->estado=='SC') echo 'selected'?>>SC</option>
			<option value="SE" <? if ($objBusca->estado=='SE') echo 'selected'?>>SE</option>
			<option value="SP" <? if ($objBusca->estado=='SP') echo 'selected'?>>SP</option>
			<option value="TO" <? if ($objBusca->estado=='TO') echo 'selected'?>>TO</option>
		</select>      
    </td>
    </tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cep_inicialed">CEP Inicial</label></td></tr>
    <tr><td class="tdbl tdbr"><input class="requerido" type="text" name="cep_inicialed" id="cep_inicialed" maxlength="10" size="10" onkeypress="return MascaraCEP(this, event);" value="<? echo texto2bd($objBusca->cep_inicial) ?>"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="cep_finaled">CEP Final</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="cep_finaled" id="cep_finaled" maxlength="10" size="10" onkeypress="return MascaraCEP(this, event);" value="<? echo texto2bd($objBusca->cep_final) ?>"></td></tr>
    
    <tr><td colspan="2" align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td></tr>
    </table>

    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>

  </div>
</div>

<? rodape(); ?>
