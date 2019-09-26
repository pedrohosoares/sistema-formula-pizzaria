<?

require_once dirname(__FILE__) . '/../bd.php';

/**
 * Classe de Cliente
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       01/08/2012   Thiago        Criado.
 *
 */
class PontosFidelidade
{

    /**
    * Consulta da tabela de pontos fidelidade.
    *
    * @param string Colocar todos os dados do cliente.
    * 
    * @return True caso consiga e False caso dê erro.
    */
    function buscar_tabela_cep() 
    {
      $conexao = conectar_bd();
      if ($_SESSION['ipi_carrinho']['buscar_balcao'] == "Balcão")
      {
        $cod_pizzarias = $_SESSION['ipi_carrinho']['cod_pizzarias'];
      }
      else
      { 
        $cep_visitante = $_SESSION['ipi_carrinho']['cep_visitante'];
        if($cep_visitante)
        {
            $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep_visitante));
            $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= $cep_limpo AND cep_final >= $cep_limpo LIMIT 1";
            $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
            $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
            $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
        }

      } 
      desconectar_bd($conexao);
      return $this->buscar_tabela_cod($cod_pizzarias); 
    }

    function buscar_tabela_cod($cod_pizzarias) 
    {
      $conexao = conectar_bd();  
      $retorno ='';
      // $retorno .= '<dl>';

      if(!$cod_pizzarias) $cod_pizzarias = 1;

      $sql_busca_pizza_cara = "SELECT max(pontos_fidelidade) as pontos_fidelidade,cod_tamanhos FROM ipi_pizzas_ipi_tamanhos where cod_pizzarias = $cod_pizzarias group by cod_tamanhos order by cod_tamanhos ASC";
      $res_busca_pizza_cara = mysql_query($sql_busca_pizza_cara);
      if(mysql_num_rows($res_busca_pizza_cara)<=0)
      {
        $sql_busca_pizza_cara = "SELECT max(pontos_fidelidade) as pontos_fidelidade ,cod_tamanhos FROM ipi_pizzas_ipi_tamanhos where cod_pizzarias = 1 group by cod_tamanhos order by cod_tamanhos ASC";
        $res_busca_pizza_cara = mysql_query($sql_busca_pizza_cara);
      }
      $obj_busca_pizza_cara  = mysql_fetch_object($res_busca_pizza_cara);
      $arr_pizzas = array();
      $arr_pizzas[1] = $obj_busca_pizza_cara->pontos_fidelidade;
      $obj_busca_pizza_cara  = mysql_fetch_object($res_busca_pizza_cara);
      $arr_pizzas[3] = $obj_busca_pizza_cara->pontos_fidelidade;
      $obj_busca_pizza_cara  = mysql_fetch_object($res_busca_pizza_cara);
      $arr_pizzas[2] = $obj_busca_pizza_cara->pontos_fidelidade;
      // $retorno .=  '<dt><strong>'.$arr_pizzas[1].' Pontos:</strong></dt> <dd>Pizza Quadrada (35cm - 10 pedaços) de qualquer sabor</dd>';
      // $retorno .=  '<dt><strong>'.$arr_pizzas[2].' Pontos:</strong></dt> <dd>Quadrada Six (27cm - 6 pedaços) de qualquer sabor </dd> ';
      // $retorno .=  '<dt><strong>'.$arr_pizzas[3].' Pontos:</strong></dt> <dd>Pizza Quadradinha (22cm - 4 pedaços) de qualquer sabor </dd>';

       $sql_busca_quadinha_classica = "select max(pontos_fidelidade) as pontos_fidelidade from ipi_pizzas_ipi_tamanhos where cod_pizzarias = $cod_pizzarias and cod_tamanhos = 4 and cod_pizzas in (43,46)";
       //echo "<br/>".$sql_busca_quadinha_classica."</br>";
       $res_busca_quadinha_classica = mysql_query($sql_busca_quadinha_classica);
       if(mysql_num_rows($res_busca_quadinha_classica)<=0)
       {
        $sql_busca_pizza_cara = "select max(pontos_fidelidade) as pontos_fidelidade from ipi_pizzas_ipi_tamanhos where cod_pizzarias = 1 and cod_tamanhos = 4 and cod_pizzas in (43,46)";
        $res_busca_pizza_cara = mysql_query($sql_busca_pizza_cara);
       }
       $obj_busca_quadinha_classica = mysql_fetch_object($res_busca_quadinha_classica);

       // $retorno .=  '<dt><strong>'.$obj_busca_quadinha_classica->pontos_fidelidade.' Pontos:</strong></dt> <dd>Pizzas Quadradinha Clássica ou Doce de Leite </dd>';

       $sql_busca_refri_caro = "SELECT max(pontos_fidelidade) as pontos_fidelidade FROM ipi_conteudos_pizzarias cp inner join ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos  = cp.cod_bebidas_ipi_conteudos WHERE cp.cod_pizzarias = $cod_pizzarias and cp.venda_net=1 and cp.situacao='ATIVO' group by cod_conteudos order by cod_conteudos asc";
       //echo $sql_busca_refri_caro;
       $res_busca_refri_caro = mysql_query($sql_busca_refri_caro);

       if(mysql_num_rows($res_busca_refri_caro)<=0)
       {
           $sql_busca_refri_caro = "SELECT max(pontos_fidelidade) as pontos_fidelidade FROM ipi_conteudos_pizzarias cp inner join ipi_bebidas_ipi_conteudos bc on bc.cod_bebidas_ipi_conteudos  = cp.cod_bebidas_ipi_conteudos WHERE cp.cod_pizzarias = 1 and cp.venda_net=1 and bc.situacao='ATIVO' group by cod_conteudos order by cod_conteudos asc";
           $res_busca_refri_caro = mysql_query($sql_busca_refri_caro);
       }

       $obj_busca_refri_caro = mysql_fetch_object($res_busca_refri_caro);
       // $retorno .=  '<dt><strong>'.$obj_busca_refri_caro->pontos_fidelidade.' Pontos:</strong></dt><dd>Refrigerante de 2 litros </dd>';

       $obj_busca_refri_caro = mysql_fetch_object($res_busca_refri_caro); 

       $sql_busca_borda_cara = "select max(pontos_fidelidade) as pontos_fidelidade from ipi_tamanhos_ipi_bordas tb where cod_pizzarias = $cod_pizzarias and cod_tamanhos = 3";
       //echo "<br/>".$sql_busca_borda_cara."</br>";
       $res_busca_borda_cara = mysql_query($sql_busca_borda_cara);
       if(mysql_num_rows($res_busca_borda_cara)<=0)
       {
            $sql_busca_borda_cara = "select max(pontos_fidelidade) as pontos_fidelidade from ipi_tamanhos_ipi_bordas tb where cod_pizzarias = 1 and cod_tamanhos = 3";
            $res_busca_borda_cara = mysql_query($sql_busca_borda_cara);
       }
       $obj_busca_borda_cara = mysql_fetch_object($res_busca_borda_cara);
       $pontos = 180;

       if($obj_busca_refri_caro->pontos_fidelidade>$obj_busca_borda_cara->pontos_fidelidade)
       {
        $pontos = $obj_busca_refri_caro->pontos_fidelidade;
       }
       else
        $pontos = $obj_busca_borda_cara->pontos_fidelidade;

       // $retorno .=  '<dt><strong>'.$pontos.' Pontos:</strong></dt><dd>Refrigerante em lata ou borda recheada </dd>';
       // $retorno .=  '</dl>';

      desconectar_bd($conexao);
      return $retorno;
    }
}
?>