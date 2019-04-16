<script>
  export default {
    name: 'has-error',

    props: {
      form: {
        type: Object,
        required: true
      },

      field: {
        type: String,
        required: true
      }
    },

    render: function (h) {
      if (this.form.errors.has(this.field)) {
        return h('div', {
          class: 'help-block invalid-feedback',
          domProps: {
            innerHTML: this.form.errors.get(this.field)
          }
        })
      }
      return h('div', {})
    }
  }
</script>
