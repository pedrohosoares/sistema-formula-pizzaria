<?
if ($_POST['enviar_form']=="1")
{

  require_once 'config.php';
  require_once 'ipi_email.php';
  require_once 'bd.php';

  $con = conectar_bd();
  $email_contato = validaVarPost('email_contato');
  $nome = validaVarPost('nome');
  $mensagem = validaVarPost('mensagem');
  $telefone = validaVarPost('telefone');
  $loja = validaVarPost('loja');

  $sql_checar_cliente = "SELECT * FROM ipi_clientes WHERE email='".$email_contato."' ";
  $res_checar_cliente = mysql_query($sql_checar_cliente);
  $obj_checar_cliente = mysql_fetch_object($res_checar_cliente);

  if (($obj_checar_cliente->cod_clientes) && ($_POST['nosso_cliente'] == "sim"))
  {
    $sql_inserir_cliente = "INSERT INTO ipi_fale_conosco (cod_clientes, cod_usuarios,cod_pizzarias, pergunta_fale_conosco, respondida, data_hora_fale_conosco) VALUES ( ".$obj_checar_cliente->cod_clientes.", 0,'".$loja."', '".$mensagem."', 0, NOW())";
    $res_inserir_cliente = mysql_query($sql_inserir_cliente);
  }
  else 
  {
    $sql_inserir_nao_clientes = "INSERT INTO ipi_fale_conosco (cod_clientes, cod_usuarios,cod_pizzarias, pergunta_fale_conosco, respondida, data_hora_fale_conosco, nome, telefone, email) VALUES (0, 0, '".$loja."' ,'".$mensagem."', 0, NOW(), '".$nome."', '".$telefone."', '".$email_contato."')";
    $res_inserir_nao_clientes = mysql_query($sql_inserir_nao_clientes);
  }
 // echo "AAA".$sql_inserir_cliente." ".$sql_inserir_nao_clientes;

  if($loja!="Franqueadora")
  {
  	$loja = validaVarPost('loja','/[0-9]+/');
  	if($loja!="")
  	{
  		$sql_busca_pizzaria = "select * from ipi_pizzarias where cod_pizzarias = '$loja'";
  		$res_busca_pizzaria = mysql_query($sql_busca_pizzaria);
  		//if($mysql_num_rows($res_busca_pizzaria)>=1)
  		//{
  			$obj_pizzaria = mysql_fetch_object($res_busca_pizzaria);
  			$loja = $obj_pizzaria->cidade." - ".$obj_pizzaria->bairro;	

  		//}

		}

  }
  desconectabd($con);




  $assunto = NOME_FANTASIA." - Contato pelo formulário do site enviado por " . $nome;

  $texto .= "<br/>Formulário envio pela área de contato do site!";
  $texto .= "<br/><br/><strong>Nome:</strong> ".$nome;
  //$texto .= "<br/><br/>E-mail: ".$_POST['email'];
  $texto .= "<br/><strong>Telefone:</strong> ".$telefone;
  $texto .= "<br/><strong>Pizzaria que o atendeu:</strong> ".$loja;
  $texto .= "<br/><strong>Mensagem:</strong> ".$mensagem;

  $texto .= "<br/><br/>Responda pelo sistema.<br/>";


  $arr_aux = array();
  $arr_aux['cod_pedidos'] = 0;
  $arr_aux['cod_usuarios'] = 0;
  $arr_aux['cod_clientes'] = $obj_checar_cliente->cod_clientes;
  $arr_aux['cod_pizzarias'] = validaVarPost('loja','/[0-9]+/');
  $arr_aux['tipo'] = 'FALE_CONOSCO';
  //if (enviar_email(EMAIL_PRINCIPAL, 'pedrohenrique@internetsistemas.com.br', $assunto, $texto, $arr_aux, 'neutro'))
  if (enviar_email(EMAIL_PRINCIPAL, EMAIL_FALECONOSCO, $assunto, $texto, $arr_aux, 'neutro'))
  {
    $sql_buscar_pizzarias_email = executar_busca_simples("SELECT * FROM ipi_pizzarias WHERE cod_pizzarias = '".( validaVarPost('loja') == "Franqueadora" ? 1 : validaVarPost('loja','/[0-9]+/') )."' AND situacao = 'ATIVO'");

    if(enviar_email(EMAIL_PRINCIPAL, $sql_buscar_pizzarias_email->emails_diretoria, $assunto, $texto, $arr_aux, 'neutro'))
    {
      echo '<script> alert("E-mail enviado com sucesso!!")</script>';
    }
  }
  else
  {
    echo '<script> alert("Erro ao enviar E-mail!")</script>';
  }
  ?>

  <?	
}
?>
<script type="text/javascript" src="sys/lib/js/mascara.js"></script>
<script type="text/javascript" src="sys/lib/js/mootools-1.2-core.js"></script>

<script type="text/javascript">
function validacaoContato(frm) 
{
	
  if ( frm.nosso_cliente.value=="")
  {
    alert ("Campo 'Você já é nosso cliente' Obrigatório");
    frm.nosso_cliente.focus();
    return false;
  } 

	if ( frm.email_contato.value=="")
	{
		alert ("Campo 'E-mail' Obrigatório");
		frm.email_contato.focus();
		return false;
	}

	if (!(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(frm.email_contato.value)))
	{
		alert("E-mail digitado inválido!");
		frm.email_contato.focus();
		return (false);
	}	

  if ( frm.nome.value=="")
  {
    alert ("Campo 'Nome' Obrigatório");
    frm.nome.focus();
    return false;
  }

	if(frm.telefone.value == '') 
	{
		alert("Campo 'Telefone' é obrigatório.");
		frm.telefone.focus();
		return false;
	}
  
	if(!validarTelefone(frm.telefone.value)) 
	{
		alert('O campo Telefone não é válido ou não foi digitado corretamente - (xx) xxxx-xxxx.');
		frm.telefone.focus();
		return false;
	}

  if ( frm.loja.value=="")
  {
    alert ("Campo Qual Loja te atendeu Obrigatório");
    frm.loja.focus();
    return false;
  }

	if ( frm.mensagem.value=="")
	{
		alert ("Campo 'Mensagem' Obrigatório");
		frm.mensagem.focus();
		return false;
	}
	
		
		
	return true;
}


</script>

<div id="form_tudo" class="divcadastro">

  <form method="post" id="frmEmail"  action="<? echo $PHP_SELF; ?>" onsubmit="return validacaoContato(this);">

    <div id="fale_conosco" class="labels" style="width: 80%; margin-left: 150px; text-align: justify; background-image: url(img/fundo_faleconosco.png); background-repeat: no-repeat; padding-left: 20px; font-size: 13px; "><br/><br/>

      * Você já é nosso cliente?

      <select name="nosso_cliente" id="nosso_cliente" style="display:block">
    	  <option value="">Selecione</option>
      	<option value="sim">Sim</option>
      	<option value="nao">Não</option>
      </select><br/>


      * E-mail:<span id="lblcliente"></span><br/>
      <input type="text" id="email_contato" name="email_contato" size="20" class="campotext" size="20" onblur="javascript: checar_email(this.value);" /><br/>


    	* Nome: <br/>
      <input type="text" name="nome" id="nome" size="20" class="campotext"/><br/>

      * Telefone: <br/>
      <input type="text" name="telefone" id="telefone" size="20" class="campotext" onkeypress="return MascaraTelefone(this,event);"/><br/>

      <? 
        $con = conectar_bd(); 
        $sql_listar_pizzarias = 'SELECT cod_pizzarias, nome FROM ipi_pizzarias WHERE situacao = "ATIVO" ORDER BY nome';
        $res_listar_pizzarias = mysql_query($sql_listar_pizzarias);
      ?>

      * Qual Loja te atendeu: <br/>
      <select name="loja" id="loja">
        <option value="">Selecione</option>
          <?
          while($obj_listar_pizzarias = mysql_fetch_object($res_listar_pizzarias))
          {
            echo '<option value="'.$obj_listar_pizzarias->cod_pizzarias.'">'.$obj_listar_pizzarias->nome.'</option>';
          }
          ?>
      </select><br/>

      <? desconectar_bd($con); ?>


      * Mensagem: <br/>
      <textarea name="mensagem" id="mensagem" class="campotextarea" cols="27" rows="7"></textarea><br/>

      <br/>
      <input type="submit" name="btEnviar" alt="Clique e envie o seu contato." value="Enviar" class="btn btn-secondary" style="width:auto; margin:0" />

      <div class="obs_fale_conosco" align="left">*Este meio de comunicação não está
vinculado ao pedido online</div>


      <input type="hidden" name="enviar_form" value="1"/>

    </div>

  </form>
</div>
<div class="bottom_div"></div>

