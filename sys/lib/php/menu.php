<?php 
require_once '../../bd.php';
require_once '../lib/php/sessao.php'; 
?>

<!-- 
<ul id="barra" class="menubar">
  Exemplo se necessitar adicionar o menu na manualmente
  <li class="menuvertical lado">
    <a href="#">Cadastro</a>
    <ul class="menu">
      <li class="menu"><a href="cad_categorias.php">Categorias</a></li>
      <li class="menu"><a href="index.php">Produtos</a></li>
    </ul>
  </li>
</ul>
-->

<ul id="barra" class="menubar">
<?

// FIXME Alterar para carregar na session.

function imprimeMenuTopo($cod) {
  $SqlBuscaPaginas = "SELECT * FROM nuc_paginas p INNER JOIN nuc_paginas_nuc_perfis pg ON (p.cod_paginas = pg.cod_paginas) WHERE p.cod_paginas_pai = $cod AND p.habilitado = TRUE AND pg.cod_perfis = ".$_SESSION['usuario']['perfil']." ORDER BY ordem, menu";
  $resBuscaPaginas = mysql_query($SqlBuscaPaginas);
  
  while ($objBuscaPaginas = mysql_fetch_object($resBuscaPaginas)) {
    if($objBuscaPaginas->tipo == 'MENU') {
      
      if($objBuscaPaginas->menu != 'Administrativo')
        echo '<li class="menuvertical lado">'."\n";
      else
        echo '<li class="menuvertical">'."\n";
      
      echo '<a href="#">' . htmlentities($objBuscaPaginas->menu) . '</a>'."\n";
      echo '<ul class="menu">'."\n";
    }
    else if($objBuscaPaginas->tipo == 'SUBMENU') {
      echo '<li class="submenu">'."\n";
      echo '<a href="#">' . htmlentities($objBuscaPaginas->menu) . '</a>'."\n";
      echo '<ul>'."\n";
    }
    else {
      echo '<li class="menu"><a href="' . $objBuscaPaginas->arquivo . '">' . htmlentities($objBuscaPaginas->menu) . '</a></li>'."\n";
    }
    
    imprimeMenuTopo($objBuscaPaginas->cod_paginas);
    
    if($objBuscaPaginas->tipo != 'PAGINA') {
      echo '</ul>'."\n";
      echo '</li>'."\n";
    }
  }
}

$con = conectabd();
imprimeMenuTopo(0);
desconectabd($con);

?>
</ul>
