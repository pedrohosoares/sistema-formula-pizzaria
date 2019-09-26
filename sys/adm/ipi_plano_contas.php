<?php

/**
 * Cadastro de plano de contas.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       02/06/2011   Elias         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Plano de Contas');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_plano_contas';
$tabela = 'ipi_plano_contas';
$campo_ordenacao = 'conta_indice';
$campo_filtro_padrao = 'conta_indice';
$quant_pagina = 100;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del_1 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        $sql_del_2 = "DELETE FROM $tabela WHERE cod_plano_contas_pai IN ($indices_sql)";
        
        //echo "<br>1: ".$sql_del_1;
        //echo "<br>2: ".$sql_del_2;

        if (mysql_query($sql_del_1) && mysql_query($sql_del_2))
        {
           mensagemOK('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros', 'Existe uma subcategoria vinculada!');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $cod_plano_contas_pai = validaVarPost('cod_plano_contas_pai');
        $conta_indice = texto2bd(validaVarPost('conta_indice'));
        $conta_nome = texto2bd(validaVarPost('conta_nome'));
        $tipo_conta = texto2bd(validaVarPost('tipo_conta'));
        $situacao = texto2bd(validaVarPost('situacao'));
                
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (conta_indice, conta_nome, tipo_conta, situacao, cod_plano_contas_pai) VALUES ('%s', '%s', '%s', '%s', '%s')", 
                $conta_indice, $conta_nome, $tipo_conta, $situacao, $cod_plano_contas_pai);

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
            $sql_edicao = sprintf("UPDATE $tabela SET conta_indice = '%s', conta_nome = '%s', tipo_conta = '%s', situacao = '%s', cod_plano_contas_pai = '%s' WHERE $chave_primaria = $codigo", 
                $conta_indice, $conta_nome, $tipo_conta, $situacao, $cod_plano_contas_pai);
            if (mysql_query($sql_edicao))
            {
               mensagemOK('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        
        desconectabd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_simples.css" />

<script type="text/javascript" src="../lib/js/mif.tree.js"></script>

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

function validar_cod_plano_contas_pai(form)
{
    if (form.cod_plano_contas_pai.value == "")
    {
        alert("Houve uma falha no sistema em definir o Plano de Contas Pai. Por favor, tente novamente e avise a equipe de desenvolvimento.");
        return false;
    }

    return true;
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

function carregar_plano_contas_pai_exibicao()
{
    var url = 'acao=carregar_plano_contas_pai_exibicao';
  
    var conta_indice = $('conta_indice').getProperty('value');
    url += '&conta_indice=' + conta_indice;

    var cod_plano_contas = document.frmIncluir.<? echo $chave_primaria ?>.value;
    url += '&cod_plano_contas=' + cod_plano_contas;

    new Request.JSON({
        url: 'ipi_plano_contas_ajax.php',
        onComplete: function(retorno) {
            if(retorno.quant == 0)
            {
                $('cod_plano_contas_pai_exibicao').set('html', "<strong style='color: red;'>Não Encontrado</strong>");
            }
            else
            {
                $('cod_plano_contas_pai_exibicao').set('html', retorno.html);
                $('cod_plano_contas_pai').setProperty('value', retorno.cod_plano_contas_pai);

                if($('tipo_conta').getProperty('value') == "")
                {
                    $('tipo_conta').setProperty('value', retorno.tipo_conta_pai);
                }
            }
        }
    }).send(url);
}


window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
  
    if (document.frmIncluir.<? echo $chave_primaria?>.value > 0)
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
            document.frmIncluir.<? echo $chave_primaria ?>.value = '';
            document.frmIncluir.cod_plano_contas_pai.value = '';
            document.frmIncluir.conta_indice.value = '';
            document.frmIncluir.conta_nome.value = '';
            document.frmIncluir.tipo_conta.value = '';
            document.frmIncluir.situacao.value = 'ATIVO';
            document.frmIncluir.botao_submit.value = 'Cadastrar';
            carregar_plano_contas_pai_exibicao();
        }
    });

    input = $('conta_indice');
    input.addEvents({
        keyup: function() {
            if ((input.value[input.value.length - 1] == '.') || (input.value.length == 0))
            {
                carregar_plano_contas_pai_exibicao(); 
            }
        }
    });

    if(document.frmIncluir.<? echo $chave_primaria ?>.value > 0)
    {
        carregar_plano_contas_pai_exibicao();
    }
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
                    <td align="center">Conta</td>
                    <td align="center" width="100">Situação</td>
                </tr>
            </thead>
            <tbody>
          
            <?

            function imprimir_plano_contas($cod_plano_contas, $espaco)
            {
                $sql_buscar_plano_contas = "SELECT * FROM ipi_plano_contas WHERE cod_plano_contas_pai = '$cod_plano_contas' ORDER BY conta_indice";
                $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
                $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);

                if(($num_buscar_plano_contas > 0) && ($cod_plano_contas > 0))
                {
                    $espaco += 25;
                }

                while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
                {
                    echo '<tr>';
                    
                    if($obj_buscar_plano_contas->cod_plano_contas_pai == 0)
                    {
                        echo '<td align="center"><input type="checkbox" disabled="disabled" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_plano_contas->cod_plano_contas . '"></td>';
                    }
                    else
                    {
                        echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_plano_contas->cod_plano_contas . '"></td>';
                    }

                    echo '<td align="left" style="padding-left: ' . $espaco . 'px;"><a href="#" onclick="editar(' . $obj_buscar_plano_contas->cod_plano_contas . ')">' . bd2texto($obj_buscar_plano_contas->conta_indice) . ' ' . bd2texto($obj_buscar_plano_contas->conta_nome) . '</a></td>';
                    echo '<td align="center">' . bd2texto($obj_buscar_plano_contas->situacao) . '</a></td>';
   
                    echo '</tr>';

                    imprimir_plano_contas($obj_buscar_plano_contas->cod_plano_contas, $espaco);
                }
            }
            
            $conexao = conectabd();
            imprimir_plano_contas(0, 3);
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

<? endif; ?>

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
    
    <form name="frmIncluir" method="post"
        onsubmit="return validaRequeridos(this) && validar_cod_plano_contas_pai(this)">

    <table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbr tdbt"><label for="cod_plano_contas_pai_exibicao">Conta Pai</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <div id="cod_plano_contas_pai_exibicao" style="border: 1px solid #888; padding: 10px;"></div>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="conta_indice">Índice</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="conta_indice" <? if( $obj_editar->tipo_conta_raiz != '') echo 'readonly="readonly"' ?> id="conta_indice" maxlength="20" size="30" value="<? echo bd2texto($obj_editar->conta_indice) ?>">
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="conta_nome">Conta</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="conta_nome" id="conta_nome" maxlength="50" size="30" value="<? echo bd2texto($obj_editar->conta_nome) ?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="tipo_conta">Tipo de Movimentação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select class="requerido" name="tipo_conta" id="tipo_conta" style="width: 150px;">
                <option value=""></option>
                <option value="ENTRADA" <? if($obj_editar->tipo_conta == 'ENTRADA') echo 'selected'; ?>> (+) Entrada</option>
                <option value="SAIDA" <? if($obj_editar->tipo_conta == 'SAIDA') echo 'selected'; ?>> (-) Saída</option>
            </select>
        </td>
    </tr>

	<tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="situacao">Situação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select class="requerido" name="situacao" id="situacao" style="width: 150px;">
                <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'selected'; ?>>Ativo</option>
                <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'selected'; ?>>Inativo</option>
            </select>
        </td>
    </tr>

    <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>

</table>

<input type="hidden" id="cod_plano_contas_pai" name="cod_plano_contas_pai" value="<? echo $obj_editar->cod_plano_contas_pai ?>">
<input type="hidden" name="acao" value="editar">
<input type="hidden" name="<? echo $chave_primaria?>" value="<? echo $codigo?>">
</form>

</div>
<!-- Tab Incluir --></div>

<? rodape(); ?>
