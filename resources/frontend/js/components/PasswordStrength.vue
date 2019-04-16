<template>
    <div class="password-wrapper">
        <div class="password-group">
            <input v-model="password"
                   :type="inputType"
                   name="password"
                   placeholder=""
                   :required="required"
                   class="form-control">
            <div class="password-icons">
                <div v-show="passwordCount" class="password-badge"
                     :class="isOK?'password-badge-success':'password-badge-error'">
                    <span v-if="!isOK">{{passwordCount}}</span>
                    <span v-else><i class="fa fa-check"></i></span>
                </div>
                <div class="password-toggle">
                    <button tabindex="-1" type="button"
                            :aria-label="passwordLabel" class="btn-clean"
                            @click="togglePassword">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                             viewBox="0 0 24 24">
                            <title>{{passwordLabel}}</title>
                            <path :d="isPwdVisible?svgPathShow:svgPathHide"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="row password-strength-meter">
            <div class="password-strength-meter-fill" :data-score="passwordScore"></div>
        </div>
        <div v-show="passwordRecommends.length&&passwordCount"
             class="row password-recommends">
            <p v-show="passwordRecommends.length&&passwordCount"
               class="pl-1">Password strength recommendations:</p>
            <transition-group name="fade" tag="ul">
                <li v-for="(val,index) in passwordRecommends"
                    :key="`rec${index}`">{{$t(`pwd-strength.${val}`)}}</li>
            </transition-group>
        </div>
    </div>
</template>
<script>
  import owasp from '../plugins/vendor/owasp'

  owasp.configs = {
    allowPassphrases: true,
    maxLength: 128,
    minLength: 8,
    minPhraseLength: 20,
    minOptionalTestsToPass: 4
  }

  export default {
    name: 'password-strength',
    props: {
      placeholder: {
        type: String,
        default: ''
      },
      value: {
        type: String,
        default: ''
      },
      name: {
        type: String,
        default: 'password'
      },
      required: {
        type: Boolean,
        default: true
      },
      disabled: {
        type: Boolean,
        default: false
      },
      secureLength: {
        type: Number,
        default: 8
      },
      labelShow: {
        type: String,
        default: 'Show Password'
      },
      labelHide: {
        type: String,
        default: 'Hide Password'
      }
    },
    data () {
      return {
        password: '',
        isPwdVisible: false,
        svgPathHide: 'M11.859 9h0.141c1.641 0 3 1.359 3 3v0.188zM7.547 9.797c-0.328 0.656-0.563 1.406-0.563 2.203 0 2.766 2.25 5.016 5.016 5.016 0.797 0 1.547-0.234 2.203-0.563l-1.547-1.547c-0.188 0.047-0.422 0.094-0.656 0.094-1.641 0-3-1.359-3-3 0-0.234 0.047-0.469 0.094-0.656zM2.016 4.266l1.266-1.266 17.719 17.719-1.266 1.266c-1.124-1.11-2.256-2.213-3.375-3.328-1.359 0.563-2.813 0.844-4.359 0.844-5.016 0-9.281-3.094-11.016-7.5 0.797-1.969 2.109-3.656 3.75-4.969-0.914-0.914-1.812-1.844-2.719-2.766zM12 6.984c-0.656 0-1.266 0.141-1.828 0.375l-2.156-2.156c1.219-0.469 2.578-0.703 3.984-0.703 5.016 0 9.234 3.094 10.969 7.5-0.75 1.875-1.922 3.469-3.422 4.734l-2.906-2.906c0.234-0.563 0.375-1.172 0.375-1.828 0-2.766-2.25-5.016-5.016-5.016z',
        svgPathShow: 'M12 9c1.641 0 3 1.359 3 3s-1.359 3-3 3-3-1.359-3-3 1.359-3 3-3zM12 17.016c2.766 0 5.016-2.25 5.016-5.016s-2.25-5.016-5.016-5.016-5.016 2.25-5.016 5.016 2.25 5.016 5.016 5.016zM12 4.5c5.016 0 9.281 3.094 11.016 7.5-1.734 4.406-6 7.5-11.016 7.5s-9.281-3.094-11.016-7.5c1.734-4.406 6-7.5 11.016-7.5z'
      }
    },
    watch: {
      password () {
        return this.value
      }
    },
    computed: {
      passwordRecommends () {
        return owasp.test(this.password).errors
      },
      passwordScore () {
        if (this.passwordCount) {
          return owasp.test(this.password).passedTests.length
        } else {
          return 0
        }
      },
      isOK () {
        return this.password ? this.password.length >= this.secureLength : null
      },
      isActive () {
        return this.password && this.password.length > 0
      },
      passwordCount () {
        return this.password &&
          (this.password.length > this.secureLength ? this.secureLength + '+' : this.password.length)
      },
      inputType () {
        return this.isPwdVisible ? 'text' : 'password'
      },
      passwordLabel () {
        return this.isPwdVisible ? this.labelHide : this.labelShow
      }
    },
    methods: {
      togglePassword () {
        this.isPwdVisible = !this.isPwdVisible
      }
    }
  }
</script>