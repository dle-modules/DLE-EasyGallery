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
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);

if (!defined('DATALIFEENGINE') || !defined('LOGGED_IN')) {
	die('Hacking attempt!');
}

/**
 * @param $arr
 * @param $key
 *
 * @return mixed
 */
function findKey($arr, $key) {
	return $arr[$key];
}


/**
 * Get the val.
 *
 * @author     Павел Белоусов
 *
 * @param      array   $arr      Массив, в котором проверяем наличие данных
 * @param      string  $data     Строка, разделенная точками (а-ля объект :))
 * @param      boolean $fallback Что выводить, если ничего не найдено
 *
 * @return     boolean  Val.
 */
function getVal($arr, $data, $fallback = false) {

	$keys = explode('.', $data);
	// $arr  = $arr || $_POST;

	if (isset($arr[$keys[0]])) {
		foreach ($keys as $key) {
			$arr = findKey($arr, $key);
		}
		// echo "<pre class='dle-pre'>"; print_r($arr); echo "</pre>";
		if (!$arr) {
			return $fallback;
		}

		return $arr;

	} else {
		return $fallback;
	}
}

/**
 * Конвертируем для подготовки нормальных json-данных в 1251
 *
 * @author     Павел Белоусов
 *
 * @param      array  $var  Массив с данными
 * @param      string $from С какой кодировки конвертируем
 * @param      string $to   В какую кодировку конвертируем
 *
 * @return     mixed   Массив с сконвертированными данными
 */
function convertArrayFromStupidEncoding($var, $from = 'WINDOWS-1251', $to = 'UTF-8') {
	if (is_array($var)) {
		$new = [];
		foreach ($var as $key => $val) {
			$new[convertArrayFromStupidEncoding($key, $from, $to)] = convertArrayFromStupidEncoding($val, $from, $to);
		}
		$var = $new;
	} else {
		if (is_string($var)) {
			if (function_exists('mb_convert_encoding')) {

				$var = mb_convert_encoding($var, $to, $from);

			} elseif (function_exists('iconv')) {

				$var = iconv($from, $to . '//IGNORE', $var);

			} else {

				$var = "The library iconv AND mbstring is not supported by your server";
			}
		}
	}

	return $var;
}