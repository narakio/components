<template>
    <div class="tree-search-wrapper">
        <div class="input-group input-wrapper">
            <input type="text" class="form-control"
                   :placeholder="$t('pages.blog.filter_name')"
                   :aria-label="$t('pages.blog.filter_name')"
                   v-model="searchTerm"
                   @input="onInput($event.target.value)"
                   @keyup.esc="isOpen = false"
                   @blur="isOpen = false">
            <div class="input-group-append">
                <label class="input-group-text"
                       :title="$t('general.search')">
                    <i class="fa fa-search"></i>
                </label>
            </div>
        </div>
        <ul v-if="isOpen && filteredOptions.length > 0" class="options-list">
            <li v-for="(res,index) in filteredOptions"
                :key="index"
                @mousedown="search(res)">
                {{res.data.target.label}}
            </li>
        </ul>
    </div>
</template>

<script>
  export default {
    name: 'search',
    props: {
      terms: {
        required: true,
        type: Array
      }
    },
    data () {
      return {
        searchTerm: '',
        isOpen: false
      }
    },
    computed: {
      filteredOptions () {
        const re = new RegExp(this.searchTerm, 'i')
        return this.terms.filter(item => item.data.target.label.match(re))
      }
    },
    methods: {
      search (payload) {
        this.$emit('show', payload.nodeMap, payload.data)
      },
      onInput (value) {
        if (value.length > 0) {
          this.isOpen = !!value
        } else {
          this.isOpen = false
        }
      }
    }
  }
</script>