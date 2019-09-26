<?php

/**
 * Relatório de Fluxo de Caixa.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       28/06/2010   Felipe        Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Saldo no Banco');

$acao = validaVarPost('acao');

$exibir_barra_lateral = false;

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css" />
<script src="../lib/js/calendario.js" type="text/javascript"></script>

<script>

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

function carregar_relatorio()
{
	var data_inicial = document.frmFiltro.data_inicial_filtro.value;
	var data_final = document.frmFiltro.data_final_filtro.value;
	var cod_pizzarias = document.frmFiltro.cod_pizzarias.value;
	
	var url = "acao=carregar_relatorio&data_inicial=" + data_inicial + "&data_final=" + data_final + "&cod_pizzarias=" + cod_pizzarias;
    
    new Request.HTML(
    {
        url: 'ipi_rel_titulos_saldo_bancos_ajax.php',
        update: $('relatorio')
    }).send(url);
}

window.addEvent('domready', function()
{
	// DatePick
    new vlaDatePicker('data_inicial_filtro', {openWith: 'botao_data_inicial_filtro', prefillDate: false});
    new vlaDatePicker('data_final_filtro', {openWith: 'botao_data_final_filtro', prefillDate: false});
    
	//carregar_relatorio();
});

</script>

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
        
        $data_inicial_filtro = (validaVarPost('data_inicial_filtro')) ? validaVarPost('data_inicial_filtro') : date("01/m/Y");
        $data_final_filtro = (validaVarPost('data_final_filtro')) ? validaVarPost('data_final_filtro') : date("t/m/Y", mktime(0, 0, 0, date('m'), 1, date('Y')));
        
        ?>
        
        <form name="frmFiltro" method="post">
        <table align="center" class="caixa" cellpadding="0" cellspacing="0">

          <tr>
              <td class="legenda tdbl tdbt" align="right"><label for="data_inicial_filtro">Data Inicial:</label></td>
              <td class="tdbt">&nbsp;</td>
              <td class="tdbt tdbr">
              	<input class="requerido" type="text" name="data_inicial_filtro" id="data_inicial_filtro" size="10" value="<? echo $data_inicial_filtro ?>" onkeypress="return MascaraData(this, event)">
              	&nbsp;
              	<a href="javascript:;" id="botao_data_inicial_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
          	</td>
          </tr>
          
          <tr>
              <td class="legenda tdbl" align="right"><label for="data_final_filtro">Data Final:</label></td>
              <td class="">&nbsp;</td>
              <td class="tdbr">
              	<input class="requerido" type="text" name="data_final_filtro" id="data_final_filtro" size="10" value="<? echo $data_final_filtro ?>" onkeypress="return MascaraData(this, event)">
              	&nbsp;
              	<a href="javascript:;" id="botao_data_final_filtro"><img src="../lib/img/principal/botao-data.gif"></a>
          	</td>
          </tr>

			    <tr>
				    <td class="legenda tdbl" align="right"><label><? echo ucfirst(TIPO_EMPRESAS)?>:</label></td>
				    <td class="">&nbsp;</td>
				    <td class="tdbr">
				      <select name="cod_pizzarias" style="width: 200px;">
					      <option value="TODOS">Todas</option>
				          <?
				          $conexao = conectabd();
				          $sql_buscar_pizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";
				          $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
				          while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
				          {
				              echo '<option value="' . $obj_buscar_pizzarias->cod_pizzarias . '" ';
				              if($cod_pizzarias == $obj_buscar_pizzarias->cod_pizzarias)
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
              <td align="right" class="tdbl tdbb tdbr" colspan="3">
                  <input class="botaoAzul" type="button" value="Buscar" onclick="carregar_relatorio();">
              </td>
          </tr>
        </table>

        <input type="hidden" name="acao" value="buscar"></form>

        <br><br>
        
        <div id="relatorio"></div>
        
        <br>

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

<? rodape(); ?>
