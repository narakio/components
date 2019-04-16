<template>
  <textarea></textarea>
</template>

<script>
  // import 'trumbowyg';
  import 'trumbowyg/dist/trumbowyg.min.js'

  // import 'trumbowyg/dist/plugins/'
  // import 'back_path/components/wysiwyg/plugins/bbImage.js'
  import 'back_path/components/wysiwyg/plugins/bbCode.js'

  const events = ['init']
  const eventPrefix = 'tbw'
  export default {
    name: 'trumbowyg',
    props: {
      value: {
        default: null,
        required: true,
        validator (value) {
          return value === null || typeof value === 'string' || value instanceof String
        }
      },
      config: {
        type: Object,
        default: () => ({})
      },
      svgPath: {
        type: [String, Boolean],
        default: true
      },
    },
    data () {
      return {
        // jQuery DOM
        el: null,
      }
    },
    mounted () {
      // console.log(svgIcons)
      // Return early if instance is already loaded
      if (this.el) return
      // Store DOM
      this.el = jQuery(this.$el)
      // Init editor with config
      this.el.trumbowyg(jQuery.extend({}, {svgPath: this.svgPath}, this.config))
      // Set initial value
      this.el.trumbowyg('html', this.value)
      // Watch for further changes
      this.el.on(`${eventPrefix}change`, this.onChange)
      // Workaround : trumbowyg does not trigger change event on Ctrl+V
      this.el.on(`${eventPrefix}paste`, this.onChange)
      // Register events
      this.registerEvents()
    },
    watch: {
      /**
       * Listen to change from outside of component and update DOM
       *
       * @param newValue String
       */
      value (newValue) {
        if (this.el) {
          // Prevent multiple input events
          if (newValue === this.el.trumbowyg('html')) return
          // Set new value
          this.el.trumbowyg('html', newValue)
        }
      },
    },
    methods: {
      /**
       * Emit input event with current editor value
       * This will update v-model on parent component
       * This method gets called when value gets changed by editor itself
       *
       * @param event
       */
      onChange (event) {
        this.$emit('input', event.target.value)
      },
      /**
       * Emit all available events
       */
      registerEvents () {
        events.forEach((name) => {
          this.el.on(`${eventPrefix}${name}`, (...args) => {
            this.$emit(`${eventPrefix}-${name}`, ...args)
          })
        })
      }
    },
    beforeDestroy () {
      // Free up memory
      if (this.el) {
        this.el.trumbowyg('destroy')
        this.el = null
      }
    }
  }
</script>