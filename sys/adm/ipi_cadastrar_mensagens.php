<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
 * 
 * Índice: cod_pedidos
 * Tabela: ipi_pedidos
 */
header('Content-Type: text/html; charset=UTF-8');
require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';
require_once '../../classe/pedido.php';
cabecalho('Cadastrar Mensagens');
$get = $_GET;
$con = conectabd();
if(isset($get['acao']) and $get['acao'] == 'ajax' and $get['status'] == '2'){
  $sql = "UPDATE ipi_mensagens SET status='2' WHERE id = '".$get['id']."'";
  mysql_query($sql);
}
if(isset($get['acao']) and $get['acao'] == 'ajax' and $get['status'] == '1'){
  $sql = "UPDATE ipi_mensagens SET status='2'";
  mysql_query($sql);
  $sql = "UPDATE ipi_mensagens SET status='1' WHERE id ='".$get['id']."'";
  mysql_query($sql);
}
$sql = "SELECT * FROM ipi_mensagens";
$dados_ = mysql_query($sql);
$num = mysql_num_rows($dados_);
if(isset($_POST) and !empty($_POST)){
  $dados = $_POST;
  $imagem = $_FILES;
  $nomeImg = date('Ymdhis').$imagem['imagem']['name'];
  if(isset($imagem['imagem']['tmp_name']) and !empty($imagem['imagem']['tmp_name'])){
    //move_uploaded_file($imagem['imagem']['tmp_name'], '../../comunicacao/suporte/'.$nomeImg);      
  }
  $p = htmlspecialchars($dados['paragrafo']);
  $texto = json_encode(array(
    'titulo'=>$dados['titulo'],
    'paragrafo'=>$p,
    'botao'=>$dados['botao'],
    'ids_pedidos'=>$dados['ids_pedidos'],
    'img'=>''//$nomeImg
  ),true);
  if(!empty($dados['id'])){
    $sql = "UPDATE ipi_mensagens SET texto='".$texto."',criado=NOW() WHERE id='".$dados['id']."'";
  }else{
    $sql = "INSERT INTO ipi_mensagens (texto,status,criado) VALUES ('".$texto."',1,NOW())";
  }
  mysql_query($sql);
}
?>
<style>
  #fundoModal{
    position: fixed; left: 0; top: 0; background: #000; width: 100%; height: 100%; opacity: 0.8; z-index: 2;animation:1s;
  }
  .modal{
    width: 100%;
    height: 100%;
    position: fixed;
    z-index: 10; 
    animation: 1s;
  }
  .modal .modal-conteudo{
    width: 825px;
    min-height: 300px;
    /*max-height: 400px;*/
    height: auto;
    background: #FFF;
    border-radius: 10px;
    margin: auto;
    margin-top: -95px;
    padding: 20px;
    font-family: sans-serif;
  }
  .modal .modal-conteudo .floatleft{
    float:left !important;
    width: 134px;
    min-height: 372px;
  }
  .modal .modal-conteudo .floatright{
    float:right !important;
  }
  .modal .modal-conteudo .img-modal{
    margin: auto;
    width: 127px !important;
    float: left;
    cursor: pointer;
  }
  .modal .modal-conteudo .texto{
    float: right !important;
    width: 682px !important;
    text-align: left;
    min-height: 300px;
    max-height: 339px;
    overflow-y: auto;
  }
  .modal .modal-conteudo .texto h2{
    font-size: 28px !important;
  }
  .modal .modal-conteudo .texto p{
    font-size: 20px !important;
  }
  .modal .modal-conteudo .texto ul{
    font-size: 18px !important;
  }
  .modal .botao{
    width: 825px !important;
    text-align: center;
  }
  .modal .botao #confirmacaoLeitura{
    background: red;
    border: 0px;
    padding: 11px;
    color: #FFF;
    border-radius: 10px;
    font-size: 17px;
    font-weight: 300;
    margin-top: 17px;
    cursor:pointer;
    display: block;
    margin: auto;
    display: inline;
  }
  .modal .botao #salvarForm{
    background: #4CAF50;
    border: 0px;
    padding: 11px;
    color: #FFF;
    border-radius: 10px;
    font-size: 17px;
    font-weight: 300;
    margin-top: 17px;
    cursor:pointer;
    display: block;
    display: inline;
  }
  .center{
    margin: auto;
    text-align: center;
  }
  .btnAtivar{
    padding:10px;
    background:#FFF;
    border:1px solid #1C4D99;
    display: block;
    cursor: pointer;
  }
</style>

<div class="modal" style="display: none;">
  <div class="modal-conteudo">
    <div class="floatleft">
      <label>
        <form enctype="multipart/form-data" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="janelaModalForm" style=" display: none; ">
          <input type="file" name="imagem">
          <input type="hidden" name="id" value="<?php echo isset($dados['id'])?$dados['id']:""; ?>">
          <input type="hidden" name="titulo" value="<?php echo isset($dados['titulo'])?$dados['titulo']:"Título de exemplo"; ?>">
          <input type="hidden" name="paragrafo" value="<?php echo isset($dados['paragrafo'])?$dados['paragrafo']:"Testo de exemplo"; ?>">
          <input type="hidden" name="botao" value="<?php echo isset($dados['botao'])?$dados['botao']:"Ok, estou ciente!"; ?>">
          <input type="hidden" name="ids_pedidos" value="<?php echo isset($dados['ids_pedidos'])?$dados['ids_pedidos']:""; ?>">
        </form>
        <img editor="imagem" src="https://rlv.zcache.com.pt/adesivo_cara_feliz_emoji_do_sorriso_grande_super-rc78a444afa9a4f0d838728ae8157daf8_v9waf_8byvr_540.jpg" class="img-modal" />
      </label>
    </div>
    <div class="texto">
      <h2 contentEditable="true" editor='h2'>
        <?php echo isset($dados['titulo'])?$dados['titulo']:"Título de exemplo"; ?>
      </h2>
      <p contentEditable="true" editor="p">
        <?php echo isset($dados['paragrafo'])?$dados['paragrafo']:"Testo de exemplo"; ?>
        <br>
      </p>
    </div>
    <div class="botao">
      <button style="margin-top: 10px;" id="confirmacaoLeitura" class="center" editor="botao" contentEditable="true">
        <?php echo isset($dados['botao'])?$dados['botao']:"Ok, estou ciente!"; ?>
      </button>
    </div>
  </div>
  <div class="modal-conteudo" style="padding-top: 96px;min-height: 54px;">
    <div class="closeModal" style=" font-family: sans-serif; font-weight: 900; font-size: 22px; position: absolute; width: 33px; height: 33px; margin: auto; text-align: center; top: -101px; background: #FFF; border-radius: 14px; box-shadow: 2px 0px 4px; margin-left: -29px;cursor: pointer; ">X</div>
    <div style="display: table;width:100%;">
      <label style="margin-right:10px;">
        <!-- <input type="checkbox" name="todas">Todas</label> -->
      </div>
      <div style="display: table;width:100%;">
        <?php 
        $sqlFranqueados = "SELECT cod_pizzarias,nome,cidade,estado,dados_extra FROM ipi_pizzarias";
        $dadosFranqueados = mysql_query($sqlFranqueados);
        while ($data = mysql_fetch_assoc($dadosFranqueados)) {
          ?>
        <!-- <label style="margin-right:10px;">
          <input type="checkbox" name="pizzarias[]" value="<?php //echo $data['cod_pizzarias']; ?>"> <?php //echo $data['nome']; ?> - <?php //echo $data['cidade']; ?> - <?php //echo $data['estado']; ?> 
        </label>&nbsp;|&nbsp;
      -->
      <?php
    }
    ?>
    <br>
    <div style="margin: auto;text-align: center;">
      <button id="salvarForm" class="center" style="text-align: center; margin: auto; background: #4CAF50 !important; color: #FFF; border: 0px; padding: 12px; border-radius: 10px;">
        Salvar Alterações
      </button>
    </div>
  </div>
</div>
</div>
<div id="fundoModal" style="display: none;">
</div>
<p>Só pode ser ativo uma janela por vez, logo, ao ativar uma, todas outras serão desativadas.</p>
<table class="listaEdicao" cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <td align="center" width="130">ATIVO/DESATIVADO</td>
      <td align="center" width="130">AVISO</td>
      <td align="center" width="70"></td>
      <td align="center" width="70"><a>VISUALIZAR / EDITAR</a></td>
    </tr>
  </thead>
  <tbody>
    <?php 
    while($d = mysql_fetch_assoc($dados_)){ 
      ?>
      <tr>
        <td align="center" width="130">
          <input class='btnAtivar' data-value="<?php echo $d['id']; ?>" type="range" min="1" max="2" value="<?php echo $d['status']; ?>" />
        </td>
        <td align="center" width="130"><?php $texto = json_decode($d['texto'],true); echo $texto['titulo']; ?></td>
        <td align="center" width="70">
          <?php 
          $data = $d['criado']; 
          $data = explode(' ', $data);
          $data[0] = explode('-',$data[0]);
          $data[0] = array_reverse($data[0]);
          $data[0] = implode('/', $data[0]);
          echo $data[0].' '.$data[1]; 
          ?>
        </td>
        <td align="center" width="70"><span class="json" style="display: none;"><?php echo $d['texto']; ?></span><a id-dado="<?php echo $d['id']; ?>" class="linkVerTexto" style="display: block; width: 99px;padding:10px;border:1px solid #1C4D99;color:#1C4D99;" href='<?php echo $d['id']; ?>'>VISUALIZAR / EDITAR</a></td>
      </tr>    
    <?php } 
    desconectabd($con);
    ?>
  </tbody>
</table>

<script type="text/javascript">

 //Ativar
 const btnAtivar = document.querySelectorAll('.btnAtivar');

 //Form dados
 const idInput = document.querySelector('input[name="id"]');
 const tituloInput = document.querySelector('input[name="titulo"]');
 const paragrafoInput = document.querySelector('input[name="paragrafo"]');
 const botaoInput = document.querySelector('input[name="botao"]');

 //Dados janela modal
 const closeModal = document.querySelector('.closeModal'); 
 const modal = document.querySelector('.modal');
 const formImage = document.querySelector('input[name="imagem"]');
 const frameImage = document.querySelector('img.img-modal');
 const h2 = document.querySelector('.modal .modal-conteudo .texto h2');
 const p = document.querySelector('.modal .modal-conteudo .texto p');

 const botao = document.querySelector('.modal .modal-conteudo .botao #confirmacaoLeitura');
 const salvarForm = document.querySelector('#salvarForm');
 const fundoModal = document.querySelector('#fundoModal');
 const formDados = document.querySelector('form#janelaModalForm');
 const checkboxTodas = document.querySelector('input[name="todas"]');
 const checkboxPizzarias = document.querySelectorAll('input[name="pizzarias[]"]');
 const links = document.querySelectorAll('a.linkVerTexto');
 const ids_pedidosInput = document.querySelector('input[name="ids_pedidos"]');

 var ids_pedidos = "<?php echo $dados['ids_pedidos']; ?>";
 ids_pedidos = ids_pedidos.split(',');
 if(ids_pedidos !== '' && ids_pedidos !== null && ids_pedidos !== undefined){
  if(ids_pedidos.length >1){
    ids_pedidos.forEach(function(v,i){
      v = v.trim();
      //document.querySelector('input[value="'+v+'"]').checked = true;
    });
  }else{
    //document.querySelector('input[value="'+ids_pedidos[0].trim()+'"][name="pizzarias[]"]').checked = true;
  }
}


function lendoConteudo(){
  p.onkeypress = function(){
    let este = this;
    if(este.innerText.indexOf('youtube') != -1){
      let vetor = este.innerText.split(" ");
      for (var i = vetor.length - 1; i >= 0; i--) {
        if(vetor[i].indexOf('youtube') != -1){
          let r = vetor[i].split('/');
          let l = "https://www.youtube.com/embed/"+r[r.length-1];
          vetor[i] = '<iframe height="315" src="'+l+'" style="width:100%;" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        }
      }
      p.innerHTML = vetor.join(' ');
    }
  }
}


function closeModalAcao(){

  closeModal.onclick = function(){
    fundoModal.setAttribute('style','display:none;');
    modal.setAttribute('style','display:none;');
  }
}

function clicaEmLinks(){
  links.forEach(function(v,i){
    v.onclick = function(e){
      e.preventDefault();
      let este = this;
      let href = this.getAttribute('href');
      let conteudo = este.parentElement.querySelector('.json').innerText.trim();
      if(conteudo != ""){
        let json = JSON.parse(este.parentElement.querySelector('.json').innerText.trim());
        h2.innerText = unescape(json['titulo']);
        p.innerHTML = unescape(json['paragrafo']);
        botao.innerText = unescape(json['botao']);
        ids_pedidosInput.innerText = json['ids_pedidos'];
        tituloInput.value = json['titulo'];
        paragrafoInput.value = json['paragrafo'];
        botaoInput.value = json['botao'];
      }else{
        h2.innerText = 'Título';
        p.innerText = 'Clique aqui e escreva o seu texto';
        botao.innerText = 'Entendido!';
        ids_pedidosInput.innerText = '';
        tituloInput.value = 'Título';
        paragrafoInput.value = 'Clique aqui e escreva o seu texto';
        botaoInput.value = 'Entendido!';
      }
      idInput.value = este.getAttribute('id-dado').trim();
      fundoModal.setAttribute('style','display:block;');
      modal.setAttribute('style','display:block;');
    }
  });
}

function checkboxTodasMarca(){
  checkboxTodas.onclick = function(){
    let este = this;
    if(este.checked){
      checkboxPizzarias.forEach(function(v,i){
        v.checked = true;
      });
    }else{
      checkboxPizzarias.forEach(function(v,i){
        v.checked = false;
      });
    }
  }
}


function carregaImg(){
  formImage.onchange = function(e){
    var tg = e.target || window.event.srcElement;
    files = tg.files;
    if(FileReader && files && files.length){
      var Fr = new FileReader();
      Fr.onload = function(){
        frameImage.src = Fr.result;
      }
      Fr.readAsDataURL(files[0]);
    } 
  }
}
function enviaFormulario(){
  salvarForm.onclick = function(){
    let valorBotao = this.innerText;
    salvarForm.setAttribute('disabled',true);
    salvarForm.innerText = 'Cadastrando..';
    formDados.querySelector('input[name="titulo"]').value = escape(h2.innerText.trim());
    formDados.querySelector('input[name="paragrafo"]').value = escape(p.innerHTML.trim());
    formDados.querySelector('input[name="botao"]').value = escape(botao.innerText.trim());
    let dadosPizzarias = [];
    checkboxPizzarias.forEach(function(v,i){
      if(v.checked){
        dadosPizzarias[dadosPizzarias.length] = v.value;
      }
    });
    formDados.querySelector('input[name="ids_pedidos"]').value = dadosPizzarias.join();
    formDados.submit();
    salvarForm.innerText = valorBotao;
  }
}
function clicarAtivarDesativar(){
  btnAtivar.forEach(function(v,i){
    v.onchange = function(){
      let este = this;
      let valor = este.value;
      let id = este.getAttribute('data-value');
      if(valor == '1'){
        btnAtivar.forEach(function(v,i){
          if(v != este){
            v.value = 2;
          }
        });
        xhr = new XMLHttpRequest();
        xhr.open('GET','ipi_cadastrar_mensagens.php?acao=ajax&status='+valor+'&id='+id,true);
        xhr.send();
      }else{
        xhr = new XMLHttpRequest();
        xhr.open('GET','ipi_cadastrar_mensagens.php?acao=ajax&status='+valor+'&id='+id,true);
        xhr.send();
      }
    }
  });
}
clicarAtivarDesativar();
closeModalAcao();
clicaEmLinks();
carregaImg();
enviaFormulario();
lendoConteudo();
//checkboxTodasMarca();
</script>
<? rodape(); ?>
