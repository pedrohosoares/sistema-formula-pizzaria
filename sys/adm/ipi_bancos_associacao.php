<?php

/**
 * ipi_bancos_ipi_pizzarias.php: Cadastro de associação entre banco e pizzaria
 * 
 * Índice: cod_bancos_ipi_pizzarias
 * Tabela: ipi_bancos_ipi_pizzarias
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Associações Entre Banco e Pizzaria');

$acao = validaVarPost('acao');

$tabela = 'ipi_bancos_ipi_pizzarias';
$chave_primaria = 'cod_bancos_pizzarias';
$campo_ordenacao = 'banco';
$campo_filtro_padrao = 'banco';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    if (mysql_query($SqlDel))
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
  break;
  case 'editar':
        $banco = validaVarPost('banco');
        $cod_pizzarias = validaVarPost('pizzarias');
        
        $conexao = conectabd();          
        $sql_del_pizzarias_bancos = "DELETE FROM ipi_bancos_ipi_pizzarias WHERE cod_bancos = '$banco'";
        $res_del_pizzarias_bancos = mysql_query($sql_del_pizzarias_bancos);
        
        $res_inserir_pizzarias_bancos = true;
        
        $sql_inserir_pizzarias_bancos = "INSERT INTO ipi_bancos_ipi_pizzarias (cod_pizzarias, cod_bancos) VALUES ($cod_pizzarias, $banco)";
        $res_inserir_pizzarias_bancos &= mysql_query($sql_inserir_pizzarias_bancos);
                    
        if($res_inserir_pizzarias_bancos)
        {
            mensagemOK('Registro adicionado com êxito!');
        }
        else
        {
            mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
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
    if (confirm('Deseja excluir os registros selecionados?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja excluir.');
     
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
      document.frmIncluir.cod_bebidas.value = '';
      document.frmIncluir.cod_conteudos.value = '';
      document.frmIncluir.preco.value = '';
      document.frmIncluir.quantidade_minima.value = '';
      document.frmIncluir.quantidade_maxima.value = '';
      document.frmIncluir.quantidade_perda.value = '';
      
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
    <table><tr>
  
    <!-- Conteúdo -->
    
    <?
        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
        $filtro = validaVarPost('filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right"><select
                    name="opcoes">
                    <option value="<? echo $campo_filtro_padrao ?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Banco / Caixa</option>
                </select></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>

            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
                    class="botaoAzul" type="submit" value="Buscar"></td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br>

        <?
        
        $conexao = conectabd();
        
        $sql_buscar_registros = "SELECT * FROM $tabela ibip INNER JOIN ipi_pizzarias ip ON(ibip.cod_pizzarias = ip.cod_pizzarias) INNER JOIN ipi_bancos ib ON(ibip.cod_bancos=ib.cod_bancos) WHERE $opcoes LIKE '%$filtro%' ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY ' . $campo_ordenacao . ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        //echo $sql_buscar_registros;

        echo "<center><b>" . $num_buscar_registros . " registro(s) encontrado(s)</center></b><br>";
        
        if ((($quant_pagina * $pagina) == $num_buscar_registros) && ($pagina != 0) && ($acao == 'excluir'))
        {
            $pagina--;
        }
        
        echo '<center>';
        
        $numpag = ceil(((int) $num_buscar_registros) / ((int) $quant_pagina));
        
        for ($b = 0; $b < $numpag; $b++)
        {
            echo '<form name="frmPaginacao' . $b . '" method="post">';
            echo '<input type="hidden" name="pagina" value="' . $b . '">';
            echo '<input type="hidden" name="filtro" value="' . $filtro . '">';
            echo '<input type="hidden" name="opcoes" value="' . $opcoes . '">';
            
            echo '<input type="hidden" name="acao" value="buscar">';
            echo "</form>";
        }
        
        if ($pagina != 0)
        {
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina - 1) . '.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
        }
        else
        {
            echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';
        }
        
        for ($b = 0; $b < $numpag; $b++)
        {
            if ($b != 0)
            {
                echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
            }
            
            if ($pagina != $b)
            {
                echo '<a href="#" onclick="javascript:frmPaginacao' . $b . '.submit();">' . ($b + 1) . '</a>';
            }
            else
            {
                echo '<span><b>' . ($b + 1) . '</b></span>';
            }
        }
        
        if (($quant_pagina == $linhas_buscar_registros) && ((($quant_pagina * $pagina) + $quant_pagina) != $num_buscar_registros))
        {
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
        }
        else
        {
            echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';
        }
        
        echo '</center>';
        
        ?>

        <br>
        
    <td class="conteudo">
    
      <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
          <thead>
            <tr>
              <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
              <td align="center">Banco / Caixa</td>
              <td align="center">Agencia</td>
              <td align="center">Nº Conta</td>
              <td align="center">Pizzaria</td>    
            </tr>
          </thead>
          <tbody>
          
          <?
          
          $con = conectabd();
          
          while ($objBuscaRegistros = mysql_fetch_object($res_buscar_registros)) {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.$objBuscaRegistros->banco.'</a></td>';
            echo '<td align="center">'.$objBuscaRegistros->agencia.'</td>';
            echo '<td align="center">'.$objBuscaRegistros->conta_corrente.'</td>';
            echo '<td align="center">'.$objBuscaRegistros->nome.'</td>';
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
        
    </tr></table>
  </div>
  <!-- Tab Editar -->
  
  
  
  <!-- Tab Incluir -->
  <div class="painelTab">
    <?
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela ibip INNER JOIN ipi_pizzarias ip ON(ibip.cod_pizzarias = ip.cod_pizzarias) INNER JOIN ipi_bancos ib ON(ibip.cod_bancos=ib.cod_bancos) WHERE $chave_primaria = $codigo");
    }
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return ((validaRequeridos(this)) && (validar_pizzarias(this)))">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbt tdbr">
            <label class="requerido" for="banco">Banco / Caixa</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
           <select class="requerido" name="banco" id="banco" style="width: 450px;">
                <option value=""></option>
                <?
                  $con = conectabd();
        
                  $SqlBuscaConteudos = "SELECT banco, cod_bancos,agencia,conta_corrente FROM ipi_bancos ORDER BY banco";
                  $resBuscaConteudos = mysql_query($SqlBuscaConteudos);
                  
                  while($objBuscaConteudos = mysql_fetch_object($resBuscaConteudos)) {
                    echo '<option value="'.$objBuscaConteudos->cod_bancos.'" ';
                    
                    if($objBuscaConteudos->cod_bancos == $obj_editar->cod_bancos)
                      echo 'selected';
                      
                    echo '>'.bd2texto($objBuscaConteudos->banco);
                    if($objBuscaConteudos->agencia)
                      echo '    -AG: '.bd2texto($objBuscaConteudos->agencia);
                    if($objBuscaConteudos->conta_corrente)  
                    echo '    -CC: '.bd2texto($objBuscaConteudos->conta_corrente);

                    echo '</option>';
                  }
                  
                  desconectabd($con);
                ?>
            </select>
        </td>
    </tr>
        
    <tr>
        <td class="tdbl tdbr sep">
           <select class="requerido" name="pizzarias" id="pizzarias" style="width: 450px;">
                <option value=""></option>
                <?
                  $con = conectabd();
        
                  $SqlBuscaConteudos = "SELECT * FROM ipi_pizzarias";
                  $resBuscaConteudos = mysql_query($SqlBuscaConteudos);
                  
                  while($objBuscaConteudos = mysql_fetch_object($resBuscaConteudos)) {
                    echo '<option value="'.$objBuscaConteudos->cod_pizzarias.'" ';
                    
                    if($objBuscaConteudos->cod_pizzarias == $obj_editar->cod_pizzarias)
                      echo 'selected';
                      
                    echo '>'.bd2texto($objBuscaConteudos->nome).'</option>';
                  }
                  
                  desconectabd($con);
                ?>
            </select>
        </td>
    </tr>
	
    <tr>
        <td align="center" class="tdbl tdbb tdbr">
        	<input name="botao_submit" class="botao" type="submit" value="Cadastrar">
    	</td>
    </tr>
</table>

<input type="hidden" name="acao" value="editar"> <input type="hidden"
    name="<?
    echo $chave_primaria?>" value="<?
    echo $codigo?>"></form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
