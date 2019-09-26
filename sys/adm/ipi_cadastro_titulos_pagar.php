<?php

/**
 * Cadastro Títulos a pagar.
 *
 * @version 1.0
 * @package gerencial
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/12/2009   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

exibir_cabecalho('Cadastro de Contas a Pagar');

$acao = validar_var_post('acao');

$chave_primaria = 'cod_titulos';
$tabela = 'ger_titulos';
$campo_ordenacao = 'cod_titulos';
$campo_filtro_padrao = 'descricao';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validar_var_post('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectar_bd();
        
        $sql_del_1 = "DELETE FROM ger_titulos_parcelas WHERE $chave_primaria IN ($indices_sql)";
        $sql_del_2 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        
        if (mysql_query($sql_del_1) && mysql_query($sql_del_2))
        {
            exibir_mensagem_ok('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            exibir_mensagem_erro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectar_bd($conexao);
        break;
    case 'editar':
        $codigo = validar_var_post($chave_primaria);
        
        $cod_titulos_subcategorias = validar_var_post('cod_titulos_subcategorias');
        $cod_colaboradores = validar_var_post('cod_colaboradores');
        $cod_fornecedores = validar_var_post('cod_fornecedores');
        $descricao = validar_var_post('descricao');
        $num_parcelas = validar_var_post('num_parcelas');
        $cod_titulos_parcelas = validar_var_post('cod_titulos_parcelas');
        
        $conexao = conectar_bd();
        
        // Retornando o plano de contas pela subcategoria
        $obj_buscar_titulos_subcategorias = executar_busca_simples("SELECT * FROM ger_titulos_subcategorias WHERE cod_titulos_subcategorias = '$cod_titulos_subcategorias'", $conexao);
        
        $tipo_cedente_sacado = $obj_buscar_titulos_subcategorias->tipo_cendente_sacado;
        
        $vencimento = validar_var_post('vencimento');
        $valor = validar_var_post('valor');
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_titulos_subcategorias, cod_colaboradores, cod_fornecedores, total_parcelas, tipo_titulo, tipo_cedente_sacado, descricao) VALUES ('%s', '%s', '%s', '%s', 'PAGAR', '%s', '%s')", 
                                $cod_titulos_subcategorias, $cod_colaboradores, $cod_fornecedores, $num_parcelas, $tipo_cedente_sacado, $descricao);

            if(mysql_query($sql_edicao))
            {
                $codigo = mysql_insert_id();
                
                if($obj_buscar_titulos_subcategorias->num_parcelas_maximo == 1)
                {
                    $sql_edicao_parcelas = sprintf("INSERT INTO ger_titulos_parcelas (cod_titulos, data_vencimento, valor, juros, valor_total, numero_parcela, situacao) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                                            $codigo, data2bd($vencimento), moeda2bd($valor * -1), 0, moeda2bd($valor * -1), 1, 'ABERTO');
                                            
                    if(mysql_query($sql_edicao_parcelas))
                    {
                        exibir_mensagem_ok('Registro adicionado com êxito!');
                    }
                    else
                    {
                        exibir_mensagem_erro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                    }
                }
                else if($obj_buscar_titulos_subcategorias->num_parcelas_maximo > 1)
                {
                    $codigo = mysql_insert_id();
                    
                    if(is_array($cod_titulos_parcelas))
                    {
                        $res_edicao_parcelas = true;
                        
                        for($i = 0; $i < count($cod_titulos_parcelas); $i++)
                        {
                            $sql_edicao_parcelas = sprintf("INSERT INTO ger_titulos_parcelas (cod_titulos, data_vencimento, valor, juros, valor_total, numero_parcela, situacao) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                                            $codigo, data2bd($vencimento[$i]), moeda2bd($valor[$i] * -1), 0, moeda2bd($valor[$i] * -1), $i + 1, 'ABERTO');
                                            
                            $res_edicao_parcelas &= mysql_query($sql_edicao_parcelas);
                        }
                        
                        if($res_edicao_parcelas)
                        {
                            exibir_mensagem_ok('Registro adicionado com êxito!');
                        }
                        else
                        {
                            exibir_mensagem_erro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                        }
                    }
                }
            }
        }
        else
        {
            
            $sql_edicao = sprintf("UPDATE $tabela SET cod_titulos_subcategorias = '%s', cod_colaboradores = '%s', cod_fornecedores = '%s', tipo_cedente_sacado = '%s', descricao = '%s' WHERE $chave_primaria = $codigo", 
                            $cod_titulos_subcategorias, $cod_colaboradores, $cod_fornecedores, $tipo_cedente_sacado, $descricao);

            if(mysql_query($sql_edicao))
            {
                if($obj_buscar_titulos_subcategorias->num_parcelas_maximo == 1)
                {
                    $obj_buscar_parcelas = executar_busca_simples("SELECT * FROM ger_titulos_parcelas WHERE $chave_primaria = $codigo", $conexao);
                    
                    $sql_edicao_parcelas = sprintf("UPDATE ger_titulos_parcelas SET data_vencimento = '%s', valor = '%s', valor_total = '%s' WHERE cod_titulos_parcelas = '" . $obj_buscar_parcelas->cod_titulos_parcelas . "'",
                                            data2bd($vencimento), moeda2bd($valor * -1), moeda2bd($valor * -1));
                                            
                    if(mysql_query($sql_edicao_parcelas))
                    {
                        exibir_mensagem_ok('Registro atualizado com êxito!');
                    }
                    else
                    {
                        exibir_mensagem_erro('Erro ao atualizar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                    }
                }
                else if($obj_buscar_titulos_subcategorias->num_parcelas_maximo > 1)
                {
                    $res_edicao_parcelas = true;
                        
                    for($i = 0; $i < count($cod_titulos_parcelas); $i++)
                    {
                        $sql_edicao_parcelas = sprintf("UPDATE ger_titulos_parcelas SET data_vencimento = '%s', valor = '%s', valor_total = '%s' WHERE cod_titulos_parcelas = '" . $cod_titulos_parcelas[$i] . "'",
                                            data2bd($vencimento[$i]), moeda2bd($valor[$i] * -1), moeda2bd($valor[$i] * -1));
                                        
                        $res_edicao_parcelas &= mysql_query($sql_edicao_parcelas);
                    }
                    
                    if($res_edicao_parcelas)
                    {
                        exibir_mensagem_ok('Registro adicionado com êxito!');
                    }
                    else
                    {
                        exibir_mensagem_erro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                    }
                }
            }
            else
            {
                exibir_mensagem_erro('Erro ao atualizar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
            
        }
        
        desconectar_bd($conexao);
        
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_interna.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />

<script src="../lib/js/calendario.js" type="text/javascript"></script>
<script src="../lib/js/tabs_interna.js" type="text/javascript"></script>

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
        'action': '<?echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
    });
  
    var input = new Element('input', 
    {
        'type': 'hidden',
        'name': '<?echo $chave_primaria?>',
        'value': cod
    });
  
    input.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
}

function criar_formulario_edicao(cod_titulos_subcategorias, cod_titulos)
{
	var url = "acao=criar_formulario_edicao&cod_titulos_subcategorias=" + cod_titulos_subcategorias + "&cod_titulos=" + cod_titulos;
    
    new Request.HTML(
    {
        url: 'ger_cadastro_titulos_pagar_ajax.php',
        update: $('formulario_edicao'),
        onComplete: function()
        {
        	criar_parcelas(document.frmIncluir.num_parcelas.value, cod_titulos);
        }
    }).send(url);
} 

function criar_parcelas(num_parcelas, cod_titulos)
{
	var url = "acao=criar_parcelas&num_parcelas=" + num_parcelas + "&cod_titulos=" + cod_titulos;
    
    new Request.HTML(
    {
        url: 'ger_cadastro_titulos_pagar_ajax.php',
        update: $('criar_parcelas')
    }).send(url);
}

window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
  
    if (document.frmIncluir.<?echo $chave_primaria?>.value > 0) 
    {
        <?
        if ($acao == '')
        {
            echo 'tabs.irpara(1);';
        }
        ?>
    
    	criar_formulario_edicao(document.frmIncluir.cod_titulos_subcategorias.value, document.frmIncluir.<?echo $chave_primaria?>.value);
    
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
        	
            document.frmIncluir.<?echo $chave_primaria?>.value = '';
            document.frmIncluir.cod_titulos_subcategorias.value = '';
            
            criar_formulario_edicao(document.frmIncluir.cod_titulos_subcategorias.value, 0);
            
            /*
            document.frmIncluir.gerar_data_primeiro_vencimento.value = '';
            document.frmIncluir.gerar_condicao_pagamento.value = '';
            document.frmIncluir.gerar_valor_parcela.value = '';
            document.frmIncluir.gerar_num_parcelas.value = '';
            document.frmIncluir.gerar_forma_pagamento.value = 'BOLETO';
            
            document.frmIncluir.cod_fornecedores.value = '';
            document.frmIncluir.cod_colaboradores.value = '';
      
      		$('gerar_parcelas').setStyle('display', 'block');
      		
      		$('parcelas').set('html', '');
      		$('info_fornecedor').set('html', '');
      		$('info_colaborador').set('html', '');
      
      		tabsInterna.irpara(0);
      		*/
      
      		//document.frmIncluir.botao_submit.style.display = 'none';
            //document.frmIncluir.botao_submit.value = 'Cadastrar';
            
            //gerar_formulario_cadastro();
        }
    });
    
    // DatePick
    new vlaDatePicker('data_inicial_filtro', {openWith: 'botao_data_inicial_filtro', prefillDate: false});
    new vlaDatePicker('data_final_filtro', {openWith: 'botao_data_final_filtro', prefillDate: false});
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
        $pagina = (validar_var_post('pagina', '/[0-9]+/')) ? validar_var_post('pagina', '/[0-9]+/') : 0;
        
        //$data_inicial_filtro = (validar_var_post('data_inicial_filtro')) ? validar_var_post('data_inicial_filtro') : date("01/m/Y");
        //$data_final_filtro = (validar_var_post('data_final_filtro')) ? validar_var_post('data_final_filtro') : date("t/m/Y", mktime(0, 0, 0, date('m'), 1, date('Y')));
        $data_inicial_filtro = validar_var_post('data_inicial_filtro');
        $data_final_filtro = validar_var_post('data_final_filtro');
        
        $descricao_filtro = validar_var_post('descricao_filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

			<tr>
                <td class="legenda tdbl sep tdbt" align="right"><label for="descricao_filtro">Descrição:</label></td>
                <td class="sep tdbt">&nbsp;</td>
                <td class="tdbr sep tdbt">
                	<input type="text" name="descricao_filtro" id="descricao_filtro" size="50" value="<? echo $descricao_filtro ?>">
            	</td>
            </tr>

            <tr>
                <td class="legenda tdbl" align="right"><label for="data_inicial_filtro">Data Inicial:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr">
                	<input type="text" name="data_inicial_filtro" id="data_inicial_filtro" size="10" value="<? echo $data_inicial_filtro ?>" onkeypress="return MascaraData(this, event)">
                	&nbsp;
                	<a href="javascript:;" id="botao_data_inicial_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
            	</td>
            </tr>
            
            <tr>
                <td class="legenda tdbl sep" align="right"><label for="data_final_filtro">Data Final:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                	<input type="text" name="data_final_filtro" id="data_final_filtro" size="10" value="<? echo $data_final_filtro ?>" onkeypress="return MascaraData(this, event)">
                	&nbsp;
                	<a href="javascript:;" id="botao_data_final_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
            	</td>
            </tr>

            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3">
                    <input class="botaoAzul" type="submit" value="Buscar">
                </td>
            </tr>

        </table>
        
        <input type="hidden" name="acao" value="buscar"></form>

        <br>

        <?
        
        $conexao = conectar_bd();
        
        $sql_buscar_registros = "SELECT DISTINCT t.*, pp.*, tc.* FROM $tabela t INNER JOIN ger_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ger_titulos_subcategorias pp ON (t.cod_titulos_subcategorias = pp.cod_titulos_subcategorias) INNER JOIN ger_titulos_categorias tc ON (pp.cod_titulos_categorias = tc.cod_titulos_categorias) WHERE t.tipo_titulo = 'PAGAR' ";
        
        if((trim($data_inicial_filtro) != '') && (trim($data_final_filtro) != ''))
        {
        	$sql_buscar_registros .= " AND tp.data_vencimento BETWEEN '" . data2bd($data_inicial_filtro) . "' AND '" . data2bd($data_final_filtro) . "'";
        }
        
        if(trim($descricao_filtro) != '')
        {
        	$sql_buscar_registros .= " AND t.descricao LIKE '%" . texto2bd($descricao_filtro) . "%'";
        }
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY ' . $campo_ordenacao . ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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
            
            echo '<input type="hidden" name="data_inicial_filtro" value="' . $data_inicial_filtro . '">';
            echo '<input type="hidden" name="data_final_filtro" value="' . $data_inicial_filtro . '">';
            echo '<input type="hidden" name="descricao_filtro" value="' . $descricao_filtro . '">';
            
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

        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="<?echo LARGURA_PADRAO?>">
            <tr>
                <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0" width="<?echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20">  
                        <input type="checkbox" onclick="marcaTodos('marcar');">
                    </td>
                    <td align="center" width="30">Cód.</td>
                    <td align="center">Lançamento</td>
                    <td align="center">Descrição</td>
                    <td align="center">Cedente</td>
                    <td align="center" width="110">Total de Parcelas</td>
                    <td align="center" width="150">Data da Primeira Parcela</td>
                    <td align="center" width="130">Data da Última Parcela</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center">' . $obj_buscar_registros->$chave_primaria . '</td>';
                echo '<td align="left"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->titulos_categoria . '  &raquo; ' . $obj_buscar_registros->titulos_subcategorias) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->descricao) . '</td>';
                
                echo '<td align="center">';
                
                if($obj_buscar_registros->tipo_cedente_sacado == 'FORNECEDOR')
                {
                	$obj_buscar_cedente = executar_busca_simples("SELECT * FROM ger_fornecedores WHERE cod_fornecedores = '" . $obj_buscar_registros->cod_fornecedores . "'", $conexao);

                	echo bd2texto($obj_buscar_cedente->nome_fantasia);
                }
                else if($obj_buscar_registros->tipo_cedente_sacado == 'COLABORADOR')
                {
                	$obj_buscar_cedente = executar_busca_simples("SELECT * FROM ger_colaboradores WHERE cod_colaboradores = '" . $obj_buscar_registros->cod_colaboradores . "'", $conexao);

                	echo bd2texto($obj_buscar_cedente->nome);
                }
                
                echo '<br>';
                echo '<b><em><small>' . $obj_buscar_registros->tipo_cedente_sacado . '</small></em></b>';
                echo '</td>';
                
                echo '<td align="center">' . $obj_buscar_registros->total_parcelas . '</td>';
                
                // Buscar a primeira data
                $obj_primeira_data = executar_busca_simples("SELECT MIN(data_vencimento) AS data_min FROM ger_titulos_parcelas WHERE cod_titulos = '" . $obj_buscar_registros->cod_titulos . "' AND data_vencimento IS NOT NULL AND data_vencimento <> '0000-00-00'", $conexao);
                echo '<td align="center">' . bd2data($obj_primeira_data->data_min) . '</td>';
                
                // Buscar a utima data
                $obj_ultima_data = executar_busca_simples("SELECT MAX(data_vencimento) AS data_max FROM ger_titulos_parcelas WHERE cod_titulos = '" . $obj_buscar_registros->cod_titulos . "'  AND data_vencimento IS NOT NULL  AND data_vencimento <> '0000-00-00'", $conexao);
                echo '<td align="center">' . bd2data($obj_ultima_data->data_max) . '</td>';
                
                echo '</tr>';
            }
            
            desconectar_bd($conexao);
            
            ?>
          
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir"></form>

<? if ($exibir_barra_lateral): ?>

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

<? endif;?>

</div>

<!-- Tab Editar --> 


<!-- Tab Incluir -->

<div class="painelTab">

    <? 
    
    $codigo = validar_var_post($chave_primaria, '/[0-9]+/'); 
    
    if ($codigo > 0)
    {
        $obj_editar = executar_busca_simples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }
    
    ?>
    
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <!--
    <div id="formulario_cadastro" style="margin: 0px auto;"></div>
    -->
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
        
        <tr>
            <td class="legenda tdbl tdbt tdbr">
            	<label for="cod_titulos_subcategorias">Pagamento</label>
            </td>
        </tr>
       	<tr>
			<td class="tdbl tdbr sep">
            	<select name="cod_titulos_subcategorias" id="cod_titulos_subcategorias" style="width: 300px;" onchange="criar_formulario_edicao(this.value, '<? echo $codigo ?>')">
            		<option value=""></option>
            		
            		<?

            		$conexao = conectar_bd();
            		
                    $sql_buscar_categorias = "SELECT * FROM ger_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ger_titulos_subcategorias WHERE tipo_titulo = 'PAGAR') ORDER BY titulos_categoria";
                    $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                    
                    while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                    {
                        echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                        
                        $sql_buscar_subcategorias = "SELECT * FROM ger_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'PAGAR' ORDER BY titulos_subcategorias";
                        $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
                        
                        while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
                        {
                            echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '"';
                            
                            if($obj_editar->cod_titulos_subcategorias == $obj_buscar_subcategorias->cod_titulos_subcategorias)
                            {
                                echo 'selected';    
                            }
                        
                            echo '>' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
                        }
                        
                        echo '</optgroup>';
                    }
            		
            		desconectar_bd($conexao);
            		
            		?>
            	</select>
        	</td>
    	</tr>
        
        <tr>
            <td class="tdbr tdbl legenda">
            	<label for=descricao>Descrição</label>
        	</td>
        </tr>
    	<tr>
            <td class="tdbr tdbl sep">
            	<input type="text" name="descricao" id="descricao" style="width: 295px;" value="<? echo bd2texto($obj_editar->descricao); ?>">
			</tr>            
		</td>
        
        <tr><td id="formulario_edicao" class="tdbl tdbr sep">&nbsp;</td></tr>
        
        <tr>
            <td align="center" class="tdbl tdbb tdbr">
                <input name="botao_submit" class="botao" type="submit" value="Cadastrar">
            </td>
        </tr>
        
        </table>
    
    <input type="hidden" name="acao" value="editar"> 
    <input type="hidden" name="<?echo $chave_primaria?>" value="<?echo $codigo?>">
    </form>


</div>
<!-- Tab Incluir -->
</div>

<?
exibir_rodape();
?>
