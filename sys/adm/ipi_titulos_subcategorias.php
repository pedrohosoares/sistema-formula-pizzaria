<?php

/**
 * Cadastro de Titulos Subcategoria.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       22/01/2010   Elias         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Subcategorias');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_titulos_subcategorias';
$tabela = 'ipi_titulos_subcategorias';
$campo_ordenacao = 'titulos_subcategorias';
$campo_filtro_padrao = 'titulos_subcategorias';
$quant_pagina = 100;
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
            mensagemOk('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $num_parcelas_maximo = validaVarPost('num_parcelas_maximo');
        $tipo_cendente_sacado = validaVarPost('tipo_cendente_sacado');
        $titulos_subcategorias = validaVarPost('titulos_subcategorias');
        $cod_titulos_categorias = validaVarPost('cod_titulos_categorias');
        $tipo_titulo = validaVarPost('tipo_titulo');
        $cod_plano_contas = validaVarPost('cod_plano_contas');

        if($tipo_titulo == 'TRANSFER')
        {
            $num_parcelas_maximo = 1;
        }
        
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (num_parcelas_maximo, tipo_cendente_sacado, titulos_subcategorias, cod_titulos_categorias, tipo_titulo) VALUES ('%s', '%s', '%s', '%s', '%s')", 
                           $num_parcelas_maximo, $tipo_cendente_sacado, $titulos_subcategorias, $cod_titulos_categorias, $tipo_titulo);
            
            if (mysql_query($sql_edicao))
            {
                $codigo = mysql_insert_id();

                $res_edicao_plano_contas = true;

                foreach($cod_plano_contas as $cod_plano_contas_atual)
                {
                    $sql_edicao_plano_contas = "INSERT INTO ipi_titulos_subcategorias_plano_contas (cod_plano_contas, cod_titulos_subcategorias) VALUES ('$cod_plano_contas_atual', '$codigo')";
                    $res_edicao_plano_contas &= mysql_query($sql_edicao_plano_contas);
                }

                if($res_edicao_plano_contas)
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
            $sql_edicao = sprintf("UPDATE $tabela SET num_parcelas_maximo = '%s', tipo_cendente_sacado = '%s' , titulos_subcategorias = '%s' , cod_titulos_categorias = '%s', tipo_titulo = '%s' WHERE $chave_primaria = $codigo", 
                            $num_parcelas_maximo, $tipo_cendente_sacado, $titulos_subcategorias, $cod_titulos_categorias, $tipo_titulo);
                            
            if (mysql_query($sql_edicao))
            {
                $sql_deletar_plano_contas = "DELETE FROM ipi_titulos_subcategorias_plano_contas WHERE cod_titulos_subcategorias = '$codigo'";
                $res_deletar_plano_contas = mysql_query($sql_deletar_plano_contas);

                $res_edicao_plano_contas = true;

                foreach($cod_plano_contas as $cod_plano_contas_atual)
                {
                    $sql_edicao_plano_contas = "INSERT INTO ipi_titulos_subcategorias_plano_contas (cod_plano_contas, cod_titulos_subcategorias) VALUES ('$cod_plano_contas_atual', '$codigo')";
                    $res_edicao_plano_contas &= mysql_query($sql_edicao_plano_contas);
                }

                if($res_edicao_plano_contas)
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
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        
        desconectabd($conexao);
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen"
    href="../lib/css/tabs_simples.css" />

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

function selecionar_tipo_titulo(valor)
{
	if(valor == 'TRANSFER')
	{
    	$('tipo_cendente_sacado_td_label').setStyle('display', 'none');
    	$('tipo_cendente_sacado_td_val').setStyle('display', 'none');
    	$('tipo_cendente_sacado_label').removeClass('requerido');
    	$('tipo_cendente_sacado').removeClass('requerido');
    	
    	$('num_parcelas_maximo_td_label').setStyle('display', 'none');
    	$('num_parcelas_maximo_td_val').setStyle('display', 'none');
    	$('num_parcelas_maximo_label').removeClass('requerido');
    	$('num_parcelas_maximo').removeClass('requerido');
	}
	else
	{
    	$('tipo_cendente_sacado_td_label').setStyle('display', 'block');
    	$('tipo_cendente_sacado_td_val').setStyle('display', 'block');
    	$('tipo_cendente_sacado_label').addClass('requerido');
    	$('tipo_cendente_sacado').addClass('requerido');
    	
    	$('num_parcelas_maximo_td_label').setStyle('display', 'block');
    	$('num_parcelas_maximo_td_val').setStyle('display', 'block');
    	$('num_parcelas_maximo_label').addClass('requerido');
    	$('num_parcelas_maximo').addClass('requerido');
	}
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
    
    	selecionar_tipo_titulo(document.frmIncluir.tipo_titulo.value);
    
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
            document.frmIncluir.num_parcelas_maximo.value = '';
            document.frmIncluir.tipo_cendente_sacado.value = '';
            document.frmIncluir.titulos_subcategorias.value = '';
      		document.frmIncluir.cod_titulos_categorias.value = '';
      		document.frmIncluir.tipo_titulo.value = '';
      
            document.frmIncluir.botao_submit.value = 'Cadastrar';
            
            selecionar_tipo_titulo('TRANSFER');
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
                        ?>>Subcategoria</option>
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
        
        $sql_buscar_registros = "SELECT * FROM $tabela s INNER JOIN ipi_titulos_categorias c ON (s.cod_titulos_categorias = c.cod_titulos_categorias) WHERE $opcoes LIKE '%$filtro%' ";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY c.titulos_categoria, s.titulos_subcategorias LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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

        <form name="frmExcluir" method="post"
            onsubmit="return verificar_checkbox(this)">

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<? echo LARGURA_PADRAO ?>">
            <tr>
                <td><input class="botaoAzul" type="submit"
                    value="Excluir Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<? echo LARGURA_PADRAO ?>">
            <thead>
                <tr>
                    <td align="center" width="20"><input type="checkbox"
                        onclick="marcaTodos('marcar');"></td>
                    
                    <td align="center" width="200">Categoria</td>
                    <td align="center">Subcategoria</td>
                    <td align="center" width="200">Conta Associada</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';

                echo '<td align="center">' . bd2texto($obj_buscar_registros->titulos_categoria) . '</td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->titulos_subcategorias) . '</a></td>';
                
                echo '<td align="center">';
                $sql_buscar_conta_associada = "SELECT * FROM ipi_titulos_subcategorias_plano_contas sp INNER JOIN ipi_plano_contas pc ON (sp.cod_plano_contas = pc.cod_plano_contas) WHERE sp.cod_titulos_subcategorias = '$obj_buscar_registros->cod_titulos_subcategorias' ORDER BY conta_indice";
                $res_buscar_conta_associada = mysql_query($sql_buscar_conta_associada);
                
                while($obj_buscar_conta_associada = mysql_fetch_object($res_buscar_conta_associada))
                {
                    echo bd2texto($obj_buscar_conta_associada->conta_indice) . '<br/>';
                }
                
                echo '</td>';

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

    $arr_plano_contas_associados = array();

    if ($codigo > 0)
    {
        $conexao = conectabd();
        
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo", $conexao);

        $sql_buscar_plano_contas_associados = "SELECT * FROM ipi_titulos_subcategorias_plano_contas WHERE cod_titulos_subcategorias = '$codigo' ORDER BY cod_titulos_subcategorias_plano_contas";
        $res_buscar_plano_contas_associados = mysql_query($sql_buscar_plano_contas_associados);
        
        while($obj_buscar_plano_contas_associados = mysql_fetch_object($res_buscar_plano_contas_associados))
        {
            $arr_plano_contas_associados[] = $obj_buscar_plano_contas_associados->cod_plano_contas;
        }

        desconectabd($conexao);
    }
    else
    {
       $arr_plano_contas_associados = array(0);
    }
    
    function listar_planos_pai($cod, $cod_plano_contas, $espaco, $nome='')
    {
		$sql_buscar_plano_contas = "SELECT * FROM ger_plano_contas WHERE cod_plano_contas_pai = $cod ORDER BY plano_contas";
		$res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
		$num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);
            
        if (($num_buscar_plano_contas > 0) && ($cod > 0))
        {
			$espaco += 25;
        }
            
        while ($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
		{
			$sql_buscar_quant_filhos = "SELECT COUNT(*) AS quatidade FROM ger_plano_contas WHERE cod_plano_contas_pai = '" . $obj_buscar_plano_contas->cod_plano_contas . "'";
			$res_buscar_quant_filhos = mysql_query($sql_buscar_quant_filhos);
			$obj_buscar_quant_filhos = mysql_fetch_object($res_buscar_quant_filhos);
            	
			echo '<tr>';
            	
			echo '<td align="center"><input type="radio" name="'.$nome.'" ';
			
			if ($obj_buscar_plano_contas->cod_plano_contas == $cod)
			{
				echo 'checked="checked"';	
			}
			
			echo ' value="' . $obj_buscar_plano_contas->cod_plano_contas . '" ></td>';
            	
			echo '<td style="padding-left: ' . $espaco . 'px; color: ' . $cor_fundo . '" align="left">' . bd2texto($obj_buscar_plano_contas->plano_contas) . '</td>';
			echo '</tr>';
                
			listar_planos_pai($obj_buscar_plano_contas->cod_plano_contas, $cod_plano_contas, $espaco, $nome);
		}
    }
    
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

	<tr>
    	<td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_titulos_categorias">Categoria</label></td>
    </tr>
    <tr>
    	<td class="sep tdbl tdbr">
        	<select name="cod_titulos_categorias" class="requerido">
            	<option value=""></option>
            	
                <?
                $conexao = conectabd();
                       
                $sql_buscar_categoria = "SELECT * FROM ipi_titulos_categorias ORDER BY titulos_categoria";
                $res_buscar_categoria = mysql_query($sql_buscar_categoria);
                        
                while ($obj_buscar_categoria = mysql_fetch_object($res_buscar_categoria))
                {
                  	echo '<option value="' . $obj_buscar_categoria->cod_titulos_categorias . '" ';
                            
                	if ($obj_buscar_categoria->cod_titulos_categorias == $obj_editar->cod_titulos_categorias)
                    {
                    	echo 'selected';
                    }
                            
                    echo '>' . $obj_buscar_categoria->titulos_categoria . '</option>';
                }
                        
                desconectabd($conexao);
		        ?>
		        
    		</select>
    	</td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="titulos_subcategorias">Subcategoria</label>
        </td>
    </tr>
    <tr>
    	<td class="sep tdbl tdbr">
			<input class="requerido" type="text" name="titulos_subcategorias" id="titulos_subcategorias" maxlength="150" style="width: 180px;" value="<?  echo bd2texto($obj_editar->titulos_subcategorias)?>">
		</td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="tipo_titulo">Tipo do Título</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select name="tipo_titulo" class="requerido" style="width: 188px;" onchange="selecionar_tipo_titulo(this.value);">
            	<option value=""></option>
             	<option value="PAGAR" <? if($obj_editar->tipo_titulo == 'PAGAR') echo 'SELECTED' ?>>Pagar</option>
             	<option value="RECEBER" <? if($obj_editar->tipo_titulo == 'RECEBER') echo 'SELECTED' ?>>Receber</option>
             	<option value="TRANSFER" <? if($obj_editar->tipo_titulo == 'TRANSFER') echo 'SELECTED' ?>>Transferência Bancária</option>
    		</select>
        </td>
    </tr>
    
    <tr>
    	<td class="legenda tdbl tdbr" id="tipo_cendente_sacado_td_label" style="display: none;"><label for="tipo_cendente_sacado" id="tipo_cendente_sacado_label">Tipo de Cedente/Sacado</label></td>
    </tr>
    <tr>
    	<td class="sep tdbl tdbr" id="tipo_cendente_sacado_td_val" style="display: none;">
        	<select name="tipo_cendente_sacado" id="tipo_cendente_sacado" style="width: 188px;">
            	<option value=""></option>
             	<option value="COLABORADOR" <? if($obj_editar->tipo_cendente_sacado == 'COLABORADOR') echo 'SELECTED' ?>>Colaborador</option>
             	<option value="CLIENTE" <? if($obj_editar->tipo_cendente_sacado == 'CLIENTE') echo 'SELECTED' ?>>Cliente</option>
             	<option value="ENTREGADOR" <? if($obj_editar->tipo_cendente_sacado == 'ENTREGADOR') echo 'SELECTED' ?>>Entregador</option>
             	<option value="FORNECEDOR" <? if($obj_editar->tipo_cendente_sacado == 'FORNECEDOR') echo 'SELECTED' ?>>Fornecedor</option>
    		</select>
    	</td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr" id="num_parcelas_maximo_td_label" style="display: none;">
            <label for="num_parcelas_maximo" id="num_parcelas_maximo_label">Núm. Máximo de Parcelas</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep" id="num_parcelas_maximo_td_val" style="display: none;">
            <input type="text" name="num_parcelas_maximo" id="num_parcelas_maximo" maxlength="50" style="width: 180px;" value="<? echo bd2texto($obj_editar->num_parcelas_maximo) ?>" onkeypress="return ApenasNumero(event)">
        </td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr">
            <label for="cod_plano_contas[]" id="cod_plano_contas_1">Conta Associada</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select name="cod_plano_contas[]" id="cod_plano_contas_1" size="10" style="height: 250px; width: 400px; padding: 3px;">
                <?
                function imprimir_combo_plano_contas($cod_plano_contas, $espaco, $cod_plano_contas_atual)
                {
                    $sql_buscar_plano_contas = "SELECT * FROM ipi_plano_contas WHERE cod_plano_contas_pai = '$cod_plano_contas' ORDER BY conta_indice";
                    $res_buscar_plano_contas = mysql_query($sql_buscar_plano_contas);
                    $num_buscar_plano_contas = mysql_num_rows($res_buscar_plano_contas);

                    if(($num_buscar_plano_contas > 0) && ($cod_plano_contas > 0))
                    {
                        $espaco += 15;
                    }
 
                    while($obj_buscar_plano_contas = mysql_fetch_object($res_buscar_plano_contas))
                    {
                        $sql_buscar_filhos_conta = "SELECT * FROM ipi_plano_contas WHERE cod_plano_contas_pai = '$obj_buscar_plano_contas->cod_plano_contas'";
                        $res_buscar_filhos_conta = mysql_query($sql_buscar_filhos_conta);
                        $num_buscar_filhos_conta = mysql_num_rows($res_buscar_filhos_conta);

                        if($num_buscar_filhos_conta > 0)
                        {
                           echo '<optgroup label="' . bd2texto($obj_buscar_plano_contas->conta_indice . ' ' . $obj_buscar_plano_contas->conta_nome) . '" style="padding-left: ' . $espaco . 'px;">';
                        }
                        else
                        {
                            echo '<option value="' . $obj_buscar_plano_contas->cod_plano_contas . '" style="padding-left: ' . $espaco . 'px;"';

                            if($obj_buscar_plano_contas->cod_plano_contas == $cod_plano_contas_atual)
                            {
                                echo ' selected="selected"';
                            }
                            
                            echo '>' . bd2texto($obj_buscar_plano_contas->conta_indice . ' ' . $obj_buscar_plano_contas->conta_nome) . '</option>';
                        }

                        imprimir_combo_plano_contas($obj_buscar_plano_contas->cod_plano_contas, $espaco, $cod_plano_contas_atual);

                        if($num_buscar_filhos_conta > 0)
                        {
                           echo '</optgroup>';
                        }
                    }
                }

                $conexao = conectabd();
                imprimir_combo_plano_contas(0, 0, $arr_plano_contas_associados[0]);
                desconectabd($conexao);
                ?>
            </select>
        </td>
    </tr>
    
    <tr>
        <td align="center" class="tdbl tdbb tdbr">
            <input name="botao_submit" class="botao" type="submit" value="Cadastrar">
        </td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> <input type="hidden"
    name="<?
    echo $chave_primaria?>" value="<?
    echo $codigo?>"></form>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>
