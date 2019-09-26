<?php

/**
 * Publicação de Páginas.
 *
 * @version 1.1
 * @package iconteudo
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       14/07/2009   FELIPE        Criado.
 * 1.1       29/04/2010   THIAGO        Corrigido Bug de publicação, em páginas com mais de uma imagem
 * 
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

exibir_cabecalho('Publicação de Páginas da Internet');

$acao = validar_var_post('acao');

$chave_primaria = 'cod_paginas';
$tabela = 'ico_paginas';
$campo_ordenacao = 'pagina';
$campo_filtro_padrao = 'pagina';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'publicar':
        $publicar = validar_var_post('publicar');
        $indices_sql = implode(',', $publicar);
        
        $conexao = conectar_bd();
        
        $sql_edicao = "UPDATE $tabela SET publicado = 1, data_hora_publicacao = NOW() WHERE $chave_primaria IN ($indices_sql)";
        $res_edicao = mysql_query($sql_edicao);
        
        if ($res_edicao)
        {
            $sql_buscar_imagens = "SELECT * FROM ico_campos_paginas cp INNER JOIN ico_tipos_campos tc ON (cp.cod_tipos_campos = tc.cod_tipos_campos) WHERE $chave_primaria IN ($indices_sql) AND rascunho = 0 AND tc.tipo = 'IMAGEM'";
            $res_buscar_imagens = mysql_query($sql_buscar_imagens);
            
            while($obj_buscar_imagens = mysql_fetch_object($res_buscar_imagens))
            {
                if(file_exists(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo))
                {
                    unlink(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo);
                }
            }
            
            $sql_del_campos = "DELETE FROM ico_campos_paginas WHERE $chave_primaria IN ($indices_sql) AND rascunho = 0";
            
            if (mysql_query($sql_del_campos))
            {
                $sql_edicao_campos = "INSERT INTO ico_campos_paginas (cod_tipos_campos, cod_paginas, conteudo, arquivo, rascunho, numero, auxiliar) SELECT cod_tipos_campos, cod_paginas, conteudo, arquivo, 0, numero, auxiliar FROM ico_campos_paginas WHERE $chave_primaria IN ($indices_sql)";
                
                if (mysql_query($sql_edicao_campos))
                {
                    $sql_buscar_imagens = "SELECT * FROM ico_campos_paginas cp INNER JOIN ico_tipos_campos tc ON (cp.cod_tipos_campos = tc.cod_tipos_campos) WHERE $chave_primaria IN ($indices_sql) AND rascunho = 0 AND tc.tipo = 'IMAGEM'";
                    $res_buscar_imagens = mysql_query($sql_buscar_imagens);
                    
                    $res_update_nome_imagem = true;
                    
                    while($obj_buscar_imagens = mysql_fetch_object($res_buscar_imagens))
                    {
                        if(file_exists(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo))
                        {
                            $arq_info = pathinfo(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo);
                            $arq_ext = $arq_info['extension'];
                            $arq_nome = $obj_buscar_imagens->$chave_primaria . '_' . $obj_buscar_imagens->numero . '_img.' . $arq_ext;

                            $sql_update_nome_imagem = "UPDATE ico_campos_paginas SET arquivo = '$arq_nome' WHERE $chave_primaria = '" . $obj_buscar_imagens->$chave_primaria . "' AND rascunho = 0 AND numero=".$obj_buscar_imagens->numero;
                            $res_update_nome_imagem &= mysql_query($sql_update_nome_imagem);
                            
                            copy(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo, UPLOAD_DIR . '/conteudos/' . $arq_nome);
                        }
                    }
                    
                    if($res_update_nome_imagem)
                    {
                        exibir_mensagem_ok('As páginas selecionadas foram publicadas com sucesso!');
                    }
                    else
                    {
                        exibir_mensagem_erro('Erro ao publicar as páginas', 'Por favor, comunique a equipe de suporte informando todos as páginas selecionadas para publicação.');
                    }
                }
                else
                {
                    exibir_mensagem_erro('Erro ao publicar as páginas', 'Por favor, comunique a equipe de suporte informando todos as páginas selecionadas para publicação.');
                }
            }
            else
            {
                exibir_mensagem_erro('Erro ao publicar as páginas', 'Por favor, comunique a equipe de suporte informando todos as páginas selecionadas para publicação.');
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
        if (confirm('Deseja publicar as páginas selecionados?'))
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
        alert('Por favor, selecione as páginas que deseja publicar.');
     
        return false;
    }
}

</script>

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
                    <option
                        value="<?
                        echo $campo_filtro_padrao?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Página</option>
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

        <form name="frmPublicar" method="post"
            onsubmit="return verificar_checkbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
                <td><input class="botaoAzul" type="submit"
                    value="Publicar Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20"><input type="checkbox"
                        onclick="marcaTodos('marcar');"></td>
                    <td align="center">Página</td>
                    <td align="center" width="100">Endereço</td>
                    <td align="center" width="115">Criação</td>
                    <td align="center" width="115">Alteração</td>
                    <td align="center" width="115">Publicação</td>
                    <td align="center" width="40">Publicado</td>
                    <td align="center" width="40">Habilitado</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="publicar[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->pagina) . '</td>';
                echo '<td align="center">' . $obj_buscar_registros->chamada . '</td>';
                echo '<td align="center">' . bd2datahora($obj_buscar_registros->data_hora_criacao) . '</td>';
                echo '<td align="center">' . bd2datahora($obj_buscar_registros->data_hora_alteracao) . '</td>';
                
                if ($obj_buscar_registros->data_hora_publicacao != '')
                {
                    echo '<td align="center">' . bd2datahora($obj_buscar_registros->data_hora_publicacao) . '</td>';
                }
                else
                {
                    echo '<td align="center">&nbsp;</td>';
                }
                
                if ($obj_buscar_registros->publicado)
                {
                    echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
                }
                else
                {
                    echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
                }
                
                if ($obj_buscar_registros->habilitado)
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

        <input type="hidden" name="acao" value="publicar"></form>

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

<?
exibir_rodape();
?>