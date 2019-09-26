<?php

/**
 * ipi_central_comunicacao.php: Central de comunicação dos muzza
 * 
 * Índice: 
 * Tabela: 
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/canal_comunicacao.php';

cabecalho('Canal de Comunicação');

?>
<link  href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen" />
<script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
<script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
<?

$cod_situacoes_resolvido = 5;
$acao = validaVarPost('acao');
$cod_usuario = $_SESSION['usuario']['codigo'];
$cod_pizzarias_usuario = implode(", ",$_SESSION['usuario']['cod_pizzarias']);

//$tabela = 'ipi_combos_produtos';
//$chave_primaria = 'cod_combos';
//$quant_pagina = 80;

switch($acao) 
{
  case 'cadastrar_ticket':
    $con = conectar_bd();
    $nome_ticket = validaVarPost('nome_ticket');
    $categoria_ticket = validaVarPost('categoria_ticket');
    $mensagem_ticket = validaVarPost('mensagem_ticket');
    $arquivos = validaVarFiles('arq');
    $arquivos_descricao = validaVarPost('desc_arq');

    $canal_ticket = new CanalDeComunicacao_ticket();

    $resultado = $canal_ticket->cadastrar_novo($cod_usuario,$_SESSION['usuario']['cod_pizzarias'][0],$categoria_ticket,$nome_ticket,$mensagem_ticket,'',$arquivos,$_SESSION['usuario']['cod_pizzarias'][0]);
   // ($usuario_criador,$pizzaria_usuario,$categoria_ticket,$nome_ticket,$mensagem_ticket,$cod_prioridades,$arquivos,$pizzarias) 

    if($resultado>0)
    {
      echo "<script>new MooDialog.Alert('Ticket Cadastrado com sucesso, numero #".sprintf("%04d",$resultado)."',{title: 'Alert' });</script>";

    }else
      echo "<script>new MooDialog.Error('Erro ao cadastrar ticket');</script>";
      desconectar_bd($con);
  break;
}
/*<pre>
<? print_r($_SESSION); ?>

  </pre>*/
?>
<style type='text/css'>
  .listaEdicao thead
  {
    border: none;
  }

  .listaEdicao thead tr td
  {
    background: #FAFAFA url('../lib/img/principal/fundo-topo.gif') repeat-x scroll top left;
    border: 1px #EB8612 solid;
  }

  .listaEdicao thead h1
  {
    color: #FFF;
    margin-left: 10px;
    float: left;
  }
</style>

<script type="text/javascript" src="../../sys/lib/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
<!--
tinyMCE.init({
  mode : "textareas",
  theme : "advanced",
  skin : "o2k7",
  language : "pt",
  plugins: "inlinepopups,fullscreen,media, table", 
  theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,|,bullist,numlist,|,link,unlink,|,fullscreen,code,media,table",
  theme_advanced_buttons2 : "",
  theme_advanced_buttons3 : "",
  theme_advanced_toolbar_location : "top",
  theme_advanced_toolbar_align : "left",
  theme_advanced_statusbar_location : "bottom",
  theme_advanced_resizing : true,
  auto_reset_designmode : true,
  force_br_newlines : true,
  force_p_newlines : false,
  entity_encoding : "raw"
});

//-->
</script>
<script type="text/javascript">
function carregar_tickets(pagina,tipo)
{
  if(typeof(tipo)=="undefined")
  {
    tipo = "tickets";
  }
  var url = "acao=carregar_tickets&pagina="+pagina+"&tipo="+tipo;
  new Request.HTML(
  {
      url: 'ipi_central_comunicacao_ajax.php',
      update: tipo,
      method:'post'
  }).send(url);
}
window.addEvent('domready', function() 
{
  carregar_novidades(1);
  carregar_tickets(1);
  carregar_tickets(1,'tickets_sug');
  //carregar_cronogramas(1);
});

function add_arq(div_alterar)
{
  var div_arquivo = new Element('div', {
        name: 'arquivo'
  });

  var label_arquivo = new Element('label', {
        html: 'Arquivo :'
  });

  var arquivo = new Element('input', {
        type: 'file',
        name: 'arq[]'
  });

    //new Element('span#another')]
  div_arquivo.adopt(new Element('<br/>'));
  div_arquivo.adopt(label_arquivo);
  div_arquivo.adopt('&nbsp;');
  div_arquivo.adopt(arquivo);
  div_arquivo.adopt(new Element('<br/>'));
  $(div_alterar).adopt(div_arquivo);
}
function carregar_cronogramas(pagina)
{
  var url = "acao=carregar_cronogramas&pagina="+pagina;
  new Request.HTML(
  {
      url: 'ipi_central_comunicacao_ajax.php',
      update: 'cronograma',
      method:'post'
  }).send(url);

}
function carregar_novidades(pagina)
{
  var url = "acao=carregar_novidades&pagina="+pagina;
  new Request.HTML(
  {
      url: 'ipi_central_comunicacao_ajax.php',
      update: 'novidades',
      method:'post'
  }).send(url);

}
/*function carregar_sistema()
{
  var url = "acao=carregar_sistema";
  new Request.HTML(
  {
      url: 'ipi_central_comunicacao_ajax.php',
      update: 'sistema',
      method:'post',
  }).send(url);
}*/
function novo_ticket()
{
  var url = "acao=exibir_form_cadastro_ticket";
  new Request.HTML(
  {
      url: 'ipi_central_comunicacao_ajax.php',
      update: 'tickets',
      method:'post'
  }).send(url);
}
function abrir_novidade(cod,nome_novidade)
{
	var opcoes = "method:'post'";//method:'post'  
  var variaveis = 'acao=exibir_novidade_detalhada&cod_novidades='+cod;
	var reqDialog = new MooDialog.Request('ipi_central_comunicacao_ajax.php',variaveis,opcoes, {
		'class': 'MooDialog',
		autoOpen: false,
    title: nome_novidade,
    size: {
           width: 500,
           height: 300
          }
	});
	// You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
	reqDialog.setRequestOptions({
		onRequest: function(){
			reqDialog.setContent('Carregando...')
		}
	}).open();
}
function abrir_cronograma(cod,nome_cronograma)
{
  var opcoes = "method:'post'";//method:'post'
  var variaveis = 'acao=exibir_cronograma_detalhado&cod_cronograma='+cod;
  var reqDialog = new MooDialog.Request('ipi_central_comunicacao_ajax.php',variaveis,opcoes, {
    'class': 'MooDialog',
    autoOpen: false,
    title: nome_cronograma
  });
  // You want the request dialog instance to set the onRequest message, so you have to do it in two steps.
  reqDialog.setRequestOptions({
    onRequest: function(){
      reqDialog.setContent('Carregando...')
    }
  }).open();
}
function ler_ticket(cod) {
  var form = new Element('form', {
    'action': 'ipi_central_tickets.php',
    'method': 'post'
  });
  
  var input = new Element('input', {
    'type': 'hidden',
    'name': 'cod_tickets',
    'value': cod
  });
  
  input.inject(form);
  $(document.body).adopt(form);
  
  form.submit();
}

</script>

<div style="width:1000px;text-align:center;margin: 0 auto;">
  <!--<div id="esquerda">


    

    <div id="cronograma"  class='div_cont'>

    </div>

  </div>-->

  <div id="direita">
    <div id="novidades" class='div_cont'>

    </div>
    <div id="tickets" class='div_cont'>
    </div>

    <div id="tickets_sug" class='div_cont'>
    </div>
  </div>
</div>


<? rodape(); ?>
