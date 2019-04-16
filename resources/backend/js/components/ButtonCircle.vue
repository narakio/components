<script>
  export default {
    name: 'button-circle',
    props: {
      enabled: {
        type: Boolean,
        required: true
      },
    },
    data () {
      return {
        buttonEnabled: this.enabled
      }
    },
    computed: {
      buttonTypeComputed () {
        return this.buttonEnabled ? 'btn-success' : 'btn-danger'
      },
      icon () {
        return this.buttonEnabled ? 'check' : 'times'
      }
    },
    methods: {
      toggle (e) {
        this.buttonEnabled = !this.buttonEnabled
        this.$emit('clicked')
      }
    },
    render: function (h) {
      return h('button', {
        class: ['btn', 'btn-circle', this.buttonTypeComputed],
        on: {
          click: this.toggle
        },
        attrs: {
          type: 'button'
        }
      }, [
        h('i', {class: ['fa fa-'+this.icon]})
      ])
    }
  }
</script>