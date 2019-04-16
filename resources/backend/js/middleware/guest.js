import store from 'back_path/store'

export default (to, from, next) => {
  if (store.getters['auth/check']) {
    next({name: 'admin.dashboard'})
  } else {
    next()
  }
}
