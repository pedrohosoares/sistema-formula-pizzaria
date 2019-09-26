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

cabecalho('Preços de Ingredientes Adicionais por pizzarias');

$acao = validaVarPost('acao');

$tabela = 'ipi_ingredientes';
$chave_primaria = 'cod_ingredientes';
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);
if(validaVarGet("cod_filt", '/[0-9]+/')!="")
{
$cod_pizzaria_filt = $cod_filt;

}
if(validaVarPost("cod_filt", '/[0-9]+/')!="")
{
$cod_pizzaria_filt = $cod_filt;

}
if(validaVarPost('cod_pizzarias_filt')!="")
{
$cod_pizzaria_filt = validaVarPost('cod_pizzarias_filt');
}
//echo "FILT".$cod_pizzaria_filt."/FILT";
switch ($acao)
{
    case 'editar' :
        $codigo = validaVarPost($chave_primaria);
        $ingrediente = validaVarPost('ingrediente');
        $tamanho = validaVarPost('tamanho');
        $preco_troca = validaVarPost('preco_troca');
        $tamanho_checkbox = validaVarPost('tamanho_checkbox');
				$consumo_adicional = validaVarPost('consumo_adicional');
        $preco = validaVarPost('preco');
				$cod_pizzarias = validaVarPost('checkpizzaria');
        
        $con = conectabd();
        
    	
        if($cod_pizzaria_filt)
				{
					$cod_pizzaria = $cod_pizzaria_filt;
          /*if (is_array($tamanho))
          {
              $SqlDelTamanho = "DELETE FROM ipi_ingredientes_ipi_tamanhos WHERE $chave_primaria = $codigo and cod_pizzarias = $cod_pizzaria";
              $resDelTamanho = mysql_query($SqlDelTamanho);
              //echo "<br />D: ".$SqlDelTamanho;
              $resDelTamanho = true;
          }
          else
          {
              $resDelTamanho = true;
          }
          
          if ($resDelTamanho)
          {*/
          		$resEdicaoTamanho = true;
              if (is_array($tamanho_checkbox))
              {
                  for($t = 0; $t < count($tamanho); $t++)
                  {
                      if (in_array($tamanho[$t], $tamanho_checkbox))
                      {
                          $cor_preco = ($preco[$t] > 0) ? moeda2bd($preco[$t]) : 0;
                          $cor_consumo = ($consumo_adicional[$t] > 0) ? $consumo_adicional[$t] : 0;
                          $cor_preco_troca = ($preco_troca[$t] > 0) ? moeda2bd($preco_troca[$t]) : 0;
                           
                          $SqlEdicaoTamanho = sprintf("UPDATE ipi_ingredientes_ipi_tamanhos set preco_troca = '%s' WHERE cod_ingredientes = '$codigo' AND cod_tamanhos= '".$tamanho[$t]."' and cod_pizzarias = '$cod_pizzaria'",$cor_preco_troca);
                          echo "<br />G: ".$SqlEdicaoTamanho;
                          $resEdicaoTamanho &= mysql_query($SqlEdicaoTamanho);
                      //    echo $SqlEdicaoTamanho."<br>";
                      }
                  }
              }
              
              
              if ($resEdicaoTamanho)
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
            mensagemErro('Erro ao dalterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
			

        desconectabd($con);
        break;
    case 'copiar_precos' :
    		$cod_matriz = validaVarPost('cod_matriz');
    		$cod_copiar = validaVarPost('cod_filt');
    		
    		$con = conectar_bd();
    		$SqlDelTamanho = "DELETE FROM ipi_ingredientes_ipi_tamanhos WHERE cod_pizzarias = $cod_copiar";
    		
        $resDelTamanho = mysql_query($SqlDelTamanho);
		                  //echo "<br />D: ".$SqlDelTamanho;
        $res_adicionar_preco = true;
				if($resDelTamanho)
				{    		
  				$sql_pegar_precos = "select * from ipi_ingredientes_ipi_tamanhos where cod_pizzarias = $cod_matriz";
    			$res_pegar_precos = mysql_query($sql_pegar_precos);
    			
    			while($obj_pegar_precos = mysql_fetch_object($res_pegar_precos))
    			{
    					$sql_adicionar_preco = "insert into ipi_ingredientes_ipi_tamanhos(cod_ingredientes,cod_tamanhos,cod_pizzarias,preco,preco_troca,quantidade_estoque_extra) values (".$obj_pegar_precos->cod_ingredientes.",".$obj_pegar_precos->cod_tamanhos.",".$cod_copiar.",".$obj_pegar_precos->preco.",".$obj_pegar_precos->preco_troca.",".$obj_pegar_precos->quantidade_estoque_extra.")";
    					$res_adicionar_preco &= mysql_query($sql_adicionar_preco);
    					
    					/*if(!$res_adicionar_preco)
    					{
    					 echo $sql_adicionar_preco;
    					}*/
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
<? if($cod_pizzaria_filt!=""): ?>
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
    'value': '<? echo "$cod_pizzaria_filt" ?>'
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
      'value': '<? echo $cod_pizzaria_filt ?>'
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
  document.getElementById('preco_troca_' + cod).value = '';
  document.getElementById('consumo_adicional_' + cod).value = '';
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
  <? endif; ?>
</script>
<form name="frmPizzariaFilt" method="post">

Pizzaria: <select name="cod_pizzarias_filt" style="width: 300px;">
<option value=''>Selecione uma Pizzaria</option>
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
<input type="hidden" name="acao" value="filtrar"/>
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

<table class="listaEdicao" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td>
					<td align="center">Ingrediente</td>
					<?
					 $con = conectabd();
            $sql_precos = "select tamanho,cod_tamanhos from ipi_tamanhos order by cod_tamanhos";
            $res_precos = mysql_query($sql_precos);
           	while($obj_precos = mysql_fetch_object($res_precos))
            {
             echo '<td align="center">'.$obj_precos->tamanho.'</td>';
            }
						desconectar_bd($con);
					?>
				</tr>
			</thead>
			<tbody>
          <?
        
        $con = conectabd();
        if($cod_pizzaria_filt!="")
        {
          $SqlBuscaIngredientes = "SELECT t.*,(SELECT ingrediente from $tabela where cod_ingredientes = t.cod_ingredientes_troca) as nome_troca FROM $tabela t where t.adicional =1 and t.consumo = 0 and t.ativo =1 and cod_ingredientes_troca>0 GROUP BY t.ingrediente ORDER BY t.ingrediente ";// WHERE cod_pizzarias = ".validaVarPost('cod_pizzarias_filt')." 
         // echo $SqlBuscaIngredientes;
          $resBuscaIngredientes = mysql_query($SqlBuscaIngredientes);
          $i=0;
          while ( $objBuscaIngredientes = mysql_fetch_object($resBuscaIngredientes) )
          {
              echo '<tr>';
              
              echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="' . $objBuscaIngredientes->$chave_primaria . '"></td>';
              
              echo '<td align="center"><a href="javascript:;" onclick="editar(' . $objBuscaIngredientes->$chave_primaria . ')">' . bd2texto($objBuscaIngredientes->nome_troca) . '</a></td>';
              $sql_tam = "select tamanho,cod_tamanhos from ipi_tamanhos order by cod_tamanhos";
              $res_tam = mysql_query($sql_tam);
             	while($obj_tam = mysql_fetch_object($res_tam))
              {
              
  		          $sql_precos = "select tp.preco_troca,t.tamanho,t.cod_tamanhos from ipi_ingredientes_ipi_tamanhos tp inner join ipi_tamanhos t on t.cod_tamanhos = tp.cod_tamanhos where tp.cod_ingredientes = ".$objBuscaIngredientes->cod_ingredientes." and tp.cod_tamanhos = ".$obj_tam->cod_tamanhos." and tp.cod_pizzarias = '$cod_pizzaria_filt' order by cod_tamanhos";
  		          $res_precos = mysql_query($sql_precos);
  		          $obj_precos = mysql_fetch_object($res_precos);
  	          	echo '<td align="center">'.($obj_precos->preco_troca ?'R$' :'' ).' '.bd2moeda($obj_precos->preco_troca).'&nbsp; </td>';
              	if($obj_precos->preco_troca!="") $i++;
              }
              echo '</tr>';
          }
          if($cod_pizzaria_filt !="" && $cod_pizzaria_filt !="1")
          {
  		     // echo 'aaa<script>alert('.$i.');</script>';
  		      //if($i<=9)
  		     // {
  		      	echo '<script> $("botao_copiar").innerHTML = "<input type=\'button\' class=\'botao\' id=\'botao_copiar\' onclick=\'copiar_precos()\' value=\'Copiar preços da pizzaria matriz\' />" ;</script>';
  		      	
  		      //}
          }
          desconectabd($con);
        }
        
        ?>
          
          </tbody>
		</table>
				<input type="hidden" name="acao" value="excluir"></form>
				<input type="hidden" name="cod_filt" value="<? echo $cod_pizzaria_filt ?>" >
		</td>
		<!-- Conteúdo -->

		<!-- Barra Lateral -->
		<td class="lateral">
		<div class="blocoNavegacao">
		<ul>
			<li>Preços</li>
			<li><a href="ipi_preco_adicionais.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Gergilim</a></li>
			<li><a href="ipi_preco_borda.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Bordas</a></li>
			<li><a href="ipi_preco_pizza.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Pizzas</a></li>
			<li><a href="ipi_preco_bebida.php?cod_filt=<? echo $cod_pizzaria_filt ;?>">Bebidas</a></li>
		</ul>
		</div>
		</td>
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
        $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
        
    }
    ?>
    
    <form name="frmIncluir" method="post" enctype="multipart/form-data"
	onsubmit="return validaRequeridos(this)">

		<table align="center" class="caixa" cellpadding="0" cellspacing="0">
		
	<tr>
		<td class="legenda tdbl tdbr tdbt"><label 
			for="Ingrediente">Ingrediente</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr"><input type="text"
			name="ingrediente" id="ingrediente" maxlength="45" size="45"
			value="<?
echo texto2bd($objBusca->ingrediente)?>" readonly></td>
	</tr>

	<tr>
		<td class="tdbl tdbr sep">
		<table class="listaEdicao" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<td align="center" width="20"><input type="checkbox"
						class="marcar_tamanho"
						onclick="marcaTodosEstado('marcar_tamanho', this.checked);"></td>
					<td align="center"><label>Tamanho</label></td>
					<!-- <td align="center"><label>Preço</label></td> -->
					<td align="center"><label>Preço <br/> de <br/> troca</label></td>
					<td align="center"><label>Qto Consumo <br />do <br/>Adiconal (gramas)</label></td>
				</tr>
			</thead>
			<tbody>

        <?
        $con = conectar_bd();
        $SqlBuscaTamanhos = "SELECT * FROM ipi_tamanhos ORDER BY tamanho";
        $resBuscaTamanhos = mysql_query($SqlBuscaTamanhos);
        
        while ( $objBuscaTamanhos = mysql_fetch_object($resBuscaTamanhos) )
        {
          echo '<tr>';
          
          if ($codigo > 0)
              $objBuscaPrecosTamanho = executaBuscaSimples(sprintf("SELECT * FROM ipi_ingredientes_ipi_tamanhos WHERE cod_ingredientes = '%d' AND cod_tamanhos = '%d' and cod_pizzarias= '%d' order by cod_tamanhos", $codigo, $objBuscaTamanhos->cod_tamanhos,$cod_pizzaria_filt), $con);
          else
              $objBuscaPrecosTamanho = null;
          
          echo '<input type="hidden" name="tamanho[]" value="' . $objBuscaTamanhos->cod_tamanhos . '">';
          
          if ($objBuscaPrecosTamanho)
              echo '<td align="center"><input type="checkbox" class="marcar_tamanho" checked="checked" name="tamanho_checkbox[]" value="' . $objBuscaTamanhos->cod_tamanhos . '" onclick="limpaPrecoFidelidade(' . $objBuscaTamanhos->cod_tamanhos . ')"></td>';
          else
              echo '<td align="center"><input type="checkbox" class="marcar_tamanho" name="tamanho_checkbox[]" value="' . $objBuscaTamanhos->cod_tamanhos . '" onclick="limpaPrecoFidelidade(' . $objBuscaTamanhos->cod_tamanhos . ')"></td>';
          
          echo '<td><label>' . $objBuscaTamanhos->tamanho . '</label></td>';
          echo '';
          
           echo '<td align="center"><input type="text" name="preco_troca[]" id="preco_troca' . $objBuscaTamanhos->cod_tamanhos . '" maxsize="5" size="6" value="' . bd2moeda($objBuscaPrecosTamanho->preco_troca) . '" onKeyPress="return formataMoeda(this, \'.\', \',\', event)"></td>';
           
          echo '<td align="center"><input type="text" name="consumo_adicional[]" id="consumo_adicional_' . $objBuscaTamanhos->cod_tamanhos . '" maxsize="5" size="3" value="' . $objBuscaPrecosTamanho->quantidade_estoque_extra . '" onKeyPress="return ApenasNumero(event)"></td>';
          
          echo '</tr>';
        }
        
        desconectabd($con);
        ?>
        
        </tbody>
		</table>
		</td>
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
