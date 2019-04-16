(function ($) {
  'use strict'

  $.extend(true, $.trumbowyg, {
    langs: {
      en: {
        bbImage: 'Add Image BBCode',
        bbImageAlt: 'Alt tag',
        bbImageCaption: 'Caption',
        bbImageUrl: 'Source url'
      },
      fr: {
        bbImage: 'Ajouter une image au format BBCode',
        bbImageAlt: 'Attribut alt',
        bbImageCaption: 'Sous-titre',
        bbImageUrl: 'Lien'
      }
    },
    plugins: {
      bbImage: {
        init: function (trumbowyg) {
          var btnDef = {
            ico: 'insert-image',
            fn: function () {
              trumbowyg.saveRange()
              trumbowyg.openModalInsert(
                trumbowyg.lang.bbImage,
                {
                  bbImageAlt: {
                    label: trumbowyg.lang.bbImageAlt,
                    required: true
                  },
                  bbImageCaption: {
                    label: trumbowyg.lang.bbImageCaption,
                    required: true
                  },
                  bbImageUrl: {
                    label: trumbowyg.lang.bbImageUrl,
                    required: true
                  }
                },
                function (v) {
                  trumbowyg.range.deleteContents()
                  trumbowyg.execCmd('insertText',
                    `[[image|${v.bbImageAlt}|${v.bbImageCaption}|${v.bbImageUrl}]]`
                  )
                  return true
                }
              )
            }
          }
          trumbowyg.addBtnDef('bbImage', btnDef)
        }
      }
    }
  })
})(jQuery)
