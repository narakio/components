import axios from 'axios'
import store from 'back_path/store'
import router from 'back_path/router'
import swal from 'sweetalert2/dist/sweetalert2.js'
import i18n from 'back_path/plugins/i18n'

// Request interceptor
axios.interceptors.request.use(request => {
  const token = store.getters['auth/token']
  if (token) {
    request.headers.common['Authorization'] = `Bearer ${token}`
  }

  const locale = store.getters['prefs/locale']
  if (locale) {
    request.headers.common['Accept-Language'] = locale
  }
  return request
})

// Response interceptor
axios.interceptors.response.use(response => response, error => {
  const {status} = error.response
  let text
  if (error.response.data && error.response.data.length > 0) {
    text = error.response.data
  } else {
    text = i18n.t('modal.error.t')
  }
  router.app.$loading.fail()
  router.app.$loading.finish()

  if (status >= 500) {
    let settings = {
      type: 'error',
      title: i18n.t('modal.error.h'),
      text: text,
      reverseButtons: true,
      confirmButtonText: i18n.t('general.ok')
    }
    swal.fire(settings)
  }

  if (status === 401 && store.getters['auth/check']) {
    swal.fire({
      type: 'warning',
      title: i18n.t('modal.token_expired.h'),
      text: i18n.t('modal.token_expired.t'),
      reverseButtons: true,
      confirmButtonText: i18n.t('general.ok'),
      cancelButtonText: i18n.t('general.cancel')
    }).then(async () => {
      await store.dispatch('auth/revokeUser')
      router.push({name: 'admin.login'})
    })
  }

  if (status === 403) {
    swal.fire({
      type: 'error',
      title: i18n.t('modal.unauthorized.h'),
      text: i18n.t('modal.unauthorized.t'),
      reverseButtons: true,
      confirmButtonText: i18n.t('general.ok')
    })
  }

  return Promise.reject(error)
})
