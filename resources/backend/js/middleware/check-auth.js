import store from 'back_path/store'

export default async (to, from, next) => {
  if (!store.getters['auth/check']) {
    next({name: 'admin.login'})
  } else {
    next()
  }
}
