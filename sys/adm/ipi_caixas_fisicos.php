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
 * 1.0       02/06/2015   Thiago         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Caixas');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_caixas_fisicos';
$tabela = 'ipi_caixas_fisicos';
$campo_ordenacao = 'numero_caixa';
$campo_filtro_padrao = 'numero_caixa';
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

        $cod_pizzarias  = validaVarPost('cod_pizzarias');
        $numero_caixa  = validaVarPost('numero_caixa');
        $caixa_pedido_online = validaVarPost('caixa_pedido_online');
        $situacao_caixa_fisico  = validaVarPost('situacao_caixa_fisico');

        $conexao = conectabd();


          if ($codigo <= 0)
          {

            $sql_caixa_web ="SELECT cf.numero_caixa FROM $tabela cf WHERE cf.cod_pizzarias='".$cod_pizzarias."' AND caixa_pedido_online='".$caixa_pedido_online."'";
            //echo $sql_caixa_web;
            $res_caixa_web = mysql_query($sql_caixa_web);

            $num_caixa_web = mysql_num_rows($res_caixa_web);

            if ($num_caixa_web == 0)
            {

              $sql_edicao = sprintf("INSERT INTO $tabela (cod_pizzarias, numero_caixa, caixa_pedido_online, situacao_caixa_fisico) VALUES (%d,'%s','%s','%s')", $cod_pizzarias, $numero_caixa, $caixa_pedido_online, $situacao_caixa_fisico);
              $res_edicao = mysql_query($sql_edicao);
              if ($res_edicao)
              {
                  $codigo = mysql_insert_id();
              }

              if ($res_edicao)
              {
                  mensagemok('Registro alterado com êxito!');
              }  
              else
              {
                  mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
              }

            }
            else
            {
              mensagemerro('Erro!', 'Já existe um caixa web!<br />Não é possível cadastrar outro!');
            }

          }
          else
          {

            if ($caixa_pedido_online == "S")
            {
              $sql_caixa_web ="SELECT cf.numero_caixa FROM $tabela cf WHERE cf.cod_pizzarias='".$cod_pizzarias."' AND caixa_pedido_online='".$caixa_pedido_online."' AND cf.cod_caixas_fisicos != '".$codigo."'";
              //echo $sql_caixa_web;
              $res_caixa_web = mysql_query($sql_caixa_web);

              $num_caixa_web = mysql_num_rows($res_caixa_web);
            }
            else
            {
              $num_caixa_web = 0 ; 
            }

            if ($num_caixa_web == 0)
            {

              $sql_edicao = sprintf("UPDATE $tabela SET caixa_pedido_online  = '%s', numero_caixa = '%s', cod_pizzarias = %d, situacao_caixa_fisico = '%s' WHERE $chave_primaria = $codigo", $caixa_pedido_online , $numero_caixa, $cod_pizzarias, $situacao_caixa_fisico);
              $res_edicao = mysql_query($sql_edicao);
              if ($res_edicao)
              {
                  mensagemok('Registro alterado com êxito!');
              }  
              else
              {
                  mensagemerro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
              }

            }
            else
            {
              mensagemerro('Erro!', 'Já existe um caixa web!<br />Não é possível cadastrar outro!');
            }

          }
          //echo "<Br />".$sql_edicao;

        
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
        document.frmIncluir.numero_caixa.value = '';
        document.frmIncluir.caixa_pedido_online.value = '';
        document.frmIncluir.situacao_caixa_fisico.value = '';
      
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
        
        $sql_buscar_registros = "SELECT cf.*,p.nome FROM $tabela cf left join ipi_pizzarias p on p.cod_pizzarias = cf.cod_pizzarias WHERE cf.$opcoes LIKE '%$filtro%' and p.cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ";

        if($cod_pizzarias!="")
        {
          $sql_buscar_registros .= "and p.cod_pizzarias = '".$cod_pizzarias."'";
        }
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY numero_caixa LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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
                    <td align="center"><? echo ucfirst(TIPO_EMPRESA) ?></td>
                    <td align="center">Número do Caixa</td>
                    <td align="center">Pedido Online</td>
                    <td align="center">Situação</td>
                </tr>
            </thead>
            <tbody>
            <?
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
              echo '<tr>';
              echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
              echo '<td align="center">'. bd2texto($obj_buscar_registros->nome).'</td>';
              echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->numero_caixa) . '</a></td>';
              echo '<td align="center">'. bd2texto($obj_buscar_registros->caixa_pedido_online=='S'?'Sim':'Não').'</td>';
              echo '<td align="center">'. bd2texto($obj_buscar_registros->situacao_caixa_fisico).'</td>';
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
        <td class="legenda tdbl tdbt tdbr" colspan="2"><label class="requerido" for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA); ?>s</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr" colspan="2">
          <select name="cod_pizzarias" id="cod_pizzarias">
          <option value=""></option>
            <?
            $con = conectabd();
            
            $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") AND situacao IN ('ATIVO','TESTE') ORDER BY nome";
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
          </select>
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="numero_caixa">Número do Caixa</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr"><input class="requerido" type="text" name="numero_caixa" id="numero_caixa" maxlength="10" size="10" value="<? echo bd2texto($obj_editar->numero_caixa)?>"></td>
    </tr>


    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="caixa_pedido_online">Caixa dos pedidos online?</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="caixa_pedido_online" id="caixa_pedido_online">
            <option value=""></option>
            <option value="S" <? if($obj_editar->caixa_pedido_online == 'S') echo 'selected'; ?>> Sim </option>
            <option value="N" <? if($obj_editar->caixa_pedido_online == 'N') echo 'selected'; ?>> Não </option>
        </select>
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="situacao_caixa_fisico">Situação</label>
        </td>
    </tr>

    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="situacao_caixa_fisico" id="situacao_caixa_fisico">
            <option value=""></option>
            <option value="ATIVO" <? if($obj_editar->situacao_caixa_fisico == 'ATIVO') echo 'selected'; ?>> Ativo </option>
            <option value="INATIVO" <? if($obj_editar->situacao_caixa_fisico == 'INATIVO') echo 'selected'; ?>> Inativo </option>
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