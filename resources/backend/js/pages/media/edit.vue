<template>
  <div class="card" ref="cropperContainer">
    <form @submit.prevent="save" @keydown="form.onKeydown($event)">
      <b-tabs card>
        <b-tab :title="$t('general.media')" active>
          <div class="col-md-8 offset-md-2">
            <div class="form-group row">
              <div class="col-md-9 offset-md-3">
                <div class="col-md-6 offset-md-3">
                  <img class="thumbnail"
                       :src="getImageUrl(form.fields.media_uuid, form.fields.suffix, form.fields.media_extension)"></img>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label for="created_at_pretty"
                     class="col-md-3 col-form-label">{{$t('general.uploaded_on')}}</label>
              <div class="col-md-9">
                <input v-model="form.fields.created_at_pretty" type="text"
                       id="created_at_pretty" class="form-control" disabled>
              </div>
            </div>
            <div class="form-group row">
              <label for="title"
                     class="col-md-3 col-form-label">{{$t('pages.media.attached_to')}}</label>
              <div class="col-md-9">
                <input type="text"
                       id="title" class="form-control"
                       :value="`${form.fields.entity_pretty} - ${form.fields.title}`"
                       disabled>
              </div>
            </div>
            <div class="form-group row">
              <label for="media_title" class="col-md-3 col-form-label">{{$t('db.media_title')}}</label>
              <div class="col-md-9">
                <input v-model="form.fields.media_title" type="text"
                       name="media_title" id="media_title" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('media_title') }"
                       :placeholder="$t('db.media_title')"
                       aria-describedby="help_media_title"
                       autocomplete="off"
                       @change="changedField('media_title')">
                <has-error :form="form" field="media_title"></has-error>
                <small id="help_media_title"
                       class="text-muted">{{$t('form.description.media_title',[form.media_title])}}
                </small>
              </div>
            </div>
            <div class="form-group row">
              <label for="media_alt" class="col-md-3 col-form-label">{{$t('db.media_alt')}}</label>
              <div class="col-md-9">
                <input v-model="form.fields.media_alt" type="text"
                       name="media_alt" id="media_alt" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('media_alt') }"
                       :placeholder="$t('db.media_alt')"
                       aria-describedby="help_media_alt" autocomplete="off"
                       @change="changedField('media_alt')">
                <has-error :form="form" field="media_alt"></has-error>
                <small id="help_media_alt"
                       class="text-muted">{{$t('form.description.media_alt',[form.media_alt])}}
                </small>
              </div>
            </div>
            <div class="form-group row">
              <label for="media_description" class="col-md-3 col-form-label">{{$t('db.media_description')}}</label>
              <div class="col-md-9">
                  <textarea v-model="form.fields.media_description"
                            name="media_description" id="media_description"
                            class="form-control txtarea-noresize"
                            :class="{ 'is-invalid': form.errors.has('media_description') }"
                            :placeholder="$t('db.media_description')"
                            aria-describedby="help_media_description"
                            rows="4"
                            @change="changedField('media_description')"></textarea>
                <has-error :form="form" field="media_description"></has-error>
                <small id="help_media_description"
                       class="text-muted">
                  {{$t('form.description.media_description',[form.media_description])}}
                </small>
              </div>
            </div>
            <div class="form-group row">
              <label for="media_caption"
                     class="col-md-3 col-form-label">{{$t('db.media_caption')}}</label>
              <div class="col-md-9">
                  <textarea v-model="form.fields.media_caption"
                            name="media_caption" id="media_caption" class="form-control txtarea-noresize"
                            :class="{ 'is-invalid': form.errors.has('media_caption') }"
                            :placeholder="$t('db.media_caption')"
                            aria-describedby="help_media_caption"
                            rows="4"
                            @change="changedField('media_caption')"></textarea>
                <has-error :form="form" field="media_caption"></has-error>
                <small id="help_media_caption"
                       class="text-muted">{{$t('form.description.media_caption',[form.media_caption])}}
                </small>
              </div>
            </div>
          </div>
          <div class="row justify-content-center mb-4 mt-2">
            <div>
              <submit-button class="align-content-center"
                             :loading="form.busy">{{ $t('general.update') }}
              </submit-button>
              <button type="button"
                      class="btn btn-secondary"
                      @click="returnToPage">{{$t('general.cancel')}}
              </button>
            </div>
          </div>
          <div class="row justify-content-center">
            <div>
              <record-paginator
                  :nav="nav"
                  :is-loading="ajaxIsLoading"
                  route-name="admin.media.edit"
                  route-param-name="media"></record-paginator>
            </div>
          </div>
        </b-tab>
        <b-tab :title="$t('general.crop')">
          <div v-html="$t('pages.media.formats_help')" class="py-2"></div>
          <button-group @active-changed="editCropperDimensions" class="py-2" ref="btnGroupDimensions"
                        :field-name="'formats'"
                        :choices="imgFormats">
            <template
                #choice="props">{{props.row.label}} <span
                v-if="props.row.dimensions.width>0">({{props.row.dimensions.width}}x{{props.row.dimensions.height}})</span>
              <i class="fa"
                 :class="[props.row.exists?'fa-check':'fa-times']"></i></template>
          </button-group>
          <cropper v-if="cropper.active" ref="cropper" class="py-3"
                   :cropper-active="cropper.active"
                   :container-width="containerWidth"
                   :cropped="cropWasTriggered"
                   :crop-width="cropper.width"
                   :crop-height="cropper.height"
                   @cropper-mounted="addCropperListeners"
                   :src="getImageUrl(form.fields.media_uuid, null, form.fields.media_extension)">
            <template #cropper-actions>
              <div class="col-md-4 offset-md-4">
                <submit-button @click="crop()"
                               native-type="button"
                               :block="true"
                               :loading="ajaxIsLoading">{{$t('cropper.crop')}}
                </submit-button>
              </div>
            </template>
          </cropper>
        </b-tab>
      </b-tabs>
    </form>
  </div>
</template>
<script>
  import Vue from 'vue'
  import SubmitButton from 'back_path/components/SubmitButton'
  import axios from 'axios'
  import Cropper from 'back_path/components/Cropper'
  import { Form, HasError } from 'back_path/components/form'
  import { Tabs } from 'bootstrap-vue/es/components'
  import RecordPaginator from 'back_path/components/RecordPaginator'
  import MediaMixin from 'back_path/mixins/media'
  import FormMixin from 'back_path/mixins/form'
  import ButtonGroup from 'back_path/components/ButtonGroup'
  import swal from 'sweetalert2/dist/sweetalert2.js'

  Vue.use(Tabs)

  export default {
    layout: 'basic',
    middleware: 'check-auth',
    name: 'media-edit',
    components: {
      SubmitButton,
      HasError,
      Form,
      RecordPaginator,
      Cropper,
      ButtonGroup
    },
    mixins: [
      MediaMixin,
      FormMixin
    ],
    data () {
      return {
        form: new Form(),
        mediaInfo: {},
        nav: {},
        type: null,
        media: null,
        ajaxIsLoading: false,
        containerWidth: 0,
        cropper: {
          active: false,
          height: 0,
          width: 0,
          filename: null
        },
        entity: '',
        fromEntity: 'media',
        fromPage: null,
        imgFormats: null,
        cropWasTriggered: false,
        selectedImageFormat: 1
      }
    },
    mounted () {
      this.containerWidth = this.$refs.cropperContainer.clientWidth
    },
    watch: {
      '$route' () {
        this.ajaxIsLoading = true
        if (this.form.hasDetectedChanges()) {
          this.sendRequest()
        }
        this.cropper.active = false
        this.$refs.btnGroupDimensions.reset()
        axios.get(`/ajax/admin/media/${this.fromEntity}/${this.$router.currentRoute.params.media}`).then(({data}) => {
          this.getInfo(data)
          this.ajaxIsLoading = false
        })
      }
    },
    methods: {
      async save () {
        this.sendRequest()
        this.returnToPage()
        this.$store.dispatch(
          'session/setFlashMessage',
          {msg: {type: 'success', text: this.$t('message.media_update_ok')}}
        )
      },
      async sendRequest () {
        await this.form.patch(`/ajax/admin/media/${this.form.fields.media_uuid}`)
      },
      addCropperListeners () {
        let vm = this
        this.$refs.cropper.$on('cropper_cropped', function (cp) {
          vm.ajaxIsLoading = true
          axios.post('/ajax/admin/media/crop/image', {
            uuid: vm.form.fields.media_uuid,
            format: parseInt(vm.selectedImageFormat),
            height: cp.height,
            width: cp.width,
            x: cp.x,
            y: cp.y
          }).then(({data}) => {
            vm.imgFormats = data.formats
            vm.ajaxIsLoading = false
            vm.cropWasTriggered = false
            swal.fire({
              position: 'top-end',
              toast: true,
              type: 'success',
              title: data.msg,
              showConfirmButton: false,
              timer: 4000
            })
          }).catch(e => {
            vm.ajaxIsLoading = false
          })
        })
      },
      crop () {
        this.cropWasTriggered = true
      },
      editCropperDimensions (index) {
        this.selectedImageFormat = index
        this.cropper.active = true
        if (this.imgFormats[index].dimensions.height > 0) {
          this.cropper.height = this.imgFormats[index].dimensions.height
          this.cropper.width = this.imgFormats[index].dimensions.width
        }
      },
      returnToPage () {
        if (this.fromPage) {
          this.$router.push(this.fromPage)
          return
        } else {
          let intended = this.$store.getters['session/intendedUrl']
          if (intended) {
            this.$router.push(intended)
            return
          }
        }
        this.$router.push({name: 'admin.media.index'})
      },
      getInfo (data, fromEntity, from) {
        this.form = new Form(data.media, true)
        this.media = this.form.fields.media
        this.type = this.form.fields.type
        this.nav = data.nav
        this.imgFormats = data.formats
        this.entity = this.form.fields.entity
        if (fromEntity !== null && fromEntity !== undefined) {
          this.fromEntity = fromEntity
        }
        if (from !== undefined && from.name !== null) {
          this.fromPage = from
        }
      }
    },
    beforeRouteEnter (to, from, next) {
      let fromEntity = 'media'
      if (from.name !== null) {
        let routeConstituents = from.name.split('.')
        fromEntity = routeConstituents[1]
      }
      axios.get(`/ajax/admin/media/${fromEntity}/${to.params.media}`).then(({data}) => {
        next(vm => vm.getInfo(data, fromEntity, {name: from.name, query: from.query, params: from.params}))
      })
    }
  }
</script>