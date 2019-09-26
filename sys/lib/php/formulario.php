<?
/**
 * Rotinas de captura de variáveis de formulário.
 * 
 * @version 1.1
 * 
 * LISTA DE MODIFICAÇÕES:
 * 
 * DATA         VERSÃO   NOME         DESCRIÇÃO
 * ====         ======   ====         =========
 * 
 * 30/05/2009   1.1      Felipe       Implementado segurança nas funções validaVarPost, validaVarGet e validaVarFiles.              
 */

/**
 * Filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco de dados (addslashes).
 * 
 * @see validaVarPost
 * @ses validaVarGet
 * @see validaVarFiles
 * 
 * @see addslashes
 * @see preg_match
 * 
 * @param string $valor Valor de entrada.
 * @param string $expressao Expressão regular adicional que filtra os indices não permitidos (OPCIONAL).
 * 
 * @return Valor filtrado.
 */
function filtraCaracteresSql($valor, $expressao = '') {
  if (($expressao != '') && (!preg_match ($expressao, $valor))) {
    $valor = '';
  }
  
  return addslashes ($valor);
}

/**
 * Valida uma variável de formulário POST.
 *
 * A função já filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco (addslashes).
 * 
 * Para mais detalhes veja:
 * 
 * @see bd2texto
 * @see filtraCaracteresSql
 * 
 * @param string $nomeVar Nome da variável.
 * @param string $expressao Expressão regular que valida a entrada.
 * 
 * @return String ou Array com os POSTs.
 */
function validaVarPost($nomeVar, $expressao = '') {
  $valor = '';
  
  if (isset ($_POST[$nomeVar])) {
    if (is_array ($_POST[$nomeVar])) {
      $valor = $_POST[$nomeVar];
      
      for($i = 0; $i < count ($valor); $i++)
        $valor[$i] = filtraCaracteresSql ($valor[$i], $expressao);
    }
    else {
      $valor = filtraCaracteresSql ($_POST[$nomeVar], $expressao);
    }
  }
  
  return $valor;
}

/**
 * Valida uma variável de formulário GET.
 *
 * A função já filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco (addslashes).
 * 
 * Para mais detalhes veja:
 * 
 * @see bd2texto
 * @see filtraCaracteresSql
 * 
 * @param string $nomeVar Nome da variável.
 * @param string $expressao Expressão regular que valida a entrada.
 * 
 * @return String ou Array com os POSTs.
 */
function validaVarGet($nomeVar, $expressao = '') {
  $valor = '';
  
  if (isset ($_GET[$nomeVar])) {
    if (is_array ($_GET[$nomeVar])) {
      $valor = $_GET[$nomeVar];
      
      for($i = 0; $i < count ($valor); $i++)
        $valor[$i] = filtraCaracteresSql ($valor[$i], $expressao);
    }
    else {
      $valor = filtraCaracteresSql ($_GET[$nomeVar], $expressao);
    }
  }
  
  return $valor;
}

/**
 * Valida uma variável de formulário FILE.
 *
 * A função já filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco (addslashes).
 * 
 * Para mais detalhes veja:
 * 
 * @see bd2texto
 * @see filtraCaracteresSql
 * 
 * @param string $nomeVar Nome da variável.
 * @param string $expressao Expressão regular que valida a entrada.
 * 
 * @return String ou Array com os POSTs.
 */
function validaVarFiles($nomeVar, $expressao = '') {
  $valor = '';
  
  if (isset ($_FILES[$nomeVar])) {
    if (is_array ($_FILES[$nomeVar])) {
      $valor = $_FILES[$nomeVar];
      
      //for($i = 0; $i < count ($valor); $i++)
      //  $valor[$i] = filtraCaracteresSql ($valor[$i], $expressao);
    }
    else {
      //$valor = filtraCaracteresSql ($_FILES[$nomeVar], $expressao);
      $valor = $_FILES[$nomeVar];
    }
  }
  
  return $valor;
}


/*
CRIA LOG TXT
*/
function criaSalvaLog($name,$data){
  $f = fopen($name.'.log','w');
  fwrite($f,$data."\n");
  fclose($f);
}

/**
 * Filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco de dados (addslashes).
 * 
 * @see validar_var_post
 * @ses validar_var_get
 * @see validar_var_file
 * 
 * @see addslashes
 * @see preg_match
 * 
 * @param string $valor Valor de entrada.
 * @param string $expressao Expressão regular adicional que filtra os indices não permitidos (OPCIONAL).
 * 
 * @return Valor filtrado.
 */
function filtrar_caracteres_sql ($valor, $expressao = '')
{
    if (($expressao != '') && (!preg_match($expressao, $valor)))
    {
        $valor = '';
    }
    
    return addslashes($valor);
}

/**
 * Valida uma variável de formulário POST.
 *
 * A função já filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco (addslashes).
 * 
 * Para mais detalhes veja:
 * 
 * @see bd2texto
 * @see filtrar_caracteres_sql
 * 
 * @param string $nome_var Nome da variável.
 * @param string $expressao Expressão regular que valida a entrada.
 * 
 * @return Variável com os POSTs.
 */
function validar_var_post ($nome_var, $expressao = '')
{
    $valor = '';
    
    if (isset($_POST[$nome_var]))
    {
        if (is_array($_POST[$nome_var]))
        {
            $valor = $_POST[$nome_var];
            
            for ($i = 0; $i < count($valor); $i++)
            {
                $valor[$i] = filtrar_caracteres_sql($valor[$i], $expressao);
            }
        }
        else
        {
            $valor = filtrar_caracteres_sql($_POST[$nome_var], $expressao);
        }
    }
    
    return $valor;
}

/**
 * Valida uma variável de formulário GET.
 *
 * A função já filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco (addslashes).
 * 
 * Para mais detalhes veja:
 * 
 * @see bd2texto
 * @see filtrar_caracteres_sql
 * 
 * @param string $nome_var Nome da variável.
 * @param string $expressao Expressão regular que valida a entrada.
 * 
 * @return Variável com os POSTs.
 */
function validar_var_get ($nome_var, $expressao = '')
{
    $valor = '';
    
    if (isset($_GET[$nome_var]))
    {
        if (is_array($_GET[$nome_var]))
        {
            $valor = $_GET[$nome_var];
            
            for ($i = 0; $i < count($valor); $i++)
            {
                $valor[$i] = filtrar_caracteres_sql($valor[$i], $expressao);
            }
        }
        else
        {
            $valor = filtrar_caracteres_sql($_GET[$nome_var], $expressao);
        }
    }
    
    return $valor;
}

/**
 * Valida uma variável de formulário FILE.
 *
 * A função já filtra os caracteres mais comuns de SQL Injection e prepara a string para gravação no banco (addslashes).
 * 
 * Para mais detalhes veja:
 * 
 * @see bd2texto
 * @see filtrar_caracteres_sql
 * 
 * @param string $nome_var Nome da variável.
 * @param string $expressao Expressão regular que valida a entrada.
 * 
 * @return Variável com os POSTs.
 */
function validar_var_file ($nome_var, $expressao = '')
{
    $valor = '';
    
    if (isset($_FILES[$nome_var]))
    {
        $valor = $_FILES[$nome_var];
    }
    
    return $valor;
}


?>
