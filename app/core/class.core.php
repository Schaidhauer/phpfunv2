<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("help.php");
require_once("class.uri.php");
require_once("class.html.php");


//require_once("class.bd.php");
//require_once("class.conexao.php");
//require_once("class.config.php");
//require_once("class.session2.php");


Class Core{
	
	//variaveis para os objetos do sistema
	public $bd;
	public $config;
	
	//chamar nas outras como core::$path; e core::$session
	public static $path;
	public static $session;
	public static $html;
	
	public function __construct()
	{
		
		
		//echo ">>".__DIR__."<<";
		$this->config  = require(__DIR__."\..\mods\config.php");
		
		$this->setPath();	
		$this->cmd     = Uri::getUri();
		self::$html    = new HTML();		
		
		//self::$session = new Sessao();
		self::$html->setAtual($this->cmd[0],@$this->cmd[1]);
			
	}
	
	public function setPath()
	{
		self::$path = $this->config['protocolo']."://".$_SERVER['HTTP_HOST'].$this->config['systemFolder'];
	}
	
	public function conectaBD()
	{
		$this->bd = new Mysqlidb (
			$this->config['dbHost'], 
			$this->config['dbUser'], 
			$this->config['dbPass'], 
			$this->config['dbDatabase'] 
		);
	}
	
	public function checkPermissions()
	{
		//$permissoes_deste_usuario = self::$session->permissoes;
		
		//bypass nas rotinas de executar
		//se tem alguma permissao dai sim vai validar, se vier em branco, nao tem problema meu amigo
		if (sizeof(@$permissoes_deste_usuario))
		{
			//print_r($permissoes_deste_usuario);
			if (in_array('*', $permissoes_deste_usuario))
				return true;
				
			if (in_array($this->cmd[0], $permissoes_deste_usuario))
				return true;
			else
				return false;
		}
		else
			return true;
		
	}
	
	public function redir()
	{
		
		$this->splitMethodsAndArgs();
		$file    = "app/views/".$this->cmd[0].".php";
		$control = "app/controls/".$this->cmd[0].".control.php";
		
		

		
		$canRedir = true;
		
		if ($this->cmd[0] != 'login')
		{
			//se as permissoes por classes estiverem ativas
			if (!$this->config['allowAllClasses'])
				$canRedir = $this->checkPermissions();
		}
		
		if ((file_exists($file) == true) && ($canRedir))
		{
			require_once($file);
			
			if (file_exists($control) == true)
				require_once($control);
			
			
			$page = new $this->cmd[0];
			
			//$http_argumentos = sizeof($this->cmd);
			
			//ver se o metodo nao ta em branco
			if (($this->http_method == "") || ($this->http_method == " "))
			{
				//metodo padrao
				call_user_func(array($page, "index"));
			}
			else
			{
				//metodo não é em branco
				
				//validar se o metodo que quer chamar, ao menos é chamável (com ou sem __call)
				if (is_callable(array($page, $this->http_method)))
				{
					//agora verificamos se o metodo existe mesmo (ignorando o call, este vai ser o papel deste if para nao precisar definir __call em todas as views - que é um saco)
					if (method_exists($page, $this->http_method))
					{
						if ($this->http_argumentos == 2)
						{
							call_user_func(array($page, $this->http_method));
						}
						else if ($this->http_argumentos > 2)
						{
							call_user_func_array(array($page, $this->http_method), $this->http_args); 
						}
					}
					
				}
				else
				{
					//pode ser que o método não exista
					//mas vamos ver se não é um segundo parametro, tratar isso no metodo VIEW
					//validar antes o http_args, pois pode ser um ? (do GET)
					if (is_array(@$this->http_args))
						call_user_func_array(array($page, 'view'), array_merge(array($this->http_method),$this->http_args)); 
					else
					{
						//é sinal que é um meotodo que nao existe, e provavelmente um '?', entao tentar enviar pro padrao
						call_user_func(array($page, "index"));
					}
				}
			}
			
		}
		else
		{
			//se nao for enviado nada, vai pro metodo padrao
			if ($this->cmd[0] == '')
			{
				//echo "metodo padrao";
				//Aqui tem que dar um redir para o metodo padrao
				header("Location: ".self::$path."/".$this->config['defaultClass']."/");
				//header("Location: ".__DIR__."://".$_SERVER['HTTP_HOST'].$this->config['systemFolder']."/".$this->config['defaultClass']."/");
				exit();
			}
			else
			{
				//Se for algo que nao existe, pagina de erro
				/*
				self::$html = new HTML($this->config->config_menu,$this->config->config_html,self::$path);
					
				self::$html->head();
				self::$html->bodyBeginBlank();
				self::$html->mensagemErro();
				self::$html->bodyEndBlank();
				*/
				echo "Erro";				
				
				
			}
			
			
		}
	
	}
	
	public function splitMethodsAndArgs()
	{
		$this->http_argumentos = sizeof($this->cmd);
		
		if ($this->http_argumentos == 1)
			$this->http_method = " ";
		else if ($this->http_argumentos == 2)
		{
			$this->http_method = $this->cmd[1];
		}
		else if ($this->http_argumentos > 2)
		{
			for($i=2;$i<$this->http_argumentos;$i++)
			{
				$args[] = $this->cmd[$i];
			}
			$this->http_method = $this->cmd[1];
			$this->http_args   = $args;
		}
	}
	
	public function getURL()
	{
		$requestURI = explode('/', $_SERVER['REQUEST_URI']);
		$scriptName = explode('/',$_SERVER['SCRIPT_NAME']);
		
		

		for($i= 0;$i < sizeof($scriptName);$i++)
		{
			if ($requestURI[$i] == $scriptName[$i])
			{
				unset($requestURI[$i]);
			}
		}

		$this->cmd = array_values($requestURI);
		
		//print_r($this->cmd);
		//die();
	}
	
	public function getCommand()
	{
		return $this->cmd;
	}
	
	
}

?>