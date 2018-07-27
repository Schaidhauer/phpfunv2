<?php
//EXEMPLO
/*
$formconfig = array(
	'form_action'=>'produtos.php',
	'form_dbtable'=>'produtos',
	'form_title'=>'Produtos',
	'campos'=>array(
		array("Nome","nome","text",250),
		array("Componente","descricao","textarea",250),
		array("Ambiente","ambiente","select",250,array(1=>"Produção",2=>"Homologação",3=>"Desenvolvimento")),
		array("Produto","produto","select",250,"produtos"),
	)
);TESTE DE MODIFICACAO
 
$crud = new CrudBootstrap($formconfig);
$crud->criaFormAdd();
*/

require_once("class.config.php");
require_once("class.ldap.php");
require_once("class.password.php");

Class CrudBootstrap{

	public $formconfig;
	
	public $campos;
	public $filtroWhere;
	public $historyFiltro;
	
	public $config;
	
	public $bdconn;
	
	public $paginar;
	public $paginarMax;
	public $paginarQuery;
	public $paginarPaginas;
	public $paginarStart;
	public $paginarParametros;
	public $paginarTotal;

	public function __construct($formconfig = ''){
	
		$this->config        = new Config();
		
		$this->path = Core::$path;
	
		if ($formconfig != '')
		{
			$this->form_action   = $formconfig['form_action'];
			$this->form_dbtable  = $formconfig['form_dbtable'];
			
			//Para os casos de nao informar a class, assumir o nome do dbtable
			if (@$formconfig['form_class'] != '')
				$this->form_class    = $formconfig['form_class'];
			else
				$this->form_class    = $formconfig['form_dbtable'];
				
			$this->form_title    = $formconfig['form_title'];
			
			$this->campos        = $formconfig['campos'];
			
			if (isset($formconfig['paginar']))
			{
				//echo "PAGINAR NAO DEFINIDO";
				$this->paginar       = $formconfig['paginar'];
			}
			else
			{
				//echo "PAGINAR DEFINIDO";
				$this->paginar       = true;
			}	
		}
		$this->bdconn = new Conexao();
	}
	
	public function post($debug = false)
	{
		if ($_REQUEST)
		{
			if ($this->campos)
			{
				foreach ($this->campos as $campo => $v)
				{
					//se o campo estiver no array de campos como hidden é sinal de campo custom, como controle de datas e etc
					//Entao, verificamos se este campo está em branco, se tiver, nem envia pro post.
					//Exemplo de campos de data de criação, só vai fazer insert do valor na hora da criação e não mais na edição da linha.
					if ($v['type'] != 'hidden')
					{
						if ((!is_array(@$_REQUEST[$v['name']])) && (@$v['type'] != 'selectRel'))
						//if (!is_array(@$_POST[$v['name']]))
						{
							if (@$v['post_bd_mask'] != '')
							{
								$post_bd_mask = fixDate($v['post_bd_mask'],$_REQUEST[$v['name']]);
								$post[$v['name']] = $post_bd_mask;
							}
							else
							{
								//fiz para os NULL
								if (isset($_REQUEST[$v['name']]))
									$post[$v['name']] = @$_REQUEST[$v['name']];
							}
						}
						else
						{
							//se for um array, é um sinal que é de um campo multiplo, enviar as informacoes no $post compiladas
							$post[$v['name']] = array(
								'tableRel'=>$v['tableRel'],
								'idPai'=>$v['idPai'],
								'idFilhos'=>$v['idFilhos'],
								'values'=>@$_REQUEST[$v['name']]
								);
						}
					}
					else
					{ 
						//echo $v['name'].":".$_REQUEST[$v['name']]."<br/>";
						if (@$_REQUEST[$v['name']] != '')
						{
							
							$post[$v['name']] = @$_REQUEST[$v['name']];
						}
					}
						
				}
			}
			

			
			if ($debug)
			{
				print_r($_REQUEST);
				echo "<hr/>";
				print_r($post);
				die();
			}
			
			if (@$_POST['crud'] == 'edit')
			{
				$this->editCRUD($_POST['id'],$post);
				if ($this->crudError == "")
					echo "<div class='alert alert-success' style='text-align: center;'>Editado com sucesso! <a href='".$_POST['id']."'>Visualizar</a></div>";
				else
					echo $this->crudError;
			}
			else if (@$_POST['crud'] == 'add')
			{
				$last_id = $this->insertCRUD($post);
				if ($this->crudError == "")
					echo "<div class='alert alert-success' style='text-align: center;'>Criado com sucesso! <a href='".$last_id."'>Visualizar</a></div>";
				else
					echo $this->crudError;
			}
			else if (@$_POST['crud'] == 'login')
			{
				$this->loginCRUD($_POST['usuario'],$_POST['senha']);
			}
			else if (@$_GET['crud'] == 'filtro')
			{
				$w;
				foreach ($post as $p => $v)
				{
					if ($v <> '')
					{
						if (!is_array($v))
						{
							if (substr($p,0,2) == 'id')
								$w[] = " ".$p." = '".$v."' ";
							else
								$w[] = " ".$p." LIKE '%".$v."%' ";
						}
						else
						{
							
							$rel_join = @implode(',',$v['values']);
							if ($rel_join != '')
							{
								$relacionamentos = " SELECT ".$v['idPai']." FROM ".$v['tableRel']." WHERE ".$v['idFilhos']." IN (".$rel_join.") ";
								//echo $relacionamentos;
								$res = $this->bdconn->select($relacionamentos);
							
								foreach($res as $rr)
								{
									$res_array[] = $rr[$v['idPai']];
								}
								
								//existe relacionamentos
								if ($res)
								{
									//print_r($res);
									$rels = implode(',',$res_array);
									
									//agrega os IDs do pai na consulta principal
									$w[] = " id IN (".$rels.") ";
								}
								else
								{
									//se não tiver nenhum dos relcionamentos, faz add um where fake para nao trazer nenhum.
									$w[] = " id IN (-1) ";
								}
							}
						}
					}
				}
				if (sizeof(@$w) > 0)
				{
					$where = implode(' AND ',$w);
					$this->setListWhere($where);
					
				}
				
				
			}
			
		}
	}
	
	public function criaFormLogin()
	{
		echo "<div class='container'>
				<div class='row'>
					<div class='col-md-4 col-md-offset-4'>
						<div class='login-panel panel panel-default'>
							<div class='panel-heading'>
								<h3 class='panel-title'>Login</h3>
							</div>
							<div class='panel-body'>
								<form role='form' method='post' action=''>
									<fieldset>
										<input type='hidden' value='login' name='crud'/>
										<div class='form-group'>
											<input class='form-control' placeholder='E-mail' name='usuario' type='text' autofocus autocomplete='off'>
										</div>
										<div class='form-group'>
											<input class='form-control' placeholder='Password' name='senha' type='password' autocomplete='off'>
										</div>
										<!-- <div class='checkbox'>
											<label>
												<input name='remember' type='checkbox' value='Remember Me'>Remember Me
											</label>
										</div>-->
										
										<button type='submit' class='btn btn-success'>Acessar</button>
										<!--Change this to a button or input when using this as a form 
										<a href='index.html' class='btn btn-lg btn-success btn-block'>Login</a>-->
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>";
	}
	
	public function criaFormEdit($id = 0)
	{
		if ($id > 0)
		{
			$crud = $this->getCRUDInfo($this->form_dbtable,$id);
			if ($crud)
			{
				echo "<div class='col-lg-12'>";
					echo "<form action='../../' role='form' id='crudForm' data-toggle='validator' method='post' class='navbar-left' style='width: 100%;'>";

						echo "<input type='hidden' value='edit' name='crud'/>";
						echo "<input type='hidden' value='".$id."' name='id'/>";
						
						foreach ($this->campos as $campo)
						{
							$subLabel = "";
							
							if ($campo['type'] == 'password'){
								$e = new Encryption();
								$value_text = $e->decode($crud[$campo['name']]);
							}else{
								$value_text = @$crud[$campo['name']];
							}
							
							if ($campo['type']=='selectRel')
							{
								$reltemp = $this->getColumnCRUDInfoMulti($campo['idFilhos'],$campo['tableRel'],$campo['idPai'],$id);
								$value_text = $reltemp;
							}
							
							if ($campo['type']!='hidden')
							{
								if (@$campo['required'] && ($value_text == ''))
								{
									$classreq = ' has-error';
									$primeiroBranco = false;
								}
								else
								{
									$classreq = '';
									$primeiroBranco = true;
								}
								//echo "<tr><td>";
									echo "<div class='form-group".$classreq."' style='margin-bottom: 0px;'>";
										if (@$campo['subLabel'] != '')
											$subLabel = " <span style='font-size: 10px; font-weight: 100; color:#666;'>".$campo['subLabel']."</span>";
									
										echo "<label class='control-label' for='id".$campo['name']."'>".$campo['label']."".@$subLabel."</label>";
										echo $this->formGeraElemento($campo,$value_text,$primeiroBranco);
										//echo "<td><input type='".$campo[2]."' name='".$campo[1]."' class='form-control' value='".$value_text."' style='width:".$campo[3]."px'/></td>";
									echo "</div>";
								//echo "</td></tr>";
							}
							else
							{
								$hiddens[] = $this->formGeraElemento($campo,'');
							}
						}
						
						echo "<div class='form-group' style='margin-top: 10px;'>";
							echo "<button type='button' onclick=\"location.href='../../';\"  class='btn btn-default'>Cancelar</button> ";
							echo "<button type='submit' class='btn btn-success'>Salvar</button>";
						echo "</div>";
						
						if (@$hiddens)
							foreach($hiddens as $h)
								echo $h;
								
					echo "</form>";
				echo "</div>";
			}
			else
			{
				echo "ID não encontrado.";
			}
		}else{
			echo "ID não informado.";
		}
	}
	
	public function criaFormAdd()
	{	
			echo "<div class='col-lg-12'>";
				echo "<h3>Adicionando ".$this->form_title."</h3>";
			
				echo "<form action='../' role='form' id='crudForm' data-toggle='validator' method='post' class='navbar-left' style='margin-left:auto;margin-right:auto;width: 100%;'>";
					echo "<input type='hidden' value='add' name='crud'/>";
					
					foreach ($this->campos as $campo)
					{
						$subLabel = "";
						if ($campo['type']!='hidden')
						{
							if (@$campo['required'])
								$classreq = ' has-error';
							else
								$classreq = '';
							
							
							if (@$campo['default']!='')
								$value=$campo['default'];
							else
								$value='';
							
							echo "<div class='form-group".$classreq."' style='margin-bottom: 0px;'>";
								if (@$campo['subLabel'] != '')
									$subLabel = " <span style='font-size: 10px; font-weight: 100; color:#ccc;'>".$campo['subLabel']."</span>";
							
								echo "<label class='control-label' for='id".$campo['name']."'>".$campo['label']."".@$subLabel."</label>";
								echo $this->formGeraElemento($campo,$value,true);
							echo "</div>";
						}
						else
						{
							$hiddens[] = $this->formGeraElemento($campo,'');
						}
					}
					
					echo "<div class='form-group' style='margin-top: 10px;'>";
						echo "<button type='button' onclick=\"location.href='../';\"  class='btn btn-default'>Cancelar</button> ";
						echo "<button type='submit' class='btn btn-success'>Salvar</button>";
					echo "</div>";
						
					if (@$hiddens)
						foreach($hiddens as $h)
							echo $h;
							
				echo "</form>";
			echo "</div>";
	}
	
	public function criaView($id,$btnExtra='')
	{
	
		//$path = $this->core->system_path;
		$obj = $this->getById($id);
		
		echo "<div class='row'>";
			$link_edit = $this->path."/".$this->form_class."/edit/".$obj['id'];
			echo "<a href='".$link_edit."'><span class='label label-default' title='Editar'>Editar</span></a> ";
			echo "<a href='../'><span class='label label-default' title='Voltar para lista'>Ver lista</span></a> ".$btnExtra;

			//Se tiver um campo com NOME, escreve grande na tela
			//if (@$obj['nome'] != '')
			//	echo "<h2>".$obj['nome']."</h2>";
			echo "<br/><br/>";
			
			foreach ($this->campos as $campo)
			{
				if ($campo['type'] == 'selectRel')
				{
					if (@$campo['selectLabel']!='')
						$selectLabel = $campo['selectLabel'];
					else
						$selectLabel = "nome";
						
					//$relNames = $this->formGetSelectContent($campo['options'],$selectLabel,@$campo['where']);
					if (!is_array($campo['options']))
						$relNames = $this->formGetSelectContent($campo['options'],$selectLabel,@$campo['where']);
					else
						$relNames = $campo['options'];
					
					$reltemp = $this->getColumnCRUDInfoMulti($campo['idFilhos'],$campo['tableRel'],$campo['idPai'],$id);
					
					
					$ret=array();
					echo "<p><b>".$campo['label'].":</b></p>";
					foreach($relNames as $r)
					{
						foreach ($reltemp as $tmp)
						{
							if (@$r['id'] == $tmp)
								$ret[] = "<a href='".$this->path."/".$campo['options']."/".@$r['id']."'>".@$r[$selectLabel]."</a>";
						}
					}
					$value_text = implode(', ',$ret);
					echo "<p>".$value_text."</p>";
					
				}
				else if ($campo['type'] == 'select')
				{
					if (@$campo['selectLabel']!='')
						$selectLabel = $campo['selectLabel'];
					else
						$selectLabel = "nome";
						
					if (!is_array($campo['options']))
						$relNames = $this->formGetSelectContent($campo['options'],$selectLabel,@$campo['where']);
					else
						$relNames = $campo['options'];
						
					//print_r($relNames);
					
					//print_r($relNames);
					//echo "<p><b>".$campo['label'].":</b> ".$id." </p>";
					
					foreach($relNames as $v => $r)
					{
						//echo $obj[$campo['name']];
						if (!is_array($r))
						{
							//se enviar o array com os options e nao fazer a busca no DB
							if (@$v == $obj[$campo['name']])
								echo "<p><b>".$campo['label'].":</b> ".@$r."</p>";
						}
						else if (@$r['id'] == $obj[$campo['name']])
							echo "<p><b>".$campo['label'].":</b> <a href='".$this->path."/".$campo['options']."/".@$r['id']."'>".@$r['nome']."</a></p>";
					}
					//print_r($relNames);
					//$reltemp = $this->getColumnCRUDInfoMulti($campo['idFilhos'],$campo['tableRel'],$campo['idPai'],$id);
					
					//foreach($reltemp as $r)
					//{
						//$ret = "<a href='".$path."/".$campo['options']."/".@$relNames[$r]['id']."'>".@$relNames[$r]['nome']."</a>";
					//}
					//$value_text = implode(',',$ret);
					//echo "<p>".$ret."</p>";
					
				}
				else				
					echo "<p><b>".$campo['label'].":</b> ".$obj[$campo['name']]."</p>";
			}
			
		echo "</div>";
	
	}
	
	public function getById($id = '')
	{
		return $this->getCRUDInfo($this->form_dbtable,$id);
	}
	
	function getGETqueryStringSEMorder()
	{
		$ret = array();
		$arrGet = explode('&',$_SERVER['QUERY_STRING']);
		foreach($arrGet as $a)
		{
			$op = explode('=',$a);
			if (($op[0] != 'order') && ($op[0] != 'order_type'))
			{
				$ret[] = $a;
			}
		}
		$r = implode('&',$ret);
		return $r;
	}
	
	function getGETStringORDERBY()
	{
		$this->orderTypeByAtual = 'desc';
		$this->orderTypeByInvertido = 'asc';
		$this->orderByAtual = 'id';
		
		$arrGet = explode('&',$_SERVER['QUERY_STRING']);
		foreach($arrGet as $a)
		{
			$op = explode('=',$a);
			if ($op[0] == 'order')
			{
				$this->orderByAtual = $op[1];
			}
			else if ($op[0] == 'order_type')
			{
				$this->orderTypeByAtual = $op[1];
			}
		}
		
		if ($this->orderTypeByAtual == 'desc')
			$this->orderTypeByInvertido = 'asc';
		else
			$this->orderTypeByInvertido = 'desc';
	}
	
	public function criaFormList($colunas = array('Nome'=>'nome'),$btnExtra='')
	{
		$res = $this->getList("*");
		$this->botaoCriar($btnExtra);
		
		//$path = $this->core->system_path;
		
		
		
		if ($res)
		{
			if ($this->paginarTotal['COUNT(id)'] > 0)
				echo "<p style='clear: both;'> Mostrando ".sizeof($res)." registros de ".$this->paginarTotal['COUNT(id)'].".</p>";
			else
				echo "<p style='clear: both;'> ".sizeof($res)." registros.</p>";
			
			echo "<table class='table'>";
				echo "<tr style='color:#000; background:#fff; text-align:left;'>";
					echo "<th>&nbsp;</td>";
					$col_size = sizeof($colunas);
					
					$col_width = ceil(100/$col_size);
					//echo "<p>".$col_width."</p>";
					foreach ($colunas as $c => $v)
					{
						//print_r($v);
						$orderIcon = '';
						if (!is_array($v))
						{
							//$orderIcon = " <i class='fa fa-unsorted'></i>";
							/*if ($this->orderByAtual == @$v)
							{
								if ($this->orderTypeByAtual == 'desc')
									$orderIcon = " <i class='fa fa-sort-desc'></i>";
								else
									$orderIcon = " <i class='fa fa-sort-asc'></i>";
							}*/
							
							echo "<th style='color:#000;width:".$col_width."%;' class='orderColuna' ref='".@$v."'>".$c."".@$orderIcon."</td>";
						}else
							echo "<th style='color:#000;width:".$col_width."%;'>".$c."</td>";
					}
				echo "</tr>";
				foreach ($res as $v)
				{
					$link_edit = $this->path."/".$this->form_class."/edit/".$v['id'];
					$link_view = $this->path."/".$this->form_class."/".$v['id'];
					
					$btn = "<a href='".$link_edit."'><span class='badge' style='background-color:#cfcfcf' title='Editar'>
						<span class='fa fa-edit' aria-hidden='true'></span> </span></a>";
					$btnView = "<a href='".$link_view."'><span class='badge' style='background-color:#cfcfcf' title='Ver'>
						<span class='fa fa-eye' aria-hidden='true'></span> </span></a>";
					
				
					echo "<tr style='color:#000; background:#fff; text-align:left;'>";
						echo "<td style='color:#000;'>".$btn." ".$btnView."</td>";
						foreach ($colunas as $cr => $vr)
						{
							if (is_array($vr))
							{
								if (isset($vr['relTable']))
								{
									$rels_table = array();
									$rels = $this->getColumnCRUDInfoMulti($vr['field'],$vr['relTable'],$vr['fieldPai'],$v['id']);
									
									if (sizeof($rels) > 0)
									{
										foreach ($rels as $r)
										{
											//se vier com valor no relURL, é sinal que a classe é outra, e não a mesma do table
											if (@$vr['relURL'] != '')
												$relURL = $vr['relURL'];
											else
												$relURL = $vr['table'];
											
											//se vier FALSE é pq não devemos ter link neste CRUD
											if (@$vr['linkURL'])
												$relURL = "";
											
											$rels_table[] = $this->getColumnCRUDInfo($vr['return'],$vr['table'],$r,$relURL);
										}
										$rtb = implode(" ",$rels_table);
									}
									else
									{
										$rtb = '';
									}
									
									echo "<td style='color:#000;'>".$rtb."</td>";
									
								}
								else if (is_array(@$vr['options']))
								{
									/*
									//se vier com valor no relURL, é sinal que a classe é outra, e não a mesma do table
									if (@$vr['relURL'] != '')
										$relURL = $vr['relURL'];
									else
										$relURL = $vr['table'];
									
									//se vier FALSE é pq não devemos ter link neste CRUD
									if (@$vr['linkURL'])
										$relURL = "";
									
									if ($relURL =! '')
										echo "<a href='".$path."/".$relURL."/".$id."'>".$vr['options'][$v[$vr['field']]]."</a>";
									else*/
										echo "<td style='color:#000;'>".$vr['options'][$v[$vr['field']]]."</td>";
								}
								else if (@$vr['function'] != '')
								{
									//chamar funcao passando customizada para tratar o valor
									$ret = call_user_func_array(array($vr['functionClass'], $vr['function']), array($v[$vr['field']])); 
									echo "<td style='color:#000;'>".$ret."</td>";
								}
								else
								{
									//se vier com valor no relURL, é sinal que a classe é outra, e não a mesma do table
									if (@$vr['relURL'] != '')
										$relURL = $vr['relURL'];
									else
										$relURL = $vr['table'];
									
									//se vier FALSE é pq não devemos ter link neste CRUD
									if (isset($vr['linkURL']))
										if (!$vr['linkURL'])
											$relURL = "";	
												
									echo "<td style='color:#000;'>".$this->getColumnCRUDInfo($vr['return'],$vr['table'],$v[$vr['field']],$relURL)."</td>";
								}
							}
							else
								echo "<td style='color:#000;'>".$v[$vr]."</td>";
						}
					echo "</tr>";
				}
			echo "</table>";
			
			if ($this->paginar)
				$this->mostraPaginacao();
		}
		else
		{
			echo "<p style='clear: both;'> Nenhum registro de ".$this->form_title." encontrado.</p>";
		}
	}
	
	public function startPaginar()
	{
		$this->paginarMax = $this->config->config['paginarMax'];
		//$this->paginar = true;
		//$this->paginarStart = $start;
		
		$this->paginarTotal = $this->getListTotal();
		
		//Quantas paginas devem aparecer
		$this->paginarPaginas = ceil($this->paginarTotal['COUNT(id)'] / $this->paginarMax);
		
		
		//$this->paginarParametros = $this->core->cmd;
		$this->paginarParametros = Uri::getUri();
		
		if (@$_GET['p']!='')
		{
			if (@$_GET['p'] > 0)
				$this->paginarStart = ($_GET['p'] - 1) * $this->paginarMax;
			else
				$this->paginarStart = 0;
		}
		else
		{
			$this->paginarStart = 0;
		}
		
		$this->paginarQuery = " LIMIT ".$this->paginarStart.", ".$this->paginarMax;
	}
	
	public function mostraPaginacao()
	{
		$paginasPadding = 5;
		
		$keepFiltro = "&".$this->rebuildGETwithoutPages($_SERVER['QUERY_STRING']);
		
		//foi enviado alguma pagina na URL?
		if (@$_GET['p'] == '')
			$_GET['p']=1;
		if (@$_GET['p'] != '')
		{
		
			//pega X para a esquerda
			$paginasEsquerda = $_GET['p'];
			$paginasEsquerda = $_GET['p']-$paginasPadding;
			//$paginasEsquerda = $_GET['p']-1;
			//echo "E: ".$paginasEsquerda;
			if ($paginasEsquerda <= 0)
				$paginasEsquerda = 0;
			
			//pega Y para a direita
			$paginasDireita = $_GET['p']+$paginasPadding;
			//echo "D: ".$paginasDireita;
			//echo "P: ".$_GET['p'];
			if ($paginasDireita >= $this->paginarPaginas)
				$paginasDireita = $this->paginarPaginas;
				
			$paginacao = "";
			
			$paginacao .= " <span class='label label-default' title='Primeiro (".$this->paginarPaginas.")' style='margin-left: 4px; margin-right: 4px;'><a href='".$this->path."/".$this->form_class."/listar/?p=1".$keepFiltro."' style='color:#fff;'> << </a></span> ";			
			if ($paginasEsquerda > 0 )
			{
				$paginacao .= " <span class='label label-default' title='Ir para o próximo bloco' style='margin-right: 4px'><a href='".$this->path."/".$this->form_class."/listar/?p=".($_GET['p']-$paginasPadding).$keepFiltro."' style='color:#fff;'> < </a></span> ";
			}
			
			for($e=$paginasEsquerda+1;$e<=($_GET['p']-1);$e++)
			{
				$paginacao .= "<span class='label label-default' title='Ir para pagina de resultado ".$e."'><a href='".$this->path."/".$this->form_class."/listar/?p=".$e.$keepFiltro."' style='color:#fff;'>".$e."</a></span> ";
			}	
			
			$paginacao .= "<span class='label' style='color:#aaa; border: 1px solid #ccc' title='Pagina de resultado atual'>".$_GET['p']."</span> ";
			
			if (($paginasDireita-$paginasPadding) < $this->paginarPaginas)
			{
				for($d=($_GET['p']+1);$d<=$paginasDireita;$d++)
				{
					$paginacao .= "<span class='label label-default' title='Ir para pagina de resultado ".$d."'><a href='".$this->path."/".$this->form_class."/listar/?p=".$d.$keepFiltro."' style='color:#fff;'>".$d."</a></span> ";
				}
				if ( ($_GET['p']+$paginasPadding) <= ($this->paginarPaginas))
				{
					$paginacao .= " <span class='label label-default' title='Ir para o próximo bloco' style='margin-right: 4px;'><a href='".$this->path."/".$this->form_class."/listar/?p=".($_GET['p']+$paginasPadding).$keepFiltro."' style='color:#fff;'> > </a></span> ";
				}
			}	
			$paginacao .= " <span class='label label-default' title='Último (".$this->paginarPaginas.")'><a href='".$this->path."/".$this->form_class."/listar/?p=".$this->paginarPaginas.$keepFiltro."' style='color:#fff;'> >> </a></span> ";
				
			echo $paginacao;
			echo "<hr/>";
		}
		else
		{
		
		
		}
	}
	
	public function rebuildGETwithoutPages($query)
	{
		
		$g = array();
		$que = explode('&',$query);
		
		//print_r($que);

		foreach($que as $q)
		{
			if (!empty($q)){
				if ($q[0] != 'p')
					$g[] = $q;
			}
		}
		
		$ret = implode("&",$g);
		return $ret;
	
	}
	
	public function setLimit($limit = 'LIMIT 0,10')
	{
		$this->limit = $limit;
	}
	
	public function setOrderby($order = '',$type='DESC')
	{
		$this->Orderby = "ORDER BY ".$order." ".$type;
	}
	
	public function setListWhere($where = '')
	{
		if ($this->filtroWhere == '')
			$this->filtroWhere = $where;
		
		//if ($this->filtroWhere != '')
		//	$this->filtroWhere .= " AND ".$where;
		//else
		//	$this->filtroWhere = $where;
	}
	
	public function getListTotal()
	{
		if ($this->filtroWhere == '')
			$sql = "SELECT COUNT(id) FROM ".$this->form_dbtable.";";
		else
			$sql = "SELECT COUNT(id) FROM ".$this->form_dbtable." WHERE ".$this->filtroWhere.";";
			
		//echo $sql; 
		
		$res = $this->bdconn->select($sql);
		
		return $res[0];
	
	}
	
	public function getList($campos)
	{
		$tbl = $this->form_dbtable;
		
		if ($this->paginar)
			$this->startPaginar();
		
		if ($campos != '*')
			$campos = 'id,'.$campos;
			
		
		if (@$this->Orderby == '')
			$this->Orderby = "ORDER BY id DESC";
		
		if (@$_GET['order'] != '')
			$this->setOrderby($_GET['order'],@$_GET['order_type']);
		
			
		if ($this->filtroWhere == '')
			$sql = "SELECT ".$campos." FROM ".$tbl." ".$this->Orderby;
		else
			$sql = "SELECT ".$campos." FROM ".$tbl." WHERE ".$this->filtroWhere." ".$this->Orderby;
			
		if ($this->paginar)
			$sql = $sql.$this->paginarQuery;
		else
		{
			if ($this->limit != '')
				$sql = $sql." ".$this->limit;
		}
			
		
		//echo $sql; 
		
		$res = $this->bdconn->select($sql);
		
		return $res;
	
	}
	
	public function jqueryFiltro()
	{
		//echo " <span class='label label-success' style='cursor:pointer;' title='Mostrar filtro'><a href='#' id='btnFiltro' style='color:#fff;'>Filtro</a></span>";
		return "
		
			$(document).on('click', '#btnFiltro', function()
			{
				event.preventDefault();
				$('#divFiltro').toggle('slow');
			});
			
			$(document).on('click', '#btnFiltroClear', function()
			{
				//alert('limpando filtro');
				//event.preventDefault();
				$('#filterForm').find('input:text, input:password, input:file, select, textarea').val('');
				$('#filterForm').find('input:radio, input:checkbox').removeAttr('checked').removeAttr('selected');
			});
			
			
			
			
			//order nas colunas - colocar o botao de ORDER AVALIABLE
			$('.orderColuna').each(function( index )
			{
				var ref = $(this).attr('ref');
				
				if ('".@$this->orderByAtual."' != '')
				{
					if (ref == '".@$this->orderByAtual."')
					{
						if ('".@$this->orderTypeByAtual."' == 'desc')
							var newLabel = $(this).text()+' <i class=\'fa fa-sort-desc\'></i>';
						else
							var newLabel = $(this).text()+' <i class=\'fa fa-sort-asc\'></i>';
						
					}
					else
					{
						var newLabel = $(this).text()+' <i class=\'fa fa-unsorted\'></i>';
						
					}
					
					$(this).html(newLabel);
				}
				
			});
			
			//order nas colunas com um clique
			$(document).on('click', '.orderColuna', function()
			{
				var getquery = '';
				var ref = $(this).attr('ref');
				if (ref != '')
				{
					//ref = $(this).text();
						
					//alert('Ordenar: '+ref);
					if ('".@$this->orderByAtual."' != '')
					{
						getquery = '?order='+ref+'&order_type=".@$this->orderTypeByInvertido."';
					}
					else
						getquery = '?order='+ref;
					
					
					window.location.replace(getquery+'&".@$this->getGETqueryStringSEMorder()."');
			}
			});
		
		";
	}
	
	public function botaoCriar($btnExtra='')
	{
		//$path = $this->core->system_path;
		//$path = Core::$path;
		
		//para o botao filtro ficar verde quando tiver filtro ativo
		//if (@$this->historyFiltro != '')
		if (@$_GET['crud'] == 'filtro')
			$corFiltro = 'success';
		else
			$corFiltro = 'default';
		
		echo "<div id='divBotoes' style='clear:both;'>";
			echo "<span class='label label-default' style='cursor:pointer;' title='Criar novo'><a href='".$this->path."/".$this->form_class."/add/' style='color:#fff;'>Adicionar</a></span>";
			echo " <span class='label label-".$corFiltro."' style='cursor:pointer;' title='Mostrar filtro'><a href='#' id='btnFiltro' style='color:#fff;'>Filtro</a></span>";
			if ($btnExtra != '')
				echo " ".$btnExtra;
		echo "</div>";
	}
	
	public function criaFiltro()
	{
		//$path = $this->core->system_path;
		//$path = Core::$path;
		$this->getGETStringORDERBY();
		
		echo "<div id='divFiltro' style='display:none;'>";
			echo "<form action='".$this->path."/".$this->form_class."/listar/' method='get' class='navbar-form navbar-left' id='filterForm'>";
				echo "<input type='hidden' value='filtro' name='crud'/>";
				echo "<table class='table'>";
				
					//print_r($this->historyFiltro);
						foreach ($this->campos as $campo)
						{
							if ($campo['type']!='hidden')
							{
								
								//print_r($this->historyFiltro[$campo['name']]);
								
								echo "<tr>";
									echo "<td style='text-align:right;' title='".$campo['type']."'>".$campo['label'].":</td>";
									
									if ($campo['type'] != 'selectRel')
									{
										//ajusta o filtro caso não venha nada, colocar o padrão (se setado)
										
										if (@$_GET['crud'] == 'filtro')
										{
											//$value=$_GET[$campo['name']];
											if (@$_GET[$campo['name']] != '')
												$value=$_GET[$campo['name']];
											else
												$value=@$campo['default'];
										}
										else
										{
											//$value='';
											$value=@$campo['default'];
										}
										/*
										if (($this->historyFiltro[$campo['name']] == '') && (@$campo['default']!=''))
											$value=$campo['default'];
										else
											$value=$this->historyFiltro[$campo['name']];
										*/
										echo "<td title='".$value."'>".$this->formGeraElemento($campo,$value,true,true,true)."</td>";
									}
									else
									{
										//print_r($_GET);
										if (@$_GET['crud'] == 'filtro')
										{
											if (@$_GET[$campo['name']] != '')
												$value=@$_GET[$campo['name']];
											else
												$value=@$campo['default'];
										}else
											$value=@$campo['default'];
									
										//echo "<td>".$this->formGeraElemento($campo,$this->historyFiltro[$campo['name']]['values'],false,true)."</td>";
										echo "<td>".$this->formGeraElemento($campo,$value,false,true,true)."</td>";
									}	
								echo "</tr>";
							
							}
							else
							{
								
								//$hiddens[] = $this->formGeraElemento($campo,$this->historyFiltro[$campo['name']],true);
								
							}
							
						}
						
					
					echo "<tr>";
							echo "<td colspan=2><input class='btn btn-info' type='button' id='btnFiltroClear' value='Limpar'/> <input class='btn btn-success' type='submit' value='Filtrar'/></td>";
					echo "</tr>";
				echo "</table>";
				
				//foreach(@$hiddens as $h)
				//	echo $h;
					
			echo "</form>";
		echo "</div>";
	}
	
	public function formGetSelectContent($tbl,$label='nome',$where='')
	{
		//validar para ver se não veio uma query MySQL ao invez do nome da tabela ('show procedure status')
		//verifico se não tem um ESPACO na string tbl, se tiver, sinal que não é uma tabela
		
		if (preg_match('/\s/',$tbl))
		{
			$sql = $tbl;
			$res = $this->bdconn->select($sql);
			foreach ($res as $r)
			{
				$ret[] = array('id'=>$r['Name'],'nome'=>$r['Name']);
			}
			return $ret;
		}
		else
		{
			if ($where != '')
				$where = "WHERE ".$where;
				
			$sql = "SELECT id,".$label." FROM ".$tbl." ".$where;
			$res = $this->bdconn->select($sql);
			return $res;
		}
		
		
		
	}
	
	public function formGeraElemento($campo,$value,$primeiroBranco=false,$ignoraRequired=false,$fromFiltro=false)
	{
		//print_r($value);
		
		//form validation
		$selectLabel = 'nome';
		
		
		if (@$campo['required'] && !$ignoraRequired)
			$req = 'required';
		else
			$req = '';
	
		if (@$campo['size'] > 0)
			$size = $campo['size']."px";
		else
			$size = "100%";
			
			
		if (@$campo['height'] > 0)
			$height = "height: ".$campo['height']."px";
		
			
		if (($campo['type'] == 'text') || ($campo['type'] == 'password'))
		{
			//verifica se é necessário tratamento de data 
			if ((@$campo['dt_tela_mask'] != '') && ($value!=''))
			{
				//echo ">>".$campo['dt_tela_mask']."||".$value."<<";
				$value = fixDate($campo['dt_tela_mask'],$value);
			}
			
			if (@$campo['color-templates'] != '')
			{
				$t = "";
				foreach ($campo['color-templates'] as $tpl)
				{
					$t .= "<span class='tplSelect label label-default' style='background-color:#".$tpl."' ref='".$tpl."'>".$tpl."</span> ";
				}
			}
			return "<input type='".$campo['type']."' id='id".$campo['name']."' name='".$campo['name']."' value=\"".$value."\" class='form-control ".@$campo['class']."' style='width:".$size."' autocomplete='off' ".$req."/> ".@$t;
		}
		else if ($campo['type'] == 'hidden')
		{
			return "<input type='hidden' name='".$campo['name']."' value='".$value."' autocomplete='off'/>";
		}
		else if($campo['type'] == 'textarea')
		{
			return "<textarea name='".$campo['name']."' class='form-control ".@$campo['class']."' style='width:".$size.";".@$height."' ".$req."/>".$value."</textarea>";
		}
		else if($campo['type'] == 'select')
		{
			if (@$campo['selectLabel']!='')
				$selectLabel = $campo['selectLabel'];
		
			if (!is_array($campo['options']))
				$sel = $this->formGetSelectContent($campo['options'],$selectLabel,@$campo['where']);
			else
				$sel = $campo['options'];
				
				
			$return_sel = "";
			
			$return_sel .= "<select name='".$campo['name']."' style='width:".$size."' class='form-control ".@$campo['class']."' ".$req.">";
			
			if ($primeiroBranco)
			{
				if ($value == '')
					$return_sel .= "<option value='' selected>&nbsp;</option>";
				else
					$return_sel .= "<option value=''>&nbsp;</option>";
			}
				
			if ($sel)
			{
				foreach ($sel as $v => $s)
				{
					//print_r($s);
					if (!is_array($campo['options'])){
						if ($value == $s['id'])
							$return_sel .= "<option value='".$s['id']."' selected>".$s[$selectLabel]."</option>";
						else
							$return_sel .= "<option value='".$s['id']."'>".$s[$selectLabel]."</option>";
					}else{
						if ($fromFiltro)
						{
							if (($value == $v) && ($value != ''))
								$return_sel .= "<option value='".$v."' selected>".$s."</option>";
							else
								$return_sel .= "<option value='".$v."'>".$s."</option>";
						}
						else
						{
							if ($value == $v)
								$return_sel .= "<option value='".$v."' selected>".$s."</option>";
							else
								$return_sel .= "<option value='".$v."'>".$s."</option>";
						}
					}
				
				}
			}
			$return_sel .= "</select>";
			
			return $return_sel;
		}
		else if($campo['type'] == 'selectRel')
		{
			//print_r($value);
			//echo "<hr/>DEBUG:";
			//echo "<p>values: [".$value."]";
			//print_r($value);
			//echo "<hr/>";
			
			if (@$campo['selectLabel']!='')
				$selectLabel = $campo['selectLabel'];
			
			if (!is_array($campo['options']))
				$sel = $this->formGetSelectContent($campo['options'],$selectLabel,@$campo['where']);
			else
				$sel = $campo['options'];
				
				
			$return_sel = "";
			
			$return_sel .= "<select name='".$campo['name']."[]' style='width:".$size."' class='form-control ".@$campo['class']." select2' multiple='multiple' ".$req.">";
			
			//if (($primeiroBranco) && ($value == ''))
			//	$return_sel .= "<option value='' selected>&nbsp;</option>";
			
			$options_rel = array();
			
			if ($sel)
			{
				foreach ($sel as $v => $s)
				{
					//print_r($value);
					if (!is_array($campo['options']))
					{
						//relacionamentos que vem do BD de outra tabela
						if (is_array($value))
						{
							/*
							foreach($value as $rv)
							{
							
								if ($rv[$campo['idFilhos']] == $s['id'])
									$return_sel .= "<option value='".$s['id']."' selected>".$s['nome']."</option>";
								else
									$return_sel .= "<option value='".$s['id']."'>".$s['nome']."</option>";
							}
							*/
							//print_r($value);
							if (in_array($s['id'], $value))
							{
								$return_sel .= "<option value='".$s['id']."' selected>".$s[$selectLabel]."</option>";
							}
							else
								$return_sel .= "<option value='".$s['id']."'>".$s[$selectLabel]."</option>";
							
							//$return_sel
						}
						else
						{
							if ($value == $s['id'])
								$return_sel .= "<option value='".$s['id']."' selected>".$s[$selectLabel]."</option>";
							else
								$return_sel .= "<option value='".$s['id']."'>".$s[$selectLabel]."</option>";
						}
					}
					else
					{
						if (is_array($value))
						{
							if (in_array($s, $value))
								$return_sel .= "<option value='".$v."' selected>".$s."</option>";
							else
								$return_sel .= "<option value='".$v."'>".$s."</option>";
						}
						else
						{
							if ($value == $v)
								$return_sel .= "<option value='".$v."' selected>".$s."</option>";
							else
								$return_sel .= "<option value='".$v."'>".$s."</option>";
						}
					}
				
				}
			}
			$return_sel .= "</select>";
			
			return $return_sel;
		}
		
	
	}
	
	public function editCRUD($id,$post)
	{
		$sql = "UPDATE ".$this->form_dbtable." SET ";
		$contem_relacionamentos = false;
		$rel_inserts = array();
		$rel_delete = array();
		
		foreach ($post as $p => $v)
		{
			if (($p == 'password')||($p == 'senha'))
			{
				$e = new Encryption();
				$encoded = $e->encode($v);
				
				$sql .= $p."='".$encoded."',";
			}
			else
			{
				//if ((is_array($v)) || (@$v['type'] == 'selectRel'))
				if (is_array($v))
				{//se for um array, é um sinal que é de um campo multiplo, que precisa de uma tabela de relacionamento
					$contem_relacionamentos = true;
					
					$rel_delete[] = "DELETE FROM ".$v['tableRel']." WHERE ".$v['idPai']." = ".$id.";";
					
					if (sizeof($v['values']) > 0)
					{
						foreach ($v['values'] as $i)
						{
							//tentativa de fazer um diff
							//$rel_in[] = array('value'=>$i,'table'=>$v['tableRel'],'idFilhos'=>$v['idFilhos'],'idPai'=>$v['idPai']);
							$rel_inserts[] = "INSERT INTO ".$v['tableRel']." (".$v['idFilhos'].",".$v['idPai'].") VALUES ('".$i."',";
						}
					}
				}
				else
				{
					$sql .= $p."='".addslashes($v)."',";
				}
			}
		}
		$sql = rtrim($sql,",");
		$sql .=  " WHERE id=".$id.";";

		/*
		echo "<pre>";
		echo htmlentities($sql);
		echo "</pre>";
		*/
		//die();
		$this->bdconn->executa($sql);
		
		$this->crudError = "";
		
		if ($this->bdconn->error())
			$this->crudError .= "<p style='color:red'>ERRO NA QUERY: ".htmlentities($sql)." - Retorno mysql:".$this->bdconn->error()."</p>";
		else
		{
			if ($this->config->config['audit'] == true)
			{
				$this->addAudit($this->form_dbtable,$id,'edit',$sql);
			}
		}
		
		
		if ($contem_relacionamentos)
		{
			//deletar todos os relacionamentos primeiro
			//echo $rel_delete;
			//print_r($rel_inserts);
			foreach($rel_delete as $del)
			{
				$this->bdconn->executa($del);
				if ($this->bdconn->error())
					$this->crudError .= "<p style='color:red'>ERRO NA QUERY: ".htmlentities($del)." - Retorno mysql:".$this->bdconn->error()."</p>";
			}
			
			
			foreach ($rel_inserts as $ins)
			{
				$this->bdconn->executa($ins.$id.")");
				if ($this->bdconn->error())
					$this->crudError .= "<p style='color:red'>ERRO NA QUERY: ".htmlentities($ins.$id.")")." - Retorno mysql:".$this->bdconn->error()."</p>";
				
				if ($this->config->config['audit'] == true)
				{
					$this->addAudit($this->form_dbtable,$id,'edit',$ins.$id.")");
				}
			}
		}

	}
	
	public function addAudit($class,$id,$tipo,$query)
	{
		$idUsuario = Core::$session->getIdUsuario();
		$sql = "INSERT INTO lf_audit (idUsuario,class,idClass,tipo,query,dt) values (".$idUsuario.",'".$class."','".$id."','".$tipo."','".addslashes($query)."',NOW())";
		$this->bdconn->executa($sql);
		
		if ($this->bdconn->error())
			echo "<p style='color:red'>ERRO NA QUERY: ".htmlentities($sql)." - Retorno mysql:".$this->bdconn->error()."</p>";
	}
		
	public function logoutCRUD()
	{
		//$s = new Sessao();
		//$s->logout();
		
		Core::$session->logout();
	}
	
	public function loginCRUD($usuario,$senha)
	{
		$canLogIn = false;
	
		if ($this->config->config['login_tipo'] == 'bd')
		{
			$sql = "SELECT id FROM ".$this->config->config['login_bd_table']." WHERE ".$this->config->config['login_bd_usuario']." = '".$usuario."' AND  ".$this->config->config['login_bd_senha']." = '".md5($senha)."';";
			$res = $this->bdconn->select($sql);
			
			if ($res)
			{
				$canLogIn = true;
				
				$sql = "UPDATE ".$this->config->config['login_bd_table']." SET lastLogin = NOW() WHERE id = ".$res[0]['id'].";";
				$res2 = $this->bdconn->insert($sql);
				
				$idUsuarioSession = $res[0]['id'];
			}
		}
		else if ($this->config->config['login_tipo'] == 'ldap')
		{
			$ldap = new LDAP($usuario,$senha);
			
			if ($ldap->login())
			{
				//salva as infos no DB se nao existir
				$sql = "SELECT id,ativo FROM ".$this->config->config['login_bd_table']." WHERE ".$this->config->config['login_bd_usuario']." = '".$usuario."' AND tipoAuth='ldap';";
				$res = $this->bdconn->select($sql);
				
				if (!$res)
				{
					$sql = "INSERT INTO ".$this->config->config['login_bd_table']." (usuario,tipoAuth,ativo) VALUES ('".$usuario."','ldap',1);";
					$res2 = $this->bdconn->insert($sql);
					
					$canLogIn = true;
					
					$idUsuarioSession = $res2;
				}
				else
				{
					if ($res[0]['ativo'] == '1')
						$canLogIn = true;
						
					$sql = "UPDATE ".$this->config->config['login_bd_table']." SET lastLogin = NOW() WHERE id = ".$res[0]['id'].";";
					$res2 = $this->bdconn->insert($sql);
					
					$idUsuarioSession = $res[0]['id'];
				}
			}
		}
		else
		{
			die("ERRO: Sem tipo de login");
		}
		
		if ($canLogIn)
		{
			//$s = new Sessao();
			//$s->login($idUsuarioSession);
			
			Core::$session->login($idUsuarioSession);
		
		}
		else
		{
			echo "<div>";
				echo "<div class='avisoFun'>";
					echo "Login ou senha incorretos.";
				echo "</div>";
			echo "</div>";
		}

		
	}
	
		
	public function insertCRUD($post){
	
		$colunas = "";
		$valores = "";
		$contem_relacionamentos = false;
		$rel_inserts = array();
		//print_r($post);
		//die();
		
		
		foreach ($post as $p => $v)
		{
			//print_r($p);
			//echo "<hr/>";
			if (($p == 'password')||($p == 'senha'))
			{
				$e = new Encryption();
				$encoded = $e->encode($v);
				
				$colunas .= $p.",";
				$valores .= "'".$encoded."',";
			}
			else
			{
			
				//if ((is_array($v)) || (@$v['type'] == 'selectRel'))
				if (is_array($v))
				{
					//se for um array, é um sinal que é de um campo multiplo, que precisa de uma tabela de relacionamento
					
					
					if (sizeof($v['values']) > 0)
					{
						$contem_relacionamentos = true;
						
						foreach ($v['values'] as $i)
						{
							$rel_inserts[] = "INSERT INTO ".$v['tableRel']." (".$v['idFilhos'].",".$v['idPai'].") VALUES ('".$i."',";
						}
					}
					
				}
				else
				{
					$colunas .= $p.",";
					$valores .= "'".addslashes($v)."',";
				}
			}
			
		}	
		
		$colunas = rtrim($colunas,",");
		$valores = rtrim($valores,",");

		$sql = "INSERT INTO ".$this->form_dbtable." (".$colunas.") VALUES (".$valores.");";
		
		//echo $sql;
		//die();
		$this->crudError = "";
		
		$last_id = $this->bdconn->insert($sql);
		if ($this->bdconn->error())
			$this->crudError .= "<p style='color:red'>ERRO NA QUERY: ".htmlentities($sql)." - Retorno mysql:".$this->bdconn->error()."</p>";
		else
		{
			if ($this->config->config['audit'] == true)
			{
				$this->addAudit($this->form_dbtable,$last_id,'add',$sql);
			}
		}
			
		//pega o retorno do INSERT
		//$last_id = 0;
		
		//print_r($sql);
		//print_r($rel_inserts);
		//print_r($last_id);
		//die();
		
		if ($contem_relacionamentos)
		{
			foreach ($rel_inserts as $ins)
			{
				$this->bdconn->executa($ins.$last_id.")");
				if ($this->bdconn->error())
					$this->crudError .= "<p style='color:red'>ERRO NA QUERY: ".htmlentities($ins)." - Retorno mysql:".$this->bdconn->error()."</p>";
				else
				{
					if ($this->config->config['audit'] == true)
					{
						$this->addAudit($this->form_dbtable,$last_id,'add',$ins);
					}
				}
			}
		}
		
		return $last_id;
		
	}
	
	public function getColumnCRUDInfoMulti($col,$table,$where,$id)
	{
		$sql = "SELECT ".$col." FROM ".$table." WHERE ".$where." = ".$id.";";
		
		$res = $this->bdconn->select($sql);
		
		//echo $sql;
		//print_r($res);
		$values = array();
		
		if ($res)
		{
			//print_r($res);
			foreach($res as $r)
			{
				$values[] = $r[$col];
			}
			
		}
		return $values;
	}
	
	public function getColumnCRUDInfo($col,$table,$id,$relURL='')
	{
		//$path = $this->core->system_path;
		
		$sql = "SELECT ".$col." FROM ".$table." WHERE id = ".$id.";";
		
		if ($id != '')
		{
			$res = $this->bdconn->select($sql);
			if ($relURL != '')
				return "<a href='".$this->path."/".$relURL."/".$id."'>".$res[0][$col]."</a>";
			else
				return $res[0][$col];
		}
		else
			return '';
	}
	
	public function getCRUDInfo($table,$id,$col="*",$search='id')
	{
		$sql = "SELECT ".$col." FROM ".$table." WHERE ".$search." = ".$id.";";
		$res = $this->bdconn->select($sql);
		return $res[0];
	}

}

?>