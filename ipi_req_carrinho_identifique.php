<?
require_once 'ipi_req_carrinho_trava_meianoite.php';
?>
  <script type="text/javascript">
  function validarLogin(frm)
  {
  
    if (frm.email.value=="")
    {
      alert("E-mail campo obrigatório!");
      frm.email.focus();
      return(false);
    }

    if (frm.senha.value=="")
    {
      alert("Senha campo obrigatório!");
      frm.senha.focus();
      return(false);
    }

    if (!validarEmail(frm.email.value))
    {
      alert("E-mail inválido para login!");
      frm.email.focus();
      return(false);
    }


    return(true);
  }
  </script>
  <?  if(!$_SESSION['ipi_cliente']['autenticado']): ?>
<form id="frmLogin" method="post" action="ipi_login.php" onsubmit="return validarLogin(this);">

    <div style="max-width:286px">
      <label for="email"></label>
      <input name="email" id="email" type="text" placeholder="E-mail:"  class="identifique_btn" />

      <label for="senha"></label>
      <input name="senha" id="senha" type="password" placeholder="Senha:"  class="identifique_btn"  />

       <div class="links_login">
        <a href="cadastro" class="identifique_link">N&atilde;o sou cadastrado</a>
        <a href="esqueci_minha_senha"  class="identifique_link">Esqueci minha senha</a>       
      </div>

      <div id="botao_entrar">
        <input type="submit" value="Entrar" alt="Botão verificar email e senha!" class="btn btn-success btn_entrar"  />
         <!-- <div class="fb-login-button" scope="email,user_checkins,user_birthday">Login com Facebook</div> -->
         <div class="fb-login-button" data-size="large" scope="email,user_checkins,user_birthday" data-show-faces="false" data-auto-logout-link="false">Login com Facebook</div>
      </div>

     

      <input type="hidden" name="origem" value="2" />
    </div>

  </form>
  <?
  else:
   echo ('Depois de autenticado não é mais necessário executar esta ação.');
 echo '<br/><br/><a class="btn btn-primary" href="algo_mais">Algo Mais</a>&nbsp;<a class="btn btn-primary" href="pagamentos">Finalizar pedido</a>';
  endif;
  ?>