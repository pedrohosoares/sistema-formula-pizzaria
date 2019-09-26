<?php

/**
 * Cadastro de Marcas de Ingredientes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       01/06/2010   FELIPE        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Marcas de Ingredientes');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_ingredientes_marcas';
$tabela = 'ipi_ingredientes_marcas';
$campo_ordenacao = 'ingrediente_marca';
$campo_filtro_padrao = 'ingrediente_marca';
$quant_pagina = 50;
$exibir_barra_lateral = true;

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
        $cod_ingredientes = validaVarPost('cod_ingredientes');
        $ingrediente_marca = validaVarPost('ingrediente_marca');
        $quantidade = validaVarPost('quantidade');
        $situacao = validaVarPost('situacao');
        
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_ingredientes, ingrediente_marca, quantidade, situacao) VALUES ('%s', '%s', '%s', '%s')", $cod_ingredientes, $ingrediente_marca, $quantidade, $situacao);
            echo $sql_edicao;
            
            if (mysql_query($sql_edicao))
            {
                mensagemOk('Registro adicionado com êxito!');
            }
            else
            {
                exibir_mensagem_erro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_ingredientes = '%s', ingrediente_marca = '%s', quantidade = '%s', situacao = '%s' WHERE $chave_primaria = $codigo", $cod_ingredientes, $ingrediente_marca, $quantidade, $situacao);
            
            if (mysql_query($sql_edicao))
            {
                mensagemOk('Registro adicionado com êxito!');
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
  
    if (document.frmIncluir.<? echo $chave_primaria?>.value > 0) 
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
            document.frmIncluir.cod_ingredientes.value = '';
            document.frmIncluir.ingrediente_marca.value = '';
            document.frmIncluir.quantidade.value = '';
      
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
                        ?>>Marca</option>
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
        
        $sql_buscar_registros = "SELECT * FROM $tabela m INNER JOIN ipi_ingredientes i ON (m.cod_ingredientes = i.cod_ingredientes) INNER JOIN ipi_ingredientes_unidade_padrao up ON (m.cod_ingredientes_unidade_padrao = up.cod_ingredientes_unidade_padrao) WHERE $opcoes LIKE '%$filtro%' ";
        
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
                <td><input class="botaoAzul" type="submit"
                    value="Excluir Selecionados"></td>
            </tr>
        </table>

        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <td align="center" width="20"><input type="checkbox"
                        onclick="marcaTodos('marcar');"></td>
                    <td align="center">Marca</td>
                    <td align="center">Ingrediente</td>
                    <td align="center">Quantidade</td>
                    <td align="center">Situação</td>
                </tr>
            </thead>
            <tbody>
          
            <?
             $con = conectabd();
          
                $Sql_buscar_registros = "SELECT * FROM $tabela  ";
                $res_buscar_registros = mysql_query($Sql_buscar_registros );

            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->ingrediente_marca) . '</a></td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->cod_ingrediente) . '</td>';
                echo '<td align="center">' . bd2texto($obj_buscar_registros->quantidade) . '</td>';
                   echo '<td align="center">' . bd2texto($obj_buscar_registros->situacao) . '</td>';
                
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
         <li><a href="ipi_adicional.php">Adicionais</a></li>
          <li><a href="ipi_borda.php">Bordas</a></li>
          <li><a href="ipi_pizza.php"><? echo ucfirst(TIPO_PRODUTOS)?></a></li>
          <li><a href="ipi_tamanho.php">Tamanhos</a></li>
          <li><a href="ipi_unidade_padrao.php">Unidade Padrão</a></li>
          <li><a href="ipi_ingrediente_marcas.php">Ingredientes - Marcas</a></li>
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
    
    <form name="frmIncluir" method="post"
    onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido"
            for="cod_ingredientes">Ingrediente</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        	<select name="cod_ingredientes" id="cod_ingredientes" class="requerido" style="width: 300px;">
        		<option value=""></option>
        		
        		<?php 
        		
        		$conexao = conectabd();
        		
        		$sql_buscar_ingredientes = "SELECT * FROM ipi_ingredientes WHERE ativo = 1 ORDER BY ingrediente";
        		$res_buscar_ingredientes = mysql_query($sql_buscar_ingredientes);
        		
        		while($obj_buscar_ingredientes = mysql_fetch_object($res_buscar_ingredientes))
        		{
        			echo '<option value="' . $obj_buscar_ingredientes->cod_ingredientes . '" ';
        			
        			if($obj_editar->cod_ingredientes == $obj_buscar_ingredientes->cod_ingredientes)
        			{
        				echo 'selected';
        			}
        			
        			echo '>' . bd2texto($obj_buscar_ingredientes->ingrediente) . '</option>';	
        		}
        		
        		desconectabd($conexao);
        		
        		?>
        	</select>
		</td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="ingrediente_marca">Marca</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text"
            name="ingrediente_marca" id="ingrediente_marca" maxlength="45" size="45"
            value="<? echo bd2texto($obj_editar->ingrediente_marca) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="quantidade">Quantidade na Embalagem</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text"
            name="quantidade" id="quantidade" maxlength="10" size="33"
            value="<? echo bd2texto($obj_editar->quantidade) ?>" onkeypress="return ApenasNumero(event)"></td>
    </tr>

    <tr>
        <td class="legenda tdbl  tdbr">
            <label class="requerido" for="situacao">Situação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="situacao" id="situacao">
               <?
        $con = conectabd();
        
        $SqlBuscaConteudos = "SELECT cod FROM ipi_conteudos ORDER BY conteudo";
        $resBuscaConteudos = mysql_query($SqlBuscaConteudos);
        
        while($objBuscaConteudos = mysql_fetch_object($resBuscaConteudos)) {
          echo '<option value="'.$objBuscaConteudos->cod_conteudos.'" ';
          
          if($objBuscaConteudos->cod_conteudos == $objBusca->cod_conteudos)
            echo 'selected';
            
          echo '>'.bd2texto($objBuscaConteudos->conteudo).'</option>';
        }
        
        desconectabd($con);
        ?>
            <option value=""></option>
            <option value="ATIVO" <? if($obj_editar->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
            <option value="INATIVO" <? if($obj_editar->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
        </select>
        </td>
    </tr>

	<tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
            class="botao" type="submit" value="Cadastrar"></td>
    </tr>

</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<? echo $chave_primaria?>" value="<? echo $codigo?>"></form>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>