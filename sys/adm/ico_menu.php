<?php

/**
 * Cadastro de Menus.
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

exibir_cabecalho('Menus');

$acao = validar_var_post('acao');

$chave_primaria = 'cod_menus';
$tabela = 'ico_menus';
$campo_ordenacao = 'campo1';
$quant_pagina = 50;
$exibir_barra_lateral = false;

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
        $menu = validar_var_post('menu');
        $cod_paginas = validar_var_post('cod_paginas');
        $tipo = validar_var_post('tipo');
        $cod_menus_pai = validar_var_post('cod_menus_pai');
        $ordem = validar_var_post('ordem');
        $habilitado = validar_var_post('habilitado');
        
        $cod_paginas = ($cod_paginas > 0) ? $cod_paginas : 'NULL';
        $habilitado = ($habilitado == 'on') ? 1 : 0;
        
        if ($tipo != 'PAGINA')
        {
            $arquivo = '';
        }
        
        if ($tipo == 'MENU')
        {
            $cod_menus_pai = 0;
        }
        
        $conexao = conectar_bd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (menu, cod_paginas, tipo, cod_menus_pai, ordem, habilitado) VALUES ('%s', %s, '%s', %d, %d, %d)", $menu, $cod_paginas, $tipo, $cod_menus_pai, $ordem, $habilitado);
            
            if (mysql_query($sql_edicao))
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
            $sql_edicao = sprintf("UPDATE $tabela SET menu = '%s', cod_paginas = %s, tipo = '%s', cod_menus_pai = %d, ordem = %d, habilitado = %d WHERE $chave_primaria = $codigo", $menu, $cod_paginas, $tipo, $cod_menus_pai, $ordem, $habilitado);
            
            if (mysql_query($sql_edicao))
            {
                exibir_mensagem_ok('Registro adicionado com êxito!');
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

function alterar_requerido() 
{
  if ($('tipo').getProperty('value') == 'PAGINA') 
  {
      $('cod_paginas_legenda').addClass('requerido');
      $('cod_paginas').addClass('requerido');
  }
  else 
  {
      $('cod_paginas_legenda').removeClass('requerido');
      $('cod_paginas').removeClass('requerido');
  }
}

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
            document.frmIncluir.menu.value = '';
            document.frmIncluir.cod_paginas.value = '';
            document.frmIncluir.tipo.value = '';
            document.frmIncluir.cod_menus_pai.value = '';
            document.frmIncluir.ordem.value = '0';
            document.frmIncluir.habilitado.checked = 'checked';
            
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
if ($exibir_barra_lateral)
:
    ?>

<table>
    <tr>

        <!-- Conteúdo -->
        <td class="conteudo">



<? endif;
?>

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
                    <td align="center">Menu</td>
                    <td align="center" width="60">Habilitado</td>
                    <td align="center" width="40">Ordem</td>
                </tr>
            </thead>
            <tbody>
          
                <?
                function imprimir_tabela_menu ($cod, $espaco)
                {
                    $sql_buscar_paginas = "SELECT * FROM ico_menus WHERE cod_menus_pai = $cod ORDER BY ordem, menu";
                    $res_buscar_paginas = mysql_query($sql_buscar_paginas);
                    $num_buscar_paginas = mysql_num_rows($res_buscar_paginas);
                    
                    if (($num_buscar_paginas > 0) && ($cod > 0))
                    {
                        $espaco += 25;
                    }
                    
                    while ($obj_buscar_paginas = mysql_fetch_object($res_buscar_paginas))
                    {
                        echo '<tr>';
                        
                        echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_paginas->cod_menus . '"></td>';
                        echo '<td align="left" style="padding-left: ' . $espaco . 'px;"><a href="#" onclick="editar(' . $obj_buscar_paginas->cod_menus . ')">' . bd2texto($obj_buscar_paginas->menu) . '</a></td>';
                        
                        if ($obj_buscar_paginas->habilitado)
                        {
                            echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
                        }
                        else
                        {
                            echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
                        }
                        
                        echo '<td align="center">' . $obj_buscar_paginas->ordem . '</td>';
                        echo '</tr>';
                        
                        imprimir_tabela_menu($obj_buscar_paginas->cod_menus, $espaco);
                    }
                }
                
                $conexao = conectar_bd();
                imprimir_tabela_menu(0, 3);
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
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="menu">Nome
        do Menu, Submenu ou Página</label></td>
    </tr>

    <tr>
        <td class="tdbl tdbr"><input class="requerido" type="text" name="menu"
            id="menu" maxlength="45" size="38"
            value="<?
            echo $obj_editar->menu?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="tipo">Tipo</label></td>
    </tr>

    <tr>
        <td class="sep tdbl tdbr"><select class="requerido" name="tipo"
            id="tipo" onchange="alterar_requerido()">
            <option value=""></option>
            <option value="MENU"
                <?
                if ($obj_editar->tipo == 'MENU')
                    echo 'SELECTED'?>>Menu</option>
            <option value="SUBMENU"
                <?
                if ($obj_editar->tipo == 'SUBMENU')
                    echo 'SELECTED'?>>Submenu</option>
            <option value="PAGINA"
                <?
                if ($obj_editar->tipo == 'PAGINA')
                    echo 'SELECTED'?>>Página</option>
        </select></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label id="cod_paginas_legenda"
            class="requerido" for="cod_paginas">Página</label></td>
    </tr>

    <tr>
        <td class="sep tdbl tdbr"><select class="requerido" name="cod_paginas"
            id="cod_paginas">
            <option value=""></option>
              
            <?
            $conexao = conectar_bd();
            
            $sql_buscar_paginas = "SELECT * FROM ico_paginas p INNER JOIN ico_modelos m ON (p.cod_modelos = m.cod_modelos) WHERE m.biblioteca = 0 AND erro_404 = 0 ORDER BY pagina";
            $res_buscar_paginas = mysql_query($sql_buscar_paginas);
            
            while ($obj_editar_paginas = mysql_fetch_object($res_buscar_paginas))
            {
                echo '<option value="' . $obj_editar_paginas->cod_paginas . '" ';
                
                if ($obj_editar_paginas->cod_paginas == $obj_editar->cod_paginas)
                {
                    echo 'selected';
                }
                
                echo '>' . $obj_editar_paginas->pagina . '</option>';
            }
            
            desconectar_bd($conexao);
            ?>
              
            </select></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="cod_menus_pai">Menu ou Submenu Pai</label></td>
    </tr>
    <tr>

        <td class="sep tdbl tdbr"><select class="requerido" name="cod_menus_pai"
            id="cod_menus_pai">
            <option value=""></option>
            <option value="0"
                <?
                if (($obj_editar->cod_menus_pai == 0) && ($obj_editar->cod_menus_pai != ''))
                {
                    echo 'SELECTED';
                }
                ?>>RAIZ</option>
              
                <?
                $conexao = conectar_bd();
                
                $sql_buscar_menus = "SELECT * FROM $tabela WHERE tipo in ('MENU', 'SUBMENU') ORDER BY ordem, menu";
                $res_buscar_menus = mysql_query($sql_buscar_menus);
                
                while ($obj_buscar_menus = mysql_fetch_object($res_buscar_menus))
                {
                    echo '<option value="' . $obj_buscar_menus->$chave_primaria . '" ';
                    
                    if ($obj_editar->cod_menus_pai == $obj_buscar_menus->$chave_primaria)
                        echo 'SELECTED';
                    
                    echo '>' . bd2texto($obj_buscar_menus->menu) . '</option>';
                }
                
                desconectar_bd($conexao);
                ?>
              
            </select></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="ordem">Ordem</label></td>
    </tr>

    <tr>
        <td class="sep tdbl tdbr"><select class="requerido" name="ordem"
            id="ordem">
            <option></option>
              
                <?
                for ($i = -10; $i <= 10; $i++)
                {
                    echo '<option value="' . $i . '"';
                    
                    if ($obj_editar->ordem == $i)
                    {
                        echo 'SELECTED';
                    }
                    
                    echo '>' . $i . '</option>';
                }
                ?>
              
            </select></td>
    </tr>

    <tr>
        <td class="legenda sep tdbl tdbr">
          
            <?
            if ($cod_paginas > 0)
            {
                echo '<input type="checkbox" name="habilitado" id="habilitado" ';
                
                if ($obj_editar->habilitado)
                {
                    echo 'checked="checked"';
                }
                
                echo '>';
            }
            else
            {
                echo '<input type="checkbox" name="habilitado" id="habilitado" checked="checked">';
            }
            ?>
          
            <label for="habilitado">Habilitado</label></td>
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