<?

require_once dirname(__FILE__) . '/../bd.php';

/**
 * Classe de Cupom.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR         DESCRIÇÃO 
 * ======    ==========   ===========   =============================================================
 *
 * 1.0       03/08/2011   Thiago        Criado.
 *
 */
class Cupom
{

    /**
    * Verificar a existencia de um Cupom no banco de dados.
    *
    * @param string $cupom
    * 
    * @return true para existe ou false para não existe
    */
    private function existe_cupom($cupom) 
    {
      $sql_contagem = "SELECT COUNT(*) AS contagem FROM ipi_cupons WHERE cupom = '$cupom'";
      $res_contagem = mysql_query($sql_contagem);
      $obj_contagem = mysql_fetch_object($res_contagem);

      if($obj_contagem->contagem > 0)
        $achou = true;
      else
        $achou = false;

      return $achou;
    }

    /**
    * Verificar a existencia de um Cupom no banco de dados.
    *
    * @param int $cod_cupons
    * 
    * @return se existir retorna o número do cupom ou false para não existe
    */
    public function consultar_numero_cupom_pela_chave($cod_cupons) 
    {
      $sql_cupom = "SELECT cupom FROM ipi_cupons WHERE cod_cupons = '".$cod_cupons."'";
      $res_cupom = mysql_query($sql_cupom);
      $obj_cupom = mysql_fetch_object($res_cupom);

      if($obj_cupom->cupom != "")
        $retorno = $obj_cupom->cupom;
      else
        $retorno = false;

      return $retorno;
    }

    /**
    * Excluir Cupom no banco de dados.
    *
    * @param int $cod_cupons
    * 
    * @return true para excluido ou false para não excluido
    */
    public function excluir_cupom($cod_cupons) 
    {
	    $sql_uso_cupom = "SELECT COUNT(c.cod_pedidos) total_uso, cup.cupom, cup.cod_cupons FROM ipi_pedidos_ipi_cupons c INNER JOIN ipi_pedidos p ON(c.cod_pedidos=p.cod_pedidos) INNER JOIN ipi_cupons cup ON(c.cod_cupons=cup.cod_cupons) WHERE p.situacao='BAIXADO' AND c.cod_cupons=".$cod_cupons;
	    $res_uso_cupom = mysql_query($sql_uso_cupom);
	    $obj_uso_cupom = mysql_fetch_object($res_uso_cupom);

	    if ($obj_uso_cupom->total_uso>0)
	    {
        $resultado = false;
	    }
	    else
	    {
        $resultado = true;

		    $SqlDel = "DELETE FROM ipi_pizzarias_cupons WHERE cod_cupons IN (".$cod_cupons.")";
		    $resultado &= mysql_query($SqlDel);

		    $SqlDel = "DELETE FROM ipi_cupons WHERE cod_cupons IN (".$cod_cupons.")";
		    $resultado &= mysql_query($SqlDel);
	    }

      return($resultado);

    }


    /**
    * Excluir Cupom no banco de dados.
    *
    * @param int $cod_cupons
    * 
    * @return true para excluido ou false para não excluido
    */
    public function alterar_pizzaria($cod_cupons,$cod_pizzarias) 
    {
      $resultado = true;

      $SqlDel = "DELETE FROM ipi_pizzarias_cupons WHERE cod_cupons IN (".$cod_cupons.")";
      $resultado &= mysql_query($SqlDel);

      $cont_pizzarias = count($cod_pizzarias);

      for($a = 0; $a < $cont_pizzarias; $a++) 
      {
        $sql_cupons_pizzarias = sprintf("INSERT INTO ipi_pizzarias_cupons (cod_cupons, cod_pizzarias) VALUES ('%s', '%s')", $cod_cupons, $cod_pizzarias[$a]);
        $resultado &= mysql_query($sql_cupons_pizzarias);
        //echo "<br>".($a+1).": ".$sql_cupons_pizzarias;
      }

      return($resultado);
    }

    /**
     * Gera um novo número de Cupom 
     *
     * @param int $tam
     * 
     * @return true para sucesso
     */
    private function gerar_cupom($tam = 10) 
    {
      $cupom = "";
      $caracteres = "123456789ABCDEFGHIJKLMNPQRSTUWXYZ";
      $i = 0;
      while ( $i < $tam ) 
      {
        $char = substr($caracteres, mt_rand(0, strlen($caracteres) - 1 ), 1);
        if (! strstr ( $cupom, $char )) 
        {
          $cupom .= $char;
          $i ++;
        }
      }
      return $cupom;
    }


    /**
     * Cadastrar um Cupom no banco de dados.
     *
     * @param date $data_validade
     * @param string $produto
     * @param int $cod_produtos
     * @param int $cod_tamanhos
     * @param bool $promocao
     * @param bool $necessita_compra
     * @param float $valor_minimo_compra
     * @param int $cod_usuario
     * @param string $generico
     * @param string $obs_cupom
     * @param array int $cod_pizzarias
     * @param int $cod_clientes
     * @return cod_cupons que é a chave primária do cupom gerado
     */
    public function inserir_cupom($data_inicio,$data_validade, $produto, $cod_produtos, $cod_tamanhos, $promocao, $necessita_compra, $valor_minimo_compra, $cod_usuario, $generico, $obs_cupom, $cod_pizzarias, $cod_clientes = '')
    {
      beginbd();

      do 
      {
          $cupom = $this->gerar_cupom();
          if($this->existe_cupom($cupom))
            $achou = true;
          else
            $achou = false;
          
      } while($achou);

      $sql_inserir = sprintf("INSERT INTO ipi_cupons (cupom, data_inicio, data_validade, produto, cod_produtos, cod_tamanhos, valido, promocao, necessita_compra, valor_minimo_compra, usuario_criacao, generico, obs_cupom, data_hora_cupom, cod_clientes) VALUES ('%s', '%s', '%s', '%s', %d, %d, 1, %d, '%s', '%s', '%s', '%s', '%s', '%s', '%d')", 
                           $cupom, $data_inicio, $data_validade, $produto, $cod_produtos, $cod_tamanhos, $promocao, $necessita_compra, $valor_minimo_compra, $_SESSION['usuario']['codigo'], $generico, $obs_cupom, date("Y-m-d H:i:s"), $cod_clientes);
      //echo "<br>sql_inserir: ".$sql_inserir;
      $res_inserir = mysql_query($sql_inserir);
      $cod_cupons = mysql_insert_id();
  
      $cont_pizzarias = count($cod_pizzarias);

      for($a = 0; $a < $cont_pizzarias; $a++) 
      {
        $sql_cupons_pizzarias = sprintf("INSERT INTO ipi_pizzarias_cupons (cod_cupons, cod_pizzarias) VALUES ('%s', '%s')", $cod_cupons, $cod_pizzarias[$a]);
        $res_cupons = mysql_query($sql_cupons_pizzarias);
        //echo "<br>".($a+1).": ".$sql_cupons_pizzarias;
      }

      if (mysql_errno() > 0)
      {
          rollbackbd();
          throw new Exception('Erro ao gravar registro de estoque no banco de dados. Erro núm.: ' . mysql_errno());
          return false;
      }
      else
      {
          commitbd();
          return($cod_cupons);
      }
    }
    

}

?>
