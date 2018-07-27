<?php
/*
|--------------------------------------------------------------------------
| CLASSE PRINCIPAL DO SISTEMA CASO NENHUMA FOR CHAMADA
|--------------------------------------------------------------------------
|
| utilizado normalmente para aspaginas Home do site, se nao tiver nada do lado o /, cai nessa classe.
|
*/
$config['defaultClass'] = 'home';


/*
|--------------------------------------------------------------------------
| PASTA DO TEMPLATE PADRAO
|--------------------------------------------------------------------------
|
| utilizado normalmente para aspaginas Home do site, se nao tiver nada do lado o /, cai nessa classe.
|
*/
$config['defaultTemplate'] = 'carousel';


/*
|--------------------------------------------------------------------------
| PASTA DO SISTEMA SITE/PASTA
|--------------------------------------------------------------------------
|
| Caso o sistema rode em uma pasta do site principal, exemplo: site.com.br/pasta
| Para deixar no padrão é necessário deixar em /
|
*/
$config['systemFolder'] = '/twig-test';

/*
|--------------------------------------------------------------------------
| PROTOCOLO
|--------------------------------------------------------------------------
|
| http ou https
|
*/
$config['protocolo']   = 'http';

/*
|--------------------------------------------------------------------------
| AUDIT
|--------------------------------------------------------------------------
|
| Auditar objetos do BD, com alterações e inserções
|
| true ou false
|
*/
$config['audit']   = true;

/*
|--------------------------------------------------------------------------
| PERMISSOES
|--------------------------------------------------------------------------
|
| Define se o acesso tem permissoes por modulos(classes) ou somente o login 
| libera tudo.
|
| true ou false
|
*/
$config['allowAllClasses']   = false;

/*
|--------------------------------------------------------------------------
| PAGINACAO
|--------------------------------------------------------------------------
|
| Define quantas linhas vao ser apresentadas antes de começar a paginar
|
|
*/
$config['paginarMax'] = 10;

/*
|--------------------------------------------------------------------------
| SESSION TIMEOUT
|--------------------------------------------------------------------------
|
| Tempo para expirar a sessao do browser
|
|
*/
$config['session_timeout'] = 9600;

/*
|--------------------------------------------------------------------------
| TIPO DE LOGIN
|--------------------------------------------------------------------------
|
| ldap, bd ou no
|
*/
$config['login_tipo']          = 'ldap';

/*LOGIN via LDAP*/
$config['login_ldap_host']     = '';
$config['login_ldap_port']     = '';
$config['login_ldap_domain']   = '';
$config['login_ldap_dn']       = '';
$config['login_ldap_group']    = '';

/*LOGIN via BD ou salvar infos do LDAP para aplicar permissoes*/
$config['login_bd_table']      = 'lf_usuarios';
$config['login_bd_permissoes'] = 'lf_usuarios_permissoes';
$config['login_bd_rel_grupo']  = 'lf_usuarios_grupos';
$config['login_bd_usuario']    = 'usuario';
$config['login_bd_senha']      = 'senha';

/*
|--------------------------------------------------------------------------
| USUARIO BANCO DE DADOS
| sql ou mysql
|--------------------------------------------------------------------------
*/
$config['dbType'] = 'mysql';

/*
|--------------------------------------------------------------------------
| USUARIO BANCO DE DADOS
|--------------------------------------------------------------------------
*/
$config['dbUser'] = 'root';

/*
|--------------------------------------------------------------------------
| SENHA BANCO DE DADOS
|--------------------------------------------------------------------------
*/
$config['dbPass'] = 'pass';

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/
$config['dbDatabase'] = 'logfun';

/*
|--------------------------------------------------------------------------
| HOST / SERVIDOR DO BANCO DE DADOS
|--------------------------------------------------------------------------
*/
//$config['dbHost'] = 'N8S6\DESENV, 54803';
$config['dbHost'] = 'localhost';

/*
|--------------------------------------------------------------------------
| VERSAO DO SISTEMA
|--------------------------------------------------------------------------
|
| Utilizado para versionamento do site.
|
*/
$config['sysVersion'] = '2.1';

/*
|--------------------------------------------------------------------------
| NOME DO SISTEMA
|--------------------------------------------------------------------------
|
| Nome do sistema, normalmente usado para aparecer no header do site.
|
*/
$config['sysName'] = 'PHPFUN';

/*
|--------------------------------------------------------------------------
| TITULO DO SISTEMA
|--------------------------------------------------------------------------
|
| Vai nas tags <Title> do sistema
|
*/
$config['sysTitulo'] = 'PHPFUN';

/*
|--------------------------------------------------------------------------
| DESCRICAO DO SISTEMA
|--------------------------------------------------------------------------
|
| Vai nas tags <meta> do head
|
*/
$config['sysDescricao'] = 'Descricao PHPFUN';

/*
|--------------------------------------------------------------------------
| AUTOR DO SISTEMA
|--------------------------------------------------------------------------
|
| Vai nas tags <meta> do sistema
|
*/
$config['sysAutor'] = 'Rianne Schaidhauer';

/*
|--------------------------------------------------------------------------
| LINGUA DO SISTEMA
|--------------------------------------------------------------------------
|
| Vai nas tags <meta> do sistema
|
*/
$config['sysLang'] = 'pt-br';

/*
|--------------------------------------------------------------------------
| ICONE DO NAVEGADOR
|--------------------------------------------------------------------------
|
| Vai nas tags <meta> do sistema
|
*/
$config['favicon'] = 'assets/img/logfun_icon.png';


/*
|--------------------------------------------------------------------------
| MOSTRAR MENU LATERAL
|--------------------------------------------------------------------------
| true / false
|
*/
$config['menuLateral'] = true;

/*
|--------------------------------------------------------------------------
| RELOGIO DIGITAL
|--------------------------------------------------------------------------
| true / false
|
*/
$config['clock'] = true;



return $config;

?>