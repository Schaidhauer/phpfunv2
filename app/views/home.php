<?php
Class Home{

	public $my;
	
	public function __construct()
	{
		//Core::$session->validaSessao();
	}
	
	function index()
	{
		$content = '';
		
		//Core::$html->head();
		//Core::$html->bodyBegin();
		
		$content .= "<h1>Title</h1>";
		$content .= "<p>p1</p><br/>";
		$content .= "<p>p2</p>";
		$content .= "<p>p3</p>";
		
		
		Core::$html->render($content,'index2.html');
		
		//Core::$html->bodyEnd(@$jquery);
	}

	
}

?>