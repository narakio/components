export default {
  name: 'media',
  methods: {
    getImageUrl (uuid, suffix, ext, fullUrl = false) {
      if (typeof uuid === 'undefined') {
        return null
      }
      let string = `/media/${this.entity}/${this.media}/${uuid}`
      if (suffix !== null) {
        string += `_${suffix}.${ext}`
      } else {
        string += `.${ext}`
      }
      return (fullUrl) ? window.location.origin + string : string
    }
  }
}
