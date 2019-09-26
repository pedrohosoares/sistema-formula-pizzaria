<?php

/**
 * ipi_ingrediente.php: Cadastro de Ingrediente
 * 
 * Índice: cod_ingredientes
 * Tabela: ipi_ingredientes
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Mapa de estoque');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';

$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
$dia_semana_completo = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');

function bd2moedacomzero($valor)
{
  if($valor==0)
  {
    return '0,00';
  }
  else
  {
    return bd2moeda($valor);
  }
}
?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>
<link  href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen" />
<script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
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
    'action': '<? echo $_SERVER['PHP_SELF'] ?>',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': '<? echo $chave_primaria ?>',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

window.addEvent('domready', function()
{
	var tabs = new Tabs('tabs'); 
/*
	if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) 
	{
		<? if ($acao == '') echo 'tabs.irpara(1);'; ?>
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
			document.frmIncluir.ingrediente.value = '';
			document.frmIncluir.ingrediente_abreviado.value = '';
			//document.frmIncluir.tipo.value = '';
			document.frmIncluir.quantidade_minima.value = '';
			document.frmIncluir.quantidade_maxima.value = '';
			document.frmIncluir.quantidade_perda.value = '';
			document.frmIncluir.adicional.checked = false;
			document.frmIncluir.ativo.checked = true;

			marcaTodosEstado('marcar_tamanho', false);

			// Limpando todos os campos input para Preço
			var input = document.getElementsByTagName('input');
			for (var i = 0; i < input.length; i++) 
			{
				if((input[i].name.match('preco')) || (input[i].name.match('quantidade_estoque_extra'))) 
				{ 
					input[i].value = ''; 
				}
			}

		document.frmIncluir.botao_submit.value = 'Cadastrar';
		}
	});
*/
});
</script>
<style>

.cinza_claro td
{
  background-color: #e8e6e9;
}

</style>
<?
$data_inicial = (validaVarPost('data_inicial') != '') ? data2bd(validaVarPost('data_inicial')) : date('Y-m-d');
$data_final = (validaVarPost('data_final') != '') ? data2bd(validaVarPost('data_final')) : date('Y-m-d');
$cod_pizzarias = validaVarPost('cod_pizzarias');
$ingrediente_filtro = validaVarPost('ingrediente_filtro');
$bebida_filtro = validaVarPost("bebida_filtro");
$titulos_subcategorias = validaVarPost('titulos_subcategorias');
$acao = validaVarPost('acao');
$dia_filtro = validaVarPost("dia_semana_filtro");
$filtros = "+'&cod_pizzarias=".$cod_pizzarias."&data_inicial=".$data_inicial."&data_final=".$data_final."'";
?>

<div id="tabs">
   <div class="menuTab">
     <ul>
       <li><a style='border:0' href="javascript:;"></a></li>
    </ul>
  </div>
    
  <!-- Tab Editar -->
  <div class="painelTab">


  <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
  <script type="text/javascript" src="../lib/js/calendario.js"></script>
  <script>
  function detalhes_ingrediente(cod,nome_ingrediente,unidade,divisor,dia,tipo,pagina)
  {
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao=explodir_ingrediente&tipo='+tipo+'&dia_filtro='+dia+'&nome_ingrediente='+nome_ingrediente+'&unidade='+unidade+'&divisor='+divisor+'&pagina='+pagina+'&cod_ingredientes='+cod<? echo $filtros ?>;
    var reqDialog = new MooDialog.Request('ipi_rel_mapa_estoque_ajax.php',variaveis,opcoes, {
      'class': 'MooDialog',
      autoOpen: false,
      title: nome_ingrediente,
      size: {
             width: 900,
             height: 500
            }
    });

    // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
    reqDialog.setRequestOptions({
      onRequest: function(){
        reqDialog.setContent('loading...');
      }
    }).open();
  }

  function detalhes_ingrediente_sem_abrir(cod,nome_ingrediente,unidade,divisor,dia,tipo,pagina)
  {
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao=explodir_ingrediente&tipo='+tipo+'&dia_filtro='+dia+'&nome_ingrediente='+nome_ingrediente+'&unidade='+unidade+'&divisor='+divisor+'&pagina='+pagina+'&cod_ingredientes='+cod<? echo $filtros ?>;


      new Request.HTML(
  {
      url: 'ipi_rel_mapa_estoque_ajax.php',
      update: 'conteudo_modal',
      method:'post'
  }).send(variaveis);

    /*var reqDialog = new MooDialog.Request('ipi_rel_mapa_estoque_ajax.php',variaveis,opcoes, {
      'class': 'MooDialog',
      autoOpen: false,
      title: nome_ingrediente,
      size: {
             width: 900,
             height: 500
            }
    });
    // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
    reqDialog.setRequestOptions({
      onRequest: function(){
        reqDialog.setContent('Carregando...')
      }
    }).open();*/

  }
  function detalhes_compra(cod,nome_ingrediente,unidade,divisor,dia,tipo)
  {
    var opcoes = "method:'post'";//method:'post'  
    var variaveis = 'acao=explodir_compra&tipo='+tipo+'&dia_filtro='+dia+'&nome_ingrediente='+nome_ingrediente+'&unidade='+unidade+'&divisor='+divisor+'&cod_ingredientes='+cod<? echo $filtros ?>;
    var reqDialog = new MooDialog.Request('ipi_rel_mapa_estoque_ajax.php',variaveis,opcoes, {
      'class': 'MooDialog',
      autoOpen: false,
      title: nome_ingrediente,
      size: {
             width: 900,
             height: 500
            }
    });
    // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
    reqDialog.setRequestOptions({
      onRequest: function(){
        reqDialog.setContent('Carregando...')
      }
    }).open();
  }
  window.addEvent('domready', function() 
  {
      new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
      new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
  });
  </script>



  <form name="frmFiltro" method="post">

  <table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="legenda tdbl tdbt" align="right"><label for="cod_pizzarias"><? echo ucfirst(TIPO_EMPRESA)?>:</label></td>
    <td class="tdbt">&nbsp;</td>
    <td class="tdbr tdbt">
      <select name="cod_pizzarias" id="cod_pizzarias">
        
        <?
    		$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
        $con = conectabd();
        $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
        $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
        //echo "<option value='$cod_pizzarias_usuario'>Todas as Pizzarias</option>";
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
      <td class="legenda tdbl" align="right"><label for="data_inicial">Data
      Inicial:</label></td>
      <td class="">&nbsp;</td>
      <td class="tdbr"><input class="requerido" type="text"
          name="data_inicial" id="data_inicial" size="8"
          value="<?
          echo bd2data($data_inicial)?>"
          onkeypress="return MascaraData(this, event)"> &nbsp; <a
          href="javascript:;" id="botao_data_inicial"><img
          src="../lib/img/principal/botao-data.gif"></a></td>
  </tr>

  <tr>
      <td class="legenda tdbl " align="right"><label for="data_final">Data
      Final:</label></td>
      <td >&nbsp;</td>
      <td class="tdbr "><input class="requerido" type="text"
          name="data_final" id="data_final" size="8"
          value="<?
          echo bd2data($data_final)?>"
          onkeypress="return MascaraData(this, event)"> &nbsp; <a
          href="javascript:;" id="botao_data_final"><img
          src="../lib/img/principal/botao-data.gif"></a></td>
  </tr>

      <tr>
        <td class="legenda tdbl" align="right"><label for="dia_semana_filtro">Dia da Semana</label></td>
        <td >&nbsp;</td>
        <td class="tdbr">
            <select name="dia_semana_filtro" id="dia_semana_filtro">
              <option value=""></option>
                <?
                for($i = 1; $i<=7 ; $i++) 
                {
                    echo '<option value="'. $i . '"';
                    if($i == $dia_filtro)
                    {
                        echo " SELECTED ";
                    }
                    echo '>' .$dia_semana_completo[($i-1)] . '</option>';
                }
                
                ?>
            </select>
        </td>
    </tr> 

    <tr>
        <td class="legenda tdbl" align="right"><label for="ingrediente_filtro">Ingrediente</label></td>
        <td >&nbsp;</td>
        <td class="tdbr">
            <select name="ingrediente_filtro" id="ingrediente_filtro" onchange="$('bebida_filtro').value=''">
              <option value=""></option>
                <?
                $con = conectabd();
                
                $sql_buscar_categorias = "SELECT * FROM ipi_ingredientes where cod_ingredientes_baixa = cod_ingredientes and ativo = 1 ORDER BY ingrediente";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                
                    echo '<option value="' . $obj_buscar_categorias->cod_ingredientes . '"';
                    if($obj_buscar_categorias->cod_ingredientes == $ingrediente_filtro)
                    {
                        echo " SELECTED ";
                    }
                    echo '>' . bd2texto($obj_buscar_categorias->ingrediente) . '</option>';
                }
                
                
                
                ?>
            </select>
        </td>
    </tr> 

    <tr>
        <td class="legenda tdbl" align="right"><label for="bebida_filtro">Bebida</label></td>
        <td >&nbsp;</td>
        <td class="tdbr">
            <select name="bebida_filtro" id="bebida_filtro" onchange="$('ingrediente_filtro').value=''">
              <option value=""></option>
                <?
                $con = conectabd();
                
                $sql_buscar_categorias = "SELECT bc.*,c.conteudo,b.bebida FROM ipi_bebidas_ipi_conteudos bc inner join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos inner join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas ORDER BY b.bebida,c.conteudo";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                
                    echo '<option value="' . $obj_buscar_categorias->cod_bebidas_ipi_conteudos . '"';
                    if($obj_buscar_categorias->cod_bebidas_ipi_conteudos == $bebida_filtro)
                    {
                        echo " SELECTED ";
                    }
                    echo '>' . bd2texto($obj_buscar_categorias->bebida) . ' - ' . bd2texto($obj_buscar_categorias->conteudo) . '</option>';
                }
                
                
                
                ?>
            </select>
        </td>
    </tr> 
  <tr>
        <td class="legenda tdbl"><label for="titulos_subcategorias">Subcategoria do Ingrediente</label></td>
        <td >&nbsp;</td>
        <td class="tdbr">
            <select name="titulos_subcategorias" id="titulos_subcategorias">
              <option value=""></option>
                <?
                $con = conectabd();
                
                $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT ts.cod_titulos_categorias FROM ipi_titulos_subcategorias ts inner join ipi_ingredientes i on i.cod_titulos_subcategorias = ts.cod_titulos_subcategorias WHERE ts.tipo_titulo = 'PAGAR' AND ts.tipo_cendente_sacado = 'FORNECEDOR') ORDER BY titulos_categoria";
                $res_buscar_categorias = mysql_query($sql_buscar_categorias);
                
                while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
                {
                    echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
                    
                    $sql_buscar_subcategorias = "SELECT ts.* FROM ipi_titulos_subcategorias ts inner join ipi_ingredientes i on i.cod_titulos_subcategorias = ts.cod_titulos_subcategorias WHERE ts.cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND ts.tipo_titulo = 'PAGAR' AND ts.tipo_cendente_sacado = 'FORNECEDOR' GROUP BY ts.titulos_subcategorias ORDER BY titulos_subcategorias";
                    $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
                    
                    while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
                    {
                        echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '"';
                        if($obj_buscar_subcategorias->cod_titulos_subcategorias == $titulos_subcategorias)
                        {
                            echo " SELECTED ";
                        }
                        echo '>' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
                    }
                    
                    echo '</optgroup>';
                }
                 echo '<optgroup label="BEBIDAS">';
                    echo '<option value="10"';
                        if(10 == $titulos_subcategorias)
                        {
                            echo " SELECTED ";
                        }
                        echo '>BEBIDAS</option>';
                
                
                ?>
            </select>
        </td>
  </tr> 

  <tr><td align="right" class="tdbl tdbb tdbr" colspan="3">
  <input class="botaoAzul" type="submit" value="Buscar">
  </td></tr>
  
  </table>

  <br />

  <input type="hidden" name="acao" value="buscar">

  </form>

<?
if ($acao == "buscar")
{

    /*echo "<pre>";
    print_r($arr_consumo1);
    echo "</pre>";
    echo "<br/><br/><br/><br/><br/><br/><br/>";
        echo "<pre>";
    print_r($arr_consumo2);
    echo "</pre>";
        echo "<br/><br/><br/><br/><br/><br/><br/>";
        echo "<pre>";
    print_r($arr_consumo3);
    echo "</pre>";*/

    $item = array();
    $pAnt = array();//pAnt = primero anterior
    $ingredientes_usados = array();

    /*$sql_buscar_movimentacoes = "SELECT sum(e.quantidade) as quantidade,(case when e.cod_ingredientes > 0 then e.cod_igredientes else e.cod_bebidas_ipi_conteudos end) as codigo_produto,e.tipo_estoque from ipi_estoque e where e.cod_pizzarias in ($cod_pizzarias_usuario) and e.cod_pizzarias in($cod_pizzarias) and data_hora_lancamento between '$data_inicial 00:00:00' and '$data_final 23:59:59' group by year(e.data_hora_lancamento), month(e.data_hora_lancamento), day(e.data_hora_lancamento), quantidade";*/

    // case (promo or combo or fideli ser promo)

    $sql_buscar_movimentacoes = "SELECT (SELECT sum(u.quantidade_compras) from ipi_estoque_mapa u where u.cod_pizzarias = em.cod_pizzarias and u.cod_ingredientes = em.cod_ingredientes and u.cod_bebidas_ipi_conteudos = em.cod_bebidas_ipi_conteudos and u.data_movimentacao  between '$data_inicial' and '$data_final' ) as total_entrada_compras,(SELECT sum(u.quantidade_compras*u.ultima_compra_preco_grama) from ipi_estoque_mapa u where u.cod_pizzarias = em.cod_pizzarias and u.cod_ingredientes = em.cod_ingredientes and u.cod_bebidas_ipi_conteudos = em.cod_bebidas_ipi_conteudos and u.data_movimentacao between  '$data_inicial' and '$data_final' ) as total_entrada_compras_dinheiro,em.*,i.ingrediente from ipi_estoque_mapa em left join ipi_ingredientes i on i.cod_ingredientes = em.cod_ingredientes where em.cod_pizzarias in ($cod_pizzarias_usuario) and em.cod_pizzarias in($cod_pizzarias) and em.data_movimentacao between '$data_inicial' and '$data_final'";

    if($ingrediente_filtro)
    {
      $sql_buscar_movimentacoes .= " and em.cod_ingredientes = '$ingrediente_filtro'";
    }
    else
      if($bebida_filtro)
      {
        $sql_buscar_movimentacoes .= " and em.cod_bebidas_ipi_conteudos = '$bebida_filtro'";
      }

    if($dia_filtro!="")
    {
      $sql_buscar_movimentacoes .= " and DAYOFWEEK(em.data_movimentacao) = '$dia_filtro'";
      //'d_'.$i
    }
    

    if($titulos_subcategorias!="")
      if($titulos_subcategorias!='10')
      {
        $sql_buscar_movimentacoes .= " and i.cod_titulos_subcategorias ='".$titulos_subcategorias."' and em.cod_bebidas_ipi_conteudos <= 0"; 
      }
      else
      {
        $sql_buscar_movimentacoes .= " and em.cod_bebidas_ipi_conteudos > 0"; 
      }

    
      $sql_buscar_movimentacoes .= ' ORDER BY em.data_movimentacao ASC,em.cod_bebidas_ipi_conteudos ASC,i.ingrediente ASC,em.cod_bebidas_ipi_conteudos ASC';
      //echo $sql_buscar_movimentacoes."<br/><br/>";
    $res_buscar_movimentacoes = mysql_query($sql_buscar_movimentacoes);
    $num_ing = mysql_num_rows($res_buscar_movimentacoes);


      ?>
      <b>Movimen.</b> = Movimentação; 
      <b>Inv.</b> = Inventário.
      <br/><br/>
      <table class="listaEdicao" cellpadding="0" cellspacing="0" align="center">
      <thead>

        <tr>
          <td align="center" width="30" align="right">Cod Item</td>
          <td align="center" width="250" align="center">Item de Estoque</td>
          <td align="center" width="90" align="center">Grupo</td>
          <td align="center" width="90" align="cener">Data da Movimen.</td>
          <td align="center" width="90" align="cener">Dia da Semana</td>
         <!--  <td align="center" width="90" align="center">Docto de movimentacao</td>  -->
          <!-- <td align="center" width="90" align="center">Tipo de <br/>Movimentação</td> -->
          <td align="center" width="90" align="right">(=)<br/>Saldo Inicial<br/>(Qtde)</td>
          <td align="center" width="90" align="right">(=)<br/>Saldo Inicial<br/>(R$)</td>
          <td align="center" width="90" align="right">(+ -)<br/>Movimen.<br/>(Qtde)</td>
          <td align="center" width="90" align="right">(+ -)<br/>Movimen.<br/>(R$)</td>
          <td align="center" width="90" align="right">(=)<br/>Saldo Final<br/>(Qtde)</td>
          <td align="center" width="90" align="right">(=)<br/>Saldo Final<br/>(R$)</td>
          <td align="center" width="90" align="right">(+)<br/>Compras<br/>(Qtde)</td>
          <td align="center" width="90" align="right">(+ -)<br/>Ajuste Inv.<br/>(Qtde)</td>
<!--           <td align="center" width="90" align="right">Devoluções</td>
          <td align="center" width="90" align="right">Amostras</td>
          <td align="center" width="90" align="right">Rebate</td> -->
          <td align="center" width="90" align="right">(-)<br/>Venda<br/>(Qtde)</td>
          <td align="center" width="90" align="right">(-)<br/>Fidelidade<br/>(Qtde)</td>
          <!-- <td align="center" width="90" align="right">(-) Combo</td> -->
          <td align="center" width="90" align="right">(-)<br/>Promoções<br/>(Qtde)</td>
          <td align="center" width="90" align="right">(-)<br/>Lanche<br/>(Qtde)</td>

        </tr>
      </thead>
      <tbody>
      <?
     /* echo "<pre>";
      print_r($item);
      echo "</pre><br/><br/><br/>";*/


      $sql_buscar_nomes_ingredientes = "SELECT i.ingrediente,i.cod_ingredientes,i.cod_unidade_padrao,ts.titulos_subcategorias from ipi_ingredientes i left join ipi_titulos_subcategorias ts on i.cod_titulos_subcategorias = ts.cod_titulos_subcategorias";
      //echo $sql_buscar_nomes_ingredientes."<br/><br/>";
      $res_buscar_nomes_ingredientes = mysql_query($sql_buscar_nomes_ingredientes);
      while($obj_buscar_nomes_ingredientes = mysql_fetch_object($res_buscar_nomes_ingredientes))
      {
        $pAnt["INGREDIENTE"][$obj_buscar_nomes_ingredientes->cod_ingredientes]['nome']= $obj_buscar_nomes_ingredientes->ingrediente;
        $pAnt["INGREDIENTE"][$obj_buscar_nomes_ingredientes->cod_ingredientes]['unidade']= $obj_buscar_nomes_ingredientes->cod_unidade_padrao;
        $pAnt["INGREDIENTE"][$obj_buscar_nomes_ingredientes->cod_ingredientes]['grupo']= $obj_buscar_nomes_ingredientes->titulos_subcategorias;
      }

      $sql_buscar_nomes_ingredientes = "SELECT b.bebida,c.conteudo,bc.cod_bebidas_ipi_conteudos from ipi_bebidas_ipi_conteudos bc inner join ipi_bebidas b on b.cod_bebidas = bc.cod_bebidas inner join ipi_conteudos c on c.cod_conteudos = bc.cod_conteudos";//where bc.cod_bebidas_ipi_conteudos in (".implode($ingredientes_usados["BEBIDA"],",").")"
     // echo $sql_buscar_nomes_ingredientes."<br/><br/>";
      $res_buscar_nomes_ingredientes = mysql_query($sql_buscar_nomes_ingredientes);

      while($obj_buscar_nomes_ingredientes = mysql_fetch_object($res_buscar_nomes_ingredientes))
      {
        $pAnt["BEBIDA"][$obj_buscar_nomes_ingredientes->cod_bebidas_ipi_conteudos]['nome']= $obj_buscar_nomes_ingredientes->bebida." - ".$obj_buscar_nomes_ingredientes->conteudo;
        $pAnt["BEBIDA"][$obj_buscar_nomes_ingredientes->cod_bebidas_ipi_conteudos]['grupo'] = "BEBIDAS";
      }
      /*echo "<pre>";
      print_r($pAnt);
      echo "</pre><br/><br/><br/>";
echo "<pre>";
              print_r($arr_unidade_padrao);
              echo "</pre>";*/
    $arr_unidade_padrao = array();
    $sql_buscar_unidades_padroes = "SELECT * from ipi_unidade_padrao";
    $res_buscar_unidades_padroes = mysql_query($sql_buscar_unidades_padroes);
    while($obj_buscar_unidades_padroes = mysql_fetch_object($res_buscar_unidades_padroes))
    {
      $arr_unidade_padrao[$obj_buscar_unidades_padroes->cod_unidade_padrao]['abr'] = $obj_buscar_unidades_padroes->abreviatura;
      $arr_unidade_padrao[$obj_buscar_unidades_padroes->cod_unidade_padrao]['divisor'] = $obj_buscar_unidades_padroes->divisor_comum;

    }

    $arr_anterior = array();
    $tabela = "";
    $cont_ing = array();  
    $saldo_total = array(); 

    $saldo_total['saldo_anterior'] = "";
    $saldo_total['saldo_anterior_dinheiro'] = "";
    $saldo_total['total'] = 0;
    $saldo_total['entrada'] = 0;
    $saldo_total['ajuste'] = 0;
    $saldo_total['saida'] = 0;
    $saldo_total['combo'] = 0;
    $saldo_total['fidelidade'] = 0;
    $saldo_total['promocional'] = 0;
    $saldo_total['lanche'] = 0;
    $saldo_total['total_dinheiro'] = 0;
    $saldo_total['movimentacao'] = 0;
    $saldo_total['movimentacao_dinheiro'] = 0;
    $mov_cont = 0;
    while($obj_buscar_movimentacoes = mysql_fetch_object($res_buscar_movimentacoes))
    {
      //echo 'a.';
   // if($infos["entrada"]!="")
   // {
      $saldo_atual = 0;
      $saldo_anterior = 0;
      if($obj_buscar_movimentacoes->cod_ingredientes>0)
      {
        $cod = $obj_buscar_movimentacoes->cod_ingredientes;
        $ing = "INGREDIENTE";
      }else
      {
        $cod = $obj_buscar_movimentacoes->cod_bebidas_ipi_conteudos;
        $ing = "BEBIDA";
      }

      $preco_grama = ($obj_buscar_movimentacoes->total_entrada_compras_dinheiro / ($obj_buscar_movimentacoes->total_entrada_compras >0 ? $obj_buscar_movimentacoes->total_entrada_compras : 1));
     // echo $preco_grama."<br/>";
      if($preco_grama<=0)
      {
        $preco_grama = $obj_buscar_movimentacoes->ultima_compra_preco_grama;
      }
       //echo $preco_grama."<br/>";
      //$saldo_anterior = $saldo_anterior + $infos["entrada"];

      $nome_exibicao = $pAnt[$ing][$cod]['nome'];
      //$arr_anterior[$ing][$cod][] = $total;
      if($ing=="INGREDIENTE")
      {
        /*echo "<pre>";
        print_r($arr_unidade_padrao);
        echo "</pre>";die();*/
       // $unidade_padrao = $arr_unidade_padrao[$pAnt[$ing][$cod]['unidade']];
       // $entrada = round(($entrada/$unidade_padrao["divisor"]),3);
        //$saida = abs((round(($saida/$unidade_padrao["divisor"]),2)));
       // $total = round(($total/$unidade_padrao["divisor"]),3);
       //$saldo_anterior =  round(($saldo_anterior/$unidade_padrao["divisor"]),3);
       // $nome_exibicao = $nome_exibicao." (".$unidade_padrao["abr"].")";
      }
      else
      {
        $nome_exibicao = $nome_exibicao." (unit)";        
      }
      //$saldo_anterior = round($saldo_anterior,3);
      /*echo "<tr>";
      echo "<td>$cod</td>";
      echo "<td align='center'><a href='javascript:void(0);' onclick='detalhes_compra(\"".$cod."\",\"".$nome_exibicao."\",\"".$unidade_padrao["abr"]."\",\"".$unidade_padrao["divisor"]."\",\"".$diamov."\",\"".($ing=="INGREDIENTE" ? 'ingrediente' : 'bebida')."\")'>".$nome_exibicao."</a></td>";
      //echo "<td align='center'>".$nome_exibicao."</a></td>";
      echo "<td align='center'>".$pAnt[$ing][$cod]['grupo']."</td>";
      echo "<td align='center'>".date("d/m/Y",strtotime($diamov))."</td>";
      echo "<td align='center'>".$infos["nota_fiscal"]."</td>";
      echo "<td align='center'>Compra</td>";
      echo "<td align='right'>".bd2moeda($saldo_anterior)."</td>";
      echo "<td align='right'>".bd2moeda($entrada)."</td>";
     // echo "<td align='right'>dev</td>";
     // echo "<td align='right'>amo</td>";
     // echo "<td align='right'>re</td>";
      echo "<td align='right'>0</td>";
      echo "<td align='right'>0</td>";
      echo "<td align='right'>0</td>";
      echo "<td align='right'>0</td>";
      echo "<td align='right'>0</td>";
      echo "<td align='right'>".bd2moeda($total)."</td>";
      echo "</tr>";*/
    //}     

    /*if($infos['saida']!="" || $infos['combo']!="" || $infos['fidelidade']!="" || $infos['lanche']!="" || $infos['promocional']!="")
    {*/
      /*$saldo_atual = 0;
      $saldo_anterior = 0;

      if($cont_ing[$ing][$cod]>-1) 
      {
        $cont_ing[$ing][$cod] = $cont_ing[$ing][$cod] + 1;
        $saldo_anterior = $arr_anterior[$ing][$cod][($cont_ing[$ing][$cod] - 1)];
      }
      else
      {
        $cont_ing[$ing][$cod] = 0;
        $saldo_anterior = $pAnt[$ing][$cod]['saldo_anterior']; 
      }*/

      $saldo_anterior = $obj_buscar_movimentacoes->saldo_inicial;
      $saldo_anterior_dinheiro = $obj_buscar_movimentacoes->saldo_inicial * $preco_grama;
      //if($mov_cont==0)
      {
        $saldo_total['saldo_anterior'] += $obj_buscar_movimentacoes->saldo_inicial;
        $saldo_total['saldo_anterior_dinheiro'] += $saldo_anterior_dinheiro;
      }

      $total = $obj_buscar_movimentacoes->saldo_final;
       $total_dinheiro = $total * $preco_grama;
      //if($mov_cont==($num_ing-1))
      {
        $saldo_total['total'] += $obj_buscar_movimentacoes->saldo_final;

       $saldo_total['total_dinheiro'] += $total_dinheiro;
      }

      $entrada = $obj_buscar_movimentacoes->quantidade_compras;
      $saldo_total['entrada'] += $obj_buscar_movimentacoes->quantidade_compras;

      $ajuste = $obj_buscar_movimentacoes->quantidade_ajuste;
      $saldo_total['ajuste'] += $obj_buscar_movimentacoes->quantidade_ajuste;

      $saida = $obj_buscar_movimentacoes->quantidade_vendas;
      $saldo_total['saida'] += $obj_buscar_movimentacoes->quantidade_vendas;

      $combo = $obj_buscar_movimentacoes->quantidade_combo;
      $saldo_total['combo'] += $obj_buscar_movimentacoes->quantidade_combo;

      $fidelidade = $obj_buscar_movimentacoes->quantidade_fidelidade;
      $saldo_total['fidelidade'] += $obj_buscar_movimentacoes->quantidade_fidelidade;

      $promocional = $obj_buscar_movimentacoes->quantidade_promocao;
      $saldo_total['promocional'] += $obj_buscar_movimentacoes->quantidade_promocao;

      $lanche = $obj_buscar_movimentacoes->quantidade_lanche;
      $saldo_total['lanche'] += $obj_buscar_movimentacoes->quantidade_lanche;

      $nome_exibicao = $pAnt[$ing][$cod]['nome'];
      $arr_anterior[$ing][$cod][] = $total;

      $movimentacao = $ajuste - $saida - $combo - $fidelidade - $promocional - $lanche + $entrada;
      $saldo_total['movimentacao'] += $movimentacao;
      $movimentacao_dinheiro = $movimentacao * $preco_grama;
      $saldo_total['movimentacao_dinheiro'] += $movimentacao_dinheiro;
      

      if($ing=="INGREDIENTE")
      {
        /*echo "<pre>";
        print_r($arr_unidade_padrao);
        echo "</pre>";*/
        $unidade_padrao = $arr_unidade_padrao[$pAnt[$ing][$cod]['unidade']];
        //$entrada = round(($entrada/$unidade_padrao["divisor"]),2);
        $entrada = round(($entrada/$unidade_padrao["divisor"]),3);
        $movimentacao = round(($movimentacao/$unidade_padrao["divisor"]),3);
        $ajuste = round(($ajuste/$unidade_padrao["divisor"]),3);
        $combo =abs((round(($combo/$unidade_padrao["divisor"]),3)));
        $fidelidade =  abs((round(($fidelidade/$unidade_padrao["divisor"]),3)));
        $lanche =  abs((round(($lanche/$unidade_padrao["divisor"]),3)));
        $promocional =  abs((round(($promocional/$unidade_padrao["divisor"]),3)));

        $saida = abs((round(($saida/$unidade_padrao["divisor"]),3)));
        $total = round(($total/$unidade_padrao["divisor"]),3);
        $saldo_anterior =  round(($saldo_anterior/$unidade_padrao["divisor"]),3);
        $nome_exibicao = $nome_exibicao." (".$unidade_padrao["abr"].")";
      }
      else
      {
        $nome_exibicao = $nome_exibicao." (unit)";        
      }
      $promocional += $combo;
      $saldo_anterior = round($saldo_anterior,3);
      $tabela .= "<tr>";
      $tabela .= "<td>$cod</td>";
      $tabela .= "<td align='center'><a href='javascript:void(0);' onclick='detalhes_ingrediente(\"".$cod."\",\"".$nome_exibicao."\",\"".$unidade_padrao["abr"]."\",\"".$unidade_padrao["divisor"]."\",\"".$obj_buscar_movimentacoes->data_movimentacao."\",\"".($ing=="INGREDIENTE" ? 'ingrediente' : 'bebida')."\")'>".$nome_exibicao."</a></td>";
      $tabela .= "<td align='center'>".$pAnt[$ing][$cod]['grupo']."</td>";//$preco_grama
      $tabela .= "<td align='center'>".date("d/m/Y",strtotime($obj_buscar_movimentacoes->data_movimentacao))."</td>";
      $tabela .= "<td align='center'>".$dia_semana_completo[date("w",strtotime($obj_buscar_movimentacoes->data_movimentacao))]."</td>";
    //  echo "<td align='center'>".$infos["nota_fiscal"]."</td>";
      //echo "<td align='center'>Venda</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($saldo_anterior)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($saldo_anterior_dinheiro)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($movimentacao)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($movimentacao_dinheiro)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($total)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($total_dinheiro)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($entrada)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($ajuste)."</td>";
      //$tabela .= "<td align='right'>dev</td>";
      //$tabela .= "<td align='right'>amo</td>";
      //$tabela .= "<td align='right'>re</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero(abs($saida))."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($fidelidade)."</td>";
      //$tabela .= "<td align='right'>".bd2moedacomzero($combo)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($promocional)."</td>";
      $tabela .= "<td align='right'>".bd2moedacomzero($lanche)."</td>";

      $tabela .= "</tr>";
    //}
      $mov_cont++;
  }
      if($ingrediente_filtro!="")
      {
        $unidade_padrao = $arr_unidade_padrao[$pAnt[$ing][$ingrediente_filtro]['unidade']];
      }
      else
      {
        $unidade_padrao = $arr_unidade_padrao[5];
      }
      $saldo_total['promocional'] += $saldo_total['combo'];
      if($unidade_padrao!="")
      {
        /*echo "<pre>";
        print_r($arr_unidade_padrao);
        echo "</pre>";*/
        $ing = "INGREDIENTE";
        
        //$entrada = round(($entrada/$unidade_padrao["divisor"]),2);
        $saldo_total['entrada'] = round(($saldo_total['entrada']/$unidade_padrao["divisor"]),3);
        $saldo_total['movimentacao'] = round(($saldo_total['movimentacao']/$unidade_padrao["divisor"]),3);
        $saldo_total['ajuste'] = round(($saldo_total['ajuste']/$unidade_padrao["divisor"]),3);
        $saldo_total['fidelidade'] =  abs((round(($saldo_total['fidelidade']/$unidade_padrao["divisor"]),3)));
        $saldo_total['lanche'] =  abs((round(($saldo_total['lanche']/$unidade_padrao["divisor"]),3)));
        $saldo_total['promocional'] =  abs((round(($saldo_total['promocional']/$unidade_padrao["divisor"]),3)));

        $saldo_total['saida'] = abs((round(($saldo_total['saida']/$unidade_padrao["divisor"]),3)));
        $saldo_total['total'] = round(($saldo_total['total']/$unidade_padrao["divisor"]),3);
        $saldo_total['saldo_anterior'] =  round(($saldo_total['saldo_anterior']/$unidade_padrao["divisor"]),3);
      }
      echo "<tr class='cinza_claro'>";
      echo "<td>0</td>";
      echo "<td align='center'><b>Totalizador (".$unidade_padrao['abr'].") </b></td>";
      echo "<td align='center'></td>";
      echo "<td align='center'>".bd2data($data_inicial)." até ".bd2data($data_final)."</td>";
      echo "<td align='center'></td>";
    //  echo "<td align='center'>".$infos["nota_fiscal"]."</td>";
      //echo "<td align='center'>Venda</td>";

      echo "<td align='right'>".bd2moedacomzero($saldo_total['saldo_anterior'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['saldo_anterior_dinheiro'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['movimentacao'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['movimentacao_dinheiro'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['total'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['total_dinheiro'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['entrada'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['ajuste'])."</td>";
      //echo "<td align='right'>dev</td>";
      //echo "<td align='right'>amo</td>";
      //echo "<td align='right'>re</td>";
      echo "<td align='right'>".bd2moedacomzero(abs($saldo_total['saida']))."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['fidelidade'])."</td>";
      //echo "<td align='right'>".bd2moedacomzero($combo)."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['promocional'])."</td>";
      echo "<td align='right'>".bd2moedacomzero($saldo_total['lanche'])."</td>";
      echo "</tr>";
      echo $tabela;
      /*echo "<pre>";
      print_r($arr_anterior);
      echo "</pre><br/><br/><br/>";
            echo "<pre>";
      print_r($cont_ing);
      echo "</pre><br/><br/><br/>";   */ 
           /* arsort($arr_consumo1);
            $arr_nome_ing = array();

            $sql_buscar_uni_padrao = "SELECT u.cod_unidade_padrao,i.ingrediente,i.cod_ingredientes,u.abreviatura,u.divisor_comum from ipi_unidade_padrao u inner join ipi_ingredientes i on i.cod_unidade_padrao = u.cod_unidade_padrao";
            $res_buscar_uni_padrao = mysql_query($sql_buscar_uni_padrao);
            while($obj_buscar_uni_padrao = mysql_fetch_object($res_buscar_uni_padrao))
            {
              $unidades[$obj_buscar_uni_padrao->cod_unidade_padrao]['abr'] = $obj_buscar_uni_padrao->abreviatura;
              $unidades[$obj_buscar_uni_padrao->cod_unidade_padrao]['divisor'] = $obj_buscar_uni_padrao->divisor_comum;
              $ing_unidade[$obj_buscar_uni_padrao->cod_ingredientes] = $obj_buscar_uni_padrao->cod_unidade_padrao;
              $arr_nome_ing[$obj_buscar_uni_padrao->cod_ingredientes] = $obj_buscar_uni_padrao->ingrediente;
            }

            foreach ( $arr_consumo1 as $ingrediente => $quantidade) 
            {
              $arr_unidade = $unidades[$ing_unidade[$ingrediente]];
              $nome_ingrediente =  $arr_nome_ing[$ingrediente];
              $quant_dividida = ($quantidade/$arr_unidade['divisor']);
              $quant_exibir = round($quant_dividida,2)." ".$arr_unidade['abr'];
              echo "<tr><td align='center'><a href='javascript:void(0);' onclick='detalhes_ingrediente(\"".$ingrediente."\",\"".$nome_ingrediente."\",\"".$arr_unidade['abr']."\",\"".$arr_unidade['divisor']."\")'>".$nome_ingrediente."</a></td><td align='center'>".$quant_exibir."</td></tr>";
            }*/
    ?>
        </tbody>
        </table>
      <!-- Conteúdo -->
      
      <!-- Barra Lateral -->
	  <!--
      <td class="lateral">
        <div class="blocoNavegacao">
          <ul>
            <li><a href="ipi_adicional.php">Adicionais</a></li>
            <li><a href="ipi_borda.php">Bordas</a></li>
            <li><a href="ipi_pizza.php">Pizzas</a></li>
            <li><a href="ipi_tamanho.php">Tamanhos</a></li>
          </ul>
        </div>
      </td>
	  -->
      <!-- Barra Lateral -->
      
      </tr></table>
    <?
  }
desconectar_bd($con);
?>


  </div>
  <!-- Tab Editar -->
  
  
  
 </div>

<? rodape(); ?>
