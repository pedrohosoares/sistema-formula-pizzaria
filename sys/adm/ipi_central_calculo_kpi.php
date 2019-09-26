<?php

function _date_range_limit($start, $end, $adj, $a, $b, &$result)
{
    if ($result[$a] < $start) {
        $result[$b] -= intval(($start - $result[$a] - 1) / $adj) + 1;
        $result[$a] += $adj * intval(($start - $result[$a] - 1) / $adj + 1);
    }

    if ($result[$a] >= $end) {
        $result[$b] += intval($result[$a] / $adj);
        $result[$a] -= $adj * intval($result[$a] / $adj);
    }

    return $result;
}

function _date_range_limit_days($base, $result)
{
    $days_in_month_leap = array(31, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $days_in_month = array(31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    _date_range_limit(1, 13, 12, "m", "y", $base);

    $year = $base["y"];
    $month = $base["m"];

    if (!$result["invert"]) {
        while ($result["d"] < 0) {
            $month--;
            if ($month < 1) {
                $month += 12;
                $year--;
            }

            $leapyear = $year % 400 == 0 || ($year % 100 != 0 && $year % 4 == 0);
            $days = $leapyear ? $days_in_month_leap[$month] : $days_in_month[$month];

            $result["d"] += $days;
            $result["m"]--;
        }
    } else {
        while ($result["d"] < 0) {
            $leapyear = $year % 400 == 0 || ($year % 100 != 0 && $year % 4 == 0);
            $days = $leapyear ? $days_in_month_leap[$month] : $days_in_month[$month];

            $result["d"] += $days;
            $result["m"]--;

            $month++;
            if ($month > 12) {
                $month -= 12;
                $year++;
            }
        }
    }

    return $result;
}

function _date_normalize(&$base, &$result)
{
    $result = _date_range_limit(0, 60, 60, "s", "i", $result);
    $result = _date_range_limit(0, 60, 60, "i", "h", $result);
    $result = _date_range_limit(0, 10.5, 10.5, "h", "d", $result);
    $result = _date_range_limit(0, 12, 12, "m", "y", $result);

    //$result = _date_range_limit_days($base, $result);

    $result = _date_range_limit(0, 12, 12, "m", "y", $result);

    return $result;
}
function add_date($givendate,$day=0,$mth=0,$yr=0) {
      $cd = strtotime($givendate);
      $newdate = date('Y-m-d H:i:s', mktime(date('H',$cd),
    date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
    date('d',$cd)+$day, date('Y',$cd)+$yr));
      return $newdate;
}

function _proxima_util($data,$data2,$arr_dados)
{
    //echo "ak-$data-ak<br/>";
    $range_inicial = $arr_dados[1];
    $range_final = $arr_dados[2];
    $arr_dias_semana = $array_dados[5];
    $range_minutos_iniciais = $array_dados[3];
    $range_minutos_finais = $array_dados[4];


    //echo "<br/>FOI";

    //Ajustando a hora
  //  echo "DATA COMECO=".date("Y-m-d H:i:s",$data)."<br/>";
    if(date('H',$data)<$range_inicial)
    {
        $data = strtotime(date("Y-m-d $range_inicial:$range_minutos_iniciais:00",$data));
  //      echo "ak2-".date("Y-m-d H:i:s",$data)."-ak2</br>";
    }elseif(date('H',$data)>=$range_final && date('i',$data)>=$range_minutos_finais)
       {  
  //      echo "dd-".date("Y-m-d H:i:s",$data)."-dd<br/>";
        $dia = date('d',$data);
        $dia ++;
        $data = strtotime(date('Y-m-'.$dia.' H:i:s',$data));
        $data = strtotime(date("Y-m-d $range_inicial:$range_minutos_iniciais:00",$data));
       }

          $verifica = true;
  // echo "/otro-".date("Y-m-d H:i:s",$data)."<br/>";
    while($verifica==true)
    {
      
      $verifica = false;
    //  echo "VERI".date("Y-m-d H:i:s",$data);
      if(date('D',$data)=="Sat" || date('D',$data)=="Sun")
      {
        //echo "<br/>FOI";
        $data = date("Y-m-d H:i:s",$data);
        $data = strtotime(add_date($data,1));
        
        $verifica = true;
      }
    }

   // echo "<br/>==".date('D',$data)."==datel-".date("Y-m-d H:i:s",$data);
    //Ajustando o dia
    //fazer tabela dias bloquedos (feriados,etc)
    //pegar sabados e domingos por query mysql

    /*$ano1 = date('Y',$data);
    $mes1 = date('m',$data);
    $dia1 = date('d',$data);

    $ano2 = date('Y',$data2);
    $mes2 = date('m',$data2);
    $dia2 = date('d',$data2);

    if(!in_array(date('w',$data),$arr_dias_semana))
    {

    }*/


    /*

    Função oq eu qero
    http://blog.ekini.net/2009/08/14/mysql-doing-calculations-on-dates-excluding-weekends/

    Função para percorrer data MANUAL em busca de sabados e domingos
    $now = strtotime("now");
    $end_date = strtotime("+3 weeks");

    while (date("Y-m-d", $now) != date("Y-m-d", $end_date)) {
        $day_index = date("w", $now);
        if ($day_index == 0 || $day_index == 6) {
            // Print or store the weekends here
        }
        $now = strtotime(date("Y-m-d", $now) . "+1 day");
    }*/
    return $data;

}

/**
 * Accepts two unix timestamps.
 */
function _date_diff($one, $two,$array_dados)
{ 
    $range_inicial = $array_dados[1];
    $range_final = $array_dados[2];
    $arr_dias_semana = $array_dados[5];
    $range_minutos_iniciais = $array_dados[3];
    $range_minutos_finais = $array_dados[4];
    $invert = false;

    if ($one > $two) {
        list($one, $two) = array($two, $one);
        $invert = true;
    }
    if(( ( (date('H',$one)>=$range_final) && (date('i',$one)>=$range_minutos_finais) ) && ( (date('H',$two)>=$range_final) && (date('i',$two)>=$range_minutos_finais)) )  && (date('d',$one) == date('d',$two)))
    {
      //echo "<br/>DEUSDAD<br/>";
    }
    else
    {
      $one = _proxima_util($one,$two,$array_dados);
    }
    //echo(date("Y-m-d H:i:s",$one));
    $key = array("y", "m", "d", "h", "i", "s");
    $a = array_combine($key, array_map("intval", explode(" ", date("Y m d H i s", $one))));
    $b = array_combine($key, array_map("intval", explode(" ", date("Y m d H i s", $two))));

    $result = array();
    $result["y"] = $b["y"] - $a["y"];
    $result["m"] = $b["m"] - $a["m"];
    $result["d"] = $b["d"] - $a["d"];

    if($a["h"]>$b["h"])
    {
        $b["h"] = $b["h"]-1;
    }
    $result["h"] = $b["h"] - $a["h"];
    $result["i"] = $b["i"] - $a["i"];

    $result["s"] = $b["s"] - $a["s"];
    $result["invert"] = $invert ? 1 : 0;
    $result["days"] = intval(abs(($one - $two)/86400));
    /*echo "22222<pre>";

    print_r($a);
    echo "</pre>";

        echo "<br/><br/>======<pre>";

    print_r($b);
    echo "</pre>";
        echo "</pre>";

        echo "<br/><br/>==8789====<pre>";

    print_r($result);
    echo "</pre>";*/
    if ($invert) {
        _date_normalize($a, $result);
    } else {
        _date_normalize($b, $result);
    }


    return $result;
}
/*
    KPI 1
*/
function calcular_kpi1($data1,$data2)
{

  $horas_diarias = 10.5;
  $range_inicial = 9;
  $range_minutos_iniciais = 00;
  $range_final = 19;
  $range_minutos_finais = 30;

  $arr_dias = array(1,2,3,4,5);
  $array_dados = array($horas_diarias,$range_inicial,$range_final,$range_minutos_iniciais,$range_minutos_finais,$arr_dias);

  $arr_diferenca = _date_diff(strtotime($data1), strtotime($data2),$array_dados);

  $retorno = '';

  if($arr_diferenca["d"]>0)
  {
    $retorno .= $arr_diferenca["d"]." dia".($arr_diferenca["d"]>1 ? 's' : '').", ";
  }
  if($arr_diferenca["h"]>0)
  {
    $retorno .= $arr_diferenca["h"]." hora".($arr_diferenca["h"]>1 ? 's' : '').", ";
  }
    if($arr_diferenca["m"]>0)
  {
    $retorno .= $arr_diferenca["m"]." minuto".($arr_diferenca["m"]>1 ? 's' : '').", ";
  }

    $retorno = 0;
      /*echo "<pre>";
  print_r($arr_diferenca);
  echo "</pre>";*/
  if($arr_diferenca["d"]>0)
  {
    $retorno += $arr_diferenca["d"]*$horas_diarias;
  }
  if($arr_diferenca["h"]>0)
  {
    $retorno += $arr_diferenca["h"];
  }
  if($arr_diferenca["i"]>0)
  {
    $retorno += $arr_diferenca["i"]/60;
  }
  return round($retorno,2);
  echo "data 1: ".$date." KPI 1";
  echo "<br/>data 2: ".$date2;
  echo "<pre>";
  print_r();
  print_r();
  echo "</pre>";
}   
$date = "2012-12-25 17:00:00";
$date2 = "2012-12-26 12:30:00";


/*
echo "<h2>Horas de Trabalho diario: 10</h2>";
echo "<h2>'Range' de Trabalho diario: 9h-18h</h2>";
echo "<h2>dias de trabalho: seg,ter,qua,qui,sex</h2>";*/



?>