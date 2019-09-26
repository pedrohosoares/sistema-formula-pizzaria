<?php

/**
 * Cadastro de Páginas.
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
    $url = strtolower($url);
    return $url;
}

// Isso resolve o problema de cache de imagens...
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");

exibir_cabecalho('Páginas da Internet');

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
        $codigo = validar_var_post($chave_primaria);
        
        $conexao = conectar_bd();
        
        $sql_edicao = "UPDATE $tabela SET publicado = 1, data_hora_publicacao = NOW() WHERE $chave_primaria = $codigo";
        $res_edicao = mysql_query($sql_edicao);
        
        if ($res_edicao)
        {
            $sql_del_campos = "DELETE FROM ico_campos_paginas WHERE $chave_primaria = $codigo AND rascunho = 0";
            
            if (mysql_query($sql_del_campos))
            {
                $sql_edicao_campos = "INSERT INTO ico_campos_paginas (cod_tipos_campos, cod_paginas, conteudo, arquivo, rascunho, numero, auxiliar) SELECT cod_tipos_campos, cod_paginas, conteudo, arquivo, 0, numero, auxiliar FROM ico_campos_paginas WHERE cod_paginas = $codigo";
                
                if (mysql_query($sql_edicao_campos))
                {
                    $sql_buscar_imagens = "SELECT * FROM ico_campos_paginas cp INNER JOIN ico_tipos_campos tc ON (cp.cod_tipos_campos = tc.cod_tipos_campos) WHERE cod_paginas = $codigo AND rascunho = 0 AND tc.tipo = 'IMAGEM'";
                    $res_buscar_imagens = mysql_query($sql_buscar_imagens);
                    
                    $res_update_nome_imagem = true;
                    
                    while ($obj_buscar_imagens = mysql_fetch_object($res_buscar_imagens))
                    {
                        if (file_exists(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo))
                        {
                            $arq_info = pathinfo(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo);
                            $arq_ext = $arq_info['extension'];
                            $arq_nome = $obj_buscar_imagens->$chave_primaria . '_' . $obj_buscar_imagens->numero . '_img.' . $arq_ext;
                            
                            $sql_update_nome_imagem = "UPDATE ico_campos_paginas SET arquivo = '$arq_nome' WHERE cod_paginas = $codigo AND rascunho = 0 AND numero=".$obj_buscar_imagens->numero;
                            $res_update_nome_imagem &= mysql_query($sql_update_nome_imagem);
                            //echo "<br>".$sql_update_nome_imagem;
                            
                            copy(UPLOAD_DIR . '/conteudos/' . $obj_buscar_imagens->arquivo, UPLOAD_DIR . '/conteudos/' . $arq_nome);
                        }
                    }
                    
                    if ($res_update_nome_imagem)
                    {
                        mensagemOk('As páginas selecionadas foram publicadas com sucesso!');
                    }
                    else
                    {
                        mensagemErro('Erro ao publicar as páginas', 'Por favor, comunique a equipe de suporte informando todos as páginas selecionadas para publicação.');
                    }
                }
                else
                {
                    mensagemErro('Erro ao publicar as páginas', 'Por favor, comunique a equipe de suporte informando todos as páginas selecionadas para publicação.');
                }
            }
            else
            {
                mensagemErro('Erro ao publicar as páginas', 'Por favor, comunique a equipe de suporte informando todos as páginas selecionadas para publicação.');
            }
        }
        
        desconectar_bd($conexao);
        break;
    case 'excluir':
        $excluir = validar_var_post('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectar_bd();
        
        foreach ($excluir as $cor_excluir)
        {
            foreach (glob(UPLOAD_DIR . '/conteudos/' . $cor_excluir . '_*.*') as $arquivo)
            {
                unlink($arquivo);
            }
        }
        
        $sql_del1 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        $sql_del2 = "DELETE FROM ico_campos_paginas WHERE $chave_primaria IN ($indices_sql)";
        
        $res_del2 = mysql_query($sql_del2);
        $res_del1 = mysql_query($sql_del1);
        
        if ($res_del1 && $res_del2)
        {
            mensagemOk('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se não há menus associados com esta página.');
        }
        
        desconectar_bd($conexao);
        break;
    case 'editar':
        $codigo = validar_var_post($chave_primaria);
        $pagina = texto2bd(validar_var_post('nome_pagina'));
        $titulo_pagina = texto2bd(validar_var_post('titulo_pagina'));
        $chamada = (trim(validar_var_post('chamada')) != '') ? normalizar_nome_internet(validar_var_post('chamada')) : normalizar_nome_internet(validar_var_post('titulo_pagina'));
        
        $habilitado = (validar_var_post('habilitado') == 'on') ? 1 : 0;
        $cod_modelos = validar_var_post('cod_modelos');
        
        $nome_menu = validar_var_post('nome_menu');
        $cod_menus_pai = validar_var_post('cod_menus_pai');
        $cod_menus = validar_var_post('cod_menus');
        
        $titulo = validar_var_post('titulo');
        $texto = validar_var_post('texto');
        $titulo_link = validar_var_post('titulo_link');
        $link = validar_var_post('link');
        $imagem = validar_var_file('imagem');
        
        $conexao = conectar_bd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (pagina, chamada, titulo, habilitado, data_hora_criacao, data_hora_alteracao, cod_modelos) VALUES ('%s', '%s', '%s', %d, NOW(), NOW(), %d)", $pagina, $chamada, $titulo_pagina, $habilitado, $cod_modelos);
            
            if (mysql_query($sql_edicao))
            {
                $codigo = mysql_insert_id();
                
                // Inserindo os Títulos
                $res_edicao_titulo = true;
                
                if ((is_array($titulo)) && (count($titulo) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'TITULO'", $conexao);
                    
                    for ($t = 0; $t < count($titulo); $t++)
                    {
                        if (trim($titulo[$t]) != '')
                        {
                            $sql_edicao_titulo = sprintf("INSERT INTO ico_campos_paginas (conteudo, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', %d, %d, %d, %d)", texto2bd($titulo[$t]), true, $t + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                            $res_edicao_titulo &= mysql_query($sql_edicao_titulo);
                        }
                    }
                }
                
                // Inserindo os Textos
                $res_edicao_texto = true;
                
                if ((is_array($texto)) && (count($texto) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'TEXTO'", $conexao);
                    
                    for ($t = 0; $t < count($texto); $t++)
                    {
                        $sql_edicao_texto = sprintf("INSERT INTO ico_campos_paginas (conteudo, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', %d, %d, %d, %d)", texto2bd($texto[$t]), true, $t + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                        $res_edicao_texto &= mysql_query($sql_edicao_texto);
                    }
                }
                
                // Inserindo os Links
                $res_edicao_link = true;
                
                if ((is_array($link)) && (count($link) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'LINK'", $conexao);
                    
                    for ($l = 0; $l < count($link); $l++)
                    {
                        $titulo_link_tratado = (trim($titulo_link[$l]) != '') ? trim($titulo_link[$l]) : $link[$l];
                            
                        $sql_edicao_link = sprintf("INSERT INTO ico_campos_paginas (conteudo, auxiliar, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', '%s', %d, %d, %d, %d)", texto2bd($link[$l]), texto2bd($titulo_link_tratado), true, $l + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                        $res_edicao_link &= mysql_query($sql_edicao_link);
                    }
                }
                
                // Inserindo as Imagens
                $res_edicao_imagem = true;
                
                if ((is_array($imagem['name'])) && (count($imagem['name']) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'IMAGEM'", $conexao);
                    
                    for ($i = 0; $i < count($imagem['name']); $i++)
                    {
                        if (trim($imagem['name'][$i]) != '')
                        {
                            $arq_info = pathinfo($imagem['name'][$i]);
                            $arq_ext = $arq_info['extension'];
                            
                            //echo 'teste->'.$imagem["type"][$i];
                            
                            if (!eregi("^image|application\\/(pjpeg|jpeg|jpg|swf|png|gif|x-shockwave-flash)$", $imagem["type"][$i]))
                            {
                                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png, *.gif, *.swf).');
                            }
                            else
                            {
                                $i_arquivo = $i + 1;
                                
                                $res_edicao_imagem &= move_uploaded_file($imagem['tmp_name'][$i], UPLOAD_DIR . "/conteudos/${codigo}_${i_arquivo}_img_r.${arq_ext}");
                                
                                $sql_edicao_imagem = sprintf("INSERT INTO ico_campos_paginas (arquivo, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', %d, %d, %d, %d)", texto2bd("${codigo}_${i_arquivo}_img_r.${arq_ext}"), true, $i + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                                $res_edicao_imagem &= mysql_query($sql_edicao_imagem);
                            }
                        }
                    }
                }
                
                if ($res_edicao_titulo && $res_edicao_texto && $res_edicao_link && $res_edicao_imagem)
                {
                    mensagemOk('Registro adicionado com êxito!');
                }
                else
                {
                    mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                }
            }
            else
            {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET pagina = '%s', chamada = '%s', titulo = '%s', habilitado = %d, cod_modelos = %d, data_hora_alteracao = NOW() WHERE $chave_primaria = $codigo", $pagina, $chamada, $titulo_pagina, $habilitado, $cod_modelos);
            
            if (mysql_query($sql_edicao))
            {
                // Alterando os Títulos
                $res_del_titulo = true;
                $res_edicao_titulo = true;
                
                if ((is_array($titulo)) && (count($titulo) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'TITULO'", $conexao);
                    
                    for ($t = 0; $t < count($titulo); $t++)
                    {
                        $sql_del_titulo = sprintf("DELETE FROM ico_campos_paginas WHERE $chave_primaria = $codigo AND rascunho = 1 AND cod_tipos_campos = " . $obj_tipos_campos->cod_tipos_campos . " AND numero = %d", $t + 1);
                        $res_del_titulo = mysql_query($sql_del_titulo);
                            
                        $sql_edicao_titulo = sprintf("INSERT INTO ico_campos_paginas (conteudo, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', %d, %d, %d, %d)", texto2bd($titulo[$t]), true, $t + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                        $res_edicao_titulo &= mysql_query($sql_edicao_titulo);
                    }
                }
                
                // Alterando os Textos
                $res_del_texto = true;
                $res_edicao_texto = true;
                
                if ((is_array($texto)) && (count($texto) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'TEXTO'", $conexao);
                    
                    for ($t = 0; $t < count($texto); $t++)
                    {
                        $sql_del_texto = sprintf("DELETE FROM ico_campos_paginas WHERE $chave_primaria = $codigo AND rascunho = 1 AND cod_tipos_campos = " . $obj_tipos_campos->cod_tipos_campos . " AND numero = %d", $t + 1);
                        $res_del_texto = mysql_query($sql_del_texto);
                            
                        $sql_edicao_texto = sprintf("INSERT INTO ico_campos_paginas (conteudo, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', %d, %d, %d, %d)", texto2bd($texto[$t]), true, $t + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                        $res_edicao_texto &= mysql_query($sql_edicao_texto);
                    }
                }
                
                // Alterando os Links
                $res_del_link = true;
                $res_edicao_link = true;
                
                if ((is_array($link)) && (count($link) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'LINK'", $conexao);
                    
                    for ($l = 0; $l < count($link); $l++)
                    {
                        if (trim($link[$l]) != '')
                        {
                            $sql_del_link = sprintf("DELETE FROM ico_campos_paginas WHERE $chave_primaria = $codigo AND rascunho = 1 AND cod_tipos_campos = " . $obj_tipos_campos->cod_tipos_campos . " AND numero = %d", $l + 1);
                            $res_del_link = mysql_query($sql_del_link);
                            
                            $titulo_link_tratado = (trim($titulo_link[$l]) != '') ? trim($titulo_link[$l]) : $link[$l];
                            
                            $sql_edicao_link = sprintf("INSERT INTO ico_campos_paginas (conteudo, auxiliar, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', '%s', %d, %d, %d, %d)", texto2bd($link[$l]), texto2bd($titulo_link_tratado), true, $l + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                            $res_edicao_link &= mysql_query($sql_edicao_link);
                        }
                    }
                }
                
                // Alterando as Imagens
                $res_del_imagem = true;
                $res_edicao_imagem = true;
                
                if ((is_array($imagem['name'])) && (count($imagem['name']) > 0))
                {
                    $obj_tipos_campos = executar_busca_simples("SELECT * FROM ico_tipos_campos WHERE tipo = 'IMAGEM'", $conexao);
                    
                    for ($i = 0; $i < count($imagem['name']); $i++)
                    {
                        if (trim($imagem['name'][$i]) != '')
                        {
                            $obj_buscar_arquivo_antigo = executar_busca_simples(sprintf("SELECT * FROM ico_campos_paginas WHERE $chave_primaria = $codigo AND rascunho = 1 AND cod_tipos_campos = " . $obj_tipos_campos->cod_tipos_campos . " AND numero = %d LIMIT 1", $i + 1), $conexao);
                            
                            if ((file_exists(UPLOAD_DIR . "/conteudos/" . $obj_buscar_arquivo_antigo->arquivo)) && ($obj_buscar_arquivo_antigo->arquivo != ''))
                            {
                                $res_del_imagem &= unlink(UPLOAD_DIR . "/conteudos/" . $obj_buscar_arquivo_antigo->arquivo);
                            }
                            
                            $sql_del_imagem = sprintf("DELETE FROM ico_campos_paginas WHERE $chave_primaria = $codigo AND rascunho = 1 AND cod_tipos_campos = " . $obj_tipos_campos->cod_tipos_campos . " AND numero = %d", $i + 1);
                            $res_del_imagem = mysql_query($sql_del_imagem);
                            
                            $arq_info = pathinfo($imagem['name'][$i]);
                            $arq_ext = $arq_info['extension'];
                            
                            if (!eregi("^image|application\\/(pjpeg|jpeg|jpg|swf|png|gif|x-shockwave-flash)$", $imagem["type"][$i]))
                            {
                                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png, *.gif, *.swf).');
                            }
                            else
                            {
                                $i_arquivo = $i + 1;
                                
                                $res_edicao_imagem &= move_uploaded_file($imagem['tmp_name'][$i], UPLOAD_DIR . "/conteudos/${codigo}_${i_arquivo}_img_r.${arq_ext}");
                                
                                $sql_edicao_imagem = sprintf("INSERT INTO ico_campos_paginas (arquivo, rascunho, numero, cod_tipos_campos, cod_paginas) VALUES ('%s', %d, %d, %d, %d)", texto2bd("${codigo}_${i_arquivo}_img_r.${arq_ext}"), true, $i + 1, $obj_tipos_campos->cod_tipos_campos, $codigo);
                                $res_edicao_imagem &= mysql_query($sql_edicao_imagem);
                            }
                        }
                    }
                }
                
                if ($res_del_titulo && $res_edicao_titulo && $res_del_texto && $res_edicao_texto && $res_del_link && $res_edicao_link && $res_del_imagem && $res_edicao_imagem)
                {
                    mensagemOk('Registro alterado com êxito!');
                }
                else
                {
                    mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                }
            }
            else
            {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        
        if ($cod_menus <= 0)
        {
            if (trim($nome_menu) != '')
            {
                $sql_edicao = sprintf("INSERT INTO ico_menus (menu, tipo, ordem, habilitado, cod_paginas, cod_menus_pai) VALUES ('%s', 'PAGINA', 0, 1, %d, %d)", $nome_menu, $codigo, $cod_menus_pai);
                
                if (!mysql_query($sql_edicao))
                {
                    mensagemErro('Erro ao inserir o menus', 'Por favor, verifique se o menu já não se encontra cadastrado.');
                }
            }
        }
        else
        {
            if (trim($nome_menu) != '')
            {
                $sql_edicao = sprintf("UPDATE ico_menus SET menu = '%s', cod_paginas = %d, cod_menus_pai = %d WHERE cod_menus = $cod_menus", $nome_menu, $codigo, $cod_menus_pai);
                
                if (!mysql_query($sql_edicao))
                {
                    mensagemErro('Erro ao alterar o menus', 'Por favor, verifique se o menu já não se encontra cadastrado.');
                }
            }
        }
        
        desconectar_bd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_simples.css" />

<script type="text/javascript" src="../lib/js/tiny_mce/tiny_mce.js"></script>

<script>

function visualizar_rascunho(chamada)
{
    window.open('../../' + chamada + '&rascunho=1', chamada, "status=yes");
}

function visualizar_publicado(chamada)
{
    window.open('../../' + chamada, chamada, "status=yes");
}

function excluir_imagem(cod) 
{
    if (confirm('Deseja excluir esta imagem?')) 
    {
        var acao = 'excluir_imagem';
        var cod_campos_paginas = cod;
        
        if(cod_campos_paginas > 0) 
        {
            var url = 'acao=' + acao + '&cod_campos_paginas=' + cod_campos_paginas;
            
            new Request.HTML({
                url: 'ico_pagina_ajax.php',
                onComplete: function(){
                    carregar_modelo();    
                }
            }).send(url);
        }
    }
}

function carregar_modelo() 
{
    var acao = 'montar';
    var cod_modelos = $('cod_modelos').getProperty('value');
    var cod_paginas = document.frmIncluir.cod_paginas.value;
    
    if(cod_modelos > 0) {
        var url = 'acao=' + acao + '&cod_modelos=' + cod_modelos + '&cod_paginas=' + cod_paginas;
        $('carregando_modelo').set('html', '<center><b>Carregando...</b></center>');
        
        new Request.HTML({
            url: 'ico_pagina_ajax.php',
            update: $('controles'),
            onComplete: function() 
            {
                $('carregando_modelo').set('html', '');
                
                // Ativa o tinyMce se houver algum textarea
                tinyMCE.init({
                    mode : "textareas",
                    theme : "advanced",
                    skin : "o2k7",
                    language : "pt",
                    plugins: "inlinepopups,fullscreen,media,table",
                    theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,media,|,fullscreen,|,code,table",
                    theme_advanced_buttons2 : "",
                    theme_advanced_buttons3 : "",
                    theme_advanced_toolbar_location : "top",
                    theme_advanced_toolbar_align : "left",
                    theme_advanced_statusbar_location : "bottom",
                    theme_advanced_resizing : false,
                    auto_reset_designmode : true,
                    forced_root_block : '',
                    entity_encoding : "raw"
                });
            }
        }).send(url);
    }
    else  
    {
        $('controles').set('html', '');
    }
}

function publicar(cod) 
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
    
    var input2 = new Element('input', 
    {
        'type': 'hidden',
        'name': 'acao',
        'value': 'publicar'
    });
  
    input.inject(form);
    input2.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
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

function init()
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
        
        carregar_modelo();
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
            document.frmIncluir.nome_pagina.value = '';
            document.frmIncluir.chamada.value = '';
            document.frmIncluir.cod_modelos.value = '';
            document.frmIncluir.habilitado.checked = true;
            document.frmIncluir.nome_menu.value = '';
            document.frmIncluir.cod_menus_pai.value = '';
            document.frmIncluir.cod_menus.value = 0;
            
            if($('endereco_legenda'))
            {
                $('endereco_legenda').destroy();
                $('endereco').destroy();
            }
            
            $('controles').set('html', '');
      
            document.frmIncluir.botao_submit.value = 'Cadastrar';
        }
    });
    
    $('cod_modelos').addEvent('change', carregar_modelo);
}

window.addEvent('domready', init);

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
        
        $conexaoexao = conectar_bd();
        
        $sql_buscar_registros = "SELECT p.*, m.modelo FROM $tabela p INNER JOIN ico_modelos m ON (p.cod_modelos = m.cod_modelos) WHERE $opcoes LIKE '%$filtro%' ";
        
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
        
        if ($acao == 'editar')
        {
            echo '<br>';
            echo '<div style="background-color: #fae5b0; border: 1px solid #fad163; padding: 10px; margin: 0pt auto; margin-bottom: 20px; font-weight: bold; width: ' . (LARGURA_PADRAO - 20) . 'px;">';
            echo 'Esta página foi alterada, se você deseja publica-la agora, <a href="javascript:publicar(' . $codigo . ');">clique aqui</a>.';
            echo '</div>';
        }
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
                    <td align="center">Página</td>
                    <td align="center">Título</td>
                    <td align="center" width="100">Endereço</td>
                    <td align="center" width="50">Modelo</td>
                    <td align="center" width="120">Criação</td>
                    <td align="center" width="120">Última Alteração</td>
                    <td align="center" width="40">Publicado</td>
                    <td align="center" width="40">Habilitado</td>
                    <td align="center" width="80">Visualizar Rascunho</td>
                    <td align="center" width="80">Visualizar Publicado</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->pagina) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->titulo) . '</td>';
                echo '<td align="center">' . $obj_buscar_registros->chamada . '</td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->modelo) . '</td>';
                echo '<td align="center">' . bd2datahora($obj_buscar_registros->data_hora_criacao) . '</td>';
                echo '<td align="center">' . bd2datahora($obj_buscar_registros->data_hora_alteracao) . '</td>';
                
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
                
                echo '<td align="center"><input type="button" class="botaoAzul" value="Visualizar" onclick="visualizar_rascunho(\'' . $obj_buscar_registros->chamada . '\')"></td>';
                echo '<td align="center"><input type="button" class="botaoAzul" value="Visualizar" onclick="visualizar_publicado(\'' . $obj_buscar_registros->chamada . '\')"></td>';
                
                echo '</tr>';
            }
            
            desconectar_bd($conexaoexao);
            
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
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data"
    onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido"
            for="nome_pagina">Nome da Página</label></td>
    </tr>
    <tr>
        <td class="sep tdbl tdbr"><input class="requerido" type="text"
            name="nome_pagina" id="nome_pagina" maxlength="45" size="103"
            value="<?
            echo bd2texto($obj_editar->pagina)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="titulo_pagina">Título da Página</label></td>
    </tr>
    <tr>
        <td class="sep tdbl tdbr"><input class="requerido" type="text"
            name="titulo_pagina" id="titulo_pagina" maxlength="50" size="103"
            value="<?
            echo bd2texto($obj_editar->titulo)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="chamada">Endereço
        de Chamada</label></td>
    </tr>
    <tr>
        <td class="sep tdbl tdbr"><input class="requerido" type="text"
            name="chamada" id="chamada" maxlength="45" size="103"
            value="<?
            echo bd2texto($obj_editar->chamada)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="cod_modelos">Modelo</label></td>
    </tr>
    <tr>
        <td class="sep tdbl tdbr"><select class="requerido" name="cod_modelos"
            id="cod_modelos">
            <option value=""></option>
        
            <?
            $conexao = conectar_bd();
            
            $sql_buscar_modelos = "SELECT * FROM ico_modelos WHERE biblioteca = 0 ORDER BY modelo";
            $res_buscar_modelos = mysql_query($sql_buscar_modelos);
            
            while ($obj_buscar_modelos = mysql_fetch_object($res_buscar_modelos))
            {
                echo '<option value="' . $obj_buscar_modelos->cod_modelos . '" ';
                
                if ($obj_buscar_modelos->cod_modelos == $obj_editar->cod_modelos)
                {
                    echo 'selected';
                }
                
                echo '>' . texto2bd($obj_buscar_modelos->modelo) . '</option>';
            }
            
            desconectar_bd($conexao);
            ?>
            
            </select>&nbsp;
        <div id="carregando_modelo"></div>
        </td>
    </tr>

    <tr>
        <td class="legenda sep tdbl tdbr">
        <?
        if ($codigo > 0)
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
    
    <?
    if ($codigo > 0)
    {
        $obj_menu_edicao = executar_busca_simples("SELECT * FROM ico_menus WHERE cod_paginas = $codigo LIMIT 1");
        echo '<input type="hidden" name="cod_menus" value="' . $obj_menu_edicao->cod_menus . '">';
    }
    else
    {
        $obj_menu_edicao = NULL;
        echo '<input type="hidden" name="cod_menus" value="0">';
    }
    ?>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="nome_menu">Nome do Menu</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input type="text" name="nome_menu" id="nome_menu"
            maxlength="45" size="45"
            value="<?
            echo texto2bd($obj_menu_edicao->menu)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label for="cod_menus_pai">Menu / Submenu</label></td>
    </tr>
    <tr>
        <td class="sep tdbl tdbr"><select name="cod_menus_pai"
            id="cod_menus_pai">
            <option value=""></option>
            <option value="0"
                <?
                if (($obj_menu_edicao->cod_menus_pai == 0) && ($obj_menu_edicao->cod_menus_pai != ''))
                {
                    echo 'SELECTED';
                }
                ?>>RAIZ</option>
        
        <?
        $conexao = conectar_bd();
        
        $sql_buscar_menus = "SELECT * FROM ico_menus WHERE tipo IN ('MENU', 'SUBMENU') ORDER BY menu";
        $res_buscar_menus = mysql_query($sql_buscar_menus);
        
        while ($obj_buscar_menus = mysql_fetch_object($res_buscar_menus))
        {
            echo '<option value="' . $obj_buscar_menus->cod_menus . '" ';
            
            if ($obj_buscar_menus->cod_menus == $obj_menu_edicao->cod_menus_pai)
                echo 'selected';
            
            echo '>' . bd2texto($obj_buscar_menus->menu) . '</option>';
        }
        
        desconectar_bd($conexao);
        ?>
        
      </select></td>
    </tr>

    <tr>
        <td class="sep tdbl tdbr" align="center" id="controles">&nbsp;</td>
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
