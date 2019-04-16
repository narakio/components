import axios from 'axios'
import swal from 'sweetalert2/dist/sweetalert2.js'
import i18n from 'front_path/plugins/i18n'

axios.interceptors.request.use(request => {
  const {token} = window.config

  request.headers.common['Accept-Language'] = document.querySelector('html')
    .getAttribute('lang')
  request.headers.common['X-Requested-With'] = 'XMLHttpRequest'

  if (token) {
    request.headers.common['Authorization'] = `Bearer ${token}`
  }
  return request
})

axios.interceptors.response.use(response => response, error => {
  const {status} = error.response
  let text
  if (error.response.data && error.response.data.length > 0) {
    text = error.response.data
  } else {
    text = i18n.t('modal.error.t')
  }

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

  if (status === 401) {
    swal.fire({
      type: 'warning',
      title: i18n.t('modal.token_expired.h'),
      text: i18n.t('modal.token_expired.t'),
      showCancelButton: false,
      confirmButtonText: i18n.t('general.ok')
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

  if (status === 422) {
    if (error.response.data.hasOwnProperty('errors')) {
      swal.fire({
        type: 'error',
        title: i18n.t('modal.error.h'),
        text: Object.keys(error.response.data.errors)
          .map(function (k) { return error.response.data.errors[k] })
          .join('<br/>'),
        reverseButtons: true,
        confirmButtonText: i18n.t('general.ok')
      })
    }
  }

  return Promise.reject(error)
})
