<?php

	class Logger
	{
		private $log_file = "gateway_cielo.log";


		private $fp = null;
		
		public function logOpen()
		{
      if ( strstr(getcwd(), "/sys/") != false )
      {
			  $this->fp = fopen(getcwd()."/../../log/cielo/".$this->log_file, 'a');
      }
      else
      {
			  $this->fp = fopen(getcwd()."/log/cielo/".$this->log_file, 'a');
      }
		}
		 
		public function logWrite($strMessage, $transacao)
		{
			if(!$this->fp)
				$this->logOpen();
			
			$path = $_SERVER["REQUEST_URI"];
			$data = date("Y-m-d H:i:s:u (T)");
			
			$log = "***********************************************" . "\n";
			$log .= $data . "\n";
			$log .= "DO ARQUIVO: " . $path . "\n"; 
			$log .= "OPERA��O: " . $transacao . "\n";
			$log .= $strMessage . "\n\n"; 

			fwrite($this->fp, $log);
		}
	}
?>
