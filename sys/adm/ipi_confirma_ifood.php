<?php

/**
 * ipi_rel_historico_pedidos.php: Histórico de Pedidos
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
$con = conectabd(); 
function verificaSessaoTeste(){
  $result = false;
  $session = $_SESSION['usuario']['cod_pizzarias'];
  foreach($session as $i=>$v){
    if($v == 24){
      $result =  true;
    }
  }
  return $result;
}
$sessaoTeste = verificaSessaoTeste();
if($sessaoTeste){
  #var_dump('asuhasuhas');
}
?>
<br>
<form name="frmBaixa" method="post">
  <table class="listaEdicao" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td align="center" width="70">Cod Pedido</td>
        <td align="center" width="120">Nome Cliente</td>
        <td align="center" width="120">Pizzaria</td>
        <td align="center" width="70">Situação</td>
        <td align="center" width="70">Horário do Pedido</td>
        <td align="center" width="70">Valor Total</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
    </thead>
    <tbody>
      <?php 
      $inicio = 0;
      $final = 20;
      $limit = $inicio.','.$final;
      $sql = "SELECT ipi_clientes.nome AS cliente_nome,ipi_pedidos.cod_pedidos,ipi_pedidos.situacao,ipi_pedidos.data_hora_inicial,ipi_pedidos.valor_total,ipi_pedidos.ifood_polling,ipi_pizzarias.nome AS pizzaria_nome FROM ipi_pedidos 
      INNER JOIN ipi_pizzarias ON (ipi_pedidos.cod_pizzarias = ipi_pizzarias.cod_pizzarias)
      INNER JOIN ipi_clientes ON (ipi_clientes.cod_clientes = ipi_pedidos.cod_clientes)
      WHERE ipi_pedidos.ifood_polling IS NOT NULL
      AND ipi_pedidos.situacao = 'NOVO_IFOOD'
      AND ipi_pedidos.cod_pizzarias IN ('14','22','20')
      ORDER BY cod_pedidos DESC LIMIT $limit;";
      $query = mysql_query($sql);
      while($result = mysql_fetch_object($query)){
       ?>
       <tr>
        <td align="center"><?php echo $result->cod_pedidos; ?></td>
        <td align="center"><?php echo utf8_encode($result->cliente_nome); ?></td>
        <td align="center"><?php echo utf8_encode($result->pizzaria_nome); ?></td>
        <td align="center"><?php echo utf8_encode($result->situacao); ?></td>
        <td align="center">
          <?php 
          $data = $result->data_hora_inicial;
          $data = explode(' ', $result->data_hora_inicial);
          $data[0] = explode('-', $data[0]);
          $data[0] = array_reverse($data[0]);
          $data[0] = implode('/', $data[0]);
          $data = $data[0].' '.$data[1];
          echo $data; 
          ?>
        </td>
        <td align="center"><?php echo $result->valor_total; ?></td>
        <td align="center"><a href="ipi_rel_historico_pedidos.php?p=<?php echo $result->cod_pedidos; ?>">VER PEDIDO</a></td>
        <td align="center"><a class="link_permite" href="p=<?php echo $result->cod_pedidos; ?>">OK</a></td>
        <td align="center"><a target="_blank" href="ipi_caixa.php?p=<?php echo $result->cod_pedidos; ?>&ref_ifood=<?php echo $result->ifood_polling; ?>">Refazer Pedido</a></td>
      </tr>
      <?
    }
    desconectabd($con);
    ?>
  </tbody>
</table>
<script type="text/javascript">
  let url = window.location.href;
  setTimeout(function(){
    window.location  = url;
  },30000);
  const links = document.querySelectorAll('.link_permite');
  function linkPermite(){
    links.forEach(function(v,i){
      v.onclick = function(e){
        e.preventDefault();
        let este = this;
        let params = este.getAttribute('href');
        let xhr = new XMLHttpRequest();
        xhr.open("GET",'https://formulasys.encontresuafranquia.com.br/muda_novo_para_novo.php?'+params,true);
        xhr.send();
        este.parentElement.parentElement.remove();
      }
    });
  }
  linkPermite();
</script>
</form>
<? rodape(); ?>
