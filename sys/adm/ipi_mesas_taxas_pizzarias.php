<?php

/**
 * ipi_pizza.php: Cadastro de Pizzas
 * 
 * Índice: cod_pizzas
 * Tabela: ipi_pizzas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Taxas por ' . TIPO_EMPRESA);

$acao = validaVarPost('acao');

$tabela = 'ipi_mesas_taxas';
$chave_primaria = 'cod_mesas_taxas';
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
if(validaVarGet("cod_filt", '/[0-9]+/')!="")
{
$cod_pizzaria_filt = validaVarGet("cod_filt", '/[0-9]+/');

}
if(validaVarPost("cod_filt", '/[0-9]+/')!="")
{
$cod_pizzaria_filt = validaVarPost("cod_filt", '/[0-9]+/');

}
if(validaVarPost('cod_pizzarias_filt')!="")
{
$cod_pizzaria_filt = validaVarPost('cod_pizzarias_filt');
}
//echo "FILT".$cod_pizzaria_filt."/FILT";

switch ($acao)
{
    case 'excluir' :
        $excluir = validaVarPost('excluir');
        $indicesSql = implode(',', $excluir);
        
        $con = conectabd();
        
        $SqlDel = "DELETE FROM ipi_mesas_taxas_pizzarias WHERE $chave_primaria IN ($indicesSql) and cod_pizzarias = '$cod_pizzaria_filt'";
        
        $resDel = mysql_query($SqlDel);
        
        if ($resDel)
            mensagemOk('Os registros selecionados foram excluídos com sucesso!');
        else
            mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
        
        desconectabd($con);
    break;
    case 'editar' :
        $codigo = validaVarPost($chave_primaria);
        $pizza = validaVarPost('pizza');
        $valor = validaVarPost('valor');
				$cod_pizzarias = validaVarPost('checkpizzaria');
        
        $con = conectabd();

        if($cod_pizzaria_filt)
				{

					$cod_pizzaria = $cod_pizzaria_filt;

          $SqlDel = "DELETE FROM ipi_mesas_taxas_pizzarias WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzaria";
          $resDel = mysql_query($SqlDel);
          
          if ($resDel)
          {
                                    
            $SqlEdicaoTaxa = sprintf("INSERT INTO ipi_mesas_taxas_pizzarias (cod_mesas_taxas, cod_pizzarias, valor) VALUES (%d, %d, '%s')", $codigo, $cod_pizzaria, moeda2bd($valor) );
            $resEdicaoTaxa = mysql_query($SqlEdicaoTaxa);
            //echo $SqlEdicaoTaxa."<br>";
            
            if ($resEdicaoTaxa)
            {
              $resEdicao = true;
              mensagemOk('Registro alterado com êxito!');
            }
            else
            {
              $resEdicao = false;
              mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }
					
        }
        else
        {
            $resEdicao = false;
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
			}
    

      desconectabd($con);
      break;
    case 'copiar_precos' :
    		$cod_matriz = validaVarPost('cod_matriz');
    		$cod_copiar = validaVarPost('cod_filt');
    		
    		$con = conectar_bd();
    		$SqlDel = "DELETE FROM ipi_mesas_taxas_pizzarias WHERE cod_pizzarias = $cod_copiar";
    		
        $resDel = mysql_query($SqlDel);
		                  //echo "<br />D: ".$SqlDel;
        $res_adicionar_preco = TRUE;
				if($resDel)
				{    		
  				$sql_pegar_precos = "select * from ipi_mesas_taxas_pizzarias where cod_pizzarias = $cod_matriz";
    			$res_pegar_precos = mysql_query($sql_pegar_precos);
    			
    			while($obj_pegar_precos = mysql_fetch_object($res_pegar_precos))
    			{
    					$sql_adicionar_preco = "insert into ipi_mesas_taxas_pizzarias(cod_mesas_taxas,cod_pizzarias,preco) values (".$obj_pegar_precos->cod_mesas_taxas.",".$cod_copiar.",".$obj_pegar_precos->valor.")";
              //echo $sql_adicionar_preco;
    					$res_adicionar_preco &= mysql_query($sql_adicionar_preco);
    			}
    			
    			if ($res_adicionar_preco)
			    {
			        mensagemOk('Preços copiados com êxito!');
			    }
			    else
			    {
			        mensagemErro('Erro ao copiar preços', 'Por favor, verifique se o registro já não se encontra cadastrado.');
			    }
    		
    		}
	  	  else
          {
              mensagemErro('Erro ao copiar preços', 'Por favor, verifique se a pizzaria tem algum preço cadastrado.');
          }
    		desconectar_bd($con);
        break;  
 
}

?>

<link rel="stylesheet" type="text/css" media="screen"
	href="../lib/css/tabs_simples.css" />

<script>

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

  var input2 = new Element('input', {
    'type': 'hidden',
    'name': 'cod_filt',
    'value': <? echo "'$cod_pizzaria_filt'" ?>
  });
  
  input.inject(form);
	input2.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}


function copiar_precos()
{
  if(confirm('TEM CERTEZA QUE DESEJA COPIAR OS PREÇOS?\n(ESSE PROCESSO NÃO TERÁ VOLTA)\n')) 
    { 
    	var cod_pizzarias_matriz = 1;
    	var form = new Element('form', {
        'action': '<?
        echo $_SERVER['PHP_SELF']?>',
        'method': 'post'
      });
      var input = new Element('input', {
        'type': 'hidden',
        'name': 'cod_matriz',
        'value': cod_pizzarias_matriz
      });

      var input2 = new Element('input', {
        'type': 'hidden',
        'name': 'cod_filt',
        'value': '<? echo "$cod_pizzaria_filt" ?>'
      });
      
      var input3 = new Element('input', {
        'type': 'hidden',
        'name': 'acao',
        'value': 'copiar_precos'
      });
      
      input.inject(form);
    	input2.inject(form);
    	input3.inject(form);
      $(document.body).adopt(form);
      form.submit();	
    }
}

function limpaPrecoFidelidade(cod) {
  document.getElementById('preco_' + cod).value = '';
  document.getElementById('fidelidade_' + cod).value = '';
}

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<?
echo $chave_primaria?>.value > 0) {
    <?
    if ($acao == '')
        echo 'tabs.irpara(1);';
    ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      document.frmIncluir.<?
    echo $chave_primaria?>.value = '';
      document.frmIncluir.pizza.value = '';
      document.frmIncluir.tipo.value = '';
      document.frmIncluir.sugestao.checked = false;
      document.frmIncluir.novidade.checked = false;
      
      marcaTodosEstado('marcar_tamanho', false);
      marcaTodosEstado('marcar_ingrediente', false);
      
      // Limpando todos os campos input para Preço e Fidelidade
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('preco')) { 
          input[i].value = ''; 
        }
      }
      
      var input = document.getElementsByTagName('input');
      for (var i = 0; i < input.length; i++) {
        if(input[i].name.match('fidelidade')) { 
          input[i].value = ''; 
        }
      }
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
    }
  });
});

</script>
<form name="frmPizzariaFilt" method="post">

<? echo ucfirst(TIPO_EMPRESA) ?>: <select name="cod_pizzarias_filt" style="width: 300px;">
<option value=''>Selecione uma Lanchonete</option>
<?
$con = conectabd();

$SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN ($cod_pizzarias_usuario) ORDER BY nome";
$resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          
while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) 
{
	echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
	if($cod_pizzaria_filt == $objBuscaPizzarias->cod_pizzarias)
		echo 'selected';
	echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
}
desconectabd($con);
?>
</select>
<input class="botaoAzul" type="submit" value="Filtrar">
</form>

<div id="tabs">
<div class="menuTab">
<ul>
	<li><a href="javascript:;">Editar</a></li>
	<li><a href="javascript:;">Incluir</a></li>
</ul>
</div>

<!-- Tab Editar -->
<div class="painelTab">
<table>
	<tr>

		<!-- Conteúdo -->
		<td class="conteudo">
		
		  <div id='botao_copiar'></div>
		<form name="frmExcluir" method="post"
			onsubmit="return verificaCheckbox(this)">
<? if($cod_pizzaria_filt > 0) : ?>
      <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
         <tr>
           <!-- <td width="150">
            <select name="cod_impressoras" style="width: 150px;">
              <?
              $con = conectabd();
              
              $SqlBuscaImpressoras = "SELECT * FROM ipi_impressoras ORDER BY nome_impressora";
              $resBuscaImpressoras = mysql_query($SqlBuscaImpressoras);
              
              while($objBuscaImpressoras = mysql_fetch_object($resBuscaImpressoras)) {
                echo '<option value="'.$objBuscaImpressoras->cod_impressoras.'" ';
                
                /*if($cod_Impressoras == $objBuscaImpressoras->cod_Impressoras)
                  echo 'selected';*/
                
                echo '>'.bd2texto($objBuscaImpressoras->nome_impressora).'</option>';
              }
              
              desconectabd($con);
              ?>
            </select>
          </td>-->
            <td><input class="botaoAzul" type="submit" value="Excluir Selecionados"></td>
          </tr>
      </table>
      <table class="listaEdicao" cellpadding="0" cellspacing="0">
  			<thead>
  				<tr>
  					<td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
  					<td align="center">Taxa</td>
  					<td align="center">Tipo</td>
            <td align="center" width="150">Valor</td>
  				</tr>
  			</thead>
  			<tbody>
            <?
          
          $con = conectabd();
          
          $SqlBuscaTaxas = "SELECT t.* FROM $tabela t ORDER BY taxa ";// WHERE cod_pizzarias = ".validaVarPost('cod_pizzarias_filt')." 
          //echo $SqlBuscaTaxas;
          $resBuscaTaxas = mysql_query($SqlBuscaTaxas);
          $i=0;
          while ( $objBuscaTaxas = mysql_fetch_object($resBuscaTaxas) )
          {
            echo '<tr>';
            
            echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $objBuscaTaxas->$chave_primaria . '"></td>';
            
            echo '<td align="center"><a href="javascript:;" onclick="editar(' . $objBuscaTaxas->$chave_primaria . ')">' . bd2texto($objBuscaTaxas->taxa) . '</a></td>';
            echo '<td align="center">' . bd2texto($objBuscaTaxas->tipo_taxa) . '</td>';

	          $sql_precos = "select valor from ipi_mesas_taxas_pizzarias mtp where mtp.cod_mesas_taxas = ".$objBuscaTaxas->cod_mesas_taxas." and mtp.cod_pizzarias = '$cod_pizzaria_filt'";
            //echo $sql_precos;
	          $res_precos = mysql_query($sql_precos);
	          $obj_precos = mysql_fetch_object($res_precos);
          	echo '<td align="center">'.($objBuscaTaxas->tipo_taxa=='VALOR' ?'R$' :'' ).' '.bd2moeda($obj_precos->valor).'&nbsp;'.($objBuscaTaxas->tipo_taxa=='PERCENTUAL' ?'%' :'' ).'</td>';
          	
            if($obj_precos->preco!="") $i++;
            
            echo '</tr>';
          }
          if($cod_pizzaria_filt !="" && $cod_pizzaria_filt !="1")
          {
  		      echo '<script> $("botao_copiar").innerHTML = "<input type=\'button\' class=\'botao\' id=\'botao_copiar\' onclick=\'copiar_precos()\' value=\'Copiar preços da pizzaria matriz\' />" ;</script>';
          }
          desconectabd($con);
          
          ?>
            
            </tbody>
  		</table>
    <? endif; ?>
				<input type="hidden" name="acao" value="excluir">
				<input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
        </form>
		</td>

		<!-- Conteúdo -->

		<!-- Barra Lateral -->
<!-- 		<td class="lateral">
		<div class="blocoNavegacao">
		<ul>
			<li>Preços</li>		
			<li><a href="ipi_preco_adicionais.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço adicionais</a></li>
      <li><a href="ipi_preco_ingredientes.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço de Ingredientes</a></li>
      <li><a href="ipi_preco_bebida.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço de Bebidas</a></li>
      <li><a href="ipi_preco_pizza.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Preço de Lanches</a></li>
		</ul>
		</div>
		</td> -->
		<!-- Barra Lateral -->

	</tr>
</table>
</div>
<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">
    <?
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    if ($codigo > 0)
    {
        $objBusca = executaBuscaSimples("SELECT * FROM $tabela t left join ipi_mesas_taxas_pizzarias mtp on mtp.cod_mesas_taxas = t.cod_mesas_taxas and mtp.cod_pizzarias = $cod_pizzaria_filt WHERE t.$chave_primaria = $codigo");
    }
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data"
	onsubmit="return validaRequeridos(this)">

		<table align="center" class="caixa" cellpadding="0" cellspacing="0">
		
	<tr>
		<td class="legenda tdbl tdbr tdbt"><label 
			for="Taxa">Taxa</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr"><input type="text"
			name="Taxa" id="Taxa" maxlength="45" size="45"
			value="<?
echo texto2bd($objBusca->taxa)?>" readonly='readonly'></td>
	</tr>

<tr>
    <td class="legenda tdbl tdbr "><label 
      for="Taxa">Valor</label></td>
  </tr>
  <tr>     
    <td class="tdbl tdbr"><input type="text" name="valor" id="valor" maxsize="5" size="6" value="<? echo  bd2moeda($objBusca->valor)  ?>" onKeyPress="return formataMoeda(this, '.', ',', event)"></td>
  </tr>

	<tr>
		<td colspan="2" align="center" class="tdbl tdbb tdbr"><input
			name="botao_submit" class="botao" type="submit" value="Cadastrar"></td>
	</tr>

</table>

<input type="hidden" name="acao" value="editar"> <input type="hidden"
	name="<?
echo $chave_primaria?>" value="<?
echo $codigo?>">
<input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
</form>
</div>
<!-- Tab Incluir --></div>

<?
rodape();
?>
