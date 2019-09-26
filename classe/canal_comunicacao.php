<?

require_once dirname(__FILE__) .'/../bd.php';
require_once dirname(__FILE__) .'/../sys/lib/php/formulario.php';
require_once dirname(__FILE__) .'/../ipi_email.php';
require_once 'ipi_central_calculo_kpi.php';


/**
 * Classe do Canal de Comunicação
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR             DESCRIÇÃO 
 * ======    ==========   ===============   =============================================================
 *
 * 1.0       14/10/2012   FilipeGranato      Criado.
 *
 */

// To do List
// Criar aqui na classe, a parte de exibir as telas para não triplicar arquivos
// Criar método de alterar propiedades (campos) para não criar um método para cada


///
/// FIXME SE LOG PIZZARIA ULTRAPASSAR 50 CHAR, DIE OU EXCEPTION
///
class CanalDeComunicacao_ticket
{
  private $cod_log = 0;//variavel para salvar o codigo do log para caso foi feita varias alteraões juntas


  /////////////////////////////////////////////////////////////
    // Cods KPI 1
    private $cod_situacao_novo = 1;
    private $cod_situacao_andamento = 3;
    private $cod_situacao_aguardando = 4;
    private $cod_situacao_analise = 8;
    // Cods KPI 2
    private $cod_situacao_resolvido = 5;

    // Cods KPI 3
    private $cod_situacao_retrabalho = 6;

    private $horas_diarias = 9;

  //////////////////////////////////////////////////////////////

  /**
  * Cadastro o ticket no banco de dados.
  *
  * @param string Colocar todos os dados do ticke3t.
  * 
  * @return codigo do ticket caso consiga e false caso dê erro.
  */
  public function cadastrar_novo($usuario_criador,$pizzaria_usuario,$categoria_ticket,$nome_ticket,$mensagem_ticket,$cod_prioridades,$arquivos,$pizzarias) 
  {   
    $sql_edicao = "insert into ipi_comunicacao_tickets (cod_usuarios,cod_ticket_subcategorias,titulo_ticket,mensagem_ticket,data_hora_ticket,cod_situacoes,cod_prioridades) values(".$usuario_criador.",".$categoria_ticket.",'".$nome_ticket."','".$mensagem_ticket."',NOW(),1,'".$cod_prioridades."')";
    //echo $sql_edicao."</br>";
    $res_edicao = mysql_query($sql_edicao);
    if ($res_edicao)
    {
      $codigo = mysql_insert_id();
      $this->logar($codigo,$usuario_criador,'TROCA_SITUACAO','1');
      if($pizzarias)
      {
        if(is_array($pizzarias))
        {
          if(in_array('0',$pizzarias))
          {
            $sql_inserir_pizzarias = "insert into ipi_comunicacao_tickets_ipi_pizzarias(cod_tickets,cod_pizzarias) values(".$codigo.",'0')";
            $res_inserir_pizzarias &= mysql_query($sql_inserir_pizzarias);
            $cods[] = 0;
          }
          else
          {        
            for($i = 0; $i<count($pizzarias);$i++)
            {
              $sql_inserir_pizzarias = "insert into ipi_comunicacao_tickets_ipi_pizzarias(cod_tickets,cod_pizzarias) values(".$codigo.",'".$pizzarias[$i]."')";
              $res_inserir_pizzarias &= mysql_query($sql_inserir_pizzarias);
              $cods[] = $pizzarias[$i];
            }
          }
        }
        else
        {
          $sql_inserir_pizzarias = "insert into ipi_comunicacao_tickets_ipi_pizzarias(cod_tickets,cod_pizzarias) values(".$codigo.",'".$pizzarias."')";
          $res_inserir_pizzarias &= mysql_query($sql_inserir_pizzarias);
          $cods[] = $pizzarias;
        }

        $this->logar($codigo,$usuario_criador,'TROCA_PIZZARIA',implode(',',$cods));
      }

      $qtd_arqs = count($arquivos['name']);
      $sucesso_uploads = true;
      for($i = 0;$i<$qtd_arqs;$i++)
      {
        if(count($arquivos['name'][$i]) > 0) {     
          if(trim($arquivos['name'][$i]) != '') 
          {
      	    $arq_info = pathinfo($arquivos['name'][$i]);
						$arq_temp_nome = $arquivos['tmp_name'][$i];
						//echo "<br/>mandou upload $codigo,$arq_info,$arq_temp_nome";
            $sucesso_uploads &= $this->upload_arquivo($codigo,$usuario_criador,$arq_info,$arq_temp_nome);
          }          
        }
      }

      $this->enviar_email($codigo,'TICKET_NOVO');
      return $codigo;
    }
    else
    {          
    	throw new Exception('Erro ao cadastrar novo ticket no banco de dados. Erro núm.: ' . mysql_errno());
      return false;
    }

  }

  /**
  * Editar o ticket no banco de dados.
  *
  * @param string Colocar todos os dados do ticke3t.Caso não for alterar o paramametro, enviar 'N'
  * 
  * @return true caso consiga e false caso dê erro.
  *
  * 
  */
  public function editar_ticket($codigo,$codigo_usuario_alteracao,$comentario = 'N',$data_prevista= 'N',$data_prevista_analise = 'N',$cod_situacoes='N',$cod_subcategorias = 'N',$tempo_desenvolvimento= 'N',$tempo_trabalhado = 'N',$prioridade = 'N',$obs_franqueadora = 'N',$pizzarias = 'N',$arquivos = 'N')
  {
    $sql_verifica_alteracao = "select * from ipi_comunicacao_tickets where cod_tickets = '$codigo'";
    $res_verifica_alteracao = mysql_query($sql_verifica_alteracao);
    $obj_verifica_alteracao = mysql_fetch_object($res_verifica_alteracao);

    /*echo $sql_verifica_alteracao."<br/>";
    echo $codigo." - ".$codigo_usuario_alteracao." - ".$cod_situacoes." - ".$cod_subcategorias." - ".$pizzarias;
    echo "<br/><br/>aaa";
    echo "<pre>";
    print_r($pizzarias);
    echo "///";
    echo "<br/><Br/>";
    echo "aa-".$comentario."-aa";
    echo "</pre><br/><br/><br/>";
    die();*/
   // echo "data".$data_prevista;die();
    if($cod_situacoes==$obj_verifica_alteracao->cod_situacoes)
    {
      $cod_situacoes = 'N';
    }

    if($obs_franqueadora==$obj_verifica_alteracao->observacao_franqueadora)
    {
      $obs_franqueadora = 'N';
    }

    if($cod_subcategorias==$obj_verifica_alteracao->cod_ticket_subcategorias)
    {
      $cod_subcategorias = 'N';
    }
    
    if($tempo_desenvolvimento == $obj_verifica_alteracao->tempo_desenvolvimento)
    {
      $tempo_desenvolvimento= 'N';
    }
    
    if($tempo_trabalhado == $obj_verifica_alteracao->tempo_trabalhado)
    {
      $tempo_trabalhado= 'N';
    }

    if($prioridade == $obj_verifica_alteracao->cod_prioridades)
    {
      $prioridade = 'N';
    }

    if(($data_prevista==date("d/m/Y",strtotime($obj_verifica_alteracao->data_prevista))) || (trim($data_prevista)=="") || $data_prevista<date("d/m/Y"))
    {
      $data_prevista = 'N';
    }

    if(($data_prevista_analise==date("d/m/Y",strtotime($obj_verifica_alteracao->data_prevista_analise))) || (trim($data_prevista_analise)=="") || $data_prevista_analise<date("d/m/Y"))
    {
      $data_prevista_analise = 'N';
    }

    if(trim($comentario) == "")
    {
      $comentario = 'N';
    }

    if($pizzarias)
    {
      $sql_pizzarias_visiveis = "SELECT * from ipi_comunicacao_tickets_ipi_pizzarias where cod_tickets = '$codigo'";
      $res_pizzarias_visiveis = mysql_query($sql_pizzarias_visiveis);
      while($obj_pizzarias_visiveis = mysql_fetch_object($res_pizzarias_visiveis))
      {
        $pizzarias_atual [] = $obj_pizzarias_visiveis->cod_pizzarias;
      }

      sort( $pizzarias );
      sort( $pizzarias_atual );

      if(($pizzarias == $pizzarias_atual) || (in_array('0',$pizzarias) && (in_array('0',$pizzarias_atual)) ) )
      {
        $pizzarias = 'N';
      }
    }

    $controle_alteracoes = true;
    $a = 0;
    if($obs_franqueadora!='N')
    {
      $controle_alteracoes &= $this->alterar_obs_franqueadora($codigo,$obs_franqueadora,$codigo_usuario_alteracao);
      //$a++;
    }

    if($tempo_desenvolvimento!='N')
    {
      $controle_alteracoes &= $this->alterar_tempo_desenvolvimento($codigo,$tempo_desenvolvimento,$codigo_usuario_alteracao);
     // $a++;
    }

    if($tempo_trabalhado!='N')
    {
      $controle_alteracoes &= $this->alterar_tempo_trabalhado($codigo,$tempo_trabalhado,$codigo_usuario_alteracao);
     // $a++;
    }

    if($prioridade!='N')
    {
      $controle_alteracoes &= $this->alterar_prioridade($codigo,$prioridade,$codigo_usuario_alteracao);
    //  $a++;
    }

    if($cod_situacoes!='N')
    {
      $controle_alteracoes &= $this->alterar_situacao($codigo,$cod_situacoes,$codigo_usuario_alteracao,false);
      $a++;
    }

    if($cod_subcategorias!='N')
    {
      $controle_alteracoes &= $this->alterar_subcategoria($codigo,$cod_subcategorias,$codigo_usuario_alteracao);
      $a++;
    }

    if($pizzarias!='N')
    {
      $controle_alteracoes &= $this->alterar_visibilidade_pizzarias($codigo,$pizzarias,$codigo_usuario_alteracao);
      $a++;
    }

    if($comentario!='N')
    {
      $controle_alteracoes &= $this->comentar($codigo,$codigo_usuario_alteracao,$comentario,false);
      $a++;
    }

    if($data_prevista !='N')
    {
      $controle_alteracoes &= $this->alterar_data_prevista($codigo,$codigo_usuario_alteracao,$data_prevista);
      $a++;
    }
    if($data_prevista_analise !='N')
    {
      $controle_alteracoes &= $this->alterar_data_prevista_analise($codigo,$codigo_usuario_alteracao,$data_prevista_analise);
      $a++;
    }

    if($arquivos!='N')
    {
      if($arquivos!='')
      {
        $qtd_arqs = count($arquivos['name']);
        $sucesso_uploads = true;
        $subidos = '';
        for($i = 0;$i<$qtd_arqs;$i++)
        {
          if(count($arquivos['name'][$i]) > 0) 
          {     
            if(trim($arquivos['name'][$i]) != '') 
            {
              $arq_info = pathinfo($arquivos['name'][$i]);
              $arq_temp_nome = $arquivos['tmp_name'][$i];
              //echo "<br/>mandou upload $codigo,$arq_info,$arq_temp_nome";
              $controle_alteracoes &= $this->upload_arquivo($codigo,$codigo_usuario_alteracao,$arq_info,$arq_temp_nome);
              $a++;
              $subidos[] = $arquivos['name'][$i];
            }          
          }
        }
      }
      else
      {
        $subidos = 'N';
      }
    }
    else
    {
      $subidos = 'N';
    }

    if($subidos=='')
    {
      $subidos = 'N';
    }
    //$array_cods = array($cod_situacoes,$cod_subcategorias,$pizzarias,$comentario,$data_prevista,$subidos);


    if($controle_alteracoes && $a>0)
    {
      $this->enviar_email($codigo,'ALTERACAO_TICKET',$codigo_usuario_alteracao);
    }
    else
    {
      if($controle_alteracoes && $a==0)
      {
        return true;
      }
      else
      {
        throw new Exception('Erro ao editar ticket.Por favor tente novamente Erro núm.: ' . mysql_errno());
        return false;
      }
    }
    return true;
  }
  /**
  * Alterara a subcategoria do ticket no banco de dados.
  *
  * @param string Colocar o codigo do ticket, o codigo para qual ira mudar a categoria e o codigo de quem fez a alteracao
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
 	private function alterar_subcategoria($codigo,$cod_subcategorias,$codigo_usuario_alteracao)
 	{
    $sql_edicao = sprintf("UPDATE ipi_comunicacao_tickets SET cod_ticket_subcategorias = '%s' WHERE cod_tickets = $codigo", $cod_subcategorias);
    $res_edicao_subcategorias = mysql_query($sql_edicao);

    if(!$res_edicao_subcategorias)
    {
      throw new Exception('Erro ao enviar ao alterar a categoria , por favor tente novamente. Erro núm.: ' . mysql_errno());
      return false;
    }
  
    $this->logar($codigo,$codigo_usuario_alteracao,"TROCA_CATEGORIA",$cod_subcategorias);
    return true;
  }

  //
  /**
  * Editar a visibilidade(pizzarias) do ticket no banco de dados.
  *
  * @param string Colocar o codigo do ticket, um array com as pizzarias(caso for mais de uma) e o codigo do usuario a alterar
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function alterar_visibilidade_pizzarias($codigo,$pizzarias,$codigo_usuario_alteracao)
  {
    if($pizzarias)
    {
      $sql_dropar = "DELETE FROM ipi_comunicacao_tickets_ipi_pizzarias where cod_tickets = '$codigo'";
      $res_dropar = mysql_query($sql_dropar);
      if($res_dropar)
      {
        $res_inserir_pizzarias = true;

        if(is_array($pizzarias))
        {
          if(in_array('0',$pizzarias))
          {
            $sql_inserir_pizzarias = "insert into ipi_comunicacao_tickets_ipi_pizzarias(cod_tickets,cod_pizzarias) values(".$codigo.",'0')";
            $res_inserir_pizzarias &= mysql_query($sql_inserir_pizzarias);
            $cods[] = 0;
          }
          else
          {        
            for($i = 0; $i<count($pizzarias);$i++)
            {
              $sql_inserir_pizzarias = "insert into ipi_comunicacao_tickets_ipi_pizzarias(cod_tickets,cod_pizzarias) values(".$codigo.",'".$pizzarias[$i]."')";
              $res_inserir_pizzarias &= mysql_query($sql_inserir_pizzarias);
              $cods[] = $pizzarias[$i];
            }
          }
        }
        else
        {
          $sql_inserir_pizzarias = "insert into ipi_comunicacao_tickets_ipi_pizzarias(cod_tickets,cod_pizzarias) values(".$codigo.",'".$pizzarias."')";
          $res_inserir_pizzarias &= mysql_query($sql_inserir_pizzarias);
          $cods[] = $pizzarias;
        }

        $this->logar($codigo,$codigo_usuario_alteracao,'TROCA_PIZZARIA',implode(',',$cods));
      }
      else
      {
        throw new Exception('Erro ao enviar ao alterar as visibilidade(pizzariasd), por favor tente novamente. Erro núm.: ' . mysql_errno());
        return false;
      }
    }

    if(!$res_inserir_pizzarias)
    {
      throw new Exception('Erro ao inserir as pizzarias, por favor tente novamente. Erro núm.: ' . mysql_errno());
      return false;
    }
    return true;
 	}

  /**
  * Editar o tempo de desenvolvimento no banco de dados.
  *
  * @param string Colocar o codigo do ticket,o codigo da situacao para qual ira mudar , codigo do usuario que requisitou a alteração e um
  * bool que controla se envia email ou não (FIXME))
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function alterar_tempo_trabalhado($codigo,$tempo_trabalhado,$usuario_alteracao)
  { 
    $sql_edicao = sprintf("UPDATE ipi_comunicacao_tickets SET tempo_trabalhado = '%s' WHERE cod_tickets = $codigo", $tempo_trabalhado);
    $res_edicao = mysql_query($sql_edicao);

    if($res_edicao)
    {
      $this->logar($codigo,$usuario_alteracao,'TEMPO_TRABALHADO',$tempo_trabalhado);
      return true;
    }
    else
    {
      return false;
    }
  
  }

  /**
  * Editar o tempo de desenvolvimento no banco de dados.
  *
  * @param string Colocar o codigo do ticket,o codigo da situacao para qual ira mudar , codigo do usuario que requisitou a alteração e um
  * bool que controla se envia email ou não (FIXME))
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function alterar_tempo_desenvolvimento($codigo,$tempo_desenvolvimento,$usuario_alteracao)
  { 
    $sql_edicao = sprintf("UPDATE ipi_comunicacao_tickets SET tempo_desenvolvimento = '%s' WHERE cod_tickets = $codigo", $tempo_desenvolvimento);
    $res_edicao = mysql_query($sql_edicao);

    if($res_edicao)
    {
      $this->logar($codigo,$usuario_alteracao,'TEMPO_DESENVOLVIMENTO',$tempo_desenvolvimento);
      return true;
    }
    else
    {
      return false;
    }
  
  }

    /**
  * Editar a prioridade no banco de dados.
  *
  * @param string Colocar o codigo do ticket,o codigo da situacao para qual ira mudar , codigo do usuario que requisitou a alteração e um
  * bool que controla se envia email ou não (FIXME))
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function alterar_prioridade($codigo,$cod_prioridade,$usuario_alteracao)
  { 
    $sql_edicao = sprintf("UPDATE ipi_comunicacao_tickets SET cod_prioridades = '%s' WHERE cod_tickets = $codigo", $cod_prioridade);
    $res_edicao = mysql_query($sql_edicao);

    if($res_edicao)
    {
      $this->logar($codigo,$usuario_alteracao,'COD_PRIORIDADES',$cod_prioridade);
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
  * Editar a situacao no banco de dados.
  *
  * @param string Colocar o codigo do ticket,o codigo da situacao para qual ira mudar , codigo do usuario que requisitou a alteração e um
  * bool que controla se envia email ou não (FIXME))
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function alterar_obs_franqueadora($codigo,$obs_franqueadora,$usuario_alteracao)
  { 

    $sql_edicao = sprintf("UPDATE ipi_comunicacao_tickets SET observacao_franqueadora = '%s' WHERE cod_tickets = $codigo", $obs_franqueadora);
    $res_edicao = mysql_query($sql_edicao);

    if($res_edicao)
    {
      $this->logar($codigo,$usuario_alteracao,'OBS_FRANQUEADORA',$obs_franqueadora);
      return true;
    }
    else
    {
      return false;
    }
    
  }

  /**
  * Editar a situacao no banco de dados.
  *
  * @param string Colocar o codigo do ticket,o codigo da situacao para qual ira mudar , codigo do usuario que requisitou a alteração e um
  * bool que controla se envia email ou não (FIXME))
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
 	private function alterar_situacao($codigo,$cod_situacao_nova,$usuario_alteracao,$email = true)
 	{	
    if($cod_situacao_nova==2)
    {
      $this->fechar_ticket($codigo,$usuario_alteracao);
    }
    else
    {
      $sql_edicao = sprintf("UPDATE ipi_comunicacao_tickets SET cod_situacoes = '%s' WHERE cod_tickets = $codigo", $cod_situacao_nova);
      $res_edicao = mysql_query($sql_edicao);

      if($res_edicao)
      {
        $this->logar($codigo,$usuario_alteracao,'TROCA_SITUACAO',$cod_situacao_nova);
      	return true;
      }
      else
      {
      	return false;
      }
    }
 	}

  /**
  * Fechar o ticket.
  *
  * @param string Colocar o codigo do ticket, o codigo do usuario que requisitou o fechamento
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
 	public function fechar_ticket($codigo,$codigo_usuario)
 	{
    $cod_tickets = validaVarPost('cod_tickets');
    $sql_edicao = "UPDATE ipi_comunicacao_tickets SET cod_situacoes = 2 WHERE cod_tickets = $codigo";
    $res_edicao = mysql_query($sql_edicao);
    if($res_edicao)
    {
      $this->logar($codigo,$codigo_usuario,'TROCA_SITUACAO','2');
    	$this->enviar_email($codigo,"FECHAMENTO_TICKET",$codigo_usuario);
    	return true;
    }
    else
    {
    	throw new Exception('Erro ao cadastrar novo ticket no banco de dados. Erro núm.: ' . mysql_errno());
      return false;
    }
 	}
  /**
  * Enviar um comentario em um ticket.
  *
  * @param string Colocar o codigo do ticket,o codigo do usuario que comentou, o comentario, um bool para controle de envio de email, e um array com os arquivos a serem subidos
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
 	public function comentar($codigo,$codigo_usuario_comentou,$comentario,$email = true,$arquivos = '')
 	{
    /*echo "<pre>";
    print_r($arquivos);
    echo "</pre>";
    die();*/
    $sql_inserir_comentario = "insert into ipi_comunicacao_tickets_comentarios(cod_tickets,cod_usuarios,comentario,status,data_hora_comentario) values('".$codigo."','".$codigo_usuario_comentou."','".$comentario."','ATIVO',NOW())";
    $res_inserir_comentario = mysql_query($sql_inserir_comentario);

    if($res_inserir_comentario)
    {
    	$codigo_comentario = mysql_insert_id();
      $this->logar($codigo,$codigo_usuario_comentou,'COMENTARIO',$codigo_comentario);
    	$alterar = true;

      if($arquivos!='')
      {
        $qtd_arqs = count($arquivos['name']);
        $sucesso_uploads = true;
        $subidos = '';
        for($i = 0;$i<$qtd_arqs;$i++)
        {
          if(count($arquivos['name'][$i]) > 0) 
          {     
            if(trim($arquivos['name'][$i]) != '') 
            {
              $arq_info = pathinfo($arquivos['name'][$i]);
              $arq_temp_nome = $arquivos['tmp_name'][$i];
              //echo "<br/>mandou upload $codigo,$arq_info,$arq_temp_nome";
              $sucesso_uploads &= $this->upload_arquivo($codigo,$codigo_usuario_comentou,$arq_info,$arq_temp_nome,$codigo_comentario);
              $subidos[] = $arquivos['name'][$i];
            }          
          }
        }
      }

	    if($alterar)
	    {
        if($email)
        {
  	    	$this->enviar_email($codigo,"COMENTARIO_TICKET",$codigo_usuario_comentou,$codigo_comentario,$subidos);
        }
	    	return true;
    	}
      
    }
    else
    {
    	throw new Exception('Erro ao enviar o comentario, por favor tente novamente. Erro núm.: ' . mysql_errno());
      return false;
    }

 	}
  /**
  * Editar o ticket no banco de dados.
  *
  * @param string Colocar o codigo do ticket, o codigo do usuario que alterou  e a data prevista para alterar
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function alterar_data_prevista($codigo,$codigo_usuario_alteracao,$data_prevista)
  {

    $sql_alterar_data = sprintf("UPDATE ipi_comunicacao_tickets set data_prevista = '%s' WHERE cod_tickets = '$codigo' ",data2bd($data_prevista));
    $res_alterar_data = mysql_query($sql_alterar_data);

    if($res_alterar_data)
    {
      $this->logar($codigo,$codigo_usuario_alteracao,"TROCA_DATA_PREVISTA",$data_prevista);
      return true;
    }
    else
    {
      throw new Exception('Erro ao alterar data_prevista, por favor tente novamente. Erro núm.: ' . mysql_errno());
      return false;
    }
  }

  private function alterar_data_prevista_analise($codigo,$codigo_usuario_alteracao,$data_prevista_analise)
  {
    $sql_alterar_data = sprintf("UPDATE ipi_comunicacao_tickets set data_prevista_analise = '%s' WHERE cod_tickets = '$codigo' ",data2bd($data_prevista_analise));
    $res_alterar_data = mysql_query($sql_alterar_data);

    if($res_alterar_data)
    {
      $this->logar($codigo,$codigo_usuario_alteracao,"TROCA_DATA_PREVISTA_ANALISE",$data_prevista_analise);
      return true;
    }
    else
    {
      throw new Exception('Erro ao alterar data_prevista, por favor tente novamente. Erro núm.: ' . mysql_errno());
      return false;
    }
  }

 	//FIXME alterar a alteração para o cod de auteração quando o log for feito
  /**
  * Editar o ticket no banco de dados.
  *
  * @param string Colocar o codigo do ticket,o tipo de email(para ele montar o cabeçaçho) o codigo do usuario que fez a ação que disparou o email, codigo auxiliar cadastro seria uma variavel extra caso seja necessario algum select dentro da query, e o cod log seria o cod do log para pesquisar  e mandar as alterações
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function enviar_email($codigo,$tipo_email,$codigo_usuario_acao = '0',$codigo_auxiliar_cadastro = '0',$cod_log = '0')
  {
    $sql_selecionar_ticket = "select t.*,cc.nome_categoria,cc.emails_associados,ccs.nome_subcategoria,ccs.emails_associados as sub_emails_associados from ipi_comunicacao_tickets t inner join ipi_comunicacao_subcategorias ccs on t.cod_ticket_subcategorias = ccs.cod_ticket_subcategorias inner join ipi_comunicacao_categorias cc on cc.cod_categorias = ccs.cod_categorias where t.cod_tickets='$codigo'";
    //echo $sql_selecionar_ticket;
    $res_selecionar_ticket = mysql_query($sql_selecionar_ticket);
    $obj_selecionar_ticket = mysql_fetch_object($res_selecionar_ticket);

    $sql_busca_usuario = "select nome from nuc_usuarios where cod_usuarios = '".($codigo_usuario_acao!="0" ? $codigo_usuario_acao : $obj_selecionar_ticket->cod_usuarios)."'";
    $res_busca_usuario = mysql_query($sql_busca_usuario);
    $obj_busca_usuario = mysql_fetch_object($res_busca_usuario);
    $nome_usuario = $obj_busca_usuario->nome;

    if($cod_log>0 || $this->cod_log>0)
    {
      $sql_busca_alteracoes = "SELECT * from ipi_comunicacao_tickets_log where cod_logs in('$cod_log',".$this->cod_log.")";
      $res_busca_alteracoes = mysql_query($sql_busca_alteracoes);
      $z = 0;
      while($obj_busca_alteracoes = mysql_fetch_object($res_busca_alteracoes))
      {
        $arr_alteracoes[$z][0] = $obj_busca_alteracoes->tipo_alteracao;
        $arr_alteracoes[$z][$obj_busca_alteracoes->tipo_alteracao] = $obj_busca_alteracoes->valor_alteracao;
        $z++;
      }
     // echo "<pre>";
      //print_r($arr_alteracoes);
      //echo "</pre>";
      for($a = 0;$a<count($arr_alteracoes);$a++)
      {
        //echo "//".$arr_alteracoes[$a][0]."<br/>";
        if($arr_alteracoes[$a][0]=="COMENTARIO")
        {
          $sql_busca_comentario = "SELECT * FROM ipi_comunicacao_tickets_comentarios where cod_comentarios=".$arr_alteracoes[$a]["COMENTARIO"];
          $res_busca_comentario = mysql_query($sql_busca_comentario);
          $obj_busca_comentario = mysql_fetch_object($res_busca_comentario);
          $comentario = "<strong>Comentou: </strong><br/>".$obj_busca_comentario->comentario."<br/>";
        }
        elseif($arr_alteracoes[$a][0]=="TROCA_SITUACAO")
        {
          $sql_buscar_situacao = "SELECT * from ipi_comunicacao_situacoes where cod_situacoes = ".$arr_alteracoes[$a]["TROCA_SITUACAO"];
          $res_buscar_situacao = mysql_query($sql_buscar_situacao);
          $obj_buscar_situacao = mysql_fetch_object($res_buscar_situacao);
          $situacao =  "<strong>Situação</strong> alterada para ".$obj_buscar_situacao->nome_situacao."<br/>";
        }
        elseif($arr_alteracoes[$a][0]=="TROCA_PIZZARIA")
        {
          if($arr_alteracoes[$a]["TROCA_PIZZARIA"]=='0')
          {
            $pizzaria =  "<strong>Visibilidade</strong> do ticket habilitada para todas pizzarias:<br/>";
          }
          else
          {
            $sql_buscar_pizzarias = "SELECT * from ipi_pizzarias where cod_pizzarias in(".$arr_alteracoes[$a]["TROCA_PIZZARIA"].")";
            $res_buscar_pizzarias = mysql_query($sql_buscar_pizzarias);

            $pizzaria =  "<strong>Visibilidade</strong> do ticket habilitada para a seguinte pizzarias:<br/>";
            while($obj_buscar_pizzarias = mysql_fetch_object($res_buscar_pizzarias))
            {
              $pizzaria .=  "&nbsp;&nbsp;".$obj_buscar_pizzarias->cidade." - ".$obj_buscar_pizzarias->bairro.":<br/>";
            }
          }
        }
        elseif($arr_alteracoes[$a][0]=="TROCA_DATA_PREVISTA")
        {
          $data_prevista = "<strong>Data Prevista :</strong> alterada para ".$arr_alteracoes[$a]["TROCA_DATA_PREVISTA"]."<br/>";
        }
        elseif($arr_alteracoes[$a][0]=="TROCA_DATA_PREVISTA_ANALISE")
        {
          $data_prevista = "<strong>Data Prevista de termino da análise :</strong> alterada para ".$arr_alteracoes[$a]["TROCA_DATA_PREVISTA_ANALISE"]."<br/>";
        }
        elseif($arr_alteracoes[$a][0]=="TROCA_CATEGORIA")
        {
          $sql_buscar_subcategoria = "SELECT sub.*,cat.nome_categoria from ipi_comunicacao_subcategorias sub inner join ipi_comunicacao_categorias cat on cat.cod_categorias = sub.cod_categorias where sub.cod_ticket_subcategorias = ".$arr_alteracoes[$a]["TROCA_CATEGORIA"];
          $res_buscar_subcategoria = mysql_query($sql_buscar_subcategoria);
          $obj_buscar_subcategoria = mysql_fetch_object($res_buscar_subcategoria);

          $categoria =  "<strong>Categoria</strong> alterada para ".$obj_buscar_subcategoria->nome_categoria." - ".$obj_buscar_subcategoria->nome_subcategoria."<br/>";
        }
        elseif($arr_alteracoes[$a][0]=="UPLOAD_ARQUIVO")
        {
            $arquivo .="&nbsp;&nbsp;".$arr_alteracoes[$a]["UPLOAD_ARQUIVO"]."<br/>";
        }
        elseif($arr_alteracoes[$a][0]=="UPLOAD_COMENTARIO")
        {
            $arquivoc .="&nbsp;&nbsp;".$arr_alteracoes[$a]["UPLOAD_COMENTARIO"]."<br/>";
        }
      }
      if($arquivo || $arquivoc)
      {
        $arquivos = '<strong>Adicionados os seguintes arquivos: </strong><br/>';
        $arquivos .=$arquivo."".$arquivoc;
      }
      $alteracoes = $categoria."".$situacao."".$data_prevista."".$pizzaria."".$arquivos."".$comentario;
     // echo $alteracoes;die();
    }
    else
      $alteracoes = "";


    $assunto = NOME_FANTASIA.'- Canal comunicação - Ticket #'.sprintf("%04d",$obj_selecionar_ticket->cod_tickets).'-'.$obj_selecionar_ticket->titulo_ticket;
    switch($tipo_email)
    {
    	case 'TICKET_NOVO':
  	    //$assunto = 'Os Muzzarellas - Canal comunicação - Novo Ticket #'.sprintf("%04d",$obj_selecionar_ticket->cod_tickets).'-'.$obj_selecionar_ticket->titulo_ticket.' na categoria '.$obj_selecionar_ticket->nome_categoria;

		    $mensagem_email = "Ticket criado por $nome_usuario, as ".date('H:i',strtotime($obj_selecionar_ticket->data_hora_ticket))." de ".date('d/m/Y',strtotime($obj_selecionar_ticket->data_hora_ticket));
		    $mensagem_email .= "<br/><br/>";
		    $mensagem_email .= "<strong>Nome do ticket: </strong>".$obj_selecionar_ticket->titulo_ticket;
		    $mensagem_email .= "<br/><strong>Categoria Pai do ticket: </strong>".$obj_selecionar_ticket->nome_categoria;
        $mensagem_email .= "<br/><strong>SubCategoria do ticket: </strong>".$obj_selecionar_ticket->nome_subcategoria;
		    $mensagem_email .= "<br/><strong>Mensagem do ticket: </strong>".bd2texto(nl2br($obj_selecionar_ticket->mensagem_ticket));
		    
        $sql_busca_uploads = "select * from ipi_comunicacao_tickets_arquivos where cod_tickets = '$codigo'";
        $res_busca_uploads = mysql_query($sql_busca_uploads);
        $arquivos = '';
        $i = 0;
        while($obj_busca_uploads = mysql_fetch_object($res_busca_uploads))
        {
          $i++;
          $arquivos .=$i.'º arquivo:'.$obj_busca_uploads->nome_arquivo.'<br/>';
        }

		    if($arquivos)
		      $mensagem_email .="<br/><strong>Arquivos: </strong><br/>".$arquivos;

		    $mensagem_email .= "</br><br/>";
		    //$mensagem_email .= ""
	    break;
	    case 'COMENTARIO_TICKET':
	    	$sql_buscar_comentario = "SELECT * from ipi_comunicacao_tickets_comentarios where cod_comentarios='".$codigo_auxiliar_cadastro."'";
	    	$res_buscar_comentario = mysql_query($sql_buscar_comentario);
	    	$obj_buscar_comentario = mysql_fetch_object($res_buscar_comentario);

        //$assunto = 'Os Muzzarellas - Canal comunicação - Novo Comentário em #'.sprintf("%04d",$obj_selecionar_ticket->cod_tickets).'-'.$obj_selecionar_ticket->titulo_ticket;

        $mensagem_email = "Novo comentario feito no ticket #".sprintf("%04d",$obj_selecionar_ticket->cod_tickets)."-".$obj_selecionar_ticket->titulo_ticket." <br/>por $nome_usuario<br/>";
        $mensagem_email .= $alteracoes;

	    break;
	    case 'ALTERACAO_TICKET':
        //$assunto = 'Os Muzzarellas - Canal comunicação - Ticket #'.sprintf("%04d",$obj_selecionar_ticket->cod_tickets).'-'.$obj_selecionar_ticket->titulo_ticket." Alterado por ".$nome_usuario;

        $mensagem_email = "Ticket #".sprintf("%04d",$obj_selecionar_ticket->cod_tickets)."-".$obj_selecionar_ticket->titulo_ticket;
        $mensagem_email .= "<br/>";
        $mensagem_email .= "Alterado em ".date("d/m/Y")." as ".date("H:i")."<br/>";
        $mensagem_email .= "$nome_usuario alterou as seguintes informações :<br/>";

        $mensagem_email .= $alteracoes;
        $mensagem_email .= "</br><br/>";
	    break;
	    case 'FECHAMENTO_TICKET':
        //$assunto = 'Os Muzzarellas - Canal comunicação - Ticket #'.sprintf("%04d",$obj_selecionar_ticket->cod_tickets).'-'.$obj_selecionar_ticket->titulo_ticket." Fechado por ".$nome_usuario;

        $mensagem_email = "Ticket #".sprintf("%04d",$obj_selecionar_ticket->cod_tickets)."-".$obj_selecionar_ticket->titulo_ticket;
        $mensagem_email .= "<br/>";
        $mensagem_email .= "Fechado por $nome_usuario em ".date("d/m/Y")." as ".date("H:i")."<br/>";
        $mensagem_email .= $alteracoes;
        $mensagem_email .= "</br><br/>";
	    break;
  	}

    //$emails_recebimento = 'thiago@internetsistemas.com.br';

    $sql_selecionar_email = "select tp.cod_pizzarias,p.emails_diretoria from ipi_comunicacao_tickets_ipi_pizzarias tp left join ipi_pizzarias p on tp.cod_pizzarias = p.cod_pizzarias where tp.cod_tickets = '$codigo' order by tp.cod_pizzarias";
    //echo $sql_selecionar_email;
    $res_selecionar_email = mysql_query($sql_selecionar_email);
    $obj_selecionar_email = mysql_fetch_object($res_selecionar_email);
    if($obj_selecionar_email->cod_pizzarias == '0')
    {
      $sql_selecionar_email = "select * from ipi_pizzarias where situacao='ATIVO'";
      $res_selecionar_email = mysql_query($sql_selecionar_email);
      //echo $sql_selecionar_email;
      while($obj_selecionar_email = mysql_fetch_object($res_selecionar_email))
      {
        if ($obj_selecionar_email->emails_diretoria)
        {
          $emails_recebimento .= ($emails_recebimento ? "," : '').$obj_selecionar_email->emails_diretoria;
        }
      }
    }else
    {
      //echo "EWLSSE";
      if ($obj_selecionar_email->emails_diretoria)
      {
        $emails_recebimento .= ($emails_recebimento ? "," : '').$obj_selecionar_email->emails_diretoria;
      }
      while($obj_selecionar_email = mysql_fetch_object($res_selecionar_email))
      {
        if ($obj_selecionar_email->emails_diretoria)
        {
          $emails_recebimento .= ($emails_recebimento ? "," : '').$obj_selecionar_email->emails_diretoria;
        }
      }
    }

    if($obj_selecionar_ticket->emails_associados)
    {
      $emails_recebimento .= ($emails_recebimento ? "," : '').$obj_selecionar_ticket->emails_associados;
    }
    if($obj_selecionar_ticket->sub_emails_associados)
    {
      $emails_recebimento .= ($emails_recebimento ? "," : '').$obj_selecionar_ticket->sub_emails_associados;
    }
    if($obj_busca_usuario->email)
    {
      $emails_recebimento .= ($emails_recebimento ? "," : '').$obj_busca_usuario->email;
    }
    $mensagem_email .= $emails_recebimento;
    $emails_recebimento = explode(',',$emails_recebimento);
    $emails_recebimento = implode(',',array_unique($emails_recebimento));
    $emails_recebimento = "filipegranato@internetsistemas.com.br";
    //$mensagem_email .= $emails_recebimento;

    $arr_aux = array();
    $arr_aux['cod_pedidos'] = 0;
    $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
    $arr_aux['cod_clientes'] = 0;
    $arr_aux['cod_pizzarias'] = 0;
    $arr_aux['tipo'] = $tipo_email;
    $res_enviar_email &= enviar_email (EMAIL_PRINCIPAL, EMAIL_PRINCIPAL, $assunto, $mensagem_email, $arr_aux, 'neutro',$emails_recebimento,false,true);
  }

  /**
  * Faz o uploade de arquivos e salva os seus respectivos nomes no banco de dados.
  *
  * @param string Colocar o codigo do ticket, o codigo do usuario que fez o upload, as o file_info do arquivo, o nome temporario dele, e o codigo do comentario caso o uplaod dele tenha sido feito por um comentario
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function upload_arquivo($codigo,$codigo_usuario_acao,$arquivo_info,$arq_temp_nome,$cod_comentarios ='0')
  {
		$arq_ext = $arquivo_info['extension'];
		$arq_nome = $arquivo_info['filename'];
    $resUploadArquivo = true;

    $nome_salvo = "${codigo}_${arq_nome}".date("his").".${arq_ext}";

    $resUploadArquivo &= move_uploaded_file($arq_temp_nome, UPLOAD_DIR."/comunicacao/suporte/$nome_salvo");
    //echo "<br/>".($resUploadArquivo ? 'move deu' : 'move falhou');	  
    $SqlEdicaoImagem = sprintf("INSERT into ipi_comunicacao_tickets_arquivos(cod_tickets,cod_comentarios,nome_arquivo,descricao_arquivo,data_hora_adicao) values (%d,%d,'%s','',NOW())", 
               $codigo,$cod_comentarios,texto2bd($nome_salvo));
    //echo "<br/> qeury ypload  ".$SqlEdicaoImagem."<br/><br/>";

    $resUploadArquivo &= mysql_query($SqlEdicaoImagem);
    if($resUploadArquivo)
    {
    	return true;
    }
    else
    {
    	return false;
    }
  }

  public function tempo_passado_ticket($cod_tickets, $precisao = 3)
  {
    $sql_buscar_logs = "SELECT * from ipi_comunicacao_tickets_log where cod_tickets = '".$cod_tickets."' and tipo_alteracao='TROCA_SITUACAO' order by data_hora_alteracao DESC";
    //echo $sql_buscar_logs;
    $res_buscar_logs = mysql_query($sql_buscar_logs);
    $obj_buscar_logs = mysql_fetch_object($res_buscar_logs);

    return $this->tempo_passado(strtotime($obj_buscar_logs->data_hora_alteracao),$precisao);
  }

  /*private function diff_segundos($obj)
  {
    return ($obj->y * 365 * 24 * 60 * 60) +
           ($obj->m * 30 * 24 * 60 * 60) +
           ($obj->d * 24 * 60 * 60) +
           ($obj->h * 60 *60) +
           $obj->s;
  }*/
      
  private function tempo_passado($timestamp, $precision = 2) 
  {
    $time = time() - $timestamp;
    /*echo "<br/>".time();
    echo "<br/>".$timestamp;
    echo "<br/>".$time."<br/>";*/
    //$time = $timestamp;
    $a = array('decada' => 315576000, 'ano' => 31557600, 'mês' => 2629800, 'semana' => 604800, 'dia' => 86400, 'hora' => 3600, 'min' => 60, 'seg' => 1);
    $i = 0;
      foreach($a as $k => $v) {
        $$k = floor($time/$v);
        if ($$k) $i++;
        $time = $i >= $precision ? 0 : $time - $$k * $v;
        $s = $$k > 1 ? 's' : '';
        $$k = $$k ? $$k.' '.$k.$s.' ' : '';
        @$result .= $$k;
      }
    return $result ? $result.'' : '1 sec atrás';//atrás
  } 

  public function calcular_kpi($codigo,$tipo_kpi = '0')
  {
    switch ($tipo_kpi) 
    {
      case '1':
        $sql_buscar_trocas = "SELECT (SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao=".$this->cod_situacao_novo.") as data_hora_inicial,(SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao in(".$this->cod_situacao_andamento.",".$this->cod_situacao_aguardando.",".$this->cod_situacao_analise.") and cod_usuarios_alteracao = '1' order by data_hora_alteracao ASC LIMIT 1) as data_hora_resposta";

        //echo "<br/><br/><br/>".$sql_buscar_trocas."<br/>";

        
      
        //,(SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao='".$cod_situacao_novo."')

        $res_buscar_trocas = mysql_query($sql_buscar_trocas);
        $obj_buscar_trocas = mysql_fetch_object($res_buscar_trocas);
        //echo "<br/>".$obj_buscar_trocas->data_hora_inicial."----".$obj_buscar_trocas->data_hora_resposta;

        return calcular_kpi1($obj_buscar_trocas->data_hora_inicial,$obj_buscar_trocas->data_hora_resposta);
      break;
      case '2':
        $sql_buscar_tickets = "SELECT * from ipi_comunicacao_tickets t where t.cod_tickets = '$codigo'";
        $res_buscar_tickets = mysql_query($sql_buscar_tickets);
        $obj_buscar_tickets = mysql_fetch_object($res_buscar_tickets);

        $sql_buscar_trocas = "SELECT (SELECT t.data_prevista from ipi_comunicacao_tickets t where t.cod_tickets = '".$codigo."') as data_hora_prevista,(SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao in(".$this->cod_situacao_resolvido.") order by data_hora_alteracao ASC LIMIT 1) as data_hora_resolvido";
        //echo "<br/>".$sql_buscar_trocas."<br/>";
        //,(SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao='".$cod_situacao_novo."')

        $res_buscar_trocas = mysql_query($sql_buscar_trocas);
        $obj_buscar_trocas = mysql_fetch_object($res_buscar_trocas);

        $data1 = strtotime($obj_buscar_trocas->data_hora_prevista);//data prevista do ticket
        $data2 = strtotime($obj_buscar_trocas->data_hora_resolvido);//data em que a situação foi colcada como resolvido
       // echo "<br/><br/> Codigo $codigo";
        //echo "<br/>d1-".date("Y-m-d",$data1).' 23:59:59'."-a</br>";
        //echo "<br/>d2-".date('Y-m-d H:i:s',$data2)."-a</br>";
        
        if(date("Y-m-d",$data1).'00:00:00'<=date("Y-m-d H:i:s") && date("Y-m-d",$data2) == "1969-12-31")
        {
         // echo "ATRASADO<br/>";
          return 0;
        }

        if(date("Y-m-d H:i:s",$data2)<=date("Y-m-d",$data1).' 23:59:59')
        {
         // echo "DENTOR<br/>";
          return 1;
        }
        else
        {
          //echo "Fora<br/>";
          return 0;
        }


        /*echo "<br/>a-".$kpi2->d."-a</br>";
        echo "<br/>a-".$kpi2->h."-a</br>";*/
       // return $kpi2;
      break;
      case '3':
        $sql_buscar_trocas = "SELECT (SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao='".$this->cod_situacao_retrabalho."') as data_hora_retrabalho,(SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao in(".$this->cod_situacao_resolvido.") order by data_hora_alteracao ASC) as data_hora_resolvido";
        echo "<br/>".$sql_buscar_trocas."<br/>";
        //,(SELECT log.data_hora_alteracao from ipi_comunicacao_tickets_log log where cod_tickets = '".$codigo."' and tipo_alteracao='TROCA_SITUACAO' and valor_alteracao='".$cod_situacao_novo."')

        $res_buscar_trocas = mysql_query($sql_buscar_trocas);
        $obj_buscar_trocas = mysql_fetch_object($res_buscar_trocas);

        $data1 = date_create($obj_buscar_trocas->data_hora_retrabalho);
        $data2 = date_create($obj_buscar_trocas->data_hora_resolvido ? date("Y-m-d",strtotime($obj_buscar_trocas->data_hora_resolvido)) :  $obj_buscar_trocas->data_hora_resolvido  );

        $kpi2 = date_diff($data1,$data2);

        echo "<br/>d1-".date_format($data1,'Y-m-d H:i:s')."-a</br>";
        echo "<br/>d2-".date_format($data2,'Y-m-d H:i:s')."-a</br>";

        echo "<br/>a-".$kpi2->d."-a</br>";
        echo "<br/>a-".$kpi2->h."-a</br>";
        return $kpi2;
      break;
    }
  }





  /**
  * Loga alguma ação no banco de dados
  *
  * @param string Colocar o codigo do ticket, o codigo do usuario que fez a alteração que esta sendo logada, o tipo de alteração e o valor para qual foi alterado
  * 
  * @return true caso consiga e false caso dê erro.
  *
  */
  private function logar($codigo,$cod_usuarios_acao,$tipo_alteracao,$valor_alteracao)
  {

    if($this->cod_log>0)
    {
      $sql_logar = sprintf("INSERT into ipi_comunicacao_tickets_log(cod_tickets,cod_logs,tipo_alteracao,cod_usuarios_alteracao,valor_alteracao,data_hora_alteracao) values(%d,%d,'%s',%d,'%s',NOW())",$codigo,$this->cod_log,$tipo_alteracao,$cod_usuarios_acao,$valor_alteracao);
      $res_logar = mysql_query($sql_logar);
    }
    else
    {
      $sql_busca_cod_log = "SELECT max(cod_logs) as cod_logs from ipi_comunicacao_tickets_log";
      $res_busca_cod_log = mysql_query($sql_busca_cod_log);
      $obj_busca_cod_log = mysql_fetch_object($res_busca_cod_log);

      if($obj_busca_cod_log->cod_logs>0)
      {
        $this->cod_log = $obj_busca_cod_log->cod_logs;
        $this->cod_log = $this->cod_log+1;
      }
      else
      {
        $this->cod_log = 1;
      }

      $sql_logar = sprintf("INSERT into ipi_comunicacao_tickets_log(cod_tickets,cod_logs,tipo_alteracao,cod_usuarios_alteracao,valor_alteracao,data_hora_alteracao) values(%d,%d,'%s',%d,'%s',NOW())",$codigo,$this->cod_log,$tipo_alteracao,$cod_usuarios_acao,$valor_alteracao);
      $res_logar = mysql_query($sql_logar);

    }

    /*echo "<br/>".$sql_logar;
    die();*/
    if($res_logar)
    {
      return true;
    }
    else
    {
      return false;
    }
  }

}

class CanalDeComunicacao_novidades
{
  public function enviar_email($cod_novidades,$cod_usuario_criador)
  {
    $sql_selecionar_novidade = "select n.*,usu.nome from ipi_comunicacao_novidades n inner join nuc_usuarios usu on usu.cod_usuarios = n.cod_usuarios where cod_novidades = '$cod_novidades'";
    //echo $sql_selecionar_novidade;
    $res_selecionar_novidade = mysql_query($sql_selecionar_novidade);
    $obj_selecionar_novidade = mysql_fetch_object($res_selecionar_novidade);

    $assunto = NOME_FANTASIA.' - Canal comunicação - Novo Comunicado / Novidade';

    //$mensagem_email = "Novo Comunicado / Novidade no canal de comunicação";
    $mensagem_email = "<br/><br/>";
    $mensagem_email .= "<strong>Novidade / Comunidade: </strong>".$obj_selecionar_novidade->titulo_novidade;
    $mensagem_email .= "<br/><strong>Acesse o canal de comunicação para maiores detalhes.</strong>";
  
    $mensagem_email .= "</br><br/>";
        //$mensagem_email .= ""

    //$emails_recebimento = 'thiago@internetsistemas.com.br';12 4 8

    $sql_selecionar_email = "select * from ipi_pizzarias where situacao='ATIVO'";
    $res_selecionar_email = mysql_query($sql_selecionar_email);
    //echo $sql_selecionar_email;
    while($obj_selecionar_email = mysql_fetch_object($res_selecionar_email))
    {
      if ($obj_selecionar_email->emails_diretoria)
      {
        $emails_recebimento .= ($emails_recebimento ? "," : '').$obj_selecionar_email->emails_diretoria;
      }
    }
    $emails_recebimento .= ($emails_recebimento ? "," : '')."suporte.osmuzza@internetsistemas.com.br";
    $mensagem_email .= $emails_recebimento;
    $emails_recebimento = explode(',',$emails_recebimento);
    $emails_recebimento = implode(',',array_unique($emails_recebimento));
    $emails_recebimento = "filipegranato@internetsistemas.com.br";
    //$mensagem_email .= $emails_recebimento;

    $arr_aux = array();
    $arr_aux['cod_pedidos'] = 0;
    $arr_aux['cod_usuarios'] = $_SESSION['usuario']['codigo'];
    $arr_aux['cod_clientes'] = 0;
    $arr_aux['cod_pizzarias'] = 0;
    $arr_aux['tipo'] = $tipo_email;
    $res_enviar_email &= enviar_email (EMAIL_PRINCIPAL, EMAIL_PRINCIPAL, $assunto, $mensagem_email, $arr_aux, 'neutro',$emails_recebimento,false,true);
  }
}
?>
