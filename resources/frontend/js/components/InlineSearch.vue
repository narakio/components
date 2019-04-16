<template>
  <div>
    <div class="input-group input-group-search">
      <input type="text" id="navbar-search-input" minlength="1"
             class="form-control border-0 bg-light input-search"
             :placeholder="placeholder" ref="searchInput"
             @keyup="search" @keyup.enter.prevent="goToSearchPage"
             v-click-outside="'outsideClick'" required>
      <button class="btn btn-light"
              type="button"
              @click="toggleFocus"
              ref="searchControl"><i v-if="!searchLoading"
                                     class="fa fa-search"></i>
        <i v-else class="fa fa-circle-o-notch fa-spin"></i>
      </button>
    </div>
    <div v-if="searchData.status&&activated" id="search-result-container">
      <div id="search-result" class="container card p-0">
        <div class="row articles">
          <header class="search-header">{{$t('search.articles')}}</header>
          <ul v-if="searchData.hasOwnProperty('articles')&&searchData.articles.length">
            <li v-for="(article, idx) in searchData.articles"
                :key="'article'+idx"><a :href="article.meta.url">
              <figure>
                <img v-if="article.meta.image" :src="article.meta.image" :alt="article.title"/>
                <img v-else :src="blackBg" :alt="article.title"/>
              </figure>
              {{article.title}}</a></li>
          </ul>
          <h6 class="header-no-results" v-else>{{$t('search.no_result')}}</h6>
        </div>
        <div class="row authors">
          <header class="search-header">{{$t('search.authors')}}</header>
          <ul v-if="searchData.hasOwnProperty('authors')&&searchData.authors.length">
            <li v-for="(author, idx) in searchData.authors"
                :key="'author'+idx"><a :href="author.url">
              <figure>
                <img :src="blackBg" :alt="author.name"/>
              </figure>
              {{author.name}}</a></li>
          </ul>
          <h6 class="header-no-results" v-else>{{$t('search.no_result')}}</h6>
        </div>
        <div class="row">
          <header class="search-header">{{$t('search.tags')}}</header>
          <ul v-if="searchData.hasOwnProperty('tags')&&searchData.tags.length">
            <li v-for="(tag, idx) in searchData.tags"
                :key="'tag'+idx"><a :href="tag.url"><i class="fa fa-tag"></i>{{tag.name}}</a></li>
          </ul>
          <h6 class="header-no-results" v-else>{{$t('search.no_result')}}</h6>
        </div>
        <div class="row">
          <header class="search-header">
            <span><a :href="searchUrl">{{$t('search.more_results',[lastInput])}} <i
                class="fa fa-chevron-right"></i></a></span>
          </header>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import Vue from 'vue'
  import axios from 'axios'

  Vue.directive('click-outside', {
    bind (el, binding, vnode) {
      let self = vnode.context
      self.event = function (event) {
        self.$emit(binding.expression.replace(/\'/g, ''), event)
      }
      el.addEventListener('click', self.stopProp)
      document.body.addEventListener('click', self.event)
    },
    unbind (el, binding, vnode) {
      el.removeEventListener('click', vnode.context.stopProp)
      document.body.removeEventListener('click', vnode.context.event)
    },
    stopProp (event) {event.stopPropagation() }
  })

  export default {
    name: 'inline-search',
    props: {
      //The url one is redirected to when one hits enter during search
      fullPageSearchUrl: {required: true},
      //scheme and host of the url used for ajax post search requests
      searchHostUrl:{required: true},
      placeholder: {required: true}
    },
    data () {
      return {
        activated: false,
        lastInput: null,
        searchTriggerDelay: 200,
        searchTriggerLength: 1,
        timer: 0,
        searchData: {headers: {}},
        blackBg: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        searchLoading: false
      }
    },
    mounted () {
      this.$on('outsideClick', this.deactivate)
    },
    destroyed () {
      this.$off('outsideClick')
    },
    computed: {
      searchUrl () {
        return this.fullPageSearchUrl +'/'+ this.lastInput
      }
    },
    methods: {
      deactivate () {
        this.activated = false
      },
      goToSearchPage () {
        document.location.href = this.searchUrl
      },
      async search (e) {
        let inputValue = e.target.value
        if (inputValue.length > this.searchTriggerLength && inputValue !== this.lastInput) {
          this.lastInput = inputValue
          this.toggleLoading(true)
          clearTimeout(this.timer)
          let vm = this
          this.timer = setTimeout(async function () {
            const {data} = await axios.post(`${vm.searchHostUrl}/search/blog/`, {q: e.target.value})
            vm.searchData = data
            vm.activated = true
            vm.toggleLoading(false)
          }, this.searchTriggerDelay)
        }
      },
      toggleFocus (e) {
        let si = this.$refs.searchInput
        if (si.clientWidth === 0) {
          si.focus()
        }
      },
      toggleLoading (value) {
        this.searchLoading = value
      }
    }
  }
</script>