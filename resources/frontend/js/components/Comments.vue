<template>
  <div id="comments-wrapper" class="container">
    <template v-if="scrolledPast">
      <div class="row" v-if="loaded">
        <div id="comments-header" class="col-lg-8 col-sm-12 mx-md-auto">
          <div class="w-100">
            <h5 class="bordered"><span>{{$t('comments.discussion')}}</span></h5>
            <div class="button-group" v-if="userIsAuthenticated">
              <submit-button :native-type="'button'" v-if="userIsAuthenticated"
                             :type="'success'"
                             :loading="refreshLoading"
                             @click="refreshComments"><i class="fa fa-refresh"></i> {{$t('comments.refresh')}}
              </submit-button>
              <button type="button" :disabled="!commentIsOK"
                      class="btn btn-primary"
                      @click="editMode=true"><i class="fa fa-reply fa-rotate-180"></i> {{$t('comments.comment')}}
              </button>
              <div class="dropdown d-inline">
                <button class="btn btn-dark dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-cog"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                  <a class="dropdown-header" href="#">I get an email when: (click/tap to toggle)</a>
                  <a v-for="(event,idx) in notificationEvents"
                     :key="'events'+idx"
                     class="dropdown-item"
                     href.prevent="#"
                     @click="changeNotifications(event.id)"><i
                      v-if="notificationOptions.hasOwnProperty(event.id)&&notificationOptions[event.id]===true"
                      class="fa fa-check mr-1"></i><i
                      v-else class="fa fa-times mr-1"></i>{{event.name}}</a>
                </div>
              </div>
            </div>
            <div class="button-group" v-else>
              <button type="button"
                      class="btn btn-primary"
                      @click="goToLoginPage"><i class="fa fa-reply fa-rotate-180"></i> {{$t('comments.login_comment')}}
              </button>

            </div>
          </div>
        </div>
      </div>
      <transition name="fade">
        <div v-show="editMode" class="row">
          <div class="col-lg-8 col-sm-12 mx-md-auto mb-3">
            <comment-editor :is-root-elem="true"
                            :edit-mode="editMode"
                            :entity-slug="slug"
                            @cancelled="editMode = false"
                            @submitted="editMode = false"
                            :search-url="searchUrl"></comment-editor>
          </div>
        </div>
      </transition>
      <div class="row">
        <div class="col-lg-8 col-sm-12 mx-md-auto">
          <div v-if="!loaded" class="fa-3x">
            <i class="fa fa-refresh fa-pulse"></i>
          </div>
          <comment-list :comments="comments"
                        :auth-check="userIsAuthenticated"
                        :slug="slug"
                        :search-url="searchUrl"></comment-list>
        </div>
      </div>
    </template>
  </div>
</template>
<script>
  import SubmitButton from 'back_path/components/SubmitButton'
  import initIntersection from 'front_path/plugins/vendor/intersection.js'
  import axios from 'axios'

  export default {
    name: 'comments',
    props: {
      slug: {
        required: true,
        type: String
      },
      login: {
        required: true,
        type: String
      },
      searchUrl: {
        required: true,
        type: String
      }
    },
    data () {
      return {
        scrolledPast: true,
        loaded: false,
        io: null,
        container: null,
        editMode: false,
        comments: null,
        favorites: null,
        refreshLoading: false,
        refreshIsOK: true,
        commentIsOK: true,
        refreshDelay: 5000,
        commentDelay: 120000,
        userIsAuthenticated: false,
        notificationOptions: {},
        notificationEvents: null
      }
    },
    components: {
      CommentList: () => import('front_path/components/CommentList'),
      CommentEditor: () => import('front_path/components/CommentEditor'),
      SubmitButton
    },
    mounted () {
      if (window.hasOwnProperty('config')) {
        if (window.config.hasOwnProperty('auth_check')) {
          this.userIsAuthenticated = window.config.auth_check
        }
      }
      this.getData()
      let vm = this
      this.$root.$on('tiptapIsMounted', () => {
        vm.loaded = true
      })
      this.$root.$on('commentFavorited', (comment) => {
        axios.patch(`/ajax/forum/blog_posts/${vm.slug}/favorite/${comment.slug}`)
      })
      this.$root.$on('commentUnfavorited', (comment) => {
        axios.delete(`/ajax/forum/blog_posts/${vm.slug}/favorite/${comment.slug}`)
      })
      this.$root.$on('commentSubmitted', () => {
        this.commentIsOK = false
        this.commentSetDelay()
      })
      this.$root.$on('commentDeleted', async ({slug}) => {
        await axios.delete(`/ajax/forum/blog_posts/${vm.slug}/comment/${slug}`)
      })
      // if (!window.IntersectionObserver) {
      //   initIntersection(window, document)
      // }
      // this.container = this.$el
      // this.io = new IntersectionObserver(([entry]) => {
      //   if (entry.isIntersecting) {
      //     this.scrolledPast = true
      //     this.io.unobserve(this.container)
      //   }
      // })
      // this.io.observe(this.container)
    },
    methods: {
      changeNotifications (type) {
        if (this.notificationOptions[type]) {
          this.notificationOptions[type] = !this.notificationOptions[type]
        } else {
          this.notificationOptions[type] = true
        }
        axios.patch(`/ajax/forum/blog_posts/options`, {data: this.notificationOptions})
      },
      goToLoginPage () {
        window.location.href = this.login
      },
      async refreshSetDelay () {
        let vm = this
        setTimeout(async function () {
          vm.refreshIsOK = true
        }, this.refreshDelay)
      },
      async commentSetDelay () {
        let vm = this
        setTimeout(async function () {
          vm.commentIsOK = true
        }, this.commentDelay)
      },
      async refreshComments () {
        if (this.refreshIsOK) {
          this.refreshLoading = true
          await this.getData()
          this.refreshLoading = false
          this.refreshIsOK = false
          this.refreshSetDelay()
        }
      },
      async getData () {
        const {data} = await axios.get(`/ajax/forum/blog_posts/${this.slug}/comment`)
        this.comments = data.posts
        this.notificationEvents = data.events
        if (data.notification_options !== null) {
          this.notificationOptions = data.notification_options
        }
      }
    },
    beforeDestroy () {
      this.$root.$off('tiptapIsMounted')
      this.$root.$off('commentSubmitted')
      this.$root.$off('commentDeleted')
      this.$root.$off('commentFavorited')
      this.$root.$off('commentUnfavorited')
    }
  }
</script>