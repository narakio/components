<template>
  <div class="container p-0" id="search-container">
    <div class="row">
      <div class="col">
        <input id="search-input" ref="searchInput" @keyup="search"
               type="text" v-model="inputValue">
      </div>
    </div>
    <div class="row">
      <div class="col-md-8" id="articles">
        <div class="container">
          <div class="card row">
            <div class="col">
              <header class="search-header">{{$t('search.articles')}}</header>
              <ul v-if="searchData.articles!=null&&searchData.articles.data.length">
                <li v-for="(article, idx) in searchData.articles.data"
                    :key="'article'+idx">
                  <div class="container p-0">
                    <div class="row d-flex align-items-center">
                      <div class="col-md-3">
                        <figure><a :href="article.meta.url"><img v-if="article.meta.image"
                                                                 :src="article.meta.image"
                                                                 :alt="article.title"/><img
                            v-else :src="blackBg" :alt="article.title"/></a></figure>
                      </div>
                      <div class="col-md-8">
                        <div class="row article-info">
                          <a :href="article.meta.url">{{article.title}}</a>
                        </div>
                        <div class="row">
                          <div class="d-inline-block">
                            <i class="fa fa-user-circle-o"></i>
                            <a :href="article.meta.author.url">{{article.meta.author.name}}</a>
                          </div>
                        </div>
                        <div class="row">
                          <div class="d-inline-block">
                            <i class="fa fa-calendar"></i> {{article.date}}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
              <h6 class="header-no-results" v-else>{{$t('search.no_result')}}</h6>
            </div>
          </div>
          <div v-if="searchData.articles!=null&&searchData.articles.data.length&&searchData.articles.last_page>1"
               class="row" id="paginator-wrapper">
            <nav aria-label="Page navigation example" id="paginator">
              <ul class="pagination">
                <li class="page-item" :class="[searchData.articles.current_page===1?'disabled':null]">
                  <a class="page-link" href="#" @click.prevent="goToPage(false)"
                     :aria-label="$t('general.prev')">
                    <span aria-hidden="true"><i class="fa fa-angle-left"></i></span>
                  </a>
                </li>
                <li v-for="n in searchData.articles.last_page"
                    class="page-item"
                    :class="[n===searchData.articles.current_page
                    ?'active':null]"><a class="page-link"
                                      href="#"
                                      @click.prevent="goToPage(n)">{{n}}</a>
                </li>
                <li class="page-item"
                    :class="[searchData.articles.current_page===searchData.articles.last_page?'disabled':null]">
                  <a class="page-link" href="#"
                     @click.prevent="goToPage(true)"
                     :aria-label="$t('general.next')">
                    <span aria-hidden="true"><i class="fa fa-angle-right"></i></span>
                  </a>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
      <div class="col-md-4" id="sidebar">
        <div class="container">
          <div id="search-tag-wrapper" class="card row">
            <div class="col">
              <header class="search-header">{{$t('search.tags')}}</header>
              <ul class="search-tags" v-if="searchData.tags!=null&&searchData.tags.length">
                <li v-for="(tag, idx) in searchData.tags"
                    :key="'tag'+idx" class="badge badge-info d-inline-flex"><a
                    :href="tag.url">{{tag.name}}</a>
                </li>
              </ul>
              <h6 class="header-no-results" v-else>{{$t('search.no_result')}}</h6>
            </div>
          </div>
          <div id="search-author-wrapper" class="card row mt-3">
            <div class="col">
              <header class="search-header">{{$t('search.authors')}}</header>
              <ul v-if="searchData.authors!=null&&searchData.authors.length">
                <li v-for="(author, idx) in searchData.authors"
                    :key="'author'+idx" class="d-block my-1"><a
                    :href="author.url">{{author.name}}</a>
                </li>
              </ul>
              <h6 class="header-no-results" v-else>{{$t('search.no_result')}}</h6>
            </div>
          </div>
          <div class="card row mt-3" v-if="searchData.articles!=null&&searchData.articles.data.length">
              <div class="col">
                <header class="search-header">{{$t('search.rss')}}</header>
                <ul class="search-tags social-icon">
                  <li class="d-block my-1"><a :href="rssUrl" target="_blank"><i class="fa fa-rss"></i></a>
                  </li>
                </ul>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import axios from 'axios'

  export default {
    name: 'full-page-search',
    props: {
      initialValue: {required: true},
      searchHostUrl: {required: true},
      rssRootUrl: {required: true}
    },
    data () {
      return {
        searchData: {tags: null, articles: null, authors: null},
        lastInput: null,
        inputValue: null,
        searchTriggerDelay: 200,
        searchTriggerLength: 1,
        timer: 0,
        blackBg: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
        searchLoading: false
      }
    },
    computed: {
      searchUrl () {
        return `${this.searchHostUrl}/search/blog/paginate`
      },
      rssUrl () {
        return this.rssRootUrl + '/' + this.lastInput
      }
    },
    mounted () {
      this.inputValue = this.initialValue
      this.lastInput = this.initialValue
      this.$refs.searchInput.focus()
      this.getData()
    },
    methods: {
      async goToPage (n) {
        let current = this.searchData.articles.current_page
        if (n === current) {
          return
        }
        let pageTarget = n
        if (typeof n === 'boolean') {
          if (n) {
            pageTarget = current + 1
          } else {
            pageTarget = current - 1
          }
        }
        const {data} = await axios.post(this.searchUrl + '?page=' + pageTarget, {q: this.lastInput})
        this.searchData = data
      },
      async search (e) {
        let inputValue = e.target.value
        if (inputValue.length > this.searchTriggerLength && inputValue !== this.lastInput) {
          this.lastInput = inputValue
          this.toggleLoading(true)
          clearTimeout(this.timer)
          let vm = this
          this.timer = setTimeout(async function () {
            const {data} = await axios.post(vm.searchUrl, {q: e.target.value})
            let url = window.location.href.substr(0, window.location.href.lastIndexOf('/') + 1) + e.target.value
            window.history.replaceState({}, vm.$root.$t('general.search') + ' - ' + e.target.value, url)
            vm.searchData = data
            vm.toggleLoading(false)
          }, this.searchTriggerDelay)
        }
      },
      toggleLoading (value) {
        this.searchLoading = value
      },
      async getData () {
        const {data} = await axios.post(this.searchUrl, {q: this.initialValue})
        this.searchData = data
      }
    }

  }
</script>