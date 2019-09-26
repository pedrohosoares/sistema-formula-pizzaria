<?php

/**
 * Cadastro de Colaboradores.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       08/01/2009   Elias         Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Colaboradores');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_colaboradores';
$tabela = 'ipi_colaboradores';
$campo_ordenacao = 'nome';
$campo_filtro_padrao = 'nome';
$quant_pagina = 50;
$exibir_barra_lateral = false;

switch ($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indices_sql = implode(',', $excluir);
        
        $conexao = conectabd();
        
        $sql_del1 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indices_sql)";
        $res_del1 = mysql_query($sql_del1);
        
        if ($res_del1)
        {
            mensagemOK('Os registros selecionados foram excluídos com sucesso!');
        }
        else
        {
            mensagemErro('Erro ao excluir os registros');
        }
        
        desconectabd($conexao);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $cod_tipo_colaboradores = validaVarPost('cod_tipo_colaboradores');
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $nome = validaVarPost('nome');
        $razao_social = validaVarPost('razao_social');
        $cpf = validaVarPost('cpf');
        $cep = validaVarPost('cep');
        $endereco= validaVarPost('endereco');
        $numero = validaVarPost('numero');
        $complemento = validaVarPost('complemento');
        $bairro = validaVarPost('bairro');
        $cidade = validaVarPost('cidade');
        $estado = validaVarPost('estado');
        $email = validaVarPost('email');
        $telefone_residencial = validaVarPost('telefone_residencial');
        $telefone_recado = validaVarPost('telefone_recado');
        $celular = validaVarPost('celular');
        $contato_emergencia = validaVarPost('contato_emergencia');
        $telefone_emergencia = validaVarPost('telefone_emergencia');
        $salario = moeda2bd(validaVarPost('salario'));
        $comissao = moeda2bd(validaVarPost('comissao'));
        $agencia = validaVarPost('agencia');
        $conta_corrente = validaVarPost('conta_corrente');
        $banco = validaVarPost('banco');
        $situacao = validaVarPost('situacao');
        $data_nascimento = data2bd(validaVarPost('data_nascimento'));  
        $data_admissao = data2bd(validaVarPost('data_admissao'));  
        $data_demissao = data2bd(validaVarPost('data_demissao'));  
        $sexo = validaVarPost('sexo');
        $rg = validaVarPost('rg');
        $estado_civil = validaVarPost('estado_civil');
        $tem_filhos = validaVarPost('tem_filhos');
        $escolaridade = validaVarPost('escolaridade');
        $fumante = validaVarPost('fumante');
        $bebida_alcoolica = validaVarPost('bebida_alcoolica');
        $animais_estimacao = validaVarPost('animais_estimacao');
        $observacoes = validaVarPost('observacoes');
        
        $conexao = conectabd();
        
        if ($codigo <= 0)
        {
            $sql_edicao = sprintf("INSERT INTO $tabela (cod_tipo_colaboradores, cod_pizzarias, nome, email, cpf, cep, endereco, numero , complemento, bairro, cidade, estado, telefone_residencial, telefone_recado, celular, contato_emergencia, telefone_emergencia, salario, comissao, agencia, conta_corrente, banco, situacao, data_nascimento, data_admissao, data_demissao, sexo, rg, estado_civil, tem_filhos, escolaridade, fumante, bebida_alcoolica, animais_estimacao, observacoes) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
                $cod_tipo_colaboradores, $cod_pizzarias, $nome, $email, $cpf, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $telefone_residencial, $telefone_recado, $celular, $contato_emergencia, $telefone_emergencia, $salario, $comissao, $agencia, $conta_corrente, $banco, $situacao, $data_nascimento, $data_admissao, $data_demissao, $sexo, $rg, $estado_civil, $tem_filhos, $escolaridade, $fumante, $bebida_alcoolica, $animais_estimacao, $observacoes);
            
            if (mysql_query($sql_edicao))
            {
                mensagemOK('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        else
        {
            $sql_edicao = sprintf("UPDATE $tabela SET cod_tipo_colaboradores = '%s', cod_pizzarias = '%s', nome = '%s', email = '%s', cpf = '%s', cep = '%s', endereco = '%s', numero = '%s', complemento = '%s', bairro = '%s', cidade = '%s', estado = '%s', telefone_residencial = '%s', telefone_recado = '%s', celular = '%s', contato_emergencia = '%s', telefone_emergencia = '%s', salario = '%s', comissao = '%s', agencia = '%s', conta_corrente = '%s', banco = '%s', situacao = '%s', data_nascimento = '%s', data_admissao = '%s', data_demissao = '%s', sexo = '%s', rg = '%s', estado_civil = '%s', tem_filhos = '%s', escolaridade = '%s', fumante = '%s', bebida_alcoolica = '%s', animais_estimacao = '%s', observacoes = '%s' WHERE $chave_primaria = $codigo", 
            $cod_tipo_colaboradores, $cod_pizzarias, $nome, $email, $cpf, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $telefone_residencial, $telefone_recado, $celular, $contato_emergencia, $telefone_emergencia, $salario, $comissao, $agencia, $conta_corrente, $banco, $situacao, $data_nascimento, $data_admissao, $data_demissao, $sexo, $rg, $estado_civil, $tem_filhos, $escolaridade, $fumante, $bebida_alcoolica, $animais_estimacao, $observacoes);
            
            if (mysql_query($sql_edicao))
            {
                mensagemOK('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
        }
        
        //echo $sql_edicao;
        
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
            document.frmIncluir.<? echo $chave_primaria?>.value = '';
            document.frmIncluir.cod_pizzarias.value = '';
            document.frmIncluir.cod_tipo_colaboradores.value = '';
            document.frmIncluir.nome.value = '';
            document.frmIncluir.email.value = '';
            document.frmIncluir.cpf.value = '';
            document.frmIncluir.cep.value = '';
            document.frmIncluir.endereco.value = '';
            document.frmIncluir.numero.value = '';
            document.frmIncluir.complemento.value = '';
            document.frmIncluir.bairro.value = '';
            document.frmIncluir.cidade.value = '';
            document.frmIncluir.estado.value = '';
            document.frmIncluir.telefone_residencial.value = '';
            document.frmIncluir.telefone_recado.value = '';
            document.frmIncluir.celular.value = '';
            document.frmIncluir.contato_emergencia.value = '';
            document.frmIncluir.telefone_emergencia.value = '';
            document.frmIncluir.salario.value = '';
            document.frmIncluir.comissao.value = '';
            document.frmIncluir.agencia.value = '';
            document.frmIncluir.conta_corrente.value = '';
            document.frmIncluir.banco.value = '';
            document.frmIncluir.situacao.value = '';
      		  document.frmIncluir.data_nascimento.value = '';
            document.frmIncluir.data_admissao.value = '';
            document.frmIncluir.data_demissao.value = '';
        		document.frmIncluir.sexo.value = '';
        		document.frmIncluir.rg.value = '';
        		document.frmIncluir.estado_civil.value = '';
        		document.frmIncluir.tem_filhos.value = '';
        		document.frmIncluir.escolaridade.value = '';
        		document.frmIncluir.fumante.value = '';
        		document.frmIncluir.bebida_alcoolica.value = '';
        		document.frmIncluir.animais_estimacao.value = '';
            document.frmIncluir.botao_submit.value = 'Cadastrar';
        }
    });
});

function completar_endereco() 
{
    var cep = document.frmIncluir.cep.value;
    var url = 'cep=' + cep;
    
   
    if(cep != '') 
    {
        new Request.JSON({url: '../../ipi_completa_cep_ajax.php', onComplete: function(retorno) 
        {
            if(retorno.status == 'OK') 
            {
                document.frmIncluir.endereco.value = retorno.endereco;
                document.frmIncluir.bairro.value = retorno.bairro;
                document.frmIncluir.cidade.value = retorno.cidade;
                document.frmIncluir.estado.value = retorno.estado;
                
                document.frmIncluir.numero.focus();
            }
            else 
            {
                alert('Erro ao completar CEP: ' + retorno.mensagem);
            }
      }}).send(url);
    }
    else 
    {
        alert('Para completar o endereço o campo CEP deverá ter um valor válido.');
    }
}
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
        $situacao_busca = validaVarPost('situacao_busca');
        $situacao_busca = ($situacao_busca!="")?$situacao_busca:"Ativo";
        $cod_pizzaria =  validaVarPost('cod_pizzarias');
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
                        ?>>Nome</option>
                </select></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr>
						 <tr>
							<td class="legenda tdbl sep" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
							<td class="tdbt sep">&nbsp;</td>
							<td class="tdbr sep">
								<select name="cod_pizzarias" id="cod_pizzarias">
									<option value="">Todas as <? echo ucfirst(TIPO_EMPRESAS)?></option>
									<?
									$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

									$con = conectabd();
									$SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE situacao!='INATIVO' AND cod_pizzarias in ($cod_pizzarias_usuario ) ORDER BY nome";//cod_pizzarias IN ($cod_pizzarias_usuario)
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
            <tr>
                <td class="legenda tdbl" align="right">
                  Situação: 
                </td>
                <td class="">&nbsp;</td>
                <td class="tdbr">
                    <select name="situacao_busca">
                      <option value="TODOS" <? echo ($situacao_busca=="TODOS")?"selected='selected'":"" ?>>Todos</option>
                      <option value="ATIVO" <? echo ($situacao_busca=="ATIVO")?"selected='selected'":"" ?>>Ativo</option>
                      <option value="INATIVO" <? echo ($situacao_busca=="INATIVO")?"selected='selected'":"" ?>>Inativo</option>
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
        
        if($cod_pizzaria !="")
        {
       	 $sql_buscar_registros = "SELECT * FROM $tabela c WHERE c.cod_pizzarias = $cod_pizzaria AND c.$opcoes LIKE '%$filtro%' ";//IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")
        }
        else
        {
        	 $sql_buscar_registros = "SELECT * FROM $tabela c WHERE c.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND c.$opcoes LIKE '%$filtro%' ";//
        }
        if ($situacao_busca!="TODOS")
        {
          $sql_buscar_registros .= " AND c.situacao='".$situacao_busca."'";
        }
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);
        
        $sql_buscar_registros .= ' ORDER BY c.' . $campo_ordenacao . ' LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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
                    <td align="center" width="20">
                        <input type="checkbox" onclick="marcaTodos('marcar');">
                    </td>
                    <td align="center">Nome</td>
                    <td align="center">E-Mail</td>
                    <td align="center">CPF</td>
                    <td align="center">Telefone Residencial</td>
                    <td align="center">Celular</td>
                    <td align="center">Situação</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                
                echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->nome) . '</a></td>';
                echo '<td align="center">'.bd2texto($obj_buscar_registros->email).'</td>';
                echo '<td align="center">'.$obj_buscar_registros->cpf.'</td>';
                echo '<td align="center">'.$obj_buscar_registros->telefone_residencial.'</td>';
                echo '<td align="center">'.$obj_buscar_registros->celular.'</td>';
                echo '<td align="center">'.bd2texto($obj_buscar_registros->situacao).'</td>';

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
		<td class="legenda tdbl tdbt tdbr" colspan="2"><label class="requerido"
			for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?></label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr" colspan="2"><select name="cod_pizzarias"
			id="cod_pizzarias">
			<option value=""></option>
        <?
        $con = conectabd();
        
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
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
      </select></td>
	</tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
        <label class="requerido" for="cod_tipo_colaboradores">Tipo de Colaborador</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
          <select class="requerido" name="cod_tipo_colaboradores" id="cod_tipo_colaboradores" style="width: 260px;">
            <option value=""></option>
                <?
                $conexao = conectabd();
                
                $sql_buscar_tipos_colaboradores = "SELECT * FROM ipi_tipo_colaboradores ORDER BY tipo_colaboradores";
                $res_buscar_tipos_colaboradores = mysql_query($sql_buscar_tipos_colaboradores);
                
                while ($obj_buscar_tipos_colaboradores = mysql_fetch_object($res_buscar_tipos_colaboradores))
                {
                    echo '<option value="' . $obj_buscar_tipos_colaboradores->cod_tipo_colaboradores . '" ';
                    
                    if ($obj_buscar_tipos_colaboradores->cod_tipo_colaboradores == $obj_editar->cod_tipo_colaboradores)
                    {
                        echo 'selected';
                    }
                    
                    echo '>' . bd2texto($obj_buscar_tipos_colaboradores->tipo_colaboradores) . '</option>';
                }
                desconectabd($conexao);
                ?>
            </select></td>
    </tr>

    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="nome">Nome</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
            <input class="requerido" type="text" name="nome" id="nome" maxlength="45" size="45" value="<?echo bd2texto($obj_editar->nome)?>">
        </td>
    </tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="sexo">Sexo</label></td></tr>
    <tr><td class="tdbl tdbr">
    <select name="sexo" id="sexo">
        <option value=""></option>
        <option value="M" <? if($obj_editar->sexo == 'M') echo 'SELECTED' ?>>Masculino</option>
        <option value="F" <? if($obj_editar->sexo == 'F') echo 'SELECTED' ?>>Feminino</option>
    </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="data_nascimento">Data Nascimento</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" name="data_nascimento" id="data_nascimento" maxlength="14" size="9" value="<? echo bd2data($obj_editar->data_nascimento) ?>" onKeyPress="return MascaraData(this,event)"></td></tr>
    

    <tr><td class="legenda tdbl tdbr"><label for="data_admissao">Data Admissão</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" name="data_admissao" id="data_admissao" maxlength="14" size="9" value="<? echo bd2data($obj_editar->data_admissao) ?>" onKeyPress="return MascaraData(this,event)"></td></tr>
    

    <tr><td class="legenda tdbl tdbr"><label for="data_demissao">Data Demissão</label></td></tr>
    <tr><td class="tdbl tdbr sep"><input type="text" name="data_demissao" id="data_demissao" maxlength="14" size="9" value="<? echo bd2data($obj_editar->data_demissao) ?>" onKeyPress="return MascaraData(this,event)"></td></tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="email">E-Mail</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" name="email" id="email" maxlength="80" size="45" value="<?echo bd2texto($obj_editar->email)?>">
       </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="cpf">CPF</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" name="cpf" id="cpf" maxlength="14" size="45" onkeypress="return MascaraCPF(this, event);" value="<?echo bd2texto($obj_editar->cpf)?>">
        </td>
    </tr>
     
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="rg">RG</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" name="rg" id="rg" maxlength="25" size="45" value="<?echo bd2texto($obj_editar->rg)?>">
        </td>
    </tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="estado_civil">Relacionamento</label></td></tr>
    <tr><td class="tdbl tdbr">
    <select name="estado_civil" id="estado_civil">
        <option value=""></option>
        <option value="Solteiro(a)" <? if($obj_editar->estado_civil == 'Solteiro(a)') echo 'SELECTED' ?>>Solteiro(a)</option>
        <option value="Casado(a)" <? if($obj_editar->estado_civil == 'Casado(a)') echo 'SELECTED' ?>>Casado(a)</option>
        <option value="Namorando" <? if($obj_editar->estado_civil == 'Namorando') echo 'SELECTED' ?>>Namorando</option>
        <option value="Viúvo(a)" <? if($obj_editar->estado_civil == 'Viúvo(a)') echo 'SELECTED' ?>>Viúvo(a)</option>
        <option value="Divorciado(a)" <? if($obj_editar->estado_civil == 'Divorciado(a)') echo 'SELECTED' ?>>Divorciado(a)</option>
    </select>
    </td></tr>
   
   	<tr><td class="legenda tdbl tdbr"><label for="tem_filhos">Tem filhos</label></td></tr>
    <tr><td class="tdbl tdbr">
    <select name="tem_filhos" id="tem_filhos">
        <option value=""></option>
        <option value="Não" <? if($obj_editar->tem_filhos == 'Não') echo 'SELECTED' ?>>Não</option>
        <option value="Sim - Moram comigo" <? if($obj_editar->tem_filhos == 'Sim - Moram comigo') echo 'SELECTED' ?>>Sim - Moram comigo</option>
        <option value="Sim - Visitam de vez em quando" <? if($obj_editar->tem_filhos == 'Sim - Visitam de vez em quando') echo 'SELECTED' ?>>Sim - Visitam de vez em quando</option>
        <option value="Sim - Não moram comigo" <? if($obj_editar->tem_filhos == 'Sim - Não moram comigo') echo 'SELECTED' ?>>Sim - Não moram comigo</option>
    </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="escolaridade">Escolaridade</label></td></tr>
    <tr><td class="tdbl tdbr">
    <select name="escolaridade" id="escolaridade">
        <option value=""></option>
        <option value="Ensino fundamental" <? if($obj_editar->escolaridade == 'Ensino fundamental') echo 'SELECTED' ?>>Ensino fundamental</option>
        <option value="Ensino fundamental incompleto" <? if($obj_editar->escolaridade == 'Ensino fundamental incompleto') echo 'SELECTED' ?>>Ensino fundamental incompleto</option>
        <option value="Ensino médio" <? if($obj_editar->escolaridade == 'Ensino médio') echo 'SELECTED' ?>>Ensino médio</option>
        <option value="Ensino fundamental incompleto" <? if($obj_editar->escolaridade == 'Ensino fundamental incompleto') echo 'SELECTED' ?>>Ensino fundamental incompleto</option>
        <option value="Superior Incompleto" <? if($obj_editar->escolaridade == 'Superior Incompleto') echo 'SELECTED' ?>>Superior Incompleto</option>
        <option value="Título de Tecnólogo" <? if($obj_editar->escolaridade == 'Título de Tecnólogo') echo 'SELECTED' ?>>Título de Tecnólogo</option>
        <option value="Superior" <? if($obj_editar->escolaridade == 'Superior') echo 'SELECTED' ?>>Superior</option>
        <option value="Mestrado" <? if($obj_editar->escolaridade == 'Mestrado') echo 'SELECTED' ?>>Mestrado</option>
        <option value="Doutorado" <? if($obj_editar->escolaridade == 'Doutorado') echo 'SELECTED' ?>>Doutorado</option>
        <option value="Pós-doutorado" <? if($obj_editar->escolaridade == 'Pós-doutorado') echo 'SELECTED' ?>>Pós-doutorado</option>
    </select>
    </td></tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="cep">CEP</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
            <input type="text" name="cep" id="cep" onkeypress="return MascaraCEP(this, event);" size="12" value="<? echo $obj_editar->cep ?>">
            &nbsp;&nbsp;&nbsp;
            <input type="button" style="width: 150px;" class="botaoAzul" onclick="completar_endereco()" value="Completar Endereço"/>
        </td>
    </tr>
    
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="endereco">Endereço</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
        <input type="text" name="endereco" id="endereco" maxlength="80" size="45" value="<? echo bd2texto($obj_editar->endereco) ?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl  tdbr">
            <label for="numero">Número</label>
            <label style="padding-left: 78px;" for="complemento">Complemento</label>
        </td>
    </tr>
    <tr>  
       <td class="legenda tdbl  tdbr">   
          <input type="text" name="numero" id="numero" maxlength="10" size="16" value="<? echo $obj_editar->numero ?>">
          &nbsp;&nbsp;
          <input type="text" name="complemento" id="complemento" maxlength="20" size="20" value="<? echo bd2texto($obj_editar->complemento) ?>">
       </td>
     </tr>       
        
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="bairro">Bairro</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
        <input type="text" name="bairro" id="bairro" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->bairro) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="cidade">Cidade</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
        <input type="text" name="cidade" id="cidade" maxlength="45" size="45" value="<? echo bd2texto($obj_editar->cidade) ?>"></td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr"><label for="estado">Estado</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <select name="estado">
                <option value=""/>
                <option value="AC" <? if($obj_editar->estado == 'AC') echo 'SELECTED' ?>>AC</option>
                <option value="AL" <? if($obj_editar->estado == 'AL') echo 'SELECTED' ?>>AL</option>
                <option value="AP" <? if($obj_editar->estado == 'AP') echo 'SELECTED' ?>>AP</option>
                <option value="AM" <? if($obj_editar->estado == 'AM') echo 'SELECTED' ?>>AM</option>
                <option value="BA" <? if($obj_editar->estado == 'BA') echo 'SELECTED' ?>>BA</option>
                <option value="CE" <? if($obj_editar->estado == 'CE') echo 'SELECTED' ?>>CE</option>
                <option value="DF" <? if($obj_editar->estado == 'DF') echo 'SELECTED' ?>>DF</option>
                <option value="ES" <? if($obj_editar->estado == 'ES') echo 'SELECTED' ?>>ES</option>
                <option value="GO" <? if($obj_editar->estado == 'GO') echo 'SELECTED' ?>>GO</option>
                <option value="MA" <? if($obj_editar->estado == 'MA') echo 'SELECTED' ?>>MA</option>
                <option value="MT" <? if($obj_editar->estado == 'MT') echo 'SELECTED' ?>>MT</option>
                <option value="MS" <? if($obj_editar->estado == 'MS') echo 'SELECTED' ?>>MS</option>
                <option value="MG" <? if($obj_editar->estado == 'MG') echo 'SELECTED' ?>>MG</option>
                <option value="PA" <? if($obj_editar->estado == 'PA') echo 'SELECTED' ?>>PA</option>
                <option value="PB" <? if($obj_editar->estado == 'PB') echo 'SELECTED' ?>>PB</option>
                <option value="PR" <? if($obj_editar->estado == 'PR') echo 'SELECTED' ?>>PR</option>
                <option value="PE" <? if($obj_editar->estado == 'PE') echo 'SELECTED' ?>>PE</option>
                <option value="PI" <? if($obj_editar->estado == 'PI') echo 'SELECTED' ?>>PI</option>
                <option value="RJ" <? if($obj_editar->estado == 'RJ') echo 'SELECTED' ?>>RJ</option>
                <option value="RN" <? if($obj_editar->estado == 'RN') echo 'SELECTED' ?>>RN</option>
                <option value="RS" <? if($obj_editar->estado == 'RS') echo 'SELECTED' ?>>RS</option>
                <option value="RO" <? if($obj_editar->estado == 'RO') echo 'SELECTED' ?>>RO</option>
                <option value="RR" <? if($obj_editar->estado == 'RR') echo 'SELECTED' ?>>RR</option>
                <option value="SC" <? if($obj_editar->estado == 'SC') echo 'SELECTED' ?>>SC</option>
                <option value="SP" <? if($obj_editar->estado == 'SP') echo 'SELECTED' ?>>SP</option>
                <option value="SE" <? if($obj_editar->estado == 'SE') echo 'SELECTED' ?>>SE</option>
                <option value="TO" <? if($obj_editar->estado == 'TO') echo 'SELECTED' ?>>TO</option>
            </select>
        </td>
    </tr>

     <tr>
        <td class="legenda tdbl tdbr">
            <label for="telefone_residencial">Telefone Residêncial</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
            <input type="text" onkeypress="return MascaraTelefone(this, event);" name="telefone_residencial" id="telefone_residencial" maxlength="14" size="30"  value="<?echo bd2texto($obj_editar->telefone_residencial)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
        <label for="telefone_recado">Telefone Recado</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" onkeypress="return MascaraTelefone(this, event);" name="telefone_recado" id="telefone_recado" maxlength="14" size="30"  value="<?echo bd2texto($obj_editar->telefone_recado)?>">
        </td>
    </tr>
    
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="celular">Celular</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input onkeypress="return MascaraTelefone(this, event);" type="text" name="celular" id="celular" maxlength="45" size="30" value="<?echo bd2texto($obj_editar->celular)?>">
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="contato_emergencia">Contato de Emergência</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
            <input type="text" name="contato_emergencia" id="contato_emergencia" maxlength="45" size="45" value="<?echo bd2texto($obj_editar->contato_emergencia)?>">
            
        </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="telefone_emergencia">Telefone de Emergência</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" onkeypress="return MascaraTelefone(this, event);" name="telefone_emergencia" id="telefone_emergencia" maxlength="14" size="20" value="<?echo bd2texto($obj_editar->telefone_emergencia)?>">
       </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="salario">Salário</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
            <input type="text" onKeyPress="return formataMoeda(this, '.', ',', event)" name="salario" id="salario" maxlength="20" size="30" value="<? echo bd2moeda($obj_editar->salario) ?>">
       </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="comissao">Comissão (%)</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" onKeyPress="return formataMoeda(this, '', ',', event)" name="comissao" id="comissao" maxlength="20" size="30" value="<?echo bd2moeda($obj_editar->comissao)?>">
       </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="banco">Banco</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr">
            <input type="text" name="banco" id="banco" maxlength="45" size="40" value="<?echo bd2texto($obj_editar->banco)?>">
       </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="agencia">Agência</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
            <input type="text" name="agencia" id="agencia" maxlength="20" size="40" value="<?echo bd2texto($obj_editar->agencia)?>">
       </td>
    </tr>
    
    <tr>
        <td class="legenda tdbl tdbr">
            <label for="conta_corrente">Conta Corrente</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
            <input type="text" name="conta_corrente" id="conta_corrente" maxlength="20" size="40" value="<?echo bd2texto($obj_editar->conta_corrente)?>">
       </td>
    </tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="fumante">Fumante</label></td></tr>
    <tr><td class="tdbl tdbr">
    <select name="fumante" id="fumante">
        <option value=""></option>
        <option value="Não" <? if($obj_editar->fumante == 'Não') echo 'SELECTED' ?>>Não</option>
        <option value="Socialmente" <? if($obj_editar->fumante == 'Socialmente') echo 'SELECTED' ?>>Socialmente</option>
        <option value="De vez em quando" <? if($obj_editar->fumante == 'De vez em quando') echo 'SELECTED' ?>>De vez em quando</option>
        <option value="Regularmente" <? if($obj_editar->fumante == 'Regularmente') echo 'SELECTED' ?>>Regularmente</option>
        <option value="Excessivamente" <? if($obj_editar->fumante == 'Excessivamente') echo 'SELECTED' ?>>Excessivamente</option>
        <option value="Tentando parar" <? if($obj_editar->fumante == 'Tentando parar') echo 'SELECTED' ?>>Tentando parar</option>
        <option value="Ex-fumante" <? if($obj_editar->fumante == 'Ex-fumante') echo 'SELECTED' ?>>Ex-fumante</option>
    </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="bebida_alcoolica">Bebida Alcoólica</label></td></tr>
    <tr><td class="tdbl tdbr">
    <select name="bebida_alcoolica" id="bebida_alcoolica">
        <option value=""></option>
        <option value="Não" <? if($obj_editar->bebida_alcoolica == 'Não') echo 'SELECTED' ?>>Não</option>
        <option value="Socialmente" <? if($obj_editar->bebida_alcoolica == 'Socialmente') echo 'SELECTED' ?>>Socialmente</option>
        <option value="De vez em quando" <? if($obj_editar->bebida_alcoolica == 'De vez em quando') echo 'SELECTED' ?>>De vez em quando</option>
        <option value="Regularmente" <? if($obj_editar->bebida_alcoolica == 'Regularmente') echo 'SELECTED' ?>>Regularmente</option>
        <option value="Excessivamente" <? if($obj_editar->bebida_alcoolica == 'Excessivamente') echo 'SELECTED' ?>>Excessivamente</option>
        <option value="Tentando parar" <? if($obj_editar->bebida_alcoolica == 'Tentando parar') echo 'SELECTED' ?>>Tentando parar</option>
        <option value="Ex-alcoólatra" <? if($obj_editar->bebida_alcoolica == 'Ex-alcoólatra') echo 'SELECTED' ?>>Ex-alcoólatra</option>
    </select>
    </td></tr>
    
    <tr><td class="legenda tdbl tdbr"><label for="animais_estimacao">Animais de Estimação</label></td></tr>
    <tr><td class="tdbl tdbr sep">
    <select name="animais_estimacao" id="animais_estimacao">
        <option value=""></option>
        <option value="Não gosto de animais" <? if($obj_editar->animais_estimacao == 'Não gosto de animais') echo 'SELECTED' ?>>Não gosto de animais</option>
        <option value="Não, mas teria" <? if($obj_editar->animais_estimacao == 'Não, mas teria') echo 'SELECTED' ?>>Não, mas teria</option>
        <option value="Não, mas já tive" <? if($obj_editar->animais_estimacao == 'Não, mas já tive') echo 'SELECTED' ?>>Não, mas já tive</option>
        <option value="Sim, tenho um" <? if($obj_editar->animais_estimacao == 'Não') echo 'SELECTED' ?>>Sim, tenho um</option>
        <option value="Sim, tenho Vários" <? if($obj_editar->animais_estimacao == 'Sim, tenho Vários') echo 'SELECTED' ?>>Sim, tenho Vários</option>
    </select>
    </td></tr>
   

    <tr>
        <td class="legenda tdbl tdbr">
            <label for="observacoes">Observações</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr ">
            <textarea name="observacoes" cols="35" rows="6"><?echo bd2texto($obj_editar->observacoes)?></textarea>
       </td>
    </tr>



    <tr>
        <td class="legenda tdbl tdbr">
            <label class="requerido" for="situacao">Situação</label>
        </td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep">
        <select class="requerido" name="situacao" id="situacao">
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

<input type="hidden" name="acao" value="editar"> <input type="hidden"
    name="<?
    echo $chave_primaria?>" value="<?
    echo $codigo?>"></form>

</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>
