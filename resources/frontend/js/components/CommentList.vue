<template>
  <transition-group name="fade" tag="div"
                    id="comments-container"
                    class="container" v-if="comments!==null&&comments.length">
    <div v-for="(comment,idx) in comments" :key="comment.slug"
         class="row comment-item"
         :class="[comment.lvl==0?'card mb-2':null,hash==comment.slug?'highlight':null]">
      <div class="col" style="border-left:2px solid #f7f7f7;">
        <div class="comment-header">
          <figure class="comment-header-item">
            <img v-if="comment.media!==null" :src="comment.media"/>
            <img v-else src="/media/img/site/placeholder_tb.png"/>
          </figure>
          <div class="comment-header-item">
            <div class="d-block">
              <div class="username">
                <a v-if="comment.username" :href="comment.url">{{comment.username}}</a>
                <span v-else>{{$t('comments.deleted_user')}}</span>
              </div>

              <div class="date">{{comment.updated_at}}</div>
            </div>
          </div>
          <div class="comment-header-item actions">
            <div class="favorite">
              <i class="fa" :class="{
              'fa-star':comment.fav,
              'fa-star-o':!comment.fav,
              auth:authCheck,
              flip:comment.fav,animate:cssAnimationsActivated}"
                 :aria-label="comment.fav?$t('comments.recommend-cancel'):$t('comments.recommend')"
                 :title="comment.fav?$t('comments.recommend-cancel'):$t('comments.recommend')"
                 @click="recommend(comment,idx)"></i>
              <div class="fav-count">{{(comment.cnt>0?comment.cnt:null)}}</div>
            </div>
            <div class="expand">
              <i class="fa" :class="[
              comment.children.length
              ?
              expanded
                ?'fa-chevron-left fa-rotate-90'
                :'fa-chevron-right fa-rotate-90'
              :null]"
                 @click="toggleExpanded"
              :title="expanded?$t('comments.collapse'):$t('comments.expand')"
              :aria-label="expanded?$t('comments.collapse'):$t('comments.expand')"></i>
            </div>
          </div>
        </div>
        <div :id="comment.slug" class="comment-body">
          <span v-if="comment.edit_mode!==2" v-html="comment.txt"></span>
          <comment-editor v-else :edit-mode="comment.edit_mode"
                          @cancelled="updateEditMode(idx)"
                          @submitted="updateComment(idx,$event)"
                          :contents="comment.txt"
                          :entity-slug="slug"
                          :comment-slug="comment.slug"
                          :search-url="searchUrl"></comment-editor>
        </div>
        <div class="comment-footer" v-if="authCheck">
          <button v-if="!comment.edit_mode" type="button"
                  class="btn btn-outline-info pull-left"
                  @click="comment.edit_mode=1"><i class="fa fa-reply fa-rotate-180"></i> {{$t('comments.reply')}}
          </button>
          <div class="pull-right">
            <button v-if="!comment.edit_mode&&comment.owns" type="button"
                    class="btn btn-sm btn-outline-info mr-2"
                    :title="$t('comments.edit')"
                    :aria-label="$t('comments.edit')"
                    @click="comment.edit_mode=2"><i class="fa fa-pencil"></i>
            </button>
            <button v-if="!comment.edit_mode&&comment.owns" type="button"
                    class="btn btn-sm btn-outline-danger"
                    :title="$t('comments.delete')"
                    :aria-label="$t('comments.delete')"
                    @click="deleteComment(comment.slug,idx)"><i class="fa fa-trash"></i>
            </button>
          </div>
          <comment-editor v-if="comment.edit_mode===1" :edit-mode="comment.edit_mode"
                          @cancelled="updateEditMode(idx)"
                          @submitted="updateEditMode(idx)"
                          :entity-slug="slug"
                          :comment-slug="comment.slug"
                          :search-url="searchUrl"></comment-editor>
        </div>
        <comment-list :comments="comment.children" v-if="expanded"
                      :auth-check="authCheck"
                      :slug="slug"
                      :search-url="searchUrl"></comment-list>
      </div>
    </div>
  </transition-group>
</template>

<script>
  import swal from 'sweetalert2/dist/sweetalert2.js'

  export default {
    name: 'comment-list',
    props: {
      comments: {required: true},
      authCheck: {required: true, type: Boolean},
      slug: {required: true, type: String},
      searchUrl: {required: true, type: String}
    },
    components: {
      CommentEditor: () => import('front_path/components/CommentEditor')
    },
    data () {
      return {
        cssAnimationsActivated: false,
        hash: window.location.hash.substr(1),
        expanded: true
      }
    },
    mounted () {
      let el = document.getElementById(this.hash)
      if (el) {
        el.scrollIntoView()
      }

      let vm = this
      //making sure css animations don't trigger on load
      setTimeout(() => {
        vm.cssAnimationsActivated = true
      }, 2000)

    },
    methods: {
      toggleExpanded () {
        this.expanded = !this.expanded
      },
      recommend (comment, index) {
        if (!this.authCheck) {
          return
        }
        if (this.comments[index].fav === true) {
          this.comments[index].fav = false
          this.comments[index].cnt--
          this.$root.$emit('commentUnfavorited', comment)
        } else {
          this.comments[index].fav = true
          this.comments[index].cnt++
          this.$root.$emit('commentFavorited', comment)
        }
      },
      deleteComment (slug, index) {
        let vm = this
        swal.fire({
          title: vm.$root.$t('comments.delete_confirm_h'),
          text: vm.$root.$t('comments.delete_confirm_t'),
          type: 'question',
          showCancelButton: true,
          confirmButtonColor: '#4b0f09',
          cancelButtonColor: '#616161',
          confirmButtonText: vm.$root.$t('comments.delete_confirm_c')
        }).then(async (result) => {
          if (result.value) {
            this.comments.splice(index, 1)
            vm.$root.$emit('commentDeleted', {slug: slug})
          }
        })
      },
      updateComment (index, event) {
        this.comments[index].edit_mode = false
        this.comments[index].txt = event.txt
      },
      updateEditMode (index) {
        this.comments[index].edit_mode = false
      }
    }
  }
</script>