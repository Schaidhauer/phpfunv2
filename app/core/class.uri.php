<?php

Class Uri{
		
	public static function getURI()
	{
		$requestURI = explode('/', $_SERVER['REQUEST_URI']);
		$scriptName = explode('/',$_SERVER['SCRIPT_NAME']);
		
		
		//para remover a pasta do projeto
		for($i= 0;$i < sizeof($scriptName);$i++)
		{
			if ($requestURI[$i] == $scriptName[$i])
			{
				unset($requestURI[$i]);
			}
		}
		//print_r(array_values($requestURI));
		//return array_values(array_filter($requestURI));
		return array_values($requestURI);
		
		
	}
	
	
}

?>