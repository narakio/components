import axios from 'axios'
import Cookies from 'js-cookie'
import * as types from '../mutation-types'
import store from 'back_path/store'

const {user, permissions} = window.config

// state
export const state = {
  user,
  permissions,
  token: Cookies.get('token'),
  remember: false
}

// getters
export const getters = {
  user: state => state.user,
  token: state => state.token,
  remember: state => state.remember,
  check: state => state.user !== null,
  shouldBeNotified: state => id => {
    if (state.user !== null) {
      return state.user.events_subscribed.includes(id)
    }
    return false
  },
  permissions: state => state.permissions
}

// mutations
export const mutations = {
  [types.SAVE_TOKEN] (state, {token, remember}) {
    state.token = token
    state.remember = remember
    Cookies.set('token', token, {expires: remember ? 365 : 10})
  },

  [types.LOGOUT] (state) {
    state.user = null
  },

  [types.UPDATE_USER] (state, {user}) {
    state.user = user
  },

  [types.PATCH_USER] (state, data) {
    state.user.events_subscribed = data
  },

  [types.REFRESH_TOKEN] (state, {token}) {
    state.token = token
    Cookies.set('token', token, {expires: state.remember ? 365 : null})
  }
}

// actions
export const actions = {
  saveToken ({commit}, payload) {
    commit(types.SAVE_TOKEN, payload)
  },

  refreshToken ({commit}, payload) {
    commit(types.REFRESH_TOKEN, payload)
  },

  updateUser ({commit}, payload) {
    commit(types.UPDATE_USER, payload)
  },

  patchUser ({commit}, payload) {
    commit(types.PATCH_USER, payload)
  },

  revokeUser ({commit}) {
    commit(types.LOGOUT)
  },

  async logout ({commit}) {
    await axios.post('/admin/logout')
    commit(types.LOGOUT)
    store.dispatch('broadcast/kill')
  }

}
