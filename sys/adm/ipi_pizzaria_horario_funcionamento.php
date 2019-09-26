<?php

/**p: Cadastro de Pizzarias
 * 
 * Índice: cod_pizzarias
 * Tabela: ipi_pizzarias
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de Horarios de Funcionamentos das '.TIPO_EMPRESAS);

$acao = validaVarPost('acao');

$tabela = 'ipi_pizzarias';
$chave_primaria = 'cod_pizzarias';

$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab');
$dia_semana_completo = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
/*if($acao!="")
{
  echo "<pre>";
  print_r($_POST);
  echo "</pre>";
 // die();
}*/
switch ($acao)
{
    case 'excluir' :
        $excluir = validaVarPost('excluir');
        $indicesSql = implode(',', $excluir);
        
        $con = conectabd();
        
       // $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
        
        if (mysql_query($SqlDel))
            mensagemOk('Os registros selecionados foram excluídos com sucesso!');
        else
            mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se a pizzaria não está responsável por algum bairro (cep).');
        
        desconectabd($con);
        break;

    case 'editar' :
        $codigo = validaVarPost($chave_primaria);
        $horarios = validaVarPost('horarios');
        $arr_funcionamento = $_POST['chk_dia'];//validaVarPost('chk_dia[]');
        $con = conectabd();
          
        $sql_atualizar_pizzarias = "UPDATE ipi_pizzarias set horarios = '$horarios' where cod_pizzarias = '$codigo'";
        $res_atualizar_pizzarias = mysql_query($sql_atualizar_pizzarias);

        $sql_dropar_dados = "DELETE FROM ipi_pizzarias_funcionamento where cod_pizzarias = '$codigo'";
        $res_dropar_dados = mysql_query($sql_dropar_dados);
        /*echo "<br/><br/><br/><pre>";
         print_r($arr_funcionamento);
        echo "</pre>";*/
        if($res_dropar_dados)
        {
          //$dias = count($arr_funcionamento);
          for($d=0;$d<7;$d++)
          {
            for($h = 0; $h<count($arr_funcionamento[$d]); $h++)
            {
             //die("d=$d-hor=".$arr_funcionamento[$d][$h]."-");
              $hor = explode("_",$arr_funcionamento[$d][$h]);

              $sql_funcionamento = sprintf("INSERT INTO ipi_pizzarias_funcionamento (cod_pizzarias, dia_semana,horario_inicial,horario_final) VALUES ('%s', '%s', '%s', '%s')", $codigo, $d,$hor[0],$hor[1]);
             // echo "<Br>sql_funcionamento: ".$sql_funcionamento;
              $res_funcionamento = mysql_query($sql_funcionamento);
            }
          }
                
          if ($res_dropar_dados && $res_funcionamento && $res_atualizar_pizzarias)
          {
              mensagemOk('Registro adicionado com êxito!');
          } 
          else
          {
              mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
          }
    
        }

      desconectabd($con);
      break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

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

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<?
echo $chave_primaria?>.value > 0) {
    <?
    if ($acao=='')
        echo 'tabs.irpara(1);';
    ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) {
      // FELIPE> Comentei por causa da bucha de limpar todos os horários...depois eu resolvo isso
      
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      
      document.frmIncluir.horarios.value = "De Segunda a Sexta-Feira\n- Das 18h00 às 00h00\n\nSábados, Domingos e Feriados\n - Das 18h00 às 00h00";
      
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
<table>
	<tr>

		<!-- Conteúdo -->
		<td class="conteudo">

		<form name="frmExcluir" method="post"
			onsubmit="return verificaCheckbox(this)">

		<table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
			<tr>
				<td><input class="botaoAzul" type="submit"
					value="Excluir Selecionados"></td>
			</tr>
		</table>

		<table class="listaEdicao" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
					<td align="center" width="20"><input type="checkbox"
						onclick="marcaTodos('marcar');"></td>
					<td align="center">Código (<? echo ucfirst(TIPO_EMPRESA)?>)</td>
					<td align="center"><? echo ucfirst(TIPO_EMPRESA)?></td>
					<td align="center">Empresa</td>
					<td align="center">Num. Franqueados</td>
					<td align="center">Situação</td>
				</tr>
			</thead>
			<tbody>
          
			<?
		    $con = conectabd();
		    $SqlBuscaRegistros = "SELECT p.nome, p.situacao, p.cod_pizzarias, e.nome_empresa, (SELECT count(cod_franqueados)  FROM ipi_franqueados f WHERE f.cod_pizzarias = p.cod_pizzarias ) total_franqueados FROM $tabela p LEFT JOIN ipi_empresas e ON (p.cod_empresas = e.cod_empresas) ORDER BY p.nome";
		    $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
		    while ( $objBuscaRegistros = mysql_fetch_object($resBuscaRegistros) )
		    {
		        echo '<tr>';
		        
		        echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
		        echo '<td align="center">'.bd2texto($objBuscaRegistros->cod_pizzarias).'</td>';
		        echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->nome).'</a></td>';
		        echo '<td align="center">'.bd2texto($objBuscaRegistros->nome_empresa).'</td>';
		        echo '<td align="center">'.bd2texto($objBuscaRegistros->total_franqueados).'</td>';
		        echo '<td align="center">'.bd2texto($objBuscaRegistros->situacao).'</td>';
		        
		        echo '</tr>';
		    }
		    desconectabd($con);
		    ?>
          
          </tbody>
		</table>

		<input type="hidden" name="acao" value="excluir"></form>

		</td>
		<!-- Conteúdo -->

		<!-- Barra Lateral -->
		<td class="lateral">
		<div class="blocoNavegacao">
		<ul>
			<li><a href="ipi_cep.php">CEP de Entrega</a></li>
			<li><a href="ipi_entregador.php">Entregadores</a></li>
			<li><a href="ipi_franqueados.php">Franqueados</a></li>
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
    
    if ($codigo>0)
    {
        $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    }
    ?>
    
    <form name="frmIncluir" method="post"
	onsubmit="return (validaRequeridos(this) && validar_formas_pagamento())" enctype="multipart/form-data">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
	<tr>
		<td class="legenda tdbl tdbr tdbt"><label>Dias de Funcionamento</label></td>
	</tr>

	<tr>
		<td class="legenda tdbl tdbr">

	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido" for="horarios">Horários
		(informativo)</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><textarea rows="10" cols="112" name="horarios"
			id="horarios"><? echo texto2bd($objBusca->horarios)?></textarea></td>
	</tr>
		
    <tr>
        <td class="tdbl tdbr sep">
		
		<div id="tabela_horarios">
		<table class="listaEdicao" cellpadding="0" cellspacing="0">
			<thead>
				<tr>
          <td align="center"><label width="15%">Horário Inicial</label></td>
          <td align="center"><label width="15%">Horário Final</label></td>
					<? for($s = 0; $s < 7; $s++): ?>
					
					<td align="center"  width="10%"><label><font color="red"><? echo $dia_semana_completo[$s] ?></font></label></td>
					
					<? endfor; ?>
				</tr>
			</thead>
			<tbody>
      
        <?
        $con = conectabd();
        
        $arr_horarios = array();

        $sql_buscar_horarios = "SELECT * from ipi_pizzarias_funcionamento where cod_pizzarias = '$codigo'";
        $res_buscar_horarios = mysql_query($sql_buscar_horarios);
        while($obj_buscar_horarios = mysql_fetch_object($res_buscar_horarios))
        {
          $arr_horarios[$obj_buscar_horarios->dia_semana][] = $obj_buscar_horarios->horario_inicial."_".$obj_buscar_horarios->horario_final;
        }
        /*echo "------<pre>";
        print_r($arr_horarios);
                echo "</pre>";*/
        for($h = 00;$h <=23; $h++ )
        {
          for($m=00;$m<60;$m+=30)
          {
            $m1 = $m;
            if($m>0)$m1--;
            echo '<tr>';
            echo "<td align='center'>".sprintf("%02d",$h).":".sprintf("%02d",$m).":00</td>";
            $m2=$m+29;
            echo "<td align='center'>".sprintf("%02d",$h).":".sprintf("%02d",$m2).":59</td>";
            
            $faixa = sprintf("%02d",$h).":".sprintf("%02d",$m).":00_".sprintf("%02d",$h).":".sprintf("%02d",$m2).":59";
            
            for($s = 0; $s < 7; $s++)
            {
              $checked = '';
              if(is_array($arr_horarios[$s]))
              {
                if(in_array($faixa, $arr_horarios[$s]))
                  $checked = "checked='checked'";
              }

              echo "<td align='center'><input type='checkbox' $checked width='10px' height='10px' name='chk_dia[$s][]' value='$faixa'/>";
               
            }
          }
          
          echo '</tr>';
        }
        
        desconectabd($con);
        
        ?>
        
        </tbody>
		</table>
		</div>
		
		
		</td>
	</tr>

	<tr>
		<td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
			class="botao" type="submit" value="Cadastrar"></td>
	</tr>

</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<? echo $chave_primaria?>" value="<? echo $codigo?>">
</form>

</div>

<!-- Tab Incluir -->
</div>

<?
rodape();
?>
