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

cabecalho('Cadastro de Contas a Pagar');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_titulos';
$tabela = 'ipi_titulos';
$campo_ordenacao = 'cod_titulos';
$campo_filtro_padrao = 'descricao';
$quant_pagina = 50;
$exibir_barra_lateral = false;

$tp = validaVarGet('tp');


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

        // file_put_contents($file, "\n SQL DELETE PARCELA \n".$sql_del_1, FILE_APPEND);
        // file_put_contents($file, "\n SQL DELETE TITULO \n".$sql_del_1, FILE_APPEND);
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
    case 'editar':
        // $file = 'log_alteracoes_titulos_pagar.txt';
        // $texto = var_export($_POST,true);

        // file_put_contents($file, "\n \n".$texto, FILE_APPEND);

        $codigo = validaVarPost($chave_primaria);
        
        $cod_titulos_subcategorias = validaVarPost('cod_titulos_subcategorias');
        $cod_colaboradores = validaVarPost('cod_colaboradores');
        $cod_fornecedores = validaVarPost('cod_fornecedores');
        $cod_entregadores = validaVarPost('cod_entregadores');
        $descricao = validaVarPost('descricao');
        $num_parcelas = validaVarPost('num_parcelas');
        $cod_titulos_parcelas = validaVarPost('cod_titulos_parcelas');
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $numero_nota_fiscal = validaVarPost('numero_nota_fiscal');
        
        $conexao = conectabd();
        
        // Retornando o plano de contas pela subcategoria
        $obj_buscar_titulos_subcategorias = executaBuscaSimples("SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_subcategorias = '$cod_titulos_subcategorias'", $conexao);
        if(mysql_error($conexao)!="")
        {
            file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
        }
        $tipo_cedente_sacado = $obj_buscar_titulos_subcategorias->tipo_cendente_sacado;
        
        $vencimento = validaVarPost('vencimento');
        $emissao = validaVarPost('emissao');
        $valor = validaVarPost('valor');
        $mes_ref_entrada = validaVarPost('mes_ref');
        /*echo $vencimento;
        echo "<br/><br/>";
        echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        echo "<br/><br/>".mysql_insert_id();
        die();*/
        //echo "<br/>codigo antes:".$codigo;
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_pizzarias, cod_titulos_subcategorias, cod_colaboradores, cod_fornecedores, cod_entregadores, total_parcelas, tipo_titulo, tipo_cedente_sacado, descricao, numero_nota_fiscal,data_hora_criacao) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', 'PAGAR', '%s', '%s', '%s',NOW())", 
                                $cod_pizzarias, $cod_titulos_subcategorias, $cod_colaboradores, $cod_fornecedores, $cod_entregadores, $num_parcelas, $tipo_cedente_sacado, $descricao, $numero_nota_fiscal);

            // file_put_contents($file, "\n SQL INSERCAO TITULO\n".$sql_edicao, FILE_APPEND);
            if(mysql_error($conexao)!="")
            {
                file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
            }
            if(mysql_query($sql_edicao))
            {
                $codigo = mysql_insert_id();
                //echo "<br/>codigo 1:".$codigo;
                if($obj_buscar_titulos_subcategorias->num_parcelas_maximo == 1)
                {
                    preg_match('/([0-9]{2})\/([0-9]{4})/', $mes_ref_entrada, $arr_match_data);
                    
                    $mes_ref = $arr_match_data[1];
                    $ano_ref = $arr_match_data[2];
                    
                    $sql_edicao_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos, data_vencimento,data_emissao, valor, juros, valor_total, numero_parcela, mes_ref, ano_ref, situacao,data_hora_criacao) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', '%s',NOW())",
                                            $codigo, data2bd($vencimento),data2bd($emissao),(moeda2bd($valor) * -1), 0, (moeda2bd($valor) * -1), 1, $mes_ref, $ano_ref, 'ABERTO');

                    // file_put_contents($file, "\n SQL INSERCAO PARCELA UNICA \n".$sql_edicao_parcelas, FILE_APPEND);
                    if(mysql_error($conexao)!="")
                    {
                        file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
                    }
                    //echo "<br/> sql 1: ".$sql_edicao_parcelas;                        
                    if(mysql_query($sql_edicao_parcelas))
                    {
                        mensagemOk('Registro adicionado com êxito!');
                    }
                    else
                    {
                        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                    }
                }
                else if($obj_buscar_titulos_subcategorias->num_parcelas_maximo > 1)
                {
                    $codigo = mysql_insert_id();
                    //echo "<br/><br/>codigo 2:".$codigo;
                    if(is_array($cod_titulos_parcelas))
                    {
                        $res_edicao_parcelas = true;
                        
                        for($i = 0; $i < count($cod_titulos_parcelas); $i++)
                        {
                            preg_match('/([0-9]{2})\/([0-9]{4})/', $mes_ref_entrada[$i], $arr_match_data);
                    
                            $mes_ref = $arr_match_data[1];
                            $ano_ref = $arr_match_data[2];
                            
                            $sql_edicao_parcelas = sprintf("INSERT INTO ipi_titulos_parcelas (cod_titulos, data_vencimento, data_emissao, valor, juros, valor_total, numero_parcela, mes_ref, ano_ref, situacao,data_hora_criacao) VALUES ('%s', '%s','%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',NOW())",
                                                    $codigo, data2bd($vencimento[$i]),data2bd($emissao[$i]), (moeda2bd($valor[$i]) * -1), 0, (moeda2bd($valor[$i]) * -1), $i + 1, $mes_ref, $ano_ref, 'ABERTO');
                            //echo "<br/>query 2 :".$sql_edicao_parcelas;          
                            // file_put_contents($file, "\n SQL INSERCAO MAIS DE UMA PARCELA\n".$sql_edicao_parcelas, FILE_APPEND);  
                            if(mysql_error($conexao)!="")
                            {
                                file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
                            }    
                            $res_edicao_parcelas &= mysql_query($sql_edicao_parcelas);
                        }
                        
                        if($res_edicao_parcelas)
                        {
                            mensagemOk('Registro adicionado com êxito!');
                        }
                        else
                        {
                            mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                        }
                    }
                }
            }
            else
            {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            
            $sql_edicao = sprintf("UPDATE $tabela SET cod_pizzarias = '%s', cod_titulos_subcategorias = '%s', cod_colaboradores = '%s', cod_fornecedores = '%s', cod_entregadores = '%s', tipo_cedente_sacado = '%s', descricao = '%s', numero_nota_fiscal = '%s' WHERE $chave_primaria = $codigo", 
                            $cod_pizzarias, $cod_titulos_subcategorias, $cod_colaboradores, $cod_fornecedores, $cod_entregadores, $tipo_cedente_sacado, $descricao, $numero_nota_fiscal);
            // file_put_contents($file, "\n SQL ALTERA TITULO \n".$sql_edicao, FILE_APPEND);
            if(mysql_error($conexao)!="")
            {
                file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
            }   
            if(mysql_query($sql_edicao))
            {
                if($obj_buscar_titulos_subcategorias->num_parcelas_maximo == 1)
                {
                    $obj_buscar_parcelas = executaBuscaSimples("SELECT * FROM ipi_titulos_parcelas WHERE $chave_primaria = $codigo", $conexao);
                    
                    preg_match('/([0-9]{2})\/([0-9]{4})/', $mes_ref_entrada, $arr_match_data);
                    
                    $mes_ref = $arr_match_data[1];
                    $ano_ref = $arr_match_data[2];
                    
                    $sql_edicao_parcelas = sprintf("UPDATE ipi_titulos_parcelas SET data_vencimento = '%s', data_emissao = '%s', valor = '%s', valor_total = '%s', mes_ref = '%s', ano_ref = '%s' WHERE cod_titulos_parcelas = '" . $obj_buscar_parcelas->cod_titulos_parcelas . "'",
                                            data2bd($vencimento),data2bd($emissao), (moeda2bd($valor) * -1), (moeda2bd($valor) * -1), $mes_ref, $ano_ref);
                    // file_put_contents($file, "\n SQL EDICAO PARCELA UNICA \n".$sql_edicao_parcelas, FILE_APPEND);
                    // die($sql_edicao_parcelas);
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
                }
                else if($obj_buscar_titulos_subcategorias->num_parcelas_maximo > 1)
                {
                    $res_edicao_parcelas = true;
                        
                    for($i = 0; $i < count($cod_titulos_parcelas); $i++)
                    {
                        preg_match('/([0-9]{2})\/([0-9]{4})/', $mes_ref_entrada[$i], $arr_match_data);
                    
                        $mes_ref = $arr_match_data[1];
                        $ano_ref = $arr_match_data[2];
                        
                        $sql_edicao_parcelas = sprintf("UPDATE ipi_titulos_parcelas SET data_vencimento = '%s', data_emissao = '%s', valor = '%s', valor_total = '%s', mes_ref = '%s', ano_ref = '%s' WHERE cod_titulos_parcelas = '" . $cod_titulos_parcelas[$i] . "'",
                                            data2bd($vencimento[$i]), data2bd($emissao[$i]), (moeda2bd($valor[$i]) * -1), (moeda2bd($valor[$i]) * -1), $mes_ref, $ano_ref);
                        // file_put_contents($file, "\n SQL EDICAO PARCELA MULTIPLAS PARCELAS \n".$sql_edicao_parcelas, FILE_APPEND);
                        if(mysql_error($conexao)!="")
                        {
                            file_put_contents($file, "ERRO ".mysql_errno($conexao) . ": " . mysql_error($conexao) . "\n",FILE_APPEND);
                        }                
                        $res_edicao_parcelas &= mysql_query($sql_edicao_parcelas);
                    }
                    
                    if($res_edicao_parcelas)
                    {
                        mensagemOk('Registro adicionado com êxito!');
                    }
                    else
                    {
                        mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
                    }
                }
            }
            else
            {
                mensagemErro('Erro ao atualizar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
            
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
            document.frmIncluir.cod_pizzarias.value = '';
            document.frmIncluir.numero_nota_fiscal.value = '';
            document.frmIncluir.descricao.value = '';
            
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

        $pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0;
        
        $data_inicial_filtro = (validaVarPost('data_inicial_filtro')) ? validaVarPost('data_inicial_filtro') : date("01/m/Y");
        $data_final_filtro = (validaVarPost('data_final_filtro')) ? validaVarPost('data_final_filtro') : date("t/m/Y", mktime(0, 0, 0, date('m'), 1, date('Y')));
        $comissao_cartao = (validaVarPost('comissao_cartao')) ? validaVarPost('comissao_cartao') : "";
        //$data_inicial_filtro = validaVarPost('data_inicial_filtro');
        //$data_final_filtro = validaVarPost('data_final_filtro');
        $pagos_filtro = validaVarPost('pagos_filtro');
        
        $descricao_filtro = validaVarPost('descricao_filtro');

        $fornecedor_filtro = validaVarPost('fornecedor_filtro');
        $colaborador_filtro = validaVarPost('colaborador_filtro');
        $entregador_filtro = validaVarPost('entregador_filtro');

        $num_nf_filtro = validaVarPost('num_nf_filtro');
        ?>
        
        <form name="frmFiltro" method="post">
        
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

			<tr>
                <td class="legenda tdbl sep tdbt" align="right"><label for="descricao_filtro">Descrição:</label></td>
                <td class=" tdbt">&nbsp;</td>
                <td class="tdbr  tdbt">
                	<input type="text" name="descricao_filtro" id="descricao_filtro" size="50" value="<? echo $descricao_filtro ?>">
            	</td>
            </tr>
            <tr>
                <td class="legenda tdbl sep " align="right"><label for="fornecedor_filtro">Fornecedor:</label></td>
                <td class="sep ">&nbsp;</td>
                <td class="tdbr ">
                    <input type="text" name="fornecedor_filtro" id="fornecedor_filtro" size="50" value="<? echo $fornecedor_filtro ?>">
                </td>
            </tr>

             <tr>
                <td class="legenda tdbl sep " align="right"><label for="colaborador_filtro">Colaborador:</label></td>
                <td class="sep ">&nbsp;</td>
                <td class="tdbr ">
                    <input type="text" name="colaborador_filtro" id="colaborador_filtro" size="50" value="<? echo $colaborador_filtro ?>">
                </td>
            </tr>

             <tr>
                <td class="legenda tdbl sep " align="right"><label for="entregador_filtro">Entregador:</label></td>
                <td class="sep ">&nbsp;</td>
                <td class="tdbr ">
                    <input type="text" name="entregador_filtro" id="entregador_filtro" size="50" value="<? echo $entregador_filtro ?>">
                </td>
            </tr>

            <tr>
                <td class="legenda tdbl sep " align="right"><label for="num_nf_filtro">Num NF:</label></td>
                <td class=" ">&nbsp;</td>
                <td class="tdbr ">
                    <input type="text" name="num_nf_filtro" id="num_nf_filtro" size="50" value="<? echo $num_nf_filtro ?>">
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
                <td class="legenda tdbl sep" align="right"><label for="comissao_cartao">Sem as comissões dos cartões:</label></td>
                <td class="sep">&nbsp;</td>
                <td class="tdbr sep">
                    <select name="comissao_cartao" id="comissao_cartao">
                        <option value="">Não</option>
                        <option value="1">Sim</option>
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
        if ($tp)
        {
          $codigo = $tp;
          
           $sql_buscar_registros = "SELECT DISTINCT t.*, pp.*, tc.*, tp.situacao, tp.ano_ref, tp.mes_ref, tp.valor FROM $tabela t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_titulos_subcategorias pp ON (t.cod_titulos_subcategorias = pp.cod_titulos_subcategorias) INNER JOIN ipi_titulos_categorias tc ON (pp.cod_titulos_categorias = tc.cod_titulos_categorias) WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND t.tipo_titulo = 'PAGAR' AND cod_titulos_parcelas = $codigo";
           // echo $sql_buscar_registros;
        }
        else
        {


                $sql_buscar_registros = "SELECT DISTINCT t.*, pp.*, tc.*, tp.situacao, tp.ano_ref, tp.mes_ref, tp.valor FROM $tabela t INNER JOIN ipi_titulos_parcelas tp ON (t.cod_titulos = tp.cod_titulos) INNER JOIN ipi_titulos_subcategorias pp ON (t.cod_titulos_subcategorias = pp.cod_titulos_subcategorias) INNER JOIN ipi_titulos_categorias tc ON (pp.cod_titulos_categorias = tc.cod_titulos_categorias) ";

                if(trim($fornecedor_filtro) != '')
                {
                    $sql_buscar_registros .= " INNER JOIN ipi_fornecedores f ON (f.cod_fornecedores = t.cod_fornecedores) ";
                    $nome_filtro = "AND f.nome_fantasia LIKE '%$fornecedor_filtro%'";
                }

                if(trim($colaborador_filtro) != '')
                {
                    $sql_buscar_registros .= " INNER JOIN ipi_colaboradores c ON (c.cod_colaboradores = t.cod_colaboradores) ";
                     $nome_filtro = "AND c.nome LIKE '%$colaborador_filtro%'";
                }

                if(trim($entregador_filtro) != '')
                {
                    $sql_buscar_registros .= " INNER JOIN ipi_entregadores e ON (e.cod_entregadores = t.cod_entregadores) ";
                     $nome_filtro = "AND e.nome LIKE '%$entregador_filtro%'";
                }


                $sql_buscar_registros.=" WHERE t.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND t.tipo_titulo = 'PAGAR'";

                if ($comissao_cartao==1)
                {
                    ## codigo das comissões do cartão = 2
                    $sql_buscar_registros.= " AND pp.cod_titulos_categorias NOT IN (2)";
                }

           
                if((trim($data_inicial_filtro) != '') && (trim($data_final_filtro) != ''))
                {
                	$sql_buscar_registros .= " AND tp.data_vencimento BETWEEN '" . data2bd($data_inicial_filtro) . "' AND '" . data2bd($data_final_filtro) . "'";
                }
                
                if(trim($descricao_filtro) != '')
                {
                	$sql_buscar_registros .= " AND t.descricao LIKE '%" . texto2bd($descricao_filtro) . "%'";
                }

                // if(trim($cedente_filtro) != '')
                // {
                //     $sql_buscar_registros .= " AND t.tipo_cedente_sacado LIKE '%" . texto2bd($cedente_filtro) . "%'";
                // }

                 if(trim($num_nf_filtro) != '')
                {
                    $sql_buscar_registros .= " AND t.numero_nota_fiscal LIKE '%" . texto2bd($num_nf_filtro) . "%'";
                }

                $sql_buscar_registros .= " $nome_filtro ";


        }
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY ' . $campo_ordenacao . ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        // echo $sql_buscar_registros;

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
            echo '<input type="hidden" name="data_final_filtro" value="' . $data_final_filtro . '">';
            echo '<input type="hidden" name="descricao_filtro" value="' . $descricao_filtro . '">';
             echo '<input type="hidden" name="cedente_filtro" value="' . $cedente_filtro . '">';
              echo '<input type="hidden" name="num_nf_filtro" value="' . $num_nf_filtro . '">';
            echo '<input type="hidden" name="pagos_filtro" value="' . $pagos_filtro . '">';
            
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
                    <td align="center">Compet.</td>
                    <td align="center">Valor Parcela</td>
                    <td align="center">Num .NF</td>
                    <td align="center" width="110">Total de Parcelas</td>
                    <td align="center" width="150">Data da Primeira Parcela</td>
                    <td align="center" width="130">Data da Última Parcela</td>
                    <td align="center" width="130">Situação</td>
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
                echo '<td align="center">' . $obj_buscar_registros->mes_ref . '/'.$obj_buscar_registros->ano_ref .'</td>';
                echo '<td align="center">' . $obj_buscar_registros->valor . '</td>';
                echo '<td align="center">' . $obj_buscar_registros->numero_nota_fiscal . '</td>';
                echo '<td align="center">' . $obj_buscar_registros->total_parcelas . '</td>';
                
                // Buscar a primeira data
                $obj_primeira_data = executaBuscaSimples("SELECT MIN(data_vencimento) AS data_min FROM ipi_titulos_parcelas WHERE cod_titulos = '" . $obj_buscar_registros->cod_titulos . "' AND data_vencimento IS NOT NULL AND data_vencimento <> '0000-00-00'", $conexao);
                echo '<td align="center">' . bd2data($obj_primeira_data->data_min) . '</td>';
                
                // Buscar a utima data
                $obj_ultima_data = executaBuscaSimples("SELECT MAX(data_vencimento) AS data_max FROM ipi_titulos_parcelas WHERE cod_titulos = '" . $obj_buscar_registros->cod_titulos . "'  AND data_vencimento IS NOT NULL  AND data_vencimento <> '0000-00-00'", $conexao);
                echo '<td align="center">' . bd2data($obj_ultima_data->data_max) . '</td>';
                 echo '<td align="center">' . $obj_buscar_registros->situacao . '</td>';
                echo '</tr>';
            }
            
            desconectabd($conexao);
            
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
    
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/'); 
    
    if ($codigo > 0)
    {
        $obj_editar = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
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


</div>
<!-- Tab Incluir -->
</div>

<?
// if ($tp)
// {
//   $codigo = $tp;
//   // $acao = "editar";
//   echo '<script>editar('.$codigo.')</script>';
// }
rodape();
?>
