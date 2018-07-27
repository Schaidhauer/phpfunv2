<?php
spl_autoload_register( function($class) {
	$prefix = 'Twig';
	
	$base_dir = __DIR__ . '/Twig/';
	
	$len = strlen($prefix);
	
	if (strncmp($prefix,$class,$len) !== 0)
	{
		require $class.'.php';
		return;		
	}
	
	$relative_class = substr($class,$len);
	
	$file = $base_dir . str_replace('_','/',$relative_class).'.php';
	
	if (file_exists($file))
			require $file;
	
});


$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader, array(
	'cache'=>false
	));
	
	$menu = [
		['nome'=>'menu1'],
		['nome'=>'menu2'],
		['nome'=>'menu3']
		];
		
		
	
echo $twig->render('template.html',
	array(
	'title'=>'titulo pelo twig',
	'mAtual'=>'menu2',
	'menu'=>$menu
	));