<script>
  export default {
    name: 'inline-form',
    render: function (h) {
      return h('form', {
        props: {
          id: {required: false, default: null},
          method: {required: false, default: 'POST'},
          action: {required: false, default: null},
          class: {required: false, default: null}
        },
        attrs: {
          role: 'form',
          method: this.method,
          action: this.action,
          class: this.class
        },
        on: {
          submit: () => {
            if (this.$root.$refs.hasOwnProperty('submitButton')) {
              this.$root.$refs.submitButton.toggleLoad(true)
            }
          }
        }
      }, this.$slots.default)
    }
  }
</script>