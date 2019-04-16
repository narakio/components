<template>
  <div id="avatar-uploader" class="form-group row">
    <div class="card w-100" ref="cropperContainer">
      <b-tabs card>
        <b-tab :title="$t('avatar-uploader.avatar-tab')" @click="resetComponentFlags" active>
          <p v-show="avatars.length>0" class="font-italic">{{$t('avatar-uploader.click_default')}}</p>
          <div class="thumbnail-group" :class="{'thumbnail-loading':ajaxIsLoading}">
            <div v-show="ajaxIsLoading" class="fa-5x sync-icon">
              <i class="fa fa-refresh fa-pulse"></i>
            </div>
            <ul class="p-0">
              <li class="thumbnail-container"
                  v-for="(avatar,index) in avatars"
                  :key="index">
                <div class="thumbnail-selectable" :class="{'selected':avatar.used}"
                     @click="setAvatarAsUsed(avatar.uuid,avatar.used)">
                  <div class="thumbnail-inner">
                    <img :src="`/media/users/image_avatar/${avatar.uuid}.${avatar.ext}`">
                  </div>
                </div>

                <div class="thumbnail-controls">
                  <button type="button" class="btn btn-sm"
                          :class="{'btn-danger':!avatar.used,'disabled':avatar.used}"
                          :title="$t('avatar-uploader.delete_avatar')"
                          @click="deleteAvatar(avatar.uuid,avatar.used)">
                    <i class="fa fa-trash"></i>
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </b-tab>
        <b-tab :title="$t('avatar-uploader.avatar-ul-tab')" :disabled="avatars.length>6">
          <wizard ref="wizard" :steps="steps" has-step-buttons="false"
                  :current-step-parent="currentStep-1"
                  @wizard-reset="resetComponentFlags">
            <template #s1>
              <div>
                <dropzone class="dropzone"
                          tag="section"
                          v-bind="dropzoneOptions"
                          :adapter-options="{
                                              url: '/ajax/admin/media/add',
                                              headers:extraHeaders,
                                                params:{
                                                    type:'users',
                                                    target:userObj.username,
                                                    media:'image_avatar'
                                                }
                                            }"
                          ref="dropzone">
                  <div class="dz-container" @click="triggerBrowse">
                    <h4 class="dropfile-instructions">{{ $t('dropzone.choose_file')}}</h4>
                    <p class="dropfile-instructions">{{ $t('dropzone.max_size')}}
                      {{dropzoneOptions.maxFilesize}}{{$t('dropzone.units.MB')}}</p>
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
                                <div class="preview"><img v-show="file.dataUrl"
                                                          :src="file.dataUrl"
                                                          :alt="file.name"/></div>
                                <p class="name">{{file.name}}</p>
                              </div>
                              <div class="col preview-actions">
                                <div class="row preview-row">
                                  <p class="size"
                                  >{{(file.size/1024/1024).toPrecision(3)}}&nbsp;{{$t('dropzone.units.MB')}}</p>
                                </div>
                                <div class="row preview-row">
                                  <div id="dropzone_progress" class="progress"
                                       v-show="file.upload.progress<100">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         :style="`width: ${file.upload.progress}%`"
                                         :aria-valuenow="file.upload.progress"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                  </div>
                                </div>
                                <transition name="fade">
                                  <div class="row preview-row"
                                       v-show="file.status==='success'">
                                    <button type="button"
                                            class="btn btn-lg btn-primary text-center"
                                            @click="currentStep=2">
                                      {{$t('avatar-uploader.image_proceed')}}
                                    </button>
                                  </div>
                                </transition>
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
              </div>
            </template>
            <template #s2>
              <div>
                <cropper ref="cropper"
                         :src="cropperOptions.src"
                         :container-width="containerWidth"
                         :crop-height="cropperOptions.minCropBoxHeight"
                         :crop-width="cropperOptions.minCropBoxWidth"
                         :cropped="cropWasTriggered" @cropper-mounted="addCropperListeners">
                  <template #cropper-actions>
                    <div class="col align-self-center">
                      <button class="btn btn-primary" @click="crop()" type="button"
                      >{{$t('cropper.crop_upload')}}
                      </button>
                      <button class="btn btn-light" @click="cancel()" type="button"
                      >{{$t('cropper.cancel')}}
                      </button>
                    </div>
                  </template>
                </cropper>
              </div>
            </template>
            <template #s3>
              <div>
                <div class="container p-0 mt-2">
                  <div class="row justify-content-lg-center">
                    <div class="col col-lg-6 text-center">
                      <img :src="cropperOptions.croppedImageData.dataURI"/>
                    </div>
                  </div>
                  <div class="row justify-content-lg-center mt-3">
                    <div class="col col-lg-6 text-center">
                      <p class="blinker blinker-red" v-if="ajaxIsLoading"
                      >{{$t('avatar-uploader.image_uploading')}}
                      </p>
                      <p v-else>{{$t('avatar-uploader.image_uploaded')}}</p>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </wizard>
        </b-tab>
      </b-tabs>
    </div>
  </div>
</template>
<script>
  import Vue from 'vue'
  import { VueTransmit } from 'vue-transmit'
  import Wizard from 'back_path/components/Wizard'
  import Cropper from 'back_path/components/Cropper'
  import axios from 'axios'
  import { Tabs } from 'bootstrap-vue/es/components'

  Vue.use(Tabs)

  export default {
    name: 'avatar-uploader',
    components: {
      Tabs,
      'dropzone': VueTransmit,
      Wizard,
      Cropper
    },
    props: {
      user: Object,
      avatarsParent: Array,
      extraHeaders: null
    },
    data () {
      return {
        steps: [
          {
            label: 'Upload',
            slot: 's1'
          },
          {
            label: 'Crop',
            slot: 's2'
          },
          {
            label: 'Review',
            slot: 's3'
          }
        ],
        cropperOptions: {
          src: '',
          croppedImageData: {
            dataURI: null,
            filename: null
          },
          minCropBoxHeight: 0,
          minCropBoxWidth: 0,
          uploadSuccess: false
        },
        currentStep: 1,
        ajaxIsLoading: false,
        error: '',
        dropzoneOptions: {
          maxFilesize: 2,
          acceptedFileTypes: ['image/jpg', 'image/jpeg', 'image/png'],
          clickable: false,
          maxConcurrentUploads: 1,
          maxFiles: 10
        },
        containerWidth: 0,
        cropWasTriggered: false,
        avatars: {}
      }
    },
    computed: {
      userObj () {
        if (this.user !== null) {
          return this.user
        } else {
          return {username: null}
        }
      }
    },
    watch: {
      avatarsParent () {
        this.avatars = this.avatarsParent
      }
    },
    mounted () {
      this.avatars = this.avatarsParent
      this.containerWidth = this.$refs.cropperContainer.clientWidth
      let vm = this
      this.$refs.wizard.$on('wizard_step_reset', function () {
        vm.currentStep = 1
      })
    },
    methods: {
      addCropperListeners () {
        let vm = this
        this.$refs.cropper.$on('cropper_cropped', function (cp, croppedCanvas) {
          vm.cropperOptions.croppedImageData.dataURI = croppedCanvas.toDataURL()
          vm.currentStep = 3
          vm.ajaxIsLoading = true
          axios.post('/ajax/admin/media/crop/avatar', {
            uuid: vm.cropperOptions.croppedImageData.filename,
            height: cp.height,
            width: cp.width,
            x: cp.x,
            y: cp.y
          }).then(({data}) => {
            vm.avatars = data
            vm.ajaxIsLoading = false
          }).catch(e => {
            vm.ajaxIsLoading = false
          })
        })
      },
      resetComponentFlags () {
        //After a successful avatar upload, currentStep=3
        // the user can click on the avatar tab in which case we need to reset current step
        // so a new avatar can be uploaded
        if (this.currentStep === 3) {
          this.currentStep = 1
          this.cropWasTriggered = false
        }
      },
      triggerBrowse () {
        let vm = this
        this.cropperOptions.uploadSuccess = false
        this.$refs.dropzone.triggerBrowseFiles()
        this.$refs.dropzone.$on('success', function (file, response) {
          vm.cropperOptions.src = `/media/tmp/${response.filename}`
          vm.cropperOptions.croppedImageData.filename = response.filename
          vm.cropperOptions.minCropBoxHeight = 128
          vm.cropperOptions.minCropBoxWidth = 128
          vm.cropperOptions.uploadSuccess = true
        })
        this.$refs.dropzone.$on('error', function (file, error, xhr) {
          if (typeof xhr != 'undefined') {
            if (xhr.response != null) {
              vm.error = xhr.response.msg
            } else {
              vm.error = error
            }
          } else {
            vm.error = error
          }
        })
      },
      deleteAvatar (uuid, alreadyUsed) {
        if (!alreadyUsed) {
          this.ajaxIsLoading = true
          axios.delete(`/ajax/admin/user/avatar/${uuid}`).then(({data}) => {
            this.avatars = data
            this.ajaxIsLoading = false
          })
        }
      },
      setAvatarAsUsed (uuid, alreadyUsed) {
        if (!alreadyUsed) {
          this.ajaxIsLoading = true
          axios.patch('/ajax/admin/user/avatar', {uuid: uuid}).then(({data}) => {
            this.avatars = data
            this.ajaxIsLoading = false
          })
        }
      },
      crop () {
        this.cropWasTriggered = true
      },
      cancel () {
        this.currentStep = 1
        this.cropWasTriggered = false
      }
    }
  }
</script>