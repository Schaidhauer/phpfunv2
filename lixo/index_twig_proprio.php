<?php

	$template = file_get_contents('template.html');
	
	//preg_match_all('({[\.\w-\s\p{L}% =]+})',$template,$matches);
	
	//pega somente as tags LOGICAS {%%}
	//preg_match_all('({%[\.\w-\s\p{L}% =]+%})',$template,$matches);
	//$tags_logicas = array_unique($matches[0]);
	
	//obtem os FOR end ENDFOR
	preg_match_all('({%for\X+endfor%})',$template,$matches);
	$tags_for = array_unique($matches[0]);
	
	
	foreach()
	
	$template_infos = [
		'title' => 'Titulo aba',
		'mAtual' => 'menu2',
		'menu' => [
			['nome'=>'menu1','link'=>'link1'],
			['nome'=>'menu2','link'=>'link3'],
			['nome'=>'menu3','link'=>'link3'],
		],
		
	];

	foreach($template_infos as $t => $v)
	{
			echo "<p>alterando {".$t."} por '".$v."'";
		$template = str_replace("{".$t."}", $v, $template);
	}
	
echo $template;