<?php

/**
 * Cadastro de Secões.
 *
 * @version 1.0
 * @package ipi
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       02/07/2014   Filipe         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Impressoras');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_impressoras';
$tabela = 'ipi_impressoras';
$campo_ordenacao = 'nome_impressora';
$campo_filtro_padrao = 'nome_impressora';
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

        $nome_impressora = validaVarPost('nome_impressora');
        $situacao  = validaVarPost('situacao');
        $cod_pizzarias  = validaVarPost('cod_pizzarias');
        $info_gerais  = validaVarPost('informacoes_gerais');
        $ip  = validaVarPost('ip');
        $mac_address  = validaVarPost('mac_address');

        $conexao = conectabd();
        
            
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (mac_address, ip, info_gerais, nome_impressora, situacao, cod_pizzarias) VALUES ('%s','%s','%s','%s','%s',%d)", $mac_address, $ip, $info_gerais, $nome_impressora, $situacao, $cod_pizzarias);
            $res_edicao = mysql_query($sql_edicao);
            if ($res_edicao)
            {
                $codigo = mysql_insert_id();
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET mac_address = '%s', ip = '%s', info_gerais = '%s', situacao = '%s', nome_impressora = '%s', cod_pizzarias = %d WHERE $chave_primaria = $codigo", $mac_address, $ip, $info_gerais, $situacao, $nome_impressora, $cod_pizzarias);
            $res_edicao = mysql_query($sql_edicao);
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
        document.frmIncluir.nome_impressora.value = '';
        document.frmIncluir.situacao.value = '';
        document.frmIncluir.cod_pizzarias.value = '';
      
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
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $filtro = validaVarPost('filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

            <tr>
                <td class="legenda tdbl tdbt" align="right">
                	<select name="opcoes">
                    	<option value="<? echo $campo_filtro_padrao ?>"<? if ($opcoes == $campo_filtro_padrao) {echo 'selected';}?>>Códido</option>
                	</select>
                </td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>

          <tr>
            <td class="legenda tdbl " align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA) ?>:</label></td>
            <td class="">&nbsp;</td>
            <td class="tdbr ">
              <select name="cod_pizzarias" id="cod_pizzarias">
                
                <?
                $cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
                $con = conectabd();
                $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
                $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                //echo "<option value='$cod_pizzarias_usuario'>Todas as Pizzarias</option>";
                while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
                {
                  echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                  if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
                  {
                    echo 'selected';
                  }
                  echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
                }
                ?>
              </select>
            </td>
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
        
        $sql_buscar_registros = "SELECT t.*, p.nome FROM $tabela t inner join ipi_pizzarias p on p.cod_pizzarias = t.cod_pizzarias WHERE t.$opcoes LIKE '%$filtro%' and p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ";

        if($cod_pizzarias!="")
        {
          $sql_buscar_registros .= "and p.cod_pizzarias = '".$cod_pizzarias."'";
        }
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY nome_impressora LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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
            echo '<input type="hidden" name="cod_pizzarias" value="' . $cod_pizzarias . '">';
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
                    <td align="center" width="150">Código da Impressora</td>
                    <td align="center"><? echo ucfirst(TIPO_EMPRESA) ?></td>
                    <td align="center">Nome da Impressora</td>
                    <td align="center">IP</td>
                    <td align="center">Mac Address</td>
                    <td align="center">Informações</td>
                    <td align="center" width="150">Situação</td>
                </tr>
            </thead>
            <tbody>
            <?
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->cod_impressoras).'</td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->nome).'</td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->nome_impressora) . '</a></td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->ip).'</td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->mac_address).'</td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->info_gerais).'</td>';
                echo '<td align="center">'. bd2texto($obj_buscar_registros->situacao).'</td>';
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
            <li><a href="ipi_central_categorias_subcategorias.php">Subcategorias</a></li>
            <li><a href="ipi_central_situacoes.php">Situações</a></li>
            <li><a href="ipi_central_situacoes_subcategorias.php">Situações por Subcategorias</a></li>
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
    <td class="legenda tdbl tdbt tdbr" colspan="2"><label class="requerido"
      for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA); ?>s</label></td>
  </tr>
  <tr>
    <td class="tdbl tdbr" colspan="2"><select name="cod_pizzarias"
      id="cod_pizzarias">
      <option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
        while ($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias))
        {
            echo '<option value="' . $objBuscaPizzarias->cod_pizzarias . '" ';
            
            if ($objBuscaPizzarias->cod_pizzarias == $obj_editar->cod_pizzarias)
                echo 'selected';
            
            echo '>' . bd2texto($objBuscaPizzarias->nome) . '</option>';
        }
        
        desconectabd($con);
        ?>
      </select></td>
  </tr>
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="nome_impressora">Nome da Impressora:</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input class="requerido" type="text" name="nome_impressora" id="nome_impressora" maxlength="100" size="45" value="<? echo bd2texto($obj_editar->nome_impressora)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbr tdbl"><label for="ip">IP:</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input  type="text" name="ip" id="ip" maxlength="40" size="45" value="<? echo bd2texto($obj_editar->ip)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbr tdbl"><label for="mac_address">Mac Address:</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input  type="text" name="mac_address" id="mac_address" maxlength="40" size="45" value="<? echo bd2texto($obj_editar->mac_address)?>"></td>
    </tr>

    <tr>
        <td class="legenda tdbr tdbl"><label for="mac_address">Informações Gerais:</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
        <textarea name="informacoes_gerais" cols="43" rows="4"><? echo bd2texto($obj_editar->informacoes_gerais)?></textarea>
        </td>
    </tr>

    
    <tr>
        <td class="legenda tdbr tdbl"><label for="situacao">Situação</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" colspan="2">
            <select name="situacao">
                <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'SELECTED' ?>>Ativo</option>  
                <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'SELECTED' ?>>Inativo</option>
            </select>
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