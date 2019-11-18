<?php

/**
 * ipi_rel_historico_pedidos_novo.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';

header('Content-Type: text/html; charset=utf-8');

cabecalho('Histórico de Pedidos');
function Utf8_ansi($valor='') {
  $Utf8_ansi2 = array(
    "u00c0" =>"À",
    "u00c1" =>"Á",
    "u00c2" =>"Â",
    "u00c3" =>"Ã",
    "u00c4" =>"Ä",
    "u00c5" =>"Å",
    "u00c6" =>"Æ",
    "u00c7" =>"Ç",
    "u00c8" =>"È",
    "u00c9" =>"É",
    "u00ca" =>"Ê",
    "u00cb" =>"Ë",
    "u00cc" =>"Ì",
    "u00cd" =>"Í",
    "u00ce" =>"Î",
    "u00cf" =>"Ï",
    "u00d1" =>"Ñ",
    "u00d2" =>"Ò",
    "u00d3" =>"Ó",
    "u00d4" =>"Ô",
    "u00d5" =>"Õ",
    "u00d6" =>"Ö",
    "u00d8" =>"Ø",
    "u00d9" =>"Ù",
    "u00da" =>"Ú",
    "u00db" =>"Û",
    "u00dc" =>"Ü",
    "u00dd" =>"Ý",
    "u00df" =>"ß",
    "u00e0" =>"à",
    "u00e1" =>"á",
    "u00e2" =>"â",
    "u00e3" =>"ã",
    "u00e4" =>"ä",
    "u00e5" =>"å",
    "u00e6" =>"æ",
    "u00e7" =>"ç",
    "u00e8" =>"è",
    "u00e9" =>"é",
    "u00ea" =>"ê",
    "u00eb" =>"ë",
    "u00ec" =>"ì",
    "u00ed" =>"í",
    "u00ee" =>"î",
    "u00ef" =>"ï",
    "u00f0" =>"ð",
    "u00f1" =>"ñ",
    "u00f2" =>"ò",
    "u00f3" =>"ó",
    "u00f4" =>"ô",
    "u00f5" =>"õ",
    "u00f6" =>"ö",
    "u00f8" =>"ø",
    "u00f9" =>"ù",
    "u00fa" =>"ú",
    "u00fb" =>"û",
    "u00fc" =>"ü",
    "u00fd" =>"ý",
    "u00ff" =>"ÿ",
    "u2022u2022u2022u2022 "=>"******"
  );
  return strtr($valor, $Utf8_ansi2);      
}
$acao = validaVarPost('acao');
$tabela = 'ipi_pedidos';
$chave_primaria = 'cod_pedidos';
$quant_pagina = 50;
if($acao=="" && validaVarGet("p")!="")
  $acao= "detalhes";
switch($acao) {
  case 'reimprimir':
  $codigo = validaVarPost($chave_primaria);
  $indicesSql = implode(',', $codigo);
  $result = false;
  $session = $_SESSION['usuario']['cod_pizzarias'];
  foreach($session as $i=>$v){
    if($v == 18 or $v == 24 or $v == 23 or $v==14 or $v == 22 or $v == 20 or $v == 9 or $v == 16 or $v == 7 or $v == 1 or $v == 21 or $v == 12 or $v == 13 or $v == 3 or $v == 17){
      $result =  true;
    }
  }
  $con = conectabd();

  $SqlUpdate = "UPDATE $tabela SET reimpressao = 1 WHERE $chave_primaria IN ($indicesSql)";

  if (mysql_query($SqlUpdate))
    mensagemOk('O(s) pedido(s) foram definidos como REIMPRESSÃO com sucesso!');
  else
    mensagemErro('Erro ao REIMPRIMIR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');

  desconectabd($con);
  break;
  case 'impresso':
  $codigo = validaVarPost($chave_primaria);
  $indicesSql = implode(',', $codigo);

  $con = conectabd();

  $SqlUpdate = "UPDATE $tabela SET situacao = 'IMPRESSO' WHERE $chave_primaria IN ($indicesSql) AND situacao = 'NOVO'";
  if (mysql_query($con,$SqlUpdate)){
    mensagemOk('O(s) pedido(s) foram definidos como IMPRESSOS com sucesso!');
  }else{
    mensagemErro('Erro ao redefinir IMPRESSO o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
  }

  desconectabd($con);
  break;
  case 'agendamento':
  $codigo = validaVarPost($chave_primaria);
  $indicesSql = implode(',', $codigo);
  $con = conectabd();

  $SqlUpdate = "UPDATE $tabela SET agendado = 0, horario_agendamento = '00:00:00' WHERE $chave_primaria IN ($indicesSql) AND situacao = 'NOVO'";
  if (mysql_query($con,$SqlUpdate))
    mensagemOk('O agendamento do(s) pedido(s) foram apagados com sucesso!');
  else
    mensagemErro('Erro ao apagar agendamento do pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');

  desconectabd($con);
  break;
  case 'cancelar':
  $codigo = validaVarPost($chave_primaria);

  #23-02-2019
  #CANCELA NFCE
  include 'classes/conectamysql.class.php';
  include 'classes/nfe.class.php';
  $nf = new Nfe();
  $nf->justificativa = "CANCELADA POR EMISSÃO ERRONEA";
  $con = conectabd();
  foreach ($codigo as $ckey => $cvalue) {
    $sql = "SELECT ref_nota_fiscal FROM ipi_pedidos WHERE cod_pedidos ='".$cvalue."'";
    $res = mysql_query($con,$sql);
    if(mysql_num_rows($res)){
      $a = mysql_fetch_assoc($res);
      if(!empty($a['ref_nota_fiscal'])){
        $nf->ref = $a['ref_nota_fiscal'];
        $nf->cancelar();
      }
    }
  }
  #FIM 23-02-2019
  $indicesSql = implode(',', $codigo);

  $sql_verificar = "SELECT * FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao IN ('BAIXADO', 'CANCELADO')";
  $res_verificar = mysql_query($sql_verificar);
  $num_verificar = mysql_num_rows($res_verificar);
  ?>
  <script type="text/javascript">
    let ids = "<?php echo $indicesSql; ?>";
    let ajaxData = JSON.stringify({
      ids:ids
    });
    xhr = new XMLHttpRequest();
    xhr.open('POST','https://formulasys.encontresuafranquia.com.br/index.php?acao=cancelar_notas_fiscais&ref='+ids+'&chave=165117047d56ce2487aa718bd8d6c5b7',true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.send(ajaxData);
    console.log(ajaxData);
  </script>
  <?php

  if ($num_verificar>0)
  {
    echo "<div style='color: #FF0000; font-weight: bold; font-size: 14px; text-align: center;'>";
    echo "Os pedidos não podem ser cancelados, pois já foram BAIXADOS: ";
    while ($obj_verificar = mysql_fetch_object($res_verificar))
    {
      echo $obj_verificar->$chave_primaria.", ";
    }
    echo "</div><br /><br />";
  }
  else
  {
        //FUSO HORARIO NECESSITA DE CONEXAO COM O BANCO E A VARIAVEL COD_PIZZARIAS    
    $sql_pizzarias = "SELECT cod_pizzarias FROM $tabela WHERE $chave_primaria IN ($indicesSql) LIMIT 1";
    $res_pizzarias = mysql_query($con,$sql_pizzarias);
    $obj_pizzarias = mysql_fetch_object($res_pizzarias);
    $cod_pizzarias = $obj_pizzarias->cod_pizzarias;
    require("../../pub_req_fuso_horario1.php"); 

    $SqlUpdate = "UPDATE $tabela SET impressao_cancelado='1', situacao = 'CANCELADO', data_hora_baixa = NOW(), data_hora_cancelamento = NOW(), cod_usuarios_cancelamento='".$_SESSION['usuario']['codigo']."' WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO'";
      //echo "<Br>3: ".$SqlUpdate;

    $SqlEstornoFidelidade = "INSERT INTO ipi_fidelidade_clientes (cod_clientes, data_hora_fidelidade, data_validade, pontos) (SELECT cod_clientes, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), pontos_fidelidade_total FROM $tabela WHERE $chave_primaria IN ($indicesSql) AND situacao <> 'BAIXADO')";
      //echo "<Br>3: ".$SqlEstornoFidelidade;

    $sql_inserir_relatorio = sprintf("INSERT into ipi_impressao_relatorio (cod_pedidos,cod_usuarios,cod_pizzarias,relatorio,data_hora_inicial,situacao) (select p.cod_pedidos,".$_SESSION['usuario']['codigo'].",p.cod_pizzarias,'CANCELAMENTO',NOW(),'NOVO' from ipi_pedidos p WHERE $chave_primaria IN ($indicesSql))");
      //$res_inserir_relatorio = mysql_query($sql_inserir_relatorio);

      //echo "<Br>3: ".$sql_inserir_relatorio;
    
    if (mysql_query($SqlUpdate) && mysql_query($SqlEstornoFidelidade) && mysql_query($sql_inserir_relatorio)){
      mensagemOk('O pedido foi CANCELADO com sucesso!');
    }else{
      mensagemErro('Erro ao CANCELAR o pedido', 'Por favor, comunique a equipe de suporte informando todos os pedidos selecionados para definição.');
    }
    
  }

  desconectabd($con);
  break;
  case 'detalhes':
  $codigo = (validaVarPost($chave_primaria) ? validaVarPost($chave_primaria) : (validaVarGet("p") ? validaVarGet("p") : 0 )) ;

  $pedido = new Pedido();
  echo utf8_decode($pedido->retornar_resumo_pedido_sys($codigo,"h1"));

  $con = conectabd();

  $objBuscaCartao = executaBuscaSimples("SELECT cod_pedidos,forma_pg FROM ipi_pedidos WHERE cod_pedidos = $codigo", $con);
  if ( ($objBuscaCartao->forma_pg=="VISANET") || ($objBuscaCartao->forma_pg=="MASTERCARDNET") || ($objBuscaCartao->forma_pg=="VISANET-CIELO") )
  {
    echo '<br/>';
    echo '<br/>';
    echo '<p><b>Detalhes Operação Cartão Crédito</b></p>';
    echo '<hr noshade="noshade" color="#D44E08"/>';
    $sql_detalhes_cc = "SELECT * FROM ipi_pedidos_detalhes_pg WHERE cod_pedidos = ".$objBuscaCartao->cod_pedidos;
    $res_detalhes_cc = mysql_query($sql_detalhes_cc);
    while ( $obj_detalhes_cc = mysql_fetch_object($res_detalhes_cc) )
    {
      echo "<br /><strong>".$obj_detalhes_cc->chave."</strong>: ".$obj_detalhes_cc->conteudo;
    }
  }

  echo '<br><br><h3><a href="ipi_rel_historico_pedidos_novo.php">&laquo; Voltar</a></h3><br><br>';
  desconectabd($con);
  break;
}

?>

<style>

  .paginas{
    padding:5px;cursor:pointer;color: blue;
  }
  .preloader{
    border: dotted 2px red;
    width: 20px;
    height: 20px;
    border-radius: 100%;
    -webkit-animation: rotation 2s infinite;
  }
  @-webkit-keyframes rotation {
    from {
      -webkit-transform: rotate(0deg);
    }
    to {
      -webkit-transform: rotate(359deg);
    }
  }
  .toolip{
    display:block;
    padding:10px;
    background:#000;
    border-radius:10px 10px 10px 0px;
    color:#FFF;
    margin-left:10px;
  }
  .reemitenota{
    display: block; width: 105px; background: red; color: #FFF !important; border-radius: 10px; padding-top: 3px; padding-bottom: 3px;
  }
</style>
<? if($acao != 'detalhes'): ?>
  <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css"/>
  <script language="javascript" src="../lib/js/calendario.js"></script>

  <script>
    function verificaCheckbox(form) {
      var cInput = 0;
      var checkBox = form.getElementsByTagName('input');

      for (var i = 0; i < checkBox.length; i++) {
        if((checkBox[i].className.match('situacao')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
          cInput++; 
        }
      }

      if(cInput > 0) {
        if (confirm('Deseja mudar de situação o(s) pedido(s) selecionado(s)?')) {
          return true;
        }
        else {
          return false;
        }
      }
      else {
        alert('Por favor, selecione os itens que deseja mudar de situação (BAIXAR / CANCELAR).');

        return false;
      }
    }

    function editar(cod) {
      var form = new Element('form', {
        'action': '<? echo $_SERVER['PHP_SELF'] ?>',
        'method': 'post'
      });

      var input1 = new Element('input', {
        'type': 'hidden',
        'name': '<? echo $chave_primaria ?>',
        'value': cod
      });

      var input2 = new Element('input', {
        'type': 'hidden',
        'name': 'acao',
        'value': 'detalhes'
      });

      input1.inject(form);
      input2.inject(form);
      $(document.body).adopt(form);

      form.submit();
    }

    function cancelar() {
      if(verificaCheckbox(document.frmBaixa)) {
        document.frmBaixa.acao.value = "cancelar";
        document.frmBaixa.submit();
      }
    }

    function reimprimir(){
      if(confirm('Deseja muda a situação dos pedidos selecionados?')){
        let inputs = document.querySelectorAll('input[name="cod_pedidos[]"]');
        let ids = [];
        inputs.forEach(function(v,i){
          if(v.checked){
            ids.push(v.value);
          }
        });
        ids = ids.join();
        let xhr = new XMLHttpRequest();
        xhr.open('GET',"<?php echo URL_PEDIR_REIMPRESSAO; ?>"+ids,true);
        xhr.send();
        exibeModal('O(s) pedido(s) foram definidos como REIMPRESSÃO com sucesso!');
      }
        /*
      if(verificaCheckbox(document.frmBaixa)) {
        document.frmBaixa.acao.value = "reimprimir";
        document.frmBaixa.submit();
      }
      */
    }
    function cozinha() {
      if(verificaCheckbox(document.frmBaixa)) {
        document.frmBaixa.acao.value = "cozinha";
        document.frmBaixa.submit();
      }
    }

    function motoqueiro() {
      if(verificaCheckbox(document.frmBaixa)) {
        document.frmBaixa.acao.value = "motoqueiro";
        document.frmBaixa.submit();
      }
    }


    function agendamento() {
      if(verificaCheckbox(document.frmBaixa)) {
        document.frmBaixa.acao.value = "agendamento";
        document.frmBaixa.submit();
      }
    }

    window.addEvent('domready', function() { 
  // DatePick
  new vlaDatePicker('data_inicial', {openWith: 'botao_data_inicial', prefillDate: false});
  new vlaDatePicker('data_final', {openWith: 'botao_data_final', prefillDate: false});
}); 

</script>

<?php
$pagina = (validaVarPost('pagina', '/[0-9]+/')) ? validaVarPost('pagina', '/[0-9]+/') : 0; 

$pedido = (validaVarPost('pedido', '/[0-9]+/')) ? (int) validaVarPost('pedido', '/[0-9]+/') : (validaVarGet('p', '/[0-9]+/') ? (int) validaVarGet('p', '/[0-9]+/') : '');

$cliente = (validaVarPost('cliente')) ? validaVarPost('cliente') : '';
$telefone = (validaVarPost('telefone')) ? validaVarPost('telefone') : '';

$con = conectabd();

$cod_pizzarias = $_SESSION['usuario']['cod_pizzarias'][0];
require("../../pub_req_fuso_horario1.php"); 

$data_inicial = (validaVarPost('data_inicial') ? validaVarPost('data_inicial') : date('d/m/Y'));
$data_final = (validaVarPost('data_final') ? validaVarPost('data_final') : date('d/m/Y'));
$cod_pizzarias = validaVarPost('cod_pizzarias');
$situacao = validaVarPost('situacao');
$origem = validaVarPost('origem');
$entrega = validaVarPost('entrega');
$tempo_envio = validaVarPost('tempo_envio');
$ref = validaVarPost('ref');
$hora_final = validaVarPost('hora_final');
$hora_inicial = validaVarPost('hora_inicial');

$busca_telefone = substr($telefone, -4);
?>

<style>
  #formula-wait {
    position: relative;
    height: 90px;
    animation: heartbeat 1s infinite;
    margin:0 auto;
  }
  @keyframes heartbeat
  {
    0%
    {
      transform: scale( .75 );
    }
    20%
    {
      transform: scale( 1 );
    }
    40%
    {
      transform: scale( .75 );
    }
    60%
    {
      transform: scale( 1 );
    }
    80%
    {
      transform: scale( .75 );
    }
    100%
    {
      transform: scale( .75 );
    }
  }
</style>

<form name="frmFiltro" method="post">
  <table align="center" class="caixa" cellpadding="0" cellspacing="0">

    <tr>
      <td class="legenda tdbl tdbt" align="right">
        <label for="pedido">Código do Pedido:</label>
      </td>
      <td class="tdbt">&nbsp;</td>
      <td class="tdbt tdbr">
        <input class="requerido" type="text" name="pedido" id="pedido" size="60" value="<? echo $pedido ?>" onkeypress="return ApenasNumero(event)">
      </td>
    </tr>
    <tr>
      <td class="legenda tdbl tdbt" align="right">
        <label for="pedido">Ref da nota:</label>
      </td>
      <td class="tdbt">&nbsp;</td>
      <td class="tdbt tdbr">
        <input class="requerido" type="text" name="ref" id="ref" size="60" value="<? echo $ref ?>">
      </td>
    </tr>
    <tr>
      <td class="legenda tdbl" align="right">
        <label for="cliente">Cliente:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr">
        <input class="requerido" type="text" name="cliente" id="cliente" size="60" value="<? echo $cliente ?>">
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl" align="right">
        <label for="telefone">Telefone:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr">
        <input class="requerido" type="text" name="telefone" id="telefone" size="12" value="<? echo $telefone ?>" onKeyPress="return MascaraTelefone(this,event)">
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl" align="right">
        <label for="data_inicial">Data e Hora Inicial:</label>
      </td>
      <td>&nbsp;</td>
      <td class="tdbr">
        <input class="requerido" type="text" name="data_inicial" id="data_inicial" size="12" value="<? echo $data_inicial ?>" onkeypress="return MascaraData(this, event)">
        &nbsp;
        <a href="javascript:;" id="botao_data_inicial">
          <img src="../lib/img/principal/botao-data.gif">
        </a>
        &nbsp;
        <input type="text" name="hora_inicial" id="hora_inicial" size="3" value="<? echo $hora_inicial ?>" onkeypress="return MascaraHora(this, event)">
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl" align="right">
        <label for="data_final">Data e Hora Final:</label>
      </td>
      <td>&nbsp;</td>
      <td class="tdbr">
        <input class="requerido" type="text" name="data_final" id="data_final" size="12" value="<? echo $data_final ?>" onkeypress="return MascaraData(this, event)">
        &nbsp;
        <a href="javascript:;" id="botao_data_final">
          <img src="../lib/img/principal/botao-data.gif">
        </a>
        &nbsp;
        <input type="text" name="hora_final" id="hora_final" size="3" value="<? echo $hora_final ?>" onkeypress="return MascaraHora(this, event)">
      </td>
    </tr>
    <tr>
      <td class="legenda tdbl" align="right">
        <label for="cod_pizzarias">
          <? echo ucfirst(TIPO_EMPRESA)?>:</label>
        </td>
        <td>&nbsp;</td>
        <td class="tdbr">
          <select name="cod_pizzarias" id="cod_pizzarias">
            
            <!-- <option value="">Todas as <? //echo ucfirst(TIPO_EMPRESAS)?></option> -->
          <?php

          $SqlBuscaPizzarias = "SELECT * FROM ipi_pizzarias WHERE cod_pizzarias IN(".implode(',',$_SESSION['usuario']['cod_pizzarias']).") AND situacao='ATIVO' ORDER BY nome";
          $resBuscaPizzarias = mysql_query($SqlBuscaPizzarias);
          $todas = implode(',',$_SESSION['usuario']['cod_pizzarias']);
          echo "<option value='".$todas."'>TODAS</option>";
          while($objBuscaPizzarias = mysql_fetch_object($resBuscaPizzarias)) {
            echo '<option value="'.$objBuscaPizzarias->cod_pizzarias.'" ';

            if($objBuscaPizzarias->cod_pizzarias == $cod_pizzarias)
              echo 'selected';

            echo '>'.utf8_encode($objBuscaPizzarias->nome).'</option>';
          }

          desconectabd($con);
          ?>
        </select>
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl" align="right">
        <label for="situacao">Situação:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr">
        <select name="situacao" id=situacao>
          <option value="" <? if($situacao == 'TODOS') echo 'selected' ?>>Todas</option>
          <option value="NOVO" <? if($situacao == 'NOVO') echo 'selected' ?>>Novo</option>
          <option value="IMPRESSO" <? if($situacao == 'IMPRESSO') echo 'selected' ?>>Impresso</option>
          <option value="BAIXADO" <? if($situacao == 'BAIXADO') echo 'selected' ?>>Baixado</option>
          <option value="CANCELADO" <? if($situacao == 'CANCELADO') echo 'selected' ?>>Cancelado</option>
          <option value="ENVIADO" <? if($situacao == 'ENVIADO') echo 'selected' ?>>Enviado</option>
        </select>
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl" align="right">
        <label for="origem">Origem:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr ">
        <select name="origem" id="origem">
          <option value="" <? if($origem == 'TODOS') echo 'selected' ?>>Todas</option>
          <option value="NET" <? if($origem == 'NET') echo 'selected' ?>>Net</option>
          <option value="TEL" <? if($origem == 'TEL') echo 'selected' ?>>Tel</option>
          <option value="IFOOD" <? if($origem == 'IFOOD') echo 'selected' ?>>Ifood</option>
        </select>
      </td>
    </tr>

    <tr>
      <td class="legenda tdbl" align="right">
        <label for="entrega">Entrega:</label>
      </td>
      <td class="">&nbsp;</td>
      <td class="tdbr">
        <select name="entrega" id="entrega">
          <option value="" <? if($entrega == 'TODOS') echo 'selected' ?>>Todas</option>
          <option value="Entrega" <? if($entrega == 'Entrega') echo 'selected' ?>>Entrega</option>
          <option value="Balcão" <? if($entrega == 'Balcão') echo 'selected' ?>>Balcão</option>
        </select>
      </td>

      <tr>
        <td class="legenda tdbl sep" align="right">
          <label for="tempo_envio">Tempo de Envio:</label>
        </td>
        <td class="sep">&nbsp;</td>
        <td class="tdbr sep">
          <select name="tempo_envio" id="tempo_envio">
            <option value="" <? if($tempo_envio == 'TODOS') echo 'selected' ?>>Todos</option>
            <option value="Branco" <? if($tempo_envio == 'Branco') echo 'selected' ?>>Dentro do Prazo</option>
            <option value="Amarelo" <? if($tempo_envio == 'Amarelo') echo 'selected' ?>>No Limite do Prazo</option>
            <option value="Vermelho" <? if($tempo_envio == 'Vermelho') echo 'selected' ?>>Atrasado</option>
            <option value="Amarelo_Vermelho" <? if($tempo_envio == 'Amarelo_Vermelho') echo 'selected' ?>>No Limite do Prazo e Atrasado</option>
          </select>
        </td>
      </tr>
    </tr>
    <tr>
      <td align="right" class="tdbl tdbb tdbr" colspan="3">
        <input class="botaoAzul" type="submit" name="buscar" value="Buscar">
      </td>
    </tr>

  </table>
  
  <input type="hidden" name="acao" value="buscar">
</form>

<br>
<center id="paginas">
  <span style="margin-right: 5px;">
    <a id="anterior" href="javascript:;" style="margin-left: 5px;">«&nbsp;</a>
  </span>
  <span id="numeracao">
  </span>
  &nbsp;&nbsp;|&nbsp;&nbsp;
  <a id="proxima" href="javascript:;" style="margin-left: 5px;">&nbsp;»
  </a>
</center>
<br>

<form name="frmBaixa" method="post">
  <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
    <tr>
      <td width="50" align="left">
        <input class="botaoAzul" type="button" value="Reimprimir" onclick="reimprimir()">
      </td>
    </tr>
  </table>

  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td colspan='4' id="n_total_pedidos" style='background-color:#E5E5E5;font-weight:bold'>Número total de pedidos <span></span></td>
        <td colspan='2' id="total_pedidos_cancelados" style='background-color:#E5E5E5;font-weight:bold'>Total dos pedidos cancelados <span></span> </td>
        <td colspan='3' id="total_pedidos_baixados" style='background-color:#E5E5E5;font-weight:bold'>Total dos pedidos baixados <span></span> </td>
        <td colspan='3' id="total_pedidos_nao_cancelados" style='background-color:#E5E5E5;font-weight:bold'>Total dos pedidos não cancelados <span></span></td>
        <td colspan='2' id="total_frete">
          Total Frete <span></span>
        </td>
        <td colspan='2' id="desconto">
          Descontos <span></span>
        </td>
      </tr>
      <tr>
        <td align="center" width="20">
          <input type="checkbox" onclick="marcaTodos('marcar');">
        </td>
        <td align="center" width="70">Pedido</td>
        <td align="center" width="70">CUPONS DOS PEDIDOS</td>
        <td align="center">Nota Fiscal</td>
        <td align="center">Cancelar Nota Fiscal / Pedido</td>
        <td align="center">Cliente</td>
        <td align="center">Endereço</td>
        <td align="center" width="120">Forma de pagamento</td>
        <td align="center" width="70">Situação</td>
        <td align="center" width="70">Agendado</td>
        <td align="center" width="130"><? echo ucfirst(TIPO_EMPRESA)?></td>
        <td align="center" width="70">Horário do Pedido</td>
        <td align="center" width="70">Horário da Expedição</td>
        <td align="center" width="70">Horário da Baixa</td>
        <td align="center" width="70">Valor Total</td>
        <td align="center" width="70">Origem</td>
      </tr>
    </thead>
    <tbody>

      <?php
      desconectabd($con);
      ?>

    </tbody>
    <tfoot id="registros">

    </tfoot>
  </table>
  
  <style type="text/css">
    .mensagemOk{
      position: absolute; top: 0px; right: 0px; background-color: rgb(255, 255, 255);
    }
  </style>
  
  <div class="mensagemOk" style="opacity:0;visibility: hidden;">
    <h2>O(s) pedido(s) foram definidos como REIMPRESSÃO com sucesso!</h2>
  </div>
  <div style="padding-left:35%" align="center">
    <br/>
    <div style='background-color:orange;width:220px;float:left'>Tempo de Envio Maior que <b>
      <?php echo (defined('TEMPO_ENTREGA')? TEMPO_ENTREGA -10: 20)?>
    </b> minutos&nbsp;</div>
    &nbsp;&nbsp;<div style='background-color:lightCoral;width:220px;float:left'>&nbsp;Tempo de Envio Maior que <b>
      <?php echo (defined('TEMPO_ENTREGA')? TEMPO_ENTREGA : 30);?>
    </b> minutos</div>
    <div style="clear:both">
    </div>
  </div>

  <input type="hidden" name="acao" value="">
</form>
<div id="dadosJSON" style="display: none;">

</div>
<? endif; ?>
<script type="text/javascript">
  let mensagemOk = document.querySelector('div.mensagemOk');

  function exibeModal(mensagem){
    mensagemOk.querySelector('h2').innerText = mensagem;
    mensagemOk.setAttribute('style','');
    let valor = 1;
    let opacidade = setInterval(function(){
      valor -= 0.1;
      mensagemOk.setAttribute('style','opacity:'+valor+';');
      if(0 >= valor){
        clearInterval(opacidade);
        mensagemOk.setAttribute('style','opacity:0;visibility: hidden;');
      }
    },100);
  }

  function tooltip(){
    let doc = document;
    let tooltip = document.querySelectorAll('.canceladoTooltip');
    let clientX;
    let clientY;
    let item;
    let reemitenota = document.querySelectorAll('.reemitenota');
    let recebeu;
    reemitenota.forEach(function(v){
      v.onclick = function(click){
        click.preventDefault();
        let este = this;
        let ref = este.parentNode.querySelector('div[ref]').getAttribute('ref')
        let url = this.getAttribute('href');
        recebeu = false;
        este.style = 'display:none;';
        este.parentNode.querySelector('.preloader').style='';
        este.parentNode.parentNode.querySelector('span.remissaoTexto').innerText = 'Reemitindo..';
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function(ev){
          let resState = this;
          if(recebeu == false && resState.readyState == 4 && resState.status == 200){
            recebeu = true;
            let item = document.querySelector('div[ref="'+ref+'"]').parentNode.parentNode;
            let res = JSON.parse(resState.response);
            if(res.status && res.status == 'autorizado'){
              item.querySelector('.preloader').style='display:none;';
              item.querySelector('span.remissaoTexto').innerText = 'Reemitido com succeso!';
            }else{
              item.querySelector('.preloader').style='display:none;';
              item.querySelector('span.remissaoTexto').innerText = 'Não foi possível reemitir';
            }
          }
        }
        xhr.open('GET',url,true);
        xhr.send();
      }
    });

    tooltip.forEach(function(v,i){
      if(v.parentNode.querySelector('.toolip').innerText == ""){
        v.parentNode.querySelector('.toolip').innerText = "Nenhum motivo cadastrado";
      }
      v.onmouseenter = function(i){
        clientX = i.clientX;
        clientY = i.clientY-20;
        v.parentNode.querySelector('.toolip').style +=";display:block;position:absolute;top:"+clientY+"px;left:"+clientX+"px;";
      };
      v.onmouseleave = function(i){
        v.parentNode.querySelector('.toolip').style.removeProperty('position');
        v.parentNode.querySelector('.toolip').style.removeProperty('top');
        v.parentNode.querySelector('.toolip').style.removeProperty('left');
        v.parentNode.querySelector('.toolip').style.removeProperty('display');
        v.parentNode.querySelector('.toolip').style +=';display:none;';
      }
    });
  }

  function sanfona(){
    const ul_sanfonas = document.querySelectorAll("b[b-class='sanfona']");
    if(ul_sanfonas.length >0){
      ul_sanfonas.forEach(function(v,i){
        v.onclick = function(){
          let este = this;
          let classe = este.className;
          classe = classe.split('sanfona').pop();
          if(este.getAttribute('abriu') == null){
            document.querySelector('ul.ul'+classe).style = "margin-left: 15px;display:block;";
            este.setAttribute('abriu','1');
          }else{
            document.querySelector('ul.ul'+classe).style = "margin-left: 15px;display:none;";
            este.removeAttribute('abriu');
          }
        }
      });
    }
  }

  let buscar = document.querySelector('input[name="buscar"]');
  let registros = document.querySelector('#registros');
  let json;

  let exibeCupomPedido = function(origem_pedido,cod_pedido,tipo_cupom){
    let pedido = '';
    if(origem_pedido == 'IFOOD'){
      if(tipo_cupom == 'balcao'){
        pedido = '<a style="display: block; background: yellow; border: 1px solid #000; padding: 2px;" target="_blank" href="<?php echo CAMINHO_PEDIDO_IMPRESSO_IFOOD; ?>'+cod_pedido+'">CUPOM PEDIDO</a>';
      }else{
        pedido = '<a style="display: block; background: yellow; border: 1px solid #000; padding: 2px;" target="_blank" href="<?php echo CAMINHO_PEDIDO_COZINHA_IFOOD; ?>'+cod_pedido+'">CUPOM COZINHA</a>';
      }
    }else{
      if(tipo_cupom == 'balcao'){
        pedido = '<a style="display: block; background: yellow; border: 1px solid #000; padding: 2px;" target="_blank" href="<?php echo CAMINHO_PEDIDO_IMPRESSO_TEL; ?>'+cod_pedido+'">CUPOM PEDIDO</a>';
      }else{
        pedido = '<a style="display: block; background: yellow; border: 1px solid #000; padding: 2px;" target="_blank" href="<?php echo CAMINHO_PEDIDO_COZINHA_TEL; ?>'+cod_pedido+'">CUPOM COZINHA</a>';
      }
    }
    return pedido;
  }

  let arrumaData = function(data){
    let data_ = data.split(' ');
    data_[1] = (data_[1] != undefined)?data_[1]:"";
    let data_inicial = data_[0].split('-');
    data_inicial = data_inicial[2]+'/'+data_inicial[1]+'/'+data_inicial[0];
    return data_inicial+' '+data_[1];
  }

  let dessarumaData = function(data){
    let data_ = data.split('/');
    return data_[2]+'-'+data_[1]+'-'+data_[0];
  }

  let notaFiscal = function(arquivo_json){
    arquivo_json = JSON.parse(arquivo_json);
    let html = '';
    if(arquivo_json != null && arquivo_json != 'null'){
      html = '<a target="_blank" href="https://api.focusnfe.com.br'+arquivo_json['caminho_danfe']+'" style="display:block;border: 1px solid #333;margin: 3px;background: yellow;padding: 2px;">CUPOM FISCAL</a><a target="_blank" href="'+arquivo_json.qrcode_url+'" style="display:block;border: 1px solid #333;margin: 3px;background: yellow;padding: 2px;">NOTA FISCAL</a>';
    }
    return html;
  }

  let div_n_total_pedidos = document.querySelector('td#n_total_pedidos span');
  let div_total_pedidos_cancelados = document.querySelector('td#total_pedidos_cancelados span');
  let div_total_pedidos_baixados = document.querySelector('td#total_pedidos_baixados span');
  let div_total_pedidos_nao_cancelados = document.querySelector('td#total_pedidos_nao_cancelados span');
  let div_total_frete = document.querySelector('td#total_frete span');
  let div_desconto = document.querySelector('td#desconto span');

  let n_total_pedidos = 0;
  let total_pedidos_cancelados = 0;
  let total_pedidos_baixados = 0;
  let total_pedidos_nao_cancelados = 0;
  let total_frete = 0;
  let desconto = 0;
  let pizzarias = "<?php echo implode(',',$_SESSION['usuario']['cod_pizzarias']); ?>";

  let percorreJSONparaValores = function(json){
    desconto = 0;
    total_frete = 0;
    n_total_pedidos = 0;
    total_pedidos_cancelados = 0;
    total_pedidos_baixados = 0;
    total_pedidos_nao_cancelados = 0;

    json.forEach(function(v,i){
      n_total_pedidos++;
      total_frete += parseFloat(v.valor_entrega);
      desconto += parseFloat(v.desconto);
      if(v.situacao == "CANCELADO"){
        total_pedidos_cancelados+= parseFloat(v.valor_total);
      }
      if(v.situacao == "BAIXADO"){
        total_pedidos_baixados+= parseFloat(v.valor_total);
      }
      if(v.situacao != "CANCELADO"){
        total_pedidos_nao_cancelados+= parseFloat(v.valor_total);
      }
    });

    div_n_total_pedidos.innerText = ": "+n_total_pedidos;
    div_total_pedidos_cancelados.innerText = ": R$"+total_pedidos_cancelados.toFixed(2);
    div_total_pedidos_baixados.innerText = ": R$"+total_pedidos_baixados.toFixed(2);
    div_total_pedidos_nao_cancelados.innerText = ": R$"+total_pedidos_nao_cancelados.toFixed(2);
    div_total_frete.innerText = ": R$"+total_frete.toFixed(2);
    div_desconto.innerText = ": R$"+desconto.toFixed(2);


  }

  let impoeTabela = function(json,primeiro,ultimo){

   json = json.slice(primeiro,ultimo);
   registros.innerHTML = '';

   json.forEach(function(v,i){

    if(v.situacao == "CANCELADO"){
      let cancelamento_json = JSON.parse(v.cancelamento_json);
      v.situacao = '<div class="toolip" style="display: none;">'+cancelamento_json.justificativa+'</div><a class="canceladoTooltip" href="<?php echo CAMINHO_PEDIDO_CANCELADO; ?>'+v.cod_pedidos+'" style="display: block; background: yellow; border: 1px solid #000; padding: 2px;" target="_blank">CANCELADO</a>';
    }
    
    let temporizador_inicial = new Date(v.data_hora_inicial);
    let temporizador_agora = new Date(v.data_hora_envio);
    let diferenca_temporizador = temporizador_agora.getTime() - temporizador_inicial.getTime();
    diferenca_temporizador = Math.round(diferenca_temporizador/60000);
    
    v.data_hora_pedido = (v.data_hora_pedido != null && v.data_hora_pedido != 'null')?arrumaData(v.data_hora_pedido):"";
    v.data_hora_envio = (v.data_hora_envio != null && v.data_hora_envio != 'null')?arrumaData(v.data_hora_envio):"";
    v.data_hora_baixa = (v.data_hora_baixa != null && v.data_hora_baixa != 'null')?arrumaData(v.data_hora_baixa):"";
    v.agendado = (v.horario_agendamento != 0 && v.horario_agendamento != '0')?v.horario_agendamento:"";

    

    let cor = "";
    if(diferenca_temporizador >19){
      cor = "#FFA500";
    }
    if(diferenca_temporizador >29){
      cor = "#F08080";
    }

    let cancelar = "<a onclick='return confirm(Você tem certeza que quer cancelar o pedido?)' href='cancelar_nota.php?ref="+v.cod_pedidos+"'>CANCELAR PEDIDO</a>";
    let nota_fiscal = notaFiscal(v.arquivo_json);
    let html = '';
    let cupom_pedido_balcao = exibeCupomPedido(v.origem_pedido,v.cod_pedidos,'balcao');
    let cupom_pedido_cozinha = exibeCupomPedido(v.origem_pedido,v.cod_pedidos,'cozinha');
    html+="<tr style='background:"+cor+"'>";
    html+="<td align='center'><input type='checkbox' class='marcar situacao' name='cod_pedidos[]' value='"+v.cod_pedidos+"'></td>";
    html+="<td align='center'><a href='<?php echo URL_PEDIDOS; ?>"+v.cod_pedidos+"'>"+v.cod_pedidos+"</a></td>";
    html+="<td align='center'>"+cupom_pedido_balcao+cupom_pedido_cozinha+"</td>";
    html+="<td align='center'>"+nota_fiscal+"</td>";
    html+="<td align='center'>"+cancelar+"</td>";
    html+="<td align='center'><a href='<?php echo URL_CLIENTES; ?>"+v.cod_clientes+"'>"+v.nome_cliente+"</a></td>";
    html+="<td align='center'>"+v.endereco+", "+v.bairro+", "+v.numero+", "+v.cidade+"/"+v.estado+"</td>";
    html+="<td align='center'>"+v.forma_pg+"</td>";
    html+="<td align='center'>"+v.situacao+"</td>";
    html+="<td align='center'>"+v.agendado+"</td>";
    html+="<td align='center'>"+v.nome+"</td>";
    html+="<td align='center'>"+v.data_hora_pedido+"</td>";
    html+="<td align='center'>"+v.data_hora_envio+"</td>";
    html+="<td align='center'>"+v.data_hora_baixa+"</td>";
    html+="<td align='center'>R$"+v.valor_total+"</td>";
    html+="<td align='center'>"+v.origem_pedido+"</td>";
    html+="</tr>";
    registros.innerHTML +=html;
  });

   tooltip();
   sanfona();
   
 }

 let campo_cod_pedido = document.querySelector('input[name="pedido"]');
 let campo_ref_nota = document.querySelector('input[name="ref"]');
 let campo_cliente = document.querySelector('input[name="cliente"]');
 let campo_telefone = document.querySelector('input[name="telefone"]');
 let campo_data_inicial = document.querySelector('input[name="data_inicial"]');
 let campo_hora_inicial = document.querySelector('input[name="hora_inicial"]');
 let campo_data_final = document.querySelector('input[name="data_final"]');
 let campo_hora_final = document.querySelector('input[name="hora_final"]');
 let campo_pizzaria = document.querySelector('select[name="cod_pizzarias"]');
 let campo_situacao = document.querySelector('select[name="situacao"]');
 let campo_origem = document.querySelector('select[name="origem"]');
 let campo_entrega = document.querySelector('select[name="entrega"]');
 let campo_tempo_envio = document.querySelector('select[name="tempo_envio"]');

 let dadosJSON = document.querySelector('div#dadosJSON');
 let animacao = '<tr><td colspan="16" style="text-align: center;"><img id="formula-wait" src="<?php echo URL_API; ?>/img/formula_pizzaria_delivery.jpg" style="max-width:155px;margin:auto;" /><br /><strong>Carregando..</strong></td></tr>';


 buscar.onclick = function(e){
  e.preventDefault();
  let este = this;
  let lcampo_cod_pedido = (campo_cod_pedido.value != '')?campo_cod_pedido.value:null;
  let lcampo_ref_nota = (campo_ref_nota.value != '')?campo_ref_nota.value:null;
  let lcampo_cliente = (campo_cliente.value != '')?campo_cliente.value:null;
  let lcampo_telefone = (campo_telefone.value != '')?campo_telefone.value:null;
  let lcampo_data_inicial = (campo_data_inicial.value != '')?dessarumaData(campo_data_inicial.value):null;
  let lcampo_hora_inicial = (campo_hora_inicial.value != '')?campo_hora_inicial.value:'00:00:01';
  let lcampo_data_final = (campo_data_final.value != '')?dessarumaData(campo_data_final.value):null;
  let lcampo_hora_final = (campo_hora_final.value != '')?campo_hora_final.value:'23:59:59';
  let lcampo_pizzaria = (campo_pizzaria.value != '')?campo_pizzaria.value:null;
  let lcampo_situacao = (campo_situacao.value != '')?campo_situacao.value:null;
  let lcampo_origem = (campo_origem.value != '')?campo_origem.value:null;
  let lcampo_entrega = (campo_entrega.value != '')?campo_entrega.value:null;
  let lcampo_tempo_envio = (campo_tempo_envio.value != '')?campo_tempo_envio.value:null;

  let link_historico_pedidos = '<?php echo AJAX_HISTORICO_PEDIDOS; ?>'+lcampo_cod_pedido+'/'+lcampo_ref_nota+'/'+lcampo_cliente+'/'+lcampo_telefone+'/'+lcampo_data_inicial+' '+lcampo_hora_inicial+'/'+lcampo_data_final+' '+lcampo_hora_final+'/'+lcampo_pizzaria+'/'+lcampo_situacao+'/'+lcampo_origem+'/'+lcampo_entrega+'/'+lcampo_tempo_envio;

  let paginas = document.querySelector('center#paginas');
  let numeracao = document.querySelector('span#numeracao');
  let totalPaginas = 1;
  

  let clicaPagina = function(pagina){

    let json = JSON.parse(dadosJSON.innerHTML);
    let ultimo = 25*parseInt(pagina);
    let primeiro = ultimo-25;
    document.querySelectorAll('b[pagina]').forEach(function(v,i){
      v.setAttribute('style','');
    });
    document.querySelector('b[pagina="'+pagina+'"]').setAttribute('style',"color:black;");
    registros.innerHTML = animacao;
    impoeTabela(json,primeiro,ultimo);
  }

  let publicaPaginas = function(numero){
    numeracao.innerHTML = '';
    for (i = 1; i < numero+1; i++) {
      numeracao.innerHTML += "<b class='paginas' pagina='"+i+"'>"+i+"</b>";  
    }
    document.querySelectorAll('b[pagina]').forEach(function(v,i){
      v.onclick = function(){
        let este = this;
        clicaPagina(este.innerText.trim());
      }
    });
  }


  este.value = 'Buscando..';
  registros.innerHTML = animacao;
  xhr = new XMLHttpRequest();
  xhr.open('GET',link_historico_pedidos,true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
  xhr.onreadystatechange = function(e){
    let resState = this;
    if(resState.readyState == 4 && resState.status == 200){
      json = JSON.parse(resState.response);
      dadosJSON.innerHTML = resState.response;
      registros.innerHTML = '';
      totalPaginas = Math.round(Object.keys(json).length/25);
      percorreJSONparaValores(json);
      publicaPaginas(totalPaginas);
      impoeTabela(json,0,25);
    }
    este.value = 'Buscar';
  }
  xhr.send();

}

</script>
<? rodape(); ?>
