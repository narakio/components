<template>
  <div id="blog-post" class="container p-0 m-0">
    <div id="trumbowyg-icons" v-html="require('trumbowyg/dist/ui/icons.svg')"></div>
    <form @submit.prevent="save" id="form_edit_blog">
      <div class="row p-0 m-0 my-1">
        <div class="card col-lg">
          <div class="container">
            <div class="form-group row col-lg mt-3">
              <div class="col-lg-10">
                <input v-model="form.fields.blog_post_title" type="text" required autocomplete="off"
                       name="blog_post_title"
                       id="blog-post-title"
                       class="form-control" maxlength="255"
                       :class="{ 'is-invalid': form.errors.has('blog_post_title') }"
                       :placeholder="$t('db.blog_post_title')"
                       aria-describedby="help_blog_post_title"
                       @change="changedField('blog_post_title')">
                <div class="blog-post-title-length">{{form.fields.blog_post_title.length}}</div>
                <small class="text-muted blog-post-url d-block mt-1" v-show="url">
                  <template v-if="!form_url_editing">
                    <template v-if="saveMode!=='create'">
                      <button type="button" class="btn btn-sm" @click="toggleEditing('form_url_editing')">
                        <i class="fa fa-pencil"></i>
                      </button>
                      <a :href="url" target="_blank">{{url}}</a>
                    </template>
                  </template>
                  <template v-else>
                    <button type="button" class="btn btn-sm" @click="toggleEditing('form_url_editing')">
                      <i class="fa fa-ban"></i>
                    </button>
                    <submit-button native-type="button" class="btn-sm" @click="saveUrl">
                      <i class="fa fa-check"></i>
                    </submit-button>
                    <input type="text" v-model="form.fields.blog_post_slug"
                           class="w-75 ml-2"
                           @change="changedField('blog_post_slug')">
                  </template>
                </small>
              </div>
              <div class="col-lg-2">
                <submit-button :loading="form.busy"
                               class="float-lg-right">{{$t('general.save')}}
                </submit-button>
              </div>
            </div>
            <div id="head-row" class="form-group row">
              <div class="col-lg-6" id="head-col-draft">
                <template v-if="form_status_editing">
                  <select v-model="form.fields.blog_status"
                          @change="changedField('blog_status')"
                          class="custom-control custom-select">
                    <option v-for="(idx,status) in status_list" :key="idx" :value="status"
                    >{{$t(`constants.${status}`)}}
                    </option>
                  </select>
                  <button type="button"
                          class="btn btn-success btn-small"
                          @click="toggleEditing('form_status_editing')">
                    <i class="fa fa-check"></i>
                  </button>
                  <button type="button"
                          class="btn btn-light btn-small"
                          @click="toggleEditing('form_status_editing')">
                    <i class="fa fa-ban"></i>
                  </button>
                </template>
                <template v-else>
                  <span>{{$t('general.status')}}: </span>
                  <span class="form-field-togglable"
                        @click="toggleEditing('form_status_editing')"
                  >{{ ($te(`constants.${form.fields.blog_status}`))?
                    $t(`constants.${form.fields.blog_status}`):
                    ''}}</span>
                </template>
              </div>
              <div class="col-lg-6 justify-content-center">
                <div v-if="form_user_editing" class="container m-0 p-0">
                  <div class="row">
                    <div class="m-0 p-0 col-lg d-inline-flex">
                      <div class="container d-inline-flex">
                        <input-tag-search :typeahead="true"
                                          :placeholder="$t('pages.members.member_search')"
                                          :searchUrl="'/ajax/admin/people/search'"
                                          @updateAddedItems="updatePeople"
                                          limit="1"/>
                        <button type="button"
                                class="btn btn-success btn-small"
                                @click="toggleEditing('form_user_editing')">
                          <i class="fa fa-check"></i>
                        </button>
                        <button type="button"
                                class="btn btn-light btn-small"
                                @click="toggleEditing('form_user_editing')">
                          <i class="fa fa-ban"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <template v-else>
                  <span>{{$t('pages.blog.author')}}: </span>
                  <span class="form-field-togglable"
                        @click="toggleEditing('form_user_editing')"
                  >{{blog_post_person}}</span>
                </template>
              </div>
            </div>
            <div class="form-group row col-lg mb-3 pl-2" style="min-height:2.5rem">
              <div class="col-lg align-items-center">
                <div class="row col" v-if="form_publish_date_editing">
                  <datepicker
                      v-model="current_publish_date"
                      :name="'published_at'"
                      :show-clear-button="false"
                      :show-on-start="true"
                      @closed="changePublishDate"></datepicker>
                  <div class="form-inline">
                    <input class="form-control input-hour-minute"
                           :value="format(current_publish_date,'HH')"
                           ref="inputHours"
                    >:<input class="form-control input-hour-minute"
                             ref="inputMinutes"
                             :value="format(current_publish_date,'mm')">
                  </div>
                  <button type="button"
                          class="btn btn-success btn-small"
                          @click="validateAndGo">
                    <i class="fa fa-check"></i>
                  </button>
                  <button type="button"
                          class="btn btn-light btn-small"
                          @click="toggleEditing('form_publish_date_editing')">
                    <i class="fa fa-ban"></i>
                  </button>
                </div>
                <div class="col pl-0" v-else>
                  <span id="span-published-at">{{$t('pages.blog.published_at')}}&nbsp;<span
                      id="span-nice-datetime"
                      @click="toggleEditing('form_publish_date_editing')">{{formattedPublishedAt}}</span></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row p-0 m-0 my-1">
        <div class="card col-lg p-0 m-0">
          <div class="row p-0 m-0">
            <trumbowyg v-model="form.fields.blog_post_content" :config="editorConfig"
                       ref="inputBlogPostContent"
                       class="form-control"
                       name="content"></trumbowyg>
          </div>
          <div class="row p-0 m-0 input-tag-wrapper">
            <span class="badge badge-pill badge-light"
                  @click.prevent="removeTag(index)"
                  v-for="(badge, index) in form.fields.tags"
                  :key="index">
              <span v-html="badge"></span>
              <i class="fa fa-tag badge-tag-icon"></i>
            </span>
            <input type="text"
                   ref="tag"
                   v-model="tagInput"
                   @keydown.enter.prevent="addTag"
                   maxlength="35"
                   :placeholder="$t('pages.blog.add_tag_pholder')"/>
          </div>
        </div>
      </div>
      <div class="row p-0 m-0">
        <div class="card col-lg p-0 m-0">
          <div class="card-body">
            <image-uploader
                :target="form.fields.blog_post_slug"
                entity="blog_posts"
                media="image"
                :is-active="this.saveMode==='edit'"
                :thumbnails-parent="thumbnails"
                @images-updated="updateThumbnails"/>
          </div>
        </div>
      </div>
      <div class="row p-0 m-0">
        <div class="card col-lg p-0 m-0">
          <div class="card-body justify-content-md-center">
            <div class="col p-0 m-0">
              <div class="container">
                <div class="row form-inline blog-source-form">
                  <!--<div class="form-group info-icon"  :title="$t('pages.blog.sources_info')" v-b-tooltip.hover>-->
                  <!--<i class="fa fa-circle fa-stack-1x"></i>-->
                  <!--<i class="fa fa-info fa-stack-1x fa-inverse"></i>-->
                  <!--</div>-->
                  <div class="form-group w-25 pr-2">
                    <select v-model='newSource.type' class="form-control w-100">
                      <option v-for="(type, idx) in source_types" :key="'st_'+idx" :value="idx">{{type}}</option>
                    </select>
                  </div>
                  <div class="form-group w-50 pr-2">
                    <input v-model='newSource.content' class="form-control w-100">
                  </div>
                  <div class="form-group w-25">
                    <submit-button :native-type="'button'" :loading="loadingButton===0"
                                   @click="addSource">{{$t('pages.blog.add_source_button')}}
                    </submit-button>
                  </div>
                </div>
                <div class="row">
                  <div class="container blog-sources">
                    <div v-for="(source, idx) in sources" class="row" :key="'source'+idx">
                      <div class="txt-container col-lg-10">
                        <i v-if="source.type=='url'" class="fa fa-link"></i>
                        <i v-else-if="source.type=='img'" class="fa fa-image"></i>
                        <i v-else class="fa fa-file"></i><a target="_blank" :href="source.source">{{source.source}}</a>
                      </div>
                      <div class="col-lg-2">
                        <submit-button :type="'sm'" class="btn-danger" :loading="loadingButton===idx+1"
                                       :native-type="'button'" @click="deleteSource(source.record,idx+1)">
                          <i class="fa fa-trash"></i>
                        </submit-button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row p-0 m-0 my-1">
        <div class="card col-lg-6 p-0 m-0">
          <div class="card-header bg-transparent">{{$t('pages.blog.blog_post_excerpt')}}</div>
          <div class="card-body">
            <div class="form-group">
              <label for="blog_post_excerpt"></label>
              <textarea class="form-control" name="blog_post_excerpt"
                        id="blog_post_excerpt" rows="5" v-model="form.fields.blog_post_excerpt"
                        placeholder="Post Excerpt"
                        @input="changedField('blog_post_excerpt')"></textarea>
              <small id="help_new_group_name" class="text-muted"
              >{{$t('pages.blog.excerpt_label')}}
              </small>
            </div>
          </div>
        </div>
        <div class="card col-lg-6 p-0 m-0">
          <div class="card-header bg-transparent">{{$t('pages.blog.categories')}}</div>
          <div class="card-body">
            <div class="mini-tree-list-container container">
              <div class="row">
                <tree-list :data="this.blog_categories"
                           :edit-mode="false"
                           :add-root-button-label="$t('pages.blog.add_root_button')"
                           @tree-selected="categorySelected"/>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
  import Vue from 'vue'
  import axios from 'axios'
  import dayjs from 'dayjs'
  import fr from 'dayjs/locale/fr'
  import SubmitButton from 'back_path/components/SubmitButton'
  import Trumbowyg from 'back_path/components/wysiwyg/Trumbowyg'
  import { Form, HasError } from 'back_path/components/form'
  import TreeList from 'back_path/components/tree-list/TreeList'
  import InputTagSearch from 'back_path/components/InputTagSearch'
  import Datepicker from 'back_path/components/Datepicker'
  import ImageUploader from 'back_path/components/ImageUploader'
  import Tooltip from 'bootstrap-vue/es/directives/tooltip/tooltip'

  Vue.directive('b-tooltip', Tooltip)

  import swal from 'back_path/mixins/sweet-alert'
  import form from 'back_path/mixins/form'

  export default {
    layout: 'basic',
    middleware: 'check-auth',
    name: 'blog-post-add',
    components: {
      SubmitButton,
      HasError,
      Trumbowyg,
      InputTagSearch,
      TreeList,
      ImageUploader,
      Datepicker
    },
    directives: {
      Tooltip
    },
    mixins: [
      swal,
      form
    ],
    data () {
      return {
        modal_show: false,
        form_status_editing: false,
        form_url_editing: false,
        form_user_editing: false,
        form_publish_date_editing: false,
        modal: false,
        editorConfig: this.getConfig(),
        status_list: null,
        current_status: '',
        current_publish_date: new Date(),
        blog_post_person: null,
        url: null,
        saveMode: null,
        blog_categories: [],
        tagInput: '',
        thumbnails: [],
        source_types: [],
        sources: [],
        loadingButton: null,
        newSource: {type: 1, content: null},
        form: new Form({
          blog_post_content: '',
          blog_post_title: '',
          blog_status: '',
          blog_post_person: '',
          categories: [],
          tags: []
        })
      }
    },
    computed: {
      formattedPublishedAt () {
        return dayjs(this.current_publish_date)
          .locale(this.$store.getters['prefs/locale'])
          .format(this.$store.getters['prefs/dateTimeFormat'])
      }
    },
    watch: {
      current_publish_date (value) {
        this.form.fields.published_at = value
      }
    },
    mounted () {
      let vm = this
      //We have to set a listener like this because the input event is emitted because the input field
      //might be populated with text that is not user input and trigger the changedField method prematurely
      this.$refs.inputBlogPostContent.$on('tbw-init', () => {
        vm.$refs.inputBlogPostContent.$on('input', () => {
          vm.changedField('blog_post_content')
        })
      })
      // window.addEventListener('beforeunload', this.checkBeforeUnload, {once: true})
    },
    beforeDestroy () {
      // window.removeEventListener('beforeunload', this.checkBeforeUnload)
    },
    methods: {
      async deleteSource (record, index) {
        this.loadingButton = index
        const {data} = await axios.delete(
          `/ajax/admin/blog/post/source/delete/${record}/${this.form.fields.blog_post_slug}`)
        this.sources = data.sources
        this.loadingButton = null
      },
      async addSource () {
        this.loadingButton = 0
        if (this.newSource.content) {
          const {data} = await axios.post('/ajax/admin/blog/post/source/create', {
            content: this.newSource.content,
            type: this.newSource.type,
            blog_slug: this.form.fields.blog_post_slug
          })
          this.sources = data.sources
          this.loadingButton = null
        }
      },
      checkBeforeUnload (event) {
        let confirmationMessage = '_'
        event.returnValue = confirmationMessage
        return confirmationMessage
      },
      format (value, format) {
        return dayjs(value).format(format)
      },
      validateAndGo () {
        let currentDate = dayjs(this.current_publish_date).format('YYYY-MM-DD')
        let dateTime = dayjs(currentDate + ' ' + this.$refs.inputHours.value + ':' + this.$refs.inputMinutes.value)

        if (dateTime.isValid()) {
          this.current_publish_date = dateTime.toDate()
          this.toggleEditing('form_publish_date_editing')
          this.changedField('published_at')
        }
      },
      updateThumbnails (data) {
        this.thumbnails = data
      },
      addTag () {
        if (this.tagInput) {
          this.form.fields.tags.push(this.tagInput)
          this.tagInput = ''
          this.$refs.tag.focus()
          this.changedField('tags')
        }
      },
      changePublishDate (value) {
        this.current_publish_date = dayjs(
          dayjs(value).format('YYYY-MM-DD') + ' ' +
          this.$refs.inputHours.value + ':' +
          this.$refs.inputMinutes.value).toDate()
        this.changedField('published_at')
      },
      removeTag (index) {
        this.form.fields.tags.splice(index, 1)
        this.$refs.tag.focus()
        this.changedField('tags')
      },
      categorySelected (val, mode) {
        if (mode === 'add') {
          if (this.form.fields.categories.indexOf(val) === -1) {
            this.form.fields.categories.push(val)
          }
        } else {
          let i = this.form.fields.categories.indexOf(val)
          if (i > -1) {
            this.form.fields.categories.splice(i, 1)
          }
        }
        this.changedField('categories')
      },
      getConfig () {
        return {
          semantic: false,
          btns: [
            ['viewHTML'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['bbCode'],
            ['fullscreen']
          ],
          plugins: {}
        }
      },
      updatePeople (people) {
        if (people.length > 0) {
          this.form.fields.blog_post_person = people[0].id
          this.blog_post_person = people[0].text
          this.changedField('blog_post_person')
        }
      },
      async saveUrl () {
        if (this.form.hasFieldChanged('blog_post_slug')) {
          let {data} = await this.form.post(
            `/ajax/admin/blog/post/url/edit/${this.$route.params.slug}`,
            {blog_post_url: this.form.fields.blog_post_slug}
          )
          this.url = data.url
          window.history.replaceState({}, null, this.$router.resolve(
            {name: 'admin.blog_posts.edit', params: {slug: this.form.fields.blog_post_slug}}).resolved.fullPath)
        }
        this.toggleEditing('form_url_editing')
      },
      toggleEditing (value) {
        this[value] = !this[value]
      },
      async save () {
        if (!this.form.fields.blog_post_title || !this.form.hasDetectedChanges()) {
          return
        }
        let verb, msg
        let route = this.$route
        if ((route.name.lastIndexOf('add') > 0)) {
          this.saveMode = verb = 'create'
          msg = this.$t('pages.blog.add_success')
        } else {
          this.saveMode = 'edit'
          verb = `${this.saveMode}/${route.params.slug}`
          msg = this.$t('pages.blog.save_success')
        }
        this.form.fields.published_at = dayjs(this.form.fields.published_at).format('YYYYMMDDHHmm')
        let formBeforeSave = this.form.getFormData()
        let {data} = await this.form.post(`/ajax/admin/blog/post/${verb}`)
        this.form = new Form(formBeforeSave, true)
        this.url = data.url
        if (this.saveMode === 'create') {
          this.form.addField('blog_post_slug', data.blog_post_slug)
          this.$router.replace({name: 'admin.blog_posts.edit', params: {slug: data.blog_post_slug}})
        } else {
          this.form.fields.blog_post_slug = data.blog_post_slug
        }
        this.swalNotification('success', msg)
      },
      getInfo (data, saveMode) {
        this.form = new Form(data.record)
        if (saveMode === 'create') {
          this.form.addField('published_at', new Date())
        } else {
          this.form.setTrackChanges(true)
          this.current_publish_date = dayjs(this.form.fields.published_at).toDate()
        }
        this.blog_post_person = this.form.fields.blog_post_person
        this.form.fields.blog_post_person = this.form.fields.person_slug
        delete (this.form.fields.person_slug)
        this.status_list = data.status_list
        this.url = data.url
        this.current_status = this.$t(`constants.${data.record.blog_status}`)
        this.saveMode = saveMode
        this.blog_categories = data.blog_categories
        this.thumbnails = data.thumbnails
        this.source_types = data.source_types
        this.sources = data.sources
      }
    },
    metaInfo () {
      return {title: this.$t('title.blog_add')}
    },
    beforeRouteEnter (to, from, next) {
      let suffix, saveMode
      if ((to.name.lastIndexOf('add') > 0)) {
        saveMode = suffix = 'create'
      } else {
        saveMode = 'edit'
        suffix = `${saveMode}/${to.params.slug}`
      }

      let url = `/ajax/admin/blog/post/${suffix}`
      axios.get(url).then(({data}) => {
        next(vm => vm.getInfo(data, saveMode))
      })
    },
    beforeRouteLeave (to, from, next) {
      if (this.form.hasDetectedChanges()) {
        this.swalSaveWarning().then((result) => {
          if (result.value) {
            next()
          } else {
            next(false)
          }
        })
      } else {
        next()
      }
    }
  }
</script>