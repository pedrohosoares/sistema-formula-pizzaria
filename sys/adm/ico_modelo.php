<?php

/**
 * Cadastro de Modelos.
 *
 * @version 1.0
 * @package iconteudo
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       14/07/2009   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

function normalizar_nome_internet ($nome)
{
    $p = strtr($nome, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ ", "aaaaeeiooouucAAAAEEIOOOUUC_");
    $url = ereg_replace('[^a-zA-Z0-9_.]', '', $p);
    $url = strtoupper($url);
    
    return $url;
}

exibir_cabecalho('Modelos de Páginas da Internet');

$acao = validar_var_post('acao');

$chave_primaria = 'cod_modelos';
$tabela = 'ico_modelos';
$campo_ordenacao = 'modelo';
$campo_filtro_padrao = 'modelo';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validar_var_post('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectar_bd();
        
        $sql_del = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        
        $sql_del1 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
        $sql_del2 = "DELETE FROM ico_campos_modelos WHERE $chave_primaria IN ($indicesSql)";
        
        $res_del2 = mysql_query($sql_del2);
        $res_del1 = mysql_query($sql_del1);
        
        if ($res_del2 && $res_del1)
        {
            exibir_mensagem_ok('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            exibir_mensagem_erro('Erro ao excluir os registros', 'Por favor, verifique se não há páginas utilizando dos modelos selecionados para exclusão.');
        }
        
        desconectar_bd($conexao);
        break;
    case 'editar':
        $codigo = validar_var_post($chave_primaria);
        $modelo = validar_var_post('modelo');
        $descricao = validar_var_post('descricao');
        $codigo_fonte = validar_var_post('codigo_fonte');
        $chamada = normalizar_nome_internet($modelo);
        $biblioteca = validar_var_post('biblioteca');
        $campos = validar_var_post('campos');
        $quantidade_campos = validar_var_post('quantidade_campos');
        
        $biblioteca = ($biblioteca == 'on') ? 1 : 0;
        
        $conexao = conectar_bd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (modelo, descricao, codigo, biblioteca, chamada) VALUES ('%s', '%s', '%s', %d, '%s')", $modelo, $descricao, $codigo_fonte, $biblioteca, $chamada);
            
            if (mysql_query($sql_edicao))
            {
                $codigo = mysql_insert_id();
                
                $res_editar_campos = true;
                
                if (is_array($quantidade_campos))
                {
                    for ($c = 0; $c < count($campos); $c++)
                    {
                        if ($quantidade_campos[$c] > 0)
                        {
                            $sql_editar_campos = sprintf("INSERT INTO ico_campos_modelos (cod_modelos, cod_tipos_campos, quantidade) VALUES (%d, %d, %d)", $codigo, $campos[$c], $quantidade_campos[$c]);
                            
                            $res_editar_campos &= mysql_query($sql_editar_campos);
                        }
                    }
                }
                
                if ($res_editar_campos)
                {
                    exibir_mensagem_ok('Registro adicionado com êxito!');
                }
                else
                {
                    exibir_mensagem_erro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                }
            }
            else
            {
                exibir_mensagem_erro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET modelo = '%s', descricao = '%s', codigo = '%s', biblioteca = %d, chamada = '%s' WHERE $chave_primaria = $codigo", $modelo, $descricao, $codigo_fonte, $biblioteca, $chamada);
            
            if (mysql_query($sql_edicao))
            {
                $sql_del_campos = "DELETE FROM ico_campos_modelos WHERE $chave_primaria = $codigo";
                
                if (mysql_query($sql_del_campos))
                {
                    $res_editar_campos = true;
                    
                    if (is_array($quantidade_campos))
                    {
                        for ($c = 0; $c < count($campos); $c++)
                        {
                            if ($quantidade_campos[$c] > 0)
                            {
                                $sql_editar_campos = sprintf("INSERT INTO ico_campos_modelos (cod_modelos, cod_tipos_campos, quantidade) VALUES (%d, %d, %d)", $codigo, $campos[$c], $quantidade_campos[$c]);
                                
                                $res_editar_campos &= mysql_query($sql_editar_campos);
                            }
                        }
                    }
                    
                    if ($res_editar_campos)
                    {
                        exibir_mensagem_ok('Registro alterado com êxito!');
                    }
                    else
                    {
                        exibir_mensagem_erro('Erro ao alterado o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                    }
                }
                else
                {
                    exibir_mensagem_erro('Erro ao alterado o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                }
            }
            else
            {
                exibir_mensagem_erro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
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
            document.frmIncluir.modelo.value = '';
            document.frmIncluir.codigo_fonte.value = '';
            document.frmIncluir.biblioteca.checked = false;
            
            // Limpando todos os campos input para Quantidade de campos
            var input = document.getElementsByTagName('input');
            for (var i = 0; i < input.length; i++)
            {
                if(input[i].name.match('quantidade_campos')) 
                { 
                    input[i].value = '';
                    input[i].disabled = false;
                }
            }
            
            document.frmIncluir.botao_submit.value = 'Cadastrar';
        }
    });
    
    $('biblioteca').addEvent('change', function()
    {
        var input = document.getElementsByTagName('input');
        for (var i = 0; i < input.length; i++)
        {
            if(input[i].name.match('quantidade_campos'))
            {
                input[i].disabled = $('biblioteca').getProperty('checked');
                
                if(input[i].disabled)
                {
                    input[i].value = '';
                }
            }
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
if ($exibir_barra_lateral)
:
    ?>

<table>
    <tr>

        <!-- Conteúdo -->
        <td class="conteudo">

        
<? endif;
?>

        <?
        $pagina = (validar_var_post('pagina', '/[0-9]+/')) ? validar_var_post('pagina', '/[0-9]+/') : 0;
        $opcoes = (validar_var_post('opcoes')) ? validar_var_post('opcoes') : $campo_filtro_padrao;
        $filtro = validar_var_post('filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right"><select
                    name="opcoes">
                    <option value="<?
                    echo $campo_filtro_padrao?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Modelo</option>
                </select></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text" name="filtro" size="60"
                    value="<?
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
        
        $conexao = conectar_bd();
        
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
                    <td align="center">Modelo</td>
                    <td align="center" width="150">Chamada</td>
                    <td align="center" width="40">Repetição</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->modelo) . '</a></td>';
                
                if ($obj_buscar_registros->biblioteca)
                {
                    echo '<td align="center"><#' . $obj_buscar_registros->chamada . '#></td>';
                    echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
                }
                else
                {
                    echo '<td align="center">&nbsp;</td>';
                    echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
                }
                
                echo '</tr>';
            }
            
            desconectar_bd($conexao);
            
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
    
    <form name="frmIncluir" method="post"
    onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="modelo">Modelo</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input class="requerido" type="text" name="modelo"
            id="modelo" maxlength="45" size="50"
            value="<?
            echo bd2texto($obj_editar->modelo)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label for="descricao">Descrição</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input type="text" name="descricao"
            id="descricao" maxlength="100" size="50"
            value="<?
            echo bd2texto($obj_editar->descricao)?>"></td>
    </tr>

    <tr>
        <td class="legenda sep tdbl tdbr">
        <?
        if ($codigo > 0)
        {
            echo '<input type="checkbox" name="biblioteca" id="biblioteca" ';
            
            if ($obj_editar->biblioteca)
            {
                echo 'checked="checked"';
            }
            
            echo '>';
        }
        else
        {
            echo '<input type="checkbox" name="biblioteca" id="biblioteca" checked="checked">';
        }
        ?>
    
        <label for="biblioteca">Repetição</label></td>
    </tr>

    <tr>
        <td class="tdbl tdbr sep">
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <td align="center"><label>Campo</label></td>
                    <td align="center" width="70"><label>Quantidade</label></td>
                    <td align="center" width="165"><label>Chamada</label></td>
                    <td align="center" width="30"><label>Arquivo</label></td>
                </tr>
            </thead>
            <tbody>
      
            <?
            $conexao = conectar_bd();
            
            $sql_buscar_tipos_campos = "SELECT * FROM ico_tipos_campos WHERE exibir = 1 ORDER BY ordem, campo";
            $res_buscar_tipos_campos = mysql_query($sql_buscar_tipos_campos);
            
            while ($obj_buscar_tipos_campos = mysql_fetch_object($res_buscar_tipos_campos))
            {
                echo '<tr>';
                
                if ($codigo > 0)
                {
                    $obj_editar_campos = executar_busca_simples(sprintf("SELECT * FROM ico_campos_modelos m INNER JOIN ico_tipos_campos c ON (m.cod_tipos_campos = c.cod_tipos_campos) WHERE m.cod_modelos = %d AND m.cod_tipos_campos = %d", $codigo, $obj_buscar_tipos_campos->cod_tipos_campos), $conexao);
                }
                else
                {
                    $obj_editar_campos = null;
                }
                
                echo '<input type="hidden" name="campos[]" value="' . $obj_buscar_tipos_campos->cod_tipos_campos . '">';
                
                echo '<td><label>' . $obj_buscar_tipos_campos->campo . '</label></td>';
                
                if ($obj_editar->biblioteca)
                {
                    echo '<td align="center"><input type="text" disabled="disabled" name="quantidade_campos[]" maxsize="5" size="3" value="' . $obj_editar_campos->quantidade . '" onKeyPress="return ApenasNumero(event)"></td>';
                }
                else
                {
                    echo '<td align="center"><input type="text" name="quantidade_campos[]" maxsize="5" size="3" value="' . $obj_editar_campos->quantidade . '" onKeyPress="return ApenasNumero(event)"></td>';
                }
                
                echo '<td align="center"><#' . $obj_buscar_tipos_campos->tipo . '[ <small style="color: #999;">parâmetro</small> ]#></td>';
                
                if ($obj_buscar_tipos_campos->aceita_arquivo)
                {
                    echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
                }
                else
                {
                    echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
                }
                
                echo '</tr>';
            }
            
            desconectar_bd($conexao);
            ?>
        
            </tbody>
        </table>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="codigo_fonte">Código Fonte</label></td>
    </tr>

    <tr>
        <td class="sep tdbl tdbr"><textarea class="requerido"
            name="codigo_fonte" id="codigo_fonte" rows="25" cols="75"><?
            echo bd2texto($obj_editar->codigo)?></textarea></td>
    </tr>

    <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> <input type="hidden"
    name="<?
    echo $chave_primaria?>" value="<?
    echo $codigo?>"></form>

</div>
<!-- Tab Incluir --></div>

<?
exibir_rodape();
?>