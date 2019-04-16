import * as types from '../mutation-types'
import swal from 'sweetalert2/dist/sweetalert2.js'

export const state = {
  flashMessage: null,
  intendedUrl: null,
  notifications: []
}

export const getters = {
  flashMessage: state => state.flashMessage,
  intendedUrl: state => state.intendedUrl,
  notificationCount: state => state.notifications.length,
  notifications: state => state.notifications
}

export const mutations = {
  [types.SET_INTENDED_URL] (state, {url}) {
    state.intendedUrl = url
  },
  [types.SET_FLASH_MESSAGE] (state, {msg}) {
    state.flashMessage = msg
  },
  [types.CHECK_FLASH_MESSAGE] (state) {
    if (state.flashMessage !== null) {
      swal.fire({
        position: 'top-end',
        toast: true,
        type: state.flashMessage.type,
        title: state.flashMessage.text,
        showConfirmButton: false,
        timer: 4000
      })
      state.flashMessage = null
    }
  },
  [types.NOTIFY] (state, {data}) {
    state.notifications.push(data.received)
    state.notificationCount++
  },
  [types.RESET_NOTIFICATIONS] (state) {
    state.notifications = []
  }
}

export const actions = {
  setIntendedUrl ({commit}, {url}) {
    commit(types.SET_INTENDED_URL, {url})
  },
  setFlashMessage ({commit}, {msg}) {
    commit(types.SET_FLASH_MESSAGE, {msg})
  },
  checkFlashMessage ({commit}) {
    commit(types.CHECK_FLASH_MESSAGE)
  },
  notify ({commit}, {data}) {
    commit(types.NOTIFY, {data})
  },
  resetNotifications ({commit}) {
    commit(types.RESET_NOTIFICATIONS)
  }
}
