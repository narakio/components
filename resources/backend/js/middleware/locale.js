import store from 'back_path/store'
import { loadMessages } from 'back_path/plugins/i18n'

export default async (to, from, next) => {
  await loadMessages(store.getters['prefs/locale'])

  next()
}
