<?php

/**
 * Definições de páginas do módulo de conteúdos.
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

exibir_cabecalho('Definições de Padrões');

$acao = validar_var_post('acao');

$chave_primaria = 'cod_paginas';
$tabela = 'ico_paginas';
$campo_ordenacao = 'campo1';
$campo_filtro_padrao = 'campo2';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'editar':
        $cod_paginas_home = validar_var_post('cod_paginas_home', '/[0-9]+/');
        $cod_paginas_404 = validar_var_post('cod_paginas_404', '/[0-9]+/');
        
        $conexao = conectar_bd();
        
        $sql_edicao_update = "UPDATE $tabela SET home = 0, erro_404 = 0";
        
        if (mysql_query($sql_edicao_update))
        {
            $sql_edicao_update_home = "UPDATE $tabela SET home = 1 WHERE $chave_primaria = $cod_paginas_home";
            $sql_edicao_update_404 = "UPDATE $tabela SET erro_404 = 1 WHERE $chave_primaria = $cod_paginas_404";
            
            $res_edicao_update_home = mysql_query($sql_edicao_update_home);
            $res_edicao_update_404 = mysql_query($sql_edicao_update_404);
            
            if ($res_edicao_update_home && $res_edicao_update_404)
            {
                exibir_mensagem_ok('Registro alterado com êxito!');
            }
            else
            {
                exibir_mensagem_erro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        
        desconectar_bd($conexao);
        
        break;
}

$conexao = conectar_bd();

$obj_buscar_paginas_home = executar_busca_simples("SELECT * FROM $tabela WHERE home = 1 LIMIT 1", $conexao);
$obj_buscar_paginas_404 = executar_busca_simples("SELECT * FROM $tabela WHERE erro_404 = 1 LIMIT 1", $conexao);

desconectar_bd($conexao);

?>

<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido"
            for="cod_paginas_home">Home</label></td>
    </tr>

    <tr>
        <td class="tdbl tdbr"><select class="requerido" name="cod_paginas_home"
            id="cod_paginas_home">
            <option value=""></option>
    
            <?
            $conexao = conectar_bd();
            
            $sql_buscar_paginas = "SELECT * FROM $tabela WHERE publicado = 1 ORDER BY pagina";
            $res_buscar_paginas = mysql_query($sql_buscar_paginas);
            
            while ($obj_buscar_paginas = mysql_fetch_object($res_buscar_paginas))
            {
                echo '<option value="' . $obj_buscar_paginas->$chave_primaria . '" ';
                
                if ($obj_buscar_paginas->$chave_primaria == $obj_buscar_paginas_home->$chave_primaria)
                {
                    echo 'selected';
                }
                
                echo '>' . bd2texto($obj_buscar_paginas->pagina) . '</option>';
            }
            
            desconectar_bd($conexao);
            ?>
    
        </select></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="cod_paginas_404">Erro 404</label></td>
    </tr>

    <tr>
        <td class="sep tdbl tdbr"><select class="requerido"
            name="cod_paginas_404" id="cod_paginas_404">
            <option value=""></option>
    
            <?
            $conexao = conectar_bd();
            
            $sql_buscar_paginas = "SELECT * FROM $tabela WHERE publicado = 1 ORDER BY pagina";
            $res_buscar_paginas = mysql_query($sql_buscar_paginas);
            
            while ($obj_buscar_paginas = mysql_fetch_object($res_buscar_paginas))
            {
                echo '<option value="' . $obj_buscar_paginas->$chave_primaria . '" ';
                
                if ($obj_buscar_paginas->$chave_primaria == $obj_buscar_paginas_404->$chave_primaria)
                {
                    echo 'selected';
                }
                
                echo '>' . bd2texto($obj_buscar_paginas->pagina) . '</option>';
            }
            
            desconectar_bd($conexao);
            ?>
    
        </select></td>
    </tr>

    <tr>
        <td colspan="2" align="center" class="tdbl tdbb tdbr"><input
            name="botao_submit" class="botao" type="submit" value="Alterar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"></form>


<?
exibir_rodape();
?>