<?php

/**
 * Cadastro de Sec�es.
 *
 * @version 1.0
 * @package ipi
 * 
 * LISTA DE MODIFICA��ES:
 *
 * VERS�O    DATA         AUTOR         DESCRI��O 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       02/07/2014   Filipe         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro Situa��es das Mesas');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_mesas_situacoes';
$tabela = 'ipi_mesas_situacoes';
$campo_ordenacao = 'situacao';
$campo_filtro_padrao = 'situacao';
$quant_pagina = 50;
$exibir_barra_lateral = false;

/*
Tipos de STATUS

PUBLICADO
EXCLUIDO
RASCUNHO

*/

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        
        if (mysql_query($sql_del))
        {
            mensagemok('Os registros selecionados foram exclu�dos com sucesso!');
        }
        else
        {
            mensagemerro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usu�rios selecionados para exclus�o.');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);

        $foto_p = validaVarFiles('foto_p');
        $situacao = validaVarPost('situacao');
        $cor_situacao = validaVarPost('cor_situacao');

        $imagem_mesa  = validaVarPost('imagem_mesa');
        $conexao = conectabd();
        
            
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cor_situacao, situacao) VALUES ('%s', '%s')", $cor_situacao, $situacao);
            $res_edicao = mysql_query($sql_edicao);
            if ($res_edicao)
            {
                $codigo = mysql_insert_id();
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cor_situacao  = '%s', situacao  = '%s' WHERE $chave_primaria = $codigo", $cor_situacao, $situacao);
            $res_edicao = mysql_query($sql_edicao);
        }
        
        if ($res_edicao)
        {
          $resEdicaoImagem = true;
          // Alterando as Imagens pequenas
          if(count($foto_p['name']) > 0) {     
            if(trim($foto_p['name']) != '') {
              $arq_info = pathinfo($foto_p['name']);
              $arq_ext = $arq_info['extension'];
              if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $foto_p["type"])) {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados s�o imagens (*.jpg, *.png).');
              }
              else {                
                $resEdicaoImagem &= move_uploaded_file($foto_p['tmp_name'], UPLOAD_DIR."/mesas/${codigo}_mesa_p.${arq_ext}");
                                        
                $SqlEdicaoImagem = sprintf("UPDATE $tabela set imagem_mesa = '%s' WHERE $chave_primaria = $codigo", 
                           texto2bd("${codigo}_mesa_p.${arq_ext}"));
                
                $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
              }
            }          
          }
        }

        if ($res_edicao)
        {
            mensagemok('Registro alterado com �xito!');
        }  
        else
        {
            mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro j� n�o se encontra cadastrado.');
        }        
        
        desconectabd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script src="../../sys/lib/js/mascara.js" type="text/javascript"></script>
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

function excluirImagem_pequena(cod) {
  if (confirm('Deseja excluir esta imagem?\n\nATEN��O: Este � um processo irrevers�vel.')) {
    var acao = 'excluir_imagem_pequena';
    var cod_pizzas = cod;
    
    if(cod_pizzas > 0) {
      var url = 'acao=' + acao + '&cod_mesas_situacoes=' + cod_pizzas;
      
      new Request.JSON({url: 'ipi_mesas_situacoes_ajax.php', onComplete: function(retorno) {
        if(retorno.status != 'OK') {
          alert('Erro ao excluir esta imagem.');
        }
        else {
          if($('foto_p_figura')) {
            $('foto_p_figura').destroy();

          }
        }
      }}).send(url);
    }
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
        document.frmIncluir.situacao.value = '';
/*        document.frmIncluir.emails.value = '';
        document.frmIncluir.status.value = '';*/
      
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

        <!-- Conte�do -->
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
                    	<option value="<? echo $campo_filtro_padrao ?>"<? if ($opcoes == $campo_filtro_padrao) {echo 'selected';}?>>Nome</option>
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
        
        $sql_buscar_registros = "SELECT * FROM $tabela t WHERE t.$opcoes LIKE '%$filtro%' ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY situacao LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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
            echo '<a href="#" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Pr�xima&nbsp;&raquo;</a>';
        }
        else
        {
            echo '<span style="margin-left: 5px;">Pr�xima&nbsp;&raquo;</span>';
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
                <td align="center">Situa��o da Mesa</td>
                <td align="center">Cor da Situa��o</td>
              </tr>
            </thead>
            <tbody>
            <?
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
              echo '<tr>';
              echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
              echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->situacao) . '</a></td>';
              echo '<td align="center"><div style="width:120px; height: 30px; border: 1px solid black; background-color: #'. $obj_buscar_registros->cor_situacao.'"></div></td>';
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
        <!-- Conte�do -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li><a href="ipi_central_categorias_subcategorias.php">Subcategorias</a></li>
            <li><a href="ipi_central_situacoes.php">Situa��es</a></li>
            <li><a href="ipi_central_situacoes_subcategorias.php">Situa��es por Subcategorias</a></li>
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
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="situacao">Situa��o da Mesa</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input class="requerido" type="text" name="situacao" id="situacao" maxlength="100" size="45" value="<? echo bd2texto($obj_editar->situacao)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label for="cor_situacao">Cor da Situa��o</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input type="text" name="cor_situacao" id="cor_situacao" maxlength="10" size="10" value="<? echo bd2texto($obj_editar->cor_situacao)?>">
        <br /> FFFFFF = Branco | 88EEFF = Azul | 88EE88 = Verde | FD7E7E = Vermelho | FBFFAA = Amarelo 
        </td>
    </tr>
    
      <tr>
        <td class="legenda tdbl tdbr"><label for="foto_p">Imagem</label></td>
      </tr>
         
        <?
        if (is_file(UPLOAD_DIR . '/mesas/' . $obj_editar->imagem_mesa))
        {
            echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_p_figura" style="padding: 15px;">';
            
            echo '<img src="' . UPLOAD_DIR . '/mesas/' . $obj_editar->imagem_mesa . '">';
            
            echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem_pequena(' . $obj_editar->$chave_primaria . ');"></td></tr>';
        }
        ?>
    
      <tr>
        <td class="sep tdbl tdbr sep"><input type="file" name="foto_p" id="foto_p" size="40"></td>
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