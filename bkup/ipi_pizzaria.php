<?php

/**
 * ipi_pizzaria.php: Cadastro de Pizzarias
 * 
 * Índice: cod_pizzarias
 * Tabela: ipi_pizzarias
 */

require_once '../../bd.php';
require_once '../lib/php/formatacao.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/mensagem.php';

cabecalho('Cadastro de '.TIPO_EMPRESAS);

$acao = validaVarPost('acao');

$tabela = 'ipi_pizzarias';
$chave_primaria = 'cod_pizzarias';

$dia_semana = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');

switch ($acao)
{
  case 'excluir' :
  $excluir = validaVarPost('excluir');
  $indicesSql = implode(',', $excluir);

  $con = conectabd();

  $SqlDel = "DELETE FROM $tabela WHERE $chave_primaria IN ($indicesSql)";

  if (mysql_query($SqlDel))
    mensagemOk('Os registros selecionados foram excluídos com sucesso!');
  else
    mensagemErro('Erro ao excluir os registros', 'Por favor, verifique se a pizzaria não está reposável por algum bairro (cep).');

  desconectabd($con);
  break;

  case 'editar' :
  $codigo = validaVarPost($chave_primaria);
  $cod_empresas = validaVarPost('cod_empresas');
  $nome = validaVarPost('nome');
  $telefone_1 = validaVarPost('telefone_1');
  $telefone_2 = validaVarPost('telefone_2');
  $telefone_3 = validaVarPost('telefone_3');
  $telefone_4 = validaVarPost('telefone_4');
  $endereco = validaVarPost('endereco');
  $numero = validaVarPost('numero');
  $complemento = validaVarPost('complemento');
  $bairro = validaVarPost('bairro');
  $cidade = validaVarPost('cidade');
  $estado = validaVarPost('estado');
  $cep = validaVarPost('cep');
  $latitude = validaVarPost('latitude');
  $longitude = validaVarPost('longitude');
  $emails_diretoria = validaVarPost('emails_diretoria');
  $cod_formas_pg = validaVarPost('cod_formas_pg');
  $dias_semana = validaVarPost('dias_semana');
  $merchant_id = $_POST['dados_extra']['ifood']['merchant_id'];
  $dados_extra = json_encode($_POST['dados_extra'],true);

  $timezone = validaVarPost('timezone');

  $imagem_g = validaVarFiles('foto_g');
  $imagem_p = validaVarFiles('foto_p');

  $razao_social = validaVarPost('razao_social');
  $nome_fantasia = validaVarPost('nome_fantasia');
  $cnpj = validaVarPost('cnpj');
  $inscricao_estadual = validaVarPost('inscricao_estadual');

  $debug_pedidos = validaVarPost("debug_pedidos");
  $impressao_automatica = validaVarPost('impressao_automatica');

  $num_afiliacao_cartao = validaVarPost('num_afiliacao_cartao');
  $num_gateway_pagamento = validaVarPost('num_gateway_pagamento');
  $situacao = validaVarPost('situacao');

  $horarios = validaVarPost('horarios');
  $horario_inicial = validaVarPost('horario_inicial');
  $horario_final = validaVarPost('horario_final');

  $tempo_entrega = validaVarPost('tempo_entrega');
  $cod_pizzarias_horarios = validaVarPost('cod_pizzarias_horarios');

  $data_inauguracao = data2bd(validaVarPost('data_inauguracao'));
  $chave_cielo = validaVarPost('chave_cielo');

  $con = conectabd();

  if ($codigo <= 0)
  {

    $SqlEdicao = sprintf("INSERT INTO $tabela (cod_empresas, nome, telefone_1, telefone_2, telefone_3, telefone_4, endereco, numero, complemento, bairro, cidade, estado, cep, lat, lon, emails_diretoria, num_afiliacao_cartao, num_gateway_pagamento,chave_cielo, impressao_automatica, razao_social, nome_fantasia, cnpj, inscricao_estadual, data_inauguracao, debug_pedidos, timezone, situacao, dados_extra, merchant_id) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s','%s','%s','%s','%s')", $cod_empresas, $nome, $telefone_1, $telefone_2, $telefone_3, $telefone_4, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep, $latitude, $longitude, $emails_diretoria, $num_afiliacao_cartao, $num_gateway_pagamento,$chave_cielo, $impressao_automatica, $razao_social, $nome_fantasia, $cnpj, $inscricao_estadual, $data_inauguracao, $debug_pedidos, $timezone, $situacao,$dados_extra,$merchant_id);
    $res_pizzarias = mysql_query($SqlEdicao);
    $cod_pizzarias = mysql_insert_id();
            //echo "<Br>1: ".$SqlEdicao;

            // Inserindo as Imagens grandes

    $resEdicaoImagem = true;
    if(count($imagem_g['name']) > 0) {     

      if(trim($imagem_g['name']) != '') {
        $arq_info = pathinfo($imagem_g['name']);
        $arq_ext = $arq_info['extension'];
        if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_g["type"])) {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
        }
        else {                
          $resEdicaoImagem &= move_uploaded_file($imagem_g['tmp_name'], UPLOAD_DIR."/pizzarias/${cod_pizzarias}_piz_g.${arq_ext}");

          $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $cod_pizzarias", 
           texto2bd("${cod_pizzarias}_piz_g.${arq_ext}"));

          $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
        }
      }          
    }

            // Inserindo as Imagens pequena

    if(count($imagem_p['name']) > 0) {     

      if(trim($imagem_p['name']) != '') {
        $arq_info = pathinfo($imagem_p['name']);
        $arq_ext = $arq_info['extension'];
        if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_p["type"])) {
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
        }
        else {                
          $resEdicaoImagem &= move_uploaded_file($imagem_p['tmp_name'], UPLOAD_DIR."/pizzarias/${cod_pizzarias}_piz_p.${arq_ext}");

          $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $cod_pizzarias", 
           texto2bd("${cod_pizzarias}_piz_p.${arq_ext}"));

          $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
        }
      }          
    }


    for($s = 0; $s < 7; $s++)
    {
      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '18:00:00', '18:29:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '18:30:00', '18:59:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '19:00:00', '19:29:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '19:30:00', '19:59:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '20:00:00', '20:29:59', 30, 30, 30, ".$s.")";

      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '20:30:00', '20:59:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '21:00:00', '21:29:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '21:30:00', '21:59:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '22:00:00', '22:29:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '22:30:00', '22:59:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '23:00:00', '23:29:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);

      $str_horario_dia_semana = "INSERT INTO ipi_pizzarias_horarios (cod_pizzarias, horario_inicial_entrega, horario_final_entrega, tempo_entrega, tempo_entrega_ideal, tempo_entrega_max, dia_semana) VALUES(".$cod_pizzarias.", '23:30:00', '23:59:59', 30, 30, 30, ".$s.")";
      $res_horario_dia_semana = mysql_query($str_horario_dia_semana);
    }


    if (count($cod_formas_pg)>0)
    {
      $sql_del_formas_pgto = sprintf("DELETE FROM ipi_formas_pg_pizzarias WHERE cod_pizzarias = '%s' ", $cod_pizzarias);
            //echo "<Br>sql_del_formas_pgto: ".$sql_del_formas_pgto;
      $res_del_formas_pgto = mysql_query($sql_del_formas_pgto);

      for($c = 0; $c<count($cod_formas_pg); $c++)
      {
        $banco_selected =  validaVarPost('cod_bancos_'.$cod_formas_pg[$c]);
        $categoria_selected = validaVarPost('cod_titulos_subcategorias_'.$cod_formas_pg[$c]);
        $categoria_taxa_selected = validaVarPost('cod_titulos_subcategorias_taxa_'.$cod_formas_pg[$c]);

        if(validaVarPost('taxa_'.$cod_formas_pg[$c])=="")
        {
         $taxa =  validaVarPost('taxa_'.$cod_formas_pg[$c]);
         $taxa = moeda2bd($taxa);
       }else
       $taxa=0;

       if(validaVarPost('prazo_'.$cod_formas_pg[$c])=="")
       {
         $prazo =  validaVarPost('prazo_'.$cod_formas_pg[$c]);
       }else
       $prazo=0;

       $ecommerce = validaVarPost('ecommerce_'.$cod_formas_pg[$c]);

       if($ecommerce!="")
       {
         $ecommerce = '1';
       }
       else
       {
         $ecommerce = '0';
       }            


       $sql_formas_pgto = sprintf("INSERT INTO ipi_formas_pg_pizzarias (cod_pizzarias, cod_formas_pg,cod_bancos,cod_titulos_subcategorias_taxa,cod_titulos_subcategorias,taxa,prazo,disponivel_ecommerce) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $cod_pizzarias, $cod_formas_pg[$c],$banco_selected,$categoria_taxa_selected,$categoria_selected,$taxa,$prazo,$ecommerce);
       $res_formas_pgto = mysql_query($sql_formas_pgto);
     }


   }


       /* $sql_del_funcionamento = sprintf("DELETE FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = '%s' ", $cod_pizzarias);
        //echo "<Br>sql_del_funcionamento: ".$sql_del_funcionamento;
        $res_del_funcionamento = mysql_query($sql_del_funcionamento);
        if ((is_array($dias_semana))&&(count($dias_semana)>0))
        {
            for($c = 0; $c<count($dias_semana); $c++)
            {
                $sql_funcionamento = sprintf("INSERT INTO ipi_pizzarias_funcionamento (cod_pizzarias, dia_semana) VALUES ('%s', '%s')", $cod_pizzarias, $dias_semana[$c]);
                //echo "<Br>sql_funcionamento: ".$sql_funcionamento;
                $res_funcionamento = mysql_query($sql_funcionamento);
				    }
         }*/

         $sql_caixa = "INSERT INTO ipi_caixa (cod_usuarios_fechamento, cod_usuarios_abertura, cod_pizzarias, data_hora_abertura, data_hora_fechamento, obs_caixa, situacao) VALUES(1, 1, '".$cod_pizzarias."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."', '', 'ABERTO');";
         $res_caixa = mysql_query($sql_caixa);



         if ($res_pizzarias && $resEdicaoImagem)
          mensagemOk('Registro adicionado com êxito!');
        else
          mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');

      } 
      else
      {
        
        $SqlEdicao = sprintf("UPDATE $tabela SET cod_empresas = '%s', nome = '%s', telefone_1 = '%s', telefone_2 = '%s', telefone_3 = '%s', telefone_4 = '%s', endereco = '%s', numero = '%s', complemento = '%s', bairro = '%s', cidade = '%s', estado = '%s', cep = '%s', lat = '%s', lon = '%s', emails_diretoria = '%s', impressao_automatica = '%s', num_afiliacao_cartao = '%s', num_gateway_pagamento = '%s',chave_cielo = '%s', razao_social = '%s', nome_fantasia = '%s', cnpj = '%s', inscricao_estadual = '%s', data_inauguracao = '%s', debug_pedidos = '%s' , timezone = '%s', situacao = '%s',dados_extra='%s',merchant_id='%s' WHERE $chave_primaria = $codigo", $cod_empresas, $nome, $telefone_1, $telefone_2, $telefone_3, $telefone_4, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $cep, $latitude, $longitude, $emails_diretoria, $impressao_automatica, $num_afiliacao_cartao, $num_gateway_pagamento,$chave_cielo, $razao_social, $nome_fantasia, $cnpj, $inscricao_estadual, $data_inauguracao,$debug_pedidos, $timezone, $situacao,$dados_extra,$merchant_id);
        


        if (mysql_query($SqlEdicao))
        {   
            // Atualizando as Imagens grandes
          $resEdicaoImagem = true;
          if(count($imagem_g['name']) > 0) {     
            if(trim($imagem_g['name']) != '') {
              $arq_info = pathinfo($imagem_g['name']);
              $arq_ext = $arq_info['extension'];
              if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_g["type"])) {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
              }
              else {                
                $resEdicaoImagem &= move_uploaded_file($imagem_g['tmp_name'], UPLOAD_DIR."/pizzarias/${codigo}_piz_g.${arq_ext}");

                $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_grande = '%s' WHERE $chave_primaria = $codigo", 
                 texto2bd("${codigo}_piz_g.${arq_ext}"));

                $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
              }
            }          
          }


            // Atualizando as Imagens pequena            
          if(count($imagem_p['name']) > 0) {     
            if(trim($imagem_p['name']) != '') {
              $arq_info = pathinfo($imagem_p['name']);
              $arq_ext = $arq_info['extension'];
              if(!eregi("^image\\/(pjpeg|jpeg|jpg|png)$", $imagem_p["type"])) {
                mensagemErro('Erro ao adicionar o registro', 'Por favor, verifique se os arquivos selecionados são imagens (*.jpg, *.png).');
              }
              else {                
                $resEdicaoImagem &= move_uploaded_file($imagem_p['tmp_name'], UPLOAD_DIR."/pizzarias/${codigo}_piz_p.${arq_ext}");

                $SqlEdicaoImagem = sprintf("UPDATE $tabela set foto_pequena = '%s' WHERE $chave_primaria = $codigo", 
                 texto2bd("${codigo}_piz_p.${arq_ext}"));

                $resEdicaoImagem &= mysql_query($SqlEdicaoImagem);
              }
            }          
          }


          $resEdicaoTempos = true;
          for($c = 0; $c<count($cod_pizzarias_horarios); $c++)
          {
            if($cod_pizzarias_horarios[$c] > 0)
            {
              $SqlEdicaoTempos = sprintf("UPDATE ipi_pizzarias_horarios SET tempo_entrega_ideal = '%s' WHERE $chave_primaria = $codigo AND cod_pizzarias_horarios = ".$cod_pizzarias_horarios[$c], $tempo_entrega[$c]);
              $resEdicaoTempos &= mysql_query($SqlEdicaoTempos);
            }
          }

            //echo "chego<br/>";
          if (count($cod_formas_pg)>0)
          {
            $sql_del_formas_pgto = sprintf("DELETE FROM ipi_formas_pg_pizzarias WHERE cod_pizzarias = '%s' ", $codigo);
              //echo "<Br>sql_del_formas_pgto: ".$sql_del_formas_pgto;
            $res_del_formas_pgto = mysql_query($sql_del_formas_pgto);

            for($c = 0; $c<count($cod_formas_pg); $c++)
            {
              $banco_selected =  validaVarPost('cod_bancos_'.$cod_formas_pg[$c]);
              $categoria_selected = validaVarPost('cod_titulos_subcategorias_'.$cod_formas_pg[$c]);
              $categoria_taxa_selected = validaVarPost('cod_titulos_subcategorias_taxa_'.$cod_formas_pg[$c]);
              $taxa =  validaVarPost('taxa_'.$cod_formas_pg[$c]);
              $taxa = moeda2bd($taxa);
              $prazo =  validaVarPost('prazo_'.$cod_formas_pg[$c]);
              $ecommerce = validaVarPost('ecommerce_'.$cod_formas_pg[$c]);

              if($ecommerce!="")
              {
               $ecommerce = '1';
             }
             else
             {
               $ecommerce = '0';
             }   

             $sql_formas_pgto = sprintf("INSERT INTO ipi_formas_pg_pizzarias (cod_pizzarias, cod_formas_pg,cod_bancos,cod_titulos_subcategorias_taxa,cod_titulos_subcategorias,taxa,prazo,disponivel_ecommerce) VALUES ('%s', '%s','%s', '%s', '%s', '%s', '%s', '%s')", $codigo, $cod_formas_pg[$c],$banco_selected,$categoria_taxa_selected,$categoria_selected,$taxa,$prazo,$ecommerce);
                //echo "<Br>sql_formas_pgto: ".$sql_formas_pgto;
                  //echo $cod_formas_pg[$c]."AAAAA".$taxa."AAAAA".$prazo."AAAAA".$banco_selected;
               // die();
             $res_formas_pgto = mysql_query($sql_formas_pgto);
           }
         }


			   /*$sql_del_funcionamento = sprintf("DELETE FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = '%s' ", $codigo);
			    //echo "<Br>sql_del_funcionamento: ".$sql_del_funcionamento;
			    $res_del_funcionamento = mysql_query($sql_del_funcionamento);

          if ((is_array($dias_semana))&&(count($dias_semana)>0))
			    {
              for($c = 0; $c<count($dias_semana); $c++)
              {
                  $sql_funcionamento = sprintf("INSERT INTO ipi_pizzarias_funcionamento (cod_pizzarias, dia_semana) VALUES ('%s', '%s')", $codigo, $dias_semana[$c]);
                  //echo "<Br>sql_funcionamento: ".$sql_funcionamento;
                  $res_funcionamento = mysql_query($sql_funcionamento);
              }
            }*/


            if ($resEdicaoTempos && $resEdicaoImagem)
            {
              mensagemOk('Registro adicionado com êxito!');
            } 
            else
            {
              mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
            }

          }
          else
            mensagemErro('Erro ao alterar o registro', 'Por favor, verifique se o registro já não se encontra cadastrado.');
        }
        desconectabd($con);
        break;
      }

      ?>

      <link rel="stylesheet" type="text/css" media="screen" href="../lib/css/tabs_simples.css" />

      <script>

        function verificaCheckbox(form) {
          var cInput = 0;
          var checkBox = form.getElementsByTagName('input');

          for (var i = 0; i < checkBox.length; i++) {
            if((checkBox[i].className.match('excluir')) && ((checkBox[i].type == 'checkbox')) && (checkBox[i].disabled != true) && ((checkBox[i].checked == true))) { 
              cInput++; 
            }
          }

          if(cInput > 0) {
            if (confirm('Deseja excluir os registros selecionados?')) {
              return true;
            }
            else {
              return false;
            }
          }
          else {
            alert('Por favor, selecione os itens que deseja excluir.');

            return false;
          }
        }

        function validar_formas_pagamento()
        {
          var formas_pg = $$('input[name=cod_formas_pg[]]');
          var retorno = true;
          formas_pg.each(function(item){
            if(!retorno)
              return false;
            if(item.checked)
            {
              var cod_forma = item.value;
              var nome_forma = document.getElementById('label_formas_'+cod_forma).get('html');
              var cod_bancos = document.getElementById('cod_bancos_'+cod_forma);
              var cod_subcategoria = document.getElementById('cod_titulos_subcategorias_'+cod_forma);
              var cod_subcategoria_taxa = document.getElementById('cod_titulos_subcategorias_taxa_'+cod_forma);
              var prazo = document.getElementById('prazo_'+cod_forma);
              var taxa = document.getElementById('taxa_'+cod_forma);

              if(cod_subcategoria.value=="")
              {
                alert('O campo "Categoria Crédito" da forma de pagamento "'+nome_forma+'" precisa ser preenchido');
                retorno = false;
                return false;
              }
              if(cod_bancos.value=="")
              {
                alert('O campo "Banco" da forma de pagamento "'+nome_forma+'" precisa ser preenchido');
                retorno = false;
                return false;
              }
              if(prazo.value=="" || Number.from(prazo.value)<0)
              {
                alert('O campo "Prazo" da forma de pagamento "'+nome_forma+'" precisa ser preenchido com "0" ou com o numero correspondente ao prazo em que o dinheiro do pagamento sera disponivel.');
                retorno = false;
                return false;
              }
              if(taxa.value=="" || Number.from(taxa.value)<0)
              {
                alert('O campo "Taxa" da forma de pagamento "'+nome_forma+'" precisa ser preenchido com "0" ou com o numero correspondente a taxa da forma pagamento.');
                retorno = false;
                return false;
              }
              if(taxa.value!="" && Number.from(taxa.value)>0)
              {
                if(cod_subcategoria_taxa.value=="")
                {
                  alert('O campo "Categoria Comissão do Cartão" da forma de pagamento "'+nome_forma+'" precisa ser preenchido pois foi preenchida a  taxa desta forma de pagamento.');
                  retorno = false;
                  return false;
                }
              }
            }
          });
          return retorno;


        }
        function editar(cod) {
          var form = new Element('form', {
            'action': '<?
            echo $_SERVER['PHP_SELF']?>',
            'method': 'post'
          });

          var input = new Element('input', {
            'type': 'hidden',
            'name': '<?
            echo $chave_primaria?>',
            'value': cod
          });

          input.inject(form);
          $(document.body).adopt(form);

          form.submit();
        }

        function excluirImagem(cod) {
          if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
            var acao = 'excluir_imagem';
            var url = 'acao=' + acao + '&cod_pizzarias=' + cod;

            new Request.JSON({url: 'ipi_pizzaria_ajax.php', onComplete: function(retorno) {
              if(retorno.status != 'OK') {
                alert('Erro ao excluir esta imagem.');
              }
              else {
                if($('foto_g_figura')) {
                  $('foto_g_figura').destroy();
                }
              }
            }}).send(url); 
          } 
        }

        function excluirImagem_pequena(cod) {
          if (confirm('Deseja excluir esta imagem?\n\nATENÇÃO: Este é um processo irreversível.')) {
            var acao = 'excluir_imagem_pequena';
            var url = 'acao=' + acao + '&cod_pizzarias=' + cod;

            new Request.JSON({url: 'ipi_pizzaria_ajax.php', onComplete: function(retorno) {
              if(retorno.status != 'OK') {
                alert('Erro ao excluir esta imagem.');
              }
              else {
                if($('foto_p_figura')) {
                  $('foto_p_figura').destroy();
                }
              }
            }}).send(url); 
          } 
        }

        window.addEvent('domready', function(){
          var tabs = new Tabs('tabs'); 

          if (document.frmIncluir.<?
            echo $chave_primaria?>.value > 0) {
            <?
            if ($acao=='')
              echo 'tabs.irpara(1);';
            ?>

            document.frmIncluir.botao_submit.value = 'Alterar';
          }
          else {
            document.frmIncluir.botao_submit.value = 'Cadastrar';
          }

          tabs.addEvent('change', function(indice){
            if(indice == 1) {
      // FELIPE> Comentei por causa da bucha de limpar todos os horários...depois eu resolvo isso
      
      document.frmIncluir.<? echo $chave_primaria ?>.value = '';
      
      document.frmIncluir.cod_empresas.value = '';
      document.frmIncluir.nome.value = '';
      document.frmIncluir.telefone_1.value = '';
      document.frmIncluir.telefone_2.value = '';
      document.frmIncluir.telefone_3.value = '';
      document.frmIncluir.telefone_4.value = '';
      document.frmIncluir.data_inauguracao.value = '';
      document.frmIncluir.endereco.value = '';
      document.frmIncluir.numero.value = '';
      document.frmIncluir.complemento.value = '';
      document.frmIncluir.bairro.value = '';
      document.frmIncluir.cidade.value = '';
      document.frmIncluir.estado.value = '';
      document.frmIncluir.cep.value = '';
      document.frmIncluir.timezone.value = '';
      document.frmIncluir.horario_inicial.value = '18:00';
      document.frmIncluir.horario_final.value = '23:59';
      document.frmIncluir.horarios.value = "De Segunda a Sexta-Feira\n- Das 18h00 às 00h00\n\nSábados, Domingos e Feriados\n - Das 18h00 às 00h00";
      document.frmIncluir.latitude.value = '';
      document.frmIncluir.longitude.value = '';
      document.frmIncluir.situacao.value = '';
      document.getElementById('tabela_horarios').destroy();
      document.frmIncluir.botao_submit.value = 'Cadastrar';
      
    }
  });
        });

        function completar_endereco() {
  //alert(postid);
  var cep = document.getElementById('cep').value;
  var url = 'cep=' + cep;
  
  if(cep != '') {
    new Request.JSON({url: '../../ipi_completa_cep_ajax.php', onComplete: function(retorno) {
      if(retorno.status == 'OK') {
        document.getElementById('endereco').value = retorno.endereco;
        document.getElementById('bairro').value = retorno.bairro;
        document.getElementById('cidade').value = retorno.cidade;
        document.getElementById('estado').value = retorno.estado;
        
        document.getElementById('numero').value = '';
        document.getElementById('numero').focus();
      }
      else {
        alert('Erro ao completar CEP: ' + retorno.mensagem);
      }
    }}).send(url);
  }
  else {
    alert('Para completar o endereço o campo CEP deverá ter um valor válido.');
  }
}
</script>

<div id="tabs">
  <div class="menuTab">
    <ul>
     <li><a href="javascript:;">Editar</a></li>
     <li><a href="javascript:;">Incluir</a></li>
   </ul>
 </div>

 <!-- Tab Editar -->
 <div class="painelTab">
  <table>
   <tr>

    <!-- Conteúdo -->
    <td class="conteudo">

      <form name="frmExcluir" method="post"
      onsubmit="return verificaCheckbox(this)">

      <table class="cabecalhoEdicao" cellpadding="0" cellspacing="0">
       <tr>
        <td><input class="botaoAzul" type="submit"
         value="Excluir Selecionados"></td>
       </tr>
     </table>

     <table class="listaEdicao" cellpadding="0" cellspacing="0">
       <thead>
        <tr>
         <td align="center" width="20"><input type="checkbox"
          onclick="marcaTodos('marcar');"></td>
          <td align="center">Código (<? echo ucfirst(TIPO_EMPRESA) ?>)</td>
          <td align="center"><? echo ucfirst(TIPO_EMPRESA) ?></td>
          <td align="center">Empresa</td>
          <td align="center">Num. Franqueados</td>
          <td align="center">Situação</td>
        </tr>
      </thead>
      <tbody>

       <?
       $con = conectabd();
       $SqlBuscaRegistros = "SELECT p.nome, p.situacao, p.cod_pizzarias, e.nome_empresa, (SELECT count(cod_franqueados)  FROM ipi_franqueados f WHERE f.cod_pizzarias = p.cod_pizzarias ) total_franqueados FROM $tabela p LEFT JOIN ipi_empresas e ON (p.cod_empresas = e.cod_empresas) ORDER BY p.nome";
       $resBuscaRegistros = mysql_query($SqlBuscaRegistros);
       while ( $objBuscaRegistros = mysql_fetch_object($resBuscaRegistros) )
       {
        echo '<tr>';

        echo '<td align="center"><input type="checkbox" class="marcar excluir" name="excluir[]" value="'.$objBuscaRegistros->$chave_primaria.'"></td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->cod_pizzarias).'</td>';
        echo '<td align="center"><a href="javascript:;" onclick="editar('.$objBuscaRegistros->$chave_primaria.')">'.bd2texto($objBuscaRegistros->nome).'</a></td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->nome_empresa).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->total_franqueados).'</td>';
        echo '<td align="center">'.bd2texto($objBuscaRegistros->situacao).'</td>';

        echo '</tr>';
      }
      desconectabd($con);
      ?>

    </tbody>
  </table>

  <input type="hidden" name="acao" value="excluir"></form>

</td>
<!-- Conteúdo -->

<!-- Barra Lateral -->
<td class="lateral">
  <div class="blocoNavegacao">
    <ul>
     <li><a href="ipi_cep.php">CEP de Entrega</a></li>
     <li><a href="ipi_entregador.php">Entregadores</a></li>
     <li><a href="ipi_franqueados.php">Franqueados</a></li>
   </ul>
 </div>
</td>
<!-- Barra Lateral -->

</tr>
</table>
</div>
<!-- Tab Editar --> <!-- Tab Incluir -->
<div class="painelTab">
  <?
  $codigo = validaVarPost($chave_primaria, '/[0-9]+/');

  if ($codigo>0)
  {
    $objBusca = executaBuscaSimples("SELECT * FROM $tabela WHERE $chave_primaria = $codigo");
    $dados_extra_busca = json_decode($objBusca->dados_extra,true);
  }
  ?>

  <form name="frmIncluir" method="post"
  onsubmit="return (validaRequeridos(this) && validar_formas_pagamento())" enctype="multipart/form-data">

  <table align="center" class="caixa" cellpadding="0" cellspacing="0">


   <tr>
    <td class="legenda tdbl tdbt tdbr"><label class="requerido" for="cod_empresas">Empresa: </label></td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">


     <select name="cod_empresas" id="cod_empresas" class="requerido">
      <option value=""></option>
      <?
      $con = conectabd();
      $sql_empresas = "SELECT * FROM ipi_empresas ORDER BY nome_empresa";
      $res_empresas = mysql_query($sql_empresas);
      while ($obj_empresas = mysql_fetch_object($res_empresas))
      {
        echo '<option value="' . $obj_empresas->cod_empresas . '" ';

        if ($obj_empresas->cod_empresas == $objBusca->cod_empresas)
          echo 'selected';

        echo '>' . bd2texto($obj_empresas->nome_empresa) . '</option>';
      }
      desconectabd($con);
      ?>
    </select>


  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label class="requerido" for="nome">Nome</label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep"><input class="requerido" type="text"
   name="nome" id="nome" maxlength="45" size="110"
   value="<? echo texto2bd($objBusca->nome)?>"></td>
 </tr>

 <tr>
  <td class="legenda tdbl tdbr"><label for="data_inauguracao">Data de Inauguração</label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep"><input type="text" name="data_inauguracao" id="data_inauguracao" maxlength="10" size="8" value="<? echo bd2data($objBusca->data_inauguracao)?>" onkeypress="return MascaraData(this,event);"></td>
</tr>

<tr>
  <td class="tdbl tdbr">
    <table cellpadding="0" cellspacing="0">
     <tr>
      <td class="legenda"><label class="requerido" for="telefone_1">Telefone
      1</label></td>
      <td>&nbsp;</td>
      <td class="legenda"><label for="telefone_2">Telefone 2</label></td>
    </tr>
    <tr>
      <td><input class="requerido" type="text" name="telefone_1"
       id="telefone_1" maxlength="15" size="20"
       value="<? echo texto2bd($objBusca->telefone_1) ?>"
       onkeypress="return MascaraTelefone(this,event);"></td>
       <td>&nbsp;</td>
       <td><input type="text" name="telefone_2" id="telefone_2"
         maxlength="15" size="20"
         value="<? echo texto2bd($objBusca->telefone_2) ?>"
         onkeypress="return MascaraTelefone(this,event);"></td>
       </tr>
     </table>
   </td>
 </tr>

 <tr>
  <td class="tdbl tdbr">
    <table cellpadding="0" cellspacing="0">
     <tr>
      <td class="legenda"><label for="telefone_2">Telefone 3</label></td>
      <td>&nbsp;</td>
      <td class="legenda"><label for="telefone_3">Telefone 4</label></td>
    </tr>
    <tr>
      <td class="sep"><input type="text" name="telefone_3" id="telefone_3"
       maxlength="15" size="20"
       value="<? echo texto2bd($objBusca->telefone_3)?>"
       onkeypress="return MascaraTelefone(this,event);"></td>
       <td>&nbsp;</td>
       <td class="sep"><input type="text" name="telefone_4" id="telefone_4"
         maxlength="15" size="20"
         value="<? echo texto2bd($objBusca->telefone_4)?>"
         onkeypress="return MascaraTelefone(this,event);"></td>
       </tr>
     </table>
   </td>
 </tr>

 <tr>
  <td class="legenda tdbl tdbr"><label class="requerido" for="cep">CEP</label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
    <input class="requerido" type="text" name="cep" id="cep" maxlength="10" size="10" value="<? echo texto2bd($objBusca->cep) ?>" onkeypress="return MascaraCEP(this,event);">
    <input type="button"style="width: 150px;" class="botaoAzul" value="Completar Endereço" onclick="completar_endereco()">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label class="requerido" for="endereco">Endereço</label></td>
</tr>
<tr>
  <td class="tdbl tdbr"><input class="requerido" type="text"
   name="endereco" id="endereco" maxlength="80" size="110"
   value="<? echo texto2bd($objBusca->endereco)?>"></td>
 </tr>

 <tr>
  <td class="tdbl tdbr">
    <table cellpadding="0" cellspacing="0">
     <tr>
      <td class="legenda"><label class="requerido" for="numero">Número</label></td>
      <td>&nbsp;</td>
      <td class="legenda"><label for="complemento">Complemento</label></td>
    </tr>
    <tr>
      <td><input class="requerido" type="text" name="numero" id="numero"
       maxlength="15" size="6"
       value="<? echo texto2bd($objBusca->numero)?>"
       onkeypress="return ApenasNumero(event);"></td>
       <td>&nbsp;</td>
       <td><input type="text" name="complemento" id="complemento"
         maxlength="45" size="33"
         value="<? echo texto2bd($objBusca->complemento)?>"></td>
       </tr>
     </table>
   </td>
 </tr>

 <tr>
  <td class="tdbl tdbr sep">
    <table cellpadding="0" cellspacing="0">
     <tr>
      <td class="legenda"><label class="requerido" for="bairro">Bairro</label></td>
      <td>&nbsp;</td>
      <td class="legenda"><label class="requerido" for="cidade">Cidade</label></td>
      <td>&nbsp;</td>
      <td class="legenda"><label class="requerido" for="estado">Estado</label></td>
    </tr>
    <tr>
      <td><input class="requerido" type="text" name="bairro" id="bairro"
       maxlength="45" size="30"
       value="<? echo texto2bd($objBusca->bairro)?>"></td>
       <td>&nbsp;</td>
       <td><input class="requerido" type="text" name="cidade" id="cidade"
         maxlength="45" size="62"
         value="<? echo texto2bd($objBusca->cidade)?>"></td>
         <td>&nbsp;</td>
         <td><select name="estado" id="estado" class="requerido">
           <option value=""></option>
           <option value="AC" <? if ($objBusca->estado=='AC') echo 'selected'?>>AC</option>
           <option value="AL" <? if ($objBusca->estado=='AL') echo 'selected'?>>AL</option>
           <option value="AM" <? if ($objBusca->estado=='AM') echo 'selected'?>>AM</option>
           <option value="AP" <? if ($objBusca->estado=='AP') echo 'selected'?>>AP</option>
           <option value="BA" <? if ($objBusca->estado=='BA') echo 'selected'?>>BA</option>
           <option value="CE" <? if ($objBusca->estado=='CE') echo 'selected'?>>CE</option>
           <option value="DF" <? if ($objBusca->estado=='DF') echo 'selected'?>>DF</option>
           <option value="ES" <? if ($objBusca->estado=='ES') echo 'selected'?>>ES</option>
           <option value="GO" <? if ($objBusca->estado=='GO') echo 'selected'?>>GO</option>
           <option value="MA" <? if ($objBusca->estado=='MA') echo 'selected'?>>MA</option>
           <option value="MG" <? if ($objBusca->estado=='MG') echo 'selected'?>>MG</option>
           <option value="MS" <? if ($objBusca->estado=='MS') echo 'selected'?>>MS</option>
           <option value="MT" <? if ($objBusca->estado=='MT') echo 'selected'?>>MT</option>
           <option value="PA" <? if ($objBusca->estado=='PA') echo 'selected'?>>PA</option>
           <option value="PB" <? if ($objBusca->estado=='PB') echo 'selected'?>>PB</option>
           <option value="PE" <? if ($objBusca->estado=='PE') echo 'selected'?>>PE</option>
           <option value="PI" <? if ($objBusca->estado=='PI') echo 'selected'?>>PI</option>
           <option value="PR" <? if ($objBusca->estado=='PR') echo 'selected'?>>PR</option>
           <option value="RJ" <? if ($objBusca->estado=='RJ') echo 'selected'?>>RJ</option>
           <option value="RN" <? if ($objBusca->estado=='RN') echo 'selected'?>>RN</option>
           <option value="RO" <? if ($objBusca->estado=='RO') echo 'selected'?>>RO</option>
           <option value="RR" <? if ($objBusca->estado=='RR') echo 'selected'?>>RR</option>
           <option value="RS" <? if ($objBusca->estado=='RS') echo 'selected'?>>RS</option>
           <option value="SC" <? if ($objBusca->estado=='SC') echo 'selected'?>>SC</option>
           <option value="SE" <? if ($objBusca->estado=='SE') echo 'selected'?>>SE</option>
           <option value="SP" <? if ($objBusca->estado=='SP') echo 'selected'?>>SP</option>
           <option value="TO" <? if ($objBusca->estado=='TO') echo 'selected'?>>TO</option>
         </select></td>
       </tr>
     </table>
   </td>
 </tr>




 <tr>
  <td class="legenda tdbl tdbr"><label class="requerido" for="timezone">Fuso Horário: </label></td>
</tr>
<tr>
  <td class="tdbl tdbr sep">
   <select name="timezone" id="timezone" class="requerido">
    <option value=""></option>
    <?
    $con = conectabd();
    $sql_timezones = "SELECT * FROM ipi_timezones ORDER BY nome_timezone";
    $res_timezones = mysql_query($sql_timezones);
    while ($obj_timezones = mysql_fetch_object($res_timezones))
    {
      echo '<option value="' . $obj_timezones->nome_timezone . '" ';

      if ($obj_timezones->nome_timezone == $objBusca->timezone)
        echo 'selected';

      echo '>' . $obj_timezones->nome_timezone ." (". $obj_timezones->variacao_gmt .")". '</option>';
    }
    desconectabd($con);
    ?>
  </select>
</td>
</tr>

<tr>
  <td class="tdbl tdbr sep">
    <table cellpadding="0" cellspacing="0">
     <tr>
      <td class="legenda"><label for="latitude">Latitude</label></td>
      <td>&nbsp;</td>
      <td class="legenda"><label for="longitude">Longitude</label></td>
    </tr>
    <tr>
      <td><input type="text" name="latitude" id="latitude" maxlength="25" size="25" value="<? echo texto2bd($objBusca->lat)?>"></td>
      <td>&nbsp;</td>
      <td><input type="text" name="longitude" id="longitude" maxlength="25" size="25" value="<? echo texto2bd($objBusca->lon)?>"></td>
    </tr>
  </table>
</td>
</tr>


<tr>
  <td class="legenda tdbl tdbr"><label class="requerido" for="emails_diretoria">Emails da Diretoria</label> (Separados por virgula)</td>
</tr>
<tr>
  <td class="tdbl tdbr sep"><input class="requerido" type="text"
   name="emails_diretoria" id="emails_diretoria" maxlength="250" size="110"
   value="<? echo texto2bd($objBusca->emails_diretoria)?>"></td>
 </tr>


 <tr>
  <td class="legenda tdbl tdbr"><label for="foto_g">Imagem grande (*.png, *.jpg)</label></td>
</tr>

<?
if (is_file(UPLOAD_DIR . '/pizzarias/' . $objBusca->foto_grande))
{
  echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_g_figura" style="padding: 15px;">';

  echo '<img height="300" src="' . UPLOAD_DIR . '/pizzarias/' . $objBusca->foto_grande . '">';

  echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem(' . $objBusca->$chave_primaria . ');"></td></tr>';
}
?>

<tr>
  <td class="sep tdbl tdbr sep"><input type="file" name="foto_g" id="foto_g" size="40"> </td>
</tr>


<tr>
  <td class="legenda tdbl tdbr"><label for="foto_p">Imagem pequena (*.png, *.jpg)</label></td>
</tr>

<?
if (is_file(UPLOAD_DIR . '/pizzarias/' . $objBusca->foto_pequena))
{
  echo '<tr><td class="sep tdbl tdbr" align="center" id="foto_p_figura" style="padding: 15px;">';

  echo '<img height="100" src="' . UPLOAD_DIR . '/pizzarias/' . $objBusca->foto_pequena . '">';

  echo '<br><br><input class="botaoAzul" type="button" value="Excluir Imagem" onclick="javascript: excluirImagem_pequena(' . $objBusca->$chave_primaria . ');"></td></tr>';
}
?>

<tr>
  <td class="sep tdbl tdbr sep"><input type="file" name="foto_p" id="foto_p" size="40"> </td>
</tr>




<tr>
  <td class="legenda tdbl tdbr"><br /><br /><label>Documentos</label></td>
</tr>
<tr>
  <td class="sep tdbl tdbr">
    <hr noshade="noshade" color="#1C4B93">
  </td>
</tr>


<tr>
  <td class="legenda tdbl tdbr"><label for="razao_social">Razão Social</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="razao_social" id="razao_social" maxlength="80" size="55" value="<? echo texto2bd($objBusca->razao_social)?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="nome_fantasia">Nome Fantasia</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="nome_fantasia" id="nome_fantasia" maxlength="80" size="55" value="<? echo texto2bd($objBusca->nome_fantasia)?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="cnpj">CNPJ</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="cnpj" id="cnpj" maxlength="18" size="20" value="<? echo texto2bd($objBusca->cnpj)?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="inscricao_estadual">Inscrição Estadual</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="inscricao_estadual" id="inscricao_estadual" maxlength="15" size="20" value="<? echo texto2bd($objBusca->inscricao_estadual)?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><br /><br /><label>Nota fiscal de homologação</label></td>
</tr>
<tr>
  <td class="sep tdbl tdbr">
    <hr noshade="noshade" color="#1C4B93">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Token de Homologação</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[token_focusnfe][homologacao][token]" id="dados_extra[token_focusnfe][homologacao][token]"  value="<?php echo isset($dados_extra_busca['token_focusnfe']['homologacao']['token'])?$dados_extra_busca['token_focusnfe']['homologacao']['token']:''; ?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Server de Homologação</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[token_focusnfe][homologacao][server]" id="dados_extra[token_focusnfe][homologacao][server]" value="<?php echo isset($dados_extra_busca['token_focusnfe']['homologacao']['server'])?$dados_extra_busca['token_focusnfe']['homologacao']['server']:''; ?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Login de Homologação</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[token_focusnfe][homologacao][login]" id="dados_extra[token_focusnfe][homologacao][login]" value="<?php echo isset($dados_extra_busca['token_focusnfe']['homologacao']['login'])?$dados_extra_busca['token_focusnfe']['homologacao']['login']:''; ?>">
  </td>
</tr>


<tr>
  <td class="legenda tdbl tdbr"><br /><br /><label>Nota fiscal de produção</label></td>
</tr>
<tr>
  <td class="sep tdbl tdbr">
    <hr noshade="noshade" color="#1C4B93">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Token de Produção</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[token_focusnfe][producao][token]" id="dados_extra[token_focusnfe][producao][token]" value="<?php echo isset($dados_extra_busca['token_focusnfe']['producao']['token'])?$dados_extra_busca['token_focusnfe']['producao']['token']:''; ?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Server de Produção</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[token_focusnfe][producao][server]" id="dados_extra[token_focusnfe][producao][server]" value="<?php echo isset($dados_extra_busca['token_focusnfe']['producao']['server'])?$dados_extra_busca['token_focusnfe']['producao']['server']:''; ?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Login de Produção</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[token_focusnfe][producao][login]" id="dados_extra[token_focusnfe][producao][login]" value="<?php echo isset($dados_extra_busca['token_focusnfe']['producao']['login'])?$dados_extra_busca['token_focusnfe']['producao']['login']:''; ?>">
  </td>
</tr>

<!-- 
LOGIN DO IFOOD APENAS PARA ADMINS, CONTADORES E DIRETORES
-->
<?php
$user = $_SESSION['usuario']['perfil'];
if($user == 1 or $user == 2 or $user == 15){
?>
<tr>
  <td class="legenda tdbl tdbr"><br /><br /><label>iFood</label></td>
</tr>
<tr>
  <td class="sep tdbl tdbr">
    <hr noshade="noshade" color="#1C4B93">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Usuário</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[ifood][usuario]" id="dados_extra[ifood][usuario]" value="<?php echo isset($dados_extra_busca['ifood']['usuario'])?$dados_extra_busca['ifood']['usuario']:''; ?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Senha</label></td>
</tr>
<tr>
  <td class="tdbl tdbr">
    <input type="text" name="dados_extra[ifood][senha]" id="dados_extra[ifood][senha]" value="<?php echo isset($dados_extra_busca['ifood']['senha'])?$dados_extra_busca['ifood']['senha']:''; ?>">
  </td>
</tr>

<tr>
  <td class="legenda tdbl tdbr"><label for="token_focusnfe_token">Merchant's ID</label></td>
</tr>

<style type="text/css">
  .floatLeft{
    float: left;
  }
</style>

<tr>
  <td class="tdbl tdbr" style="float: left;">
    <input class='floatLeft' type="text" name="merchant_id[]" value="" value="<?php echo isset($dados_extra_busca['ifood']['merchant_id'])?$dados_extra_busca['ifood']['merchant_id']:''; ?>">
    <button style="float: right;width: 27px;" id="addMerchant">+</button>
  </td>
</tr>

<script type="text/javascript">
  let addMerchant = document.querySelector('#addMerchant');
  let newMerchant = '<tr><td class="tdbl tdbr" style="float: left;"><input type="text" name="merchant_id[]" value="" style="float: left;"></td></tr>';
  addMerchant.onclick = function(e){
    this.parentNode.parentNode.insertAdjacentHTML('afterend',newMerchant);
  }
</script>

<?php } ?>



<tr>
  <td class="legenda tdbl tdbr"><br /><br /><label>Logística</label></td>
</tr>
<tr>
  <td class="sep tdbl tdbr">
    <hr noshade="noshade" color="#1C4B93">
  </td>
</tr>



<tr>
  <td class="legenda tdbl tdbr"><label>Dias de Funcionamento</label></td>
</tr>

	<!--<tr>
		<td class="legenda tdbl tdbr">

		<?
		$con = conectabd();
		$sql_funcionamento = "SELECT * FROM ipi_pizzarias_funcionamento WHERE cod_pizzarias = '".$codigo."'";
		$res_funcionamento = mysql_query($sql_funcionamento);
		$dias_semana = array();
		while ( $obj_funcionamento = mysql_fetch_object($res_funcionamento) )
		{
			$dias_semana[] = $obj_funcionamento->dia_semana;
		}
		desconectabd($con);
		?>

			  		<label><input type="checkbox" name="dias_semana[]" value="Dom" <? if(in_array("Dom", $dias_semana)) echo 'checked="checked"'; ?>>&nbsp;Domingo</label>
			  <br /><label><input type="checkbox" name="dias_semana[]" value="Seg" <? if(in_array("Seg", $dias_semana)) echo 'checked="checked"'; ?>>&nbsp;Segunda</label>
			  <br /><label><input type="checkbox" name="dias_semana[]" value="Ter" <? if(in_array("Ter", $dias_semana)) echo 'checked="checked"'; ?>>&nbsp;Terça</label>
			  <br /><label><input type="checkbox" name="dias_semana[]" value="Qua" <? if(in_array("Qua", $dias_semana)) echo 'checked="checked"'; ?>>&nbsp;Quarta</label>
			  <br /><label><input type="checkbox" name="dias_semana[]" value="Qui" <? if(in_array("Qui", $dias_semana)) echo 'checked="checked"'; ?>>&nbsp;Quinta</label>
			  <br /><label><input type="checkbox" name="dias_semana[]" value="Sex" <? if(in_array("Sex", $dias_semana)) echo 'checked="checked"'; ?>>&nbsp;Sexta</label>
			  <br /><label><input type="checkbox" name="dias_semana[]" value="Sab" <? if(in_array("Sab", $dias_semana)) echo 'checked="checked"'; ?>>&nbsp;Sábado</label>
			  <br /><br />

		</td>
	</tr>-->

	<!--<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="horario_inicial">Horário de Abertura</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr"><input class="requerido" type="text"
			name="horario_inicial" id="horario_inicial" maxlength="5" size="5"
			value="<? echo substr(texto2bd($objBusca->horario_inicial), 0, 5)?>"
			onkeydown="return MascaraHora(this,event);"></td>
	</tr>

	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido"
			for="horario_final">Horário de Fechamento</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><input class="requerido" type="text"
			name="horario_final" id="horario_final" maxlength="5" size="5"
			value="<? echo substr(texto2bd($objBusca->horario_final), 0, 5)?>"
			onkeydown="return MascaraHora(this,event);">Para efeito de sistema deve ser 23:59</td>
	</tr>

	<tr>
		<td class="legenda tdbl tdbr"><label class="requerido" for="horarios">Horários
		(informativo)</label></td>
	</tr>
	<tr>
		<td class="tdbl tdbr sep"><textarea rows="10" cols="112" name="horarios"
			id="horarios"><? echo texto2bd($objBusca->horarios)?></textarea></td>
   </tr>-->


   <tr>
    <td class="legenda tdbl tdbr">
      <label class="requerido" for="situacao">Situação</label>
    </td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">
      <select class="requerido" name="situacao" id="situacao">
        <option value=""></option>
        <option value="ATIVO" <? if($objBusca->situacao == 'ATIVO') echo 'selected'; ?>> Ativo </option>
        <option value="TESTE" <? if($objBusca->situacao == 'TESTE') echo 'selected'; ?>> Teste </option>
        <option value="INATIVO" <? if($objBusca->situacao == 'INATIVO') echo 'selected'; ?>> Inativo </option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl tdbr">
      <label  class="requerido" for="debug_pedidos">Habilitar modo monitoramento de erros de impressão de pedidos?</label>
    </td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">
      <select class="requerido" name="debug_pedidos" id="debug_pedidos">
        <option value=""></option>
        <option value="0" <? if($objBusca->debug_pedidos == '0') echo 'selected'; ?>> Não </option>
        <option value="1" <? if($objBusca->debug_pedidos == '1') echo 'selected'; ?>> Sim </option>
      </select>
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl tdbr">
      <label class="requerido" for="impressao_automatica">Impressão Automática de Comanda?</label>
    </td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">
      <select class="requerido" name="impressao_automatica" id="impressao_automatica">
        <option value=""></option>
        <option value="1" <? if($objBusca->impressao_automatica == '1') echo 'selected'; ?>> Sim </option>
        <option value="0" <? if($objBusca->impressao_automatica == '0') echo 'selected'; ?>> Não </option>
      </select>
    </td>
  </tr>


  <tr>
    <td class="legenda tdbl tdbr">
      <label class="requerido" for="cod_formas_pg">Formas de Pagamento</label>
    </td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">
      <table class="listaEdicao" cellpadding="0" cellspacing="0">
        <tr>
          <td></td>
          <td>Forma de Pagamento</td>
          <td>Online</td>
          <td>Banco</td>
          <td>Categoria Crédito</td>
          <td>Categoria Comissão do Cartão</td>
          <td>Taxa</td>
          <td>Prazo</td>
        </tr>

        <?
        $con = conectabd();

        $cod_formas_pizzaria = array();
        $cod_formas_ecommerce = array();
        $sql_formas_pg = "SELECT * FROM ipi_formas_pg_pizzarias WHERE cod_pizzarias = '".$codigo."'";
        $res_formas_pg = mysql_query($sql_formas_pg);
        while ($obj_formas_pg = mysql_fetch_object($res_formas_pg))
        {
          $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['cod'] = $obj_formas_pg->cod_formas_pg;
          $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['banco'] = $obj_formas_pg->cod_bancos;
          $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['subcategorias'] = $obj_formas_pg->cod_titulos_subcategorias;
          $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['subcategorias_taxa'] = $obj_formas_pg->cod_titulos_subcategorias_taxa;
          $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['taxa'] = $obj_formas_pg->taxa;
          $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['prazo'] = $obj_formas_pg->prazo;
          $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['ecommerce'] = $obj_formas_pg->disponivel_ecommerce;
        }
        $sql_formas_pgs_pizzas = $sql_formas_pg;
        $res_formas_pgs_pizzas = mysql_query($sql_formas_pgs_pizzas);


        $sql_formas_pg = "SELECT * FROM ipi_formas_pg ORDER BY forma_pg";
        $res_formas_pg = mysql_query($sql_formas_pg);
        $num_formas_pg = mysql_num_rows($res_formas_pg);

        $sql_buscar_bancos = "SELECT * FROM ipi_bancos WHERE cod_bancos IN (SELECT cod_bancos FROM ipi_bancos_ipi_pizzarias WHERE cod_pizzarias IN (" . implode(',', $_SESSION['usuario']['cod_pizzarias']) . ")) ORDER BY banco";
    	//echo $sql_buscar_bancos;

        $sql_buscar_categorias = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias WHERE tipo_titulo = 'PAGAR') ORDER BY titulos_categoria";

        $sql_buscar_categorias2 = "SELECT * FROM ipi_titulos_categorias WHERE cod_titulos_categorias IN (SELECT cod_titulos_categorias FROM ipi_titulos_subcategorias WHERE tipo_titulo = 'RECEBER') ORDER BY titulos_categoria";

        for ($a = 0; $a < $num_formas_pg; $a++)
        {

          $obj_formas_pg_pizzas = mysql_fetch_object($res_formas_pgs_pizzas);
          $obj_formas_pg = mysql_fetch_object($res_formas_pg);
          $res_buscar_bancos = mysql_query($sql_buscar_bancos);
          $res_buscar_categorias = mysql_query($sql_buscar_categorias);
          $res_buscar_categorias2 = mysql_query($sql_buscar_categorias2);
          echo "<tr>
          <td><input type='checkbox' name='cod_formas_pg[]' id='cod_formas_pg_".$obj_formas_pg->cod_formas_pg."' value='".$obj_formas_pg->cod_formas_pg."' ".(($cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['cod']==$obj_formas_pg->cod_formas_pg)?' checked="checked" ':'')." /></td>";
          
          echo "<td><label for='cod_formas_pg_".$obj_formas_pg->cod_formas_pg."' id='label_formas_".$obj_formas_pg->cod_formas_pg."'>".$obj_formas_pg->forma_pg."</label></td>";

          echo "<td align='center'><input type='checkbox' name='ecommerce_".$obj_formas_pg->cod_formas_pg."' value='".$obj_formas_pg->cod_formas_pg."' ".(($cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['ecommerce']==true)?' checked="checked" ':'')." /></td>";  			  

          echo '<td>
          <select name="cod_bancos_'.$obj_formas_pg->cod_formas_pg.'" id="cod_bancos_'.$obj_formas_pg->cod_formas_pg.'" style="width: 200px;">
          <option value=""></option>';

          while($obj_buscar_bancos = mysql_fetch_object($res_buscar_bancos))
          {
            echo '<option value="' . $obj_buscar_bancos->cod_bancos . '" ';
            
            if($obj_buscar_bancos->cod_bancos ==  $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['banco'])
            {
            	echo 'selected';
            }
            
            echo '>' . bd2texto($obj_buscar_bancos->banco); 

            if(!$obj_buscar_bancos->caixa)
            {
              echo ' - AG: ' . bd2texto($obj_buscar_bancos->agencia)  . ' - C/C: ' . bd2texto($obj_buscar_bancos->conta_corrente);
            }

            echo '</option>';                	       
          }

          echo ' </select>
          </td>
          <td>
          <select name="cod_titulos_subcategorias_'.$obj_formas_pg->cod_formas_pg.'" id="cod_titulos_subcategorias_'.$obj_formas_pg->cod_formas_pg.'"  value="'.$obj_formas_pg->cod_formas_pg.'" style="width: 200px;">
          <option value=""></option>';

          while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias2))
          {

            echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
            $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'RECEBER' ORDER BY titulos_subcategorias";
            $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
            while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
            {
              echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '"';
              if($cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['subcategorias'] == $obj_buscar_subcategorias->cod_titulos_subcategorias)
              {
                echo 'selected';    
              }
              echo '>' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
            }
            echo '</optgroup>';
          }


          echo '</select>
          </td>';

          echo '<td>
          <select name="cod_titulos_subcategorias_taxa_'.$obj_formas_pg->cod_formas_pg.'" id="cod_titulos_subcategorias_taxa_'.$obj_formas_pg->cod_formas_pg.'" value="'.$obj_formas_pg->cod_formas_pg.'" style="width: 200px;">

          <option value=""></option>';

          while($obj_buscar_categorias = mysql_fetch_object($res_buscar_categorias))
          {
            echo '<optgroup label="' . bd2texto($obj_buscar_categorias->titulos_categoria) . '">';
            $sql_buscar_subcategorias = "SELECT * FROM ipi_titulos_subcategorias WHERE cod_titulos_categorias = '" . $obj_buscar_categorias->cod_titulos_categorias . "' AND tipo_titulo = 'PAGAR' ORDER BY titulos_subcategorias";
            $res_buscar_subcategorias = mysql_query($sql_buscar_subcategorias);
            while($obj_buscar_subcategorias = mysql_fetch_object($res_buscar_subcategorias))
            {
              echo '<option value="' . $obj_buscar_subcategorias->cod_titulos_subcategorias . '"';
              if($cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['subcategorias_taxa'] == $obj_buscar_subcategorias->cod_titulos_subcategorias)
              {
                echo 'selected';    
              }
              echo '>' . bd2texto($obj_buscar_subcategorias->titulos_subcategorias) . '</option>';
            }
            echo '</optgroup>';
          }


          echo '</select>
          </td>';



          echo '<td class="tdbl tdbr sep"><input type="text" name="taxa_'.$obj_formas_pg->cod_formas_pg.'" id="taxa_'.$obj_formas_pg->cod_formas_pg.'" maxlength="5" size="10" value="'.bd2moeda($cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['taxa']).'" onKeyPress="return formataMoeda(this, \'.\', \',\', event)"></td>';

          echo '<td class="tdbl tdbr sep"><input type="text" name="prazo_'.$obj_formas_pg->cod_formas_pg.'" id="prazo_'.$obj_formas_pg->cod_formas_pg.'" maxlength="5" size="10" value="'. $cod_formas_pizzaria[$obj_formas_pg->cod_formas_pg]['prazo'].'" onKeyPress="return ApenasNumero(event)"></td>';
          echo " </tr>";


        }
        desconectabd($con);
        ?>
      </table>
    </td>
  </tr>



  <tr>
    <td class="legenda tdbl tdbr"><label for="num_afiliacao_cartao">Num. Afiliação Cielo</label></td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">
      <input type="text" name="num_afiliacao_cartao" id="num_afiliacao_cartao" maxlength="20" size="20"	value="<? echo texto2bd($objBusca->num_afiliacao_cartao) ?>">
    </td>
  </tr>




  <tr>
    <td class="legenda tdbl tdbr"><label for="num_gateway_pagamento">Num. Gateway Pagamentos Locaweb</label></td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">
      <input type="text" name="num_gateway_pagamento" id="num_gateway_pagamento" maxlength="20" size="20"	value="<? echo texto2bd($objBusca->num_gateway_pagamento) ?>">
    </td>
  </tr>

  <tr>
    <td class="legenda tdbl tdbr"><label for="chave_cielo">Chave Cielo</label></td>
  </tr>
  <tr>
    <td class="tdbl tdbr sep">
      <input type="text" name="chave_cielo" id="chave_cielo" maxlength="100" size="100" value="<? echo texto2bd($objBusca->chave_cielo) ?>">
    </td>
  </tr>


  <tr>
    <td class="tdbl tdbr sep">

      <div id="tabela_horarios">
        <table class="listaEdicao" cellpadding="0" cellspacing="0">
         <thead>
          <tr>
           <td align="center"><label>Horário Inicial</label></td>
           <td align="center"><label>Horário Final</label></td>

           <? for($s = 0; $s < 7; $s++): ?>

             <td align="center" width="50"><label>Tempo de Entrega <font color="red"><? echo $dia_semana[$s] ?></font></label></td>

           <? endfor; ?>
         </tr>
       </thead>
       <tbody>

        <?
        $con = conectabd();
        
        $sqlBuscaHorarios = "SELECT DISTINCT horario_inicial_entrega, horario_final_entrega FROM ipi_pizzarias_horarios WHERE $chave_primaria = '$codigo' ORDER BY horario_inicial_entrega";
        $resBuscaHorarios = mysql_query($sqlBuscaHorarios);
        //echo $sqlBuscaHorarios;
        
        while ( $objBuscaHorarios = mysql_fetch_object($resBuscaHorarios) )
        {
          echo '<tr>';
          echo '<td align="center">'.$objBuscaHorarios->horario_inicial_entrega.'</td>';
          echo '<td align="center">'.$objBuscaHorarios->horario_final_entrega.'</td>';

          for($s = 0; $s < 7; $s++)
          {
            $str_buscar_horario_dia_semana = "SELECT * FROM ipi_pizzarias_horarios WHERE $chave_primaria = $codigo AND horario_inicial_entrega = '" . $objBuscaHorarios->horario_inicial_entrega . "' AND horario_final_entrega = '" . $objBuscaHorarios->horario_final_entrega . "' AND dia_semana = '$s' ORDER BY horario_inicial_entrega";
            $res_buscar_horario_dia_semana = mysql_query($str_buscar_horario_dia_semana);
            $obj_buscar_horario_dia_semana = mysql_fetch_object($res_buscar_horario_dia_semana);

            echo '<td align="center"><input type="text" name="tempo_entrega[]" maxsize="2" size="2" value="' . $obj_buscar_horario_dia_semana->tempo_entrega_ideal . '" onkeypress="return ApenasNumero(event);"></td>';

            echo '<input type="hidden" name="cod_pizzarias_horarios[]" value="' . $obj_buscar_horario_dia_semana->cod_pizzarias_horarios . '">';
          }

          echo '</tr>';
        }
        
        desconectabd($con);
        
        ?>
        
      </tbody>
    </table>
  </div>


</td>
</tr>

<tr>
  <td align="center" class="tdbl tdbb tdbr"><input name="botao_submit"
   class="botao" type="submit" value="Cadastrar"></td>
 </tr>

</table>

<input type="hidden" name="acao" value="editar"> 
<input type="hidden" name="<? echo $chave_primaria?>" value="<? echo $codigo?>">
</form>

</div>

<!-- Tab Incluir -->
</div>

<?
rodape();
?>
