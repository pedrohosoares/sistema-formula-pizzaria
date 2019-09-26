<?php

/**
 * ine_envio_emails.php: Envio de E-Mails
 * 
 * Índice: cod_mensagens
 * Tabela: ine_mensagens
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Envio de E-Mails');

$acao = validaVarPost('acao');

$chave_primaria = 'cod_mensagens';
$tabela = 'ine_mensagens';
$codigo_usuario = $_SESSION['usuario']['codigo'];
if($acao == 'enviar') {

  $data_envio = data2bd(validaVarPost('data_envio'));
  $cod_mensagens = validaVarPost('cod_mensagens');
  $arr_bairro = $_POST['bairro'];

  if($data_envio>=date('Y-m-d'))
  {
    $con = conectabd();
/*    echo "<pre>";
      print_r($_POST);
      echo "</pre>";*/
      //die();
    if(is_array($arr_bairro)) {
      //$indicesSql = texto2bd(implode("','", $arr_bairro));
      //$bairro_selecionados = texto2bd(implode("'&#&'", $indicesSql));
      //$cidades_selecionadas = texto2bd(implode("'&#&'", $indicesSql));

      $query = '';
      $log = '';
      foreach ($arr_bairro as $cidade => $bairros) 
      {
        if($query!="")
          $query .= ' OR ';

        $bairros = explode(',',filtraCaracteresSql(implode(',',$bairros)));
        $log .= $cidade.":".implode(',',$bairros).". ";
        $query .= '( e.cidade in("'.filtraCaracteresSql($cidade).'") and e.bairro in("'.(implode('","',$bairros)).'") )';
      }

      $sql_resgistrar_envio = sprintf("INSERT INTO ine_disparo_mensagens(cod_mensagens,cod_usuarios_disparo,data_agendamento, observacao, data_hora_disparo) values(%d,%d,'%s','%s',NOW())",$cod_mensagens,$codigo_usuario,$data_envio,$log);
      //echo $sql_resgistrar_envio;
      $res_resgistrar_envio = mysql_query($sql_resgistrar_envio);

      $cod_disparo_mensagens = mysql_insert_id();

      $sqlInsert = "INSERT INTO ine_emails_cadastro_ine_mensagens (cod_mensagens, cod_emails_cadastro, situacao,cod_disparo_mensagens, agendamento) (SELECT DISTINCT $cod_mensagens, cod_emails_cadastro, 'NOVO','".$cod_disparo_mensagens."', '".$data_envio."' FROM ine_emails_cadastro ec INNER JOIN ipi_clientes c ON (ec.cod_ligacao = c.cod_clientes) INNER JOIN ipi_enderecos e ON (e.cod_clientes = c.cod_clientes) WHERE $query AND ec.ativo = 1)";
      //echo $sqlInsert;
      $sql_logar_envio = sprintf("INSERT INTO ine_log(cod_mensagens,cod_usuario_enviador,cod_disparo_mensagens,email_envio,tipo_retorno_envio,data_agendamento, observacao, data_hora_envio) values(%d,%d,%d,'%s','%s','%s','%s',NOW())",$cod_mensagens,$codigo_usuario,$cod_disparo_mensagens,'','AGENDAMENTO_DISPARO',$data_envio,$log);
      //echo $sql_logar_envio;
      $res_logar_envio = mysql_query($sql_logar_envio);
    }
    else {

      $sql_resgistrar_envio = sprintf("INSERT INTO ine_disparo_mensagens(cod_mensagens,cod_usuarios_disparo,data_agendamento, observacao, data_hora_disparo) values(%d,%d,'%s','%s',NOW())",$cod_mensagens,$codigo_usuario,$data_envio,'');
      //echo $sql_resgistrar_envio;
      $res_resgistrar_envio = mysql_query($sql_resgistrar_envio);

      $cod_disparo_mensagens = mysql_insert_id();

      $sqlInsert = "INSERT INTO ine_emails_cadastro_ine_mensagens (cod_mensagens, cod_emails_cadastro, situacao,cod_disparo_mensagens, agendamento) (SELECT $cod_mensagens, cod_emails_cadastro, 'NOVO','".$cod_disparo_mensagens."','".$data_envio."' FROM ine_emails_cadastro WHERE ativo = 1)";    

      $sql_logar_envio = sprintf("INSERT INTO ine_log(cod_mensagens,cod_usuario_enviador,cod_disparo_mensagens,email_envio,tipo_retorno_envio,data_agendamento, observacao, data_hora_envio) values(%d,%d,%d,'%s','%s','%s','%s',NOW())",$cod_mensagens,$codigo_usuario,$cod_disparo_mensagens,'','AGENDAMENTO_DISPARO',$data_envio,'');
      //echo $sql_logar_envio;
      $res_logar_envio = mysql_query($sql_logar_envio);

    }
    
    //echo $sqlInsert;
    //die('foi1');
    if (mysql_query($sqlInsert)) {
      mensagemOk('E-mail enviado com êxito!');
    }
    else {
      mensagemErro('Erro ao enviar o e-mail', 'Esta mensagem já se encontra cadastrada para envio nesssa data; Por favor, aguarde até a mensagem ser enviada por completo.');
    }

    
    desconectabd($con);
  }else
  {
    mensagemErro('Erro ao enviar o e-mail', 'A data para enviar não pode ser menor que a data de hoje.');
  }
}

?>
<script type="text/javascript" src="../lib/js/calendario.js"></script>
<script type="text/javascript">  

function mostrar_mensagem(obj) {
  var url = 'cod_mensagens=' + obj.value;

  if(obj.value > 0) {
    $('carrega_mensagem').setStyle('border', '1px solid #3768AD');
  }
  else {
    $('carrega_mensagem').setStyle('border', 'none');
  }

  new Request.HTML({
    url: 'ine_envio_emails_ajax.php',
    update: $('carrega_mensagem')
  }).send(url);
}

function validarForm(envio) {

 if (envio.cod_mensagens.value == "") {
    alert('Campo Mensagem Requerido.');
    envio.cod_mensagens.focus();
    return false;
  	}

  	if (envio.data_envio.value == "") {
    alert('Campo Data de envio Requerido.');
    envio.data_envio.focus();
    return false;
    }
    
  /*if (envio.email.value == "") {
    alert('Campo E-Mail Requerido.');
    envio.email.focus();
    return false;
  	}*/
  	
  return true; 
}
</script>

<form id="envio" name="envio" method="post" action="<? echo $PHP_SELF; ?>" onsubmit="return validarForm(this);">

<table align="center" class="caixa" cellpadding="0" cellspacing="0">
  <tr>
    <td class="tdbl tdbt tdbr">
      <table>
        <tr>
        <td class="legenda "><label class="requerido" for="cod_mensagens">Mensagem</label></td><td class=''>&nbsp;</td><td class='legenda'><label class="requerido" for="data_envio">Data para envio</label></td></tr>
        <tr><td class=" sep">
          <select class="requerido" name="cod_mensagens" id="cod_mensagens" style="width: 260px;" onchange="mostrar_mensagem(this)" >
            <option value=""></option>
            
            <?
            $con = conectabd();
            
            $sqlMensagem = "SELECT * FROM ine_mensagens";
            $resMensagem = mysql_query($sqlMensagem);
        
            while ($objMensagem = mysql_fetch_object($resMensagem)) {
              echo '<option value="'.$objMensagem->cod_mensagens.'">'.bd2texto($objMensagem->assunto).'</option>';
            }
            
            desconectabd($con);
            ?>
          </select>
        </td>
        <td class='sep'>&nbsp;</td>
        <td class="sep"><input class="requerido" type="text"
              name="data_envio" id="data_envio" size="8"
              value="<?
              echo date('d/m/Y') ?>"
              onkeypress="return MascaraData(this, event)"> &nbsp; <a
              href="javascript:void(0);" id="botao_data_envio"><img
              src="../lib/img/principal/botao-data.gif"></a>
              <script>new vlaDatePicker('data_envio', {openWith: 'botao_data_envio'});</script></td>
        </tr>
      </table>
    </td>
  </tr>
  
  <tr><td class="legenda tdbl tdbr"><label for="bairro">Bairro(s)</label></td></tr>
  <tr><td class="tdbl tdbr sep">
    <?
    $con = conectabd();
    
    $sqlBairros = "SELECT DISTINCT cidade, bairro FROM ipi_enderecos e INNER JOIN ipi_clientes c ON (c.cod_clientes = e.cod_clientes) WHERE (bairro IS NOT NULL AND bairro <> '') AND (c.origem_cliente = 'NET' OR c.origem_cliente= 'TEL') ORDER BY cidade, bairro";
    $resBairros = mysql_query($sqlBairros);
    $numBairros = mysql_num_rows($resBairros);
    // echo $sqlBairros;
    echo "<table cellspacing='5' cellpadding='0' border='0' width='900'>";
    echo "<tr><td valign='top' width='200'>";
    
    $divisor = floor($numBairros / 3);
    
    if (($numBairros%3)!=0)
        $divisor++;
      
    for($a = 0; $a < $numBairros; $a ++) {
      if ((($a%$divisor) == 0) && ($a != 0))
  	  echo "</td><td valign='top' width='200'>";
  	
    	$objBairros = mysql_fetch_object($resBairros);
    	
    	echo "<small>";
    	echo "<input type='checkbox' name='bairro[".$objBairros->cidade."][]' value='".$objBairros->bairro."' style='border: 0; background: none;'/>&nbsp;";
    	echo bd2texto($objBairros->cidade)." - ".bd2texto($objBairros->bairro)."<br/>";
    	echo "</small>";
    }
    echo "</td></tr>";
    echo "</table>";
	
	desconectabd($con);
	
    ?>
    
  </td></tr>

  <!-- 
  <tr><td class="legenda tdbl tdbr"><label class="requerido" for="agendamento">Agendamento</label></td></tr>
  <tr><td class="tdbl tdbr sep"><input class="requerido" type="text" name="agendamento" id="agendamento" maxlength="45" size="45" onkeypress="return MascaraDataHora(this, event)" ></td></tr>
   -->
  
  <tr><td align="center" class="tdbl tdbr sep"><input name="botao_submit" class="botao" type="submit" value="Enviar E-mail de Marketing"></td></tr>
  
  <tr><td class="tdbl tdbb tdbr sep" align="center" id="carrega_mensagem" style="padding: 20px;"></td></tr>
</table>

<input type="hidden" name="acao" value="enviar"/>

</form>

<br><br>

<? rodape(); ?>
