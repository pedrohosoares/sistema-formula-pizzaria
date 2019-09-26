<?
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';
require_once 'ipi_req_carrinho_classe.php';
require_once 'classe/cliente.php';


if($_SESSION['ipi_cliente']['autenticado'] == true)
{
  echo ('Depois de autenticado não é mais necessário executar esta ação. Se deseja alterar seus dados pessoais acesse no menu ACESSO RESTRITO, Meus Dados.');
}
else
{
$acao = validaVarPost('acao', '/inserir/');

if($acao == 'inserir') 
{
  $con = conectabd();
  $email = validaVarPost('email_cadastro');
  $senha = validaVarPost('senha_cadastro');
  $nome = validaVarPost('nome');
  $cpf = validaVarPost('cpf');
  $nascimento = validaVarPost('nascimento');
  $celular = validaVarPost('celular');
  $telefone_1 = validaVarPost('telefone_1');
  $telefone_2 = validaVarPost('telefone_2');
  $cep = validaVarPost('cep');
  $endereco = validaVarPost('endereco');
  $numero = validaVarPost('numero');
  $complemento = validaVarPost('complemento');
  $edificio = validaVarPost('edificio');
  $bairro = validaVarPost('bairro');
  $cidade = validaVarPost('cidade');
  $promocoes = validaVarPost('receber_noticias');
  $estado = validaVarPost('estado');
  $sexo = validaVarPost('sexo');
  $onde_conheceu = validaVarPost('onde_conheceu');
  $referencia_cliente = validaVarPost('referencia_cliente');

  $carrinho = new ipi_carrinho();
  $cliente = new cliente();
  
  $cod_cliente = $cliente->cadastrar($email,$senha,$nome,$cpf,$nascimento,$celular,$sexo,$promocoes,$onde_conheceu);

  switch($cod_cliente)
  {
    case '-1':
      echo "<script>alert('Houve um erro de sistema ao cadastrar.');</script>";
    break;
    case '-2':
      echo "<script>alert('O e-mail/cpf digitado já está cadastrado, se você esqueceu sua senha, acesso a página \"Esqueci minha senha\"');</script>";
    break;
    default :
      $cliente->cadastrar_endereco($cod_cliente,'Endereço Padrão',$endereco,$numero,$complemento,$edificio,$bairro,$cep,$cidade,$estado,$telefone_1,$telefone_2, $referencia_cliente);
      $cliente->enviar_email_cadastro_com_senha($cod_cliente,$senha);

      if ($carrinho->existe_pedido()) 
      {
        echo "<script>alert('O seu cadastro foi efetuado com sucessso!'); window.location = 'pagamentos';</script>";
      }
      else {
        echo "<script>alert('O seu cadastro foi efetuado com sucessso!'); window.location = 'pedidos';</script>";
      }
    break;
  }

  desconectabd($con);
}
else if ($acao == ''):

  $con = conectabd();
  $sql_dominios_bloqueados = "SELECT dominio FROM ipi_dominios_bloqueados";
  $res_dominios_bloqueados = mysql_query($sql_dominios_bloqueados);
  $num_dominos_bloqueados = mysql_num_rows($res_dominios_bloqueados);
  $dominios_bloqueados = "";
  if($num_dominios_bloqueados > 0)
  {
    $dominios_bloqueados = '("';
    while ( $obj_dominios_bloqueados = mysql_fetch_object($res_dominios_bloqueados) )
    {
      if($dominios_bloqueados == '(')
      {
        $dominios_bloqueados .= $obj_dominios_bloqueados->dominio;
      }
      else
      {
        $dominios_bloqueados .= ' '.$obj_dominios_bloqueados->dominio;      
      }
    }
    $dominios_bloqueados .= '")';
  }
  desconectabd($con);
?>


<script type="text/javascript">
function validaForm(form) {
  if(form.email_cadastro.value == '') {
    alert('Campo e-mail obrigatório.');
    form.email_cadastro.focus();
    return false;
  }
  
  if(!validarEmail(form.email_cadastro.value)) {
    alert('O campo e-mail não é válido ou não foi digitado corretamente.');
    form.email_cadastro.focus();
    return false;
  }
  
  if(form.confirmar_email.value != form.email_cadastro.value) {
    alert('O campo e-mail deve ser idêntico ao campo confirmar e-mail.');
    form.confirmar_email.focus();
    return false;
  }
  
  var arr_email = form.confirmar_email.value.split("@");
  var dominios = '<? echo $dominios_bloqueados; ?>';
  if(dominios.indexOf(arr_email[1]) != -1 ) {
    alert('O e-mail digitado é inválido. \nDomínio escrito errado: ' + arr_email[1]);
    form.email_cadastro.focus();
    return false;
  }  
  
  if(form.senha_cadastro.value == '') {
    alert('Campo senha obrigatório.');
    form.senha_cadastro.focus();
    return false;
  }
  
  if(form.nome.value == '') {
    alert('Campo nome obrigatório.');
    form.nome.focus();
    return false;
  }
  
  if(form.cpf.value == '') {
    alert('Campo CPF obrigatório.');
    form.cpf.focus();
    return false;
  }

   if(!ValidarCPF(form.cpf.value)) {
    alert('O campo CPF não é válido ou não foi digitado corretamente.');
    return false;

  }

  if(form.sexo.value == '') {
    alert('Epa! Você esqueceu de informar o seu sexo.');
    form.sexo.focus();
    return false;
  }
  
  if(form.celular.value != '') {
    if(!validarTelefone(form.celular.value)) {
      alert('O campo celular não é válido ou não foi digitado corretamente - (xx) xxxx-xxxx.');
      form.celular.focus();
      return false;
    }
  }
  
  if(form.telefone_1.value == '') {
    alert('Campo telefone local 1 obrigatório.');
    form.telefone_1.focus();
    return false;
  }
  
  if(!validarTelefone(form.telefone_1.value)) {
    alert('O campo telefone local 1 não é válido ou não foi digitado corretamente - (xx) xxxx-xxxx.');
    form.telefone_1.focus();
    return false;
  }
  
  if(form.telefone_2.value != '') {
    if(!validarTelefone(form.telefone_2.value)) {
      alert('O campo telefone local 2 não é válido ou não foi digitado corretamente.');
      form.telefone_2.focus();
      return false;
    }
  }
  
  if(form.onde_conheceu.value == '') {
    alert('Epa! Você esqueceu de informar como conheceu a fórmula.');
    form.onde_conheceu.focus();
    return false;
  }
  
  if(form.cep.value == '') {
    alert('Campo CEP obrigatório.');
    form.cep.focus();
    return false;
  }
  
  if(form.endereco.value == '') {
    alert('Campo endereço obrigatório.');
    form.endereco.focus();
    return false;
  }
  
  if(form.numero.value == '') {
    alert('Campo número obrigatório.');
    form.numero.focus();
    return false;
  }
  
  if(form.bairro.value == '') {
    alert('Campo bairro obrigatório.');
    form.bairro.focus();
    return false;
  }
  
  if(form.cidade.value == '') {
    alert('Campo cidade obrigatório.');
    form.cidade.focus();
    return false;
  }
  
  if(form.estado.value == '') {
    alert('Campo estado obrigatório.');
    form.estado.focus();
    return false;
  }

  return true;
}

function completar_endereco() {
  var cep = document.getElementById('cep').value;
  var url_var = 'cep=' + cep;
  
  if(cep != '') {  
    jQuery.ajax({
    url: 'ipi_completa_cep_ajax.php', 
    type: "POST",
    data: url_var,  
    dataType: "json",  
    success: function(retorno) {
      if(retorno == null)
      {
        alert("CEP não encontrado, digite seus dados!");

        $("#cadEndereco").fadeIn(200);
        document.getElementById('cadEndereco').style.display='block';

        document.getElementById('endereco').readOnly = false;
        document.getElementById('bairro').readOnly = false;
        document.getElementById('cidade').readOnly = false;
        document.getElementById('estado').readOnly = false;

      }
      else if(retorno.status == 'OK') 
      {
        document.getElementById('endereco').value = retorno.endereco;
        document.getElementById('bairro').value = retorno.bairro;
        document.getElementById('cidade').value = retorno.cidade;
        document.getElementById('estado').value = retorno.estado;
        $("#cadEndereco").fadeIn(200);
        document.getElementById('cadEndereco').style.display='block';
//        document.getElementById('cadBairro').style.display='block';
//        document.getElementById('cadComplemento').style.display='block';
//        document.getElementById('cadBotao').style.display='block';
        document.getElementById('numero').focus();
      }
      else if(retorno.mensagem == "Esse CEP nao existe")
      {
        alert("CEP não encontrado, digite seus dados!");

        $("#cadEndereco").fadeIn(200);
        document.getElementById('cadEndereco').style.display='block';

        document.getElementById('endereco').readOnly = false;
        document.getElementById('bairro').readOnly = false;
        document.getElementById('cidade').readOnly = false;
        document.getElementById('estado').readOnly = false;

      }
      else
      {
        alert('Erro ao completar CEP: ' + retorno.mensagem);
      }

    }});
  }
  else {
    alert('Para completar o endereço o campo CEP deverá ter um valor válido.');
  }
}

var mensagem="";
function clickIE() {if (document.all) {(mensagem);return false;}}
function clickNS(e) {if (document.layers||(document.getElementById&&!document.all)) {if (e.which==2||e.which==3) {(mensagem);return false;}}}

if (document.layers){document.captureEvents(Event.MOUSEDOWN);document.onmousedown=clickNS;}
else{document.onmouseup=clickNS;document.oncontextmenu=clickIE;}
  
document.oncontextmenu = new Function("return false")

function disableCtrlKeyCombination(e)
{
  var key;
  var isCtrl;

  if(window.event) {
    key = window.event.keyCode;     //IE
    if(window.event.ctrlKey)
      isCtrl = true;
    else
      isCtrl = false;
  }
  else {
    key = e.which;     //firefox
    if(e.ctrlKey)
      isCtrl = true;
    else
      isCtrl = false;
  }

  if(isCtrl) {
    return false;
  }
  
  return true;
}
</script>

<form id="frmCadastro" action="<? echo $PHP_SELF ?>" method="post" onsubmit="return validaForm(this);">

  <div id="formulario_cadastro" class="divcadastro">
  <div>
    <p><h3>Dados Pessoais</strong></h3></p>
    <br />
    
      <label for="email_cadastro" title="E-mail" >* Seu E-mail:</label><br/>
      <input id="email_cadastro" name="email_cadastro" type="text" class='campotextcf' /><br/>              

    

      <label for="confirmar_email" title="Confirmar E-mail" >* Confirmar e-mail:</label><br/>
      <input id="confirmar_email" name="confirmar_email" type="text" class='campotextcf' /><br/>


      <label for="senha_cadastro" title="Senha" >* Senha:</label><br/>
      <input id="senha_cadastro" name="senha_cadastro" type="password" class='campotextcf' /><br/>

      <br />

      <label for="nome" title="Nome" >* Nome completo:</label><br/>
      <input id="nome" name="nome" type="text" class='campotextcf' /><br/>


     
      <label for="cpf" title="CPF" >* CPF:</label><br/>
      <input id="cpf" name="cpf" type="text" maxlength="14" onkeypress="return MascaraCPF(this, event);" class='campotextcf' /><br/>



      <label for="sexo" title="Sexo" >* Sexo:</label><br/>
      <select name="sexo" id="sexo"  style="width: 113px">
        <option value=""></option>
        <option value="M">Masculino</option>
        <option value="F">Feminino</option>
      </select><br/>

    

      <label for="nascimento" title="Data de nascimento" > Data de nascimento:</label><br/>
      <input id="nascimento" name="nascimento" type="text" maxlength="14" onkeypress="return MascaraData(this, event);" class='campotextcf' />
      <span class="fonte10">Responda e concorra a promoções.</span><br/>
   
      <br /><br/>

      <label for="telefone_1" title="Telefone fixo primário" >* Telefone 1: <span class="fonte10">(xx) xxxx-xxxx</span></label> <br/>
      <input id="telefone_1" name="telefone_1" type="text" value="" onkeypress="return MascaraTelefone(this, event);" class='campotextcf' /><br/>
    
      <label for="celular" title="Celular" > Celular: <span class="fonte10">(xx) xxxxx-xxxx</span></label><br/>
      <input id="celular" name="celular" type="text" onkeypress="return MascaraTelefone(this, event);" class='campotextcf' /><br/>
    
    
      <label for="telefone_2" title="Telefone fixo secundário" >Telefone 2: <span class="fonte10">(xx) xxxx-xxxx</span></label><br/> 
      <input id="telefone_2" name="telefone_2" type="text" onkeypress="return MascaraTelefone(this, event);" class='campotextcf' /><br />
    

    
      <label for='onde_conheceu' title='Onde conheceu a fórmula' >*Como conheceu a Fórmula:</label><br/>
      <select name="onde_conheceu" id='onde_conheceu' >
      <option></option>
      <?
        $con = conectabd();
        $sql_buscar_onde_conheceu = "SELECT * from ipi_onde_conheceu where situacao='ATIVO' order by onde_conheceu";
        $res_buscar_onde_conheceu = mysql_query($sql_buscar_onde_conheceu);
        while($obj_buscar_onde_conheceu = mysql_fetch_object($res_buscar_onde_conheceu))
        {
          echo "<option value='".$obj_buscar_onde_conheceu->cod_onde_conheceu."'>".$obj_buscar_onde_conheceu->onde_conheceu."</option>";
        }

        desconectabd();
      ?>
      </select><br /><br />
    

    </div>


      <p><h3>Endereço de Entrega</h3></p>

      <p>Para finalizar digite seu CEP e clique em Buscar</p>
        <label for="cep" title="Digite o seu CEP">* CEP: 
          <!-- <a href="http://www.correios.com.br/servicos/cep/cep_loc_log.cfm" target="_blank" >Não sei meu CEP</a> -->
        </label> <br/>
        <input name="cep" id="cep" type="text" onkeypress="return MascaraCEP(this, event);" class='campotextcf'  />
        <a onclick="completar_endereco();return false;" class="btn btn-secondary" style="width: 220px"><b>
          Completar Endereço </b></a>

      <br />
      
      <div id='cadEndereco' style="display: none">
          
            <label for="endereco" title="Endereço de entrega" >* Endereço:</label><br/>
            <input name="endereco" id="endereco" type="text" class='campotextcf' readonly="readonly" /><br/>
          

          
            <label for="numero" title="Número" >* Número:</label><br/>
            <input name="numero" id="numero" type="text" class='campotextcf' maxlength="10" /><br/>
         
          
          
            <label for="edificio" title="Edifício" >Edifício:</label><br/>
            <input name="edificio" id="edificio" type="text" class='campotextcf' /><br/>
          

         
            <label for="complemento" title="Complemento" >Complemento:</label><br/>
            <input name="complemento" id="complemento" type="text" class='campotextcf' /><br/>
         
            <label for="referencia_cliente" title="referencia_cliente" >Ponto de Referência:</label><br/>
            <input name="referencia_cliente" id="referencia_cliente" type="text" class='campotextcf' /><br/>
          
            <label for="bairro" title="Bairro" >* Bairro:</label><br/>
            <input name="bairro" id="bairro" type="text" class='campotextcf' readonly="readonly"/><br/>
         

          
            <label for="cidade" title="Cidade" >* Cidade:</label><br/>
            <input name="cidade" id="cidade" type="text" class='campotextcf' readonly="readonly"/><br/>
          

          
            <label for="estado" title="Estado" >* Estado:</label><br/>
            <select name="estado" id="estado" style="width: 70px" >
              <option value="">&nbsp;</option>
              <option value="AC">AC</option>
              <option value="AL">AL</option>
              <option value="AP">AP</option>
              <option value="AM">AM</option>
              <option value="BA">BA</option>
              <option value="CE">CE</option>
              <option value="DF">DF</option>
              <option value="ES">ES</option>
              <option value="GO">GO</option>
              <option value="MA">MA</option>
              <option value="MT">MT</option>
              <option value="MS">MS</option>
              <option value="MG">MG</option>
              <option value="PA">PA</option>
              <option value="PB">PB</option>
              <option value="PR">PR</option>
              <option value="PE">PE</option>
              <option value="PI">PI</option>
              <option value="RJ">RJ</option>
              <option value="RN">RN</option>
              <option value="RS">RS</option>
              <option value="RO">RO</option>
              <option value="RR">RR</option>
              <option value="SC">SC</option>
              <option value="SP">SP</option>
              <option value="SE">SE</option>
              <option value="TO">TO</option>
            </select>
           <br />
      <br />

    <label for="receber_noticias" title="Receber promoções no meu e-mail." ><input type="checkbox" checked="checked" id="receber_noticias" name="receber_noticias" value='1' /> Receber promoções no meu e-mail.</label>
    <br />
    <br />
      <div id="cadBotao"><input type="submit" alt="Clique e confirme o seu cadastro." value="Cadastrar" class="btn btn-secondary"/> </div>
      </div>
    </div>  
    <input type="hidden" name="acao" value="inserir" />
  </div>
  <div class='bottom_div'>&nbsp;</div>
</div>
</form>

<? 
endif; 
}
?>
