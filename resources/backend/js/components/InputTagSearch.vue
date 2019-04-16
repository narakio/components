<template>
  <div class="input-tag-search-wrapper">
    <div class="search-spinner-wrapper">
      <i class="fa fa-cog" :class="loadIconIsAnimated"></i>
    </div>
    <div class="input-tag-container">
      <div class="form-control input-tag">
        <span class="badge badge-pill badge-light"
              v-for="(badge, index) in tagBadges"
              :key="index">
          <span v-html="badge"></span>
          <i href="#" class="button-badge-close" @click.prevent="removeTag(index)"></i>
        </span>
        <input type="text" v-if="tagBadges.length<limit"
               :placeholder="dataPlaceholder"
               v-model="input"
               ref="search"
               @focus="dataPlaceholder=''"
               @blur="dataPlaceholder=placeholder"
               @keyup.enter.prevent="tagFromInput"
               @keyup.esc="ignoreSearchResults"
               @keyup.delete="removeLastTag"
               @keyup="searchTag"
               @value="tags">
        <input type="hidden" :name="elementId" :id="elementId" v-model="hiddenInput">
      </div>
      <div v-show="searchResults.length" class="search-results-area">
        <ul>
          <li v-for="(tag, index) in searchResults"
              :key="index"
              v-text="tag.text"
              @click="tagFromSearch(tag)"
              class="badge"
              :class="{
                  'badge-primary': index == searchSelection,
                  'badge-dark': index != searchSelection}"></li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'

  export default {
    name: 'input-tag-search',
    props: {
      elementId: String,
      existingTags: {
        type: [Array, Object],
        default: () => {
          return []
        }
      },
      oldTags: [Array, String],
      typeahead: Boolean,
      placeholder: String,
      searchUrl: String,
      limit: {
        default: 5
      }
    },

    data () {
      return {
        badgeId: 0,
        tagBadges: [],
        tags: [],
        tagBag: [],

        input: '',
        oldInput: '',
        hiddenInput: '',

        searchResults: [],
        searchSelection: 0,
        dataPlaceholder: this.placeholder,
        searchTriggerDelay: 200,
        searchTriggerLength: 1,
        timer: 0,
        loadIconIsAnimated: false
      }
    },
    mounted () {
      this.$refs.search.focus()
    },
    created () {
      if (this.oldTags && this.oldTags.length) {
        let oldTags = Array.isArray(this.oldTags)
          ? this.oldTags
          : this.oldTags.split(',')

        for (let id of oldTags) {
          let existingTag = this.existingTags[id]
          let text = existingTag ? existingTag : id

          this.addTag(id, text)
        }
      }
    },
    watch: {
      tags () {
        // Updating the hidden input
        this.hiddenInput = this.tags.join(',')
        this.$emit('updateAddedItems', this.tagBag)
      }
    },
    methods: {
      tagFromInput (e) {
        // If we're choosing a tag from the search results
        if (this.searchResults.length && this.searchSelection >= 0) {
          this.tagFromSearch(this.searchResults[this.searchSelection])

          this.input = ''
        }
      },
      tagFromSearch (tag) {
        this.searchResults = []
        this.input = ''
        this.$refs.search.focus()
        this.addTag(tag.id, tag.text)
      },
      makeSlug (value) {
        return value.toLowerCase().replace(/\s/g, '-')
      },
      addTag (id, text) {
        // Attach the tag if it hasn't been attached yet
        let searchSlug = this.makeSlug(id)
        let found = this.tags.find((text) => {
          return searchSlug == this.makeSlug(text)
        })
        if (!found) {
          this.tagBadges.push(text.replace(/\s/g, '&nbsp;'))
          this.tags.push(id)
          this.tagBag.push({id, text})
        }
      },
      async removeLastTag (e) {
        //If no text was entered we remove the rightmost tag, otherwise we refresh the search results
        if (!e.target.value.length) {
          this.removeTag(this.tags.length - 1)
        } else {
          this.searchTag(e.target.value)
        }
      },
      removeTag (index) {
        this.tags.splice(index, 1)
        this.tagBadges.splice(index, 1)
        this.tagBag.splice(index, 1)
        this.$refs.search.focus()
      },
      async searchTag (e, value) {
        let input = (typeof value === 'undefined') ? this.input.trim() : value
        if (this.typeahead === true) {
          if (input.length < this.searchTriggerLength) {
            return
          }
          clearTimeout(this.timer)
          let vm=this
          this.timer = setTimeout(async function () {
            if (vm.oldInput !== input) {
              vm.loadIconIsAnimated = true
              vm.searchResults = await vm.searchTerm(input)
              vm.loadIconIsAnimated = false
              vm.searchSelection = 0

              if (input.length) {
                for (let id in vm.existingTags) {
                  let text = vm.existingTags[id].toLowerCase()

                  if (text.search(input.toLowerCase()) > -1) {
                    vm.searchResults.push({id, text: vm.existingTags[id]})
                  }
                }
              }
              vm.oldInput = input
            }
          }, this.searchTriggerDelay)
        }
      },
      async searchTerm (term) {
        let {data} = await axios.get(`${this.searchUrl}/${term}/${this.limit}`)
        return data
      },
      nextSearchResult () {
        if (this.searchSelection + 1 <= this.searchResults.length - 1) {
          this.searchSelection++
        }
      },
      prevSearchResult () {
        if (this.searchSelection > 0) {
          this.searchSelection--
        }
      },
      ignoreSearchResults () {
        this.searchResults = []
        this.searchSelection = 0
      }
    }
  }
</script>