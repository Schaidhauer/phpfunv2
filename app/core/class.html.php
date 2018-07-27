<?php

Class Html{

	public $title;
	public $favicon;
	public $menu;
	public $login;
	public $atual;
	
	public $customCSS = "";
	public $customJS = "";
	
	public $path;
	public $session;

	public function __construct()
	{
		$this->path               = Core::$path;
		
		$this->menu            = require(__DIR__."/../mods/menu.php");
		$this->config          = require(__DIR__."/../mods/config.php");
		
		$this->title           = $this->config['sysTitulo'];
		$this->sysDescricao    = $this->config['sysDescricao'];
		$this->sysAutor        = $this->config['sysAutor'];
		$this->favicon         = $this->config['favicon'];
		$this->clock           = $this->config['clock'];
		$this->menuLateral     = $this->config['menuLateral'];
		
		$this->mostraMenuEsquerda = true;
		
		
		//$this->session = new Sessao();
	}
	
	function render($content,$fileHtml='index.html',$template='')
	{
		$this->startTwig($fileHtml,$template);
		//echo $this->atual;
		echo $this->twig->render($fileHtml,
			array(
			'title'=>$this->title,
			'mAtual'=>$this->atual,
			'path'=>$this->path,
			'menu'=>$this->menu,
			'content'=>$content,
			));
		
	}
	
	function startTwig($fileHtml='index.html',$template='')
	{
		if ($template == '')
			$template = $this->config['defaultTemplate'];
		
		
		spl_autoload_register( function($class) {
			$prefix = 'Twig';
			
			$base_dir = __DIR__ . '/ootb/Twig/';
			
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


		$loader = new Twig_Loader_Filesystem(__DIR__.'/../mods/templates/'.$template.'/');
		$this->twig = new Twig_Environment($loader, array(
			'cache'=>false
			));
	}
	
	public function setLogin($login)
	{
		$this->login = $login;
	}
	
	public function setMenuEsquerda($show)
	{
		$this->mostraMenuEsquerda = $show;
	}
	
	public function changeTitle($title)
	{
		$this->title = $title;
	}
	
	public function addCSS($cssfile)
	{
		$this->customCSS .= '<link href="'.$this->path."/".$cssfile.'" rel="stylesheet">';
	}
	
	public function addJS($jsfile)
	{
		$this->customJS .= '<script src="'.$this->path."/".$jsfile.'"></script>';
	}
	
	public function head()
	{
		$head = file_get_contents('app/template/head.html');
		
		
		$template_infos = [
			'path' => $this->path,
			'title' => $this->title,
			'sysDescricao' => $this->sysDescricao,
			'sysAutor' => $this->sysAutor,
			'favicon' => $this->favicon,
			'customCSS' => $this->customCSS,
			
		];

		foreach($template_infos as $t => $v)
		{
			$head = str_replace("{{".$t."}}", $v, $head);
		}
			
		echo $head;
		
	}
	
	public function setTabAlertContent($c)
	{
		$this->tabAlertContent = $c;
	}
	
	public function menuSuperior()
	{
		$m = "";
		$m .="
		<div class='navbar-header' style='width: 251px; border-right:1px solid #e7e7e7;'>
			<button type='button' class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>
				<span class='sr-only'>Toggle navigation</span>
				<span class='icon-bar'></span>
				<span class='icon-bar'></span>
				<span class='icon-bar'></span>
			</button>
			<a class='navbar-brand' href='".$this->path."/'>".$this->title."</a>
			<a class='navbar-brand' href='javascript:return false;' id='escondeMenu' title='Esconde/Mostrar Menu lateral' style='float:right;'><i class='fa fa-list fa-fw'></i> </a>
			
		</div>
		<form action='".$this->path."/search/' method='get' class='navbar-form' style='float:left;' id='formSearch'>
			<div class='input-group custom-search-form'>
				<input type='text' name='s' class='form-control' placeholder='Search...' value='".@$_GET['s']."' style='width: 100%; min-width: 300px;'>
				<span class='input-group-btn'>
				<button class='btn btn-default' type='button' id='idBtnSearch'>
					<i class='fa fa-search'></i>
				</button>
			</span>
			</div>
		</form>
		
		<div class='input-group custom-search-form'>
		
		</div>
		
		<!-- /.navbar-header -->
		
		<ul class='nav navbar-top-links navbar-right'>";
		
		
		if ($this->clock)
		{
			$m .= "
			<li>
				<div id='clock' style='color: #ccc;'> </div>	
				
			</li>
			<!-- /.dropdown -->";
		}
		
		if (sizeof(@$this->tabMsgContent) > 0)
		{
			$m .= "
			<li class='dropdown'>
				<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
					<i class='fa fa-envelope fa-fw'></i> <i class='fa fa-caret-down'></i>
				</a>
				<ul class='dropdown-menu dropdown-messages'>
					<li>
						<a href='#'>
							<div>
								<strong>John Smith</strong>
								<span class='pull-right text-muted'>
									<em>Yesterday</em>
								</span>
							</div>
							<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
						</a>
					</li>
					<li class='divider'></li>
					<li>
						<a href='#'>
							<div>
								<strong>John Smith</strong>
								<span class='pull-right text-muted'>
									<em>Yesterday</em>
								</span>
							</div>
							<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
						</a>
					</li>
					<li class='divider'></li>
					<li>
						<a href='#'>
							<div>
								<strong>John Smith</strong>
								<span class='pull-right text-muted'>
									<em>Yesterday</em>
								</span>
							</div>
							<div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eleifend...</div>
						</a>
					</li>
					<li class='divider'></li>
					<li>
						<a class='text-center' href='#'>
							<strong>Read All Messages</strong>
							<i class='fa fa-angle-right'></i>
						</a>
					</li>
				</ul>
				<!-- /.dropdown-messages -->
			</li>
			<!-- /.dropdown -->";
		}
		
		if (sizeof(@$this->tabTaskContent) > 0)
		{
			$m .= "
			<li class='dropdown'>
				<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
					<i class='fa fa-tasks fa-fw'></i> <i class='fa fa-caret-down'></i>
				</a>
				<ul class='dropdown-menu dropdown-tasks'>
					<li>
						<a href='#'>
							<div>
								<p>
									<strong>Task 1</strong>
									<span class='pull-right text-muted'>40% Complete</span>
								</p>
								<div class='progress progress-striped active'>
									<div class='progress-bar progress-bar-success' role='progressbar' aria-valuenow='40' aria-valuemin='0' aria-valuemax='100' style='width: 40%'>
										<span class='sr-only'>40% Complete (success)</span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class='divider'></li>
					<li>
						<a href='#'>
							<div>
								<p>
									<strong>Task 2</strong>
									<span class='pull-right text-muted'>20% Complete</span>
								</p>
								<div class='progress progress-striped active'>
									<div class='progress-bar progress-bar-info' role='progressbar' aria-valuenow='20' aria-valuemin='0' aria-valuemax='100' style='width: 20%'>
										<span class='sr-only'>20% Complete</span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class='divider'></li>
					<li>
						<a href='#'>
							<div>
								<p>
									<strong>Task 3</strong>
									<span class='pull-right text-muted'>60% Complete</span>
								</p>
								<div class='progress progress-striped active'>
									<div class='progress-bar progress-bar-warning' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: 60%'>
										<span class='sr-only'>60% Complete (warning)</span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class='divider'></li>
					<li>
						<a href='#'>
							<div>
								<p>
									<strong>Task 4</strong>
									<span class='pull-right text-muted'>80% Complete</span>
								</p>
								<div class='progress progress-striped active'>
									<div class='progress-bar progress-bar-danger' role='progressbar' aria-valuenow='80' aria-valuemin='0' aria-valuemax='100' style='width: 80%'>
										<span class='sr-only'>80% Complete (danger)</span>
									</div>
								</div>
							</div>
						</a>
					</li>
					<li class='divider'></li>
					<li>
						<a class='text-center' href='#'>
							<strong>See All Tasks</strong>
							<i class='fa fa-angle-right'></i>
						</a>
					</li>
				</ul>
				<!-- /.dropdown-tasks -->
			</li>
			<!-- /.dropdown -->";
		}
		
		if (isset($this->tabAlertContent))
		{
			$qtd = sizeof($this->tabAlertContent);
			
			if ($qtd > 0)
				$contadorAlertas = "<span style='font-size: 10px;background-color: red;padding-left: 2px;padding-right: 2px;border-radius: 10px;color: #fff;position: absolute;'>".$qtd."</span>";
			
			$m .= "
			<li class='dropdown'>
				<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
					".@$contadorAlertas."
					<i class='fa fa-bell fa-fw'></i> <i class='fa fa-caret-down'></i>
				</a>
				<ul class='dropdown-menu dropdown-alerts'>";
				
				foreach ($this->tabAlertContent as $a)
				{
					$m .= "
					<li>
						<a href='".$a['link']."'>
							<div>
								<i class='fa fa-comment fa-fw'></i> ".$a['text']."
								<span class='pull-right text-muted small'>".$a['subtext']."</span>
							</div>
						</a>
					</li>
					<li class='divider'></li>";
				}
				
				if ($qtd > 0)
				{
					$m .= "
						<li>
							<a class='text-center' href='".$this->path."/alertas/'>
								<strong>Ver todos</strong>
								<i class='fa fa-angle-right'></i>
							</a>
						</li>
					</ul>";
				}
				else
				{
					$m .= "
						<li>
							<a class='text-center' href='".$this->path."/alertas/'>
								<strong>Nenhum alerta ativo</strong>
							</a>
						</li>
					</ul>";
				}
				
				$m .="
				<!-- /.dropdown-alerts -->
			</li>";
		}
		
		$m .= "
			<!-- /.dropdown -->
			<li class='dropdown'>
				<a class='dropdown-toggle' data-toggle='dropdown' href='#'>
					<i class='fa fa-user fa-fw'></i> <i class='fa fa-caret-down'></i>
				</a>
				<ul class='dropdown-menu dropdown-user'>
					<li><a href='".$this->path."/profile/'><i class='fa fa-user fa-fw'></i> Profile</a>
					</li>
					<li class='divider'></li>
					<li><a href='".$this->path."/login/logout/'><i class='fa fa-sign-out fa-fw'></i> Logout</a>
					</li>
				</ul>
				<!-- /.dropdown-user -->
			</li>
			<!-- /.dropdown -->
		</ul>
		<!-- /.navbar-top-links -->
		
		
		<!-- Brand and toggle get grouped for better mobile display 
				
				<ul class='nav navbar-right top-nav'>
					<li class='dropdown'>
						<a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-user'></i> ".$this->login." <b class='caret'></b></a>
						<ul class='dropdown-menu'>
							<li>
								<a href='".$this->path."/login/logout'><i class='fa fa-fw fa-power-off'></i> Log Out</a>
							</li>
						</ul>
					</li>
				</ul>-->
		";
		
		return $m;
		
	}
	
	public function montaPesquisa(){
	
		if (!$this->mostraMenuEsquerda)
			$escondeMenuCss = " style='display:none;'";
	
		return "
			
			<!--<li><a href='javascript:return false;' id='escondeMenu2'><i class='fa fa-list fa-fw'></i> </a></li>-->
			
			<!--
			<li class='sidebar-search'".@$escondeMenuCss." style='padding: 0px; '>
				<form action='".$this->path."/search/' method='get' class='navbar-form'>
					<div class='input-group custom-search-form'>
						<input type='text' class='form-control' placeholder='Search.....'>
						<span class='input-group-btn'>
						<button class='btn btn-default' type='button'>
							<i class='fa fa-search'></i>
						</button>
					</span>
					</div>
				</form>				
			</li>-->
		";
		
	}
	
	public function menuLateral()
	{		
		if ($this->mostraMenuEsquerda)
			$nav_options = "";
		else
			$nav_options = " lf_sidebar_closed";
		
		echo "
		<div class='navbar-default sidebar ".$nav_options."' id='idMenuLateral' role='navigation'>
            <div class='sidebar-nav navbar-collapse'>
				<ul class='nav' id='side-menu'>";
				
				echo $this->montaPesquisa();
				echo $this->montaOpcoesMenuInterno();
				
				
		echo "  </ul>
			</div>
		</div>
		";
	}
	
	public function nav(){
	
		echo '<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">';
			echo $this->menuSuperior();
			
			if ($this->menuLateral)
				$this->menuLateral();
		echo '</nav>';
	
	}
	
	public function mensagemErro()
	{
		/*
		echo "<div>";
			echo "<div class='avisoFun'>";
				echo "<img src='".$this->path."/assets/img/alert.png' height=24 style='float:left;' />";
				echo "404: Essa página não existe.";
			echo "</div>";
		echo "</div>";
		*/
		echo "
			<div class='alert alert-danger' style='text-align: center;'>
				404: Essa página não existe.
			</div>
		";
	
	}
	
	function jsLibs(){
	
		echo '
		
		
		<!-- jQuery -->
		<script src="'.$this->path.'/assets/theme/vendor/jquery/jquery.min.js"></script>

		<!-- Bootstrap Core JavaScript -->
		<script src="'.$this->path.'/assets/theme/vendor/bootstrap/js/bootstrap.min.js"></script>

		<!-- Metis Menu Plugin JavaScript -->
		<script src="'.$this->path.'/assets/theme/vendor/metisMenu/metisMenu.min.js"></script>

		<!-- Morris Charts JavaScript -->
		<!--script src="'.$this->path.'/assets/theme/vendor/raphael/raphael.min.js"></script-->
		<!--script src="'.$this->path.'/assets/theme/vendor/morrisjs/morris.min.js"></script-->
		<!--script src="'.$this->path.'/assets/theme/data/morris-data.js"></script-->

		<!-- Custom Theme JavaScript -->
		<script src="'.$this->path.'/assets/theme/dist/js/sb-admin-2.min.js"></script>
		
		<!-- PLUGINS -->
		<script src="'.$this->path.'/assets/plugins/select2/js/select2.min.js"></script>
		<script src="'.$this->path.'/assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
		<script src="'.$this->path.'/assets/plugins/validator/validator.min.js"></script>
		<script src="'.$this->path.'/assets/plugins/datetimepicker/jquery.datetimepicker.min.js"></script>
		<script src="'.$this->path.'/assets/plugins/datetimepicker/moment.js"></script>
		
		<!-- Custom JS-->
		'.$this->customJS.'
		
		';
	
	}
	
	public function setAtual($atual,$segundo='')
	{
		//echo "[setAtual: ".$atual."]";
		$this->atual = $atual;
		$this->atualSegundo = $segundo;
	}
	
	function montaOpcoesMenuInterno()
	{
		//if ($this->atualSegundo == '')
			$atual = $this->atual;
		//else
		//	$atual = $this->atualSegundo;
			
			
		//echo "[usando atual: ".$atual."]";
		//echo "[usando atualSegundo: ".$this->atualSegundo."]";
		
		$h_menu = "";
		foreach ($this->menu as $m)
		{
			if (@$m['link'])
			{
				$h_menu .= $this->montaOpcaoMenu($m['label'],$m['icon'],$m['link'],$atual);
			}
			else if (@$m['dropdown'])
			{
				$paiHighlight = false;
				$netoHighlight = false;
				
				//foreach para prever selecionando um filho, deixar highlight no pai
				//usar o extra_highlights ou query_highlights
				foreach($m['dropdown'] as $d)
				{
					//echo $d['link'];
					//se um dor filhos for o highlight, o pai deve expandir (colocando classe IN)
					if (@$d['link'])
					{
						//echo "[ $atual == ".$d['link']."]";
						if ($atual == $d['link'])
							$paiHighlight = true;
					}
					else
					{
						foreach($d['dropdown'] as $t)
						{
							//se for o neto, entao é o pai tambem
							if ($atual == $t['link'])
							{
								$paiHighlight = true;
								$netoHighlight = true;
							}
						}
					}
					//	$atual = $m['linkdrop'];
				}
				
				$lis = "";
				$lis_trd = "";
				foreach($m['dropdown'] as $sub)
				{
					//$paiHighlight = false;
					//$netoHighlight = false;
				
					$lis_trd = "";
					//verificar se tem mais um nivel de dropdown
					if (@$sub['dropdown'])
					{
						foreach($sub['dropdown'] as $trd)
						{
							$lis_trd .= $this->montaOpcaoMenu($trd['label'],$trd['icon'],$trd['link'],$atual);
							
							if ($atual == $trd['link'])
							{
								$paiHighlight = true;
								$netoHighlight = true;
							}
							
						}
						
						$lis .= $this->dropDownVertical($sub['label'],$sub['icon'],$lis_trd,'',$atual,true,$netoHighlight);						
					}
					else
						$lis .= $this->montaOpcaoMenu($sub['label'],$sub['icon'],$sub['link'],$atual);
				}
				
				$h_menu .= $this->dropDownVertical($m['label'],$m['icon'],$lis,'',$atual,false,$paiHighlight);
				
			
			}
		}
		
		return $h_menu;
	}
	
	function montaOpcaoMenu($nome,$icon = 'fa-dashboard',$link,$atual)
	{
		if (!$this->mostraMenuEsquerda)
			$escondeMenuCss = " style='display:none;'";
			
		if ($atual == $link)
		{
			$liclass = "class='active";
			$aclass = "class='active'";
		}
	
		$menu = file_get_contents('app/template/menu_lateral_opcao.html');
		
		$template_infos = [
			'path' => $this->path,
			'$escondeMenuCss' => @$escondeMenuCss,
			'aTitle' => strip_tags($nome),
			'aClass' => @$aclass,
			'liClass' => @$liclass,
			'nome' => $nome,
			'icon' => $icon,
			'link' => $link,
			
		];

		foreach($template_infos as $t => $v)
		{
			$menu = str_replace("{{".$t."}}", $v, $menu);
		}
		
		if (sizeof($this->session->permissoes)>0)
		{
			if (in_array('*', $this->session->permissoes))
				return $menu;
				
			$l = explode("/",$link);	
			
			if (in_array($l[0], $this->session->permissoes))
				return $menu;
			else
				return "";
		}
		else
		{
			return $menu;
		}
		
		
	}

	function dropDownHorizontal($label,$icon = 'fa-user',$lis,$link,$atual,$terceiro=false)
	{

		if ($atual == $link)
		{
		
			$menu = "<li class='active'>";
			
		}
		else
			$menu = "<li>";
			
		if ($terceiro)
			$class_drop = 'nav nav-third-level';
		else
			$class_drop = 'nav nav-second-level';

		return "
		
		<li>
			<a href='#'><i class='fa ".$icon." fa-fw'></i> ".$label."<span class='fa arrow'></span></a>
			<ul class='nav ".$class_drop."'>
				".$lis."
			</ul>
		</li>
		
		";

	}

	function dropDownVertical($label,$icon = 'fa-wrench',$lis,$link,$atual,$terceiro=false,$paiHighlight=false){
		//$arrow = "";
		
		if (!$this->mostraMenuEsquerda)
			$escondeMenuCss = " style='display:none;'";
		
		if ($paiHighlight){
			$class_in = " in ";
			//echo "<p>[PAI: ".$label." ";
		}else{
			//echo "<p>[PAI: ".$label." ";
			$class_in = "";
		}
		
		if ($terceiro)
			$class_drop = 'nav nav-third-level'.$class_in;
		else
			$class_drop = 'nav nav-second-level'.$class_in;
			
			
		//echo "class_drop: ".$class_drop." ]";
		//echo "(3- $atual == $link)";
		
		/*if ($atual == $link)
		{
		
			$menu = "<li class='active'>";
			//if ($this->orientacao == 'v')
				//$arrow = "<span class='side-nav-selected-arrow'></span>";
		}
		else*/
			$menu = "<li>";
		
		//<a href='javascript:;' data-toggle='collapse' data-target='#".$id."'><i class='fa fa-fw ".$icon."'></i>".$arrow." ".$label." <i class='fa fa-fw fa-caret-down'></i></a>
		$menu = $menu."
		
			<a href='#' title='".$label."'><i class='fa ".$icon." fa-fw'></i> <span class='menuTitles'".@$escondeMenuCss.">".$label."<span class='fa arrow'></span></span></a>
			<ul class='nav ".$class_drop."'>
				".$lis."
			</ul>
		</li>
		
		";
		
		if ($lis != '')
			return $menu;
		else
			return "";
		
		/*
		if (sizeof($this->session->permissoes)>0)
		{
			if (in_array('*', $this->session->permissoes))
				return $menu;
				
			if (in_array($link, $this->session->permissoes))
				return $menu;
			else
				return "";
		}
		else
		{
			return $menu;
		}*/

	}
	
	function bodyBeginBlank($menu = true){
		
		
		echo "<body>";
	
	}
	
	function bodyBegin(){
		
		$menu = $this->nav();
		if (!$this->mostraMenuEsquerda)
			$escondeMenuCss = " style='margin-left:55px;'";
		
		echo "
		
			<body>
			<div id='wrapper'>
				<!-- Navigation -->
				".$menu."
				<div id='page-wrapper'".@$escondeMenuCss.">
					
		
		";
	
	}
	
	function bodyEndBlank($jquery=''){
		
		echo "
			".$this->jsLibs()."
			".$this->jQueryReady($jquery)."
		</body>
		</html>
		
		";
	
	}
	
	function bodyEnd($jquery='')
	{
		echo "
		
					
				</div>
				<!-- /#page-wrapper -->
			</div>
			<!-- /#wrapper -->
			".$this->jsLibs()."
			".$this->jQueryReady($jquery)."
		</body>
		</html>
		
		";
	
	}
	
	function jQueryReady($jquery)
	{
		if ($this->clock)
		{
			$jquery_clock = "
			var d = new Date(".(time() * 1000).");
			function digitalClock() {
			  d.setTime(d.getTime() + 1000);
			  var hrs = d.getHours();
			  var mins = d.getMinutes();
			  var secs = d.getSeconds();
			  mins = (mins < 10 ? '0' : '') + mins;
			  secs = (secs < 10 ? '0' : '') + secs;
			  //var apm = (hrs < 12) ? 'am' : 'pm';
			  //hrs = (hrs > 12) ? hrs - 12 : hrs;
			  //hrs = (hrs == 0) ? 12 : hrs;
			  //var ctime = hrs + ':' + mins + ':' + secs + ' ' + apm;
			  var ctime = hrs + ':' + mins + ':' + secs ;
			  document.getElementById('clock').firstChild.nodeValue = ctime;
			}
			window.onload = function() {
			  digitalClock();
			  setInterval('digitalClock()', 1000);
			}";
		}
	
		echo "
		<script>
		
			
		".@$jquery_clock."
		
		$( document ).ready(function() {
			
			$('#lf_displayOnLoad').show();
			$('#lf_displayOnLoadImg').hide();
			
			$('#idBtnSearch').click(function()
			{
				$('#formSearch').submit();
			});
			
			
			$('#escondeMenu').click(function()
			{
				if ($('#idMenuLateral').hasClass('lf_sidebar_closed'))
				{
					$('#idMenuLateral').removeClass('lf_sidebar_closed');
					$('.sidebar-search').show();
					$('.menuTitles').show();
					$('#page-wrapper').css('margin-left','250px');
				}
				else
				{
					$('#idMenuLateral').addClass('lf_sidebar_closed');
					$('.sidebar-search').hide();
					$('.menuTitles').hide();
					$('#page-wrapper').css('marginLeft','55px');
				}
			});
			
			
			$('#crudForm').validator();
			
			$('#searchInput').click(function() {
				$(this).val('');
			});
			
			$('.select2').select2();
			
			$('.datepicker').datepicker({
				format: 'dd/mm/yyyy',
				language: 'pt-BR',
				
				pickerPosition: 'top-left',
				autoclose: true
			});
			
			$('.datepickerFull').datepicker({
				format: 'dd/mm/yyyy 00:00:00',
				language: 'pt-BR',
				
				pickerPosition: 'top-left',
				autoclose: true
			});
			
			$('.datepickerEN').datepicker({
				format: 'yyyy-mm-dd 00:00:00',
				language: 'pt-BR',
				
				pickerPosition: 'top-left',
				autoclose: true
			});
			
			$('.datepickerENfinal').datepicker({
				format: 'yyyy-mm-dd 23:59:59',
				language: 'pt-BR',
				
				pickerPosition: 'top-left',
				autoclose: true
			});
			
			$.datetimepicker.setLocale('pt-BR');
			$.datetimepicker.setDateFormatter('moment');
			$.datetimepicker.setDateFormatter({
				parseDate: function (date, format)
				{
					var d = moment(date, format);
					return d.isValid() ? d.toDate() : false;
				},
				formatDate: function (date, format)
				{
					return moment(date).format(format);
				},
			});
			$('.datetimepicker').datetimepicker({
				format:'DD/MM/YYYY HH:mm:ss'
			});
			$('.datetimepickerEN').datetimepicker({
				format:'YYYY-MM-DD HH:mm:ss'
			});
		
			".@$jquery."
			
		});
		</script>
		";
	}
}


?>