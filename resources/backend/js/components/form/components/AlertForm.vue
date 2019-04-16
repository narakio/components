<template>
    <div v-show="isShowable">
        <transition name="fade" :appear="true">
            <div v-if="form.errors.any()" :class="classObject" role="alert" aria-live="polite" aria-atomic="true">
                <div>{{$t('error.form')}}</div>
                <ul v-if="showErrors">
                    <li v-for="error in form.errors.flatten()" v-html="error"></li>
                </ul>
                <button v-show="dismissible" type="button"
                        class="close" data-dismiss="alert" :aria-label="dismissLabel" @click="dismiss">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div v-else-if="form.successful&&message!=null" :class="classObject" role="alert">
                <div v-html="message"></div>
            </div>
        </transition>
    </div>
</template>

<script>
  export default {
    name: 'alert-form',
    props: {
      variant: {
        type: String,
        default: 'info'
      },
      dismissible: {
        type: Boolean,
        default: false
      },
      dismissLabel: {
        type: String,
        default: 'Close'
      },
      form: {
        type: Object,
        required: true
      },
      message: {
        type: String,
        required: false,
        default: null
      },
      showErrors: {
        type: Boolean,
        required: false,
        default: true
      },
      show: {
        type: [Boolean, Number],
        default: true
      },
      fade: {
        type: Boolean,
        default: true
      }
    },
    data () {
      return {
        countDownTimerId: null,
        dismissed: false
      }
    },
    watch: {
      show () {
        this.showChanged()
      }
    },
    computed: {
      classObject () {
        return ['alert', this.alertVariant, this.dismissible ? 'alert-dismissible' : '',(this.showable)?'show':null]
      },
      alertVariant () {
        let variant
        if (this.form.errors.any()) {
          variant = 'danger'
        } else {
          variant = this.variant
        }
        return 'alert-' + variant
      },
      isShowable () {
        return !this.dismissed && (this.countDownTimerId || this.show)
      }
    },
    mounted () {
      this.showChanged()
    },
    destroyed () {
      this.clearCounter()
    },
    methods: {
      dismiss (e) {
        this.clearCounter()
        this.dismissed = true
        this.$emit('dismissed')
        this.$emit('input', false)
        if (typeof this.show === 'number') {
          this.$emit('dismiss-count-down', 0)
          this.$emit('input', 0)
        } else {
          this.$emit('input', false)
        }
      },
      clearCounter () {
        if (this.countDownTimerId) {
          clearInterval(this.countDownTimerId)
          this.countDownTimerId = null
        }
      },
      showChanged () {
        var vm = this

        // Reset counter status
        this.clearCounter()
        // Reset dismiss status
        this.dismissed = false
        // No timer for boolean values
        if (this.show === true || this.show === false || this.show === null || this.show === 0) {
          return
        }
        // Start counter
        var dismissCountDown = this.show
        this.countDownTimerId = setInterval(function () {
          if (dismissCountDown < 1) {
            vm.dismiss()
            return
          }
          dismissCountDown--
          vm.$emit('dismiss-count-down', dismissCountDown)
          vm.$emit('input', dismissCountDown)
        }, 1000)
      }
    }

  }
</script>
