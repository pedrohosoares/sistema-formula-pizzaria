<?php

/**
 * Cadastro Títulos a pagar.
 *
 * @version 1.0
 * @package osmuzzarellas
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

cabecalho('Estorno de Contas Pagas');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_titulos';
$tabela = 'ipi_titulos';
$campo_ordenacao = 'cod_titulos';
$campo_filtro_padrao = 'descricao';
$quant_pagina = 50;
$exibir_barra_lateral = false;
$codigo_usuario = $_SESSION['usuario']['codigo'];

switch ($acao)
{
    case 'excluir':
        $file = 'log_alteracoes_titulos_pagar.txt';
        $texto = var_export($_POST,true);

        file_put_contents($file,"\n \n".$texto, FILE_APPEND);
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del_1 = "DELETE FROM ipi_titulos_parcelas WHERE $chave_primaria IN ($indices_sql)";
        $sql_del_2 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";

        file_put_contents($file, "\n SQL DELETE PARCELA \n".$sql_del_1, FILE_APPEND);
        file_put_contents($file, "\n SQL DELETE TITULO \n".$sql_del_1, FILE_APPEND);
        if(mysql_error($conexao)!="")
        {
            file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
        }

        if (mysql_query($sql_del_1) && mysql_query($sql_del_2))
        {
            mensagemOk('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        }
        
        desconectabd($conexao);
        break;
    case 'estornar':
        $file = 'log_alteracoes_titulos_pagar.txt';
        $texto = var_export($_POST,true);

        file_put_contents($file, "\n \n".$texto, FILE_APPEND);

        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);

        $cod_pizzarias = validaVarPost('cod_pizzarias');
        //$numero_nota_fiscal = validaVarPost('numero_nota_fiscal');
        
        $conexao = conectabd();
        
        // Retornando o plano de contas pela subcategoria
        /*$obj_buscar_titulos_subcategorias = executaBuscaSimples("SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_subcategorias = '$cod_titulos_subcategorias'", $conexao);
        if(mysql_error($conexao)!="")
        {
            file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
        }
        $tipo_cedente_sacado = $obj_buscar_titulos_subcategorias->tipo_cendente_sacado;
        
        $vencimento = validaVarPost('vencimento');
        $emissao = validaVarPost('emissao');
        $valor = validaVarPost('valor');
        $mes_ref_entrada = validaVarPost('mes_ref');*/


        /*echo $vencimento;
        echo "<br/><br/>";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        echo "<br/><br/>".mysql_insert_id();
        die();*/
        //echo "<br/>codigo antes:".$codigo;
          
          
          
          $sql_edicao_parcelas = sprintf("UPDATE ipi_titulos_parcelas SET data_pagamento =  NULL,cod_usuarios_estorno = '%d', data_hora_estorno = NOW(), situacao = 'ABERTO' WHERE cod_titulos_parcelas IN ($indices_sql)",
                                  $codigo_usuario);
          //echo $sql_edicao_parcelas;
          file_put_contents($file, "\n SQL ESTORNO PARCELA UNICA \n".$sql_edicao_parcelas, FILE_APPEND);
          if(mysql_error($conexao)!="")
          {
              file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
          }                        
          if(mysql_query($sql_edicao_parcelas))
          {
              mensagemOk('Registro atualizado com êxito!');
          }
          else
          {
              mensagemErro('Erro ao atualizar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
        
        desconectabd($conexao);
        
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_interna.css" />
<!--<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />-->
<script type="text/javascript" src="../lib/js/Picker.js" /></script>
<script type="text/javascript" src="../lib/js/Picker.Attach.js" ></script>
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
        if (confirm('Deseja estornar os registros selecionados?'))
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
        alert('Por favor, selecione os itens que deseja estornar.');
     
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
        url: 'ipi_titulos_pagar_ajax.php',
        update: $('formulario_edicao'),
        onComplete: function()
        {
        	if(document.frmIncluir.num_parcelas.value > 0)
        	{
        		criar_parcelas(document.frmIncluir.num_parcelas.value, cod_titulos);
        	}
        }
    }).send(url);
} 

function criar_parcelas(num_parcelas, cod_titulos)
{
	var url = "acao=criar_parcelas&num_parcelas=" + num_parcelas + "&cod_titulos=" + cod_titulos;
    
    new Request.HTML(
    {
        url: 'ipi_titulos_pagar_ajax.php',
        update: $('criar_parcelas')
    }).send(url);
}

window.addEvent('domready', function()
{
    var tabs = new Tabs('tabs'); 
  
    /*if (document.frmIncluir.<?echo $chave_primaria?>.value > 0) 
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
    }*/
  
/*    tabs.addEvent('change', function(indice)
    {
        if(indice == 1)
        {
        	
            document.frmIncluir.<?echo $chave_primaria?>.value = '';
            document.frmIncluir.cod_titulos_subcategorias.value = '';
            document.frmIncluir.cod_pizzarias.value = '';
            document.frmIncluir.numero_nota_fiscal.value = '';
            document.frmIncluir.descricao.value = '';
            
            criar_formulario_edicao(document.frmIncluir.cod_titulos_subcategorias.value, 0);

            
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
      		
      
      		//document.frmIncluir.botao_submit.style.display = 'none';
            //document.frmIncluir.botao_submit.value = 'Cadastrar';
            
            //gerar_formulario_cadastro();
        }
    });*/
    
    // DatePick
    new vlaDatePicker('data_inicial_filtro', {openWith: 'botao_data_inicial_filtro', prefillDate: false});
    new vlaDatePicker('data_final_filtro', {openWith: 'botao_data_final_filtro', prefillDate: false});
});

</script>

<div id="tabs">
<div class="menuTab">
<ul>
    <li><a href="javascript:;">Editar</a></li>
    <!-- <li><a href="javascript:;">Incluir</a></li> -->
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
        
        $data_inicial_filtro = (validaVarPost('data_inicial_filtro')) ? validaVarPost('data_inicial_filtro') : date("01/m/Y");
        $data_final_filtro = (validaVarPost('data_final_filtro')) ? validaVarPost('data_final_filtro') : date("t/m/Y", mktime(0, 0, 0, date('m'), 1, date('Y')));
        //$data_inicial_filtro = validaVarPost('data_inicial_filtro');
        //$data_final_filtro = validaVarPost('data_final_filtro');
        $pagos_filtro = validaVarPost('pagos_filtro');
        $cod_pizzarias = validaVarPost("cod_pizzarias");
        $descricao_filtro = validaVarPost('descricao_filtro');
        $tipo_titulos = validaVarPost('tipo_titulos');
        $filtrar_por = validaVarPost('filtrar_filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

			<tr>
                <td class="legenda tdbl tdbt" align="right"><label for="descricao_filtro">Descrição:</label></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbr tdbt">
                	<input type="text" name="descricao_filtro" id="descricao_filtro" size="50" value="<? echo $descricao_filtro ?>">
            	</td>
            </tr>

            <tr>
                <td class="legenda tdbl" align="right"><label for="cod_pizzarias">Pizzaria:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr">
                  <select name="cod_pizzarias" id="cod_pizzarias">
                    <option value="">Todas as Pizzarias</option>
                    <?
                    $con = conectabd();
                    $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
                    $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                    while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
                    {
                      echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                      if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
                        echo 'selected';
                      echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
                    }
                    desconectabd($con);
                    ?>
                  </select>
                </td>
            </tr>

            <tr>
                <td class="legenda tdbl" align="right"><label for="tipo_titulos">Tipo:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr">
                  <select name="tipo_titulos" id="tipo_titulos">
                    <option value="PAGAR" <? if($tipo_titulos=="PAGAR") echo "selected='selected'" ;?> >Pagamentos</option>
                    <option value="RECEBER" <? if($tipo_titulos=="RECEBER") echo "selected='selected'" ;?> >Recebimentos</option>
                  </select>
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
                <td class="legenda tdbl " align="right"><label for="data_final_filtro">Data Final:</label></td>
                <td class="">&nbsp;</td>
                <td class="tdbr ">
                	<input type="text" name="data_final_filtro" id="data_final_filtro" size="10" value="<? echo $data_final_filtro ?>" onkeypress="return MascaraData(this, event)">
                	&nbsp;
                	<a href="javascript:;" id="botao_data_final_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
            	</td>
            </tr>
              <tr>
                <td class="legenda tdbl sep" align="right"><label for="filtrar_filtro">Filtrar por:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                    <select name="filtrar_filtro">
                        <option value="DATA_PAGAMENTO" <? if($filtrar_por=="DATA_PAGAMENTO") echo "SELECTED='SELECTED'"; ?>>Data de Pagamento</option>
                        <option value="MES_REFERENCIA" <? if($filtrar_por=="MES_REFERENCIA") echo "SELECTED='SELECTED'"; ?>>Data de Competência</option>
                        <option value="DATA_VENCIMENTO" <? if($filtrar_por=="DATA_VENCIMENTO") echo "SELECTED='SELECTED'"; ?>>Data de Vencimento</option>
                    </select>
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
        
        $conexao = conectabd();
        
        $sql_buscar_registros = "SELECT DISTINCT t.*, pp.*, tc.*,tp.valor_total,tp.numero_parcela,tp.cod_titulos_parcelas,tp.data_pagamento,tp.data_vencimento FROM $tabela t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_titulos_subcategorias pp ON (t.cod_titulos_subcategorias = pp.cod_titulos_subcategorias) INNER JOIN ipi_titulos_categorias tc ON (pp.cod_titulos_categorias = tc.cod_titulos_categorias) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND tp.situacao = 'PAGO'";
        
        if($tipo_titulos!="")
        {
            $sql_buscar_registros .= " AND t.tipo_titulo = '$tipo_titulos'";
        }
        else
        {
            $sql_buscar_registros .= " AND t.tipo_titulo = 'PAGAR'";
        }

        if((trim($data_inicial_filtro) != '') && (trim($data_final_filtro) != ''))
        {
          if($filtrar_por =="MES_REFERENCIA")
          {
            $sql_buscar_registros .= " AND tp.mes_ref BETWEEN month('" . data2bd($data_inicial_filtro) . "') AND month('" . data2bd($data_final_filtro) . "')  AND tp.ano_ref BETWEEN year('" . data2bd($data_inicial_filtro) . "') AND year('" . data2bd($data_final_filtro) . "')";
          }
          else if($filtrar_por =="DATA_PAGAMENTO")
          {
            $sql_buscar_registros .= " AND tp.data_pagamento BETWEEN '" . data2bd($data_inicial_filtro) . "' AND '" . data2bd($data_final_filtro) . "'";
          }
          else if($filtrar_por =="DATA_CRIADA")
          {
            $sql_buscar_registros .= " AND tp.data_hora_criacao BETWEEN '" . data2bd($data_inicial_filtro) . "' AND '" . data2bd($data_final_filtro) . "'";
          }
          else if($filtrar_por=="DATA_EMISSAO")
          {
            $sql_buscar_registros .= " AND tp.data_emissao BETWEEN '" . data2bd($data_inicial_filtro) . "' AND '" . data2bd($data_final_filtro) . "'";
          }
          else
          {
            $sql_buscar_registros .= " AND tp.data_vencimento BETWEEN '" . data2bd($data_inicial_filtro) . "' AND '" . data2bd($data_final_filtro) . "'";
          } 
        }
        
        if(trim($descricao_filtro) != '')
        {
        	$sql_buscar_registros .= " AND t.descricao LIKE '%" . texto2bd($descricao_filtro) . "%'";
        }
        
        if($cod_pizzarias!="")
        {
            $sql_buscar_registros .= " AND t.cod_pizzarias = '".$cod_pizzarias."'";
        }

        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY t.cod_titulos,tp.cod_titulos_parcelas,tp.numero_parcela LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        //echo $sql_buscar_registros;

        echo "<center><b>" . $num_buscar_registros . " parcela(s) BAIXADA(S) encontrada(s)</center></b><br>";
        
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
            echo '<input type="hidden" name="data_final_filtro" value="' . $data_final_filtro . '">';
            echo '<input type="hidden" name="descricao_filtro" value="' . $descricao_filtro . '">';
            echo '<input type="hidden" name="pagos_filtro" value="' . $pagos_filtro . '">';
            echo '<input type="hidden" name="filtrar_filtro" value="' . $filtrar_filtro . '">';
            echo '<input type="hidden" name="cod_pizzarias" value="' . $cod_pizzarias . '">';
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
                <td><input class="botaoAzul" type="submit" value="Estornar Selecionados"></td>
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
                    <td align="center" width="110">Parcela</td>
                    <td align="center" width="110">Valor</td>
                    <td align="center" width="150">Data de Pagamento</td>
                    <td align="center" width="130">Data de Vencimento</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->cod_titulos_parcelas . '"></td>';
                echo '<td align="center">' . $obj_buscar_registros->cod_titulos_parcelas . '</td>';
                echo '<td align="left">' . bd2texto($obj_buscar_registros->titulos_categoria . '  &raquo; ' . $obj_buscar_registros->titulos_subcategorias) . '</td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->descricao) . '</td>';
                
                echo '<td align="center">';
                
                if($obj_buscar_registros->tipo_cedente_sacado == 'FORNECEDOR')
                {
                	$obj_buscar_cedente = executaBuscaSimples("SELECT * FROM ipi_fornecedores WHERE cod_fornecedores = '" . $obj_buscar_registros->cod_fornecedores . "'", $conexao);

                	echo bd2texto($obj_buscar_cedente->nome_fantasia);
                }
                else if($obj_buscar_registros->tipo_cedente_sacado == 'COLABORADOR')
                {
                	$obj_buscar_cedente = executaBuscaSimples("SELECT * FROM ipi_colaboradores WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND  cod_colaboradores = '" . $obj_buscar_registros->cod_colaboradores . "'", $conexao);

                	echo bd2texto($obj_buscar_cedente->nome);
                }
                else if($obj_buscar_registros->tipo_cedente_sacado == 'ENTREGADOR')
                {
                	$obj_buscar_cedente = executaBuscaSimples("SELECT * FROM ipi_entregadores WHERE cod_entregadores = '" . $obj_buscar_registros->cod_entregadores . "'", $conexao);
                	
                	echo bd2texto($obj_buscar_cedente->nome);
                }
                
                echo '<br>';
                echo '<b><em><small>' . $obj_buscar_registros->tipo_cedente_sacado . '</small></em></b>';
                echo '</td>';
                
                echo '<td align="center">' . $obj_buscar_registros->numero_parcela . ' / '.$obj_buscar_registros->total_parcelas .'</td>';

                // Buscar a primeira data
                $obj_primeira_data = executaBuscaSimples("SELECT SUM(valor_total) AS total FROM ipi_titulos_parcelas WHERE cod_titulos = '" . $obj_buscar_registros->cod_titulos . "' AND data_vencimento IS NOT NULL AND data_vencimento <> '0000-00-00'", $conexao);
                echo '<td align="center">'.bd2moeda(abs($obj_buscar_registros->valor_total)).' / ' . bd2moeda(abs($obj_primeira_data->total)) . '</td>';
                
                // Buscar a primeira data
                //$obj_primeira_data = executaBuscaSimples("SELECT MIN(data_vencimento) AS data_min FROM ipi_titulos_parcelas WHERE cod_titulos = '" . $obj_buscar_registros->cod_titulos . "' AND data_vencimento IS NOT NULL AND data_vencimento <> '0000-00-00'", $conexao);
                echo '<td align="center">' . bd2data($obj_buscar_registros->data_pagamento) . '</td>';
                
                // Buscar a utima data
                //$obj_ultima_data = executaBuscaSimples("SELECT MAX(data_vencimento) AS data_max FROM ipi_titulos_parcelas WHERE cod_titulos = '" . $obj_buscar_registros->cod_titulos . "'  AND data_vencimento IS NOT NULL  AND data_vencimento <> '0000-00-00'", $conexao);
                echo '<td align="center">' . bd2data($obj_buscar_registros->data_vencimento) . '</td>';
                
                echo '</tr>';
            }
            
            desconectabd($conexao);
            
            ?>
          
            </tbody>
        </table>

        <input type="hidden" name="acao" value="estornar"></form>

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
    
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/'); 
    
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT t.* FROM $tabela t inner join ipi_titulos_parcelas tp on tp.cod_titulos = t.cod_titulos WHERE tp.cod_titulos_parcelas = $codigo");
    }
    
    ?>
    
    <!-- <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">
        
        <tr>
            <td class="legenda tdbl tdbt tdbr">
            	<label for="cod_titulos_subcategorias">Pagamento</label>
            </td>
        </tr>
       	<tr>
			<td class="tdbl tdbr sep">
            	<select name="cod_titulos_subcategorias" id="cod_titulos_subcategorias" style="width: 300px;" onchange="criar_formulario_edicao(this.value, document.frmIncluir.cod_titulos.value)">
            		<option value=""></option>
            		
            		<?

            		$conexao = conectabd();
            		
                    $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias WHERE tipo_titulo = 'PAGAR') ORDER BY titulos_categoria";
                    $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                    
                    while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                    {
                        echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                        
                        $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'PAGAR' ORDER BY titulos_subcategorias";
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
            		
            		desconectabd($conexao);
            		
            		?>
            	</select>
        	</td>
    	</tr>
    	
    	<tr>
            <td class="legenda tdbl tdbr">
            	<label for="cod_pizzarias">Pizzaria</label>
            </td>
        </tr>
       	<tr>
			<td class="tdbl tdbr sep">
            	<select name="cod_pizzarias" id="cod_pizzarias" style="width: 300px;">
            		<option value=""></option>
            		
            		<?

            		$conexao = conectabd();
            		
                    $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
                    $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
                    
                    while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
                    {
                        echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '"';
                        
                        if($obj_editar->cod_pizzarias == $obj_buscar_pizzarias->cod_pizzarias)
                        {
                            echo 'selected';    
                        }
                    
                        echo '>' . bd2texto($obj_buscar_pizzarias->nome) . '</option>';
                        
                    }
            		
            		desconectabd($conexao);
            		
            		?>
            	</select>
        	</td>
    	</tr>
        
        
        <tr>
            <td class="tdbr tdbl legenda">
                <label for="descricao">Descrição</label>
            </td>
        </tr>
        <tr>
            <td class="tdbr tdbl sep">
                <input type="text" name="descricao" id="descricao" style="width: 295px;" value="<? echo bd2texto($obj_editar->descricao); ?>">
            </tr>            
        </td>

        <tr>
            <td class="tdbr tdbl legenda">
            	<label for="numero_nota_fiscal">Número Nota Fiscal</label>
        	</td>
        </tr>
    	<tr>
            <td class="tdbr tdbl sep">
            	<input type="text" name="numero_nota_fiscal" id="numero_nota_fiscal" style="width: 295px;" maxlength="60" value="<? echo bd2texto($obj_editar->numero_nota_fiscal); ?>">
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
 -->

</div>
<!-- Tab Incluir -->
</div>

<?
rodape();
?>
