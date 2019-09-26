<?php
/**
 * Rotinas de conexão com o banco de dados.
 * 
 * @version 1.1
 * 
 * @uses config.php
 * 
 * LISTA DE MODIFICAÇÕES:
 * 
 * ===================================================================================
 * DATA: 30/05/2009
 * VERSÃO: 1.1
 * AUTOR: Felipe
 * DESCRIÇÃO: Repassado a segurança da função texto2bd para validaVarPost e validaVarGet e documentação nas funções.
 * 
 */

require_once 'config.php';
require_once 'traducoes.php';
ini_set('display_errors', 'Off');

/**
 * Compactar valores, para comparar nos bloqueios.
 * 
 * @return Link de conexão
 */
function compactar_valores($str) 
{
  $p = strtr($str, "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC");
  $url = ereg_replace('[^a-zA-Z0-9]', '', $p);
  $url = strtolower($url);
  return $url;
}

/**
 * Conecta ao banco de dados.
 * 
 * @return Link de conexão
 */
function conectabd()
{
    date_default_timezone_set('America/Sao_Paulo');
    $con = mysql_connect(BD_HOST, BD_USUARIO, BD_SENHA) or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Problemas na conexão com a base de dados. <br>Verifique se o MySQL está funcionando corretamente.</font></center>");
    
    mysql_select_db(BD_NOME, $con) or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Base de dados (" . BD_NOME . ") inexistente ou configurada incorretamente.</font></center>");
    
    mysql_set_charset('latin1') or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Não foi possível selecionar o charset apropriado.</font></center>");
    
    return ($con);
}

/**
 * Conectar ao banco de dados, mesma função que a anterior apenas migrando para o novo padrão de nomenclatura
 * 
 * @return Link de conexão
 */
function conectar_bd()
{
    date_default_timezone_set('America/Sao_Paulo');
    $con = mysql_connect(BD_HOST, BD_USUARIO, BD_SENHA) or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Problemas na conexão com a base de dados. <br>Verifique se o MySQL está funcionando corretamente.</font></center>");
    
    mysql_select_db(BD_NOME, $con) or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Base de dados (" . BD_NOME . ") inexistente ou configurada incorretamente.</font></center>");
    
    mysql_set_charset('latin1') or die("<center><br><font color=red><b><h1>Atenção</h1><b><br><br>Não foi possível selecionar o charset apropriado.</font></center>");
    
    return ($con);
}

/**
 * Desconecta do banco de dados.
 *
 * @param int $con Link de conexão
 */
function desconectabd($con)
{
    mysql_close($con);
}

/**
 * Desconectar do banco de dados, mesma função que a anterior apenas migrando para o novo padrão de nomenclatura
 *
 * @param int $con Link de conexão
 */
function desconectar_bd($con)
{
    mysql_close($con);
}


/**
 * Verifica se existe nota ativa
*/
function nota_ativa(){
    $con = conectabd();
    $codpizzarias = $_SESSION['usuario']['cod_pizzarias'];
    $possuiDespachoNotaFiscal = mysql_query('SELECT cod_pizzarias,nota_ativa FROM ipi_pizzarias WHERE cod_pizzarias IN ('.implode(',', $codpizzarias).')');
    while($nota_ativa = mysql_fetch_assoc($possuiDespachoNotaFiscal)){
        $notasAtivas[] = array(
            'cod_pizzarias'=>$nota_ativa['cod_pizzarias'],
            'nota_ativa'=>$nota_ativa['nota_ativa'],
        );
    }
    desconectabd($con);
    return $notasAtivas;
}

/**
 * Inicia a transação do banco de dados.
 * 
 * Exemplo de utilização:
 * 
 * beginbd();
 * 
 * // Executa os comandos de banco como INSERT, UPDATE, etc...
 * 
 * if(mysql_error())
 * {
 *     rollbackbd();
 * }
 * else
 * {
 *     commitbd();
 * }
 * 
 * @see mysql_error()
 */
function beginbd()
{
    mysql_query('begin');
}

/**
 * Realiza o commit no banco.
 * 
 * Exemplo de utilização:
 * 
 * beginbd();
 * 
 * // Executa os comandos de banco como INSERT, UPDATE, etc...
 * 
 * if(mysql_error())
 * {
 *     rollbackbd();
 * }
 * else
 * {
 *     commitbd();
 * } 
 * 
 * @see mysql_error()
 */
function commitbd()
{
    mysql_query('commit');
}

/**
 * Realiza o rollback no banco.
 * 
 * Exemplo de utilização:
 * 
 * beginbd();
 * 
 * // Executa os comandos de banco como INSERT, UPDATE, etc...
 * 
 * if(mysql_error())
 * {
 *     rollbackbd();
 * }
 * else
 * {
 *     commitbd();
 * } 
 * 
 * @see mysql_error()
 * 
 */
function rollbackbd()
{
    mysql_query('rollback');
}

/**
 * Executa um comando de consulta no banco de dados (select) e retorna o objeto.
 * 
 * Utilizar com cuidado, pois, quando não é passada o argumento $con a mesma se conecta no banco de dados automaticamente.
 *
 * @see conectabd
 * @see desconectabd
 * 
 * @see mysql_fetch_object
 * 
 * @param string $sql Comando SQLs
 * @param int $con Link de conexão
 * 
 * @return Objeto da consulta.
 */
function executaBuscaSimples($sql, $con = '')
{
    if (!$con)
        $ncon = conectabd();
    
    $res = mysql_query($sql);
    
    if (!$res)
        return false;
    
    $obj = mysql_fetch_object($res);
    
    mysql_free_result($res);
    
    if (!$con)
        desconectabd($ncon);
    
    return $obj;
}


/**
 * Executa um comando de consulta no banco de dados (select) e retorna o objeto.
 * 
 * Utilizar com cuidado, pois, quando não é passada o argumento $con a mesma se conecta no banco de dados automaticamente.
 *
 * @see conectar_bd
 * @see desconectar_bd
 * 
 * @see mysql_fetch_object
 * 
 * @param string $sql Comando SQLs
 * @param int $conexao Link de conexão
 * 
 * @return Objeto da consulta.
 */
function executar_busca_simples ($sql, $conexao = '')
{
    if (!$conexao)
        $ncon = conectar_bd();
    
    $res = mysql_query($sql);
    
    if (!$res)
        return false;
    
    $obj = mysql_fetch_object($res);
    
    mysql_free_result($res);
    
    if (!$conexao)
        desconectar_bd($ncon);
    
    return $obj;
}


/**
 * Converte o formato de data do banco de dados para formato brasileiro (DD/MM/YYYY).
 *
 * Se o parâmetro $data contiver a string "vazia", um valor "__/__/____" é retornado.
 * 
 * @see data2bd
 * 
 * @param string $data
 * 
 * @return Data formatada
 */
function bd2data($data)
{
    if ($data == 'vazia')
    {
        return ('__/__/____');
    }
    else 
        if ($data == '0000-00-00')
        {
            return '';
        }
        else 
            if ($data == '')
            {
                return '';
            }
            else
            {
                $data_array = explode("-", $data);
                
                if (count($data_array) == 2)
                    array_push($data_array, date(Y));
                
                return ($data_array[2] . '/' . $data_array[1] . '/' . $data_array[0]);
            }
}

/**
 * Converte o formato de data e hora do banco de dados para formato brasileiro (DD/MM/YYYY HH:MM:SS).
 *
 * @see data2bd
 * 
 * @param string $data
 * 
 * @return Data e hora formatada
 */
function bd2datahora($data)
{
    if ($data == '')
    {
        return '';
    }
    else
    {
        return (date("d/m/Y H:i:s", strtotime($data)));
    }
}



/**
 * Converte o texto de entrada com as primeira letras maiuscula e retirando algumas exceções
 * 
 * @param string $data
 * 
 * @return texto formatada
 */

function primeira_maiuscula($string)
{
	$string = trim(ucwords(mb_strtolower($string)));
	$string = str_replace("Ii","II",$string);
	$string = str_replace("IIi","III",$string);
	$string = str_replace("Iv","IV",$string);
	$string = str_replace(" E "," e ",$string);
	$string = str_replace("De","de",$string);
	$string = str_replace("Do","do",$string);
	$string = str_replace("Dos","dos",$string);
	$string = str_replace("Da","da",$string);
	return $string;
}


/**
 * Converte o formato de data e hora brasileiro para formato do banco de dados (YYYY-MM-DD).
 *
 * @see bd2data
 * @see bd2datahora
 * 
 * @param string $data
 * 
 * @return Data formatada
 */
function data2bd($data)
{
    $data_array = explode("/", $data);
    
    if (count($data_array) == 2)
        array_push($data_array, date(Y));
    
    return ($data_array[2] . '-' . $data_array[1] . '-' . $data_array[0]);
}

/**
 * Formata o valor de moeda para float (banco de dados).
 *
 * @param string $valor Valor em moeda
 * 
 * @return Valor formatado 
 */
function moeda2bd($valor)
{
    return (str_replace(",", ".", str_replace(".", "", $valor)));
}

/**
 * Formata o valor de float para moeda.
 *
 * @param float $valor Valor em número
 * 
 * @return Valor formatado
 */
function bd2moeda($valor)
{
    if ($valor)
        return (number_format($valor, 2, ",", "."));
    else
        return '';
}

//Formata o texto para exibição
/**
 * Retira os slashes do texto.
 * 
 * @see filtraCaracteresSql
 *
 * @param string $texto Texto de entrada
 * 
 * @return Textos com slashes retirados
 */
function bd2texto($texto)
{
    return stripslashes($texto);
}

/**
 * Adiciona os slashes ao texto.
 *
 * @deprecated Esta função será retirada em breve. Deixada apenas para efeitos de compatibilidade.
 * 
 * @see validaVarPost
 * @see validaVarGet
 * @see validaVarFiles
 * 
 * @param string $texto Texto de entrada
 * 
 * @return Texto com slashes adicionados
 */
function texto2bd($texto)
{
    //return addslashes($texto);
    return $texto;
}

/**
 * Exibe o conteudo preparado para uma tabela HTML.
 *
 * Este problema acontece quando um valor de <td></td> é vazio no Internet Explorer.
 * 
 * @deprecated Será removida em breve.
 * 
 * @param string $texto Texto de entrada
 * 
 * @return Valor formatado
 */
function bd2table($texto)
{
    if ($texto)
        return $texto;
    else
        return '&nbsp';
}

/**
 * Retorna o número de meses entre as data_inicial e data_final.
 * 
 *  @deprecated Formato da data DD/MM/AAAA
 * 
 * @param string $data_inicial data inicial
 * @param string $data_final data final
 * 
 * @return Número de meses entre as datas
 */
function numero_meses($data_inicial, $data_final)
{
	$arr_data_inicial = explode('/',$data_inicial); 
	$arr_data_final = explode('/',$data_final); 

	$dia1 = $arr_data_inicial[0]; 
	$mes1 = $arr_data_inicial[1]; 
	$ano1 = $arr_data_inicial[2]; 
	
	$dia2 = $arr_data_final[0]; 
	$mes2 = $arr_data_final[1]; 
	$ano2 = $arr_data_final[2]; 
	
	$a1 = ($ano2 - $ano1)*12;
	$m1 = ($mes2 - $mes1)+1;
	$m3 = ($m1 + $a1);
	
	return $m3;
}

/**
 * Função para validade um horário, se o horário for válido retorna true.
 * 
 *  @deprecated Formato da data HH:MM
 * 
 * @param string $hora_inicial
 * 
 * @return true ou false
 */
function validar_hora($hora)
{

    $t=explode(":",$hora);
    if ($t=="")
        return false;
    $h=$t[0];
    $m=$t[1];
    
    if (!is_numeric($h) || !is_numeric($m) )
        return false;
        
    if ($h<0 || $h>24)
        return false;
    if ($m<0 || $m>59)
        return false;
        
    return true;
}
?>
