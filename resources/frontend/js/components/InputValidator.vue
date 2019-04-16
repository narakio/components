<template>
  <div>
    <input :type="type"
           :name="name"
           :class="currentClasses"
           :maxlength="maxlength"
           :autocomplete="autocomplete"
           @keyup="search"
           @blur="search"
           :required="required"
           v-model="currentValue">
    <div class="invalid-feedback" v-if="errors">
      <strong>{{ errors }}</strong>
    </div>
    <div class="validator-valid" v-if="validated">
      <span class="fa-stack-1x icon">
        <i class="fa fa-circle fa-stack-1x" :class="[validationOk?'success':'danger']"></i>
        <i class="fa fa-stack-1x status" :class="[validationOk?'fa-check':'fa-times']"></i>
      </span>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'

  export default {
    name: 'input-validator',
    props: {
      classes: {type: Object},
      minlength: {type: Number, default: () => 0},
      maxlength: {type: Number, default: () => 255},
      type: {type: String, default: () => 'text'},
      name: {type: String},
      value: {type: String},
      autocomplete: {type: String, default: () => 'off'},
      required: {type: Boolean, default: () => false},
      //scheme and host of the url used for ajax post search requests
      searchHostUrl: {type: String, required: true},
      searchField: {type: String, required: true},
      validationType: {type: String, default: () => 'text'},
      errors: {type: String}
    },
    watch: {
      value () {
        this.currentValue = this.value
      },
      classes () {
        this.currentClasses = classes
      }
    },
    computed: {
      // currentValue () {
      //   return this.value
      // },
      // currentClasses () {
      //   return this.classes
      // },
      regex () {
        let regex = null
        switch (this.validationType) {
          case 'email':
            regex = new RegExp(
              /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/)
            break
          case 'username':
            regex = new RegExp(/^\w+$/)
            break
          default:
            regex = new RegExp(/\w*/)
            break
        }
        return regex
      }
    },
    data () {
      return {
        currentValue: null,
        currentClasses: null,
        validated: false,
        searchTriggerLength: 5,
        lastInput: null,
        searchTriggerDelay: 1000,
        timer: 0,
        validationOk: false
      }
    },
    mounted () {
      this.currentValue = this.value
      this.currentClasses = this.classes
    },
    methods: {
      async search (e) {
        let inputValue = e.target.value
        if (inputValue.length && inputValue !== this.lastInput && (inputValue.length < this.minLength
          || inputValue.length > this.maxLength
          || !inputValue.match(this.regex))) {
          this.lastInput = inputValue
          this.validated = true
          this.validationOk = false
          return
        }
        if (
          inputValue.length > this.searchTriggerLength
          && inputValue !== this.lastInput
        ) {
          this.lastInput = inputValue
          clearTimeout(this.timer)

          let vm = this
          this.timer = setTimeout(async function () {
            const {data} = await axios.post(`${vm.searchHostUrl}/search/check`,
              {q: e.target.value, field: vm.searchField})
            vm.validated = true
            if (data.hasOwnProperty('cnt')) {
              vm.validationOk = data.cnt === 0
              if (vm.validationOk) {
                vm.currentClasses['is-invalid'] = false
              }
            }
          }, this.searchTriggerDelay)
        }
      }
    }
  }
</script>