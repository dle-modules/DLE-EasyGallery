/**
 * =============================================================================
 * DLE-EasyGallery
 * =============================================================================
 * Автор:   ПафНутиЙ 
 * URL:     http://pafnuty.name/ 
 * twitter: https://twitter.com/pafnuty_name
 * =============================================================================
 */

/* global $ */
/* global dle_root */
/* global console */
/* global PhotoSwipe */
/* global PhotoSwipeUI_Default */

(function () {
	'use strict';

	var galleryObjCahe = {};

	$(document)
		.on('click', '[data-egal-id]', function () {

			var $this = $(this),
				galleryId = $this.data('egalId'),
				galleryItems = [];

			if (galleryObjCahe[galleryId]) {
				openPhotoSwipe(galleryObjCahe[galleryId]);
			}
			else {
				$.ajax({
						url: dle_root + 'engine/ajax/egal/get.php',
						dataType: 'json',
						data: {
							id: galleryId
						},
						baforeSend: function () {
							$this.addClass('loaing');
						}
					})
					.done(function (galleryData) {
						if (galleryData.data) {
							$.each(galleryData.data, function (i, val) {
								var item = {
									src: val.url,
									w: val.width,
									h: val.height,
									title: val.title,
									alt: val.description
								};
								galleryItems.push(item);
							});

							galleryObjCahe[galleryId] = galleryItems;

							openPhotoSwipe(galleryItems);
						}
					})
					.fail(function () {
						console.error('error loading gallery with ID: ', galleryId + '');
					})
					.always(function () {
						$this.removeClass('loaing');
					});
			}

		});


	$(document).ready(function ($) {

		$('body').append('<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="Закрыть (Esc)"></button><button class="pswp__button pswp__button--share" title="Поделиться"></button><button class="pswp__button pswp__button--fs" title="Полноэкранный режим"></button><button class="pswp__button pswp__button--zoom" title="Увеличение/уменьшение"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="Пред. картинка (стрелка влево)"></button><button class="pswp__button pswp__button--arrow--right" title="След. картинка (стрелка вправо)"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>');

	});

	function openPhotoSwipe(items) {

		var pswpElement = document.querySelectorAll('.pswp')[0];

		var options = {
			shareEl: false
		};

		var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);

		gallery.init();
	}
})();