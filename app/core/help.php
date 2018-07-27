<?php


	function startTimer()
	{
		$this->time_start = microtime(true); 
	}
	
	function stopTimer()
	{
		$this->time_end = microtime(true);

		//echo $this->time_end." - ".$this->time_start;
		//dividing with 60 will give the execution time in minutes otherwise seconds
		$total_secs = ((floor($this->time_end) - floor($this->time_start)));
		
		if ($total_secs > 100)
			$this->execution_time = ($total_secs/60)." mins";
		else
			$this->execution_time = $total_secs." secs";
	}
	
	function showTimer()
	{
		echo '<b>Tempo total de execução:</b> '.$this->execution_time;
	}
	
	function getTimerTotal()
	{
		return $this->execution_time;
	}
	
	function getTimeToMicroseconds()
	{
		$t = microtime(true);
		$micro = sprintf("%06d", ($t - floor($t)) * 1000000);
		$d = new DateTime(date('Y-m-d H:i:s.' . $micro, $t));

		return $d->format("Y-m-d H:i:s.u"); 
	}
	
	function remover_caracter($string) {
		$string = preg_replace("/[áàâãä]/", "a", $string);
		$string = preg_replace("/[ÁÀÂÃÄ]/", "A", $string);
		$string = preg_replace("/[éèê]/", "e", $string);
		$string = preg_replace("/[ÉÈÊ]/", "E", $string);
		$string = preg_replace("/[íì]/", "i", $string);
		$string = preg_replace("/[ÍÌ]/", "I", $string);
		$string = preg_replace("/[óòôõö]/", "o", $string);
		$string = preg_replace("/[ÓÒÔÕÖ]/", "O", $string);
		$string = preg_replace("/[úùü]/", "u", $string);
		$string = preg_replace("/[ÚÙÜ]/", "U", $string);
		$string = preg_replace("/ç/", "c", $string);
		$string = preg_replace("/Ç/", "C", $string);
		$string = preg_replace("/[][><}{)(:;,!?*%~^`&#@]/", "", $string);
		$string = preg_replace("/ /", "_", $string);
		return $string;
	}
	
	function ms_escape_string($data)
	{
		if ( !isset($data) or empty($data) ) return '';
		if ( is_numeric($data) ) return $data;

		$non_displayables = array(
			'/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
			'/%1[0-9a-f]/',             // url encoded 16-31
			'/[\x00-\x08]/',            // 00-08
			'/\x0b/',                   // 11
			'/\x0c/',                   // 12
			'/[\x0e-\x1f]/'             // 14-31
		);
		foreach ( $non_displayables as $regex )
			$data = preg_replace( $regex, '', $data );
		$data = str_replace("'", "''", $data );
		return $data;
	}
	
	function escreverCodigosComBR($string){
		return str_replace("\n",'<br />',htmlentities(str_replace('<br />',"\n",$string)));
	}
	
	function fixDate($format,$dt)
	{
		/*
		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$dt))
			$this->dtENtoBR($dt);
		else
			$this->dtBRtoEN($dt);
		*/
		
		if ($format == 'yyyy-mm-dd')
		{
			return $this->dtBRtoEN($dt);
		}
		else if ($format == 'dd/mm/yyyy')
		{
			return $this->datetimeENtoBR($dt,true);
		}
		else
			return "erro dt format";
	}
	
	function dtBRtoEN($data){
        $data1 = explode("/",$data);
        $data = $data1[2]."-".$data1[1]."-".$data1[0];
        return $data;
    }
    
    function dtENtoBR($data){
        $data1 = explode("-",$data);
        $data = $data1[2]."/".$data1[1]."/".$data1[0];
        return $data;
    }

    function datetimeBRtoEN($data,$noHour=false){
        $datas = explode(" ",$data);
        
        $data1 = explode("/",$datas[0]);
        $hora = explode(":",@$datas[1]);
		
		if (@$datas[1] == '')
		{
			$hora[0] = "00";
			$hora[1] = "00";
			$hora[2] = "00";
		}
		
		if ($noHour)
			$data = $data1[2]."-".$data1[1]."-".$data1[0];
		else
			$data = $data1[2]."-".$data1[1]."-".$data1[0]." ".@$hora[0].":".@$hora[1].":".@$hora[2];
        return $data;
    }

    function datetimeENtoBR($data,$noHour=false){
        $datas = explode(" ",$data);
        
        $data1 = explode("-",$datas[0]);
        $hora = explode(":",@$datas[1]);
		
		if (@$datas[1] == '')
		{
			$hora[0] = "00";
			$hora[1] = "00";
			$hora[2] = "00";
		}
		
		if ($noHour)
			$data = $data1[2]."/".$data1[1]."/".$data1[0];
		else
			$data = $data1[2]."/".$data1[1]."/".$data1[0]." ".@$hora[0].":".@$hora[1].":".@$hora[2];
        return $data;
    }
    
    function arredondarParaCima($n,$x=5,$limite = 0) {
        $x = round(($n+$x/2)/$x)*$x;

        if ($x > $limite)
            return $limite;
        else
            return $x;
        
        
    }
    
    function cortar($text, $len) {
        if (strlen($text) < $len) {
            return $text;
        }
        $text_words = explode(' ', $text);
        $out = null;


        foreach ($text_words as $word) {
            if ((strlen($word) > $len) && $out == null) {

                return substr($word, 0, $len) . "...";
            }
            if ((strlen($out) + strlen($word)) > $len) {
                return $out . "...";
            }
            $out.=" " . $word;
        }
        return $out;
    }
    
    function defPosicao($pos){
		switch($pos){
				case 1:
					$x = "GOL";
					break;
				case 2:
					$x = "DEF";
					break;
				case 3:
					$x = "MEI";
					break;
				case 4:
					$x = "ATA";
					break;
		}
		return $x;
    }

    function dateafter($a){
        $hours = $a * 24;
        $added = ($hours * 3600)+time();
        //$days = date("l", $added);
        $month = date("m", $added);
        $day = date("d", $added);
        $year = date("Y", $added);
        $result = "$year-$month-$day";
        return ($result);
    }
	
	function comparaDatas($d1,$d2){
		$data_desafio 	= strtotime($d1); // converte para timestamp Unix
		$data_atual  	= strtotime($d2); // converte para timestamp Unix

		// data atual é maior que a data de expiração
		if ($data_desafio > $data_atual) // true
		  return true;
		else // false
		  return false;
	}
	
	function isValidXml($content)
	{
		if ($content != "")
		{
			$content = trim($content);
			if (empty($content)) {
				return false;
			}
			//html go to hell!
			if (stripos($content, '<!DOCTYPE html>') !== false) {
				return false;
			}
			//echo "START: ".$content[0];

			libxml_use_internal_errors(true);
			simplexml_load_string($content);
			$errors = libxml_get_errors();          
			libxml_clear_errors();  
			//print_r($errors);
			return empty($errors);
		}
		else{
			return false;
		}
	}
	
	function daysBetween($dt,$dt2='')
	{
		if ($dt2 == '')
			$now = time();
		else
			$now = strtotime($this->dtBRtoEN($dt2)." 00:00:00");
			
		$your_date = strtotime($this->dtBRtoEN($dt)." 00:00:00");
		$datediff = $your_date - $now;

		$datediff = ceil($datediff / (60 * 60 * 24));
		return $datediff;
	}
	
	function horasAgo($time1)
	{
		$time2 = strtotime(date("m/d/Y H:i:s"));
		
		$x = $time2 - $time1;
		$horas = ($x/60)/60;
		return round($horas);
	}	
	
	function time_ago($date)
	{
		if (empty($date))
		{
			return "No date provided";
		}
		$periods = array("segundo", "minuto", "hora", "dia", "semana", "mes", "ano", "decada");
		$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
		$now = time();
		$unix_date = strtotime($date);
		// check validity of date
		if (empty($unix_date))
		{
			return "Bad date";
		}
		// is it future date or past date
		if ($now > $unix_date)
		{
			$difference = $now - $unix_date;
			$tense = "atrás";
		} else {
			$difference = $unix_date - $now;
			$tense = "agora";
		}
		for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++)
		{
			$difference /= $lengths[$j];
		}
		$difference = round($difference);
		if ($difference != 1) {
			if ($periods[$j] == 'mes')
				$periods[$j].= "es";
			else
				$periods[$j].= "s";
		}
		return "$difference $periods[$j] {$tense}";
	}
	
	function diaSemana($d)
	{
		switch($d){
			case '1':
				$x = 'Segunda';
				break;
			case '2':
				$x = 'Terça';
				break;
			case '3':
				$x = 'Quarta';
				break;
			case '4':
				$x = 'Quinta';
				break;
			case '5':
				$x = 'Sexta';
				break;
			case '6':
				$x = 'Sábado';
				break;
			case '7':
				$x = 'Domingo';
				break;

		}
		return $x;
	}
	
	//procura um valor em um array multidimencional
	function in_array_r($needle, $haystack, $strict = false)
	{
		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
				return true;
			}
		}

		return false;
	}
	
	function convertToReadableSize($size)
	{
		$base = log($size) / log(1024);
		$suffix = array("", " KB", " MB", " GB", " TB");
		$f_base = floor($base);
		return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
	}
	
	function like($needle, $haystack)
	{
		$regex = '/' . str_replace('%', '.*?', $needle) . '/';

		return preg_match($regex, $haystack) > 0;
	}

?>