<?php

/**
 * Tela de consulta e alteração de dados de clientes.
 *
 * @version 1.0
 * @package osmuzzarellas
 * 
 * LISTA DE MODIFICAÇÕES:
 *
 * VERSÃO    DATA         AUTOR            DESCRIÇÃO 
 * ======    ==========   ==============   =============================================================
 *
 * 1.0       23/08/2013   FilipeGranato    Criado.
 *
 */

require_once '../../bd.php';
require_once '../lib/php/formulario.php';
require_once '../lib/php/sessao.php';
$param = explode(',', validaVarGet('param'));
$acao = $param[0];
switch ($acao) {
	case '1':
		$cont_net = $param[1];
		$cont_tel = $param[2];
		?>
		<chart>
	  <set label='NET' value='<? echo $cont_net ?>'/>
		<set label='TEL' value='<? echo $cont_tel ?>'/>
		</chart>
	  <?
		break;
		case '2':
		$cont_f = $param[1];
		$cont_m = $param[2];
		?>
		<chart>
	  <set label='Feminino' value='<? echo $cont_f ?>'/>
		<set label='Masculino' value='<? echo $cont_m ?>'/>
		</chart>
	  <?
		break;		
		case '3':

		$cont_menos18 = $param[1];
		$cont_i20a25 = $param[2];
		$cont_i26a30 = $param[3];
		$cont_i31a40 = $param[4];
		$cont_i41a55 = $param[5];
		$cont_i56mais = $param[6];
		?>
		<chart>
	  <set label='Menores de 18 anos' value='<? echo $cont_menos18 ?>'/>
		<set label='de 18 a 25 anos' value='<? echo $cont_i20a25 ?>'/>
	  <set label='de 26 a 30 anos' value='<? echo $cont_i26a30 ?>'/>
		<set label='de 31 a 40 anos' value='<? echo $cont_i31a40 ?>'/>
	  <set label='de 41 a 55 anos' value='<? echo $cont_i41a55 ?>'/>
		<set label='Maiores de 56 anos' value='<? echo $cont_i56mais ?>'/>
		</chart>
	  <?
		break;		
		case '4':
			$meses = array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
			$mes_atual = (date('m')-1);
			$ano_atual = date('Y');
			$campos = array();
			$datas = array();
			for($i = 0;$i<=12;$i++)
			{
				$mes_usar = $mes_atual - ($i+1);
				$ano_usar = $ano_atual;
				if($mes_usar<0)
				{
					$ano_usar = (date('Y')-1);
					$mes_usar = (12+$mes_usar);
				}
				$campos[] = $meses[$mes_usar].'/'.$ano_usar;
				$mes_usar = sprintf('%1$02d',($mes_usar+1));
				$datas[] = $ano_usar.'-'.($mes_usar+1);
			}
			$valor1 = $param[1] ;
			$valor2 = $param[2] ;
			$valor3 = $param[3] ;
			$valor4 = $param[4] ;
			$valor5 = $param[5] ;
			$valor6 = $param[6] ;
			$valor7 = $param[7] ;
			$valor8 = $param[8] ;
			$valor9 = $param[9] ;
			$valor10 = $param[10];
			$valor11 = $param[11];
			$valor12 = $param[12];

			$valorn1 = $param[12] ;
			$valorn2 = $param[13] ;
			$valorn3 = $param[14] ;
			$valorn4 = $param[15] ;
			$valorn5 = $param[16] ;
			$valorn6 = $param[17] ;
			$valorn7 = $param[18] ;
			$valorn8 = $param[19] ;
			$valorn9 = $param[20];
			$valorn10 = $param[21];
			$valorn11 = $param[22];	
			$valorn12 = $param[23];

			/*		<chart palette='3' caption='Crescimentos dos clientes' subcaption='Nos ultimos 12 meses' xAxisName='Mes/Ano' yAxisMinValue='0' yAxisName='Cadastros' numberPrefix='' showValues='0' autoScale='1' >
		        <set label='<? echo $campo1; ?>'  value ='<? echo $valor1 ?>' />
	        	<set label='<? echo $campo2; ?>'  value ='<? echo $valor2 ?>' />	
		        <set label='<? echo $campo3; ?>'  value ='<? echo $valor3 ?>' />
	        	<set label='<? echo $campo4; ?>'  value ='<? echo $valor4 ?>' />
		        <set label='<? echo $campo5; ?>'  value ='<? echo $valor5 ?>' />
	        	<set label='<? echo $campo6; ?>'  value ='<? echo $valor6 ?>' />
		        <set label='<? echo $campo7; ?>'  value ='<? echo $valor7 ?>'  />
	        	<set label='<? echo $campo8; ?>'  value ='<? echo $valor8 ?>' />
		        <set label='<? echo $campo9; ?>'  value ='<? echo $valor9 ?>' />
	        	<set label='<? echo $campo10; ?>' value ='<? echo $valor10 ?>'  />
		        <set label='<? echo $campo11; ?>' value ='<? echo $valor11 ?>'  />
	        	<set label='<? echo $campo12; ?>' value ='<? echo $valor12 ?>'  />
		</chart>*/
		?>
		<chart palette='4' caption="Crescimento de clientes TEL x NET" numdivlines="4" lineThickness="2" showValues="0" formatNumberScale="1" decimals="1" anchorRadius="2" numberPrefix=""  yAxisMinValue="0" shadowAlpha="50">
		<categories>
      <category label='<? echo $campos[11]; ?>'  />
    	<category label='<? echo $campos[10]; ?>'  />	
      <category label='<? echo $campos[9]; ?>'  />
    	<category label='<? echo $campos[8]; ?>'  />
      <category label='<? echo $campos[7]; ?>'  />
    	<category label='<? echo $campos[6]; ?>'  />
      <category label='<? echo $campos[5]; ?>'   />
    	<category label='<? echo $campos[4]; ?>'  />
      <category label='<? echo $campos[3]; ?>'  />
    	<category label='<? echo $campos[2]; ?>'   />
      <category label='<? echo $campos[1]; ?>'   />
    	<category label='<? echo $campos[0]; ?>'   />
		  </categories>
		<dataset seriesName="Clientes Tel" color="A66EDD" anchorBorderColor="A66EDD" anchorRadius="4">
      <set value ='<? echo $valor1 ?>' />
    	<set value ='<? echo $valor2 ?>' />	
      <set value ='<? echo $valor3 ?>' />
    	<set value ='<? echo $valor4 ?>' />
      <set value ='<? echo $valor5 ?>' />
    	<set value ='<? echo $valor6 ?>' />
      <set value ='<? echo $valor7 ?>'  />
    	<set value ='<? echo $valor8 ?>' />
      <set value ='<? echo $valor9 ?>' />
    	<set value ='<? echo $valor10 ?>'  />
      <set value ='<? echo $valor11 ?>'  />
    	<set value ='<? echo $valor12 ?>'  />
		  </dataset>
		<dataset seriesName="Clientes NET" color="F6BD0F" anchorBorderColor="F6BD0F" anchorRadius="4">
      <set value ='<? echo $valorn1 ?>' />
    	<set value ='<? echo $valorn2 ?>' />	
      <set value ='<? echo $valorn3 ?>' />
    	<set value ='<? echo $valorn4 ?>' />
      <set value ='<? echo $valorn5 ?>' />
    	<set value ='<? echo $valorn6 ?>' />
      <set value ='<? echo $valorn7 ?>'  />
    	<set value ='<? echo $valorn8 ?>' />
      <set value ='<? echo $valorn9 ?>' />
    	<set value ='<? echo $valorn10 ?>'  />
      <set value ='<? echo $valorn11 ?>'  />
    	<set value ='<? echo $valorn12 ?>'  />
		  </dataset>
		<styles>
			<definition>
				<style name='MyXScaleAnim' type='ANIMATION' duration='0.7' start='0' param="_xScale" />
		<style name='MyYScaleAnim' type='ANIMATION' duration='0.7' start='0' param="_yscale" />
		<style name='MyAlphaAnim' type='ANIMATION' duration='0.7' start='0' param="_alpha" />
			</definition>
		<application>
		        <apply toObject='DIVLINES' styles='MyXScaleAnim,MyAlphaAnim' />
		        <apply toObject='HGRID' styles='MyYScaleAnim,MyAlphaAnim' />
			</application>
		</styles>
		</chart>
	  <?
		break;
		case '5':

		?>
				<chart caption="Bairros mais cadastrados" palette="2" animation="1" formatNumberScale="0" numberPrefix="" labeldisplay="ROTATE" slantLabels="1" seriesNameInToolTip="0" sNumberSuffix="" showValues="0" plotSpacePercent="0" labelDisplay="STAGGER">
		    <set label="<? echo $param[1]; ?>" value="<? echo $param[2]; ?>" />
		    <set label="<? echo $param[3]; ?>" value="<? echo $param[4]; ?>" />
		    <set label="<? echo $param[5]; ?>" value="<? echo $param[6]; ?>" />
		    <set label="<? echo $param[7]; ?>" value="<? echo $param[8]; ?>" />
		    <set label="<? echo $param[9]; ?>" value="<? echo $param[10]; ?>" />
		    <set label="<? echo $param[11]; ?>" value="<? echo $param[12]; ?>" />
		    <set label="<? echo $param[13]; ?>" value="<? echo $param[14]; ?>" />
		    <set label="<? echo $param[15]; ?>" value="<? echo $param[16]; ?>" />
		    <set label="<? echo $param[17]; ?>" value="<? echo $param[18]; ?>" />
		    <set label="<? echo $param[19]; ?>" value="<? echo $param[20]; ?>" />
		    <styles>
		        <definition>
		<style type="font" name="CaptionFont" size="15" color="666666" />
		            <style type="font" name="SubCaptionFont" bold="0" />
		        </definition>
		<application>
		            <apply toObject="caption" styles="CaptionFont" />
		            <apply toObject="SubCaption" styles="SubCaptionFont" />
		        </application>
		    </styles>
		</chart>


		<?
		break;
				case '6':

		?>
				<chart caption="Bairros com mais pedidos" palette="2" animation="1" formatNumberScale="0" numberPrefix="" labeldisplay="ROTATE" slantLabels="1" seriesNameInToolTip="0" sNumberSuffix="" showValues="0" plotSpacePercent="0" labelDisplay="STAGGER">
		    <set label="<? echo $param[1]; ?>" value="<? echo $param[2]; ?>" />
		    <set label="<? echo $param[3]; ?>" value="<? echo $param[4]; ?>" />
		    <set label="<? echo $param[5]; ?>" value="<? echo $param[6]; ?>" />
		    <set label="<? echo $param[7]; ?>" value="<? echo $param[8]; ?>" />
		    <set label="<? echo $param[9]; ?>" value="<? echo $param[10]; ?>" />
		    <set label="<? echo $param[11]; ?>" value="<? echo $param[12]; ?>" />
		    <set label="<? echo $param[13]; ?>" value="<? echo $param[14]; ?>" />
		    <set label="<? echo $param[15]; ?>" value="<? echo $param[16]; ?>" />
		    <set label="<? echo $param[17]; ?>" value="<? echo $param[18]; ?>" />
		    <set label="<? echo $param[19]; ?>" value="<? echo $param[20]; ?>" />
		    <styles>
		        <definition>
		<style type="font" name="CaptionFont" size="15" color="666666" />
		            <style type="font" name="SubCaptionFont" bold="0" />
		        </definition>
		<application>
		            <apply toObject="caption" styles="CaptionFont" />
		            <apply toObject="SubCaption" styles="SubCaptionFont" />
		        </application>
		    </styles>
		</chart>


		<?
		break;
		case '7':

		?>
				<chart caption="Bairros com mais faturamento" palette="2" animation="1" formatNumberScale="0" numberPrefix="" labeldisplay="ROTATE" slantLabels="1" seriesNameInToolTip="0" sNumberSuffix="" showValues="0" plotSpacePercent="0" labelDisplay="STAGGER">
		    <set label="<? echo $param[1]; ?>" value="<? echo $param[2]; ?>" />
		    <set label="<? echo $param[3]; ?>" value="<? echo $param[4]; ?>" />
		    <set label="<? echo $param[5]; ?>" value="<? echo $param[6]; ?>" />
		    <set label="<? echo $param[7]; ?>" value="<? echo $param[8]; ?>" />
		    <set label="<? echo $param[9]; ?>" value="<? echo $param[10]; ?>" />
		    <set label="<? echo $param[11]; ?>" value="<? echo $param[12]; ?>" />
		    <set label="<? echo $param[13]; ?>" value="<? echo $param[14]; ?>" />
		    <set label="<? echo $param[15]; ?>" value="<? echo $param[16]; ?>" />
		    <set label="<? echo $param[17]; ?>" value="<? echo $param[18]; ?>" />
		    <set label="<? echo $param[19]; ?>" value="<? echo $param[20]; ?>" />
		    <styles>
		        <definition>
		<style type="font" name="CaptionFont" size="15" color="666666" />
		            <style type="font" name="SubCaptionFont" bold="0" />
		        </definition>
		<application>
		            <apply toObject="caption" styles="CaptionFont" />
		            <apply toObject="SubCaption" styles="SubCaptionFont" />
		        </application>
		    </styles>
		</chart>


		<?
		break;
		case '8': // GRAFICO ONDE CONHECEU

		echo "<chart>";
		for($a = 2; $a<= ($param[1]*2)+2;$a+=2)
		{

			echo "<set label='".$param[$a]."' value='".$param[$a+1]."'/>";
		
		}
		echo "</chart>";
		?>



		<?
		break;
}
?>
