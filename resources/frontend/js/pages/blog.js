function openWindow (url, title, options = {}) {
  options = {url, title, width: 650, height: 430, ...options}

  const dualScreenLeft = window.screenLeft !== undefined
    ? window.screenLeft
    : window.screen.left
  const dualScreenTop = window.screenTop !== undefined
    ? window.screenTop
    : window.screen.top
  const width = window.innerWidth || document.documentElement.clientWidth ||
    window.screen.width
  const height = window.innerHeight || document.documentElement.clientHeight ||
    window.screen.height

  options.left = ((width / 2) - (options.width / 2)) + dualScreenLeft
  options.top = ((height / 2) - (options.height / 2)) + dualScreenTop

  const optionsStr = Object.keys(options).reduce((acc, key) => {
    acc.push(`${key}=${options[key]}`)
    return acc
  }, []).join(',')

  const newWindow = window.open(url, title, optionsStr)

  if (window.focus) {
    newWindow.focus()
  }

  return newWindow
}

$(document).ready(function () {
  $('.twitter-container').each(function (i, elm) {
    window.tweetLoader.load(function (err, twttr) {
      if (err) {
        console.error(err)
        return
      }
      twttr.widgets.createTweet(
        // tweet container elements have an id with a prefix of 'tweet-' > 6 letters
        elm.id.substring(6),
        elm,
        {
          omit_script: true,
          align: 'center',
          dnt: true,
          hide_thread: true
        }
      )
    })
  })

  $('#blog-share-buttons>a').each(function (i, elm) {
    $(elm).on('click', function (e) {
      e.preventDefault()
      var elmClass = e.target.getAttribute('class')
      var title = $('meta[name="twitter:title"]').attr('content')
      var url = $('meta[property="og:url"]').attr('content')
      if (elmClass.includes('facebook')) {
        openWindow('https://www.facebook.com/sharer/sharer.php?u=' +
          encodeURIComponent(url), '')
      } else if (elmClass.includes('twitter')) {
        var publisher = $('meta[name="twitter:site"]').attr('content')
        openWindow(
          'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title)
          + '&url=' + encodeURIComponent(url)
          + '&via=' + encodeURIComponent(publisher.substr(1)), ''
        )
      }
    })
  })
})
