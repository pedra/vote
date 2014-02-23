<?php

//Router (todo: getController)
function router(){
	//Routers
	$router['*'] = 'Index'; 
	$router['admin'] = 'Admin';
	//$router['teste'] = 'Main';

	$req = REQST == '' ? '*' : REQST;
	if(array_key_exists($req, $router)) return $router[$req];
	else exit(getHtml('404', true));
}

//Apaga os dados de votação e votantes ($all => ip dos votantes)
function clearIni($file, $dt, $all = false){
	//Salvando um backup antes
	setIni(BACKUP.date('YmdHis_').basename($file), $dt);

	//Calendário 
	$d = ['domingo','segunda-feira','terça-feira','quarta-feira','quinta-feira','sexta-feira','sábado'];
	$h = [14,15,16,17,18,19,20,21,22,23];
	foreach($h as $vh){
		foreach($d as $vd) {
			$dt['votos'][$vh][$vd] = 0;
		}
	}
	$dt['votos']['total'] = 0;

	if($all) $dt['eleitores'] = array('ip'=>array('0.0.0.0'=>'01/01/2001 às 00:00:00'));

	//salvando novos dados
	setIni($file, $dt);
	return $dt;
}

//Recupera e salva arquivos INI (respectivamente get/set)
function getIni($file){ return parse_ini_file($file, true);}
function setIni($file, $data){
    $o = '';
    foreach($data as $k => $v){
        $o .= '[' . $k . "]\r\n";
        //segundo nó
        if(is_array($v)){
            foreach($v as $_k => $_v){
                //terceiro nó
                if(is_array($_v)){
                    foreach($_v as $__k => $__v){
                        if(is_array($__v)) $__v = print_r($__v, true);
                        $o .= "\t" . $_k . '[' . $__k . '] = ' . (is_numeric($__v) ? $__v : '"' . $__v . '"') . "\r\n";
                    }
                } else $o .= "\t" . $_k . ' = ' . (is_numeric($_v) ? $_v : '"' . $_v . '"') . "\r\n";
            }
        }
    }
    return file_put_contents($file, $o);
}


//Gera uma tabela com resultados e celulas/botões de votação
function table(array $dt){
	//Pegando o maior registro
	$cont = 0;
	$reg = '';

	foreach ($dt['votos'] as $h => $d) {
		if($h == 'total') continue;
		foreach ($d as $k => $v) {
			if($v > $cont) {
				$cont = $v;
				$reg = $h.'_'.$k;
			}
		}
	}

	$ret = '<table>
			<tr>
				<th>Hora</th>
				<th>Dom</th>
				<th>Seg</th>
				<th>Ter</th>
				<th>Qua</th>
				<th>Qui</th>
				<th>Sex</th>
				<th>Sab</th>
			</tr>';
	$o = '';
	$total = 0;
	foreach ($dt['votos'] as $h => $d) {
		if($h == 'total'){
			$total = $d;
			continue;
		}
		$o .= '<td>'.$h.':00</td>';
		$cont = 0;
		foreach($d as $k=>$v){ 
			$cont += $v;
			$o .= (VOTOU) ? '
				<td class="'.(($v == 0)?'red':'green').(($reg == $h.'_'.$k)?' first':'').'">
					<button type="submit" title="'.ucfirst($k).' às '.$h.':00">'.$v.'</button>
				</td>':'
				<td class="'.(($v == 0)?'red':'green').(($reg == $h.'_'.$k)?' first':'').'">
					<form method="post">
						<input type="hidden" name="somar" value="'.$k.'_'.$h.'">
						<button type="submit" title="'.ucfirst($k).' às '.$h.':00">'.$v.'</button>
					</form>
				</td>';}

		$ret .= '<tr class="'.(($cont == 0)?'trnull':'trok').'">'.$o.'</tr>';
		$o = '';
	}
	$ret .= '<p class="total">Total de <b>'.$total.'</b> pessoas votaram até '.date('d/m/Y').' às '.date('H:i:s').'</p>';
	return $ret.'</table>';
}

//Login de Administrador
function login(array $dt){
	if(isset($_GET['vote']) && $_GET['vote'] == 'admin'){
		//se já está logado retorna 'admin'
		if(isset($_SESSION['vote']['user']) && $_SESSION['vote']['user'] == 'admin') return 'admin';
		//checa o fastlogin (senha MD5)
		if(isset($_POST['fastlog']) && in_array(md5($_POST['fastlog']), $dt['users'])){
			return $_SESSION['vote']['user'] = 'admin';
		} else {
			$_SESSION['vote']['user'] = 'guest';
			exit(getHtml('login'));
		}
	} else return 'guest';
}

//Seta o usuário como 'guest'
function logoff(){
	$_SESSION['vote']['user'] = 'guest';
	go();
}


//Retorna a condição de logado (true/false)
function loged(){
	return (isset($_SESSION['vote']['user']) 
				&& $_SESSION['vote']['user'] == 'admin')
					? true : false;
}

//Mostra uma view
function getHtml($view, $php = false){	
	if(file_exists(ROOT.'html/'.$view.'.html'))
		$out = file_get_contents(ROOT.'html/'.$view.'.html');
	if($php){
		ob_start();
		eval('?>'.$out);
		$out = ob_get_clean();
	}
	echo $out;
}

/** DEBUG PRINT
 * mostra um print na tela (aceita um mixed como valor)
 *
 * ex.: p(mixed, [optional false/true]); --> mostra o array na tela html/texto      
 * 'mixed' pode ser um numero, string, array, object, etc.
 * se o segundo argumento for true (default) print & exit.
 */

function p($val, $ex = true) {
    $val = '<pre>' . print_r($val, true) . '</pre>';
    if ($ex)
        exit($val);
    echo $val;
}


//Go to URL
function go($uri = '', $metodo = '', $cod = 302) {
    if (strpos($uri, 'http://') === false || strpos($uri, 'https://') === false)
        $uri = URL . $uri; //se tiver 'http' na uri então será externo.
    if (strtolower($metodo) == 'refresh') {
        header('Refresh:0;url=' . $uri);
    } else {
        header('Location: ' . $uri, TRUE, $cod);
    }
    exit;
}

function debug(){
	echo 'SESSION:';
	p($_SESSION, false);
	echo '<br>POST:';
	p($_POST, false);
	echo '<br>GET:';
	p($_GET, false);
	echo '<br>USER: '.USER;
	echo '<br>PHP: '.PHP;
	echo '<br>CONFIG: '.CONFIG;
	echo '<br>LIB: '.LIB;
	echo '<br>ROOT: '.ROOT;

}