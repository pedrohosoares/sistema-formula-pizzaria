        <div id="fb-root"></div>
        <script>
          window.fbAsyncInit = function() {
            FB.init({
              appId      : '264264503687010', // App ID
              channelUrl : '//www.osmuzzarellas.com.br/sitenovo/channel.html', // Channel File
              status     : false, // check login status
              cookie     : true, // enable cookies to allow the server to access the session
              xfbml      : true  // parse XFBML
            });
            
                    // listen for and handle auth.statusChange events
        
        /*FB.Event.subscribe('auth.login', function(response) {
          alert('logou'); 
          var form = $('<form />').attr({'action': 'ipi_login.php','method': 'post','method': 'post'});
          var input =$('<input type="text">').attr({'name': 'como','value': 'facebook'});
          //var input2= $('<input type="text">').attr({'name': 'f','value': fundo});

          form.append(input);
          //form.append(input2);
          $(document.body).append(form);
          form.submit();

          //window.location('ipi_login.php');
        });*/
                  
           FB.Event.subscribe('auth.statusChange', function(response) {
          if (response.authResponse) {
            // user has auth'd your app and is logged into Facebook
           //  alert('logou'); 
             var form = $('<form />').attr({'action': 'ipi_login.php','method': 'post','method': 'post'});
          var input =$('<input type="text">').attr({'name': 'como','value': 'facebook'});
          //var input2= $('<input type="text">').attr({'name': 'f','value': fundo});

          form.append(input);
          //form.append(input2);
          $(document.body).append(form);
          form.submit();
          } else {
            // user has not auth'd your app, or is not logged into Facebook
           // alert('nnnnnnnnnnnlogou'); 
          }
        });

          };


          // Load the SDK Asynchronously
          (function(d){
             var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
             if (d.getElementById(id)) {return;}
             js = d.createElement('script'); js.id = id; js.async = true;
             js.src = "//connect.facebook.net/pt_BR/all.js";
             ref.parentNode.insertBefore(js, ref);
           }(document));
        </script>

<? 
if(!$facebook)
{
  require 'classe/facebook.php';

  $facebook = new Facebook(array(
  'appId'  => '264264503687010',
  'secret' => '56d2a493b367a020f407beb1e9d33b40',
));
}
function imprimeFormLogin1() {
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
  <form id="frmLogin" method="post" action="ipi_login.php" onsubmit="return validarLogin(this);">

    <div>
      <label for="email"></label>
      <input name="email" id="email" type="text" class="form_text3 email_cadastro" placeholder="E-mail:" />

      <label for="senha"></label>
      <input name="senha" id="senha" type="password" class="form_text3" placeholder="Senha:" />

       <div class="links_login">
        <a href="cadastro">N&atilde;o sou cadastrado</a>
        <a href="esqueci_minha_senha">Esqueci minha senha</a>       
      </div>

      <div id="botao_entrar">
        <input type="submit" value="Entrar" alt="Botão verificar email e senha!" class="btn btn-success" />
         <!-- <div class="fb-login-button" scope="email,user_checkins,user_birthday">Login com Facebook</div> -->
         <div class="fb-login-button" data-size="large" scope="email,user_checkins,user_birthday" data-show-faces="false" data-auto-logout-link="false">Login com Facebook</div>
      </div>

     

      <input type="hidden" name="origem" value="1" />
    </div>

  </form>
  <?
}

$erro = $_GET['erro'];

$user = $facebook->getUser();
if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // error_log($e);
    $user = null;
  }
}

// Login or logout url will be needed depending on current user state.
$params = array(
  'scope' => 'email, user_checkins',
  'redirect_uri' => 'https://www.osmuzzarellas.com.br/sitenovo/meu_home'
);
/*
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl($params);
}
?>
 <? if ($user): ?>
      <a href="<? echo $logoutUrl; ?>">Logout</a>
    <? else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<? echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <? endif ?>


    <? if ($user): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">

      <h3>Your User Object (/me)</h3>
      <pre><? print_r($user_profile); ?></pre>
    <? else: ?>
      <strong><em>You are not Connected.</em></strong>
    <? endif ?>

    <?*/


if(($erro != 1)&&($erro != 2))
{
  if($_SESSION['ipi_cliente']['autenticado']) 
  {
    echo '<div id="menu_rodape_logado">';

    $nome = explode(' ', $_SESSION['ipi_cliente']['nome']);
    echo '<div class="cor_cinza1">Você está logado como:</div><br />';
    echo '<strong> '.$nome[0].'</strong>';
    echo '<br /><br/><a href="meu_home" title="Acessar sua área restrita!" class="btn btn-primary btn_logado">Área Restrita</a>';
    echo '<a href="ipi_logout.php" title="Sair do sistema, você está logado como '.$nome[0].'" class="btn btn-primary btn_logado">Sair</a>';
    echo '</div>';
  }
  else 
  {
    imprimeFormLogin1();
  }
} 
else 
{
  imprimeFormLogin1();
  if ($erro==1)  
    echo '<script type="text/javascript">alert("E-mail ou senha incorretos, por favor, tente novamente."); $("#email").focus();</script>';
  elseif ($erro==2)
    echo '<script type="text/javascript">alert("Faça o login e tente novamente ;)"); $("#email").focus();</script>';
}
?>
