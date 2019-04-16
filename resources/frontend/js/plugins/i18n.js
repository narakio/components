import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.use(VueI18n)

const {locale} = window.config

const i18n = new VueI18n({
  locale: locale,
  fallbackLocale: locale,
  messages: {}
})

export async function loadMessages () {
  const messages = await import(/* webpackChunkName: "lang-[request]" */ `../lang/${locale}`)
  i18n.setLocaleMessage(locale, messages)
  return Promise.resolve(locale)
}

export default i18n
