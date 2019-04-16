(function ($) {
  'use strict'

  // My plugin default options
  var defaultOptions = {
    vm: null
  }

  // If my plugin is a button
  function buildButtonDef (trumbowyg) {
    return {
      ico: 'insert-image',
      fn: function () {
        console.log(trumbowyg.o.plugins.image.vm)
        trumbowyg.execCmd('insertHTML', '<p>this has been inserted</p>')
      }
    }
  }

  $.extend(true, $.trumbowyg, {
    // Add some translations
    langs: {
      en: {
      }
    },
    // Add our plugin to Trumbowyg registred plugins
    plugins: {
      myplugin: {
        init: function (trumbowyg) {
          // Fill current Trumbowyg instance with my plugin default options
          trumbowyg.o.plugins.myplugin = $.extend(true, {},
            defaultOptions,
            trumbowyg.o.plugins.myplugin || {}
          )

          // If my plugin is a button
          trumbowyg.addBtnDef('image', buildButtonDef(trumbowyg))
        },
        tagHandler: function (element, trumbowyg) {
          return []
        },
        destroy: function () {
        }
      }
    }
  })
})(jQuery)