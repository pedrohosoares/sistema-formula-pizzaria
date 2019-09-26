<?php

/**
 * ipi_conteudo.php: Cadastro Categorias Comentarios
 * 
 * Índice: cod_categorias_comentarios
 * Tabela: ipi_categorias_comentarios
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Responder Fale Conosco');

$acao = validaVarPost('acao');

$tabela = 'ipi_fale_conosco';
$chave_primaria = 'cod_fale_conosco';
$quant_pagina = 50;
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
switch($acao)
{
    case 'excluir':
        $excluir = validaVarPost('excluir');
        $indicesSql = implode(',',$excluir);
        
        $con = conectabd();
        
        $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
        
        if(mysql_query($SqlDel))
            mensagemOk('Os registros selecionados foram excluídos com sucesso!');
        else
            mensagemErro('Erro ao excluir os registros','Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        
        desconectabd($con);
        break;
    case 'editar':
        $codigo = validaVarPost($chave_primaria);
        $resposta = validaVarPost('resposta');
        $cod_tipo_categoria = validaVarPost('cod_tipo_categoria');
        $tipo_resposta = validaVarPost('tipo_resposta');
        
        $respondida_tel = ($tipo_resposta == 'tel') ? 1 : 0;
        
        $con = conectabd();
        
        if($codigo > 0)
        {
            $SqlEdicao = sprintf("UPDATE $tabela SET resposta_fale_conosco = '%s', respondida=1, respondida_tel = '$respondida_tel', cod_usuarios=" . $_SESSION['usuario']['codigo'] . ", data_hora_reposta=NOW() WHERE $chave_primaria = $codigo", $resposta);
            $resEdicao = mysql_query($SqlEdicao);
            
            //echo $SqlEdicao;
            
            $res_inserir_categorias = true;
            
            for($x = 0; $x < count($cod_tipo_categoria); $x++)
            {
                if($cod_tipo_categoria[$x] > 0)
                {
                	$sql_inserir_categorias = "INSERT INTO ipi_fale_conosco_categorias_comentarios (cod_fale_conosco, cod_categorias_comentarios) VALUES (" . $codigo . ", " . $cod_tipo_categoria[$x] . ")";
                    $res_inserir_categorias &= mysql_query($sql_inserir_categorias);
                }
            }
            
            if($res_inserir_categorias)
            {
                mensagemOk('Registro adicionado com êxito!');
            }
            else
            {
                mensagemErro('Erro ao associar categorias','Sua resposta foi enviada, porem, as categorias não foram registradas.');
            }
            
            if(!$respondida_tel)
            {
                $obj_fale_conosco = executaBuscaSimples("SELECT *, c.email AS c_email, f.email AS f_email FROM $tabela f LEFT JOIN ipi_clientes c ON (f.cod_clientes = c.cod_clientes) WHERE $chave_primaria = $codigo", $con);
                
                if($obj_fale_conosco->cod_clientes > 0)
                {
                	$email_destino = $obj_fale_conosco->c_email;
                }
                else
                {
                	$email_destino = $obj_fale_conosco->f_email;
                }
                
                require_once('../../ipi_email.php');
                $arr_aux = array();
                $arr_aux['cod_pedidos'] = 0;
                $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
                $arr_aux['cod_clientes'] = ($obj_fale_conosco->cod_clientes > 0 ? $obj_fale_conosco->cod_clientes : 0);
                $arr_aux['cod_pizzarias'] = ($obj_fale_conosco->cod_pizzarias > 0 ? $obj_fale_conosco->cod_pizzarias : 0);
                $arr_aux['tipo'] = 'FALECONOSCO_RESPOSTA';
                enviar_email (EMAIL_FALECONOSCO, $email_destino, "Sua sugestão/reclamação foi respondida", nl2br($resposta), $arr_aux, 'neutro');
            }
        }
        if($respondida_tel)
            desconectabd($con);
        
        break;
}

?>

<link rel="stylesheet" type="text/css" media="screen"
	href="../lib/css/tabs_simples.css" />

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
    'action': '<?
    echo $_SERVER['PHP_SELF']?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<?
    echo $chave_primaria?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

function enviar_resposta(tipo_resposta)
{
    $('tipo_resposta').setProperty('value', tipo_resposta);
    document.frmIncluir.submit();
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<?
echo $chave_primaria?>.value > 0) {
    <?
    if($acao == '')
        echo 'tabs.irpara(1);';
    ?>
    
    document.frmIncluir.botao_submit.value = 'Responder';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<?
    echo $chave_primaria?>.value = '';
      document.frmIncluir.resposta.value = '';

      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>

<div id="tabs">

<div class="menuTab">
<ul>
	<li><a href="javascript:;">Lista de Perguntas</a></li>
	<li></li>
</ul>
</div>

<!-- Tab Editar -->
<div class="painelTab">
  
  <?
$pagina = (validaVarPost('pagina','/[0-9]+/')) ? validaVarPost('pagina','/[0-9]+/') : 0;
$opcoes = (validaVarPost('opcoes')) ? validaVarPost('opcoes') : 'nome';
$filtro = validaVarPost('filtro');

$bairro = (validaVarPost('bairro')) ? validaVarPost('bairro') : 'TODOS';
$situacao_filtro = (validaVarPost('situacao_filtro')) ? validaVarPost('situacao_filtro') : 'TODOS';
$origem_cliente = (validaVarPost('origem_cliente')) ? validaVarPost('origem_cliente') : 'TODOS';

$respondidos = (validaVarPost('respondidos')) ? validaVarPost('respondidos') : '';
$clientes = (validaVarPost('clientes')) ? validaVarPost('clientes') : '';
$cod_pizzarias = (validaVarPost('cod_pizzarias')) ? validaVarPost('cod_pizzarias') : '';

$respondidos = (validar_var_get('resp') ? validar_var_get('resp') : $respondidos);

?>

<form name="frmFiltro" method="post">
<table align="center" class="caixa" cellpadding="0" cellspacing="0">

	<tr>
		<td class=" tdbl tdbt" align="right"><select name="opcoes">
			<option value="nome" <?
			if($opcoes == 'nome')
    			echo 'selected'?>>Nome</option>
		</select></td>

		<td class="tdbt tdbr">&nbsp;<input type="text" name="filtro" size="60"
			value="<? echo $filtro ?>"></td>
	</tr>

	<tr>
		<td class="legenda tdbl" align="right"><label>Respondidos:</label>&nbsp;</td>
		<td class="tdbr"><select name="respondidos">
			<option value="">Todos</option>
			<option value="sim" <?
                if($respondidos == 'sim')
                    echo 'selected'?>>Sim</option>
			<option value="nao" <?
                if($respondidos == 'nao')
                    echo 'selected'?>>Não</option>
		</select></td>
	</tr>

	<tr>
		<td class="legenda tdbl" align="right"><label>Clientes:</label>&nbsp;</td>
		<td class="tdbr"><select name="clientes">
			<option value="">Todos</option>
			<option value="sim" <?
                if($clientes == 'sim')
                    echo 'selected'?>>Sim</option>
			<option value="nao" <?
                if($clientes == 'nao')
                    echo 'selected'?>>Não</option>
		</select></td>
	</tr>
    <tr>
        <td class="legenda tdbl" align="right"><label for='cod_pizzarias'><? echo ucfirst(TIPO_EMPRESAS)?>:</label>&nbsp;</td>
        <td class="tdbr"><select name="cod_pizzarias">
            <option value="">Todas</option>
            <?
            $con = conectabd();

            $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
            $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
            if(in_array(1, $_SESSION['usuario']['cod_pizzarias']))
                    echo "<option value='Franqueadora'>Franqueadora</option>";          
            while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
            {
                echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
                if($cod_pizzaria_filt == $objBuscaPizzarias->cod_pizzarias)
                    echo 'selected';
                echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
            }
            desconectabd($con);
            ?>
        </select></td>
    </tr>

	<tr>
		<td align="right" class="tdbl tdbb tdbr" colspan="3"><input
			class="botaoAzul" type="submit" value="Buscar"></td>
	</tr>

</table>

<input type="hidden" name="acao" value="buscar"></form>

<br>

<?

$con = conectabd();

if($respondidos != "")
{
    if($respondidos == 'sim')
    {
        $consulta_respondidos = " AND f.respondida=1";
    }
    else
    {
        $consulta_respondidos = " AND f.respondida=0";
    }
}

if($clientes != "")
{
    if($clientes == 'sim')
    {
        $consulta_clientes = " AND c.cod_clientes>0";
    }
    else
    {
        $consulta_clientes = " AND (c.cod_clientes IS NULL OR c.cod_clientes=0)";
    }
}
if($cod_pizzarias !="" && !validar_var_get('resp'))
{
    //if($cod_pizzarias !="Franqueadora")
    $consulta_pizzarias = " AND cod_pizzarias = '$cod_pizzarias'";

}else
{

     $cod_pizzarias_usu_aspa = implode("','",$_SESSION['usuario']['cod_pizzarias'])."".(in_array(1, $_SESSION['usuario']['cod_pizzarias']) ? "', 'Franqueadora" : "");

     $consulta_pizzarias  = " AND cod_pizzarias in(".(!validar_var_get('resp') ? "'0'," : '')."'$cod_pizzarias_usu_aspa')";
 }


$SqlBuscaRegistros = "SELECT f.*, c.cod_clientes, c.nome AS nome_cliente FROM $tabela f LEFT JOIN ipi_clientes c ON(f.cod_clientes=c.cod_clientes) WHERE (c.$opcoes LIKE '%$filtro%' OR f.$opcoes LIKE '%$filtro%') $consulta_respondidos $consulta_clientes $consulta_pizzarias";
//echo "<br/><br/>".$cod_pizzarias_usu_aspa."</br><br/>";
//echo $SqlBuscaRegistros."<br/><br/>";
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$objBuscaRegistros = mysql_fetch_object($resBuscaRegistros);
$numBuscaRegistros = mysql_num_rows($resBuscaRegistros);

$SqlBuscaRegistros .= ' ORDER BY f.data_hora_fale_conosco DESC LIMIT ' . ($quant_pagina * $pagina) . ', ' . $quant_pagina;
$resBuscaRegistros = mysql_query($SqlBuscaRegistros);
$linhasBuscaRegistros = mysql_num_rows($resBuscaRegistros);

//echo $SqlBuscaRegistros;


echo "<center><b>" . $numBuscaRegistros . " registro(s) encontrado(s)</center></b><br>";

if((($quant_pagina * $pagina) == $numBuscaRegistros) && ($pagina != 0) && ($acao == 'excluir'))
    $pagina--;

echo '<center>';

$numpag = ceil(((int)$numBuscaRegistros) / ((int)$quant_pagina));

for($b = 0; $b < $numpag; $b++)
{
    echo '<form name="frmPaginacao' . $b . '" method="post">';
    echo '<input type="hidden" name="pagina" value="' . $b . '">';
    echo '<input type="hidden" name="filtro" value="' . $filtro . '">';
    echo '<input type="hidden" name="opcoes" value="' . $opcoes . '">';
    echo '<input type="hidden" name="respondidos" value="' . $respondidos . '">';
    echo '<input type="hidden" name="clientes" value="' . $clientes . '">';
    echo '<input type="hidden" name="bairro" value="' . $bairro . '">';
    echo '<input type="hidden" name="situacao_filtro" value="' . $situacao_filtro . '">';
    
    echo '<input type="hidden" name="acao" value="buscar">';
    echo "</form>";
}

if($pagina != 0)
    echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . ($pagina - 1) . '.submit();" style="margin-right: 5px;">&laquo;&nbsp;Anterior</a>';
else
    echo '<span style="margin-right: 5px;">&laquo;&nbsp;Anterior</span>';

for($b = 0; $b < $numpag; $b++)
{
    if($b != 0)
        echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
    
    if($pagina != $b)
        echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . $b . '.submit();">' . ($b + 1) . '</a>';
    else
        echo '<span><b>' . ($b + 1) . '</b></span>';
}

if(($quant_pagina == $linhasBuscaRegistros) && ((($quant_pagina * $pagina) + $quant_pagina) != $numBuscaRegistros))
    echo '<a href="javascript:;" onclick="javascript:frmPaginacao' . ($pagina + 1) . '.submit();" style="margin-left: 5px;">Próxima&nbsp;&raquo;</a>';
else
    echo '<span style="margin-left: 5px;">Próxima&nbsp;&raquo;</span>';

echo '</center>';

?>
  
  
    <table width="100%">
	<tr>

		<!-- Conteúdo -->
		<td class="conteudo">

		<form name="frmExcluir" method="post"
			onsubmit="return verificaCheckbox(this)">

		<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>

		<table class="listaEdicao" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<td align="center" width="15%">Cliente</td>
					<td align="center" width="30%">Pergunta</td>
                    <td align="center" width="25%">Resposta</td>
                    <td align="center" width="6%">Data Pergunta</td>
                    <td align="center" width="6%">Data Resposta</td>
                    <td align="center" width="5%">Pizzaria</td>
					<td align="center" width="5%">É Cliente?</td>
					<td align="center" width="5%">Respondida</td>
				</tr>
			</thead>
			<tbody>
          <?
        
        $con = conectabd();
        
        while($objBuscaRegistros = mysql_fetch_object($resBuscaRegistros))
        {
            echo '<tr>';
            
            if($objBuscaRegistros->nome_cliente != '')
            {
                echo '<td align="center"><a href="javascript:;" onclick="editar(' . $objBuscaRegistros->$chave_primaria . ')">' . bd2texto($objBuscaRegistros->nome_cliente) . '</a></td>';
            }
            else
            {
                echo '<td align="center"><a href="javascript:;" onclick="editar(' . $objBuscaRegistros->$chave_primaria . ')">' . bd2texto($objBuscaRegistros->nome) . '</a></td>';
            }
            echo '<td align="center">' . bd2texto($objBuscaRegistros->pergunta_fale_conosco) . '</td>';
            echo '<td align="center">' . bd2texto($objBuscaRegistros->resposta_fale_conosco) . '</td>';
            echo '<td align="center">' . bd2datahora($objBuscaRegistros->data_hora_fale_conosco) . '</td>';
            echo '<td align="center">' . bd2datahora($objBuscaRegistros->data_hora_reposta) . '</td>';

            if($objBuscaRegistros->cod_pizzarias!="Franqueadora" && $objBuscaRegistros->cod_pizzarias!="0")
            {
                $sql_busca_pizzarias = "select * from ipi_pizzarias where cod_pizzarias = ".$objBuscaRegistros->cod_pizzarias;
                $res_busca_pizzarias = mysql_query($sql_busca_pizzarias);
                $obj_busca_pizzarias = mysql_fetch_object($res_busca_pizzarias);
                 echo '<td align="center">' . $obj_busca_pizzarias->nome . '</td>';
            }else
                echo '<td align="center">' . ($objBuscaRegistros->cod_pizzarias!="0" ? $objBuscaRegistros->cod_pizzarias: '' ). '</td>';

            if($objBuscaRegistros->cod_clientes > 0)
                echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
            else
                echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
            
            if($objBuscaRegistros->respondida == 1)
                echo '<td align="center"><img src="../lib/img/principal/ok.gif"></td>';
            else
                echo '<td align="center"><img src="../lib/img/principal/erro.gif"></td>';
            
            echo '</tr>';
        }
        
        desconectabd($con);
        
        ?>
          
          </tbody>
		</table>

		<input type="hidden" name="acao" value="excluir"></form>

		</td>
		<!-- Conteúdo -->



	</tr>
</table>
</div>
<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">
    <?
    $codigo = validaVarPost($chave_primaria,'/[0-9]+/');
    
    if($codigo > 0)
    {
        $objBusca = executaBuscaSimples("SELECT f.pergunta_fale_conosco, f.resposta_fale_conosco, f.nome nome_contato, f.email email_contato, c.cod_clientes, c.nome nome_cliente, c.email email_cliente FROM $tabela f LEFT JOIN ipi_clientes c ON(c.cod_clientes=f.cod_clientes) WHERE $chave_primaria = $codigo");
    }
    

    if($objBusca->nome_cliente != '')
    {
        $nome_contato = $objBusca->nome_cliente;
    }
    else
    {
        $nome_contato = $objBusca->nome_contato;
    }


    if($objBusca->email_cliente != '')
    {
        $email_contato = $objBusca->email_cliente;
    }
    else
    {
        $email_contato = $objBusca->email_contato;
    }

    
    ?>
    
    <form name="frmIncluir" method="post"
	onsubmit="return validaRequeridos(this)">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">

	<tr>
    <td class="legenda tdbl tdbt tdbr">
    <label>
    <?
    echo $nome_contato;
    echo " - ".$email_contato;
    ?>
    </label>perguntou:
    </td>
	</tr>
	<tr>
		<td class="legenda tdbl tdbr">&nbsp;</td>
	</tr>
	<tr>
		<td class="legenda tdbl tdbr"><? echo $objBusca->pergunta_fale_conosco; ?></td>
	</tr>
	<tr>
		<td class="legenda tdbl tdbr">&nbsp;</td>
	</tr>
   
    <?
   
    $con = conectabd();
    
    $sql_buscar_categorias = "SELECT * FROM ipi_fale_conosco_categorias_comentarios WHERE $chave_primaria = $codigo";
    $res_buscar_categorias = mysql_query($sql_buscar_categorias);
    while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
    {
        $arr_categorias[] = $obj_buscar_categorias->cod_categorias_comentarios;
    }
    
    $sql_buscar_categorias = "SELECT * FROM ipi_categorias_comentarios WHERE tipo_categoria = 'FALECONOSCO' ORDER BY categoria_comentario";
    $res_buscar_categorias = mysql_query($sql_buscar_categorias);
    $num_buscar_categorias = mysql_num_rows($res_buscar_categorias);
    
    echo "<tr>";
    echo "<td class='legenda tdbl tdbr sep'>";
    
    for($e = 0; $e < $num_buscar_categorias; $e++)
    {
        $obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias);
        
        echo '<input type="checkbox" name="cod_tipo_categoria[]" id="cod_tipo_categoria[]" value=' . $obj_buscar_categorias->cod_categorias_comentarios . ' ';
        
        if(count($arr_categorias) > 0)
        {
            if(in_array($obj_buscar_categorias->cod_categorias_comentarios,$arr_categorias))
            {
                echo 'checked="checked"';
            }
        }
        
        echo '>&nbsp;' . $obj_buscar_categorias->categoria_comentario;
        echo '<br />';
    }
    
    desconectabd($con);
    
    echo "</td>";
    echo "</tr>";
    
    ?>
   
   	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="tipo_categoria">Resposta</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><textarea rows="8" cols="100" name="resposta"
			id="resposta" class="requerido"><? echo $objBusca->resposta_fale_conosco; ?></textarea></td>
	</tr>

	<tr>
		<td align="center" class="tdbl tdbb tdbr">&nbsp;
       	<?
        if($objBusca->respondida == 0)
        {
        ?>
        <input name="botao_submit" class="botao" type="button" value="Enviar Resposta por E-mail" onclick="enviar_resposta('email');">

		<input name="botao_submit" class="botao" type="button" value="Registrar Resposta por Telefone" onclick="enviar_resposta('tel');">
        <?
        }
    ?>
    </td>
	</tr>
</table>

<input type="hidden" name="acao" value="editar">
<input type="hidden" name="tipo_resposta" id="tipo_resposta" value="">
<input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo; ?>">

</form>
</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>
