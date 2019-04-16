<template>
  <button class="btn btn-dark" :class="[provider+'-bg']" type="button" @click="login">
    <i :class="`fa fa-${provider}`"></i>
    {{ $t(`login-o-auth.login_with_${provider}`) }}
  </button>
</template>

<script>
  import axios from 'axios'

  export default {
    name: 'login-o-auth',
    props: {
      provider: {required: true, type: String}
    },
    mounted () {
      this.$nextTick(() => {
        window.addEventListener('message', this.onMessage, false)
      })
    },
    beforeDestroy () {
      window.removeEventListener('message', this.onMessage)
    },
    methods: {
      async login () {
        const newWindow = openWindow('', 'Login')
        const {data} = await axios.post(`/oauth/${this.provider}`)
        newWindow.location.href = data
      },
      onMessage (e) {
        if (e.data.hasOwnProperty('route')) {
          window.location.href = e.data.route
        }
      }
    }
  }

  function openWindow (url, title, options = {}) {
    if (typeof url === 'object') {
      options = url
      url = ''
    }

    options = {url, title, width: 600, height: 720, ...options}

    const dualScreenLeft = window.screenLeft !== undefined ? window.screenLeft : window.screen.left
    const dualScreenTop = window.screenTop !== undefined ? window.screenTop : window.screen.top
    const width = window.innerWidth || document.documentElement.clientWidth || window.screen.width
    const height = window.innerHeight || document.documentElement.clientHeight || window.screen.height

    options.left = ((width / 2) - (options.width / 2)) + dualScreenLeft
    options.top = ((height / 2) - (options.height / 2)) + dualScreenTop

    const optionsStr = Object.keys(options).reduce((acc, key) => {
      acc.push(`${key}=${options[key]}`)
      return acc
    }, []).join(',')

    const newWindow = window.open(url, title, optionsStr)

    if (window.focus) {
      newWindow.focus()
    }

    return newWindow
  }
</script>
