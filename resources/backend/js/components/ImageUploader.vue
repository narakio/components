<template>
    <div>
        <b-tabs card>
            <b-tab :title="$t('pages.blog.tab_available')" @click="resetUploadsList">
                <template v-if="thumbnails.length>0">
                    <p class="font-italic">{{$t('pages.blog.click_featured')}}</p>
                </template>
                <div class="thumbnail-group" :class="{'thumbnail-loading':ajaxIsLoading}">
                    <div v-show="ajaxIsLoading" class="fa-5x sync-icon">
                        <i class="fa fa-refresh fa-pulse"></i>
                    </div>
                    <ul class="p-0">
                        <li class="thumbnail-container"
                            v-for="(image,index) in thumbnails"
                            :key="index">
                            <div class="thumbnail-selectable" :class="{'selected':image.used}"
                                 @click="setImageAsUsed(image.uuid,image.used)">
                                <div class="thumbnail-inner">
                                    <img :src="getImageUrl(image.uuid, image.suffix, image.ext)">
                                </div>
                            </div>
                            <div class="thumbnail-controls">
                                <button type="button" class="btn btn-sm"
                                        :class="{'btn-danger':!image.used,'disabled':image.used, 'btn-dark':image.used}"
                                        :title="$t('pages.blog.delete_image')"
                                        @click="deleteImage(image.uuid,image.used)">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info"
                                        :title="$t('pages.blog.edit_image')"
                                        @click="goToEditImagePage(image.uuid)">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-info" :title="$t('avatar-uploader.image_url_copy')"
                                        v-clipboard:copy="getImageUrl(image.uuid, 'ft', image.ext, true)">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </b-tab>
            <b-tab :title="$t('pages.blog.tab_upload')" :disabled="!isActive">
                <dropzone class="dropzone"
                          tag="section"
                          v-bind="dropzoneOptions"
                          :adapter-options="{
                      url: '/ajax/admin/media/add',
                        params:{
                            type:this.entity,
                            target:this.target,
                            media:this.media
                        }
                    }"
                          ref="dropzone">
                    <div class="dz-container" @click="triggerBrowse">
                        <h4 class="dropfile-instructions">{{ $t('dropzone.choose_file')}}</h4>
                        <p class="dropfile-instructions">{{ $t('dropzone.max_size')}}
                            {{maxFilesize}}{{$t('dropzone.units.MB')}}</p>
                        <p class="dropfile-instructions">{{ $t('dropzone.accepted_formats')}} JPG,
                            PNG</p>
                        <i class="fa fa-4x fa-cloud-upload"></i>
                    </div>
                    <template #files="props">
                        <div v-for="(file, i) in props.files" :key="file.id"
                             :class="{'mt-5': i === 0}">
                            <div class="table files previews-container">
                                <div class="file-row template">
                                    <div class="container position-relative">
                                        <div class="row">
                                            <div class="col">
                                                <div class="preview">
                                                    <img v-show="file.dataUrl"
                                                         :src="file.dataUrl"
                                                         :alt="file.name"/>
                                                </div>
                                                <p class="name">{{file.name}}</p>
                                            </div>
                                            <div class="col preview-actions">
                                                <div class="row preview-row">
                                                    <p class="size">
                                                        {{(file.size/1024/1024).toPrecision(3)}}&nbsp;{{$t('dropzone.units.MB')}}</p>
                                                </div>
                                                <div class="row preview-row">
                                                    <div id="dropzone_progress" class="progress">
                                                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                             role="progressbar"
                                                             :style="`width: ${file.upload.progress}%`"
                                                             :aria-valuenow="file.upload.progress"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                                <div class="row preview-row" v-if="file.status==='success'">
                                                    <p>{{$t('pages.blog.image_uploaded')}}</p>
                                                </div>
                                                <div v-else-if="file.status!=='error'" class="row blinker blinker-red">
                                                    <p>{{$t('avatar-uploader.image_uploading')}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="container position-relative mb-2 font-weight-bold">
                                        <div class="col" v-show="file.status==='error'">
                                                        <span
                                                                class="dropzone-error clearfix text-danger"
                                                                v-html="error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </dropzone>
            </b-tab>
        </b-tabs>

    </div>
</template>

<script>
  import Vue from 'vue'
  import { Tabs } from 'bootstrap-vue/es/components'
  import { VueTransmit } from 'vue-transmit'
  import axios from 'axios'
  import media from 'back_path/mixins/media'
  import VueClipboard from 'vue-clipboard2'

  Vue.use(VueClipboard)
  Vue.use(Tabs)

  export default {
    name: 'image-uploader',
    components: {
      'dropzone': VueTransmit,
      Tabs
    },
    mixins: [
      media
    ],
    props: {
      target: {required: true},
      entity: {required: true},
      media: {required: true},
      isActive: {default: true},
      thumbnailsParent: {required: true}
    },
    watch: {
      thumbnailsParent () {
        this.thumbnails = this.thumbnailsParent
      }
    },
    data () {
      return {
        maxFilesize: 2,
        error: null,
        ajaxIsLoading: false,
        thumbnails: [],
        dropzoneOptions: {
          acceptedFileTypes: ['image/jpg', 'image/jpeg', 'image/png'],
          clickable: false,
          maxConcurrentUploads: 1,
          maxFiles: 5
        }
      }
    },
    methods: {
      resetUploadsList () {
        this.$refs.dropzone.removeAllFiles()
      },
      deleteImage (uuid, alreadyUsed) {
        if (!alreadyUsed) {
          this.ajaxIsLoading = true
          axios.delete(`/ajax/admin/blog/post/edit/${this.target}/image/${uuid}`).then(({data}) => {
            this.$emit('images-updated', data)
            this.ajaxIsLoading = false
          })
        }
      },
      setImageAsUsed (uuid, alreadyUsed) {
        if (!alreadyUsed) {
          this.ajaxIsLoading = true
          axios.patch(`/ajax/admin/blog/post/edit/${this.target}/image/${uuid}`).then(({data}) => {
            this.$emit('images-updated', data)
            this.ajaxIsLoading = false
          })
        }
      },
      goToEditImagePage (uuid) {
        this.$router.push({name: 'admin.media.edit', params: {entity:this.entity, media: uuid}})
      },
      triggerBrowse () {
        if (!this.isActive) {
          return
        }
        let vm = this
        this.$refs.dropzone.triggerBrowseFiles()
        this.$refs.dropzone.$on('success', function (file, response) {
          vm.$emit('images-updated', response)
        })
        this.$refs.dropzone.$on('error', function (file, error, xhr) {
          if (typeof xhr.response.msg != 'undefined') {
            vm.error = xhr.response.msg
          } else {
            vm.error = error
          }
        })
      },
    }
  }
</script>