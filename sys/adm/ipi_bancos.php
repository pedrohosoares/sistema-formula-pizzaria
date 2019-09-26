<?php

/**
 * Cadastro de Bancos.
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

cabecalho('Cadastro de Bancos e Caixas');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_bancos';
$tabela = 'ipi_bancos';
$campo_ordenacao = 'banco';
$campo_filtro_padrao = 'banco';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del1 = "DELETE FROM ipi_bancos_ipi_pizzarias WHERE $chave_primaria IN ($indices_sql)";
        $sql_del2 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        
        $res_del1 = mysql_query($sql_del1);
        $res_del2 = mysql_query($sql_del2);
        
        if ($res_del1 && $res_del2)
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
        $banco = validaVarPost('banco');
        $agencia = validaVarPost('agencia');
        $conta_corrente = validaVarPost('conta_corrente');
        $caixa = (validaVarPost('caixa') == 'on') ? 1 : 0;
        $situacao = validaVarPost('situacao');
        
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (banco, agencia, conta_corrente, caixa, situacao) VALUES ('%s', '%s', '%s', '%s', '%s')", 
                                   $banco, $agencia, $conta_corrente, $caixa, $situacao);
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET banco = '%s', agencia = '%s', conta_corrente = '%s', caixa = '%s', situacao = '%s' WHERE $chave_primaria = $codigo", 
                                $banco, $agencia, $conta_corrente, $caixa, $situacao);
        }
        
        if(mysql_query($sql_edicao))
        {
            mensagemOK('Registro adicionado com êxito!');
        }
        else
        {
            mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
              
        desconectabd($conexao);
        
        break;
}

?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script>
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
        'action': '<?
        echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
    });
  
    var input = new Element('input', 
    {
        'type': 'hidden',
        'name': '<?
        echo $chave_primaria?>',
        'value': cod
    });
  
    input.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
}

function validar_pizzarias(formulario)
{
	return true;
}

window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
  
    if (document.frmIncluir.<?
    echo $chave_primaria?>.value > 0) 
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
            document.frmIncluir.<?
            echo $chave_primaria?>.value = '';
            document.frmIncluir.banco.value = '';
            document.frmIncluir.agencia.value = '';
            document.frmIncluir.conta_corrente.value = '';
            document.frmIncluir.situacao.value = '';
      		
      		marcaTodosEstado('marcar_pizzaria', false);
      		
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
        
        $sql_buscar_registros = "SELECT * FROM $tabela WHERE $opcoes LIKE '%$filtro%' ";
        
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

        <form name="frmExcluir" method="post"
            onsubmit="return verificar_checkbox(this)">

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
                    <td align="center" width="20"><input type="checkbox"
                        onclick="marcaTodos('marcar');"></td>
                    <td align="center">Banco / Caixa</td>
                    <td align="center">Agência</td>
                    <td align="center">Conta Corrente</td>
                    <td align="center">Situação</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->banco) . '</a></td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->agencia) .'</td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->conta_corrente) .'</td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->situacao) .'</td>';
                
                echo '</tr>';
            }
            
            desconectabd($conexao);
            
            ?>
          
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir"></form>

<? if ($exibir_barra_lateral): ?>

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
    
    <form name="frmIncluir" method="post" onsubmit="return ((validaRequeridos(this)) && (validar_pizzarias(this)))">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbt tdbr">
            <label class="requerido" for="banco">Banco / Caixa</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="banco" id="banco" maxlength="45" size="40" value="<?echo bd2texto($obj_editar->banco)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="agencia">Agência</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text"  name="agencia" id="agencia" maxlength="20" size="20" value="<? echo bd2texto($obj_editar->agencia) ?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="conta_corrente">Conta Corrente</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" name="conta_corrente" id="conta_corrente" maxlength="20" size="20" value="<?echo bd2texto($obj_editar->conta_corrente)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr sep">
            <input type="checkbox" name="caixa" id="caixa" <? echo ($obj_editar->caixa) ? 'checked' : '' ?>>
            &nbsp;
            <label for="caixa">Caixa</label>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="situacao">Situação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select class="requerido" name="situacao" id="situacao" style="width: 100px;">
                <option value=""></option>
                <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
                <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
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
<!-- Tab Incluir --></div>

<?
rodape();
?>
