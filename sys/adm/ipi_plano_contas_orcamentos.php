<?php

/*
 * Cadastro de Orçamento de Plano de Contas.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       29/06/2011   Thiago         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Orçamento de Plano de Contas');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_plano_contas_orcamento';
$tabela = 'ipi_plano_contas_orcamento';
$campo_ordenacao = 'ano_exercicio, nome_plano_contas';
$campo_filtro_padrao = 'nome_plano_contas';
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
            mensagemOK('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);


        $valor = validaVarPost('valor');
        $cod_plano_contas = validaVarPost('cod_plano_contas');
        $nome_plano_contas = validaVarPost('nome_plano_contas');
        $ano_exercicio = validaVarPost('ano_exercicio');
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $comentario = validaVarPost('comentario');
        $cod_plano_contas_orcamento_pai = validaVarPost('cod_plano_contas_orcamento_pai');
        $tipo_orcamento = 'PRIMEIRO';
        $cont_contas = count($cod_plano_contas);

        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO ipi_plano_contas_orcamento (cod_plano_contas_orcamento_pai, cod_pizzarias, data_hora_cadastro, ano_exercicio, tipo_orcamento, numero_revisao, nome_plano_contas, comentario) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $cod_plano_contas_orcamento_pai, $cod_pizzarias, date("Y-m-d H:i:s"), $ano_exercicio, $tipo_orcamento, $numero_revisao, $nome_plano_contas, $comentario);
            
            if (mysql_query($sql_edicao))
            {
                $codigo = mysql_insert_id();
                mensagemOK('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_plano_contas_orcamento_pai = '%s', cod_pizzarias = '%s', ano_exercicio = '%s', tipo_orcamento = '%s', numero_revisao = '%s', nome_plano_contas = '%s', comentario = '%s' WHERE $chave_primaria = $codigo", $cod_plano_contas_orcamento_pai, $cod_pizzarias, $ano_exercicio, $tipo_orcamento, $numero_revisao, $nome_plano_contas, $comentario);
            
            if (mysql_query($sql_edicao))
            {
                mensagemOK('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        
        //echo "<br/>sql_edicao: ".$sql_edicao;
        if ($cod_plano_contas)
        {
          $sql_delete = "DELETE FROM ipi_plano_contas_lancamentos WHERE cod_plano_contas_orcamento = '".$codigo."'";
          $res_delete = mysql_query($sql_delete);

          for ($a=0; $a<$cont_contas; $a++)
          {
            $sql_contas = sprintf("INSERT INTO ipi_plano_contas_lancamentos (cod_plano_contas_orcamento, cod_plano_contas, valor) VALUES('%s', '%s', '%s')", $codigo, $cod_plano_contas[$a], moeda2bd($valor[$a]));
            $res_contas = mysql_query($sql_contas);
            //echo "<br/>sql_contas: ".$sql_contas;
          }
        }

        desconectabd($conexao);
        break;
}

?>
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

<script>
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
            document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      		  document.frmIncluir.nome_setor.value = '';
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
                <td class="legenda tdbl tdbt" align="right"><select
                    name="opcoes">
                    <option value="<? echo $campo_filtro_padrao ?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Nome Plano Contas</option>
                </select></td>
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
        
        $sql_buscar_registros = "SELECT pco.*, p.nome FROM $tabela pco INNER JOIN ipi_pizzarias p ON (pco.cod_pizzarias = p.cod_pizzarias) WHERE pco.$opcoes LIKE '%$filtro%' ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY pco.' . $campo_ordenacao . ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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

        <form name="frmExcluir" method="post" onsubmit="return verificar_checkbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="<? echo LARGURA_PADRAO ?>">
            <tr>
                <td>
                    <input class="botaoAzul" type="submit" value="Excluir Selecionados">
                </td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
                    <td align="center">Nome Orçamento Contas</td>
                    <td align="center">Pizzaria</td>
                    <td align="center">Ano Exercicio</td>
                    <td align="center">Revisão</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->nome_plano_contas) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->nome) . '</td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->ano_exercicio) . '</td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->numero_revisao) . '</td>';
                echo '</tr>';
            }
            
            desconectabd($conexao);
            
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
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }
    ?>
    
  <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">

  <table align="center" class="caixa" cellpadding="0" cellspacing="0">


    <tr>
        <td class="legenda tdbl tdbt tdbt" align="right">
            <label for="cod_pizzarias" class="requerido">Pizzaria:</label>
        </td>
        <td>&nbsp;</td>
        <td class="tdbr tdbt">

          <select name="cod_pizzarias" class="requerido" style="width: 150px;">
            <option value="TODOS">Todas Pizzarias</option>
            <?
            $con = conectabd();
            $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") ORDER BY nome";
            $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
            while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
            {
              echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
              if ($obj_editar->cod_pizzarias == $objBuscaPizzarias->cod_pizzarias)
                 echo 'selected';
              echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
            }
            desconectabd($con);
            ?>
          </select>

        </td>
    </tr>


    <tr>
        <td class="legenda tdbl" align="right">
            <label for="nome_plano_contas" class="requerido">Nome do Orçamento:</label>
        </td>
        <td>&nbsp;</td>
        <td class="tdbr">
            <input type="text"  name="nome_plano_contas" class="requerido" id="nome_plano_contas" maxlength="30" size="30" value="<? echo bd2texto($obj_editar->nome_plano_contas); ?>">
        </td>
    </tr>


    <tr>
        <td class="legenda tdbl" align="right"><label for="ano_exercicio" class="requerido">Ano Exercicio:</label></td>
        <td>&nbsp;</td>
        <td class="tdbr">
          <select name="ano_exercicio" id="ano_exercicio" style="width: 80px;" class="requerido">
          <option value=""></option>
          <?
          $con = conectabd();
          $sql_buscar_ano_min = "SELECT MIN(YEAR(data_hora_pedido)) AS ano_min FROM ipi_pedidos WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
          $res_buscar_ano_min = mysql_query($sql_buscar_ano_min);
          $obj_buscar_ano_min = mysql_fetch_object($res_buscar_ano_min);
          
          $sql_buscar_ano_max = "SELECT MAX(YEAR(data_hora_pedido)) AS ano_max FROM ipi_pedidos WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")";
          $res_buscar_ano_max = mysql_query($sql_buscar_ano_max);
          $obj_buscar_ano_max = mysql_fetch_object($res_buscar_ano_max);
		      if (($obj_buscar_ano_max->ano_max!="")&&($obj_buscar_ano_min->ano_min))
		      {
	          for ($a = $obj_buscar_ano_max->ano_max; $a >= $obj_buscar_ano_min->ano_min; $a--)
	          {
              echo '<option value="' . $a . '"';
              if ($a == $obj_editar->ano_exercicio)
                  echo ' selected="selected"';
              echo '>' . $a . '</option>';
	          }
		      }
		      else
		      {
            echo '<option value="' . date("Y") . '"';
              if (date("Y") == $obj_editar->ano_exercicio)
                  echo ' selected="selected"';
            echo '>' . date("Y") . '</option>';
		      }
          desconectabd($con);
          ?>
          </select>
        </td>
    </tr>


    <tr>
        <td class="tdbl tdbr" align="left" colspan="3">
            <label for="nome_plano_contas" class="requerido">Comentários:</label><br />
            <textarea rows="5" cols="62" name="comentario"><? echo bd2texto($obj_editar->comentario); ?></textarea>
        </td>
    </tr>

    <tr>
      <td align="center" class="tdbl tdbr" colspan="3">
        &nbsp;
      </td>
    </tr>

    <tr>
      <td align="center" class="tdbl tdbr" colspan="3">

        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="<? echo LARGURA_PADRAO ?>">
            <thead>
                <tr>
                    <td align="center">Conta</td>
                    <td align="center" width="100">Valor (R$)</td>
                </tr>
            </thead>
            <tbody>
            <?
            function imprimir_plano_contas($cod_plano_contas, $espaco, $cod_plano_contas_orcamento)
            {
                $sql_buscar_plano_contas = "SELECT pc.*, (SELECT pcl.valor FROM ipi_plano_contas_lancamentos pcl WHERE pcl.cod_plano_contas_orcamento = '$cod_plano_contas_orcamento' AND pcl.cod_plano_contas = pc.cod_plano_contas) valor FROM ipi_plano_contas pc WHERE pc.cod_plano_contas_pai = '$cod_plano_contas' ORDER BY pc.conta_indice";
                $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
                $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);
                //echo "<br>X: ".$sql_buscar_plano_contas;
                if(($num_buscar_plano_contas > 0) && ($cod_plano_contas > 0))
                {
                    $espaco += 25;
                }

                while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
                {
                    echo '<tr>';
                    echo '<td align="left" style="padding-left: ' . $espaco . 'px;">' . bd2texto($obj_buscar_plano_contas->conta_indice) . ' ' . bd2texto($obj_buscar_plano_contas->conta_nome) . '</td>';
                    echo '<td align="center">';
                    echo '<input type="text" name="valor[]" size="8" value="'.bd2moeda($obj_buscar_plano_contas->valor).'" onclick="javascript: if(this.value==\'0\'){this.value=\'\'; }" onblur="javascript: if(this.value==\'\'){this.value=\'0\';}" />';
                    echo '<input type="hidden" name="cod_plano_contas[]" value="'.$obj_buscar_plano_contas->cod_plano_contas.'" />';
                    echo '</td>';
                    echo '</tr>';

                    imprimir_plano_contas($obj_buscar_plano_contas->cod_plano_contas, $espaco, $cod_plano_contas_orcamento);
                }
            }
            
            $conexao = conectabd();
            imprimir_plano_contas(0, 3, $codigo);
            desconectabd($conexao);
            ?>
            </tbody>
        </table>

      </td>
    </tr>

    <tr>
      <td align="center" class="tdbl tdbr" colspan="3">
        &nbsp;
      </td>
    </tr>



    <tr>
      <td align="center" class="tdbl tdbr" colspan="3">
        Nome Revisão: <input type="text" name="nome_revisao" size="30" value="" />
        <input name="botao_revisao" class="botao" type="button" value="Criar Revisão">
      </td>
    </tr>

    <tr>
      <td align="center" class="tdbl tdbr" colspan="3">
        &nbsp;
      </td>
    </tr>



    <tr>
      <td align="center" class="tdbl tdbb tdbr" colspan="3">
        <input name="botao_submit" class="botao" type="submit" value="Cadastrar">
      </td>
    </tr>

  </table>

  <input type="hidden" name="cod_plano_contas_orcamento_pai" value="<? echo $codigo; ?>">
  <input type="hidden" name="acao" value="editar">
  <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo; ?>">

</form>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>
