<?
require_once 'ipi_session.php';
require_once 'bd.php';
require_once 'sys/lib/php/formulario.php';
  
$acao = validaVarPost('acao');

if($acao == 'salvar_endereco') {
  $cod_enderecos = validaVarPost("cod_enderecos");
  $apelido = validaVarPost('apelido');
  $telefone_1 = validaVarPost('telefone_1');
  $telefone_2 = validaVarPost('telefone_2');
  $cep = validaVarPost('cep');
  $endereco = validaVarPost('endereco');
  $numero = validaVarPost('numero');
  $complemento = validaVarPost('complemento');
  $edificio = validaVarPost('edificio');
  $bairro = validaVarPost('bairro');
  $cidade = validaVarPost('cidade');
  $estado = validaVarPost('estado');
  $ponto_referencia = validaVarPost("ponto_referencia");
  
  $cod_clientes = $_SESSION['ipi_cliente']['codigo'];
  
  $con = conectabd();
  
  //$SqlDel = "DELETE FROM ipi_enderecos WHERE cod_clientes = '$cod_clientes' and cod_enderecos = '$cod_enderecos'";
  
  //for($r = 0; $r < count($apelido); $r++) {
  if($cod_enderecos>0)
  {
    $SqlEdicao = sprintf("UPDATE ipi_enderecos SET apelido = '%s', endereco = '%s', numero = '%s', complemento = '%s', edificio = '%s', bairro = '%s', cidade = '%s', estado = '%s', cep = '%s', telefone_1 = '%s', telefone_2 = '%s',referencia_cliente = '%s' WHERE cod_clientes = '$cod_clientes' and cod_enderecos = '$cod_enderecos'", filtrar_caracteres_sql($apelido), filtrar_caracteres_sql($endereco), filtrar_caracteres_sql($numero), filtrar_caracteres_sql($complemento), filtrar_caracteres_sql($edificio), filtrar_caracteres_sql($bairro), filtrar_caracteres_sql($cidade), filtrar_caracteres_sql($estado), filtrar_caracteres_sql($cep), filtrar_caracteres_sql($telefone_1), filtrar_caracteres_sql($telefone_2),filtrar_caracteres_sql($ponto_referencia));
    $resEdicao = mysql_query($SqlEdicao);

    $sqlCliente = "SELECT * FROM ipi_clientes c INNER JOIN ipi_enderecos e ON (e.cod_clientes=c.cod_clientes) WHERE c.cod_clientes=" . $cod_clientes . " AND e.cod_enderecos=" . $cod_enderecos;
    $resCliente = mysql_query($sqlCliente);
    $objCliente = mysql_fetch_object($resCliente);

    $sql_telefone1_cliente = sprintf("UPDATE ins_cadastro_telefone SET numero_telefone = '%s' WHERE cod_enderecos = '%s' and numero_telefone = '".$objCliente->telefone_1."'", $telefone_1, $cod_enderecos);
    $res_telefone1_cliente = mysql_query($sql_telefone1_cliente);

    if($objCliente->telefone_2!="")
    {
      $sql_telefone2_cliente = sprintf("UPDATE ins_cadastro_telefone SET numero_telefone = '%s' WHERE cod_enderecos = '%s' and numero_telefone = '".$objCliente->telefone_2."'", $telefone_2, $cod_enderecos);
      $res_telefone2_cliente = mysql_query($sql_telefone2_cliente);
    }
    else if($telefone_2!="")
    {
      $SqlInsertTel2 = sprintf("INSERT INTO ins_cadastro_telefone (cod_clientes, numero_telefone, cod_enderecos) VALUES (%d, '%s', %d)",
                                    $cod_clientes, $telefone_2, $cod_enderecos);

      mysql_query($SqlInsertTel2);
    }

  }
  else
  {
    $SqlEdicao = sprintf("INSERT INTO ipi_enderecos (apelido, endereco, numero, complemento, edificio, bairro, cidade, estado, cep, telefone_1, telefone_2, referencia_cliente, cod_clientes) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s', %d)",
                         filtrar_caracteres_sql($apelido), filtrar_caracteres_sql($endereco), filtrar_caracteres_sql($numero), filtrar_caracteres_sql($complemento), filtrar_caracteres_sql($edificio), filtrar_caracteres_sql($bairro), filtrar_caracteres_sql($cidade), filtrar_caracteres_sql($estado), filtrar_caracteres_sql($cep), filtrar_caracteres_sql($telefone_1), filtrar_caracteres_sql($telefone_2), filtrar_caracteres_sql($ponto_referencia), $cod_clientes);
                         
    $resEdicao = mysql_query($SqlEdicao);

    $cod_enderecos = mysql_insert_id();

    // Cadastrando o celular no sms marketing
    $SqlInsertTel1 = sprintf("INSERT INTO ins_cadastro_telefone (cod_clientes, numero_telefone, cod_enderecos) VALUES (%d, '%s', %d)",
                                    $cod_clientes, $telefone_1, $cod_enderecos);

    mysql_query($SqlInsertTel1);

    if($telefone_2!="")
    {
      // Cadastrando o celular no sms marketing
      $SqlInsertTel2 = sprintf("INSERT INTO ins_cadastro_telefone (cod_clientes, numero_telefone, cod_enderecos) VALUES (%d, '%s', %d)",
                                      $cod_clientes, $telefone_2, $cod_enderecos);
    }

    mysql_query($SqlInsertTel2);
  }
  
  desconectabd($con);
  
  if($resEdicao) {
    $acao = '';
    echo '<script>alert("Endereço alterado com sucesso...")</script>';
    if($_SESSION['ipi_carrinho']['pedido'])
      echo '<p><a href="pagamentos">Clique aqui</a> para fechar o seu pedido!</p>';
    else
      echo '<p><a href="pedidos">Clique aqui</a> e comece a pedir a sua pizza agora mesmo!</p>';
  }
  else {
    echo '<script>alert("Erro ao cadastrar, ocorreu um erro interno do sistema")</script>';
  }
}
else if($acao=="excluir_endereco")
{
  $cod_clientes = $_SESSION['ipi_cliente']['codigo'];
  $cod_enderecos = validaVarPost("cod_enderecos");

  
  if($cod_enderecos>0)
  {
    $con = conectabd();
    $SqlDel = "DELETE FROM ipi_enderecos WHERE cod_clientes = '$cod_clientes' and cod_enderecos = '$cod_enderecos'";
    $ResDel = mysql_query($SqlDel);
    desconectabd($con);
  }

  
  if($ResDel) {
    $acao = '';
    echo '<script>alert("Endereço excluido com sucesso...")</script>';
    if($_SESSION['ipi_carrinho']['pedido'])
      echo '<p><a href="pagamentos">Clique aqui</a> para fechar o seu pedido!</p>';
    else
      echo '<p><a href="pedidos">Clique aqui</a> e comece a pedir a sua pizza agora mesmo!</p>';
  }
  else {
    echo '<script>alert("Erro ao excluir, ocorreu um erro interno do sistema")</script>';
  }
}
?>

<? if ($acao == ''): ?>


  <div class="areaTextoRound fundoBranco">

     
            <table  border="1" style=" width:90%; margin: 50px 0 0 25px; text-align: left;">
                <thead>
                    <tr style="vertical-align: top;">
                        <td class="center padding_15"><b>Apelido</b></td>
                        <!-- <td class="bordaD">Cidade</td>
                        <td class="bordaD">Bairro</td>
                        <td class="bordaD">Rua</td> -->
                        <td class="center padding_15"><b>Endereço</b></td>
                        <td class="center padding_15"><b>Ações</b></td>
                    </tr>
                </thead>
                <?
                  $codigo = $_SESSION['ipi_cliente']['codigo'];
              
                  if($codigo > 0):
                  
                    $con = conectabd();
                    
                    $SqlBusca = "SELECT * FROM ipi_enderecos WHERE cod_clientes = '$codigo' ORDER BY cod_enderecos";
                    
                    $resBusca = mysql_query($SqlBusca);
                    $numBusca = mysql_num_rows($resBusca);
                    
                    $linha = 0;
                    
                    for($registro = 0; $registro < $numBusca; $registro++):
                      $objBusca = mysql_fetch_object($resBusca);
                ?>

                <tr style="vertical-align: top;">
                    <td class="padding_15"><? echo $objBusca->apelido ?></td>
                    <td class="padding_15"> <? echo $objBusca->endereco ?>, nº
                    <? echo $objBusca->numero ?><br/><? echo $objBusca->cidade ?>, <!-- </td> -->
                    <!-- <td class="bordaD"> --> <? echo $objBusca->bairro ?><!-- </td> -->
                    <!-- <td class="bordaD"> --> </td>
                    <td class="center padding_15"><form name="editarEnd" action='' method="post"><input id="btnCancelar" type="submit" value="Editar" style="font-size: 10pt" class="btn btn-secondary" />
                    <input type="hidden" name="cod_enderecos" value="<? echo $objBusca->cod_enderecos ?>" /><input type="hidden" name="acao" value="editar_endereco" /></form></td>
                </tr>

                <?
                  endfor;
                  endif;
                ?>
            </table>
            <div style='margin-right: 10px;float:left'>
            <br/>
              <form name="formNovoEnd" action='' method="post">
                <input id="btnConfirmar" type="submit" value="Novo Endereço" class="btn btn-secondary" />
                   <a href="meu_home" class="btn btn-secondary"style="width:80px; display:inline" >Cancelar</a>
                <input type="hidden" name="acao" value="editar_endereco" /><br/><br/>
              </form>
            </div>
            <div style="float:clear"></div>

  </div>
<? endif; ?>



<? if($acao == 'editar_endereco'): ?>

<script type="text/javascript" src="sys/lib/js/mascara.js"></script>
  <script type="text/javascript">

  var contador_tabela_novas = 0;

  function validaForm(form) {

    var apelido = document.getElementById('apelido');
    var telefone_1 = document.getElementById('telefone_1');
    var cep = document.getElementById('cep');
    var endereco = document.getElementById('endereco');
    var numero = document.getElementById('numero');
    var bairro = document.getElementById('bairro');
    var cidade = document.getElementById('cidade');
    var estado = document.getElementById('estado');
    
    if(apelido.value == '') {
      alert('Campo apelido obrigatório.');
      apelido.focus();
      
      return false;
    }
  
    if(telefone_1.value == '') {
      alert('Campo telefone local 1 obrigatório.');
      telefone_1.focus();
      
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

    if(cep.value == '') {
      alert('Campo cep obrigatório.');
      cep.focus();
      
      return false;
    }
  
    if(endereco.value == '') {
      alert('Campo endereço obrigatório.');
      endereco.focus();
      
      return false;
    }
  
    if(numero.value == '') {
      alert('Campo número obrigatório.');
      numero.focus();
      
      return false;
    }
  
    if(bairro.value == '') {
      alert('Campo bairro obrigatório.');
      bairro.focus();
      
      return false;
    }
  
    if(cidade.value == '') {
      alert('Campo cidade obrigatório.');
      cidade.focus();
      
      return false;
    }
  
    if(estado.value == '') {
      alert('Campo estado obrigatório.');
      estado.focus();
      
      return false;
    }

    return true;
  }

  function confirmar_exclusao()
  {

    if(confirm("Você tem certeza que deseja excluir este endereço? Porque depois que você excluir não tem volta eim. :)"))
    {
      document.getElementById("acao").value = "excluir_endereco";
      document.getElementById("formSalvarEndereco").submit();
    }
  }

  function completarEndereco(postid) {
  var cep = document.getElementById('cep').value;
  var url_var = 'cep=' + cep;
  
  if(cep != '') {  
    jQuery.ajax({
    url: 'ipi_completa_cep_ajax.php', 
    type: "POST",
    data: url_var,  
    dataType: "json",  
    success: function(retorno) {
      if(retorno == null || retorno.status == 'ERRO')
      {
        alert("CEP não encontrado, digite seus dados!");

        //$("#cadEndereco").fadeIn(200);
        //document.getElementById('cadEndereco').style.display='block';

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
        //$("#cadEndereco").fadeIn(200);
        //document.getElementById('cadEndereco').style.display='block';
/*        document.getElementById('cadBairro').style.display='block';
        document.getElementById('cadComplemento').style.display='block';
        document.getElementById('cadBotao').style.display='block';*/
        document.getElementById('numero').focus();
      }
      else if(retorno.mensagem == "Esse CEP nao existe")
      {
        alert("CEP não encontrado, digite seus dados!");

        //$("#cadEndereco").fadeIn(200);
        //document.getElementById('cadEndereco').style.display='block';

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

  </script>
    <?
      $codigo = $_SESSION['ipi_cliente']['codigo'];
      $cod_enderecos = validaVarPost("cod_enderecos");
      if($codigo > 0 && $cod_enderecos>0)
      {
      
        $con = conectabd();
        
        $SqlBusca = "SELECT *,(SELECT count(*) FROM ipi_enderecos WHERE cod_clientes = '$codigo') as qtd_enderecos FROM ipi_enderecos WHERE cod_clientes = '$codigo' and cod_enderecos = '$cod_enderecos' ORDER BY cod_enderecos";
        $resBusca = mysql_query($SqlBusca);
        $objBusca = mysql_fetch_object($resBusca);
      }
      else
      {
        $objBusca = (object) '';
      }
      ?>
<form name="formSalvarEndereco" id="formSalvarEndereco" method="post" onsubmit="return validaForm(this);" action=''>
  <div class="divcadastro">

            <!-- <h1>Atualização de Endereço:</h1> -->
                <span class="spanFull corTextoMarrom" style="margin-top: 40px;">
   
                    <span class="spanAE espacoRodape">
                        
                        <label for="apelido" title="Telefone fixo primário">* Apelido:</label><br />
                        <input id="apelido" name="apelido" type="text" value="<? echo $objBusca->apelido ?>"  class="textBoxPadrao espacoRodape"  /><br/>

                        <label for="telefone_1" title="Telefone fixo primário">* Telefone Fixo (a):</label><br />
                        <input id="telefone_1" name="telefone_1" type="text" value="<? echo $objBusca->telefone_1 ?>" onkeypress="return MascaraTelefone(this, event);"  class="textBoxPadrao espacoRodape"  /><br/>

                        <label for="telefone_2" title="Telefone fixo secundário">Telefone Fixo (b):</label><br />
                        <input id="telefone_2" name="telefone_2" type="text" value="<? echo $objBusca->telefone_2 ?>" onkeypress="return MascaraTelefone(this, event);" class="textBoxPadrao espacoRodape"  />

                        <span class="linhaDivisor espacoRodape espacoTopo fundoBranco">&nbsp;</span><br />

                        <label for="cep" title="Digite o seu CEP">* CEP:</label> &nbsp;&nbsp;&nbsp; <small><a href="http://www.correios.com.br/servicos/cep/cep_loc_log.cfm" target="_blank" class='fonte12'>Não sei meu CEP</a></small><br />
                        <input name="cep" id="cep" type="text" value="<? echo $objBusca->cep ?>" onkeypress="return MascaraCEP(this, event);" class="textBoxPadrao espacoRodape"  />
                        <a id="btnPesquisarCEP"  onclick="completarEndereco();return false;">Buscar</a><br/>

                        * Endereco:<br />
                        <input id="endereco" name="endereco" type="text" value="<? echo $objBusca->endereco ?>" class="textBoxPadrao espacoRodape" readonly="readonly"  /><br/>

                        * Número:<br />
                        <input id="numero" name="numero" type="text" value="<? echo $objBusca->numero ?>" class="textBoxPadrao espacoRodape"  /><br/>

                        Edifício:<br />
                        <input id="edificio" name="edificio"  type="text" value="<? echo $objBusca->edificio ?>" class="textBoxPadrao espacoRodape"  /><br/>

                        Complemento:<br />
                        <input id="complemento" name="complemento" type="text" value="<? echo $objBusca->complemento ?>" class="textBoxPadrao espacoRodape"  /><br/>

                        * Bairro:<br />
                        <input id="bairro" name="bairro"  type="text" value="<? echo $objBusca->bairro ?>" class="textBoxPadrao espacoRodape" readonly="readonly"  /><br/>

                        * Cidade:<br />
                        <input id="cidade" name="cidade" type="text" value="<? echo $objBusca->cidade ?>" class="textBoxPadrao espacoRodape" readonly="readonly"  /><br/>

                        * Estado:<br />
                        <select name="estado" id="estado" style="width: 50px;" class="espacoRodape"><br/>
                          <option value="">&nbsp;</option>
                          <option value="AC" <? if($objBusca->estado == 'AC') echo 'SELECTED' ?>>AC</option>
                          <option value="AL" <? if($objBusca->estado == 'AL') echo 'SELECTED' ?>>AL</option>
                          <option value="AP" <? if($objBusca->estado == 'AP') echo 'SELECTED' ?>>AP</option>
                          <option value="AM" <? if($objBusca->estado == 'AM') echo 'SELECTED' ?>>AM</option>
                          <option value="BA" <? if($objBusca->estado == 'BA') echo 'SELECTED' ?>>BA</option>
                          <option value="CE" <? if($objBusca->estado == 'CE') echo 'SELECTED' ?>>CE</option>
                          <option value="DF" <? if($objBusca->estado == 'DF') echo 'SELECTED' ?>>DF</option>
                          <option value="ES" <? if($objBusca->estado == 'ES') echo 'SELECTED' ?>>ES</option>
                          <option value="GO" <? if($objBusca->estado == 'GO') echo 'SELECTED' ?>>GO</option>
                          <option value="MA" <? if($objBusca->estado == 'MA') echo 'SELECTED' ?>>MA</option>
                          <option value="MT" <? if($objBusca->estado == 'MT') echo 'SELECTED' ?>>MT</option>
                          <option value="MS" <? if($objBusca->estado == 'MS') echo 'SELECTED' ?>>MS</option>
                          <option value="MG" <? if($objBusca->estado == 'MG') echo 'SELECTED' ?>>MG</option>
                          <option value="PA" <? if($objBusca->estado == 'PA') echo 'SELECTED' ?>>PA</option>
                          <option value="PB" <? if($objBusca->estado == 'PB') echo 'SELECTED' ?>>PB</option>
                          <option value="PR" <? if($objBusca->estado == 'PR') echo 'SELECTED' ?>>PR</option>
                          <option value="PE" <? if($objBusca->estado == 'PE') echo 'SELECTED' ?>>PE</option>
                          <option value="PI" <? if($objBusca->estado == 'PI') echo 'SELECTED' ?>>PI</option>
                          <option value="RJ" <? if($objBusca->estado == 'RJ') echo 'SELECTED' ?>>RJ</option>
                          <option value="RN" <? if($objBusca->estado == 'RN') echo 'SELECTED' ?>>RN</option>
                          <option value="RS" <? if($objBusca->estado == 'RS') echo 'SELECTED' ?>>RS</option>
                          <option value="RO" <? if($objBusca->estado == 'RO') echo 'SELECTED' ?>>RO</option>
                          <option value="RR" <? if($objBusca->estado == 'RR') echo 'SELECTED' ?>>RR</option>
                          <option value="SC" <? if($objBusca->estado == 'SC') echo 'SELECTED' ?>>SC</option>
                          <option value="SP" <? if($objBusca->estado == 'SP') echo 'SELECTED' ?>>SP</option>
                          <option value="SE" <? if($objBusca->estado == 'SE') echo 'SELECTED' ?>>SE</option>
                          <option value="TO" <? if($objBusca->estado == 'TO') echo 'SELECTED' ?>>TO</option>
                        </select><br />

                        Ponto de Referência:<br />
                        <input id="ponto_referencia" type="text" name="ponto_referencia" rows="3" class="textBoxPadrao espacoRodape" value="<?echo$objBusca->referencia_cliente;?>" ><br />
                        <div class="btn_enderecos">
                           <input id="btnConfirmar" type="submit" value="Salvar" style="display:inline" class="btn btn-secondary3 " />&nbsp;&nbsp;
                        <input id="btnCancelar" type="button" value="Cancelar" onclick='javascript:window.location.href = "meus_enderecos";' style="display:inline" class="btn btn-secondary3 "/>
                        </div>
                       
                       <br/><br/>
                        <? if($objBusca->cod_enderecos > 0 && $objBusca->qtd_enderecos>1): ?>
                        
                        <input id="btnCancelar" type="button" value="Excluir" onclick='confirmar_exclusao();' style="display:inline" class="btn btn-secondary" />

                        <? endif; ?>
                        <input type="hidden" name="cod_enderecos" value="<? echo $objBusca->cod_enderecos ?>" />
                        <input type="hidden" name="acao" id="acao"  value="salvar_endereco" />
                    </span>
                </span>

  </div>
</form>
<? endif; ?>
