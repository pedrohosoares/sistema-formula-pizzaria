<?
require_once 'bd.php';
require_once 'ipi_session.php';
require_once 'sys/lib/php/formulario.php';
require_once 'classe/pedido.php';
require_once 'ipi_req_carrinho_classe.php';
?>
<h2>Área restrita</h2>
<!--   <p>Seja bem-vindo(a) ao sistema de pedidos pela internet!<br/>
      Vamos verificar a disponibilidade para entregar em seu bairro:</p> -->
  <div style='height:auto'>

  <div id='container_home_client'>
    <div class='top_box'>
      <?
        $arr_nome = explode(' ', $_SESSION[ipi_cliente][nome]);  
        echo "<h3> Olá, ".$arr_nome[0]."! </h3>";
      ?>
      <span class='float_right'> 
      <?
        $obj_pontos = executar_busca_simples('SELECT SUM(pontos) as total FROM ipi_fidelidade_clientes where cod_clientes = '.$_SESSION['ipi_cliente']['codigo']);  
        if($obj_pontos->total != NULL || $obj_pontos->total != 0)
        {
          echo "<p>Você tem ".$obj_pontos->total." PONTOS <br />";
          echo "<a href='usar_fidelidade' title='Use agora seus pontos!'> Usar os Pontos</a>";
        }
        else
        {
          echo "<p>Você tem 0 PONTOS </p> <br />";
        }
      ?>
      </span>
    </div>

    <div class='middle_box'>
      <div class='t'> </div>
        <div > 
          <?
          if($_SESSION['ipi_carrinho']['pedido'] || $_SESSION['ipi_carrinho']['bebida'])
          {
            echo "O pedido abaixo está só aguardando sua finalização.";        
            $carrinho = new ipi_carrinho();
            $carrinho->exibir_resumo_pedido();
            echo '<br/>';
            echo "<input type='button' value='Finalizar meu pedido' class='bt_pedido' onclick='location.href=\"pagamentos\"'>";
            //echo "<a class='float_right' href='pagamentos' title='Finalizar o seu pedido!'> Finalizar meu pedido </a>";
            echo "<div class='clear'></div>";
          }
          else
          {
            ?>     
            Não há nenhum pedido em andamento.
            
            <a class='pedir_algo float_right' href='pedidos' title='Peça algo agora mesmo!'> Peça Algo Agora</a>
            <div class='clear'></div>
            <? 
          } 
          ?>
        </div>
      <div class='b'></div>
    </div>
    <br/><br/>
  		<a class="btn btn-primary" href="alterar_senha">Alterar Senha</a>
      <a class="btn btn-primary" href="meus_dados">Meus Dados</a>
      <a class="btn btn-primary" href="meus_enderecos">Meus Endereços</a>
      <a class="btn btn-primary" href="meus_pedidos">Meus Pedidos</a>
  </div>
</div>
