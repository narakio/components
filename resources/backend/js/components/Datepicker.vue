<template>
  <v-datepicker :class="classItems"
                ref="datePicker"
                v-model="dateValue"
                :name="name"
                :language="language[$store.getters['prefs/locale']]"
                :monday-first="true"
                :typeable="typeable"
                :clear-button="showClearButton"
                :placeholder="placeholder"
                :format="dateFormat" @closed="closed">
    <!--<div slot="beforeCalendarHeader" class="calender-header">-->
    <!--<div class="container">-->
    <!--<div class="col-lg-12">-->
    <!--<div class="row justify-content-md-center"-->
    <!--id="datepicker-clear-button" v-show="showClearButton"><span>Clear</span></div>-->
    <!--<div class="row justify-content-md-center"-->
    <!--id="datepicker-today-button" v-show="showTodayButton"><span>Today</span></div>-->
    <!--</div>-->
    <!--</div>-->
    <!--</div>-->
  </v-datepicker>
</template>
<script>
  import Datepicker from 'vuejs-datepicker'
  import { en, fr } from 'vuejs-datepicker/dist/locale'

  export default {
    name: 'datepicker',
    components: {
      'v-datepicker': Datepicker
    },
    props: {
      showClearButton: {
        default: false,
        type: Boolean
      },
      showTodayButton: {
        default: false,
        type: Boolean
      },
      name: {
        type: String
      },
      value: {
        type: Date
      },
      classList:{
        type:Array,
        default: ()=>[]
      },
      openLeft: {
        type: Boolean,
        default: false
      },
      typeable: {
        type: Boolean,
        default: false
      },
      placeholder: {
        type: String
      },
      showOnStart: {
        type: Boolean,
        default: false
      },
      format: {
        type: String
      }
    },
    computed: {
      classItems(){
        let list = {'calendar-open-left':this.openLeft}
        this.classList.forEach((value)=>{
          list[value]=true
        })
        return list
      }
    },
    methods: {
      closed () {
        this.$emit('closed', this.dateValue)
      }
    },
    mounted () {
      if (this.format) {
        this.dateFormat = this.format
      } else {
        this.dateFormat = this.$store.getters['prefs/dateFormat']
      }

      this.dateValue = this.value
      if (this.showOnStart) {
        this.$refs.datePicker.showCalendar()
      }
      this.$refs.datePicker.$el.querySelector('input').focus()
    },
    data () {
      return {
        dateFormat: null,
        dateValue: '',
        language: {
          'en': en,
          'fr': fr
        }
      }
    },
    watch: {
      value (value) {
        this.dateValue = value
      }
    }
  }
</script>