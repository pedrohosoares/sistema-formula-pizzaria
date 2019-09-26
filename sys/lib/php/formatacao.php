<?php
/**
 * Contem todas as rotinas de formatação da área de administração.
 */

require_once '../../config.php';

/*
if ($_SERVER ['SERVER_NAME'] != HOST) {
  cabecalho('Erro de Configuração');
  echo '<center><p style="font-weight: bold; color: red;">Para suporte com este aplicativo contacte a Internet Sistemas pelo site <a href="http://www.internetsistemas.com.br">www.internetsistemas.com.br</a>.</p></center>';
  rodape();
  
  die();
}
*/

require_once 'sessao.php';
// Monta cabeçalho
function cabecalho($titulo = '', $botao = array()) {
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt_br" lang="pt_br">';
  echo '<head>';
  echo '<title>'.NOME_SITE.' | Painel de Administração</title>';
  //echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>';
  echo '<meta name="author" content="Internet Sistemas http://www.internetsistemas.com.br"/>';
  echo '<meta name="copyright" content="Copyright (c) '.date('Y'). ' Internet Sistemas. Todos os direitos reservados."/>';
  echo '<meta name="description" content="Painel de Administração do Site"/>';
  echo '<meta name="keywords" content=""/>';
  echo '<meta name="robots" content="noindex, nofollow"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/menu.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/validacao.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/datepicker_vista/datepicker_vista.css" />';
  echo '<link rel="shortcut icon" type="image/x-icon" href="../lib/img/principal/icone.png"/>';
  
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-more.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/form.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mascara.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/tabs.js"></script>';
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
      top: 121px;
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
  <?php
  echo '</head>';
  echo '<body>';
  
  echo '<div id="logo">';
  echo '<div id="nome_site"><a href="index.php" title="Voltar a Página Inicial"><img src="../lib/img/principal/logo_cliente.png" align="middle" border="0"></a>&nbsp;Sistema Administrativo</div>';
  
  if ($_SESSION['usuario']['autenticado']) 
  {
    echo '<div id="infos">';
    echo '<table cellpadding="0" cellspacing="0"><tr>';
    echo '<td><img src="../lib/img/principal/infos.gif"></td>';
    echo '<td valign="top" style="padding-top: 5px;">'.htmlentities('Usuário').': '.$_SESSION['usuario']['usuario'].'</td>';
    echo '<td valign="top" style="padding-top: 5px;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td>';
    echo '<td valign="top" style="padding-top: 5px;"><a href="index.php">Home</a></td>';
    echo '<td valign="top" style="padding-top: 5px;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td>';
    //echo '<td valign="top" style="padding-top: 5px;"><a href="#">Ajuda</a></td>';
    //echo '<td valign="top" style="padding-top: 5px;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td>';
    //echo '<td valign="top" style="padding-top: 5px;"><a href="http://www.internetsistemas.com.br/suporte" target="_blank">Suporte</a></td>';
    //echo '<td valign="top" style="padding-top: 5px;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td>';
    echo '<td valign="top" style="padding-top: 5px;"><a href="nuc_logout.php">Sair</a></td>';
    echo '</tr></table>';
    echo '</div>';
  }
  
  echo '</div>';
  
  echo '<div id="pagina">';
  echo '<div id="cabecalho">';
  
  if ($_SESSION['usuario']['autenticado']) {
    echo '<script type="text/javascript" src="../lib/js/menu.js"></script>';
    
    echo '<div id="menu">';
    require_once 'menu.php';
    echo '</div>';
  }
  
  echo '</div>';
  
  if ($titulo) 
  {
    echo '<div id="caixa">';
    echo '<div id="titulo"><h1>'.$titulo.'</h1></div>';
    
    if (count($botao) > 0) {
      echo '<div id="botao">';
      
      foreach($botao as $botaoId => $botaoImg) {
        echo '<a class="caixa_botao" id="'.$botaoId.'" href="javascript:;"><img src="'.$botaoImg.'"/></a>';
      }
      
      echo '</div>';
    }
    
    echo '</div>';
  }
  
  echo '<div id="conteudo">';
  
  ?>
  <script>
    window.addEvent('load', function() {
  //window.addEvent('domready', function() {
    // Define automaticamente a altura do rodape e coloca uma barra de rolagem no conteudo.
    $('conteudo').setStyle('overflow', 'auto');
    $('conteudo').setStyle('height', $(document.body).getCoordinates().height - $('logo').getCoordinates().height - $('cabecalho').getCoordinates().height - $('caixa').getCoordinates().height - $('rodape').getCoordinates().height - 30);
  });
</script>
<?
}

// Monta cabeçalho para pagina popup
function cabecalho_popup($titulo = '') {
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt_br" lang="pt_br">';
  echo '<head>';
  echo '<title>'.NOME_SITE.' | Painel de Administração</title>';
  echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>';
  echo '<meta name="author" content="Internet Sistemas http://www.internetsistemas.com.br"/>';
  echo '<meta name="copyright" content="Copyright (c) '.date('Y'). ' Internet Sistemas. Todos os direitos reservados."/>';
  echo '<meta name="description" content="Painel de Administração do Site"/>';
  echo '<meta name="keywords" content=""/>';
  echo '<meta name="robots" content="noindex, nofollow"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/menu.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/validacao.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/datepicker_vista/datepicker_vista.css" />';
  
  echo '<link rel="shortcut icon" type="image/x-icon" href="../lib/img/principal/icone.png"/>';
  
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-more.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/form.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mascara.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/tabs.js"></script>';
  
  echo '</head>';
  echo '<body>';
  
  echo '<div id="logo">';
  echo '<div id="nome_site">'.NOME_SITE.'</div>';
  echo '</div>';
  
  echo '<div id="pagina">';
  
  if ($titulo) {
    echo '<div id="caixa">';
    echo '<div id="titulo"><h1>'.$titulo.'</h1></div>';
    echo '</div>';
  }
  
  echo '<div id="conteudo">';
  
  ?>
  <script>
    window.addEvent('load', function() {
  //window.addEvent('domready', function() {
    // Define automaticamente a altura do rodape e coloca uma barra de rolagem no conteudo.
    $('conteudo').setStyle('overflow', 'auto');
    $('conteudo').setStyle('height', $(document.body).getCoordinates().height - $('logo').getCoordinates().height - $('caixa').getCoordinates().height - 20);
  });
</script>
<?
}



// Monta cabeçalho para pagina relatorio
function cabecalho_relatorio($titulo = '') 
{
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt_br" lang="pt_br">';
  echo '<head>';
  echo '<title>'.NOME_SITE.' | Painel de Administração</title>';
  echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>';
  echo '<meta name="author" content="Internet Sistemas http://www.internetsistemas.com.br"/>';
  echo '<meta name="copyright" content="Copyright (c) '.date('Y'). ' Internet Sistemas. Todos os direitos reservados."/>';
  echo '<meta name="description" content="Painel de Administração do Site"/>';
  echo '<meta name="keywords" content=""/>';
  echo '<meta name="robots" content="noindex, nofollow"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/impressao.css"/>';

/*
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/menu.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/validacao.css"/>';
  
  echo '<link rel="shortcut icon" type="image/x-icon" href="../lib/img/principal/icone.png"/>';
  
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-more.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/form.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mascara.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/tabs.js"></script>';
*/
  echo '</head>';
  echo '<body style="margin: 0px">';


  echo '<table border="0" width="800">';

  echo '<tr>';
  echo '<td align="center">';
  echo '<img src="../lib/img/principal/logo_cliente.png" align="middle">&nbsp;<br>'.NOME_SITE;
  echo '</td>';
  echo '<td align="center">';
  if ($titulo)
  {
    echo "<h1>".$titulo."</h1>";
  }

  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="2">';
  echo '<hr noshade="noshade" size="1">';

}


// Monta Rodapé
function rodape() {
  $idsLogados = $_SESSION['usuario']['cod_pizzarias'];
  $idsSerializados = implode(',', $idsLogados);
  $c = conectabd();
  if(!empty($idsLogados) and isset($idsLogados)){
    $sql = "SELECT * FROM ipi_mensagens WHERE status='1' LIMIT 1";
    $query = mysql_query($sql);
    $ob = mysql_fetch_assoc($query);
    $idsVisualizaram = (!empty($ob['visualizados']))?json_decode($ob['visualizados'],true):array();
    $visualizou = true;
    if(!empty($idsVisualizaram)){
      foreach ($idsVisualizaram as $key => $id) {
        foreach ($idsLogados as $key => $idLogado) {
          if($idLogado != $id and $visualizou == true){
            $visualizou = false;
          }
        }
      }
    }else{
      $visualizou = false;
    }
    $idsVisualizaram = implode(',', $idsVisualizaram);
    ?>
    <script type="text/javascript">
      console.log("<?php echo $idsSerializados; ?>");
      console.log("<?php echo $idsVisualizaram; ?>");
      console.log("<?php echo $visualizou; ?>");
    </script>
    <?php
    $visualizou = true;
    $dados = !empty($ob['texto'])?json_decode($ob['texto'],true):"";
    $visualizou == true;
    if($visualizou == false and !empty($dados)){
      ?>
      <div class="modal" style="display:block;">
        <div class="modal-conteudo">
          <div class="floatleft">
          </div>
          <div class="texto">
            <h2><?php echo utf8_encode(urldecode($dados['titulo'])); ?></h2>
            <p><?php echo utf8_encode(urldecode($dados['paragrafo'])); ?></p>
          </div>
          <div class="botao">
            <button style="margin-top: 10px;" id="confirmacaoLeitura" class="center"><?php utf8_encode(urldecode($dados['botao'])); ?></button>
          </div>
        </div>
      </div>
      <div id="fundoModal" style="display:block;"></div>
      <script type="text/javascript">
        const confirma = document.querySelector('#confirmacaoLeitura');
        const modal = document.querySelector('.modal');
        const fundo = document.querySelector('#fundoModal');
        function confirmacaoLeitura(){
          confirma.onclick = function(){
            fundo.setAttribute('style','display:none;');
            modal.setAttribute('style','display:none;');
            let xhr = new XMLHttpRequest();
            xhr.open('GET','ipi_cadastrar_mensagens_ajax.php?acao=confirmar&ids='+"<?php echo $idsSerializados; ?>",true);
            xhr.send();
          }
        }
        confirmacaoLeitura();
      </script>
      <?php
    }
  }
  desconectabd($c);
  echo '</div>';
  echo '</div>';

  echo '<div id="rodape">';
  echo '<table cellpadding="0" cellspacing="0" width="100%"><tr>';
  echo '<td style="padding-left: 10px;"></td>';
  echo '<td text-align="center" valign="top" width="180" style="padding-top: 3px;"><small>'.htmlentities("Sistema de Administração Integrado").'</small></td>';
  echo '<td text-align="center" valign="top" width="80" style="padding-top: 3px;"><small>'.htmlentities("Versão").VERSAO.'</small></td>';
  echo '</tr></table>';
  echo '</div>';

  echo '</body>';
  echo '</html>';
}

// Monta Rodapé para popup
function rodape_popup() {
  echo '</div>';
  echo '</div>';

  echo '</body>';
  echo '</html>';
}


// Monta Rodapé para popup
function rodape_relatorio() 
{
  echo '</td>';
  echo '</tr>';

  echo '<tr>';
  echo '<td colspan="2">';

  echo '<hr noshade="noshade" size="1">';
  echo '<table cellpadding="0" cellspacing="0" width="100%" border="0">';
  echo '<tr>';
  echo '<td style="padding-left: 10px;"><a href="http://www.internetsistemas.com.br" title="Internet Sistemas" target="_blank">Internet Sistemas</a></td>';
  echo '<td text-align="center" valign="top" width="180" style="padding-top: 3px;"><small>Sistema de Administração Integrado</small></td>';
  echo '<td text-align="center" valign="top" width="80" style="padding-top: 3px;"><small>Versão '.VERSAO.'</small></td>';
  echo '</tr>';
  echo '</table>';

  echo '</td>';
  echo '</tr>';

  echo '</table>';

  echo '</body>';
  echo '</html>';
}


/**
 * Monta o cabeçalho da área de administração.
 *
 * @param string $titulo Título da página atual
 * @param array $botao Botão superior ao menu (opcional)
 */
function exibir_cabecalho ($titulo = '', $botao = array())
{
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt_br" lang="pt_br">';
  echo '<head>';
  echo '<title>' . NOME_SITE . ' | Painel de Administração</title>';
  echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>';
  echo '<meta name="author" content="Internet Sistemas http://www.internetsistemas.com.br"/>';
  echo '<meta name="copyright" content="Copyright (c) ' . date('Y') . ' Internet Sistemas. Todos os direitos reservados."/>';
  echo '<meta name="description" content="Painel de Administração do Site"/>';
  echo '<meta name="keywords" content=""/>';
  echo '<meta name="robots" content="noindex, nofollow"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css"/>';

  echo '<!--[if lt IE 7]>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal_ie6.css"/>';
  echo '<![endif]-->';

  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/menu.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/validacao.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/datepicker_vista/datepicker_vista.css" />';

  echo '<link rel="shortcut icon" type="image/x-icon" href="../lib/img/principal/icone.png"/>';

  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-more.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/form.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mascara.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/tabs.js"></script>';

  echo '</head>';
  echo '<body>';

  echo '<div id="logo">';
  echo '<div id="nome_site"><a href="index.php" title="Voltar a Página Inicial"><img src="../lib/img/principal/logo_cliente.png" align="middle" border="0"></a>&nbsp;Sistema Administrativo</div>';

  if ($_SESSION['usuario']['autenticado'])
  {
    echo '<div id="infos">';
    echo '<table cellpadding="0" cellspacing="0"><tr>';
    echo '<td><img src="../lib/img/principal/infos.gif"></td>';
    echo '<td valign="top" style="padding-top: 5px;">Usuário: ' . $_SESSION['usuario']['usuario'] . '</td>';
    echo '<td valign="top" style="padding-top: 5px;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td>';
        //echo '<td valign="top" style="padding-top: 5px;"><a href="#">Ajuda</a></td>';
        //echo '<td valign="top" style="padding-top: 5px;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td>';
        //echo '<td valign="top" style="padding-top: 5px;"><a href="http://www.internetsistemas.com.br/suporte" target="_blank">Suporte</a></td>';
        //echo '<td valign="top" style="padding-top: 5px;">&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;</td>';
    echo '<td valign="top" style="padding-top: 5px;"><a href="nuc_logout.php">Sair</a></td>';
    echo '</tr></table>';
    echo '</div>';
  }

  echo '</div>';

  echo '<div id="pagina">';
  echo '<div id="cabecalho">';
  if ($_SESSION['usuario']['autenticado'])
  {
    echo '<script type="text/javascript" src="../lib/js/menu.js"></script>';

    echo '<div id="menu">';
    require_once 'menu.php';
    echo '</div>';
  }

  echo '</div>';

  if ($titulo)
  {
    echo '<div id="caixa">';
    echo '<div id="titulo"><h1>' . $titulo . '</h1></div>';

    if (count($botao) > 0)
    {
      echo '<div id="botao">';

      foreach ($botao as $botaoId => $botaoImg)
      {
        echo '<a class="caixa_botao" id="' . $botaoId . '" href="javascript:;"><img src="' . $botaoImg . '"/></a>';
      }

      echo '</div>';
    }

    echo '</div>';
  }

  echo '<div id="conteudo">';

  ?>
  <script>
    window.addEvent('load', function() {
  //window.addEvent('domready', function() {
    // Define automaticamente a altura do rodape e coloca uma barra de rolagem no conteudo.
    $('conteudo').setStyle('overflow', 'auto');
    $('conteudo').setStyle('height', $(document.body).getCoordinates().height - $('logo').getCoordinates().height - $('cabecalho').getCoordinates().height - $('caixa').getCoordinates().height - $('rodape').getCoordinates().height - 20);
  });
</script>
<?
}

/**
 * Monta o cabeçalho para pagina popup.
 *
 * @param string $titulo Título da página atual
 */
function exibir_cabecalho_popup ($titulo = '')
{
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
  echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pt_br" lang="pt_br">';
  echo '<head>';
  echo '<title>' . NOME_SITE . ' | Painel de Administração</title>';
  echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>';
  echo '<meta name="author" content="Internet Sistemas http://www.internetsistemas.com.br"/>';
  echo '<meta name="copyright" content="Copyright (c) ' . date('Y') . ' Internet Sistemas. Todos os direitos reservados."/>';
  echo '<meta name="description" content="Painel de Administração do Site"/>';
  echo '<meta name="keywords" content=""/>';
  echo '<meta name="robots" content="noindex, nofollow"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/principal.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/menu.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/validacao.css"/>';
  echo '<link rel="stylesheet" type="text/css" media="screen" href="../lib/css/datepicker_vista/datepicker_vista.css" />';

  echo '<link rel="shortcut icon" type="image/x-icon" href="../lib/img/principal/icone.png"/>';

  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-core.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mootools-1.2-more.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/form.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/mascara.js"></script>';
  echo '<script type="text/javascript" src="../lib/js/tabs.js"></script>';

  echo '</head>';
  echo '<body>';

  echo '<div id="logo">';
  echo '<div id="nome_site">' . NOME_SITE . '</div>';
  echo '</div>';

  echo '<div id="pagina">';

  if ($titulo)
  {
    echo '<div id="caixa">';
    echo '<div id="titulo"><h1>' . $titulo . '</h1></div>';
    echo '</div>';
  }

  echo '<div id="conteudo">';

  ?>
  <script>
    window.addEvent('load', function() {
  //window.addEvent('domready', function() {
    // Define automaticamente a altura do rodape e coloca uma barra de rolagem no conteudo.
    $('conteudo').setStyle('overflow', 'auto');
    $('conteudo').setStyle('height', $(document.body).getCoordinates().height - $('logo').getCoordinates().height - $('caixa').getCoordinates().height - 20);
  });
</script>
<?
}

/**
 * Monta o rodapé da área de administração.
 */
function exibir_rodape ()
{
  echo '</div>';
  echo '</div>';

  echo '<div id="rodape">';
  echo '<table cellpadding="0" cellspacing="0" width="100%"><tr>';
  echo '<td style="padding-left: 10px;"><a href="http://www.internetsistemas.com.br" target="_blank"><img src="../lib/img/principal/logo-rodape.gif"></a></td>';
  echo '<td text-align="center" valign="top" width="180" style="padding-top: 3px;"><small>Sistema de Administração Integrado</small></td>';
  echo '<td text-align="center" valign="top" width="80" style="padding-top: 3px;"><small>Versão ' . VERSAO . '</small></td>';
  echo '</tr></table>';
  echo '</div>';

  echo '</body>';
  echo '</html>';
}

/**
 * Monta o rodapé para pagina popup.
 */
function exibir_rodape_popup ()
{
  echo '</div>';
  echo '</div>';

  echo '</body>';
  echo '</html>';
}

?>
