<?php

/**
 * ipi_tamanho.php: Cadastro de Empresas
 * 
 * Índice: cod_empresas
 * Tabela: ipi_empresas
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Bloqueio de Clientes');

$acao = validaVarPost('acao');

$tabela = 'ipi_clientes_bloqueio';
$chave_primaria = 'cod_clientes_bloqueio';

switch($acao) {
  case 'excluir':
    $excluir = validaVarPost('excluir');
    $indicesSql = implode(',', $excluir);
    
    $con = conectabd();
    
    $SqlDel5 = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";
    $resDel5 = mysql_query($SqlDel5);
    
    if ($resDel5)
      mensagemOk('Os registros selecionados foram excluídos com sucesso!');
    else
      mensagemErro('Erro ao excluir os registros', 'Por favor, comunique a equipe de suporte informando todos os usuários selecionados para exclusão.');
    
    desconectabd($con);
  break;
  case 'editar':

    $codigo  = validaVarPost($chave_primaria);

    $tipo_bloqueio = validaVarPost('tipo_bloqueio');
    $cpf = validaVarPost('cpf');
    $numero_cartao = validaVarPost('numero_cartao');
    $bandeira = validaVarPost('bandeira');
    $email = validaVarPost('email');

    $cod_pizzarias = validaVarPost('cod_pizzarias');

    $endereco = validaVarPost('endereco');
    $numero = validaVarPost('numero');
    $complemento = validaVarPost('complemento');
    $bairro = validaVarPost('bairro');
    $cidade = validaVarPost('cidade');
    $estado = validaVarPost('estado');
    $cep = validaVarPost('cep');
    
    $cartao_compactado = compactar_valores($bandeira.$numero_cartao);
    $endereco_compactado = compactar_valores($endereco.$numero.$complemento.$bairro.$cidade.$estado.$cep);

    $con = conectabd();


    $sql_buscar_bloqueios = "SELECT * FROM $tabela WHERE tipo_bloqueio='".$tipo_bloqueio."' AND situacao='BLOQUEADO' AND ( (email='".$email."' AND email<>'') OR (cpf='".$cpf."' AND cpf<>'') OR (cartao_compacto='".$cartao_compactado."' AND cartao_compacto<>'') OR (endereco_compacto='".$endereco_compactado."' AND endereco_compacto<>'')) ORDER BY data_hora_bloqueio";
    //echo $sql_buscar_bloqueios;
    $res_buscar_bloqueios = mysql_query($sql_buscar_bloqueios);
    $obj_buscar_bloqueios = mysql_fetch_object($res_buscar_bloqueios);
    $num_buscar_bloqueios = mysql_num_rows($res_buscar_bloqueios);

    if ($num_buscar_bloqueios == 0)
    {
      if($codigo <= 0) 
      {

        $SqlEdicao = sprintf("INSERT INTO $tabela (cod_pizzarias, cod_usuarios_bloqueio, cod_clientes, data_hora_bloqueio, obs, tipo_bloqueio, email, cpf, endereco, numero, complemento, bairro, cidade, estado, cep, bandeira, numero_cartao, cartao_compacto, endereco_compacto, situacao) VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 'BLOQUEADO')", $cod_pizzarias, $_SESSION['usuario']['codigo'], $cod_clientes, date("Y-m-d H:i:s"), $obs, $tipo_bloqueio, $email, $cpf, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep, $bandeira, $numero_cartao, $cartao_compactado, $endereco_compactado);

        if(mysql_query($SqlEdicao))
          mensagemOk('Registro adicionado com êxito!');
        else
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
      else 
      {
        $SqlEdicao = sprintf("UPDATE $tabela SET cod_pizzarias = '%s', cod_usuarios_alteracao='%s', cod_clientes='%s', obs='%s', tipo_bloqueio='%s', email='%s', cpf='%s', endereco='%s', numero='%s', complemento='%s', bairro='%s', cidade='%s', estado='%s', cep='%s', bandeira='%s', numero_cartao='%s', cartao_compacto='%s', endereco_compacto='%s', situacao='%s', data_hora_alteracao='%s' WHERE $chave_primaria = $codigo", $cod_pizzarias, $_SESSION['usuario']['codigo'], $cod_clientes, $obs, $tipo_bloqueio, $email, $cpf, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep, $bandeira, $numero_cartao, $cartao_compactado, $endereco_compactado, $situacao, date("Y-m-d H:i:s"));
        if(mysql_query($SqlEdicao))
          mensagemOk('Registro adicionado com êxito!');
        else
          mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
      }
    }
    else
    {
        mensagemErro('Erro ao cadastrar', 'Bloqueio já está ativado para este cadastro!.');
    }
    //echo "<Br>sql: ".$SqlEdicao;
    desconectabd($con);
  break;
}

?>

<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css"/>

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
    if (confirm('Deseja excluir os registros selecionados?\n\nATENÇÃO: Todos os valores de tamanho associados anteriormente juntamente com seus respectivos preços e fidelidade (pizza, ingredientes, bordas e adicionais) serão APAGADOS definitivamente do sistema.')) {
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

window.addEvent('domready', function(){
  var tabs = new Tabs('tabs'); 
  
  if (document.frmIncluir.<? echo $chave_primaria ?>.value > 0) {
    <? if ($acao == '') echo 'tabs.irpara(1);'; ?>
    
    document.frmIncluir.botao_submit.value = 'Alterar';
  }
  else {
    document.frmIncluir.botao_submit.value = 'Cadastrar';
  }
  
  tabs.addEvent('change', function(indice){
    if(indice == 1) 
    {
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';

      document.frmIncluir.cod_pizzarias.value = '';
      document.frmIncluir.tipo_bloqueio.value = '';
      document.frmIncluir.cpf.value = '';
      document.frmIncluir.email.value = '';
      document.frmIncluir.bandeira.value = '';
      document.frmIncluir.cep.value = '';
      document.frmIncluir.endereco.value = '';
      document.frmIncluir.numero.value = '';
      document.frmIncluir.bairro.value = '';
      document.frmIncluir.cidade.value = '';
      document.frmIncluir.estado.value = '';
      document.frmIncluir.complemento.value = '';
      document.frmIncluir.email.value = '';
      document.frmIncluir.obs.value = '';
      document.frmIncluir.situacao.value = '';

      $("tabela_cpf").setStyle('display','none');
      $("tabela_email").setStyle('display','none');
      $("tabela_cartao").setStyle('display','none');
      $("tabela_endereco").setStyle('display','none');
      
      document.frmIncluir.botao_submit.value = 'Cadastrar';
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
    <table><tr>
  
    <!-- Conteúdo -->
    <td class="conteudo">
    
      <form name="frmExcluir" method="post" onsubmit="return verificaCheckbox(this)">
    
        <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
          <tr>
            <td><!-- <input class="botaoAzul" type="submit" value="Excluir Selecionados"> --></td>
          </tr>
        </table>
      
        <table class="listaEdicao" cellpadding="0" cellspacing="0">

          <thead>
            <tr>
              <!-- <td align="center" width="20"><input type="checkbox" onclick="marcaTodos('marcar');"></td> -->
              <td align="center" width="130">Tipo de Bloqueio</td>
              <td align="center" width="500">Bloqueado</td>
              <td align="center" width="100">Data Hora</td>
              <td align="center" width="100">Situação</td>
            </tr>
          </thead>

          <tbody>
          <?
          $con = conectabd();
          $SqlBusca = "SELECT * FROM $tabela WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY data_hora_bloqueio";
          $resBusca = mysql_query($SqlBusca);
          while ($objBusca = mysql_fetch_object($resBusca)) 
          {
            if ($objBusca->tipo_bloqueio == "EMAIL")
            {
              $valores_bloqueados = bd2texto($objBusca->email);
            }
            else if ($objBusca->tipo_bloqueio == "CPF")
            {
              $valores_bloqueados = bd2texto($objBusca->cpf);
            }
            else if ($objBusca->tipo_bloqueio == "CARTAO_CREDITO")
            {
              $valores_bloqueados = bd2texto($objBusca->bandeira)." - ".bd2texto($objBusca->numero_cartao);
            }
            else if ($objBusca->tipo_bloqueio == "ENDERECO")
            {
              $valores_bloqueados = bd2texto($objBusca->endereco).", ".bd2texto($objBusca->numero)." - ".bd2texto($objBusca->complemento)." - ".bd2texto($objBusca->bairro);
              $valores_bloqueados .= " <Br /> ".bd2texto($objBusca->cidade)." - ".bd2texto($objBusca->estado)." - ".bd2texto($objBusca->cep);
            }

            echo '<tr>';
            
            //echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBusca->$chave_primaria.'"></td>';
            echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBusca->$chave_primaria.')">'.bd2texto($objBusca->tipo_bloqueio).'</a></td>';
            echo '<td align="center">'.$valores_bloqueados.'</td>';
            echo '<td align="center">'.bd2datahora($objBusca->data_hora_bloqueio).'</td>';
            echo '<td align="center">'.bd2texto($objBusca->situacao).'</td>';
            
            echo '</tr>';
          }
          desconectabd($con);
          ?>
          </tbody>
        </table>
      
        <input type="hidden" name="acao" value="excluir">
      </form>
    
    </td>
    <!-- Conteúdo -->
    
    <!-- Barra Lateral -->
	<!--
    <td class="lateral">
      <div class="blocoNavegacao">
        <ul>
          <li><a href="ipi_adicional.php">Adicionais</a></li>
          <li><a href="ipi_borda.php">Bordas</a></li>
          <li><a href="ipi_ingrediente.php">Ingredientes</a></li>
          <li><a href="ipi_pizza.php">Pizzas</a></li>
        </ul>
      </div>
    </td>
	-->
    <!-- Barra Lateral -->
    
    </tr></table>
  </div>
  <!-- Tab Editar -->
  
  
  
  <!-- Tab Incluir -->
  <div class="painelTab">
    <? 
    $codigo = validaVarPost($chave_primaria, '/[0-9]+/');
    
    if($codigo > 0) 
    {
      $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    } 
    ?>
    <script>
    function selecionar_bloqueio(tipo)
    {
      if (tipo=="CPF")
      {
        $("tabela_cpf").setStyle('display','block');
        $("tabela_email").setStyle('display','none');
        $("tabela_cartao").setStyle('display','none');
        $("tabela_endereco").setStyle('display','none');
      }
      else if (tipo=="CARTAO_CREDITO")
      {
        $("tabela_cpf").setStyle('display','none');
        $("tabela_email").setStyle('display','none');
        $("tabela_cartao").setStyle('display','block');
        $("tabela_endereco").setStyle('display','none');
      }
      else if (tipo=="EMAIL")
      {
        $("tabela_cpf").setStyle('display','none');
        $("tabela_email").setStyle('display','block');
        $("tabela_cartao").setStyle('display','none');
        $("tabela_endereco").setStyle('display','none');
      }
      else if (tipo=="ENDERECO")
      {
        $("tabela_cpf").setStyle('display','none');
        $("tabela_email").setStyle('display','none');
        $("tabela_cartao").setStyle('display','none');
        $("tabela_endereco").setStyle('display','block');
      }
    }

    <?
    if($codigo > 0) 
    {
      ?>
      window.addEvent('domready', function(){
        selecionar_bloqueio(<? echo "'".$objBusca->tipo_bloqueio."'"; ?>)
      });
      <?
    }
    ?>
    </script>
    <form name="frmIncluir" method="post" onsubmit="return validaRequeridos(this)">
    
    <table align="center" class="caixa" cellpadding="0" cellspacing="0">

      <tr>
        <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="tipo_bloqueio"><?php echo TIPO_EMPRESA ?></label></td>
      </tr>
      <tr>
        <td class="tdbl tdbr sep">

          <select class="requerido" name="cod_pizzarias" id="cod_pizzarias">
            <option value="">Todas as <?php echo TIPO_EMPRESAS ?></option>
            <?
            $con = conectabd();
            
            $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ") ORDER BY nome";

            $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
            
            while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
              echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';
              
              if($objBuscaPizzarias->cod_pizzarias == $objBusca->cod_pizzarias)
                echo 'selected';
              
              echo '>'.bd2texto($objBuscaPizzarias->nome).'</option>';
            }
            
            ?>
          </select>

        </td>
      </tr>



      <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="tipo_bloqueio">Tipo de Bloqueio</label></td>
      </tr>
      <tr>
        <td class="tdbl tdbr sep">
          <select class="requerido" name="tipo_bloqueio" onChange="javascript:selecionar_bloqueio(this.value);">
            <option value=""></option>
            <option value="CPF" <? if ($objBusca->tipo_bloqueio=="CPF") echo 'selected="selected"' ?>>CPF</option>
            <option value="CARTAO_CREDITO" <? if ($objBusca->tipo_bloqueio=="CARTAO_CREDITO") echo 'selected="selected"' ?>>Cartão Credito</option>
            <option value="EMAIL" <? if ($objBusca->tipo_bloqueio=="EMAIL") echo 'selected="selected"' ?>>E-Mail</option>
            <option value="ENDERECO" <? if ($objBusca->tipo_bloqueio=="ENDERECO") echo 'selected="selected"' ?>>Endereço</option>
          </select>
        </td>
      </tr>

      <tr id="tabela_cpf" style="display: none">
        <td class="tdbl tdbr sep">
          <table width="350">
            <tr>
              <td><label for="cpf">CPF</label></td>
            </tr>
            <tr>
              <td><input type="text" name="cpf" id="cpf" maxlength="45" size="22" value="<? echo texto2bd($objBusca->cpf) ?>"></td>
            </tr>
          </table>
        </td>
      </tr>

      <tr id="tabela_email" style="display: none">
        <td class="tdbl tdbr sep">
          <table>
            <tr>
              <td><label for="email">E-Mail</label></td>
            </tr>
            <tr>
              <td><input type="text" name="email" id="email" maxlength="80" size="45" value="<? echo texto2bd($objBusca->email) ?>"></td>
            </tr>
          </table>
        </td>
      </tr>

      <tr id="tabela_cartao" style="display: none">
        <td class="tdbl tdbr sep">
          <table width="350">
            <tr>
              <td><label for="bandeira">Bandeira</label></td>
            </tr>
            <tr>
              <td>
                <select name="bandeira">
                  <option value=""></option>
                  <option value="VISA" <? if ($objBusca->bandeira=="VISA") echo 'selected="selected"' ?>>Visa</option>
                  <option value="MASTERCARD" <? if ($objBusca->bandeira=="MASTERCARD") echo 'selected="selected"' ?>>Master Card</option>
                </select>
              </td>
            </tr>
            <tr>
              <td><label for="numero_cartao">Cartão de Crédito</label></td>
            </tr>
            <tr>
              <td><input type="text" name="numero_cartao" id="numero_cartao" maxlength="20" size="22" value="<? echo texto2bd($objBusca->numero_cartao) ?>"></td>
            </tr>
          </table>
        </td>
      </tr>

      <tr id="tabela_endereco" style="display: none">
        <td class="tdbl tdbr sep">

          <table>
            <tr>
              <td><label for="cep">CEP</label></td>
            </tr>
            <tr>
              <td>
                <input type="text" name="cep" id="cep" maxlength="10" size="10"	value="<? echo texto2bd($objBusca->cep) ?>"	onkeypress="return MascaraCEP(this,event);">
              </td>
            </tr>
	          <tr>
		          <td><label for="endereco">Endereço</label></td>
	          </tr>
	          <tr>
		          <td><input type="text" name="endereco" id="endereco" maxlength="80" size="45" value="<? echo texto2bd($objBusca->endereco)?>"></td>
	          </tr>

	          <tr>
		          <td>
		          <table cellpadding="0" cellspacing="0">
			          <tr>
				          <td class="legenda"><label for="numero">Número</label></td>
				          <td>&nbsp;</td>
				          <td class="legenda"><label for="complemento">Complemento</label></td>
			          </tr>
			          <tr>
				          <td>
                    <input type="text" name="numero" id="numero" maxlength="15" size="6" value="<? echo texto2bd($objBusca->numero)?>" onkeypress="return ApenasNumero(event);">
                  </td>
				          <td>&nbsp;</td>
				          <td>
                    <input type="text" name="complemento" id="complemento" maxlength="45" size="35" value="<? echo texto2bd($objBusca->complemento)?>">
                  </td>
			          </tr>
		          </table>
		          </td>
	          </tr>

	          <tr>
		          <td>
		          <table cellpadding="0" cellspacing="0">
			          <tr>
				          <td class="legenda"><label for="bairro">Bairro</label></td>
			          </tr>
			          <tr>
				          <td><input type="text" name="bairro" id="bairro" maxlength="45" size="37" value="<? echo texto2bd($objBusca->bairro)?>"></td>
			          </tr>
			          <tr>
				          <td class="legenda"><label for="cidade">Cidade</label></td>
				          <td>&nbsp;</td>
				          <td class="legenda"><label for="estado">Estado</label></td>
			          </tr>
				          <td><input type="text" name="cidade" id="cidade" maxlength="45" size="37" value="<? echo texto2bd($objBusca->cidade)?>"></td>
				          <td>&nbsp;</td>
				          <td><select name="estado" id="estado">
					          <option value=""></option>
					          <option value="AC" <? if ($objBusca->estado=='AC') echo 'selected'?>>AC</option>
					          <option value="AL" <? if ($objBusca->estado=='AL') echo 'selected'?>>AL</option>
					          <option value="AM" <? if ($objBusca->estado=='AM') echo 'selected'?>>AM</option>
					          <option value="AP" <? if ($objBusca->estado=='AP') echo 'selected'?>>AP</option>
					          <option value="BA" <? if ($objBusca->estado=='BA') echo 'selected'?>>BA</option>
					          <option value="CE" <? if ($objBusca->estado=='CE') echo 'selected'?>>CE</option>
					          <option value="DF" <? if ($objBusca->estado=='DF') echo 'selected'?>>DF</option>
					          <option value="ES" <? if ($objBusca->estado=='ES') echo 'selected'?>>ES</option>
					          <option value="GO" <? if ($objBusca->estado=='GO') echo 'selected'?>>GO</option>
					          <option value="MA" <? if ($objBusca->estado=='MA') echo 'selected'?>>MA</option>
					          <option value="MG" <? if ($objBusca->estado=='MG') echo 'selected'?>>MG</option>
					          <option value="MS" <? if ($objBusca->estado=='MS') echo 'selected'?>>MS</option>
					          <option value="MT" <? if ($objBusca->estado=='MT') echo 'selected'?>>MT</option>
					          <option value="PA" <? if ($objBusca->estado=='PA') echo 'selected'?>>PA</option>
					          <option value="PB" <? if ($objBusca->estado=='PB') echo 'selected'?>>PB</option>
					          <option value="PE" <? if ($objBusca->estado=='PE') echo 'selected'?>>PE</option>
					          <option value="PI" <? if ($objBusca->estado=='PI') echo 'selected'?>>PI</option>
					          <option value="PR" <? if ($objBusca->estado=='PR') echo 'selected'?>>PR</option>
					          <option value="RJ" <? if ($objBusca->estado=='RJ') echo 'selected'?>>RJ</option>
					          <option value="RN" <? if ($objBusca->estado=='RN') echo 'selected'?>>RN</option>
					          <option value="RO" <? if ($objBusca->estado=='RO') echo 'selected'?>>RO</option>
					          <option value="RR" <? if ($objBusca->estado=='RR') echo 'selected'?>>RR</option>
					          <option value="RS" <? if ($objBusca->estado=='RS') echo 'selected'?>>RS</option>
					          <option value="SC" <? if ($objBusca->estado=='SC') echo 'selected'?>>SC</option>
					          <option value="SE" <? if ($objBusca->estado=='SE') echo 'selected'?>>SE</option>
					          <option value="SP" <? if ($objBusca->estado=='SP') echo 'selected'?>>SP</option>
					          <option value="TO" <? if ($objBusca->estado=='TO') echo 'selected'?>>TO</option>
				          </select></td>
			          </tr>
		          </table>
		          </td>
	          </tr>

          </table>
        </td>
      </tr>

      <tr>
        <td class="legenda tdbl tdbr"><label for="situacao">OBS</label></td>
      </tr>
      <tr>
        <td class="tdbl tdbr sep">
          <textarea name="obs" cols="43" rows="3"><? echo ($objBusca->obs) ?></textarea>
        </td>
      </tr>


      <tr>
        <td class="legenda tdbl tdbr"><label class="requerido" for="situacao">Situação</label></td>
      </tr>
      <tr>
        <td class="tdbl tdbr sep">
          <select name="situacao">
            <option value=""></option>
            <option value="BLOQUEADO" <? if ($objBusca->situacao=="BLOQUEADO") echo 'selected="selected"' ?>>Bloqueado</option>
            <option value="LIBERADO" <? if ($objBusca->situacao=="LIBERADO") echo 'selected="selected"' ?>>Liberado</option>
          </select>
        </td>
      </tr>


      <tr>
        <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit" class="botao" type="submit" value="Cadastrar"></td>
      </tr>
    </table>
    
    <input type="hidden" name="acao" value="editar">
    <input type="hidden" name="<? echo $chave_primaria ?>" value="<? echo $codigo ?>">
    
    </form>
  </div>
  <!-- Tab Incluir -->
    
 </div>

<? rodape(); ?>
