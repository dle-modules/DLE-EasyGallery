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
 * @global boolean $is_logged           Является ли посетитель авторизованным пользователем или гостем.
 * @global array   $member_id           Массив с информацией о авторизованном пользователе, включая всю его информацию из профиля.
 * @global object  $db                  Класс DLE для работы с базой данных.
 * @global object  $tpl                 Класс DLE для работы с шаблонами.
 * @global array   $cat_info            Информация обо всех категориях на сайте.
 * @global array   $config              Информация обо всех настройках скрипта.
 * @global array   $user_group          Информация о всех группах пользователей и их настройках.
 * @global integer $category_id         ID категории которую просматривает посетитель.
 * @global integer $_TIME               Содержит текущее время в UNIX формате с учетом настроек смещения в настройках скрипта.
 * @global array   $lang                Массив содержащий текст из языкового пакета.
 * @global boolean $smartphone_detected Если пользователь со смартфона - true.
 * @global string  $dle_module          Информация о просматриваемомразделе сайта, либо информацию переменной do из URL браузера.
 */


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);

if (!defined('DATALIFEENGINE') || !defined('LOGGED_IN')) {
	die('Hacking attempt!');
}

if (!$user_group[$member_id['user_group']]['admin_addnews']) {
	msg("error", $lang['index_denied'], $lang['index_denied']);
}

echoheader('<i class="icon-picture"></i> DLE-EasyGallery', 'Модуль создания простых галлерей');

include ENGINE_DIR . '/modules/egal/classes/SafeMySQL.php';
include ENGINE_DIR . '/modules/egal/admin/functions.php';

$SafeMySQL = SafeMySQL::getInstanse([
	'host'    => DBHOST,
	'user'    => DBUSER,
	'pass'    => DBPASS,
	'db'      => DBNAME,
	'charset' => COLLATE,
]);

$action = $_REQUEST['action'];

?>
<style>

	.egal-form-group {
		border-bottom: solid 1px #d5d5d5;
		padding-bottom: 15px;
		font-size: 14px;
	}

	.egal-form-group:last-child {
		border-bottom: 0;
		padding-bottom: 0;
		margin-bottom: 0;
	}

	.egal-form-group .control-label {
		font-size: 14px;
	}

	.egal-form-group .radio input[type="radio"] {
		margin-top: 3px;
	}

	.icheckbox_flat-aero + label,
	.iradio_flat-aero + label {
		font-size: 14px;
		cursor: pointer;
	}

	.egal-input-small {
		width: 60px;
	}

	.copycode {
		width: 100%;
		overflow: hidden;
	}

	tr.is-new td {
		background: #ccc !important;
	}

	.btn [class^="icon-"] {
		line-height: inherit;
	}

	input[type="text"], input[type="number"] {
		height: 28px;
	}

</style>

<?php if (!$action): ?>
	<script>var galElementData = '[]'</script>
	<div class="box">
		<div class="box-header">
			<div class="title">Список галлерей</div>
			<ul class="box-toolbar">
				<li class="toolbar-link">
					<a href="<?php echo $config['admin_path'] ?>?mod=egal&action=add"> <i class="icon-plus"></i>
						Добавить галерею
					</a>
				</li>
			</ul>
		</div>
		<div class="box-content">
			<table class="table table-normal table-hover">
				<thead>
				<tr>
					<td>Название</td>
					<td>Кол-во</td>
					<td class="news-list-tab">Описание</td>
					<td class="news-list-tab">Код для вставки</td>
					<td class="news-list-tab">Действия</td>
				</tr>
				</thead>

				<?php
				$galleryList = $SafeMySQL->getAll('SELECT * FROM ?n', PREFIX . '_egal');

				if (!count($galleryList)) {
					$galTr = '<tr>
							<td colspan="5">
								<div class="alert alert-info" style="margin-bottom: 0;">
									Вы пока не добавили ни одной галереи
								</div>
							</td>
						</tr>';
					echo $galTr;
				} else {
					foreach ($galleryList as $key => $galItem) {
						?>

						<tr>
							<td><?php echo $galItem['name']; ?></td>
							<td><?php echo $galItem['count']; ?></td>
							<td style="max-width: 300px;"><?php echo $galItem['description']; ?></td>
							<td>
								<input type="text" class="copycode" readonly value='<?php
								echo '<span data-egal-id="' . $galItem['hash'] . '"></span>';
								?>'>
							</td>
							<td class="text-right">
								<div class="btn-group">
									<a href="<?php echo $config['admin_path'] ?>?mod=egal&action=edit&id=<?php echo $galItem['id'] ?>"
									   class="btn btn-sm btn-default" title="Редактировать"><i class="icon-pencil"></i>
										Правка</a>
									<a href="<?php echo $config['admin_path'] ?>?mod=egal&action=delete&id=<?php echo $galItem['id'] ?>"
									   class="btn btn-sm btn-gold" title="Удалить"><i class="icon-trash"></i></a>
								</div>
							</td>
						</tr>

						<?php
					}
				}


				?>
			</table>
		</div>
	</div>
<?php else: ?>

	<?php if (($action == 'edit' && isset($_REQUEST['id'])) || $action == 'add' || $action == 'done' || $action == 'delete'): ?>
		<?php
		$galElement     = $SafeMySQL->getRow('SELECT * FROM ?n WHERE id=?i', PREFIX . '_egal', $_REQUEST['id']);
		$galElementData = '[]';

		if ($galElement['id'] > 0) {
			$galElement['config'] = json_decode($galElement['config'], true);
			$galElementData       = (!empty($galElement['data'])) ? $galElement['data'] : '[]';
			$galElement['data']   = json_decode($galElement['data'], true);

		} elseif (isset($_POST['galElement'])) {

			$galElement = $_POST['galElement'];
			if ($config['charset'] == 'windows-1251') {
				$postData   = convertArrayFromStupidEncoding($galElement['data']);
				$configData = convertArrayFromStupidEncoding($galElement['config']);
			} else {
				$postData   = $galElement['data'];
				$configData = $galElement['config'];
			}

			$galElementData = json_encode($postData);
			$_arDbIns       = [];
			if ($action == 'done') {
				$_arDbIns['name']        = $SafeMySQL->parse('?s', $_POST['galElement']['name']);
				$_arDbIns['count']       = count($postData);
				$_arDbIns['description'] = $SafeMySQL->parse('?s', $_POST['galElement']['description']);
				$_arDbIns['data']        = $SafeMySQL->parse('?s', json_encode($postData));
				$_arDbIns['config']      = $SafeMySQL->parse('?s', json_encode($configData));
				if (!isset($_POST['galElement']['id'])) {
					$_arDbIns['hash'] = $SafeMySQL->parse('?s', 'gal_' . crc32($_arDbIns['name'] . $_arDbIns['config'] . time()));
				}
				$arDbIns = [];
				foreach ($_arDbIns as $key => $value) {
					$arDbIns[] = $key . '=' . $value;
				}

				$dbIns = implode(', ', $arDbIns);

				if (!isset($_POST['galElement']['id'])) {
					$SafeMySQL->query('INSERT INTO ' . PREFIX . '_egal SET ?p', $dbIns);
				} else {
					$SafeMySQL->query('UPDATE ' . PREFIX . '_egal SET ?p WHERE id=?i', $dbIns, $galElement['id']);
				}


				clear_cache('egal_');

			}

		}


		?>
		<script>var galElementData = <?php echo $galElementData; ?></script>

		<?php if ($action == 'done'): ?>
			<div class="box">
				<div class="box-header">
					<div class="title">
						Галерея
						<?php if (!isset($_POST['galElement']['id'])): ?>
							добавлена
						<?php else: ?>
							изменена
						<?php endif ?>
					</div>
					<ul class="box-toolbar">
						<li class="toolbar-link">
							<a href="<?php echo $config['admin_path'] ?>?mod=egal"> <i class="icon-list"></i>
								Вернуться к списку
							</a>
						</li>
					</ul>
				</div>
				<div class="box-content">
					<div class="row box-section">
						<p class="alert alert-success">
							Галерея <b><?php /** @var array $_arDbIns */
								echo $_arDbIns['name']; ?></b> успешно
							<?php if (!isset($_POST['galElement']['id'])): ?>
								добавлена
							<?php else: ?>
								изменена
							<?php endif ?>
						</p>
						<?php if (!isset($_POST['galElement']['id'])): ?>
							<p>
								Код для вставки <br>
								<input type="text" class="copycode" readonly value='<?php
								echo '<span data-egal-id="' . str_replace("'", '', $_arDbIns['hash']) . '"></span>';
								?>'>
							</p>
						<?php endif ?>
						<a href="<?php echo $config['admin_path'] ?>?mod=egal" class="btn btn-default">Вернуться
							назад</a>
					</div>

				</div>
			</div>


		<?php elseif (($action == 'edit' && $galElement['id'] > 0) || $action == 'add'): ?>

			<form method="post" class="form-horizontal"
			      action="<?php echo $config['admin_path'] ?>?mod=egal&action=done">
				<div class="box">
					<div class="box-header">
						<div class="title">
							<?php if ($action == 'add'): ?>
								Добавление
							<?php else: ?>
								Редактирование
							<?php endif ?>
							галереи
						</div>
						<ul class="box-toolbar">
							<li class="toolbar-link">
								<a href="<?php echo $config['admin_path'] ?>?mod=egal"> <i class="icon-list"></i>
									Вернуться к списку
								</a>
							</li>
						</ul>
					</div>
					<div class="box-content">
						<div class="row box-section">
							<div class="form-group egal-form-group">
								<label class="control-label col-md-3">
									Название галереи
								</label>
								<div class="col-md-9">
									<input
										type="text"
										name="galElement[name]"
										value="<?php echo getVal($galElement, 'name', '') ?>"
										style="width: 100%; max-width: 500px;"
									>
								</div>
							</div>

							<div class="form-group egal-form-group">
								<label class="control-label col-md-3">
									Описание галереи
								</label>
								<div class="col-md-9">
									<textarea
										name="galElement[description]"
										rows="10"
										style="width: 100%; max-width: 500px;"
									><?php echo getVal($galElement, 'description', '') ?></textarea>
								</div>
							</div>

							<div class="form-group egal-form-group">
								<label class="control-label col-md-3">
									Путь до папки с картинками
									<span class="help-button" data-rel="popover" data-trigger="hover"
									      data-placement="right"
									      data-content="Укажите путь от корня сайта до папки с картинками">?</span>
								</label>
								<div class="col-md-9">
									<input
										type="text"
										name="galElement[config][path]"
										value="<?php echo getVal($galElement, 'config.path', '') ?>"
										placeholder="/path/to/folder/"
										style="width: 100%; max-width: 500px;"
									>
								</div>
							</div>

							<div class="form-group egal-form-group">
								<label class="control-label col-md-3">
									Мин. ширина картинки
									<span class="help-button" data-rel="popover" data-trigger="hover"
									      data-placement="right"
									      data-content="Картинки меньшей ширины исключаются">?</span>
								</label>
								<div class="col-md-9">
									<input type="number" step="10" min="0" class="egal-input-small"
									       name="galElement[config][minWidth]"
									       value="<?php echo getVal($galElement, 'config.minWidth', 300) ?>">
								</div>
							</div>
							<div class="form-group egal-form-group">
								<label class="control-label col-md-3">
									Мин. высота картинки
									<span class="help-button" data-rel="popover" data-trigger="hover"
									      data-placement="right"
									      data-content="Картинки меньшей высоты исключаются">?</span>
								</label>
								<div class="col-md-9">
									<input type="number" step="10" min="0" class="egal-input-small"
									       name="galElement[config][minHeight]"
									       value="<?php echo getVal($galElement, 'config.minHeight', 300) ?>">
								</div>
							</div>
							<?php if ($action == 'add'): ?>
								<div class="form-group egal-form-group">
									<label class="control-label col-md-3">
										Получить список картинок
										<span class="help-button" data-rel="popover" data-trigger="hover"
										      data-placement="right"
										      data-content="Обновлять список картинок имеет смысл, если изенился путь или минимальные размеры картинок">?</span>
									</label>
									<div class="col-md-9">
										<span class="btn btn-sm btn-green btn-get-data">
											Получить данные <span class="loading hide"><i
													class="icon-spinner icon-spin"></i></span>
										</span>
									</div>
								</div>
							<?php else: ?>
								<div class="form-group egal-form-group">
									<label class="control-label col-md-3">
										Cписок картинок
										<span class="help-button" data-rel="popover" data-trigger="hover"
										      data-placement="right"
										      data-content="Обновлять список картинок имеет смысл, если изенился путь или минимальные размеры картинок">?</span>
									</label>
									<div class="col-md-9">
										<span class="btn btn-sm btn-green btn-get-data">
											Обновить данные <span class="loading hide"><i
													class="icon-spinner icon-spin"></i></span>
										</span>
									</div>
								</div>
							<?php endif ?>

							<table class="table table-bordered">
								<thead>
								<tr>
									<td colspan="2">Картинка</td>
									<td>Заголовок</td>
									<td>Описание</td>
								</tr>
								</thead>
								<tbody id="photo-tbody">

								</tbody>
							</table>

							<div class="form-group egal-form-group">
								<label class="control-label col-md-3">&nbsp;</label>
								<div class="col-md-9">
									<input type="hidden" name="mod" value="egal">
									<input type="hidden" name="user_hash" value="<?php echo $dle_login_hash ?>							
									">
									<?php if (($action == 'edit' && $galElement['id'] > 0)): ?>
										<input type="hidden" name="galElement[id]"
										       value="<?php echo $galElement['id'] ?>">
									<?php endif ?>
									<?php
									$btnName = ($action == 'edit') ? 'Сохранить' : 'Добавить';
									?>
									<input type="submit" name="add" class="btn btn-lg btn-green"
									       value="<?php echo $btnName; ?>">
								</div>
							</div>


						</div>
					</div>
				</div>
			</form>

		<?php elseif ($action == 'delete' && $galElement['id'] > 0): ?>
			<?php if ($_REQUEST['confirm'] == 'yes'): ?>
				<?php
				$SafeMySQL->query('DELETE FROM ?n WHERE id=?i', PREFIX . '_egal', $galElement['id']);
				?>
				<div class="box">
					<div class="box-header">
						<div class="title">Галерея удалена</div>
						<ul class="box-toolbar">
							<li class="toolbar-link">
								<a href="<?php echo $config['admin_path'] ?>?mod=egal"> <i class="icon-list"></i>
									Вернуться к списку
								</a>
							</li>
						</ul>
					</div>
					<div class="box-content">
						<div class="row box-section">
							<p class="alert alert-success">
								Галерея успешно удалена.
							</p>
							<a href="<?php echo $config['admin_path'] ?>?mod=egal" class="btn btn-default">Отлично,
								вернёмся к списку</a>
						</div>

					</div>
				</div>
			<?php else: ?>
				<div class="box">
					<div class="box-header">
						<div class="title">Подтвердите действие</div>
						<ul class="box-toolbar">
							<li class="toolbar-link">
								<a href="<?php echo $config['admin_path'] ?>?mod=egal"> <i class="icon-list"></i>
									Вернуться к списку
								</a>
							</li>
						</ul>
					</div>
					<div class="box-content">
						<div class="row box-section">
							<p class="alert">
								Действиетльно удалить галерею?
							</p>
							<a href="<?php echo $config['admin_path'] ?>?mod=egal&action=delete&id=<?php echo $_REQUEST['id']; ?>&confirm=yes"
							   class="btn btn-red">Да, удалить</a>
							<a href="<?php echo $config['admin_path'] ?>?mod=egal" class="btn btn-default">Нет</a>
						</div>

					</div>
				</div>
			<?php endif ?>

		<?php else: ?>
			<div class="box">
				<div class="box-header">
					<div class="title">Ошибка</div>
					<ul class="box-toolbar">
						<li class="toolbar-link">
							<a href="<?php echo $config['admin_path'] ?>?mod=egal"> <i class="icon-list"></i>
								Вернуться к списку
							</a>
						</li>
					</ul>
				</div>
				<div class="box-content">
					<div class="row box-section">
						<p class="alert alert-danger">
							Галерея не найдена
						</p>
						<a href="<?php echo $config['admin_path'] ?>?mod=egal" class="btn btn-default">Вернуться
							назад</a>
					</div>

				</div>
			</div>
		<?php endif ?>
	<?php endif ?>


<?php endif ?>

<template id="photo-list">
	{% for(var i in this.items) { %}
	<tr class="{% this.items[i].isNew %}">
		<td width="80">
			<a href="{% this.items[i].url %}" target="blank">
				<img src="{% this.items[i].url %}" alt="{% this.items[i].name %}" style="width: 60px;">
			</a>
		</td>
		<td style="width: 320px;">
			<b>{% this.items[i].name %}</b> <span class="btn btn-sm btn-red pull-right btn-remove-gal-item"
			                                      title="Удалить запись из списка"><i class="icon-trash"></i></span>
			<br><span class="text-muted">({% this.items[i].width %}x{% this.items[i].height %})</span>

			<input type="hidden" name="galElement[data][{% i %}][name]" value="{% this.items[i].name %}">
			<input type="hidden" name="galElement[data][{% i %}][url]" value="{% this.items[i].url %}">
			<input type="hidden" name="galElement[data][{% i %}][height]" value="{% this.items[i].height %}">
			<input type="hidden" name="galElement[data][{% i %}][width]" value="{% this.items[i].width %}">

		</td>
		<td>
			<input style="width: 100%;" type="text" name="galElement[data][{% i %}][title]"
			       value="{% this.items[i].title %}">
		</td>
		<td>
			<textarea style="width: 100%;" name="galElement[data][{% i %}][description]" rows="3">{% this.items[i].description %}</textarea>
		</td>
	</tr>
	{% } %}
</template>


<script>
	/**
	 * Простой js-шаблонизатор
	 *
	 * Установка шаблона:
	 *  <template id="tpl">
	 *      <ul>
	 *          {% for(var i in this.items) { %}
	 *              <li>
	 *                  <b>{% this.items[i].name %}</b>
	 *              </li>
	 *          {% } %}
	 *      </ul>
	 *  </template>
	 * Использование:
	 * var template = document.getElementById('tpl').innerHTML;
	 * var items = {0:{'name': 'one'}, 1: {'name': 'two'}};
	 * var show = (jsTemplater(template, {
					items: items
				}));
	 * console.log(show);
	 */

	function jsTemplater(html, options) {
		'use strict';
		var re = /\{%(.+?)%}/g,
			reExp = /(^( )?(var|if|for|else|switch|case|break|{|}|;))(.*)?/g,
			code = 'with(obj) { var r=[];\n',
			cursor = 0,
			result,
			match;
		var add = function (line, js) {
			js ? (code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
				(code += line !== '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
			return add;
		};
		while (match = re.exec(html)) {
			add(html.slice(cursor, match.index))(match[1], true);
			cursor = match.index + match[0].length;
		}
		add(html.substr(cursor, html.length - cursor));
		code = (code + 'return r.join(""); }').replace(/[\r\t\n]/g, '');
		try {
			result = new Function('obj', code).apply(options, [options]);
		}
		catch (err) {
			console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n");
		}
		return result;
	}

	var template = document.getElementById('photo-list').innerHTML;

	$(document)
		.on('focus', '.copycode', function (event) {
			event.preventDefault();
			$(this).select();
		})
		.on('click', '.btn-get-data', function () {
			var data = {
				path: $('[name="galElement[config][path]"]').val() || '',
				minWidth: $('[name="galElement[config][minWidth]"]').val() || '',
				minHeight: $('[name="galElement[config][minHeight]"]').val() || '',
				user_hash: '<?php echo $dle_login_hash; ?>'
			};

			var $this = $(this),
				$loader = $this.find('.loading');

			$loader.removeClass('hide');

			$.ajax({
					url: 'engine/ajax/egal/admin.php',
					dataType: 'json',
					data: data
				})
				.done(function (data) {
					var newData = [];

					$.each(data, function (index, val) {
						newData[index] = val;
						newData[index].isNew = 'warning';

						$.each(galElementData, function (i, v) {
							if (val.name == v.name) {
								newData[index] = v;
							}
						});
					});

					var show = (jsTemplater(template, {
						items: newData
					}));

					if (newData.length) {
						$('#photo-tbody').html(show);
					}


				})
				.fail(function () {
					console.log('error');
				})
				.always(function () {
					$loader.addClass('hide');
				});

		})
		.on('click', '.btn-remove-gal-item', function () {
			$(this).closest('tr').remove();
		});

	jQuery(document).ready(function ($) {
		if (galElementData) {
			var show = (jsTemplater(template, {
				items: galElementData
			}));
			// if (galElementData.length) {
			$('#photo-tbody').html(show);
			// };
		}
	});
</script>
