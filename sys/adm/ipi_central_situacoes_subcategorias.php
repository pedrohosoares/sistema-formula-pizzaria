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
 * 1.0       10/09/2012   Filipe         Criado.
 *
 */


require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro Situações da Central');

$acao = validaVarPost('acao');

$tabela = 'ipi_comunicacao_subcategorias_situacoes';
$chave_primaria ='cod_ticket_subcategorias' ;
$campo_ordenacao = 'nome_situacao';
$campo_filtro_padrao = 'nome_situacao';
$quant_pagina = 50;
$exibir_barra_lateral = true;

switch ($acao)
{
    case 'editar':
        $codigo = validaVarPost($chave_primaria);

        $cod_subcategoria = validaVarPost('cod_sub');
        $cods = validaVarPost('ligacao');// Proxima Situação x Situação Atual
        $conexao = conectabd();

        $res_edicao = true;

        $sql_dropar = "DELETE from $tabela where cod_ticket_subcategorias = '$cod_subcategoria'";
        $res_edicao &= mysql_query($sql_dropar);
        /*echo "<pre>";
        print_r($_POST);
        echo "</pre>";*/
        if($res_edicao)
        {
          for($c=0;$c<count($cods);$c++)
          {
            $cods_array = explode('##',$cods[$c]);
            $codigo_atual = $cods_array[1];
            $codigo_proxima = $cods_array[0];
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_ticket_subcategorias,cod_situacoes_origem,cod_situacoes_fim) VALUES (%d,%d,%d)", $cod_subcategoria,$codigo_atual,$codigo_proxima);
            $res_edicao &= mysql_query($sql_edicao);
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

</script>
<div >
    <?
    $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
    $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
    $subcategoria = validaVarPost('sel_subcategorias');
    
    if($subcategoria=="")
    {
      $subcategoria = validaVarPost('cod_sub');
    }
    ?>
    
    <form name="frmFiltro" method="post">
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">

        <tr>
            <td class="legenda tdbl tdbt" align="right">
            	<label for="sel_subcategorias" name="lbl_subcategorias">Subcategorias</label>
            </td>
            <td class="tdbt">&nbsp;</td>
            <td class="tdbt tdbr">
          <select name="cod_sub" >
            <?
                      $conexao = conectabd();
              $sql_busca_categoria_pai = "SELECT cc.cod_categorias,cc.nome_categoria from ipi_comunicacao_categorias cc where cc.status='ATIVO' ";//,count(select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias=cc.cod_categorias) as qtd_filha and qtd_filha>0
              $res_busca_categoria_pai = mysql_query($sql_busca_categoria_pai);
              while($obj_busca_categoria_pai = mysql_fetch_object($res_busca_categoria_pai))
              {
                echo "<optgroup label='".$obj_busca_categoria_pai->nome_categoria."'>";
                $sql_busca_categorias = "select* from ipi_comunicacao_subcategorias where situacao='ATIVO' and cod_categorias='".$obj_busca_categoria_pai->cod_categorias."'";
                $res_busca_categorias = mysql_query($sql_busca_categorias);
                while($obj_busca_categorias = mysql_fetch_object($res_busca_categorias))
                {
                  echo "<option value=".$obj_busca_categorias->cod_ticket_subcategorias."".($subcategoria == $obj_busca_categorias->cod_ticket_subcategorias? " selected " : "" ).">".$obj_busca_categorias->nome_subcategoria."</option>";
                }
                echo "</optgroup>";
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
    //echo "AAAA-".$subcategoria."-AAA";

    if($subcategoria!="")
    {

      $sql_buscar_vinculo = "SELECT * FROM ipi_comunicacao_subcategorias_situacoes WHERE cod_ticket_subcategorias='$subcategoria'";
      $res_buscar_vinculo = mysql_query($sql_buscar_vinculo);
      while($obj_buscar_vinculo = mysql_fetch_object($res_buscar_vinculo))
      {
        $vinculos[] = $obj_buscar_vinculo->cod_situacoes_fim."##".$obj_buscar_vinculo->cod_situacoes_origem;
      }
      $sql_buscar_registros = "SELECT * FROM ipi_comunicacao_situacoes WHERE situacao='ATIVO' ORDER BY nome_situacao";
      
      $res_buscar_registros = mysql_query($sql_buscar_registros);
      //echo $sql_buscar_registros."<br/>";
      
      //echo $sql_buscar_registros;
      while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
      {
        $situacoes[] = bd2texto($obj_buscar_registros->nome_situacao);
        $cod_situacoes[] = $obj_buscar_registros->cod_situacoes;
      }
    }
    
    ?>

    <br>

    <form name="frmEditar" method="post" >
    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
        width="<?
        echo LARGURA_PADRAO?>">
        <tr>
            <td></td>
        </tr>
    </table>

    <table class="listaEdicao" cellpadding="0" cellspacing="0"
      width="<?
      echo LARGURA_PADRAO?>">

      <thead>
        <tr><td  colspan="150" align="center"><b>Situação Atual</b></td></tr>
      </thead>
      <tbody>
      <?
      /*    <pre>
     <? print_r($vinculos) ?>
    </pre>*/
      $colunas = 0;
      $largura = ceil(100/(count($situacoes)+1));
      echo '<tr>';
      echo '<td align="center" width="'.$largura.'%" style="background-color:#E5E5E5" ><strong>Proxima situacao</strong></td>';
      for($a = 0;$a<count($situacoes);$a++)
      {
        echo '<td width="'.$largura.'%" align="center">' . $situacoes[$a] . '</td>';
        $colunas++;
      }
      echo '</tr>';

      for($a = 0;$a<count($situacoes);$a++)
      {
        $cod_situ_linha = $cod_situacoes[$a];
        echo '<tr>';
        echo '<td width="'.$largura.'%" align="center">' . $situacoes[$a] . '</td>';
        for($i= 0;$i<$colunas;$i++)
        {
          $cod_situ_coluna = $cod_situacoes[$i];
          $valor =  $cod_situ_linha."##".$cod_situ_coluna;// Proxima Situação x Situação Atual
          $check = '';
          if(is_array($vinculos))
          {
            if(in_array($valor, $vinculos))
            {
              $check = " checked='checked' ";
            }
          }
          echo "<td align='center'><input name='ligacao[]' $check value='$valor' type='checkbox'/></td>";
        }
        echo '</tr>';
      }

      if($subcategoria)
        echo '<tr><td colspan="'.($colunas+1).'" align="center"><input class="botao" type="submit" name="btn_enviar" id="btn_enviar"  value="Alterar" /></td></tr>';

      desconectabd($conexao);
      ?>
      </tbody>
  </table>
  <input type="hidden" name="cod_sub" value="<? echo $subcategoria ; ?>"/>
  <input type="hidden" name="acao" value="editar"/>
  </form>

</div>

<?
rodape();
?>