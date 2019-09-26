<?
require_once 'ipi_req_carrinho_trava_meianoite.php';

$cep_visitante = validar_var_post('cep_visitante');
$buscar_balcao = validar_var_post('buscar_balcao');
$cod_pizzarias = validar_var_post('cod_pizzarias');
$horario = validar_var_post('horario');
$agendar_horario = validar_var_post('agendar_horario');
$registrar_entrega = validar_var_post('registrar_entrega');

/*
echo "<br>cep_visitante: ".$cep_visitante;
echo "<br>buscar_balcao: ".$buscar_balcao;
echo "<br>cod_pizzarias: ".$cod_pizzarias;
echo "<br>horario: ".$horario;
echo "<br>agendar_horario: ".$agendar_horario;
*/

if (!isset($_SESSION['ipi_carrinho']['data_hora_inicial']))
{
  $_SESSION['ipi_carrinho']['data_hora_inicial'] = date("Y-m-d H:i:s");
}

if ($cep_visitante)
{
  $_SESSION['ipi_carrinho']['cep_visitante'] = $cep_visitante;
}

if ($buscar_balcao)
{
  $_SESSION['ipi_carrinho']['buscar_balcao'] = $buscar_balcao;
}

if ($cod_pizzarias)
{
  $_SESSION['ipi_carrinho']['cod_pizzarias'] = $cod_pizzarias;
}

if ( ($horario) && ($horario!="N") )
{
  $_SESSION['ipi_carrinho']['agendar'] = "Sim";
  $_SESSION['ipi_carrinho']['horario'] = $horario;
}

if($horario=="N")
{
  $_SESSION['ipi_carrinho']['agendar'] = "Não";
  $_SESSION['ipi_carrinho']['horario'] = "";
}

if ($registrar_entrega)
{
  $_SESSION['ipi_carrinho']['registrar_entrega'] = $registrar_entrega;
}

//echo "<br>Cep:".$_SESSION['ipi_carrinho']['cep_visitante'];
//echo "<br>CUpom:".$_SESSION['ipi_carrinho']['pergunta_cupom'];



/*
if (isset($_SESSION['ipi_carrinho']['cep_cardapio']))
{
  $_SESSION['ipi_carrinho']['cep_visitante'] = $_SESSION['ipi_carrinho']['cep_cardapio'];
}

echo "<br>Aut: ".$_SESSION['ipi_cliente']['autenticado'];
echo "<br>Cep_card: ".$_SESSION['ipi_carrinho']['cep_cardapio'];
echo "<br>Cep_vis: ".$_SESSION['ipi_carrinho']['cep_visitante'];
echo "<br>balcao: ".$_SESSION['ipi_carrinho']['buscar_balcao'];
*/

if ( (isset($_SESSION['ipi_carrinho']['registrar_entrega'])) && (($_SESSION['ipi_carrinho']['cep_visitante']!="") || ($_SESSION['ipi_cliente']['autenticado'] == true)) )
{
  if ($_SESSION['ipi_carrinho']['combo'])
  {
    echo "<script>window.location='pedido_combo&cod_combos=".$_SESSION['ipi_carrinho']['combo']['id_combo']."'</script>"; 
  }
  else
  {
    if ($_SESSION['ipi_carrinho']['pergunta_cupom'] != "Respondida"	)
    {
      require_once 'ipi_req_carrinho_pedido_codigo_cupom.php';
    }
    else
    {
      require_once 'ipi_req_carrinho_pedido.php';
    }
  }
}
else
{
  require_once 'ipi_req_carrinho_cep.php';
}
?>
