(function ($) {
  'use strict'

  // Adds the language variables
  $.extend(true, $.trumbowyg, {
    langs: {
      en: {
        bbCode: 'BBCodes',
        image: 'Image',
        youtube: 'Youtube video',
        link: 'Link',
        tweet: 'Tweet',
        code: 'Code'
      },
      fr: {
        bbCode: 'BBCodes',
        image: 'image',
        youtube: 'Video youtube',
        link: 'Lien',
        tweet: 'Tweet',
        code: 'Code'
      }
    }
  })

  // Adds the extra button definition
  $.extend(true, $.trumbowyg, {
    plugins: {
      bbCode: {
        init: function (trumbowyg) {
          trumbowyg.addBtnDef('bbCode', {
            dropdown: bbCodeSelector(trumbowyg),
            hasIcon: false,
            text: trumbowyg.lang.bbCode
          })
        }
      }
    }
  })

  // Creates the template-selector dropdown.
  function bbCodeSelector (trumbowyg) {
    var templates = []
    addBtn(trumbowyg, `[[image|alt|caption|url]]`, trumbowyg.lang.image)
    addBtn(trumbowyg, `[[link|text|url]]`, trumbowyg.lang.link)
    addBtn(trumbowyg, `[[tweet|url]]`, trumbowyg.lang.tweet)
    addBtn(trumbowyg, `[[youtube|url]]`, trumbowyg.lang.youtube)
    addBtn(trumbowyg, '```language|code```', trumbowyg.lang.code)

    templates.push(trumbowyg.lang.image)
    templates.push(trumbowyg.lang.link)
    templates.push(trumbowyg.lang.tweet)
    templates.push(trumbowyg.lang.youtube)
    templates.push(trumbowyg.lang.code)

    return templates
  }

  function addBtn (trumbowyg, template, title) {
    trumbowyg.addBtnDef(
      title,
      {
        fn: function () {
          trumbowyg.execCmd('insertText',
            template
          )
        },
        hasIcon: false,
        title: title
      }
    )
    return trumbowyg
  }
})(jQuery)
