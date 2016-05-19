# DLE-EasyGallery

![version](https://img.shields.io/badge/version-1.0.0-red.svg?style=flat-square "Version")
![DLE](https://img.shields.io/badge/DLE-10.x-green.svg?style=flat-square "DLE Version")
[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://github.com/dle-modules/DLE-EasyGallery/blob/master/LICENSE)

Модуль создания простых галлерей для DLE.

Умеет создавать галереи только из папок с картинками.

Основное назначение — реализация интерфейса для получения картинок из нужной галереи.


## Простой пример получения галереи по её ID
```js
var galleryId = 'XXX';
$.ajax({
        url: dle_root + 'engine/ajax/egal/get.php',
        dataType: 'json',
        data: {
            id: galleryId
        }
    })
    .done(function (data) {
        console.log(data);
    });
```


В комплекте с модулем есть готовый пример, с использованием плагина PhotoSwipe.

В конец `main.tpl`  пропишите `{include file="egal/egal_photoswipe.tpl"}`, а в том месте где нужно вывести кнопку показа галереи: `<span data-egal-id="XXX" class="btn">Показать фотки</span>`
