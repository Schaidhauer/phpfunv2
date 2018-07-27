<?php
	
	$newIcon = '<span style="color:#fff; font-size:10px; padding:3px; background-color:red; float:right">NEW</span>';
	

	$menu = array(
		array('label'=>'Menu 1','icon'=>'fa-dashboard','link'=>'1'),
		
		array('label'=>'Menu 2 '.@$newIcon,'icon'=>'fa-graduation-cap','link'=>'home'),
		
		array('label'=>'Menu 3','icon'=>'fa-link','link'=>'3'),
		
		
	);
	
	return $menu;
	
?>