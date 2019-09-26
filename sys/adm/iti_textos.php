<?php

/**
 * Cadastro de Secões.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       11/09/2009   ELIAS         Criado.
 *
 */

$cod_secoes = 5; //Seleciona o tipo de secao.

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro Notícias');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_textos';
$tabela = 'iti_textos';
$campo_ordenacao = 'titulo';
$campo_filtro_padrao = 'titulo';
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
            mensagemok('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemerro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $titulo = validaVarPost('titulo');
        $texto = validaVarPost('texto');
        $data_texto = data2bd(validaVarPost('data_texto'));
        $orientacao_imagem = validaVarPost('orientacao_imagem');
        $imagem = validaVarFiles('imagem');
        $situacao = validaVarPost('situacao');
        $link = validaVarPost('link');
		$data_inicio_exibicao = data2bd(validaVarPost('data_inicio_exibicao'));
        
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_secoes, titulo, texto, data_texto, orientacao_imagem, situacao, link, data_inicio_exibicao, data_hora_texto) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NOW())", $cod_secoes, $titulo, $texto, $data_texto, $orientacao_imagem, $situacao, $link, $data_inicio_exibicao);
            $res_edicao = mysql_query($sql_edicao);
            if ($res_edicao)
            {
                $codigo = mysql_insert_id();
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_secoes = '%s', titulo = '%s', texto = '%s', data_texto = '%s', orientacao_imagem = '%s', situacao = '%s', link = '%s', data_inicio_exibicao = '%s' WHERE $chave_primaria = $codigo", $cod_secoes, $titulo, $texto, $data_texto, $orientacao_imagem, $situacao, $link, $data_inicio_exibicao);
            $res_edicao = mysql_query($sql_edicao);
        }

        if (trim($imagem['name']) != '') 
        {
        	
        	$arq_info = pathinfo($imagem['name']);
            $arq_ext = strtolower($arq_info['extension']);
                
            if (!eregi("^image|application\\/(pjpeg|jpeg|jpg|swf|png|gif|x-shockwave-flash)$", $imagem["type"]))
            {
            	exibir_mensagem_erro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png, *.gif, *.swf).');
			}
            else
            {
            	$sql_buscar_imagem_antigo = "SELECT * FROM $tabela WHERE $chave_primaria = $codigo";
	        	$res_buscar_imagem_antigo = mysql_query($sql_buscar_imagem_antigo);
	        	$obj_buscar_imagem_antigo = mysql_fetch_object($res_buscar_imagem_antigo);

	        	if( (file_exists(UPLOAD_DIR."/imagens_iti/".$obj_buscar_imagem_antigo->imagem_gde)) && ($obj_buscar_imagem_antigo->imagem_gde!='') )
	        	{
	        		unlink(UPLOAD_DIR."/imagens_iti/".$obj_buscar_imagem_antigo->imagem_gde);	
	        	}
            	
            	if (move_uploaded_file($imagem['tmp_name'], UPLOAD_DIR . "/imagens_iti/${codigo}_imagem_gde.${arq_ext}"))
                {
                	$sql_edicao_imagem = sprintf("UPDATE $tabela SET imagem_gde = '%s' WHERE $chave_primaria = $codigo", "${codigo}_imagem_gde.${arq_ext}");
                    $res_edicao = mysql_query($sql_edicao_imagem);
				}
			}
        }
        
        if ($res_edicao)
        {
            mensagemok('Registro alterado com êxito!');
        }  
        else
        {
            mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }        
        
        desconectabd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>
<script type="text/javascript" src="../../sys/lib/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
<!--
tinyMCE.init({
  mode : "textareas",
  theme : "advanced",
  skin : "o2k7",
  language : "pt",
  plugins: "inlinepopups,fullscreen,media, table", 
  theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,fullscreen,code,media,table",
  theme_advanced_buttons2 : "",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_statusbar_location : "bottom",
  theme_advanced_resizing : true,
  auto_reset_designmode : true,
  force_br_newlines : true,
  force_p_newlines : false,
  entity_encoding : "raw"
});

//-->
</script>

<script>
function editar_fotos(cod_produto)
{
    document.frmImagem.codigo_produtos.value = cod_produto;
    document.frmImagem.submit(); 
}

function verificaCheckbox(form) {
  var cInput = 0;
  var checkBox = form.getElementsByTagName('input');

  for (var i = 0; i < checkBox.length; i++) {
    if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
      cInput++; 
    }
  }
   
  if(cInput > 0) {
    if (confirm('Deseja excluir os registros selecionados?')) {
      return true;
    }
    else {
      return false;
    }
  }
  else {
    alert('Por favor, selecione os itens que deseja excluir.');
     
    return false;
  }
}

function editar(cod) {
  var form = new Element('form', {
    'action': '<?
    echo $_SERVER['PHP_SELF']?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<?
    echo $chave_primaria?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<? echo $chave_primaria?>.value > 0) {
    <?
    if ($acao == '')
        echo 'tabs.irpara(1);';
    ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      	document.frmIncluir.<? echo $chave_primaria?>.value = '';
        document.frmIncluir.cod_secoes.value = '';
        document.frmIncluir.titulo.value = '';
        document.frmIncluir.situacao.value = '';
        document.frmIncluir.data_texto.value = '';
        document.frmIncluir.data_inicio_exibicao.value = '';
        document.frmIncluir.orientacao_imagem.value = '';
        tinyMCE.getInstanceById('texto').setContent('');
      
        if($('foto_figura')) 
        {
        	$('foto_figura').destroy();
		}
      
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
                    	<option value="<? echo $campo_filtro_padrao ?>"<? if ($opcoes == $campo_filtro_padrao) {echo 'selected';}?>>Título</option>
                	</select>
                </td>
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
        
        $sql_buscar_registros = "SELECT * FROM $tabela t INNER JOIN iti_secoes s ON(t.cod_secoes=s.cod_secoes) WHERE s.cod_secoes=$cod_secoes AND t.$opcoes LIKE '%$filtro%' $sessao ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY t.data_texto DESC LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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

        <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">

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
                    <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
                    <td align="center" width="200">Seção</td>
                    <td align="center" width="300">Título</td>
                    <td align="center" width="100">Data do Texto</td>
                    <td align="center" width="100">Data Início Exibição</td>
                    <td align="center" width="80">Situação</td>
                </tr>
            </thead>
            <tbody>
            <?
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                
                echo '<td align="center">' . bd2texto($obj_buscar_registros->secao) . '</td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->titulo) . '</a></td>';
                
                echo '<td align="center">' . bd2data($obj_buscar_registros->data_texto) . '</td>';
                echo '<td align="center">' . bd2data($obj_buscar_registros->data_inicio_exibicao) . '</td>';
                if ($obj_buscar_registros->situacao == 'ATIVO')
                {
                    echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
                }
                else
                {
                    echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
                }
                echo '</tr>';
            }
            desconectabd($conexao);
            ?>
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir">
        </form>

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
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
    
    <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="titulo">Titulo</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="titulo" id="titulo" maxlength="100" size="45" value="<? echo bd2texto($obj_editar->titulo)?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label class="requerido" for="texto">Texto</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" colspan="2"><textarea rows="10" cols="50" class="requerido" name="texto" id="texto"> <? echo bd2texto($obj_editar->texto)?></textarea></td>
    </tr>    
    
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="data_texto">Data do Texto</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="data_texto" id="data_texto" maxlength="15" size="15" onkeypress="javascript: return MascaraData(this,event);" value="<? echo bd2data($obj_editar->data_texto)?>"></td>
    </tr>

	<? 
	if ($obj_editar->data_inicio_exibicao!='')
	{
		$data_exibicao = bd2data($obj_editar->data_inicio_exibicao);
	}
	else 
	{
		$data_exibicao = date('d/m/Y');
	}
	?>
	<tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="data_inicio_exibicao">Data início da exibição</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="data_inicio_exibicao" id="data_inicio_exibicao" maxlength="15" size="15" onkeypress="javascript: return MascaraData(this,event);" value="<? echo $data_exibicao;?>"></td>
    </tr>

    <tr><td class="legenda tdbl tdbr"><label for="imagem">Imagem (*.jpg)</label></td></tr>
    <?
    
    if (is_file(UPLOAD_DIR.'/imagens_iti/'.$obj_editar->imagem_gde)) 
    {
    	echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_figura" style="padding: 15px;">';
      
    	if($obj_editar->imagem_gde)
      	{
			$info_imagem = getimagesize(UPLOAD_DIR."/imagens_iti/".$obj_editar->imagem_gde);
			
      		if ($info_imagem['mime'] == "application/x-shockwave-flash") 		
      		{
      			echo '<embed quality="autolow" src="'.UPLOAD_DIR."/imagens_iti/".$obj_editar->imagem_gde.'" type="application/x-shockwave-flash" align="middle" '.$info_imagem[3].'></embed>';	
      		}
      		else 
      		{
	      		if ($info_imagem[0]>260)
				{
					echo '<img src="'.UPLOAD_DIR.'/imagens_iti/'.$obj_editar->imagem_gde.'" width="260">';
				}
				else
				{
					echo '<img src="'.UPLOAD_DIR.'/imagens_iti/'.$obj_editar->imagem_gde.'">';					      			
				}
      		}
          	  
      	}
      
      	//echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem('.$obj_editar->$chave_primaria.');"></td></tr>';
    }
    ?>
    <tr><td class="sep tdbl tdbr sep"><input type="file" name="imagem" id="imagem" size="40"></td></tr>

    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label for="orientacao_imagem">Localização da imagem</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" colspan="2">
            <select class="input" name="orientacao_imagem">
                <option value="DIREITA" <? if($obj_editar->orientacao_imagem == 'DIREITA') echo 'SELECTED' ?>>Direita</option>  
                <option value="ESQUERDA" <? if($obj_editar->orientacao_imagem == 'ESQUERDA') echo 'SELECTED' ?>>Esquerda</option>
            </select>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label for="situacao">Situacao</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" colspan="2">
            <select class="input" name="situacao">
                <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'SELECTED' ?>>Ativo</option>  
                <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'SELECTED' ?>>Inativo</option>
            </select>
        </td>
    </tr>

	<tr>
        <td class="legenda tdbl tdbr"><label for="link" id="lbllink">Link</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input type="text" name="link" id="link" maxlength="200" size="45" value="<? echo bd2texto($obj_editar->link)?>"></td>
    </tr>

    <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<? echo $chave_primaria?>" value="<?  echo $codigo?>">

</form>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>