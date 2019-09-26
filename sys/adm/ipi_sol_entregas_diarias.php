<?php

/**
 * Cadastro de Entregas Avulsas.
 *
 * @version 1.0
 * @package ipizza
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       04/07/2011   Thiago         ReMake - A tela só tinha cadastro sem listar, refiz a tela com listar, vou deixar sem editar
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Registrar Diárias Entregador');

$acao = validaVarPost('acao');

$tabela = 'ipi_entregas_avulsas';
$chave_primaria = 'cod_entregas_avulsas';

$campo_ordenacao = 'data_hora_entrega';
$campo_filtro_padrao = 'cod_entregas_avulsas';
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
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $cod_entregadores = validaVarPost('cod_entregadores');
        $data_entrega = validaVarPost('data_entrega');
        $valor = validaVarPost("valor");
        $obs_entrega_avulsa = validaVarPost('obs_entrega_avulsa');
        $data_hora_entrega = data2bd($data_entrega);

        $conexao = conectabd();
        
        if ($codigo <= 0)
        {

            $sql_edicao = sprintf("INSERT INTO $tabela (cod_pizzarias, cod_entregadores, data_hora_entrega,valor, obs_entrega_avulsa,tipo_entrega) VALUES ('%s', '%s', '%s','%s', '%s','DIARIA')", $cod_pizzarias, $cod_entregadores, $data_hora_entrega,moeda2bd($valor), $obs_entrega_avulsa);
            //echo $sql_edicao;
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
            $sql_edicao = sprintf("UPDATE $tabela SET motivo_promocao = '%s', situacao = '%s' WHERE $chave_primaria = $codigo", $motivo_promocao, $situacao);
            
            if (mysql_query($sql_edicao))
            {
                mensagemOK('Registro adicionado com êxito!');
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
<script type="text/javascript" src="../lib/js/mascara.js"></script>
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

function carregar_entregadores_filtro()
{
  var url = "acao=carregar_entregadores&cod_pizzarias="+$('cod_pizzarias_filtro').value;
  new Request.HTML(
  {
      url: 'ipi_sol_entregas_diarias_ajax.php',
      update: $('cod_entregadores_filtro')
  }).send(url);
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
            document.frmIncluir.cod_entregadores.value = '';
            document.frmIncluir.obs_entrega_avulsa.value = '';
            document.frmIncluir.botao_submit.value = 'Cadastrar';
        }
    });
});

window.addEvent('domready', function() { 
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
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
        $data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-01');
        $data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-t');
        $cod_pizzarias = validaVarPost('cod_pizzarias');
        $cod_entregadores = validaVarPost('cod_entregadores');
        $opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : $campo_filtro_padrao;
        $filtro = validaVarPost('filtro');
        ?>
        
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">
<!--             <tr>
                <td class="legenda tdbl tdbt" align="right"><select
                    name="opcoes">
                    <option value="<? echo $campo_filtro_padrao ?>"
                        <?
                        if ($opcoes == $campo_filtro_padrao)
                        {
                            echo 'selected';
                        }
                        ?>>Motivo Promoção</option>
                </select></td>
                <td class="tdbt">&nbsp;</td>
                <td class="tdbt tdbr"><input type="text"
                    name="filtro" size="60" value="<?
                    echo $filtro?>"></td>
            </tr> -->
            <tr>
              <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias_filtro">Pizzaria:</label></td>
              <td class="tdbt">&nbsp;</td>
              <td class="tdbr tdbt">
                <select name="cod_pizzarias" id="cod_pizzarias_filtro"  onChange="javascript:carregar_entregadores_filtro()">
                  <option value="">Todas as Pizzarias</option>
                  <?
                  $con = conectabd();
                  
                  $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.nome";
                  $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
                  
                  while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
                    echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                    
                    if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
                      echo 'selected';
                    
                    echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
                  }
                  ?>
                </select>
              </td>
            </tr>

            <tr>
              <td class="legenda tdbl" align="right"><label for="cod_entregadores_filtro">Entregador:</label></td>
            <td class="">&nbsp;</td>
            <td class="tdbr">
            <select name="cod_entregadores" id="cod_entregadores_filtro">
                <? if($cod_pizzarias > 0)
                {
                  echo '<option value=""></option>';
                  $sql_buscar_entregadores = "SELECT e.* FROM ipi_entregadores e WHERE e.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND e.cod_pizzarias = '".$cod_pizzarias."' ORDER BY e.numero_cadastro, e.nome";
                  //echo "<option>".$sql_buscar_entregadores."</option>";
                  $res_buscar_entregadores = mysql_query($sql_buscar_entregadores);
                  
                  while($obj_buscar_entregadores = mysql_fetch_object($res_buscar_entregadores))
                  {
                      echo '<option value="' . $obj_buscar_entregadores->cod_entregadores . '"';
                      if($cod_entregadores==$obj_buscar_entregadores->cod_entregadores)
                      {
                        echo " SELECTED='SELECTED' ";
                      }
                      echo '>' . bd2texto($obj_buscar_entregadores->numero_cadastro) . ' - ' . bd2texto($obj_buscar_entregadores->nome) . '</option>';
                  }
                }
                else
                {
                  echo '<option value="">Selecione uma Pizzaria</option>';
                }
                ?>
                </select>
              </td>
            </tr>


            <tr>
              <td class="legenda tdbl" align="right"><label for="data_inicial">Data Inicial:</label></td>
              <td>&nbsp;</td>
              <td class="tdbr"><input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo bd2data($data_inicial) ?>" onkeypress="return MascaraData(this, event)">
              &nbsp;
              <a href="javascript:;" id="botao_data_inicial"><img src="../lib/img/principal/botao-data.gif"></a>
              </td>
            </tr>
            

            <tr>
              <td class="legenda tdbl" align="right"><label for="data_final">Data Final:</label></td>
              <td>&nbsp;</td>
              <td class="tdbr">
              <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo bd2data($data_final) ?>" onkeypress="return MascaraData(this, event)">
              &nbsp;
              <a href="javascript:;" id="botao_data_final"><img src="../lib/img/principal/botao-data.gif"></a>
              </td>
            </tr>
            <tr>
                <td align="right" class="tdbl tdbb tdbr" colspan="3"><input
                    class="botaoAzul" type="submit" value="Buscar"></td>
            </tr>
        </table>
        <input type="hidden" name="acao" value="buscar">
        </form>
        

        <br>

        <?
        
        $conexao = conectabd();
        
        $sql_buscar_registros = "SELECT ea.*, e.nome nome_entregador, p.nome nome_pizzaria FROM $tabela ea INNER JOIN ipi_entregadores e ON (ea.cod_entregadores = e.cod_entregadores) INNER JOIN ipi_pizzarias p ON (ea.cod_pizzarias = p.cod_pizzarias) WHERE $opcoes LIKE '%$filtro%' ";
        $sql_buscar_registros .= " AND ea.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") AND ea.tipo_entrega = 'DIARIA' and ea.data_hora_entrega BETWEEN '$data_inicial 00:00:00' AND '$data_final 23:59:59'";
        if($cod_pizzarias>0)
        {
          $sql_buscar_registros.= " AND ea.cod_pizzarias = '".$cod_pizzarias."'";
        }
        if($cod_entregadores>0)
        {
          $sql_buscar_registros.= " AND ea.cod_entregadores = '".$cod_entregadores."'";
        }
        $res_buscar_registros = mysql_query($sql_buscar_registros);
        $num_buscar_registros = mysql_num_rows($res_buscar_registros);

        
        $sql_buscar_registros .= ' ORDER BY ' . $campo_ordenacao . ' DESC LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
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
            echo '<input type="hidden" name="cod_pizzarias" value="' . $cod_pizzarias . '">';
            echo '<input type="hidden" name="data_inicial" value="' . data2bd($data_inicial) . '">';
            echo '<input type="hidden" name="data_final" value="' . data2bd($data_final) . '">';
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
<!--
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <tr>
                <td><input class="botaoAzul" type="submit"
                    value="Excluir Selecionados"></td>
            </tr>
        </table>
-->
        <table class="listaEdicao" cellpadding="0" cellspacing="0"
            width="<?
            echo LARGURA_PADRAO?>">
            <thead>
                <tr>
                    <!--
                    <td align="center" width="20">
                      <input type="checkbox" onclick="marcaTodos('marcar');">
                    </td>
                    -->
                    <td align="center">Pizzaria</td>
                    <td align="center">Entregador</td>
                    <td align="center">Data Diária</td>
                    <td align="center">Valor</td>
                    <td align="center">OBS</td>
                </tr>
            </thead>
            <tbody>
          
            <?
            
            while ($obj_buscar_registros = mysql_fetch_object($res_buscar_registros))
            {
                echo '<tr>';
                //echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $obj_buscar_registros->$chave_primaria . '"></td>';
                //echo '<td align="center"><a href="#" onclick="editar(' . $obj_buscar_registros->$chave_primaria . ')">' . bd2texto($obj_buscar_registros->motivo_promocao) . '</a></td>';
                echo '<td align="center">'.bd2texto($obj_buscar_registros->nome_pizzaria).'</td>';
                echo '<td align="center">'.bd2texto($obj_buscar_registros->nome_entregador).'</td>';
                echo '<td align="center">'.date("d/m/Y", strtotime($obj_buscar_registros->data_hora_entrega)).'</td>';
                echo '<td align="center">'.bd2moeda($obj_buscar_registros->valor).'</td>';
                echo '<td align="center">'.bd2texto($obj_buscar_registros->obs_entrega_avulsa).'</td>';
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

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>

<script type="text/javascript" src="../lib/js/calendario.js"></script>

<script>
function carregar_entregadores()
{
	var url = "acao=carregar_entregadores&cod_pizzarias="+$('cod_pizzarias').value;
  new Request.HTML(
  {
      url: 'ipi_sol_entregas_diarias_ajax.php',
      update: $('cod_entregadores')
  }).send(url);
}

window.addEvent('domready', function()
{
    new vlaDatePicker('data_entrega', {openWith: 'botao_data_entrega', prefillDate: false});
});
</script>
    
    <form name="frmIncluir" method="post"
    onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">


	<tr>
		<td class="legenda tdbl tdbt tdbr"><label class="requerido"	for="cod_pizzarias">Pizzaria</label></td>
	</tr>
  <tr>
    <td class="tdbr tdbl sep">
      <select name="cod_pizzarias" id="cod_pizzarias" onChange="javascript:carregar_entregadores()">
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
        ?>
      </select>
    </td>
  </tr>



	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"	for="cod_entregadores">Entregador</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep">

			<select name="cod_entregadores" id="cod_entregadores">
				<option value="">Selecione uma Pizzaria</option>
			</select>

		</td>
	</tr>


	
	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido" for="data_entrega">Data</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr">
      <input class="requerido" type="text" name="data_entrega" id="data_entrega" maxlength="10" size="10"	value="<? echo date('d/m/Y') ?>" onkeypress="return MascaraData(this, event);">
      &nbsp;
      <a href="javascript:;" id="botao_data_entrega"><img src="../lib/img/principal/botao-data.gif"></a>
    </td>
	</tr>
	
	<!-- <tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="hora_entrega">Hora</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><input class="requerido" type="text"
			name="hora_entrega" id="hora_entrega" maxlength="5" size="5"
			value="<? echo date('H:i') ?>"  onkeypress="return MascaraHora(this, event);"></td>
	</tr> -->

    <?
      $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias p WHERE p.cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY p.cod_pizzarias";
      $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
      $objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias);

      $cidade = $objBuscaPizzarias->cidade;
      $estado = $objBuscaPizzarias->estado;
    ?>

	<!-- <tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="bairro">Bairro</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><input class="requerido" type="text"
			name="bairro" id="bairro" maxlength="45" size="53"></td>
	</tr>
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="cidade">Cidade</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text"
            name="cidade" id="cidade" value="<? echo $cidade ?>" maxlength="45" size="53"></td>
    </tr>
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="estado">Estado</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><input class="requerido" type="text"
            name="estado" id="estado"  value="<? echo $estado;?>"  maxlength="45" size="53"></td>
    </tr> -->
    <tr>
        <td class="legenda tdbl tdbr"><label class="requerido"
            for="valor">Diária</label></td>
    </tr>
    <tr>
        <td class="tdbl tdbr sep"><select name="valor"><option value='15'>15,00</option><option value='20'>20,00</option></td>
    </tr>
	
	<tr>
		<td class="legenda tdbl tdbr"><label
			for="obs_entrega_avulsa">Observação</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><input type="text"
			name="obs_entrega_avulsa" id="obs_entrega_avulsa" maxlength="250" size="53"></td>
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
