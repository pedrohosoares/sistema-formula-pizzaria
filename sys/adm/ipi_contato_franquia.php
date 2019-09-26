<?php

/**
 * Cadastro de Propostas.
 *
 * @version 1.0
 * @package gerencial
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       10/08/2009   BRUNO         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Interessados em Franquia');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_interesse_fraquia';
$tabela = 'ipi_interesse_fraquia';
$campo_ordenacao = 'data_cadastro';
$campo_filtro_padrao = 'nome';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $obj_arquivos_excluir = executar_busca_simples("SELECT * FROM ger_propostas WHERE $chave_primaria = $indices_sql", $conexao);
        
        if (is_file(UPLOAD_DIR . '/propostas/' . $obj_arquivos_excluir->arquivo))
        {
            unlink(UPLOAD_DIR . '/propostas/' . $obj_arquivos_excluir->arquivo);
        }
        
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
        
    case 'comentar':
        
        $codigo = validaVarPost($chave_primaria);
        $novo_comentario = validaVarPost('novo_comentario');
        
        $conexao = conectabd();
        
        $sql_comentario = sprintf("INSERT INTO ipi_interesse_comentarios (cod_interesse_fraquia, cod_usuarios, comentario, data_hora_comentario) VALUES ('%s', '%s', '%s', NOW())", 
                            $codigo, $_SESSION['usuario']['codigo'] , $novo_comentario);
                            
        if (mysql_query($sql_comentario))
        {
            mensagemOK('Comentário adicionado com êxito!');
        }
        else
        {
            mensagemErro('Erro ao inserir comentario', 'Por favor, comunique a equipe de suporte informando o erro.');
        }
        
        desconectabd($conexao);
        break;    
}

?>
<link rel="stylesheet" type="text/css" media="screen"  href="../lib/css/tabs_simples.css" />

<!-- Seção do comentário -->
<script>
function validar_comentarios(frm)
{
    if (frm.novo_comentario.value == '')
    {
        alert("Campo comentário é obrigatório!");
        frm.novo_comentario.focus();
        return (false);
    }
    
	return (true);
}

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

function aprovar(cod_propostas) 
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
        'value': cod_propostas
    });
    
    var acao = new Element('input', 
    {
        'type': 'hidden',
        'name': 'acao',
        'value': 'aprovar'
    });
  
    input.inject(form);
    acao.inject(form);
    $(document.body).adopt(form);
  
    form.submit();
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
  
    if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) 
    {
        <?
        if ($acao == '')
        {
            echo 'tabs.irpara(1);';
        }
        ?>
    }
  
    tabs.addEvent('change', function(indice)
    {
        if(indice == 1)
        {
            document.frmIncluir.<? echo $chave_primaria ?>.value = '';
            
            document.frmComentario.<? echo $chave_primaria ?>.value = '';
      
      		$('comentarios').set('html', '<center><b>Sem Comentários</b></center>');
      
        }
    });
});

</script>

<div id="tabs">
<div class="menuTab">
<ul>
    <li><a href="javascript:;">Listar</a></li>
    <li><a href="javascript:;" style="display: none;">Incluir</a></li>
</ul>
</div>

<!-- Tab Editar -->
<div class="painelTab">

<?
if ($exibir_barra_lateral)
:
    ?>

<table>
    <tr>
        <!-- Conteúdo -->
        <td class="conteudo">

<? endif;
?>

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
                    <option value="<?
                        echo $campo_filtro_padrao?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Cliente</option>
                </select></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text" name="filtro" size="60"
                    value="<?
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
        
        $sql_buscar_registros = "SELECT f.*, c.data_hora_comentario FROM $tabela f LEFT JOIN ipi_interesse_comentarios c ON(f.cod_interesse_fraquia=c.cod_interesse_fraquia) WHERE f.$opcoes LIKE '%$filtro%'";
        
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' GROUP BY f.cod_interesse_fraquia ORDER BY f.' . $campo_ordenacao . ' DESC LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina. '';
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $linhas_buscar_registros = mysql_num_rows($res_buscar_registros);
        
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
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
            	<!-- 
                <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
                 -->
                 <td>&nbsp;</td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <!-- 
                    <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
                    --> 
                    <td align="center" width="400">Cliente</td>
                    <td align="center" width="150">Data do Cadastro</td>
                    <td align="center">Último Comentário</td>
					<td align="center" width="150">Total de Comentários</td>
					<td align="center" width="150">Situação</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
            	$sql_buscar_comentarios = "SELECT (SELECT nome FROM ipi_interesse_comentarios c INNER JOIN nuc_usuarios u ON(c.cod_usuarios=u.cod_usuarios) WHERE cod_interesse_fraquia=".$obj_buscar_registros->$chave_primaria." ORDER BY data_hora_comentario DESC LIMIT 1) AS nome_ultimo_comentario ,(SELECT comentario FROM ipi_interesse_comentarios WHERE cod_interesse_fraquia=".$obj_buscar_registros->$chave_primaria." ORDER BY data_hora_comentario DESC LIMIT 1) AS ultimo_comentario,(SELECT data_hora_comentario FROM ipi_interesse_comentarios WHERE cod_interesse_fraquia=".$obj_buscar_registros->$chave_primaria." ORDER BY data_hora_comentario DESC LIMIT 1) AS data_ultimo_comentario ,COUNT(*) AS total_comentarios FROM ipi_interesse_comentarios c INNER JOIN nuc_usuarios u ON(c.cod_usuarios=u.cod_usuarios) WHERE c.cod_interesse_fraquia=".$obj_buscar_registros->$chave_primaria;
            	$res_buscar_comentarios = mysql_query($sql_buscar_comentarios);
            	$obj_buscar_comentarios = mysql_fetch_object($res_buscar_comentarios);

            	echo '<tr>';
            	 
            	//echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->nome) . '</a></td>';
                echo '<td align="center">'.bd2datahora($obj_buscar_registros->data_cadastro).'</td>';
                echo '<td align="center"><small><i>'. $obj_buscar_comentarios->nome_ultimo_comentario.'</i> - '.bd2datahora($obj_buscar_comentarios->data_ultimo_comentario).'</small></td>';
              	echo '<td align="center">'.$obj_buscar_comentarios->total_comentarios.'</td>';
              	echo '<td align="center">'.$obj_buscar_registros->situacao.'</td>';
                echo '</tr>';
            }
            
            desconectabd($conexao);
            
            ?>
          
            </tbody>
        </table>

        <input type="hidden" name="acao" value="excluir"></form>

<?
if ($exibir_barra_lateral):
    ?>

        </td>
        <!-- Conteúdo -->

        <!-- Barra Lateral -->
        <td class="lateral">
        <div class="blocoNavegacao">
        <ul>
            <li><a href="#">Exemplo</a></li>
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
        $obj_editar = ExecutaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }
    ?>


<table align="center" class="caixa" cellpadding="0" cellspacing="0">
<form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
	<tr>
        <td class="legenda tdbl tdbt tdbr">
            <label class="requerido" for="nome">Nome</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="nome" id="nome" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->nome)?>">
        </td>
    </tr>
    
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="sexo">Sexo</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="sexo" id="sexo" maxlength="5" size="22" value="<?echo bd2texto($obj_editar->sexo)?>">
        </td>
	</tr>
	
	<tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="data_nascimento">Data Nascimento</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="data_nascimento" id="data_nascimento" maxlength="45" size="22" value="<?echo bd2data($obj_editar->data_nascimento)?>">
        </td>
    </tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="estado_civil">Estado Civil</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="estado_civil" id="estado_civil" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->estado_civil)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="tem_filhos">Tem Filhos?</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="tem_filhos" id="tem_filhos" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->tem_filhos)?>">
        </td>
	</tr>
	
	<tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="idades">Idades</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="idades" id="idades" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->idades)?>">
        </td>
    </tr>
    
	<tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="cep">CEP</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="cep" id="cep" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->cep)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="endereco">Endereço</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="endereco" id="endereco" maxlength="90" size="68" value="<?echo bd2texto($obj_editar->endereco)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="numero">Número</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="numero" id="numero" maxlength="10" size="22" value="<?echo bd2texto($obj_editar->numero)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="complemento">Complemento</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="complemento" id="complemento" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->complemento)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="bairro">Bairro</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="bairro" id="bairro" maxlength="100" size="68" value="<?echo bd2texto($obj_editar->bairro)?>">
        </td>
    </tr> 
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="cidade">Cidade</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="cidade" id="cidade" maxlength="100" size="68" value="<?echo bd2texto($obj_editar->cidade)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="estado">Estado</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="estado" id="estado" maxlength="5" size="10" value="<?echo bd2texto($obj_editar->estado)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="telefone">Telefone</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="telefone" id="telefone" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->telefone)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="telefone_comercial">Telefone Comercial</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="telefone_comercial" id="telefone_comercial" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->telefone_comercial)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="celular">Celular</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="celular" id="celular" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->celular)?>">
        </td>
    </tr> 
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="email">E-mail</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="email" id="email" maxlength="150" size="68" value="<?echo bd2texto($obj_editar->email)?>">
        </td>
    </tr>
    
    <tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="escolaridade">Escolaridade</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="escolaridade" id="escolaridade" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->escolaridade)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="profissao">Profissão</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="profissao" id="profissao" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->profissao)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="profissao_conjugue">Profissão Conjugue</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="profissao_conjugue" id="profissao_conjugue" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->profissao_conjugue)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="negocio_proprio">Negócio Próprio</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="negocio_proprio" id="negocio_proprio" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->negocio_proprio)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="qual_negocio">Qual Negócio?</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="qual_negocio" id="qual_negocio" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->qual_negocio)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="data_inicio_negocio">Data de início do negócio</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="data_inicio_negocio" id="data_inicio_negocio" maxlength="45" size="22" value="<?echo bd2data($obj_editar->data_inicio_negocio)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="data_saida_negocio">Data de saída do negócio</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="data_saida_negocio" id="data_saida_negocio" maxlength="45" size="22" value="<?echo bd2data($obj_editar->data_saida_negocio)?>">
        </td>
	</tr>
	         
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="interesses">Interesses</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="interesses" id="interesses" maxlength="45" size="68" value="<?echo bd2texto($obj_editar->interesses)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="papel_sociedade">Papel na sociedade</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="papel_sociedade" id="papel_sociedade" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->papel_sociedade)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="tempo_dedicacao">Tempo de Dedicação</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="tempo_dedicacao" id="tempo_dedicacao" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->tempo_dedicacao)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="regiao_interesse">Região de Interesse</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="regiao_interesse" id="regiao_interesse" maxlength="100" size="68" value="<?echo bd2texto($obj_editar->regiao_interesse)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="investimento">Investimento</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="investimento" id="investimento" maxlength="45" size="22" value="<?echo bd2moeda($obj_editar->investimento)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="como_conheceu">Como conheceu?</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <input class="requerido" type="text" name="como_conheceu" id="como_conheceu" maxlength="45" size="22" value="<?echo bd2texto($obj_editar->como_conheceu)?>">
        </td>
	</tr>
	
	<tr>
		<td class="legenda tdbl tdbr" colspan="2"><label class="requerido"	for="comentarios">Comentários</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">
            <textarea rows="7" cols="65" name="comentarios"><?echo bd2texto($obj_editar->comentarios)?></textarea>
        </td>
	</tr>
	

	<input type="hidden" name="acao" value="editar"> 
	<input type="hidden"  name="<?  echo $chave_primaria?>" value="<?  echo $codigo?>">
</form>

    <form name="frmComentario" method="post">
    
    <tr>
        <td class="tdbl tdbr">
		<br /><br /><label>Comentários</label>
        <hr noshade="noshade" size="1" color="#E06610">
        <br>
		</td>
    </tr>
    <tr>
        <td class="legenda tdbl tdbr sep" id="comentarios">
        
            <?
            
            $conexao = conectabd();
            
            $sql_buscar_comentarios = "SELECT * FROM ipi_interesse_comentarios c INNER JOIN nuc_usuarios l ON(c.cod_usuarios=l.cod_usuarios) WHERE cod_interesse_fraquia=".$codigo." ORDER BY data_hora_comentario";
            $res_buscar_comentarios = mysql_query($sql_buscar_comentarios);
            $num_buscar_comentarios = mysql_num_rows($res_buscar_comentarios);
            
            desconectabd($conexao);
            
            if($num_buscar_comentarios > 0)
            {
                for ($a = 0; $a < $num_buscar_comentarios; $a++):
                
                    $obj_buscar_comentarios= mysql_fetch_object($res_buscar_comentarios);
                    
                    $cod_fundo_comentario = ($a%2 == 0) ? '#EFEFEF' : '#DFDFDF' ;
                    
                    ?>
                    
                    <table width="375" align="center" style="margin: 0px auto; background-color: <? echo $cod_fundo_comentario ?>;">
                    
                    <tr>
                        <td>
                        <i><? echo bd2texto($obj_buscar_comentarios->nome); ?></i>
                        </td>
                        <td align="right">
                        <? echo bd2datahora($obj_buscar_comentarios->data_hora_comentario); ?>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2"><? echo nl2br(bd2texto($obj_buscar_comentarios->comentario)); ?></td>
                    </tr>
                    
                    </table>
                    
                    <?
                
                endfor;
            }
            else
            {
                echo '<center><b>Sem Comentários</b></center>';
            }
            
            ?>
            
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
		<br>
        <label>Novo Comentário</label>
        <hr noshade="noshade" size="1" color="#E06610">
        <br>
        <textarea rows="10" cols="65" name="novo_comentario" id="novo_comentario"></textarea>
		</td>
    </tr>
    
    <tr>
        <td align="center" class="tdbl tdbr tdbb">
            <input name="botao_submit_comentario" id="botao_submit_comentario" class="botao" type="button" value="Enviar Comentário" onclick=" if('<? echo $codigo ?>' != '') { document.frmComentario.submit(); } else { alert('Por favor, primeiro salve a proposta para depois comentar.'); } ">
        </td>
    </tr>
    
    <input type="hidden" name="acao" value="comentar"> 
    <input type="hidden" name="<?echo $chave_primaria?>" value="<?echo $codigo?>">
    </form>

</table>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>