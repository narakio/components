<template>
  <div class="card comment-editor-container">
    <div>
      <tiptap ref="tiptap"
              :edit-mode="currentEditMode"
              :is-root-elem="isRootElem"
              :search-url="searchUrl"
              :content="txtContent"></tiptap>
      <div class="comment-editor-footer">
        <div class="pull-left"><p>{{$t('comments.posting_warning')}}</p></div>
        <div class="pull-right p-2">
          <button type="button" class="btn btn-outline-primary"
                  @click="cancelEditing">{{$t('comments.cancel')}}
          </button>
          <submit-button :native-type="'button'"
                         @click="save"
                         :loading="isLoading">{{$t('comments.submit')}}
          </submit-button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
  import SubmitButton from 'back_path/components/SubmitButton'
  import swal from 'sweetalert2/dist/sweetalert2.js'
  import axios from 'axios'

  export default {
    name: 'comment-editor',
    props: {
      editMode: {required: true},
      contents: {type: String, default: () => null},
      isRootElem: {type: Boolean},
      commentSlug: {type: String, default: () => null},
      entitySlug: {type: String},
      searchUrl: {required: true, type: String}
    },
    components: {
      Tiptap: () => import('front_path/components/Tiptap'),
      SubmitButton
    },
    watch: {
      watch: {
        contents () {
          this.txtContent = this.contents
        }
      }
    },
    computed: {
      txtContent () {
        return this.contents
      },
      currentEditMode () {
        return this.editMode
      }
    },
    data () {
      return {
        isLoading: false
      }
    },
    mounted () {
    },
    methods: {
      cancelEditing () {
        this.$emit('cancelled')
      },
      async save () {
        let vm = this
        this.isLoading = true
        let txt = this.$refs.tiptap.getData()
        let mode = this.currentEditMode === 1 || this.currentEditMode === true ? 'create' : 'edit'
        let response
        if (mode === 'create') {
          await axios.post(`/ajax/forum/blog_posts/${vm.entitySlug}/comment`, {
            reply_to: vm.commentSlug,
            txt: txt
          }).then((response) => {
            vm.fireModal(response.data)
            vm.$refs.tiptap.clearContent()
            vm.$root.$emit('commentSubmitted')
            vm.$emit('submitted', {txt: txt})
          }).catch(() => null)
        } else {
          response = await axios.patch(`/ajax/forum/blog_posts/${vm.entitySlug}/comment`, {
            reply_to: vm.commentSlug,
            txt: txt
          })
          if (typeof response.data != 'undefined') {
            vm.fireModal(response.data)
          }
          this.$emit('submitted', {txt: txt})
        }
        this.isLoading = false
      },
      fireModal (data) {
        swal.fire({
          type: data.type,
          title: data.title,
          position: 'top-end',
          toast: true,
          showConfirmButton: false,
          timer: 8000
        })
      }
    }
  }
</script>