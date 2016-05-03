<?php
/*
=============================================================================
DLE-EasyGallery
=============================================================================
Автор:   ПафНутиЙ
URL:     http://pafnuty.name/
twitter: https://twitter.com/pafnuty_name
google+: http://gplus.to/pafnuty
email:   pafnuty10@gmail.com
=============================================================================
 */

/**
 * @global boolean $is_logged Является ли посетитель авторизованным пользователем или гостем.
 * @global array   $member_id Массив с информацией о авторизованном пользователе, включая всю его информацию из профиля.
 * @global object  $db        Класс DLE для работы с базой данных.
 */

@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -16));
define('ENGINE_DIR', ROOT_DIR . '/engine');

include ENGINE_DIR . '/data/config.php';

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/inc/include/functions.inc.php';

dle_session();

require_once ENGINE_DIR . '/modules/sitelogin.php';


if (($member_id['user_group'] != 1)) {
	die ("error");
}

if ($_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash) {
	die ("error");
}

// Конфигурация модуля.
$cfg = [

	// Путь к папке с каринками, относительно корня сайта
	'imageFolder' => $_GET['path'],

	// Минимальная ширина картинки 
	'minWidth'    => $_GET['minWidth'],

	// Минимальная высота картинки 
	'minHeight'   => $_GET['minHeight'],
];


// Получаем картинки из папки
$arImages = glob(ROOT_DIR . $cfg['imageFolder'] . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);

// Сюда будем складывать нужные картинки
$arResult = [];

// Пробегаем по массиву с полученными картинками
foreach ($arImages as $key => $image) {
	// Определяем  размеры картинки
	$imgSize = getimagesize($image);

	// Складываем в массив только большие картинки
	if ($imgSize[0] >= $cfg['minWidth'] && $imgSize[1] >= $cfg['minHeight']) {
		$imgName                  = basename($image);
		$arResult[$key]['name']   = $imgName;
		$arResult[$key]['url']    = $cfg['imageFolder'] . $imgName;
		$arResult[$key]['width']  = $imgSize[0];
		$arResult[$key]['height'] = $imgSize[1];
	}
}
$showFoto = json_encode($arResult);

header('Content-Type: application/json');

echo $showFoto;