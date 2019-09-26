<?
require_once '../config.php';
require_once '../bd.php';
//require_once '../ipi_email.php';
//require_once '../classe/cupom.php';

$con = conectabd();

$limite_envio = 625; // Fazer a conta do limite atual dividido por 24horas dividido por 6 (o disparo na dreamhost de 10 em 10 min, dá 6 por hora)
$taxa_emails_por_segundo = 22; // Velocidade máxima por segundo do envio ver limite na aws, deixar folga para os envios do site.

$time = microtime();
$time = explode(" ", $time);
$time = $time[1] + $time[0];
$start = $time;

$quantidade_por_unidade = 100;
/*
SELECT c.nome, c.email, (SELECT count(cod_clientes) cont_pedidos FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes) cont_ped
FROM ipi_clientes c 
WHERE 
(SELECT count(cod_clientes) cont_pedidos FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes) > 5
AND
(SELECT DATE_SUB(NOW(), INTERVAL 90 DAY) dt_ant, data_hora_pedido FROM ipi_pedidos p WHERE p.cod_clientes = 1 AND DATE_SUB(NOW(), INTERVAL 90 DAY) < p.data_hora_pedido  ORDER BY cod_pedidos DESC LIMIT 1) > NOW()
AND 
c.email<>"" AND  c.nome<>""

ORDER BY cont_ped DESC
LIMIT 10


======================

SELECT c.nome, c.email, (SELECT count(cod_clientes) cont_pedidos FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes) cont_ped
FROM ipi_clientes c 
WHERE 
(SELECT count(cod_clientes) cont_pedidos FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes) > 5
AND
(SELECT data_hora_pedido FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes AND DATE_SUB(NOW(), INTERVAL 90 DAY) < p.data_hora_pedido  ORDER BY cod_pedidos DESC LIMIT 1) != ""
AND 
c.email<>"" AND  c.nome<>""

ORDER BY cont_ped DESC
LIMIT 3



======================

SELECT DATE_SUB(NOW(), INTERVAL 90 DAY) dt_ant, data_hora_pedido FROM ipi_pedidos p WHERE p.cod_clientes = 1 AND DATE_SUB(NOW(), INTERVAL 90 DAY) < p.data_hora_pedido  ORDER BY cod_pedidos DESC LIMIT 1

*/

//49599 < - cod_clientes teste ultima compra em janeiro

$sql_buscar_pizzarias = "SELECT cod_pizzarias from ipi_pizzarias where  situacao = 'ATIVO' order by cod_pizzarias";
$res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);
while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
{
  echo "<h1>".$obj_buscar_pizzarias->cod_pizzarias."</h1>";

  $sql_buscar_clientes = '
  SELECT c.cod_clientes, c.nome, c.email, 
  (SELECT count(cod_clientes) cont_pedidos FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes and p.cod_pizzarias = "'.$obj_buscar_pizzarias->cod_pizzarias.'") cont_ped,

  (SELECT COUNT(cod_pedidos) FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes and p.cod_pizzarias = "'.$obj_buscar_pizzarias->cod_pizzarias.'" AND DATE_SUB(NOW(), INTERVAL 40 DAY) < p.data_hora_pedido
   ORDER BY p.cod_pedidos DESC LIMIT 1) cont_ped_filtro,

  (SELECT data_hora_pedido FROM ipi_pedidos p WHERE p.cod_clientes = c.cod_clientes ORDER BY cod_pedidos DESC LIMIT 1) data_ultimo,

  (SELECT count(ea.cod_clientes) FROM ipi_email_automatico ea
  WHERE ea.cod_clientes = c.cod_clientes AND ea.tipo_email="CLIENTE_INATIVO_REGRA01" AND ea.data_hora_envio > DATE_SUB(NOW(), INTERVAL 90 DAY)) qtd_enviados

  FROM ipi_clientes c 

  WHERE 
  c.email<>"" AND c.nome<>"" 
  
  AND
  c.cod_clientes NOT IN (

  SELECT ea.cod_clientes FROM ipi_email_automatico ea
  WHERE ea.cod_clientes = c.cod_clientes AND ea.tipo_email="CLIENTE_INATIVO_REGRA01" AND ea.data_hora_envio > DATE_SUB(NOW(), INTERVAL 90 DAY)

  )

  HAVING cont_ped_filtro = 0 
  ORDER BY cont_ped DESC LIMIT '.$quantidade_por_unidade.' 

  
  ';
/*  


  

  */
  
/*  if (date("d")%2==0)
  {
    $cod_clientes_dia = "1";
  }
  else
  {
    $cod_clientes_dia = "56131";
  }


  $sql_buscar_clientes = 'SELECT c.cod_clientes, c.nome, c.email FROM ipi_clientes c WHERE c.cod_clientes IN ('.$cod_clientes_dia.') AND
  c.cod_clientes NOT IN (
  SELECT ea.cod_clientes FROM ipi_email_automatico ea
  WHERE ea.cod_clientes = c.cod_clientes AND ea.tipo_email="CLIENTE_INATIVO_REGRA01" AND ea.data_hora_envio > DATE_SUB(NOW(), INTERVAL 90 DAY)
  )';*/
  $res_buscar_clientes = mysql_query($sql_buscar_clientes);
  //echo "<br>1: ".$sql_buscar_clientes;

  for ($i = 0; $i < $limite_envio; $i++)
  {
    if ($obj_buscar_clientes = mysql_fetch_object($res_buscar_clientes))
    {
      if($obj_buscar_clientes->cont_ped_filtro==0 && $obj_buscar_clientes->qtd_enviados==0)
      {
        //$email_destino = $obj_buscar_clientes->email;

        //$email_origem = EMAIL_PRINCIPAL;
        //$assunto = NOME_SITE. " - Sentimos a sua falta";

        //$nome = explode(' ', $obj_buscar_clientes->nome);

        //$cupom = new Cupom();

        //$cod_cupons = $cupom->inserir_cupom($data_validade, $produto, $cod_produtos, $cod_tamanhos, $promocao, $necessita_compra, $valor_minimo_compra, $cod_usuario, $generico, $obs_cupom, $cod_pizzarias);

        // $timestamp = strtotime('+30 days');
        // $data_validade = date('Y-m-d', $timestamp);
        // $cod_cupons = $cupom->inserir_cupom(date("Y-m-d"),$data_validade, 'PIZZA', 0, 4, 1, '1', '15.00', NULL, '1', 'Gerado Automático - Cliente Inativo', $obj_buscar_pizzarias->cod_pizzarias, $obj_buscar_clientes->cod_clientes);
        // $numero_cupom = $cupom->consultar_numero_cupom_pela_chave($cod_cupons);

        // $texto  = 'Olá, <strong>'.primeira_maiuscula($nome[0]).'</strong>, faz tempo que você não aparece, aconteceu alguma coisa?';
        // $texto .= '<br />O que podemos fazer pra ter o seu nome de volta nossa lista de pedidos online?';
        // $texto .= '<br /><br />Vamos fazer assim, você volta, pede uma Pizza Quadrada pelo site e nós';
        // $texto .= 'te daremos uma Pizza Quadradinha de qualquer sabor! É só usar o código';
        // $texto .= 'abaixo. Estamos te aguardando!';
        // $texto .= '<br /><br />Número do Cupom: <strong>'.$numero_cupom.'</strong> *';
        // $texto .= '<br /><br />Suzana Rodrigues';
        // $texto .=  "<br><br><br><b>* Cupom válido até ".date("d/m/Y",$timestamp)."</b>";
        // $arr_aux = array();
        // $arr_aux['cod_pedidos'] = 0;
        // $arr_aux['cod_usuarios'] = 0;
        // $arr_aux['cod_clientes'] = $sql_buscar_clientes->cod_clientes;
        // $arr_aux['cod_pizzarias'] = 0;
        // $arr_aux['tipo'] = 'JOB_DEIXOU_COMPRAR';
        //echo "<br/><br/>Enviou:";

        //echo "<br/>$email_destino";
        // if (enviar_email($email_origem, $email_destino, $assunto, $texto, $arr_aux, 'neutro', '', false,$con))
        {
         //echo "<br/>enviou";
           $sql_log_envio = "INSERT INTO ipi_email_automatico (cod_clientes, tipo_email, data_hora_envio,cod_pizzarias,cod_cupons) VALUES ('".$obj_buscar_clientes->cod_clientes."', 'CLIENTE_INATIVO_REGRA01', '".date("Y-m-d H:i:s")."','".$obj_buscar_pizzarias->cod_pizzarias."','".$cod_cupons."')";

           $res_log_envio = mysql_query($sql_log_envio);
        }
  //
        if ( ($i%$taxa_emails_por_segundo==0) && ($i!=0))
        {
            // O AWS suporta apenas X emails por segundo...
            sleep(1);
            //echo "<br>###";
        }
      }
    }

  }
}



/*
$email_destino = $objBuscaCliente->email;
$assunto = NOME_SITE . " - Aumente seus pontos de FIDELIDADE!";

$nome = explode(' ', $objBuscaCliente->nome);

$texto = "<br /><strong>".primeira_maiuscula($nome[0])."</strong>, gostou de usar os Pontos Fidelidade Muzza? Legal, né?";
$texto .= "<br />Então, você sabia que indicar nosso site para os amigos te dá direito";
$texto .= "<br />a mais pontos?";
$texto .= "<br />É, basta o seu indicado fazer uma compra que os pontos do primeiro";
$texto .= "<br />pedido dele também vão pra você!";
$texto .= "<br /><br />Não perca tempo e fale pra todo mundo como é legal pedir Pizza";
$texto .= "<br />Quadrada dos Muzza pela internet!";
$texto .= "<br /><br />Acesse www.osmuzzarellas.com.br/indique e veja como é fácil ganhar";
$texto .= "<br />mais Pontos Fidelidade Muzza com a ajuda dos amigos.";
$texto .= "<br /><br />Boa pontuação pra você!";
$texto .= "<br /><br />Equipe Muzza";
//echo "<br>email: ".$texto;

if(!enviar_email($email_origem, $email_destino, $assunto, $texto, 'neutro'))
mensagemErro('Erro ao ENVIAR enquete', 'Por favor, verifique as configurações do cliente: '.bd2texto($objBuscaCliente->nome).'.');




$sql_log_email = "INSERT INTO ipi_email_automatico (cod_clientes, tipo_email, data_hora_envio) VALUES( '".$cod_clientes."', 'AUMENTAR_FIDELIDADE', '".date("Y-m-d H:i:s")."')";
$res_log_email = mysql_query($sql_log_email);




Olá, .........., faz tempo que você não aparece, aconteceu alguma coisa?
O que podemos fazer pra ter o seu nome de volta nossa lista de pedidos online?

Vamos fazer assim, você volta, pede uma Pizza Quadrada pelo site e nós
te daremos uma Pizza Quadradinha de qualquer sabor! É só usar o código
abaixo. Estamos te aguardando!

Suzana Rodrigues
*/


$time = microtime();
$time = explode(" ", $time);
$time = $time[1] + $time[0];
$finish = $time;
$totaltime = ($finish - $start);
printf("<br /><br />Tempo de carga %f segundos", $totaltime);


desconectabd($con);
?>