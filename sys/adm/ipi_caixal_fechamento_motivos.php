<?php

/**
 * Cadastro de Motivos de Gargalos para o fechamento de caixa.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICA��ES:
 *
 * VERS�O    DATA         AUTOR         DESCRI��O 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       03/10/2012   Filipe         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro Novidades da Central');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_motivos';
$tabela = 'ipi_caixa_motivos';
$campo_ordenacao = 'motivo';
$campo_filtro_padrao = 'motivo';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del = "UPDATE $tabela SET status='EXCLUIDO' WHERE $chave_primaria IN ($indices_sql)";
        
        if (mysql_query($sql_del))
        {
            mensagemok('Os registros selecionados foram exclu�dos com sucesso!');
        }
        else
        {
            mensagemerro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usu�rios selecionados para exclus�o.');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
				$status = validaVarPost('status');
        $motivo = validaVarPost('motivo');
				
        $conexao = conectabd();
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (motivo) VALUES ('%s', '%s')", $motivo,$status);
            $res_edicao = mysql_query($sql_edicao);
            if ($res_edicao)
            {
                $codigo = mysql_insert_id();
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET motivo = '%s', situacao = '%s', WHERE $chave_primaria = $codigo", $motivo, $staus);
            $res_edicao = mysql_query($sql_edicao);
        }
        
        if ($res_edicao)
        {
            mensagemok('Registro alterado com �xito!');
        }  
        else
        {
            mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro j� n�o se encontra cadastrado.');
        }        
        
        desconectabd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>

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
    'action': '<?
    echo $_SERVER['PHP_SELF']?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<?
    echo $chave_primaria?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<? echo $chave_primaria?>.value > 0) {
    <?
    if ($acao == '')
        echo 'tabs.irpara(1);';
    ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      	document.frmIncluir.<? echo $chave_primaria?>.value = '';
        document.frmIncluir.titulo_novidade.value = '';
        document.frmIncluir.novidade.value = '';
        document.frmIncluir.data_novidade.value = '';
        document.frmIncluir.status.value = '';
        tinyMCE.getInstanceById('texto').setContent('');
      
        if($('foto_figura')) 
        {
        	$('foto_figura').destroy();
		}
      
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

<? if ($exibir_barra_lateral): ?>

<table>
    <tr>

        <!-- Conte�do -->
        <td class="conteudo">
        

<? endif; ?>

        <?
        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
        $filtro = validaVarPost('filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right">
                	<select name="opcoes">
                    	<option value="<? echo $campo_filtro_padrao ?>"<? if ($opcoes == $campo_filtro_padrao) {echo 'selected';}?>>T�tulo</option>
                	</select>
                </td>
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
        
        $sql_buscar_registros = "SELECT * FROM $tabela t WHERE t.$opcoes LIKE '%$filtro%' ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY t.data_novidade DESC LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        //echo $sql_buscar_registros."<br/>";
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
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Pr�xima&nbsp;&raquo;</a>';
        }
        else
        {
            echo '<span style="margin-left: 5px;">Pr�xima&nbsp;&raquo;</span>';
        }
        
        echo '</center>';
        
        ?>

        <br>

        <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
                <td><input class="botaoAzul" type="submit"
                    value="Excluir Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
                    <td align="center" width="30">N� Motivo</td>
                    <td align="center" width="200">Motivo</td>
                    <td align="center" width="150">Status</td>
                </tr>
            </thead>
            <tbody>
            <?
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                
                echo '<td align="center">' . $obj_buscar_registros->cod_motivos . '</td>';
								
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->motivo) . '</a></td>';
								
                echo '<td align="center">'. bd2texto($obj_buscar_registros->status).'</td>';

                echo '</tr>';
            }
            desconectabd($conexao);
            ?>
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir">
        </form>

<?
if ($exibir_barra_lateral)
:
    ?>

        </td>
        <!-- Conte�do -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li><a href="#">Atalho 1</a></li>
            <li><a href="#">Atalho 2</a></li>
        </ul>
        </div>
        </td>
        <!-- Barra Lateral -->

</table>


<? endif;
?>

</div>

<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">

    <?
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="motivo">Motivo</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="motivo" id="motivo" maxlength="100" size="45" value="<? echo bd2texto($obj_editar->motivo)?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label for="status">Situacao</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" colspan="2">
            <select class="input" name="status">
                <option value="ATIVO" <? if($obj_editar->status == 'ATIVO') echo 'SELECTED' ?>>Ativo</option>  
                <option value="INATIVO" <? if($obj_editar->status == 'INATIVO') echo 'SELECTED' ?>>Inativo</option>
            </select>
        </td>
    </tr>

    <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<? echo $chave_primaria?>" value="<?  echo $codigo?>">

</form>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>