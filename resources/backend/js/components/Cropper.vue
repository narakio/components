<template>
  <div class="media-resize" ref="imageContainer">
    <div class="resize-instructions container">
      <div class="row">
        <h5 class="text-center">{{$t('cropper.resize_image')}} <span v-if="imgSizeLabel">({{imgSizeLabel}})</span></h5>
        <div class="container p-0">
          <div class="cropper row">
            <img ref="img" class="cropper-img"/>
          </div>
          <div class="cropper-commands row">
            <div class="container p-0">
              <div class="row">
                  <div class="col-md-4 offset-md-4">
                  <button type="button" :title="$t('cropper.reload')"
                          class="btn btn-primary cropper-reset" @click="resetCropper()">
                    <i class="fa fa-refresh"></i>
                  </button>
                  </div>
              </div>
            </div>
          </div>
          <div class="row">
              <p class="cropper-errors text-danger"></p>
          </div>
        </div>
      </div>
      <div class="row">
        <h5 class="text-center">{{$t('cropper.preview')}}</h5>
        <div class="col-xs-12 container-fluid">
          <div class="cropper-preview-wrapper">
            <div class="justify-content-center">
              <figure class="cropper-preview">

              </figure>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-2">
        <div class="container">
          <div class="row">
            <slot name="cropper-actions">
            </slot>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import Cropper from 'cropperjs'

  export default {
    name: 'cropper',
    props: {
      src: {required: true},
      containerWidth: {required: true, type: Number},
      cropHeight: {type: Number, default: () => 0},
      cropWidth: {type: Number, default: () => 0},
      cropperActive: false,
      cropped: {required: false, type: Boolean, default: () => false},
      resizable: {default: () => false}
    },
    data () {
      return {
        cropper: null,
        imgSizeLabel: null,
        imgData: null
      }
    },
    watch: {
      cropperActive () {
        if (this.cropperActive) {
          this.makeCropper()
        } else {
          this.cropper.destroy()
        }
      },
      cropped () {
        if (this.cropped) {
          this.$emit(
            'cropper_cropped',
            this.cropper.getData(true),
            this.cropper.getCroppedCanvas({imageSmoothingQuality: 'high', width: 256, height: 256})
          )
        }
      },
      src () {
        this.$refs.img.setAttribute('src', this.src)
        if (this.cropper) {
          this.cropper.replace(this.src)
        }
        this.setImgSizeLabel()
      },
      cropHeight () {
        this.setCropboxDimensions()
      }
    },
    mounted () {
      if (typeof this.src === 'string') {
        this.$refs.img.setAttribute('src', this.src)
        this.makeCropper()
        this.$emit('cropper-mounted')
      }
    },
    updated () {

    },
    methods: {
      setImgSizeLabel () {
        this.imgSizeLabel = `${this.$i18n.t(
          'cropper.original_size')} ${this.imgData.naturalWidth} x ${this.imgData.naturalHeight}`
      },
      makeCropper () {
        let vm = this
        let containerWidth = Math.round(this.containerWidth * 60 / 100)
        let containerHeight = Math.round(this.containerWidth * 50 / 100)
        this.cropper = new Cropper(this.$refs.img, {
          preview: '.cropper-preview',
          dragMode: 'move',
          viewMode: 1,
          minCanvasWidth: containerWidth - 50,
          minCanvasHeight: containerHeight - 50,
          minContainerWidth: containerWidth,
          minContainerHeight: containerHeight,
          cropBoxResizable: vm.resizable,
          zoomable: true,
          ready: function () {
            vm.imgData = vm.cropper.getImageData()
            vm.setCropboxDimensions()
            vm.setImgSizeLabel()
          }
        })
      },
      resetCropper () {
        this.cropper.reset()
        this.setCropboxDimensions()
      },
      setCropboxDimensions () {
        if (this.cropWidth > 0 && this.cropHeight > 0) {

          let cropboxData = this.cropper.getCropBoxData()
          let aspectRatio = parseFloat(this.cropWidth / this.cropHeight).toFixed(2)

          if (this.cropHeight <= this.imgData.height) {
            cropboxData.height = this.imgData.height
            cropboxData.width = cropboxData.height * aspectRatio
            if (cropboxData.height * aspectRatio >= this.imgData.width) {
              cropboxData.width = this.imgData.width
              cropboxData.height = Math.round(this.imgData.width / aspectRatio)
            }
          } else {
            cropboxData.width = this.imgData.width
            cropboxData.height = Math.round(this.imgData.width / aspectRatio)
          }
          this.cropper.setCropBoxData(cropboxData)
        } else {
          this.cropper.setAspectRatio(1)
        }
      },
      getImageData () {
        return this.cropper.getImageData()
      }
    }
  }
</script>