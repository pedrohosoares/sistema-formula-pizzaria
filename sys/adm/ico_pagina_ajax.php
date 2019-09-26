<?php

/**
 * Cadastro de Páginas Ajax.
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

require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/sessao.php';
require_once '../lib/php/formulario.php';

$acao = validar_var_post('acao');

$cod_modelos = validar_var_post('cod_modelos', '/[0-9]+/');
$cod_paginas = validar_var_post('cod_paginas', '/[0-9]+/');
$cod_campos_paginas = validar_var_post('cod_campos_paginas', '/[0-9]+/');

switch ($acao)
{
    case 'montar':
        $conexao = conectar_bd();
        
        $sql_buscar_quantidade = sprintf("SELECT * FROM ico_campos_modelos c INNER JOIN ico_tipos_campos t ON (c.cod_tipos_campos = t.cod_tipos_campos) WHERE c.cod_modelos = %d ORDER BY t.ordem, t.tipo", $cod_modelos);
        $sql_buscar_quantidade = mysql_query($sql_buscar_quantidade);
        
        echo '<table align="center" cellpadding="0" cellspacing="0">';
        
        while ($obj_buscar_quantidade = mysql_fetch_object($sql_buscar_quantidade))
        {
            for ($q = 1; $q <= $obj_buscar_quantidade->quantidade; $q++)
            {
                if ($cod_paginas > 0)
                {
                    $obj_buscar = executar_busca_simples("SELECT * FROM ico_campos_paginas WHERE cod_paginas = $cod_paginas AND cod_tipos_campos = " . $obj_buscar_quantidade->cod_tipos_campos . " AND numero = $q  AND rascunho = 1 LIMIT 1", $conexao);
                }
                else
                {
                    $obj_buscar = null;
                }
                
                $tamanho = ($obj_buscar_quantidade->tipo == 'IMAGEM') ? 88 : 103;
                
                echo '<tr><td>&nbsp;</td></tr>';
                
                switch ($obj_buscar_quantidade->tipo)
                {
                    case 'TITULO':
                        echo '<tr><td align="left" class="legenda"><label for="TITULO">' . utf8_encode('Título') . ' ' . $q . '</label></td></tr>';
                        echo '<tr><td align="left"><input id="' . strtolower($obj_buscar_quantidade->tipo) . '_' . $q . '" name="' . strtolower($obj_buscar_quantidade->tipo) . '[]" value="' . bd2texto(utf8_encode($obj_buscar->conteudo)) . '" type="text" size="' . $tamanho . '"></td></tr>';
                        break;
                    case 'TEXTO':
                        echo '<tr><td align="left" class="legenda"><label for="TEXTO">' . utf8_encode('Texto') . ' ' . $q . '</label></td></tr>';
                        echo '<tr><td align="left"><textarea name="' . strtolower($obj_buscar_quantidade->tipo) . '[]" id="' . strtolower($obj_buscar_quantidade->tipo) . '_' . $q . '" cols="105" rows="15">' . bd2texto(utf8_encode($obj_buscar->conteudo)) . '</textarea></td></tr>';
                        break;
                    case 'LINK':
                        echo '<tr><td align="left" class="legenda"><label for="TITULO_LINK_' . $q . '">' . utf8_encode('Título do Link') . ' ' . $q . '</label></td></tr>';
                        echo '<tr><td align="left"><input id="titulo_link_' . $q . '" name="titulo_link[]" value="' . bd2texto(utf8_encode($obj_buscar->auxiliar)) . '" type="text" size="' . $tamanho . '"></td></tr>';
                        
                        echo '<tr><td align="left" class="legenda"><label for="LINK_' . $q . '">' . utf8_encode('Endereço do Link') . ' ' . $q . '</label></td></tr>';
                        echo '<tr><td align="left"><input id="' . strtolower($obj_buscar_quantidade->tipo) . '_' . $q . '" name="' . strtolower($obj_buscar_quantidade->tipo) . '[]" value="' . bd2texto(utf8_encode($obj_buscar->conteudo)) . '" type="text" size="' . $tamanho . '"></td></tr>';
                        break;
                    case 'IMAGEM':
                        echo '<tr><td align="left" class="legenda"><label for="IMAGEM">' . utf8_encode('Imagem') . ' ' . $q . ' (*.jpg, *.png, *.gif, *.swf)</label></td></tr>';
                        
                        if (is_file(UPLOAD_DIR . "/conteudos/" . $obj_buscar->arquivo))
                        {
                            echo '<tr><td>&nbsp;</td></tr>';
                            echo '<tr><td align="center" style="padding: 15px; border: 1px solid #ccc;">';
                            
                            $info_imagem = getimagesize(UPLOAD_DIR."/conteudos/".$obj_buscar->arquivo);                            
                            
                            if ($info_imagem['mime'] == "application/x-shockwave-flash")
                            {
                                echo '<object>';
                                echo '<param name="movie" value="' . UPLOAD_DIR . '/conteudos/' . $obj_buscar->arquivo . '">';
                                //echo '<embed src="' . UPLOAD_DIR_CONTEUDO . '/' . $obj_campo->arquivo . '" width="' . $objBusca->largura . '" height="' . $objBusca->altura . '" wmode="transparent">';
                                echo '<embed src="' . UPLOAD_DIR . '/conteudos/' . $obj_buscar->arquivo . '" wmode="transparent" '.$info_imagem[3].'>';
                                echo '</embed>';
                                echo '</object>';
                            }
                            else
                            {
                                if ($info_imagem[0]>260)
								{
									echo '<img src="'.UPLOAD_DIR.'/conteudos/'.$obj_buscar->arquivo.'" width="260">';
								}
								else
								{
									echo '<img src="'.UPLOAD_DIR.'/conteudos/'.$obj_buscar->arquivo.'">';					      			
								}
                            }
                            
                            echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluir_imagem(' . $obj_buscar->cod_campos_paginas . ');"></td></tr>';
                            echo '<tr><td>&nbsp;</td></tr>';
                        }
                        
                        echo '<tr><td align="left"><input id="' . strtolower($obj_buscar_quantidade->tipo) . '_' . $q . '" name="' . strtolower($obj_buscar_quantidade->tipo) . '[]" value="' . $obj_buscar->arquivo . '" type="file" size="' . $tamanho . '"></td></tr>';
                        break;
                }
            }
            
            echo '<tr><td>&nbsp;</td></tr>';
        }
        
        echo '</table>';
        
        desconectar_bd($conexao);
        break;
    case 'excluir_imagem':
        $conexao = conectar_bd();
        
        $obj_buscar_arquivo = executar_busca_simples("SELECT * FROM ico_campos_paginas WHERE cod_campos_paginas = $cod_campos_paginas LIMIT 1", $conexao);
        
        $sql_del_imagem = "DELETE FROM ico_campos_paginas WHERE cod_campos_paginas = $cod_campos_paginas";
        
        if (mysql_query($sql_del_imagem))
        {
            if (file_exists(UPLOAD_DIR . "/conteudos/" . $obj_buscar_arquivo->arquivo))
            {
                unlink(UPLOAD_DIR . "/conteudos/" . $obj_buscar_arquivo->arquivo);
            }
        }
        
        desconectar_bd($conexao);
        break;
}

?>