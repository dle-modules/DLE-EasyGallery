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

// Конфигурация модуля.
$cfg = [

	// ID Галлереи
	'galId'       => (isset($_GET['id']) && $_GET['id'] != '') ? trim($_GET['id']) : '',

	// Префикс кеша
	'cachePrefix' => 'egal_',
];


@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -16));
define('ENGINE_DIR', ROOT_DIR . '/engine');

/** @var array $config */
date_default_timezone_set($config['date_adjust']);

include ENGINE_DIR . '/data/config.php';
require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';

dle_session();


//################# Определение групп пользователей
$user_group = get_vars("usergroup");

if (!$user_group) {
	$user_group = [];

	$db->query("SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC");

	while ($row = $db->get_row()) {

		$user_group[$row['id']] = [];

		foreach ($row as $key => $value) {
			$user_group[$row['id']][$key] = stripslashes($value);
		}

	}
	set_vars("usergroup", $user_group);
	$db->free();
}

//####################################################################################################################
//                    Определение забаненных пользователей и IP
//####################################################################################################################
$banned_info = get_vars("banned");

if (!is_array($banned_info)) {
	$banned_info = [];

	$db->query("SELECT * FROM " . USERPREFIX . "_banned");
	while ($row = $db->get_row()) {

		if ($row['users_id']) {

			$banned_info['users_id'][$row['users_id']] = [
				'users_id' => $row['users_id'],
				'descr'    => stripslashes($row['descr']),
				'date'     => $row['date'],
			];

		} else {

			if (count(explode(".", $row['ip'])) == 4) {
				$banned_info['ip'][$row['ip']] = [
					'ip'    => $row['ip'],
					'descr' => stripslashes($row['descr']),
					'date'  => $row['date'],
				];
			} elseif (strpos($row['ip'], "@") !== false) {
				$banned_info['email'][$row['ip']] = [
					'email' => $row['ip'],
					'descr' => stripslashes($row['descr']),
					'date'  => $row['date'],
				];
			} else {
				$banned_info['name'][$row['ip']] = [
					'name'  => $row['ip'],
					'descr' => stripslashes($row['descr']),
					'date'  => $row['date'],
				];
			}

		}

	}
	set_vars("banned", $banned_info);
	$db->free();
}

if (check_ip($banned_info['ip'])) {
	die('error');
}


// Основной код модуля


/**
 * Конвертируем для подготовки нормальных json-данных в 1251
 *
 * @author     Павел Белоусов
 *
 * @param      array  $var  Массив с данными
 * @param      string $from С какой кодировки конвертируем
 * @param      string $to   В какую кодировку конвертируем
 *
 * @return     array   Массив с сконвертированными данными
 */
function convertEgalData($var, $from = 'WINDOWS-1251', $to = 'UTF-8') {
	if (function_exists('mb_convert_encoding')) {

		$var = mb_convert_encoding($var, $to, $from);

	} elseif (function_exists('iconv')) {

		$var = iconv($from, $to . '//IGNORE', $var);

	} else {

		$var = "The library iconv AND mbstring is not supported by your server";
	}

	return $var;
}


$cacheName = md5(implode('_', $cfg));
$showEgal  = false;
$showEgal  = dle_cache($cfg['cachePrefix'], $cacheName . $config['skin']);


if (!$showEgal) {
	$galHash = $db->safesql(trim($cfg['galId']));
	// Получаем данные
	$galItem = $db->super_query('SELECT id, name, count, description, data FROM ' . PREFIX . '_egal WHERE hash = \'' . $galHash . '\'');

	if ($galItem['id'] > 0) {

		if ($config['charset'] == 'windows-1251') {
			$galItem['name']        = convertEgalData($galItem['name']);
			$galItem['description'] = convertEgalData($galItem['description']);
		}
		$arResult['name']        = $galItem['name'];
		$arResult['count']       = $galItem['count'];
		$arResult['description'] = $galItem['description'];
		$arResult['data']        = json_decode($galItem['data'], true);

		$showEgal = json_encode($arResult);

		unset($arResult);
		unset($galItem);

		create_cache($cfg['cachePrefix'], $showEgal, $cacheName . $config['skin']);
	}

}


header('Content-Type: application/json');

echo $showEgal;