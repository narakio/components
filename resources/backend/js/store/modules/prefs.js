import Cookies from 'js-cookie'
import * as types from '../mutation-types'

const locales = {
  'en': 'en',
  'fr': 'fr'
}
const dateFormat = {
  'en': {date: 'yyyy-MM-dd', dateTime: 'MMMM DD, YYYY @ HH:mm'},
  'fr': {date: 'dd-MM-yyyy', dateTime: 'DD MMMM YYYY @ HH:mm'}
}

const {locale} = window.config

// state
export const state = {
  locale: Cookies.get('locale') || locale,
  locales: locales,
  fallback: 'en',
  dateFormat: dateFormat[locale]['date'],
  dateTimeFormat: dateFormat[locale]['dateTime']
}

// getters
export const getters = {
  locale: state => state.locale,
  locales: state => state.locales,
  fallback: state => state.fallback,
  dateFormat: state => state.dateFormat,
  dateTimeFormat: state => state.dateTimeFormat
}

// mutations
export const mutations = {
  [types.SET_LOCALE] (state, {locale}) {
    state.locale = locale
    state.dateFormat = dateFormat[locale]['date']
    state.dateFormat = dateFormat[locale]['dateTime']
  }
}

// actions
export const actions = {
  setLocale ({commit}, {locale}) {
    commit(types.SET_LOCALE, {locale})
    Cookies.set('locale', locale, {expires: 365})
  }
}
