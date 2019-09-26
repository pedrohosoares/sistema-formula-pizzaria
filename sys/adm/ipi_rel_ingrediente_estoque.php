<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * Índice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Relatório de Ingredientes em Estoque');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel1 = "DELETE FROM ipi_ingredientes_ipi_tamanhos WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel2 = "DELETE FROM ipi_ingredientes_ipi_pizzas WHERE $chave_primaria IN ($indicesSql)";
    $SqlDel3 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    
    $resDel1 = mysql_query($SqlDel1);
    $resDel2 = mysql_query($SqlDel2);
    $resDel3 = mysql_query($SqlDel3);
    
    if ($resDel1 && $resDel2 && $resDel3)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':
    $codigo  = validaVarPost($chave_primaria);
    $cod_ingredientes_troca = validaVarPost('cod_ingredientes_troca');
    $ingrediente = validaVarPost('ingrediente');
    $ingrediente_abreviado = validaVarPost('ingrediente_abreviado');
    $tipo = validaVarPost('tipo');
    $tamanho = validaVarPost('tamanho');
    $tamanho_checkbox = validaVarPost('tamanho_checkbox');
    $preco = validaVarPost('preco');
    $preco_troca = validaVarPost('preco_troca');
    $quantidade_estoque_extra = validaVarPost('quantidade_estoque_extra');
    
    $adicional = (validaVarPost('adicional') == 'on') ? 1 : 0;
    $consumo = (validaVarPost('consumo') == 'on') ? 1 : 0;
    $ativo = (validaVarPost('ativo') == 'on') ? 1 : 0;
    $destaque = (validaVarPost('destaque') == 'on') ? 1 : 0;
    
    $quantidade_minima = validaVarPost('quantidade_minima');
    $quantidade_maxima = validaVarPost('quantidade_maxima');
    $quantidade_perda = validaVarPost('quantidade_perda');
    
    $con = conectabd();
    
    if($codigo <= 0) 
    {
      $SqlEdicao = sprintf("INSERT INTO $tabela (cod_ingredientes_troca, ingrediente, ingrediente_abreviado, tipo, adicional, consumo, ativo, destaque, quantidade_minima, quantidade_maxima, quantidade_perda) VALUES (%d, '%s', '%s', '%s', %d, %d, %d, %d, %d, %d, %d)", 
                           $cod_ingredientes_troca, $ingrediente, $ingrediente_abreviado, $tipo, $adicional, $consumo, $ativo, $destaque, $quantidade_minima, $quantidade_maxima, $quantidade_perda);

      if(mysql_query($SqlEdicao)) 
      {
        $codigo = mysql_insert_id();
        
        $resEdicaoTamanhoIngrediente = true;
        
        if(is_array($tamanho_checkbox)) {
          for($t = 0; $t < count($tamanho); $t++) {
            if(in_array($tamanho[$t], $tamanho_checkbox)) {
              $cor_preco = ($preco[$t] > 0) ? moeda2bd($preco[$t]) : 0;
              if ($cod_ingredientes_troca>0)
              {
                $cor_preco_troca = ($preco_troca[$t] > 0) ? moeda2bd($preco_troca[$t]) : 0;
              }
              else
              {
                  $cor_preco_troca = 0;
              }
              $cor_quantidade_estoque_extra = ($quantidade_estoque_extra[$t] > 0) ? $quantidade_estoque_extra[$t] : 0;
              
              $SqlEdicaoTamanhoIngrediente = sprintf("INSERT INTO ipi_ingredientes_ipi_tamanhos (cod_ingredientes, cod_tamanhos, preco, preco_troca, quantidade_estoque_extra) VALUES (%d, %d, %s, %s, %s)", 
                                                     $codigo, $tamanho[$t], $cor_preco, $cor_preco_troca, $cor_quantidade_estoque_extra);
                                                     
              $resEdicaoTamanhoIngrediente &= mysql_query($SqlEdicaoTamanhoIngrediente);
            }
          }
        }
        
        if($resEdicaoTamanhoIngrediente) {
          mensagemOk('Registro adicionado com êxito!');
        }
        else {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        
      }
      else {
        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
    }
    else {
      $SqlEdicao = sprintf("UPDATE $tabela SET cod_ingredientes_troca = '%s', ingrediente = '%s', ingrediente_abreviado = '%s', tipo = '%s', adicional = %d, consumo = %d, ativo = %d, destaque = %d, quantidade_minima = %d, quantidade_maxima = %d, quantidade_perda = %d WHERE $chave_primaria = $codigo", 
                           $cod_ingredientes_troca, $ingrediente, $ingrediente_abreviado, $tipo, $adicional, $consumo, $ativo, $destaque, $quantidade_minima, $quantidade_maxima, $quantidade_perda);
                           
       if(mysql_query($SqlEdicao)) {
        $resEdicaoTamanhoIngrediente = true;
        
        if(is_array($tamanho)) {
          $SqlDelTamanhoIngrediente = "DELETE FROM ipi_ingredientes_ipi_tamanhos WHERE $chave_primaria = $codigo";
          $resDelTamanhoIngrediente = mysql_query($SqlDelTamanhoIngrediente);
        }
        else {
          $resDelTamanhoIngrediente = true;
        }
        
        if($resDelTamanhoIngrediente) {
          if(is_array($tamanho_checkbox)) {
            for($t = 0; $t < count($tamanho); $t++) {
              if(in_array($tamanho[$t], $tamanho_checkbox)) {
                $cor_preco = ($preco[$t] > 0) ? moeda2bd($preco[$t]) : 0;
                  if ($cod_ingredientes_troca > 0)
                  {
                    $cor_preco_troca = ($preco_troca[$t] > 0) ? moeda2bd($preco_troca[$t]) : 0;
                  }
                  else
                  {
                      $cor_preco_troca = 0;
                  }
                  
                  
                $cor_quantidade_estoque_extra = ($quantidade_estoque_extra[$t] > 0) ? $quantidade_estoque_extra[$t] : 0;
                
                $SqlEdicaoTamanhoIngrediente = sprintf("INSERT INTO ipi_ingredientes_ipi_tamanhos (cod_ingredientes, cod_tamanhos, preco, preco_troca, quantidade_estoque_extra) VALUES (%d, %d, %s, %s, %s)", 
                                                       $codigo, $tamanho[$t], $cor_preco, $cor_preco_troca, $cor_quantidade_estoque_extra);
               $resEdicaoTamanhoIngrediente &= mysql_query($SqlEdicaoTamanhoIngrediente);
              }
            }
          }
          
          if($resEdicaoTamanhoIngrediente) {
            mensagemOk('Registro alterado com êxito!');
          }
          else {
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
          
          
        }
        else {
          mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
      }
      else {
        mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
    }
    
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

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
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function limpaPreco(cod) 
{
	document.getElementById('preco_' + cod).value = '';
}

window.addEvent('domready', function()
{
	var tabs = new Tabs('tabs'); 
/*
	if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) 
	{
		<? if ($acao == '') echo 'tabs.irpara(1);'; ?>
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
			document.frmIncluir.ingrediente.value = '';
			document.frmIncluir.ingrediente_abreviado.value = '';
			//document.frmIncluir.tipo.value = '';
			document.frmIncluir.quantidade_minima.value = '';
			document.frmIncluir.quantidade_maxima.value = '';
			document.frmIncluir.quantidade_perda.value = '';
			document.frmIncluir.adicional.checked = false;
			document.frmIncluir.ativo.checked = true;

			marcaTodosEstado('marcar_tamanho', false);

			// Limpando todos os campos input para Preço
			var input = document.getElementsByTagName('input');
			for (var i = 0; i < input.length; i++) 
			{
				if((input[i].name.match('preco')) || (input[i].name.match('quantidade_estoque_extra'))) 
				{ 
					input[i].value = ''; 
				}
			}

		document.frmIncluir.botao_submit.value = 'Cadastrar';
		}
	});
*/
});
</script>

<?
$cod_pizzarias = validaVarPost('cod_pizzarias');
?>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a href="javascript:;">Relatório</a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">



  <form name="frmFiltro" method="post">



  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt sep" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
    <td class="tdbt sep">&nbsp;</td>
    <td class="tdbr tdbt sep">
      <select name="cod_pizzarias" id="cod_pizzarias">
        <option value="">Todas as Pizzarias</option>
        <?
		$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

        $con = conectabd();
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        
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

  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3"><input class="botaoAzul" type="submit" value="Buscar"></td></tr>
  
  </table>
  
  <input type="hidden" name="acao" value="buscar">


    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo" align="center">
    
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" align="center">
          <tr>
            <td>
			<!--
			<input class="botaoAzul" type="submit" value="Excluir Selecionados">
			-->
			</td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center">
          <thead>
            <tr>
              <td align="center" width="250">Ingrediente</td>
              <td align="center">Marca</td>
              <td align="center">Unid. Padrão</td>
              <td align="center">Qtde Atual</td>
            </tr>
          </thead>

          <tbody>
			<?
			if (!$cod_pizzarias)
			{
				$cod_pizzarias = $cod_pizzarias_usuario;
			}
			//$SqlBuscaIngredientes = "SELECT i.ingrediente, im.ingrediente_marca, im.quantidade, i.quantidade_minima, (SELECT SUM(e.quantidade) FROM ipi_estoque e INNER JOIN ipi_estoque_entrada_itens eei ON (e.cod_estoque_entrada_itens=eei.cod_estoque_entrada_itens) WHERE e.cod_ingredientes = i.cod_ingredientes AND e.cod_pizzarias IN ($cod_pizzarias) AND eei.cod_ingredientes_marcas=im.cod_ingredientes_marcas ) quantidade_atual FROM $tabela i LEFT JOIN ipi_ingredientes_marcas im ON (i.cod_ingredientes = im.cod_ingredientes)ORDER BY i.ingrediente";

			$SqlBuscaIngredientes = "SELECT i.ingrediente, ip.quantidade_minima, ip.quantidade_maxima, ip.quantidade_perda, (SELECT SUM(e.quantidade) FROM ipi_estoque e WHERE e.cod_ingredientes = i.cod_ingredientes AND e.cod_pizzarias IN ($cod_pizzarias) ) quantidade_atual FROM $tabela i LEFT JOIN ipi_ingredientes_pizzarias ip ON (i.cod_ingredientes = ip.cod_ingredientes AND ip.cod_pizzarias IN ($cod_pizzarias)) ORDER BY i.ingrediente";
			$resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
			//echo "<br>1: ".$SqlBuscaIngredientes;
			while ($objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes)) 
			{
				echo '<tr>';
				echo '<td align="center">'.bd2texto($objBuscaIngredientes->ingrediente).'</td>';
				echo '<td align="center">'.bd2texto($objBuscaIngredientes->ingrediente_marca).'</td>';
				echo '<td align="center">'.bd2texto($objBuscaIngredientes->quantidade).'</td>';
				echo '<td align="center">'.bd2texto($objBuscaIngredientes->quantidade_atual).'</td>';
				echo '</tr>';
			}
			desconectabd($con);
			?>
          </tbody>
        </table>
      
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
	<!--
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="ipi_adicional.php">Adicionais</a></li>
          <li><a href="ipi_borda.php">Bordas</a></li>
          <li><a href="ipi_pizza.php">Pizzas</a></li>
          <li><a href="ipi_tamanho.php">Tamanhos</a></li>
        </ul>
      </div>
    </td>
	-->
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Editar -->
  
  
  
 </div>

<? rodape(); ?>
