<?
require_once '../lib/php/sessao.php';
require_once '../../config.php';
require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once 'ipi_caixa_classe.php';

$conexao = conectabd();
$acao = validaVarPost("acao");
$cod_pizzarias = $_SESSION['ipi_caixa']['pizzaria_atual'];
//$hora_inicial = microtime(true);
//echo "<script>console.log('ajax $acao -".date("d/m/Y H:i:s", $hora_inicial)."')</script>"; 
switch ($acao)
{
    case "carregar_ingredientes":
    if (validaVarPost('cod_pizzas'))
    {
        $cod_pizzas = validaVarPost('cod_pizzas');
        $cod_tamanhos = validaVarPost('cod_tamanhos');
        $num_fracao = validaVarPost('num_fracao');
        $num_sabores = validaVarPost('num_sabores');
         //sleep(2);
        ?>
       
        <table border="0" width="500">
            <tr>
                <td><span class="laranja">Ingredientes <? echo $num_fracao; ?></span> 
                <input type="text" name="cod_ingredientes_<? echo $num_fracao; ?>_digito" id="cod_ingredientes_<? echo $num_fracao; ?>_digito" class="proximo" style="width: 50px;" onkeypress="javascript:selecionar_box(event, this, 'ingredientes<? echo $num_fracao; ?>[]');">
                <br>
                <?
                $sql_ingredientes = "SELECT i.cod_ingredientes,i.ingrediente,i.cod_ingredientes_troca FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (i.cod_ingredientes=ip.cod_ingredientes) WHERE ip.cod_pizzas='" . $cod_pizzas . "' AND i.ativo = 1 AND i.consumo = 0 ORDER BY ingrediente";
                $res_ingredientes = mysql_query($sql_ingredientes);
                $num_ingredientes = mysql_num_rows($res_ingredientes);
                if ($num_ingredientes > 0)
                {
                    echo "<table bgcolor='#EEEEEE' width='100%'>";
                    echo "<tr>";
                    for ($a = 0; $a < $num_ingredientes; $a++)
                    {
                        if (($a % 3 == 0) && ($a != 0))
                            echo "</tr><tr>";
                        $obj_ingredientes = mysql_fetch_object($res_ingredientes);
                        echo "<td><small>";
                        $id_troca = "ingredientes" . $num_fracao."_t".$a;
                        $id_normal = "ingredientes_" . $num_fracao."_".$a;
                        echo utf8_encode("<input type='checkbox' onclick='ccbox(this,\"".$id_troca."\")' id='ingredientes_".$num_fracao."_".$a."' name='ingredientes" . $num_fracao . "[]' tabindex='1' value='" . $obj_ingredientes->cod_ingredientes . "' checked='checked' style='border: 0; background: none;' />");
                        
                        
                        //echo "<a href='javascript:;' onMouseover=\"Mostrar('<div style=\'float: left; margin-right: 5px;\'><img src=\'img/ing_mucarela.jpg\'></div><br><strong>".utf8_encode($objIngre->ingrediente)."</strong><br><br>".utf8_encode('Descrição ou alguma dica sobre o ingrediente.')."<br><br>')\" onMouseout=\"Esconder()\">".utf8_encode($objIngre->ingrediente) . "</a><br />";
                        echo utf8_encode($obj_ingredientes->cod_ingredientes . " - " . $obj_ingredientes->ingrediente) . "<br />";
                        //echo "a".$obj_ingredientes->cod_ingredientes_troca;
                        if($obj_ingredientes->cod_ingredientes_troca)
                        {
                            $sql_troca = "SELECT it.preco_troca, itroca.ingrediente FROM ipi_ingredientes i INNER JOIN ipi_ingredientes_ipi_tamanhos it ON (i.cod_ingredientes=it.cod_ingredientes) INNER JOIN ipi_ingredientes itroca ON (i.cod_ingredientes_troca=itroca.cod_ingredientes) WHERE i.cod_ingredientes = ".$obj_ingredientes->cod_ingredientes." AND it.cod_tamanhos=" . $cod_tamanhos;
                          //echo $sql_troca;
                            $res_troca = mysql_query($sql_troca);
                            $obj_troca = mysql_fetch_object($res_troca);

                            echo utf8_encode("<span><input type='checkbox' onclick='ccbox(this,\"".$id_normal."\")' id='ingredientes" . $num_fracao."_t".$a."' name='ingredientes_adicionais" . $num_fracao . "[]' value='TROCA###" . $obj_ingredientes->cod_ingredientes_troca . "###" . $obj_ingredientes->cod_ingredientes . "' style='border: 0; background: none;' />");
                        
                            echo "Trocar por ".$obj_troca->ingrediente." (".bd2moeda(arredondar_preco_ingrediente($obj_troca->preco_troca, $num_sabores)).") </span>";
                        }
                        
                        echo "</small></td>";
                    }
                    echo "</tr>";
                    echo "</table>";
                }
                ?>
                <input type="button" name="bt_adicionais_<? echo $num_fracao; ?>" value="Adicionais" onclick="javascript:carregar_adicionais('<? echo $num_fracao; ?>')" />
                
               </td>
            </tr>
        </table>
        <br />
        <?
    }        
    break;
    case "carregar_adicionais":
        $cod_tamanhos = validaVarPost('cod_tamanhos');
        $num_fracao = validaVarPost('num_fracao');
        $num_sabores = validaVarPost('num_sabores');
        ?>
        
        <table border="0" width="500">
             <tr>
                <td colspan="4"><span class="laranja">Adicionais <? echo $num_fracao; ?></span>
                <input type="text" name="cod_adicionais_<? echo $num_fracao; ?>_digito" id="cod_adicionais_<? echo $num_fracao; ?>_digito"
                class="proximo" style="width: 50px;" onkeypress="javascript:selecionar_box(event, this, 'ingredientes_adicionais<? echo $num_fracao; ?>[]');">
                <br />
                <?
                $sql_adicionais = "SELECT it.preco,i.cod_ingredientes,i.ingrediente_abreviado FROM ipi_ingredientes_ipi_tamanhos it LEFT JOIN ipi_ingredientes i ON (i.cod_ingredientes=it.cod_ingredientes) WHERE i.adicional AND it.cod_tamanhos='" . $cod_tamanhos . "' AND it.cod_pizzarias = '".$cod_pizzarias."' AND i.ativo = 1 ORDER BY ingrediente";
                //echo $sql_ingredientes;
                $res_adicionais = mysql_query($sql_adicionais);
                $num_adicionais = mysql_num_rows($res_adicionais);
                if ($num_adicionais > 0)
                {
                    echo "<table cellspacing='5' cellpadding='0' border='0' width='100%'>";
                    echo "<tr><td valign='top' width='156'>";
                    $divisor = floor($num_adicionais / 3);
                    if (($num_adicionais % 3) != 0)
                        $divisor++;
                    for ($a = 0; $a < $num_adicionais; $a++)
                    {
                        if ((($a % $divisor) == 0) && ($a != 0))
                            echo "</td><td valign='top' width='156'>";
                        $obj_adicionais = mysql_fetch_object($res_adicionais);
                        echo "<small>";
                        echo utf8_encode("<input type='checkbox' name='ingredientes_adicionais" . $num_fracao . "[]' tabindex='1' value='" . $obj_adicionais->cod_ingredientes . "' style='border: 0; background: none;' />");
                        
                        echo utf8_encode( $obj_adicionais->cod_ingredientes . " - " . $obj_adicionais->ingrediente_abreviado . " <font style='font-size:9px; '>(" . bd2moeda(arredondar_preco_ingrediente($obj_adicionais->preco, $num_sabores)) . ")</font><br />");
                        //echo "<a href='javascript:;' onMouseover=\"Mostrar('<div style=\'float: left; margin-right: 5px;\'><img src=\'img/ing_mucarela.jpg\'></div><br><strong>".utf8_encode($objAdic->ingrediente_abreviado)."</strong><br><br>".utf8_encode('Descrição ou alguma dica sobre o ingrediente.')."<br><br>')\" onMouseout=\"Esconder()\">".utf8_encode($objAdic->ingrediente_abreviado) . " <font style='font-size:9px; '>(" . bd2moeda(arredondar_preco_ingrediente($objAdic->preco, $qtde_sabor)) . ")</font></a><br />";
                        echo "</small>";
                    }
                    echo "</td></tr>";
                    echo "</table>";
                }
                ?>
                </td>
            </tr>
        </table>
        <br />
        <?
    break;    
    case "exibir_tabela_frete":
      echo "<div style='width:500px'>";
      echo "<label for='busca_bairro'>Digite o bairro:</label>&nbsp;&nbsp;";
      echo "<input type='text' name='busca_bairro' onkeyup='carregar_frete(this.value)' size='30'/>";
      echo "<div id='tabela_frete_div'></div>";
      echo "</div>";
    break;
    case "exibir_tabela_frete_bairros":
        $pagina = 30;
        $bairro = utf8_decode(validaVarPost("tbairro"));
        $sql_buscar_frete = "SELECT distinct c.bairro as bairro, c.cidade as cidade,c.cod_taxa_frete,f.valor_frete,(SELECT ph.tempo_entrega from ipi_pizzarias_horarios ph where ph.cod_pizzarias = c.cod_pizzarias and dia_semana = '".date('w')."' and horario_final_entrega > CURTIME() ORDER BY horario_final_entrega LIMIT 1) as tempo_entrega,
        (select count(distinct(c.bairro)) from ipi_cep c inner join ipi_taxa_frete f on f.cod_taxa_frete = c.cod_taxa_frete where c.cod_pizzarias IN (".implode(",", $_SESSION['usuario']['cod_pizzarias']).")  and bairro like '%".$bairro."%') as total_bairro 
        from ipi_cep c inner join ipi_taxa_frete f on f.cod_taxa_frete = c.cod_taxa_frete where c.cod_pizzarias IN (".implode(",", $_SESSION['usuario']['cod_pizzarias']).") and bairro like '%".$bairro."%' ORDER BY c.bairro LIMIT 30";
        //echo $sql_buscar_frete;
        $res_buscar_frete = mysql_query($sql_buscar_frete);
        $qtd_atual = mysql_num_rows($res_buscar_frete);
        $total = 0;
        //echo "<!-- ".$sql_buscar_frete." -->";
        echo "<table width='500px' style='border:1px solid black' class='listaEdicao'>";
        echo "<thead>";
        echo "<tr>";
        echo "<td style='border-right:1px solid black' align='center'><strong>Bairro</strong></td>";
        echo "<td style='border-right:1px solid black' align='center'><strong>Cidade</strong></td>";
        echo "<td style='border-right:1px solid black' align='center'><strong>Frete</strong></td>";
        echo "<td style='' align='center'><strong>Tempo de entrega aproximado</strong></td>";
        echo "</thead>";
        echo "<tbody>";
        $i = 0;
        while($obj_buscar_frete = mysql_fetch_object($res_buscar_frete))
        {
          $cor = ($i%2==0 ? "background-color:#E5E5E5" : 'background-color:#D5D5D5');
          echo "<tr>";
          echo "<td width='30%' style='border-top:1px solid black;border-right:1px solid black;$cor' align='left'>".utf8_encode($obj_buscar_frete->bairro)."</td>";
          echo "<td width='30%' style='border-top:1px solid black;border-right:1px solid black;$cor' align='left'>".utf8_encode($obj_buscar_frete->cidade)."</td>";
          echo "<td width='20%' style='border-top:1px solid black;border-right:1px solid black;$cor' align='right'>R$ ".bd2moeda($obj_buscar_frete->valor_frete)."</td>";
          echo "<td width='20%' style='border-top:1px solid black;$cor' align='center'>".$obj_buscar_frete->tempo_entrega." minutos</td>";
          echo "</tr>";
          $total = $obj_buscar_frete->total_bairro;
          $i++;
        }
        echo "<tr><td style='border-top:1px solid black;font-weight:bold' colspan='4'>Exibindo $qtd_atual de $total </td></tr>";
        echo "</tbody>";
        echo "</table>";

    break;
    case "definir_cliente":
        $cod_clientes = validaVarPost("cod");
        $cod_enderecos = validaVarPost("cod_enderecos");
        $_SESSION['ipi_caixa']['tipo_cliente'] = 'ANTIGO';
        $_SESSION['ipi_caixa']['cliente']['cod_clientes'] = $cod_clientes;
        $_SESSION['ipi_caixa']['cliente']['cod_enderecos'] = $cod_enderecos;
        
        $sql_clientes = "SELECT * FROM ipi_clientes c LEFT JOIN ipi_enderecos e ON (c.cod_clientes=e.cod_clientes) WHERE c.cod_clientes = $cod_clientes AND e.cod_enderecos = $cod_enderecos";
        $res_clientes = mysql_query($sql_clientes);
        $num_colunas = mysql_num_fields($res_clientes);
        $arr_clientes = mysql_fetch_array($res_clientes);
        $arr_json = array();
        $cep = "";
        for ($a=0; $a<$num_colunas; $a++)
        {
            $campo = mysql_fetch_field($res_clientes, $a);

            if ($campo->name=="nascimento")
            {
                $arr_json[$campo->name] = utf8_encode(bd2data($arr_clientes[$campo->name]));
            }
            else if($campo->name == "telefone_1" || $campo->name == "telefone_2" || $campo->name == "celular")
            {
                if($arr_clientes[$campo->name]=="")
                {
                    $arr_clientes[$campo->name] = $_SESSION['usuario']['ddd_pizzaria'];
                }
                $arr_json[$campo->name] = utf8_encode($arr_clientes[$campo->name]); 
            }
            else if($campo->name == "cidade")
            {
                if($arr_clientes[$campo->name]=="")
                {
                    $arr_clientes[$campo->name] = $_SESSION['usuario']['cidade_pizzaria'];
                }
                $arr_json[$campo->name] = utf8_encode($arr_clientes[$campo->name]); 
            }
            else if($campo->name == "estado")
            {
                if($arr_clientes[$campo->name]=="")
                {
                    $arr_clientes[$campo->name] = $_SESSION['usuario']['estado_pizzaria'];
                }
                $arr_json[$campo->name] = utf8_encode($arr_clientes[$campo->name]); 
            }
            else
            {
                $arr_json[$campo->name] = utf8_encode($arr_clientes[$campo->name]);
            }
            if($campo->name="cep")
            {
                $cep = $arr_clientes[$campo->name];
            }
        }

        if($cep!="")
        {
            $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep));

            $sql_cep = "SELECT COUNT(*) AS contagem FROM ipi_cep WHERE cep_inicial <= '$cep_limpo' AND cep_final >= '$cep_limpo'";
            //echo $sql_cep."<br/>";
            $res_cep = mysql_query($sql_cep);
            $ObjCep = mysql_fetch_object($res_cep);
            $contagem = $objCep->contagem; 

            $sql_cod_pizzarias = "SELECT cod_pizzarias FROM ipi_cep WHERE cep_inicial <= '$cep_limpo' AND cep_final >= '$cep_limpo' LIMIT 1";
            $res_cod_pizzarias = mysql_query($sql_cod_pizzarias);
            $obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias);
            $cod_pizzarias = $obj_cod_pizzarias->cod_pizzarias;
            while($obj_cod_pizzarias = mysql_fetch_object($res_cod_pizzarias))
            {
                $arr_cod_pizzarias[]['cod_pizzarias'] = $obj_cod_pizzarias->cod_pizzarias;
            }

            if($cod_pizzarias=="")
            {
                $arr_json["fora_cobertura"] = utf8_encode("Cep fora da área de cobertua!");
                $_SESSION['ipi_caixa']['cliente']['cobertura'] = "Fora";
                $arr_json['cod_pizzarias'] = "";
            }
            else
            {
                 unset($_SESSION['ipi_caixa']['cliente']['cobertura']);
                 $arr_json['cod_pizzarias'] = $cod_pizzarias;
            }
        }else
        {
            $arr_json['cod_pizzarias'] = "";
            //$arr_json["fora_cobertura"] = "";
        }

        echo json_encode($arr_json);
        
        break;
    case "buscar_clientes":
        $busca_cliente = validaVarPost("busca_cliente");
        if(substr($busca_cliente,-5,1)=='-')
        {
            $tel = $busca_cliente;
        }
        else
        {
            $tel = substr($busca_cliente,0,-4).'-'.substr($busca_cliente,-4,4);
        }
        //echo "<br/>tel = =".$tel."=<br/><br/>";
        $sql_clientes = "SELECT count(*) as qtd FROM ipi_clientes c RIGHT JOIN ipi_enderecos e ON (c.cod_clientes=e.cod_clientes) WHERE (c.nome like'%".$busca_cliente."%') OR (c.email like'%".$busca_cliente."%') OR (c.cpf like'%".$busca_cliente."%') OR (c.celular like'%".$tel."%') OR (e.telefone_1 like'%".$tel."%') OR (e.telefone_2 like'%".$tel."%')";
        $res_clientes = mysql_query($sql_clientes);
        $obj_clientesnum = mysql_fetch_object($res_clientes);
        $num_clientes = $obj_clientesnum->qtd;
    
        $sql_clientes = "SELECT c.cod_clientes,c.email,c.nome,celular,e.cod_enderecos,e.endereco,e.numero,e.bairro,e.complemento,e.edificio,e.cidade,e.telefone_1,e.telefone_2 FROM ipi_clientes c RIGHT JOIN ipi_enderecos e ON (c.cod_clientes=e.cod_clientes) WHERE (c.nome like'%".$busca_cliente."%') OR (c.email like'%".$busca_cliente."%') OR (c.cpf like'%".$busca_cliente."%') OR (c.celular like'%".$tel."%') OR (e.telefone_1 like'%".$tel."%') OR (e.telefone_2 like'%".$tel."%') LIMIT 10";
        $res_clientes = mysql_query($sql_clientes);
        
        if ($num_clientes>0)
        {
            echo utf8_encode('<br /><center><b>'.$num_clientes.'</b> clientes encontrados!</center><br />');
            echo utf8_encode('<table class="listaEdicao" cellpadding="0" border="1" width="500" cellspacing="0">');
            echo utf8_encode('<thead>');
            echo utf8_encode('<tr height="22">');
            echo utf8_encode('<td align="center" width="80"><b>Selecionar</b></td>');
            echo utf8_encode('<td align="center" width="320"><b>Nome</b></td>');
            echo utf8_encode('<td align="center" width="100"><b>Telefones</b></td>');
            echo utf8_encode('</tr>');
            echo utf8_encode('</thead>');
            echo utf8_encode('<tbody>');
            while ($obj_clientes = mysql_fetch_object($res_clientes)) 
            {
                echo utf8_encode('<tr>');
                echo utf8_encode('<td align="center"><input type="button" name="bt_selecionar" value=" X " class="proximo_cliente" onclick="javascript:definir_cliente('.$obj_clientes->cod_clientes.','.$obj_clientes->cod_enderecos.');"></td>');
                echo utf8_encode('<td align="left" style="padding: 3px;"><b>'.bd2texto($obj_clientes->nome)."</b><br />".bd2texto($obj_clientes->email));
                echo utf8_encode( "<br />".bd2texto($obj_clientes->endereco).", ".bd2texto($obj_clientes->numero)."<br />".bd2texto($obj_clientes->bairro) );
                if ($obj_clientes->complemento)
                    echo utf8_encode("<br /><i>Complemento:</i> ".bd2texto($obj_clientes->complemento));
                if ($obj_clientes->edificio)
                    echo utf8_encode("<br /><i>Edificio:</i> ".bd2texto($obj_clientes->edificio));
                echo utf8_encode("<br /><u>".bd2texto($obj_clientes->cidade)."</u>");
                echo utf8_encode('</td><td align="center">');

                echo utf8_encode(bd2texto($obj_clientes->celular));
                $sql_telefones = "SELECT * FROM ipi_enderecos WHERE cod_clientes=".$obj_clientes->cod_clientes." AND cod_enderecos=".$obj_clientes->cod_enderecos;
                $res_telefones = mysql_query($sql_telefones);
                while ($obj_telefones = mysql_fetch_object($res_telefones))
                {
                    if ($obj_telefones->telefone_1)
                        echo "<br />".utf8_encode(bd2texto($obj_telefones->telefone_1));
                    if ($obj_telefones->telefone_2)
                        echo "<br />".utf8_encode(bd2texto($obj_telefones->telefone_2));
                }
                
                echo utf8_encode('</td>');
                echo utf8_encode('</tr>');
            }
            echo utf8_encode('</tbody>');
            echo utf8_encode('</table>');      
        }
        else 
        {
            echo utf8_encode('<br><br><center>Nenhum cliente encontrado <b>('.$busca_cliente.')</b></center>');      
        }
    break;
    case "carregarCombo":
    
        $tamanho_pizza = $_POST['tamanho_pizza'];
        $sabor = $_POST['sabor'];
        $cod_tipo_pizza = validar_var_post('cod_tipo_pizza');
        
        if ($_POST['cod'] == "cod_bordas")
        {
            echo utf8_encode("<option value='0'>0 - Não</option>");
            $sqlBordas = "SELECT tb.cod_bordas,b.borda,tb.preco FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_bordas tb ON (tb.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_bordas b ON (tb.cod_bordas=b.cod_bordas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND tb.cod_pizzarias = '".$cod_pizzarias."' ORDER BY b.borda";
            $resBordas = mysql_query($sqlBordas);
            $linBordas = mysql_num_rows($resBordas);
            
            if ($linBordas > 0)
            {
                for ($a = 0; $a < $linBordas; $a++)
                {
                    $objBordas = mysql_fetch_object($resBordas);
                    $preco_borda = $objBordas->preco;
                    
                    if ($_SESSION['ipi_caixa']['combo']['qtde_bordas']>0)                    
                    {    
                        $preco_borda = "Combo";
                    }
                    
                    echo utf8_encode('<option value="' . $objBordas->cod_bordas . '">' .$objBordas->cod_bordas." - ". $objBordas->borda . ' (' . $preco_borda . ')</option>');
                }
            }
        }
        
        if ($_POST['cod'] == "num_sabores")
        {
            //$sqlFracoes = "SELECT * FROM ipi_tamanhos_ipi_fracoes_precos tf INNER JOIN ipi_fracoes_precos fp ON (tf.cod_fracoes_precos=fp.cod_fracoes_precos) WHERE tf.cod_tamanhos=" . $tamanho_pizza . " ORDER BY fp.fracao";
            $sqlFracoes = "SELECT f.fracoes,tf.preco,tf.selecao_padrao_fracao FROM ipi_tamanhos_ipi_fracoes tf INNER JOIN ipi_fracoes f ON (tf.cod_fracoes=f.cod_fracoes) WHERE tf.cod_tamanhos='" . $tamanho_pizza . "' AND tf.cod_pizzarias = '".$cod_pizzarias."' ORDER BY f.fracoes";

            $resFracoes = mysql_query($sqlFracoes);
            $linFracoes = mysql_num_rows($resFracoes);
            if ($linFracoes > 0)
            {
                for ($a = 0; $a < $linFracoes; $a++)
                {
                    
                    $objFracoes = mysql_fetch_object($resFracoes);
                    
                    echo utf8_encode('<option value="' . $objFracoes->fracoes . '" '.($objFracoes->selecao_padrao_fracao==1 ? 'selected="selected"' : '').'>' . $objFracoes->fracoes);
                    
                    
                    if ($objFracoes->preco != "0.00")
                    {
                        echo utf8_encode(' (+ R$' . bd2moeda($objFracoes->preco) . ')');
                    }
                    
                    echo utf8_encode('</option>');
                }
            }
        }
        
        if ($_POST['cod'] == "cod_tipo_massa")
        {
            $sql_tipo_massa = "SELECT tm.cod_tipo_massa,tm.tipo_massa,tt.preco,tt.selecao_padrao_massa FROM ipi_tamanhos_ipi_tipo_massa tt INNER JOIN ipi_tipo_massa tm ON (tt.cod_tipo_massa=tm.cod_tipo_massa) WHERE tt.cod_tamanhos=" . $tamanho_pizza . " ORDER BY tm.tipo_massa";
            $res_tipo_massa = mysql_query($sql_tipo_massa);
            $num_tipo_massa = mysql_num_rows($res_tipo_massa);
            if ($num_tipo_massa > 0)
            {
                for ($a = 0; $a < $num_tipo_massa; $a++)
                {
                    
                    $obj_tipo_massa = mysql_fetch_object($res_tipo_massa);
                    
                    echo utf8_encode('<option value="' . $obj_tipo_massa->cod_tipo_massa  . '" '.($obj_tipo_massa->selecao_padrao_massa==1 ? 'selected="selected"' : '').' >' . $obj_tipo_massa->cod_tipo_massa . " - " . $obj_tipo_massa->tipo_massa);
                    
                    if ($obj_tipo_massa->preco != "0.00")
                    {
                        echo utf8_encode(' (+ R$' . bd2moeda($obj_tipo_massa->preco) . ')');
                    }
                    
                    echo utf8_encode('</option>');
                }
            }
        }
        
        if ($_POST['cod'] == "cod_opcoes_corte")
        {
            $sql_corte = "SELECT toc.cod_opcoes_corte,oc.opcao_corte,toc.preco,toc.selecao_padrao_corte FROM ipi_tamanhos_ipi_opcoes_corte toc INNER JOIN ipi_opcoes_corte oc ON (toc.cod_opcoes_corte=oc.cod_opcoes_corte) WHERE toc.cod_tamanhos=" . $tamanho_pizza . " ORDER BY oc.opcao_corte";
            $res_corte = mysql_query($sql_corte);
            $num_corte = mysql_num_rows($res_corte);
            if ($num_corte > 0)
            {
                for ($a = 0; $a < $num_corte; $a++)
                {
                    
                    $obj_corte = mysql_fetch_object($res_corte);
                    
                    echo utf8_encode('<option value="' . $obj_corte->cod_opcoes_corte  . '"');
                    if ($obj_corte->selecao_padrao_corte==1)
                      echo ' selected="selected"';
                    echo utf8_encode('>' . $obj_corte->cod_opcoes_corte . " - " . $obj_corte->opcao_corte);
                    
                    if ($obj_corte->preco != "0.00")
                    {
                        echo utf8_encode(' (+ R$' . bd2moeda($obj_corte->preco) . ')');
                    }
                    
                    echo utf8_encode('</option>');
                }
            }
        }
        
        if ($_POST['cod'] == "cod_adicionais")
        {
            echo utf8_encode("<option value='0'>0 - Não</option>");
            $sqlBordas = "SELECT ta.cod_adicionais,a.adicional,ta.preco,ta.selecao_padrao_adicional FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_adicionais ta ON (ta.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_adicionais a ON (ta.cod_adicionais=a.cod_adicionais) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND ta.cod_pizzarias = '".$cod_pizzarias."' ORDER BY a.adicional";
            $resBordas = mysql_query($sqlBordas);
            $linBordas = mysql_num_rows($resBordas);
            if ($linBordas > 0)
            {
                for ($a = 0; $a < $linBordas; $a++)
                {
                    $objBordas = mysql_fetch_object($resBordas);
                    echo utf8_encode('<option value="' . $objBordas->cod_adicionais . '" '.($objBordas->selecao_padrao_adicional==1 ? 'selected="selected"' : '').'>' . $objBordas->cod_adicionais ." - ". $objBordas->adicional . ' (' . bd2moeda($objBordas->preco) . ')</option>');
                }
            }
        }
        
        if ($_POST['cod'] == "tipo_massa")
        {
            $sqlBordas = "SELECT tm.preco,m.tipo_massa,m.cod_tipo_massa FROM ipi_tamanhos t INNER JOIN ipi_tamanhos_ipi_tipo_massa tm ON (tm.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_tipo_massa m ON (tm.cod_tipo_massa=m.cod_tipo_massa) WHERE t.cod_tamanhos=" . $tamanho_pizza . " ORDER BY m.tipo_massa";
            $resBordas = mysql_query($sqlBordas);
            $linBordas = mysql_num_rows($resBordas);
            
            if ($linBordas > 0)
            {
                for ($a = 0; $a < $linBordas; $a++)
                {
                    $objBordas = mysql_fetch_object($resBordas);
                    
                    if($objBordas->preco > 0)
                    {
                        echo utf8_encode('<option value="' . $objBordas->cod_tipo_massa . '" '.($objBordas->selecao_padrao_corte==1 ? 'selected="selected"' : '').'>' . $objBordas->tipo_massa . ' (' . bd2moeda($objBordas->preco) . ')</option>');    
                    }
                    else
                    {
                        echo utf8_encode('<option value="' . $objBordas->cod_tipo_massa . '" '.($objBordas->selecao_padrao_corte==1 ? 'selected="selected"' : '').'>' . $objBordas->tipo_massa . '</option>');
                    }
                }
            }
        }
        
        if ((($_POST['cod'] == "cod_pizzas_1") || ($_POST['cod'] == "cod_pizzas_2") || ($_POST['cod'] == "cod_pizzas_3") || ($_POST['cod'] == "cod_pizzas_4")) )
        {
            $sqlPizzas = "SELECT p.cod_pizzas,p.pizza,pt.preco FROM ipi_tamanhos t INNER JOIN ipi_pizzas_ipi_tamanhos pt ON (pt.cod_tamanhos=t.cod_tamanhos) INNER JOIN ipi_pizzas p ON (pt.cod_pizzas=p.cod_pizzas) WHERE t.cod_tamanhos=" . $tamanho_pizza . " AND pt.cod_pizzarias = '".$cod_pizzarias."'";

            if ($sabor)
            {
              //$sqlPizzas .= " AND p.cod_tipo_pizza='".$sabor."'";
              $sqlPizzas .= " AND p.cod_pizzas IN (".$sabor.")";
            }

            if ($cod_tipo_pizza)
            {
              $sqlPizzas .= " AND p.cod_tipo_pizza = '".$cod_tipo_pizza."'";
            }

            $sqlPizzas .= " ORDER BY p.pizza";
            //echo $sqlPizzas;
            
            $resPizzas = mysql_query($sqlPizzas);
            $linPizzas = mysql_num_rows($resPizzas);
            
            if ($linPizzas > 0)
            {
                for ($a = 0; $a < $linPizzas; $a++)
                {
                    $objPizzas = mysql_fetch_object($resPizzas);
                    echo utf8_encode('<option value="' . $objPizzas->cod_pizzas . '">' . $objPizzas->cod_pizzas . " - " . $objPizzas->pizza . ' (' . bd2moeda($objPizzas->preco) . ')</option>');
                }
            }
        }

      if ($_POST['cod'] == "cod_tamanhos")
      {
        
        $sql_tamanhos = "SELECT DISTINCT(pt.cod_tamanhos), t.tamanho FROM ipi_pizzas_ipi_tamanhos pt LEFT JOIN ipi_pizzas p ON (pt.cod_pizzas = p.cod_pizzas) LEFT JOIN ipi_tipo_pizza tp ON (p.cod_tipo_pizza = tp.cod_tipo_pizza) LEFT JOIN ipi_tamanhos t ON (pt.cod_tamanhos = t.cod_tamanhos) ";
        
        if ($cod_tipo_pizza)
        {
          $sql_tamanhos .= " WHERE p.cod_tipo_pizza = '".$cod_tipo_pizza."'";
        }
//echo $sql_tamanhos;
        $res_tamanhos = mysql_query($sql_tamanhos);
        $num_tamanhos = mysql_num_rows($res_tamanhos);
        
        if ($num_tamanhos > 0)
        {
          for ($a = 0; $a < $num_tamanhos; $a++)
          {
            $obj_tamanhos = mysql_fetch_object($res_tamanhos);
            echo utf8_encode('<option value="' . $obj_tamanhos->cod_tamanhos . '">' . $obj_tamanhos->cod_tamanhos . " - " . $obj_tamanhos->tamanho . '</option>');
          }
        }

      }
        
    break;
    case "revisar_pedido":
        
        
        $numero_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
        if ($numero_pizzas > 0)
        {
            

            $num_pizzas = isset($_SESSION['ipi_caixa']['pedido']) ? count($_SESSION['ipi_caixa']['pedido']) : 0;
            for ($a = 0; $a < $num_pizzas; $a++)
            {
                
                echo utf8_encode('<hr><h2>');  
                
                echo utf8_encode('<form method="post" name="frm_revisar_pizza_'.$a.'" id="frm_revisar_pizza_'.$a.'" action="ipi_caixa_acoes.php" style="display: inline">');
                echo utf8_encode('<input type="hidden" name="tipo" value="pizza">');
                echo utf8_encode('<input type="hidden" name="acao" value="remover">');
                echo utf8_encode('<input type="hidden" name="id_sessao" value="'.$_SESSION['ipi_caixa']['pedido'][$a]['pizza_id_sessao'].'">');
                echo utf8_encode('<input type="button" name="bt_revisar_'.$a.'" id="bt_revisar_'.$a.'" value="X" onClick="javascript:if(confirm(\'Deseja realmente excluir esta pizza?\')){document.frm_revisar_pizza_'.$a.'.submit();}">');
                echo utf8_encode('</form> ');
                
                echo utf8_encode(($a + 1) . 'ª Pizza');
                
                $preco_pizza = $_SESSION['ipi_caixa']['pedido'][$a]['preco_pizza'];
                $tem_preco = true;

                $preco_promocional = 0;

                if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_promocional'] == "1")
                {
                    $tem_preco = false;
                    echo utf8_encode(" (GRÁTIS)");
                }
                if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_fidelidade'] == "1")
                {
                    $tem_preco = false;
                    echo utf8_encode(" (FIDELIDADE)");
                }
                if ($_SESSION['ipi_caixa']['pedido'][$a]['pizza_combo'] == "1")
                {
                    $tem_preco = false;
                    echo utf8_encode(" (COMBO)");
                }
                if ($_SESSION['ipi_caixa']['pedido'][$a]['preco_promocional'] == "1")
                {
                    $tem_preco = true;
                    $preco_promocional = 1;
                    echo utf8_encode(" (PROMOCIONAL)");
                }
                if($_SESSION['ipi_caixa']['pedido'][$a]['porcentagem_promocional'] == "1")
                {
                    $tem_preco = true;
                    $preco_promocional = 1;
                    echo utf8_encode(" (".$_SESSION['ipi_caixa']['pedido'][$a]['valor_porcentagem_promocional']." %)");
                }
                if($tem_preco)
                {
                    echo " ( <span style='color:red'>R$ ".bd2moeda($preco_pizza)."</span> ) ";
                }
                echo utf8_encode('</h2>');
                    
                /*
                  //Excluir temparariamente removido
                echo '<form method="post" action="ipi_req_carrinho_acoes.php" name="frmExcluirPizza_' . $a . '" style="margin: 0px">';
                echo '<input type="hidden" name="ind_ses" value="' . $a . '">';
                echo '<input type="hidden" name="acao" value="excluir_pizza">';
                echo '</form>';
                echo "<div style='text-align: right'><a href='javascript:confirmar_excluir_pizza(document.frmExcluirPizza_{$a},\"pizza\");'>Remover</a></div>";
                */
                                
                $sqlAux = "SELECT tamanho FROM ipi_tamanhos WHERE cod_tamanhos=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                echo utf8_encode('<b>Tamanho:</b> ' . $objAux->tamanho);
                echo utf8_encode('<br><b>Quantidade de Sabores:</b> ' . $_SESSION['ipi_caixa']['pedido'][$a]['quantidade_fracoes']);
                if($_SESSION['ipi_caixa']['pedido'][$a]['preco_divisao']>0)
                {
                    echo " ( <span style='color:red'>R$ ".bd2moeda($_SESSION['ipi_caixa']['pedido'][$a]['preco_divisao'])."</span> ) ";
                }
                
                if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'] != "0")
                {
                    $sqlAux = "SELECT borda FROM ipi_bordas WHERE cod_bordas=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_bordas'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    $preco_borda = $_SESSION['ipi_caixa']['pedido'][$a]['preco_borda'];
                    $tem_preco = true;
                    echo utf8_encode('<br><b>Borda:</b> ' . $objAux->borda);
                    if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_promocional'] == "1")
                    {
                        $tem_preco = false;
                        echo utf8_encode(" (GRÁTIS)");
                    }
                    if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_fidelidade'] == "1")
                    {
                        $tem_preco = false;
                        echo utf8_encode(" (FIDELIDADE)");
                    }
                    if ($_SESSION['ipi_caixa']['pedido'][$a]['borda_combo'] == "1")
                    {
                        $tem_preco = false;
                        echo utf8_encode(" (COMBO)");
                    }
                    if($tem_preco)
                    {
                        echo " ( <span style='color:red'>R$ ".bd2moeda($preco_borda)."</span> ) ";
                    }
                }
                else
                {
                    echo utf8_encode('<br><b>Borda:</b> Não');
                }
                
/*                if ($_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'] != "0")
                {
                    $sqlAux = "SELECT adicional FROM ipi_adicionais WHERE cod_adicionais=" . $_SESSION['ipi_caixa']['pedido'][$a]['cod_adicionais'];
                    $resAux = mysql_query($sqlAux);
                    $objAux = mysql_fetch_object($resAux);
                    echo utf8_encode('<br><b>Gergelim:</b> ' . $objAux->adicional)." ( <span style='color:red'>R$ ".bd2moeda($_SESSION['ipi_caixa']['pedido'][$a]['preco_adicional'])."</span> )";
                }
                else
                {
                    echo utf8_encode('<br><b>Gergelim:</b> Não');
                }*/
                
                $sqlAux = "SELECT tm.tipo_massa,tt.preco FROM ipi_tipo_massa tm INNER JOIN ipi_tamanhos_ipi_tipo_massa tt ON (tm.cod_tipo_massa = tt.cod_tipo_massa) WHERE tm.cod_tipo_massa = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_tipo_massa']." AND tt.cod_tamanhos = ".$_SESSION['ipi_caixa']['pedido'][$a]['cod_tamanhos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);
                
                echo utf8_encode('<br><b>Tipo da Massa:</b> '.$objAux->tipo_massa);
                
                if($objAux->preco > 0)
                {
                    echo utf8_encode('&nbsp;(<span style="color:red">'.bd2moeda($objAux->preco).'</span>)');   
                }
                
                
                $num_fracoes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao']);
                for ($b = 0; $b < $num_fracoes; $b++)
                {
                    if($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'] > 0)
                    {
                    
                        $sqlAux = "SELECT pizza FROM ipi_pizzas WHERE cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'];
                        $resAux = mysql_query($sqlAux);
                        $objAux = mysql_fetch_object($resAux);
                        echo utf8_encode("<br><br><b>" . ($b + 1) . "º Sabor:</b> " . $objAux->pizza);
                        
                        $num_ingredientes = count($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes']);
                        $ingredientes_padroes = array ();
                        $ingredientes_nao_padroes = array ();
                        $precos_ingredientes = array();
                        $ind_aux_padrao = 0;
                        $ind_aux_nao_padrao = 0;
                        for ($c = 0; $c < $num_ingredientes; $c++)
                        {
                            /*$sqlAux = "SELECT * FROM ipi_ingredientes WHERE cod_ingredientes=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                            $resAux = mysql_query($sqlAux);
                            $objAux = mysql_fetch_object($resAux);*/
                            
                            if ($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == true)
                            {
                                $ingredientes_padroes[$ind_aux_padrao] = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                                $ind_aux_padrao++;
                            }
                            if ($_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['ingrediente_padrao'] == false)
                            {
                                $ingredientes_nao_padroes[$ind_aux_nao_padrao] = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes'];
                                $precos_ingredientes[$_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['cod_ingredientes']] = $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['ingredientes'][$c]['preco'];
                                $ind_aux_nao_padrao++;
                            }
                        }
                        
                        if (count($ingredientes_padroes) > 0)
                        {
                            $sqlAux = "SELECT i.ingrediente FROM ipi_ingredientes_ipi_pizzas ip INNER JOIN ipi_ingredientes i ON (ip.cod_ingredientes=i.cod_ingredientes) WHERE ip.cod_pizzas=" . $_SESSION['ipi_caixa']['pedido'][$a]['fracao'][$b]['cod_pizzas'] . " AND ip.cod_ingredientes NOT IN (" . implode(",", $ingredientes_padroes) . ") AND consumo = 0";
                            $resAux = mysql_query($sqlAux);
                            $linAux = mysql_num_rows($resAux);
                            //echo $sqlAux;
                            if ($linAux > 0)
                            {
                                echo utf8_encode("<br><b>Ingredientes:</b> ");
                                while ($objAux = mysql_fetch_object($resAux))
                                {
                                    echo utf8_encode("SEM " . $objAux->ingrediente . ", ");
                                }
                            }
                        }
                        
                        if (count($ingredientes_nao_padroes) > 0)
                        {
                            $sqlAux = "SELECT i.ingrediente,i.cod_ingredientes FROM ipi_ingredientes i WHERE i.cod_ingredientes IN (" . implode(",", $ingredientes_nao_padroes) . ")";
                            $resAux = mysql_query($sqlAux);
                            $linAux = mysql_num_rows($resAux);
                            if ($linAux > 0)
                            {
                                echo utf8_encode("<br><b>Adicionais:</b> ");
                                while ($objAux = mysql_fetch_object($resAux))
                                {
                                    $preco_ing = "";
                                    if($precos_ingredientes[$objAux->cod_ingredientes]>0)
                                    {
                                        $preco_ing = " ( <span style='color:red'>R$ ".bd2moeda($precos_ingredientes[$objAux->cod_ingredientes])."</span> ) ";
                                    }
                                    echo utf8_encode($objAux->ingrediente . " $preco_ing, ");
                                }
                            }
                        }
                        else
                        {
                            echo utf8_encode("<br><b>Adicionais:</b> Sem adicionais");
                        }
                    
                    }
                
                }
                /*
                // Desconto pelo PDV
                if($preco_promocional==0)
                {
                    echo utf8_encode("<br/><br/>Valor dos sabores da pizza: <span style='color:red'>R$ ".bd2moeda($_SESSION['ipi_caixa']['pedido'][$a]['preco_total_fracoes'])."</span> ");
                    echo utf8_encode("<br/><form name='preco_promocional' action='ipi_caixa_acoes.php' method='post'>Alterar Preço:<select name='valor_preco_motivos'><option value='6'>9,90</option><option value='7'>25,00</option><option value='8'>50%</option></select><br/><input type='hidden' value='preco_promocional' name='acao' /><input type='hidden' value='incluir' name='tipo' /><input type='hidden' value='".$_SESSION['ipi_caixa']['pedido'][$a]['pizza_id_sessao']."' name='id_sessao_pizza' /><input type='submit' value='Aplicar desconto' /></form>");//no select colocar "d" para desconto em % ou "p" para preco promocional, um traço e depois e o valor e depois o cod_motivo_promocoes
                }
                elseif($preco_promocional==1)
                {
                    echo utf8_encode("<br/><br/>Valor dos sabores da pizza: <span style='color:red'>R$ ".bd2moeda($_SESSION['ipi_caixa']['pedido'][$a]['preco_total_fracoes'])."</span> ");
                    echo utf8_encode("<br/><form name='preco_promocional' action='ipi_caixa_acoes.php' method='post'><input type='hidden' value='preco_promocional' name='acao' /><input type='hidden' value='excluir' name='tipo' /><input type='hidden' value='".$_SESSION['ipi_caixa']['pedido'][$a]['pizza_id_sessao']."' name='id_sessao_pizza' /><input type='submit' value='Remover desconto' /></form>");
                }
                echo utf8_encode('<br /><br />');
                */
            }
            
            echo utf8_encode('<hr>');
        }
        
        
        $numero_bebidas = isset($_SESSION['ipi_caixa']['bebida']) ? count($_SESSION['ipi_caixa']['bebida']) : 0;
        if ($numero_bebidas > 0)
        {
            
            echo utf8_encode("<br><h2>BEBIDAS:</h2> ");
            
            for ($a = 0; $a < $numero_bebidas; $a++)
            {
                $sqlAux = "SELECT b.bebida,c.conteudo FROM ipi_bebidas b INNER JOIN ipi_bebidas_ipi_conteudos bc ON (bc.cod_bebidas=b.cod_bebidas) INNER JOIN ipi_conteudos c ON (bc.cod_conteudos=c.cod_conteudos) WHERE bc.cod_bebidas_ipi_conteudos=" . $_SESSION['ipi_caixa']['bebida'][$a]['cod_bebidas_ipi_conteudos'];
                $resAux = mysql_query($sqlAux);
                $objAux = mysql_fetch_object($resAux);

                echo utf8_encode('<form method="post" name="frm_revisar_bebida_'.$a.'" id="frm_revisar_bebida_'.$a.'" action="ipi_caixa_acoes.php" style="display: inline;" >');
                echo utf8_encode('<input type="hidden" name="tipo" value="bebida">');
                echo utf8_encode('<input type="hidden" name="acao" value="remover">');
                echo utf8_encode('<input type="hidden" name="id_sessao" value="'.$_SESSION['ipi_caixa']['bebida'][$a]['bebida_id_sessao'].'">');
                echo utf8_encode('<input type="button" name="bt_revisar_bebida_'.$a.'" id="bt_revisar_bebida_'.$a.'" value="X" onClick="javascript:if(confirm(\'Deseja realmente excluir esta bebida?\')){document.frm_revisar_bebida_'.$a.'.submit();}">');
                echo utf8_encode('</form> ');
                
                $preco_bebida = $_SESSION['ipi_caixa']['bebida'][$a]['preco_bebida'];
                $tem_preco = true;
                echo utf8_encode('<b>Quantidade:</b> ' . $_SESSION['ipi_caixa']['bebida'][$a]['quantidade']);

                if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_promocional'] == "1")
                {
                    $tem_preco = false;
                    echo utf8_encode(" (GRÁTIS)");
                }
                if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_fidelidade'] == "1")
                {
                    $tem_preco = false;
                    echo utf8_encode(" (FIDELIDADE)");
                }
                if ($_SESSION['ipi_caixa']['bebida'][$a]['bebida_combo'] == "1")
                {
                    $tem_preco = false;
                    echo utf8_encode(" (COMBO)");
                }
                if($tem_preco)
                {
                    echo "( <span style='color:red'>R$ ".bd2moeda($preco_bebida)."</span> ) ";
                }
                echo utf8_encode('<br><b>Sabor:</b> ' . $objAux->bebida . " - " . $objAux->conteudo . "<hr /><br />");
            }
            
        }        
        
        //echo "<pre>";
        //print_r($_SESSION['ipi_caixa']['pedido']);
       // echo "</pre>";
        if (($numero_pizzas) || ($numero_bebidas))
        {
            //echo "<center><b><font color='#FF0000'>Total do pedido: <br /><font color='#FF0000' size='4'>R$ " . $this->calcular_total() . "</font></font></b></center>";
            if ($numero_pizzas)
            {
                echo "<script> $('bt_revisar_0').focus(); </script>";
            }
            else if ($numero_bebidas)
            {
                echo "<script> $('bt_revisar_bebida_0').focus(); </script>";
            }
        }
        else
        {
            echo "<br /><center><b>Pedido vazio!</b></center>";
        }
    break;
    case "carregar_formas_pagamento":
        $cod_pizzaria = validaVarPost("codp");
        $cod_formapg = validaVarPost('codfp');
        $conexao = conectar_bd();

        $sql_formas_pg = "SELECT fp.cod_formas_pg,fp.forma_pg FROM ipi_formas_pg fp INNER JOIN ipi_formas_pg_pizzarias fpp on fpp.cod_formas_pg = fp.cod_formas_pg WHERE fpp.cod_pizzarias = '$cod_pizzaria' ORDER BY forma_pg";
        $res_formas_pg = mysql_query($sql_formas_pg);
        while($obj_formas_pg = mysql_fetch_object($res_formas_pg)) 
        {
            echo '<option value="'.utf8_encode($obj_formas_pg->forma_pg).'" ';
            if($cod_formapg == $obj_formas_pg->cod_formas_pg)
                echo 'selected';
            echo '>'.utf8_encode(bd2texto($obj_formas_pg->forma_pg)).'</option>';
        }
        desconectabd($conexao);
    break;
    case "verificar_pizzaria_existe":
    //echo "SHUDFHAFUDA";
        $retorno = array();
        $conexao = conectar_bd();
        $cep = validaVarPost("cep");
        $bairro = utf8_decode(validaVarPost("bairro"));
        if($cep!="")
        {
            $cep_limpo = str_replace ( "-", "", str_replace('.', '', $cep));
            $sql_buscar_pizzaria = "SELECT cod_pizzarias from ipi_cep where cep_inicial <= '$cep_limpo' and cep_final >='$cep_limpo'";
           // echo ".cep".$sql_buscar_pizzaria;
            $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
            $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
        }
        else
        {
            $sql_buscar_pizzaria = "SELECT cod_pizzarias from ipi_cep where bairro like '$bairro'";
            //echo ".bs".$sql_buscar_pizzaria;
            $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
            $num_buscar_pizzaria = mysql_num_rows($res_buscar_pizzaria);
            if ($num_buscar_pizzaria>0)
            {
                $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
            }
            else
            {

                $sql_buscar_pizzaria = "SELECT cod_pizzarias from ipi_cep where bairro like '%$bairro%'";
                //echo ".bs".$sql_buscar_pizzaria;
                $res_buscar_pizzaria = mysql_query($sql_buscar_pizzaria);
                $obj_buscar_pizzaria = mysql_fetch_object($res_buscar_pizzaria);
            }
        }
        //echo "p-".$obj_buscar_pizzaria->cod_pizzarias."-";
        $cod_pizzarias = $obj_buscar_pizzaria->cod_pizzarias;
        
        if($cod_pizzarias=="")
        {
            $cod_pizzarias = "n";
        }
        require("../../pub_req_fuso_horario1.php");

        desconectabd($conexao);
        $retorno['cod_pizzarias'] = $cod_pizzarias;

        echo json_encode($retorno);
    break;
    case "atualizar_total":
        $caixa = new ipi_caixa();
        $retorno = array();
        $retorno['total'] = $caixa->calcular_total();
        $retorno['cod_clientes'] = $_SESSION['ipi_caixa']['cliente']['cod_clientes'];
        $retorno['tipo_cliente'] = $_SESSION['ipi_caixa']['tipo_cliente'];
        
        echo json_encode($retorno);
    break;
    case "calcular_troco":

      $total_geral = validar_var_post("total_geral");
      $troco = validar_var_post("troco");

      $arr_resposta = array();
      if ( ($total_geral != "") && ($troco != "") )
      {
        $arr_resposta["ok"] = "S";
        $arr_resposta["troco"] = bd2moeda( moeda2bd($troco) - moeda2bd($total_geral) );
      }
      else
      {
        $arr_resposta["ok"] = "N";
      }
      
      echo json_encode($arr_resposta);
    break;          
    case "mensagem_direta":

        $numero_minutos = 5;
        $data_hora_inicial = strtotime("-$numero_minutos minute");
        $data_hora_final = strtotime("+$numero_minutos minute");
        $total_mensagens = count($_SESSION['ipi_caixa']['mensagens']);

        // se a pessoa tiver mais de uma pizzaria pegar uma unica mensagem
        
        $sql_msg = "SELECT DISTINCT(mensagem_pizzaria) mensagem FROM ipi_mensagem_pizzarias WHERE data_hora_exibicao > '".date("Y-m-d H:i:s", $data_hora_inicial)."' AND data_hora_exibicao < '".date("Y-m-d H:i:s", $data_hora_final)."'";
        if ($total_mensagens>0)
            $sql_msg .= " AND cod_mensagem_pizzarias NOT IN (".implode(",", $_SESSION['ipi_caixa']['mensagens']).")";
        
        if (count($_SESSION['usuario']['cod_pizzarias'])>0)
            $sql_msg .= " AND cod_pizzarias IN (".implode(",", $_SESSION['usuario']['cod_pizzarias']).")";
        $res_msg = mysql_query($sql_msg);
        $obj_msg = mysql_fetch_object($res_msg);
        


        $sqlBordas = "SELECT * FROM ipi_mensagem_pizzarias WHERE data_hora_exibicao > '".date("Y-m-d H:i:s", $data_hora_inicial)."' AND data_hora_exibicao < '".date("Y-m-d H:i:s", $data_hora_final)."'";
        if ($total_mensagens>0)
            $sqlBordas .= " AND cod_mensagem_pizzarias NOT IN (".implode(",", $_SESSION['ipi_caixa']['mensagens']).")";
        
        if (count($_SESSION['usuario']['cod_pizzarias'])>0)
            $sqlBordas .= " AND cod_pizzarias IN (".implode(",", $_SESSION['usuario']['cod_pizzarias']).")";
                        
        $resBordas = mysql_query($sqlBordas);
        $linBordas = mysql_num_rows($resBordas);
        $objBordas = mysql_fetch_object($resBordas);
        $retorno = array();
        if ($linBordas>0)
        {
            $retorno['status'] = "OK";
            $retorno['mensagem'] = utf8_encode(nl2br($objBordas->mensagem_pizzaria));
            $retorno['cod_mensagem_pizzaria'] = $objBordas->cod_mensagem_pizzarias;
            
            // carregar todos os código de pizzarias de um mesmo usuario, para não exibir mais de uma vez a mesma mensagem
            $sql_msg2 = "SELECT * FROM ipi_mensagem_pizzarias WHERE mensagem_pizzaria='".$obj_msg->mensagem."' AND data_hora_exibicao > '".date("Y-m-d H:i:s", $data_hora_inicial)."' AND data_hora_exibicao < '".date("Y-m-d H:i:s", $data_hora_final)."'";
            if ($total_mensagens>0)
                $sql_msg2 .= " AND cod_mensagem_pizzarias NOT IN (".implode(",", $_SESSION['ipi_caixa']['mensagens']).")";
            
            if (count($_SESSION['usuario']['cod_pizzarias'])>0)
                $sql_msg2 .= " AND cod_pizzarias IN (".implode(",", $_SESSION['usuario']['cod_pizzarias']).")";
            
            $res_msg2 = mysql_query($sql_msg2);
            while ($obj_msg2 = mysql_fetch_object($res_msg2))
            {
                $_SESSION['ipi_caixa']['mensagens'][$total_mensagens] = $obj_msg2->cod_mensagem_pizzarias;
                $total_mensagens++;
            }
            
        }
        else
        {
            $retorno['status'] = "NOK";
        }
        //$retorno['sql'] = $sqlBordas;
        //$retorno['sql2'] = $sql_msg;
        //$retorno['sql3'] = $sql_msg2;
        
        echo json_encode($retorno);
        break;
    
    case "baixar_telemarketing":
        
        $sql_aux = "INSERT INTO ipi_telemarketing_ativo_respostas (cod_clientes, cod_telemarketing_ativo, data_hora_telemarketing_resposta) VALUES(".$_SESSION['ipi_caixa']['cliente']['cod_clientes'].", ".$_POST['cod'].", '".date("Y-m-d H:i:s")."');";
        $res_aux = mysql_query($sql_aux);
        
        break;

    case 'completar_cep':
        $cep_completar = validaVarPost('cep');
        //file_put_contents($diretorio, "ERRO".mysql_errno($con_web) . ": " . mysql_error($con_web) . "\n",FILE_APPEND);
        //$datafile = fopen("http://www.internetsistemas.com.br/completa_cep_is.php?cep=".$cep_completar,"r");
        $datafile = fopen("http://sistema.formulapizzaria.com.br/formula/production/current/site/cep/production/completa_cep_is.php?cep=".$cep_completar,"r");
  	//$datafile = fopen("http://sistema.formulapizzaria.com.br/formula/production/current/site/cep/production/completa_cep_barathrum.php?cep=".$cepDest,"r");
        //$datafile = fopen("http://www.uol.com.br/","r");
        $data = fread($datafile, 100000000); 

        $arr_json = array();

        $arr_infos = explode("<br>",utf8_encode($data));
        //$arr_json["oqpego"] = "";
        foreach($arr_infos as $infoa)
        {
            $info = explode(": ",$infoa);
            switch($info[0])
            {
                case 'OK':
                    $arr_json["status"] = "OK";
                break;
                case "Endereco":
                    $arr_json["endereco"] = $info[1];
                break;
                case "Bairro":
                    $arr_json["bairro"] = $info[1];
                break;
                case "Cidade":
                    $arr_json["cidade"] = $info[1];
                break;
                case "UF":
                    $arr_json["estado"] = $info[1];
                break;
            }
        } 
        if($arr_json["status"]!="OK")
        {
            $arr_json["status"] = "ERRO";
        }    
        echo json_encode($arr_json);
    break;
        
        
}
//$hora_final = microtime(true);
//$diferenca_hora = $hora_final - $hora_inicial;
//echo "<script>console.log('ajax $acao - ".date("d/m/Y H:i:s", $hora_final)."')</script>"; 
//echo "<script>console.log('ajax $acao(".$_POST["cod"].") - ".($diferenca_hora)."')</script>";
//desconectabd($conexao);
?>
