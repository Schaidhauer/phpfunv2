<?php

Class Config{
	
	public $config_menu = array();
	public $config = array();
	
	public function __construct(){
		$this->loadConfig();
		$this->loadMenuConfig();
	}
	
	public function loadMenuConfig(){
		include("app/config/menu.php");
		$this->config_menu = $config_menu;
	}
	
	public function loadConfig(){
		include("app/config/config.php");
		$this->config = $config_g;
	}
	
}


?>