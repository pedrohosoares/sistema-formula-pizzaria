<?php
require_once '../lib/php/sessao.php';
require_once '../../bd.php';
?><html slick-uniqueid="4"><head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Formula Pizzaria - Expedição de Pedidos</title>

  <link href="../lib/css/principal.css" media="screen" type="text/css" rel="stylesheet">
  <link type="text/css" rel="stylesheet" href="../../css/autocompleter.css">

  <script type="text/javascript" src="../lib/js/mascara.js"></script>
  <script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>
  <script type="text/javascript" src="../lib/js/tabs.js"></script>
  <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs.css">

  <script type="text/javascript" src="../../js/autocompleter.js"></script>
  <script type="text/javascript" src="../../js/autocompleter.request.js"></script>
  <script type="text/javascript" src="../../js/observer.js"></script>

  <link href="../lib/js/moodialog/css/MooDialog.css" rel="stylesheet" type="text/css" media="screen">
  <script src="../lib/js/moodialog/MooDialog.js" type="text/javascript"></script>
  <script src="../lib/js/moodialog/Overlay.js" type="text/javascript"></script>
  <script src="../lib/js/moodialog/MooDialog.Fx.js" type="text/javascript"></script>
  <script src="../lib/js/moodialog/MooDialog.Alert.js" type="text/javascript"></script>
  <script src="../lib/js/moodialog/MooDialog.Request.js" type="text/javascript"></script>
  <script src="../lib/js/moodialog/MooDialog.Confirm.js" type="text/javascript"></script>
  <script src="../lib/js/moodialog/MooDialog.Prompt.js" type="text/javascript"></script>
  <script src="../lib/js/moodialog/MooDialog.Error.js" type="text/javascript"></script>
  <script type="text/javascript" src="../lib/js/form.js"></script>
  <style>

    body 
    {
      background: ''!important;
    }

  </style>
</head>

<body style="background:none !important" cz-shortcut-listen="true">




  <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/calendario.css">
  <style>

    #frmBaixa td 
    {

      font-size :15pt;

    }
    .botaoAzul{
      cursor:pointer;
    }

  </style>
  <script language="javascript" src="../lib/js/calendario.js"></script>
  <?php
  $get = implode(',', $_GET['cod_pedidos']);
  ?>
  <script type="text/javascript">

    function imprimirNotaFiscal(caminho_danfe,pasta,num_pedido){
      var botao = document.querySelector('button[value="'+num_pedido+'"]');
      botao.innerText = "Imprimindo o Cupom";
      let params = 'acao=imprimir_nota_fiscal&caminho_danfe='+caminho_danfe+'&pasta='+pasta+'&num_pedido='+num_pedido+'&chave=165117047d56ce2487aa718bd8d6c5b7';       
      let url = 'https://formulasys.encontresuafranquia.com.br/despacha.php?'+params;
      var enviaPedido = new XMLHttpRequest();
      enviaPedido.open('GET',url,true);
      enviaPedido.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      enviaPedido.onreadystatechange = function(e) {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText != ''){
            let r = JSON.parse(this.responseText);
            console.log(r);
            if(r.content !== ''){
              botao.innerText = "Cupom impresso com sucesso";
              //remove parametro da página e url 
              document.querySelector("td.p"+num_pedido).parentElement.remove();
              let url = window.location.href;
              novaurl = url.replace('&cod_pedidos[]='+num_pedido,'');
              novaurl = novaurl.replace('cod_pedidos[]='+num_pedido,'');
              window.history.pushState('page2', 'Title', novaurl);
            }else{
              botao.innerText = "Falha ao imprimir cupom";
            }
          }
        }
      }
      enviaPedido.send();
    }
    function chamadaGeracaoImpressaoNotaFiscal(caminho_danfe,pasta,num_pedido,qr_code,status){
      var botao = document.querySelector('button[value="'+num_pedido+'"]');
      let params = 'acao=criaraquivo_nota_fiscal&caminho_danfe='+caminho_danfe+'&pasta='+pasta+'&num_pedido='+num_pedido+'&qr_code='+qr_code+'&status='+status+'&chave=165117047d56ce2487aa718bd8d6c5b7';
      let url = 'https://formulasys.encontresuafranquia.com.br/despacha.php?'+params;
      var enviaPedido = new XMLHttpRequest();
      enviaPedido.open('GET',url,true);
      enviaPedido.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      enviaPedido.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(this.responseText.length > 0){
            let resposta = JSON.parse(this.responseText);
            if(resposta[1]['resposta'] && resposta[1]['resposta'] == 'Done'){
              let dados = resposta[0];
              botao.innerText = "Arquivo Gerado..";
              imprimirNotaFiscal(dados['caminho_danfe'],dados['pasta'],dados['num_pedido']);
            }else{
              botao.innerText = "Falha na Impressão";
            }
          }
        }
      }
      enviaPedido.send();
    }
    function enviaPedidoNota(cod_pedidos){
      var botao = document.querySelector('button[value="'+cod_pedidos+'"]');
      botao.setAttribute('disabled','true');
      botao.innerText = "Enviando Requisição";
      let params = 'acao=gerar_nota_fiscal&cod_pedidos='+cod_pedidos+'&chave=165117047d56ce2487aa718bd8d6c5b7';      
      let url = 'https://formulasys.encontresuafranquia.com.br/despacha.php?'+params;
      var enviaPedido = new XMLHttpRequest();
      enviaPedido.open('GET',url,true);
      enviaPedido.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      enviaPedido.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          if(enviaPedido.responseText !== '' && this.responseText !== 'existe'){
            let nota = JSON.parse(this.responseText);
            if(nota['status'] == 'autorizado'){
              botao.innerText = "Nota Emitida, criando arquivo..";
              chamadaGeracaoImpressaoNotaFiscal(nota['caminho_danfe'],nota['pasta'],nota['num_pedido'],nota['qr_code'],nota['status'],botao);
            }else{
              botao.innerText = "Falha na comunicação. Nota não pode ser criada";
            }
          }else if(this.responseText == 'existe'){
            botao.innerText = "A nota fiscal já foi criada";
          }
        }
      }
      enviaPedido.send();
    }

  </script>



  <form name="frmBaixa" id="frmBaixa" method="post">
    <div id="relacao_despacho"><!-- cod_pizzarias:  -->    <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0" width="100%">
      <thead>
        <tr>
          <td width="50%" style="background-color: #E5EFFD">
            <div style="float:left"><h3>Fórmula Pizzaria NFC-e</h3>

             <div align="center"></div>
           </div>
         </td>
         <td width="50%" style="background-color: #E5EFFD">
            <div style="float:right;">
              <h3 class='ifood'></h3>
           </div>
         </td>
       </tr>
     </thead>
   </table>
   <style type="text/css">
     .botaoAzul{  
      border: 1px solid #000;
      padding: 16px;
      font-size: 20;
      background: aliceblue;
    }
    .botaoAzul:hover{
      background: chartreuse;
    }
  </style>
  <table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%">
    <tbody>
      <tr>
       <td name="td_entregador" width="50%" valign="top" style="vertical-align:top">
         <table class="listaEdicao" cellpadding="0" cellspacing="0" width="100%">
          <tbody>
            <?php
          //bc483fa8600278fc82bdbc5cee62c7ca
            $cor = true;
            if(isset($_GET['cod_pedidos']) and !empty($_GET['cod_pedidos'])){
              $_GET['cod_pedidos'] = array_unique($_GET['cod_pedidos']);
              $valores = implode(',',$_GET['cod_pedidos']);
              $pizzarias = implode(',',$_SESSION['usuario']['cod_pizzarias']);
              $conexao = conectabd();
              $query = 'SELECT pe.tipo_entrega,pe.ifood_polling,pe.cod_pedidos,fp.forma_pg FROM ipi_pedidos pe 
              INNER JOIN ipi_pedidos_formas_pg pa ON (pe.cod_pedidos = pa.cod_pedidos) 
              INNER JOIN ipi_formas_pg fp ON (pa.cod_formas_pg = fp.cod_formas_pg) 
              WHERE  (pe.ref_nota_fiscal IS NULL OR pe.ref_nota_fiscal = "") 
              AND pe.cod_pizzarias IN ('.$pizzarias.') 
              AND pe.cod_pedidos IN ('.$valores.')
              GROUP BY pe.cod_pedidos';
              $db = mysql_query($query);
              while($dados = mysql_fetch_assoc($db)){
                $cor = !$cor;
                ?>
                <tr>
                  <?php 
                  $p = "p".$dados['cod_pedidos']; 
                  $codpedidos = $dados['cod_pedidos'];
                  $formaPg = $dados['forma_pg'];
                  if(!empty($dados['ifood_polling'])){
                    if($dados['tipo_entrega'] == 'Entrega'){
                      $ifood[] = $dados['ifood_polling'];
                    }else{
                      $ifoodBuscar[] = $dados['ifood_polling'];
                    }  
                  }
                  if(!isset($ifoodBuscar)){
                    $ifoodBuscar = array();
                  }
                  if(!isset($ifood)){
                    $ifood = array();
                  }

                  ?>
                  <td class="<?php echo $p; ?>" style="height:35px;background-color:<?php echo ($cor)?'#CCCCCC':'#FFF'; ?>" >
                    <button class="botaoAzul" value="<?php echo $codpedidos; ?>" onclick="enviaPedidoNota('<?php echo $codpedidos; ?>');">Gerar nota e Imprimir </button> 
                    <?php echo $codpedidos; ?> - <?php echo htmlentities($formaPg); ?>
                  </td>
                </tr>
                <?php
              }
              desconectabd($conexao); 
        }
        ?>
      </tbody>
    </table>
  </td>   
</tr>
</tbody>
</table>
<script type="text/javascript">
let url;
let despachoIfood;
    document.querySelector('.ifood').innerText = "Aguarde...Confirmando Pedidos";
    let codPedidosIfood = "<?php echo implode(',',$ifood); ?>";
    url = "https://formulasys.encontresuafranquia.com.br/ifood/dispatch.php?reference="+codPedidosIfood;
    despachoIfood = new XMLHttpRequest();
    despachoIfood.open('GET',url,true);
    despachoIfood.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    despachoIfood.onreadystatechange = function(e) {
      document.querySelector('.ifood').innerText = 'PEDIDOS CONFIRMADOS';
    }
    despachoIfood.send();

    document.querySelector('.ifood').innerText = "Aguarde...Confirmando Pedidos";
    let codPedidosIfoodDelivery = "<?php echo implode(',',$ifoodBuscar); ?>";
    url = "https://formulasys.encontresuafranquia.com.br/ifood/readyToDelivery.php?reference="+codPedidosIfoodDelivery;
    despachoIfood = new XMLHttpRequest();
    despachoIfood.open('GET',url,true);
    despachoIfood.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    despachoIfood.onreadystatechange = function(e) {
          document.querySelector('.ifood').innerText = 'PEDIDOS CONFIRMADOS';
    }
    despachoIfood.send();

</script>
</div>     

</form>
<style type="text/css">
  #preloader{
    position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    background: #FFF;
    text-align: center;
    padding-top: 82px;
    opacity: 0.8;
  }
  #preloader #simbolo{
    border: 2px solid red;
    width: 55px;
    height: 53px;
    display: block;
    border-radius: 50%;
    border-left: 0px;
    margin: auto;
    -webkit-animation:spin 1.4s linear infinite;
    -moz-animation:spin 1.4s linear infinite;
    animation:spin 1.4s linear infinite;
  }
  #preloader p{
    font-size: 29px;
  }
  @-moz-keyframes spin { 100% { -moz-transform: rotate(360deg); } }
  @-webkit-keyframes spin { 100% { -webkit-transform: rotate(360deg); } }
  @keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
</style>
</body>
</html>