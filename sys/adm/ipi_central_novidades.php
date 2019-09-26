<?php

/**
 * Cadastro de Novidades da Central.
 *
 * @version 1.0
 * @package iti
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       06/09/2012   Filipe         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/canal_comunicacao.php';

cabecalho('Cadastro Novidades da Central');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_novidades';
$tabela = 'ipi_comunicacao_novidades';
$campo_ordenacao = 'titulo_novidade';
$campo_filtro_padrao = 'titulo_novidade';
$quant_pagina = 50;
$exibir_barra_lateral = false;

$codigo_usuario = $_SESSION['usuario']['codigo'];

/*
Tipos de STATUS

PUBLICADO
EXCLUIDO
RASCUNHO

*/
function strip_tags_content($text, $tags = '', $invert = FALSE) {

  preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
  $tags = array_unique($tags[1]);
   
  if(is_array($tags) AND count($tags) > 0) {
    if($invert == FALSE) {
      return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    else {
      return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
    }
  }
  elseif($invert == FALSE) {
    return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
  }
  return $text;
} 

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del = "UPDATE $tabela SET status='EXCLUIDO' WHERE $chave_primaria IN ($indices_sql)";
        
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
    case 'excluir_arquivo':
        $codigo = validaVarPost($chave_primaria);
        $nome = validaVarPost("nome_arquivo");

        $con = conectabd();

        $sql_deletar_arquivo = "DELETE FROM ipi_comunicacao_novidades_arquivos where cod_novidades = '$codigo' and nome_arquivo='$nome'";
        //echo "<br/>".$sql_deletar_arquivo."</br>";
        $res_deletar_arquivo = mysql_query($sql_deletar_arquivo);

        $sucesso = ($res_deletar_arquivo ? true : false );

        if(file_exists(UPLOAD_DIR."/comunicacao/novidades/$nome"))
        {
         // echo "ACHO";
          if(unlink(UPLOAD_DIR."/comunicacao/novidades/$nome"))
          {
           // echo "<br/>DELETO";
            $sucesso &= true;
          }
          else
          {
            $sucesso &= false;
          }
        }
        else
        {
          $sucesso &= false;
        }

        desconectabd($con);
        if($sucesso)
        {
          mensagemok('Os arquivos selecionados foram excluídos com sucesso!');
        } 
        else
        {
          mensagemerro('Erro ao excluir o arquivo', 'Por favor, comunique a equipe de suporte informando o arquivo solicitado para a exclusão.');
        }
        $acao = "";
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);

        $titulo_novidade = validaVarPost('titulo_novidade');
        $novidade = validaVarPost('novidade');
        $data_novidade = data2bd(validaVarPost('data_novidade'));
        $status = validaVarPost('status');
        $codigo_usuario = $_SESSION['usuario']['codigo'];
        $destaque = validaVarPost('destaque');
        $email = validaVarPost('email');
        $arquivos = validaVarFiles('arq');
        $conexao = conectabd();

        if($destaque >=1 && $destaque !="")
        {
            $destaque = 1;
        }else
            $destaque = 0;
        
        if($email >=1 && $email !="" && $status=="PUBLICADO")
        {
            $email = 1;
        }else
            $email = 0; 

        if ($codigo <= 0)
        {
          $sql_edicao = sprintf("INSERT INTO $tabela (titulo_novidade,cod_usuarios, novidade, status, destaque, data_novidade) VALUES ('%s','%s','%s', '%s', '%s', NOW())", $titulo_novidade,$codigo_usuario, $novidade, $status,$destaque);
          $res_edicao = mysql_query($sql_edicao);
          if ($res_edicao)
          {
              $codigo = mysql_insert_id();
          }
        }
        else
        {
          $sql_edicao = sprintf("UPDATE $tabela SET titulo_novidade = '%s', novidade = '%s', status = '%s',destaque = '%d' WHERE $chave_primaria = $codigo", $titulo_novidade, $novidade, $status,$destaque);
          $res_edicao = mysql_query($sql_edicao);
        }
          $canal_novidades = new CanalDeComunicacao_novidades();
          if($email)
          $canal_novidades->enviar_email($codigo,$codigo_usuario);

          $qtd_arqs = count($arquivos['name']);
          $resUploadArquivo = true;
          for($i = 0;$i<$qtd_arqs;$i++)
          {
            if(count($arquivos['name'][$i]) > 0) {     
              if(trim($arquivos['name'][$i]) != '') 
              {
                $arq_info = pathinfo($arquivos['name'][$i]);
                $arq_temp_nome = $arquivos['tmp_name'][$i];
                //echo "<br/>mandou upload $codigo,$arq_info,$arq_temp_nome";
                //$sucesso_uploads &= $this->upload_arquivo($codigo,$usuario_criador,$arq_info,$arq_temp_nome);

                $arq_ext = $arq_info['extension'];
                $arq_nome = $arq_info['filename'];

                $nome_salvo = "${codigo}_${arq_nome}".date("his").".${arq_ext}";

                $resUploadArquivo &= move_uploaded_file($arq_temp_nome, UPLOAD_DIR."/comunicacao/novidades/$nome_salvo");
                //echo "<br/>".($resUploadArquivo ? 'move deu' : 'move falhou');    
                $SqlEdicaoImagem = sprintf("INSERT into ipi_comunicacao_novidades_arquivos(cod_novidades,nome_arquivo,descricao_arquivo,data_hora_adicao) values (%d,'%s','',NOW())", 
                           $codigo,texto2bd($nome_salvo));
                //echo "<br/> qeury ypload  ".$SqlEdicaoImagem."<br/><br/>";

                $resUploadArquivo &= mysql_query($SqlEdicaoImagem);
              }          
            }
          }

        if ($res_edicao && $resUploadArquivo)
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
 function add_arq(div_alterar)
  {
    var div_arquivo = new Element('div', {
        name: 'arquivo'
    });

    var label_arquivo = new Element('label', {
        html: 'Arquivo :'
    });

    var arquivo = new Element('input', {
        type: 'file',
        name: 'arq[]'
    });

    //new Element('span#another')]
    div_arquivo.adopt(new Element('<br/>'));
    div_arquivo.adopt(label_arquivo);
    div_arquivo.adopt(new Element('<br/>'));
    div_arquivo.adopt(arquivo);
    div_arquivo.adopt(new Element('<br/>'));
    $(div_alterar).adopt(div_arquivo);
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

function excluir_arquivo(nome_arq,cod) {
  if(confirm("Tem certeza que dejeja excluir o arquivo '"+nome_arq+"'?"))
  {
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

    var input2 = new Element('input', {
      'type': 'hidden',
      'name': 'nome_arquivo',
      'value': nome_arq
    });

    var input3 = new Element('input', {
      'type': 'hidden',
      'name': 'acao',
      'value': 'excluir_arquivo'
    });
    input.inject(form);
    input2.inject(form);
    input3.inject(form);
    $(document.body).adopt(form);
    
    form.submit();
  }
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
        document.frmIncluir.titulo_novidade.value = '';
        document.frmIncluir.novidade.value = '';
        document.frmIncluir.status.value = '';
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
        
        $sql_buscar_registros = "SELECT t.*,usu.nome FROM $tabela t inner join nuc_usuarios usu on usu.cod_usuarios = t.cod_usuarios WHERE t.$opcoes LIKE '%$filtro%' ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY t.cod_novidades DESC LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        //echo $sql_buscar_registros."<br/>";
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
                    <td align="center" width="30">Nº Novidade</td>
                    <td align="center" width="200">Título /<br/>Usuario Criador</td>
                    <td align="center" width="300">Novidade</td>
                    <td align="center" width="100">Data </td>
                    <td align="center" width="150">Status</td>
                </tr>
            </thead>
            <tbody>
            <?
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                
                echo '<td align="center">' . $obj_buscar_registros->cod_novidades . '</td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->titulo_novidade) . '</a><br/><small> Criado por '.$obj_buscar_registros->nome.'</small></td>';

                echo '<td align="center">'. substr(strip_tags(bd2texto($obj_buscar_registros->novidade)), 0, 100).'</td>';

                echo '<td align="center">' . bd2data($obj_buscar_registros->data_novidade) . '</td>';

                echo '<td align="center">'. bd2texto($obj_buscar_registros->status).'</td>';

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
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="titulo_novidade">Titulo</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="titulo_novidade" id="titulo_novidade" maxlength="100" size="45" value="<? echo bd2texto($obj_editar->titulo_novidade)?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label class="requerido" for="texto">Novidade</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" colspan="2"><textarea rows="10" cols="50" class="requerido" name="novidade" id="novidade"> <? echo bd2texto($obj_editar->novidade)?></textarea></td>
    </tr>    
    
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="data_novidade">Data do Novidade</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text" name="data_novidade" id="data_novidade" maxlength="15" size="15" onkeypress="javascript: return MascaraData(this,event);" <? if($obj_editar->data_novidade) echo "disabled=disabled value='".bd2data($obj_editar->data_novidade)."'"; else echo "disabled=disabled value='".bd2data(date("Y-m-d"))."'" ?> ></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="destaque">Destaque</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="checkbox" name="destaque" id="destaque"<? if($obj_editar->destaque) echo "checked=checked " ?> value='1'></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="email">Enviar Email?</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="checkbox" name="email" id="email" checked=checked value='1'></td>
    </tr>


    <tr>
        <td class="legenda tdbl tdbr" colspan="2"><label for="status">Situacao</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" colspan="2">
            <select class="input" name="status">
                <option value="RASCUNHO" <? if($obj_editar->status == 'RASCUNHO') echo 'SELECTED' ?>>Rascunho</option>  
                <option value="PUBLICADO" <? if($obj_editar->status == 'PUBLICADO') echo 'SELECTED' ?>>Publicado</option>
                <option value="EXCLUIDO" <? if($obj_editar->status == 'EXCLUIDO') echo 'SELECTED' ?>>Excluido</option>
            </select>
        </td>
    </tr>
    <tr>
        <td align='center'>
          <?  
            if($codigo > 0)
            {
              $con = conectar_bd();
              $sql_buscar_uploads = "select * from ipi_comunicacao_novidades_arquivos where cod_novidades='$codigo'";
              $res_buscar_uploads = mysql_query($sql_buscar_uploads);
              $num_buscar_uploads = mysql_num_rows($res_buscar_uploads);

              if($num_buscar_uploads>0)
              {
                echo '<div id="cont_anexos">';
                echo "<h3>Anexos</h3>";
                echo "<br/>";
                echo "<table class='listaEdicao'>";
                echo "<thead><tr><td style='background-color:#EB8612';
    border: 1px solid #EB8612;'>Nome do Arquivo</td></tr></thead><tbody>";
                while($obj_buscar_uploads = mysql_fetch_object($res_buscar_uploads))
                {
                  echo "<tr>";
                  echo "<td ><a href='".UPLOAD_DIR."/comunicacao/novidades/".$obj_buscar_uploads->nome_arquivo."' target='_blank'>".$obj_buscar_uploads->nome_arquivo."</a><a style='float:right' href='javascript:void(0);' onclick='excluir_arquivo(\"$obj_buscar_uploads->nome_arquivo\",\"$codigo\")'>Excluir arquivo</a></td>";
                }
                echo "</tbody></table></div><br/><br/>";
              }
              desconectar_bd($con);
            }
          ?>
        </td>
      </tr>
    <tr>
        <td align='center'>
            <div id='upload_arquivos_edicao'>
              <a href='javascript:void(0)' onclick='add_arq("upload_arquivos_edicao")'>Adicionar mais um arquivo </a>
              <div name='arquivo'>
                <label for='arq[]'>Arquivo:<br /></label>&nbsp;<input name='arq[]' type='file' /><br/><br />
              </div>
            </div>
        </td>
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