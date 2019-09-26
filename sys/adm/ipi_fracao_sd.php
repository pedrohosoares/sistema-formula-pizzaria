
<?php

/**
 * Tela para cadastrar as possíveis frações das pizzas.
 *
 * @version 2.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       27/08/2012   PEDRO H       Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Fraçoes');

$acao = validaVarPost("acao");
$tabela = 'ipi_fracoes';
$chave_primaria = 'cod_fracoes';
$campo_ordenacao = 'cascata';
$campo_filtro_padrao = 'cascata';
$quant_pagina = 50;

switch ($acao)
{
    case 'excluir':
        $excluir = validar_var_post('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectar_bd();

        $sql_del = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        
        if (mysql_query($sql_del))
        {
            exibir_mensagem_ok('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            exibir_mensagem_erro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectar_bd($conexao);
        break;
    case 'editar':
        $codigo = validar_var_post($chave_primaria);
        $fracoes = validar_var_post('fracoes');

        $conexao = conectar_bd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (fracoes) VALUES ('%s')", $fracoes);
            $res_edicao = mysql_query($sql_edicao);

            if ($res_edicao)
            {
                $codigo = mysql_insert_id();
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET fracoes = '%s' WHERE $chave_primaria = $codigo", $fracoes);
            $res_edicao = mysql_query($sql_edicao);
        }

        if($res_edicao)
        {
          exibir_mensagem_ok('Registro alterado com êxito!');
        }
        else
        {
          exibir_mensagem_erro('Erro ao cadastrar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
        desconectar_bd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_simples.css" />

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
            document.frmIncluir.fracoes.value = '';
     
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
        
        $conexao = conectar_bd();
        
        $sql_buscar_registros = "SELECT * FROM $tabela";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY ' . $campo_ordenacao . ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        echo $sql_buscar_registros;

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
                    <td align="center">Fração</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            if($num_buscar_registros):
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->fracao) . '</a></td>';
                
                echo '</tr>';
            }
            
            desconectar_bd($conexao);
            else:
                echo '<tr>';
                
                echo '<td align="center" colspan="2">Nenhum registro encontrado</td>';
                
                echo '</tr>';

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


<? endif;
?>

</div>

<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">

    <?
    $codigo = validar_var_post($chave_primaria, '/[0-9]+/');
    
    if ($codigo > 0)
    {
        $obj_editar = executar_busca_simples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">

    <table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr><td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cascata">Cascata</label></td></tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text"
            name="cascata" id="cascata" maxlength="45" size="45"
            value="<? echo bd2texto($obj_editar->cascata) ?>"></td>
    </tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="situacao">Situação</label></td></tr>
    <tr><td class="tdbl tdbr sep">
        <select class="requerido" name="situacao" id="situacao" style="width: 100px;">
            <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'SELECTED' ?>>Ativo</option>
            <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'SELECTED' ?>>Inativo</option>
        </select>
    </td></tr>

    <tr><td class="legenda tdbl tdbr"><label class="requerido" for="orientacao_cascata">Orientação</label></td></tr>
    <tr><td class="tdbl tdbr sep">
        <select class="requerido" name="orientacao_cascata" id="orientacao_cascata" style="width: 100px;">
            <option value="esquerda" <? if($obj_editar->orientacao == 'esquerda') echo 'SELECTED' ?>>Esquerda</option>
            <option value="direita" <? if($obj_editar->orientacao == 'direita') echo 'SELECTED' ?>>Direita</option>
        </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="imagem_barra">Imagem da Barra (*.png)</label></td></tr>

    <?
    if(is_file('../../monte-seu-lago/images/' . $obj_editar->imagem_barra))
    {
        echo '<tr><td class="sep tdbl tdbr" align="center" style="padding: 15px;">';
        echo '<img src="' . '../../monte-seu-lago/images/' . $obj_editar->imagem_barra . '">';
        echo '</td></tr>';
    }
    ?>

    <tr><td class="sep tdbl tdbr sep"><input type="file" name="imagem_barra" id="foto" size="30"></td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="imagem_fundo">Imagem de Fundo (*.png)</label></td></tr>

    <?
    if(is_file('../../monte-seu-lago/images/pecas/ornamentos/' . $obj_editar->imagem_fundo))
    {
        echo '<tr><td class="sep tdbl tdbr" align="center" style="padding: 15px;">';
        echo '<img src="' . '../../monte-seu-lago/images/pecas/ornamentos/' . $obj_editar->imagem_fundo . '">';
        echo '</td></tr>';
    }
    ?>

    <tr><td class="sep tdbl tdbr sep"><input type="file" name="imagem_fundo" id="foto" size="30"></td></tr>

    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label>Dimensão CSS do objeto na barra de seleção (WIDTH e HEIGHT)</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" colspan="2">

        <table>
            <tr>
                <td><input type="text" name="css_lago_objeto_width" id="css_lago_objeto_width" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_objeto_width)?>"></td>

                <td><input type="text" name="css_lago_objeto_height" id="css_lago_objeto_height" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_objeto_height)?>"></td>
            </tr>
        </table>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label>Dimensão CSS do objeto depois de dropado (WIDTH e HEIGHT)</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" colspan="2">

        <table>
            <tr>
                <td><input type="text" name="css_lago_drop_width" id="css_lago_drop_width" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_drop_width)?>"></td>

                <td><input type="text" name="css_lago_drop_height" id="css_lago_drop_height" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_drop_height)?>"></td>
            </tr>
        </table>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label>Posicionamento CSS do objeto na barra de seleção (TOP e LEFT)</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" colspan="2">

        <table>
            <tr>
                <td><input type="text" name="css_lago_objeto_top" id="css_lago_objeto_top" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_objeto_top)?>"></td>

                <td><input type="text" name="css_lago_objeto_left" id="css_lago_objeto_left" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_objeto_left)?>"></td>
            </tr>
        </table>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label>Margem CSS do objeto na barra de seleção (TOP, RIGHT, BOTTOM, LEFT)</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" colspan="2">

        <table>
            <tr>
                <td><input type="text" name="css_lago_barra_margin_top" id="css_lago_barra_margin_top" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_barra_margin_top)?>"></td>

                <td><input type="text" name="css_lago_barra_margin_right" id="css_lago_barra_margin_right" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_barra_margin_right)?>"></td>

                <td><input type="text" name="css_lago_barra_margin_bottom" id="css_lago_barra_margin_bottom" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_barra_margin_bottom)?>"></td>

                <td><input type="text" name="css_lago_barra_margin_left" id="css_lago_barra_margin_left" maxlength="255" size="5" value="<? echo bd2texto($obj_editar->css_lago_barra_margin_left)?>"></td>
            </tr>
        </table>
        </td>
    </tr>

    <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>

    </table>

    <input type="hidden" name="acao" value="editar"> <input type="hidden"
    name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
</form>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>