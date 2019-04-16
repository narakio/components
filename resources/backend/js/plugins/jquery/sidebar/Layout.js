/* Layout()
 * ========
 * Implements AdminLTE layout.
 * Fixes the layout height in case min-height fails.
 *
 * @usage activated automatically upon window load.
 *        Configure any options by passing data-option="value"
 *        to the body tag.
 */
+function ($) {
  'use strict'

  var DataKey = 'lte.layout'

  var Default = {
    resetHeight: true
  }

  var Selector = {
    wrapper: '.wrapper',
    contentWrapper: '#content-wrapper',
    content: '#app',
    mainHeader: '.main-header',
    sidebar: '.sidebar',
    sidebarMenu: '.sidebar-menu',
    logo: '.main-header .logo'
  }

  var ClassName = {
    fixed: 'fixed',
    holdTransition: 'hold-transition'
  }

  var Layout = function (options) {
    this.options = options
    this.bindedResize = false
    this.activate()
  }

  Layout.prototype.activate = function () {
    this.fix()

    $('body').removeClass(ClassName.holdTransition)

    if (this.options.resetHeight) {
      $('body, html, ' + Selector.wrapper).css({
        'height': 'auto',
        'min-height': '100%'
      })
    }

    if (!this.bindedResize) {
      $(window).resize(function () {
        this.fix()

        $(Selector.logo + ', ' + Selector.sidebar)
          .one(
            'webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend',
            function () {
              this.fix()
            }.bind(this))
      }.bind(this))

      this.bindedResize = true
    }

    $(Selector.sidebarMenu).on('expanded.tree', function () {
      this.fix()
    }.bind(this))

    $(Selector.sidebarMenu).on('collapsed.tree', function () {
      this.fix()
    }.bind(this))
  }

  Layout.prototype.fix = function () {
    // Get window height and the wrapper height
    // var neg = $(Selector.mainHeader).outerHeight()
    var neg = 0
    var windowHeight = $(window).height()
    var contentHeight = $(Selector.content).height()
    var sidebarHeight = $(Selector.sidebar).height() || 0

    var winner = (windowHeight > contentHeight) ? windowHeight : contentHeight
    // Set the min-height of the content and sidebar based on
    // the height of the document.
    if (winner >= sidebarHeight) {
      $(Selector.contentWrapper).css('min-height', winner - neg)
      $('#slider').css('min-height', winner - neg)
    } else {
      $(Selector.contentWrapper).css('min-height', sidebarHeight)
      $('#slider').css('min-height', sidebarHeight - neg)
    }
  }

  // Plugin Definition
  // =================
  function Plugin (option) {
    return this.each(function () {
      var $this = $(this)
      var data = $this.data(DataKey)

      if (!data) {
        var options = $.extend({}, Default, $this.data(), typeof option ===
          'object' && option)
        $this.data(DataKey, (data = new Layout(options)))
      }

      if (typeof option === 'string') {
        if (typeof data[option] === 'undefined') {
          throw new Error('No method named ' + option)
        }
        data[option]()
      }
    })
  }

  var old = $.fn.layout

  $.fn.layout = Plugin
  $.fn.layout.Constuctor = Layout

  // No conflict mode
  // ================
  $.fn.layout.noConflict = function () {
    $.fn.layout = old
    return this
  }

  // Layout DATA-API
  // ===============
  $(window).on('load', function () {
    Plugin.call($('body'))
  })
  $('body').on('sidebar-fix', function () {
    Plugin.call($(this), 'fix')
  })
}(jQuery)
