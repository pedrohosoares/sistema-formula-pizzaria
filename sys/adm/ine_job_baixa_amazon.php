<?
require_once '../../bd.php'; 

$tipo_ativo = 1;

$arquivo = "log_emails.txt";
$post = var_export($HTTP_RAW_POST_DATA, true);
file_put_contents($arquivo,date("d/m/Y H:i:s")."\n\n",FILE_APPEND);
file_put_contents($arquivo,"//////////////  POST     /////////".$post   ."///////////POST    //////////// \n\n",FILE_APPEND);

$json = $HTTP_RAW_POST_DATA;

file_put_contents($arquivo,$json."\n",FILE_APPEND);

$arr_json = json_decode($json,true);
$arr_dados = $arr_json;//(array) 
// echo "<br/> LAST ERROR=".json_last_error()."<br/>";
// echo "///////ARRAY JSON AMAZON/////////<pre>";
// print_r($arr_dados);
// echo "</pre><br/>////////FIM ARRAY AMAZON//////////";

if($arr_dados["Message"]["notificationType"] =="Bounce")
{
	if($arr_dados["Message"]["bounce"]["bounceType"]=="Permanent" || $arr_dados["Message"]["bouce"]["bounceType"]=="Undetermined")
	{
		$tipo_ativo = 3;
		$arr_emails = array();
		$cont_bounce = count($arr_dados["Message"]["bounce"]["bouncedRecipients"]);
		
      for($b = 0;$b <=$cont_bounce-1; $b++)
		{
			$arr_emails[] = $arr_dados["Message"]["bounce"]["bouncedRecipients"][$b]["emailAddress"];
		}

		$lista_emails = implode("','",$arr_emails);

		$con = conectar_bd();
		$sql_alterar = "update ine_emails_cadastro set ativo=".$tipo_ativo." where email in ('".$lista_emails."')";
      file_put_contents($arquivo,$sql_alterar."\n",FILE_APPEND);
		$res_alterar = mysql_query($sql_alterar);
		desconectar_bd($con);
	}
}
elseif($arr_dados["Message"]["notificationType"] =="Complaint")
{
	$tipo_ativo = 0;
	$arr_emails = array();
	$cont_comp = count($arr_dados["Message"]["complaint"]["complainedRecipients"]);
	
   for($c = 0;$c <=$cont_comp-1; $c++)
	{
		$arr_emails[] = $arr_dados["Message"]["complaint"]["complainedRecipients"][$c]["emailAddress"];
	}
	
   $lista_emails = implode("','",$arr_emails);

	switch($arr_dados["Message"]["complaint"]["complaintFeedbackType"])
	{
		case 'abuse':
			$tipo_ativo = 8;
		break;
		case 'auth-failure':
			$tipo_ativo = 9;
		break;
		case 'fraud':
			$tipo_ativo = 10; 
		break;
		case 'not-spam':
			$tipo_ativo = 1; //na verdade é o 11, mas como o "Complaint" é de 'Não Spam' então pôr como ativo 
		break;
		case 'other':		
			$tipo_ativo = 12; 
		break;
		case 'virus':
			$tipo_ativo = 15;
		break;
		default:
			$tipo_ativo = 14;
		break;
	}

	$con = conectar_bd();
	$sql_alterar = "update ine_emails_cadastro set ativo=".$tipo_ativo." where email in ('".$lista_emails."')";
   file_put_contents($arquivo,$sql_alterar."\n",FILE_APPEND);
	$res_alterar = mysql_query($sql_alterar);
	desconectar_bd($con);
}

?>
