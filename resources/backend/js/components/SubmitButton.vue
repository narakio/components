<script>
  export default {
    name: 'submit-button',
    render: function (h) {
      return h('button', {
        class: {
          'align-content-center': true,
          'btn': true,
          [`btn-${this.type}`]: true,
          'btn-block': this.block,
          'btn-lg': this.large,
          'btn-loading': this.isLoading
        },
        on: {
          click: () => this.$emit('click')
        },
        attrs: {
          type: this.nativeType,
          disabled: this.isLoading
        }
      }, [
        (this.value) ? h('span', this.value) : h('span', this.$slots.default)
      ])
    },
    props: {
      type: {
        type: String,
        default: () => 'primary'
      },
      nativeType: {
        type: String,
        default: () => 'submit'
      },
      loading: {
        type: Boolean,
        default: () => false
      },
      block: {
        type: Boolean,
        default: () => false
      },
      large: {
        type: Boolean,
        default: () => false
      },
      value: {
        type: String,
        default: () => null
      }
    },
    data () {
      return {
        isLoading: false
      }
    },
    watch: {
      loading () {
        this.isLoading = this.loading
      }
    },
    methods: {
      toggleLoad () {
        this.isLoading = !this.isLoading
      }
    }
  }
</script>
