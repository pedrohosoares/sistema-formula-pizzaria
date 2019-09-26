<?php

/**
 * Cadastro de Telemarketing Ativo.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/01/2009   Elias         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Telemarketing Ativo');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_telemarketing_ativo';
$tabela = 'ipi_telemarketing_ativo';
$campo_ordenacao = 'mensagem';
$campo_filtro_padrao = 'mensagem';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        
        if (mysql_query($sql_del))
        {
            mensagemOK('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        
        $mensagem = texto2bd(validaVarPost('mensagem'));
        $data_inicial_prog = data2bd(validaVarPost('data_inicial'));
        $data_final_prog = data2bd(validaVarPost('data_final'));
        $situacao = texto2bd(validaVarPost('situacao'));
        $cod_pizzarias = texto2bd(validaVarPost('cod_pizzarias'));
		$mensagem_obrigatoria = (validaVarPost('mensagem_obrigatoria') == 'on') ? 1 : 0;
        
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_usuarios, cod_pizzarias, situacao, data_final_prog, data_inicial_prog, mensagem, data_hora_telemarketing, mensagem_obrigatoria) VALUES (".$_SESSION['usuario']['codigo'].", '%s', '%s', '%s', '%s', '%s', NOW(), '%s')", $cod_pizzarias, $situacao, $data_final_prog, $data_inicial_prog, $mensagem, $data_hora_telemarketing, $mensagem_obrigatoria);
            if (mysql_query($sql_edicao))
            {
                mensagemOK('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_usuarios  = ".$_SESSION['usuario']['codigo'].", cod_pizzarias  = '%s', situacao  = '%s' , data_final_prog = '%s' , data_inicial_prog = '%s' , mensagem = '%s', mensagem_obrigatoria = '%s' WHERE $chave_primaria = $codigo", $cod_pizzarias, $situacao, $data_final_prog, $data_inicial_prog, $mensagem, $mensagem_obrigatoria);
            if (mysql_query($sql_edicao))
            {
                mensagemOK('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        //echo $sql_edicao;
        desconectabd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
<script language="javascript" src="../lib/js/calendario.js"></script>

<script>
window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 

function verificar_checkbox(form) 
{
    var cInput = 0;
    var checkBox = form.getElementsByTagName('input');

    for (var i = 0; i < checkBox.length; i++)
    {
        if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) 
        { 
            cInput++; 
        }
    }
   
    if(cInput > 0) 
    {
        if (confirm('Deseja excluir os registros selecionados?'))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    else 
    {
        alert('Por favor, selecione os itens que deseja excluir.');
     
        return false;
    }
}

function editar(cod) 
{
    var form = new Element('form', 
    {
        'action': '<?echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
    });
  
    var input = new Element('input', 
    {
        'type': 'hidden',
        'name': '<?echo $chave_primaria?>',
        'value': cod
    });
  
    input.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
}

window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
  
    if (document.frmIncluir.<?echo $chave_primaria?>.value > 0) 
    {
        <?
        if ($acao == '')
        {
            echo 'tabs.irpara(1);';
        }
        ?>
    
        document.frmIncluir.botao_submit.value = 'Alterar';
    }
    else 
    {
        document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  
    tabs.addEvent('change', function(indice)
    {
        if(indice == 1)
        {
            document.frmIncluir.<?echo $chave_primaria?>.value = '';
            document.frmIncluir.cod_pizzarias.value = '';
            document.frmIncluir.situacao.value = '';
            document.frmIncluir.data_final.value = '';
            document.frmIncluir.data_inicial.value = '';
            document.frmIncluir.mensagem.value = '';
      		document.frmIncluir.mensagem_obrigatoria.checked = true;
      		
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
        <!-- Conteúdo -->
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
                    <option value="<? echo $campo_filtro_padrao ?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Mensagem</option>
                </select></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr">
                    <input type="text" name="filtro" size="60" value="<?echo $filtro?>">
                </td>
            </tr>

            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3">
                    <input class="botaoAzul" type="submit" value="Buscar">
                </td>
            </tr>

        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br>

        <?
        $conexao = conectabd();
        
        $sql_buscar_registros = "SELECT * FROM $tabela t INNER JOIN nuc_usuarios u ON(t.cod_usuarios=u.cod_usuarios) INNER JOIN ipi_pizzarias p ON(t.cod_pizzarias=p.cod_pizzarias) WHERE  $opcoes LIKE '%$filtro%' ";
        
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

        <form name="frmExcluir" method="post" onsubmit="return verificar_checkbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="<?echo LARGURA_PADRAO?>">
            <tr>
                <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="<?echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20">  
                        <input type="checkbox" onclick="marcaTodos('marcar');">
                    </td>
                    <td align="center">Mensagens</td>
                    <td align="center">Pizzarias</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->mensagem) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->nome) . '</td>';
                echo '</tr>';
            }
            
            desconectabd($conexao);
            
            ?>
          
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir"></form>

<?
if ($exibir_barra_lateral)
:
?>

        </td>
        <!-- Conteúdo -->

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

    </tr>
</table>

<? endif;?>

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
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

	<tr>
		<td class="legenda tdbl tdbt tdbr" colspan="2"><label class="requerido"	for="cod_pizzarias">Pizzarias</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr" colspan="2"><select name="cod_pizzarias" id="cod_pizzarias">
			<option value=""></option>
	        <?
	        $con = conectabd();
	        
	        $sql_busca_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
	        $res_busca_pizzarias = mysql_query($sql_busca_pizzarias);
	        
	        while ($obj_busca_pizzarias = mysql_fetch_object($res_busca_pizzarias))
	        {
	            echo '<option value="' . $obj_busca_pizzarias->cod_pizzarias . '" ';
	            
	            if ($obj_busca_pizzarias->cod_pizzarias == $obj_editar->cod_pizzarias)
	                echo 'selected';
	            
	            echo '>' . bd2texto($obj_busca_pizzarias->nome) . '</option>';
	        }
	        
	        desconectabd($con);
	        ?>
      </select></td>
	</tr>

	<tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="mensagem">Mensagem</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="mensagem" id="mensagem" maxlength="250" size="45" value="<?echo bd2texto($obj_editar->mensagem)?>">
        </td>
    </tr>
	
	
	<tr>
        <td class="legenda tdbl tdbr">
            <label for="data_inicial">Data Inicial:</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" name="data_inicial" id="data_inicial" size="12" value="<?echo bd2data($obj_editar->data_inicial_prog)?>" onkeypress="return MascaraData(this, event)">
		    &nbsp;
		    <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
        </td>
    </tr>
  
  	<tr>
        <td class="legenda tdbl tdbr">
            <label for="data_final">Data Final:</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
			<input type="text" name="data_final" id="data_final" size="12" value="<?echo bd2data($obj_editar->data_final_prog)?>" onkeypress="return MascaraData(this, event)">
		    &nbsp;
		    <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="situacao">Situação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="situacao" id="situacao">
            <option value=""></option>
            <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
            <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
        </select>
        </td>
    </tr>
	
	<tr>
		<td class="tdbl tdbr" colspan="2">
			<input type="checkbox" name="mensagem_obrigatoria" id="mensagem_obrigatoria" <? if ($obj_editar->mensagem_obrigatoria) echo 'checked="checked"'?>>&nbsp;<label for="mensagem_obrigatoria">Mensagem Obrigatória</label>
		</td>
	</tr>
	
    <tr>
        <td align="center" class="tdbl tdbb tdbr">
            <input name="botao_submit" class="botao" type="submit" value="Cadastrar">
        </td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<?echo $chave_primaria?>" value="<?echo $codigo?>">
</form>

</div>
<!-- Tab Incluir -->
</div>

<?
rodape();
?>