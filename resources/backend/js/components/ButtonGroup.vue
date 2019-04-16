<template>
  <div class="btn-group btn-group-toggle">
    <label v-for="(choice, idx) in choices" @click.prevent="checked(idx)" class="btn" :key="'choices'+idx"
           :class="activeItem===idx?'active btn-info':'btn-light'">
      <input type="radio" name="fieldName" autocomplete="off" :checked="activeItem===idx">
      <slot name="choice" :row="choice">{{choice}}</slot>
    </label>
  </div>
</template>

<script>
  export default {
    name: 'button-group',
    props: {
      active: null,
      fieldName: {required: true, type: String},
      choices: {}
    },
    watch: {
      active () {
        this.activeItem = this.active
      }
    },
    data () {
      return {
        activeItem: null
      }
    },
    mounted () {
      this.activeItem = this.active
    },
    methods: {
      checked (checked) {
        this.activeItem = checked
        this.$emit('active-changed', checked)
      },
      reset () {
        this.activeItem = null
      }
    }
  }
</script>