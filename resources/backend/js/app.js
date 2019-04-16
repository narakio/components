import Vue from 'vue'
import store from 'back_path/store'
import i18n from 'back_path/plugins/i18n'
import router from 'back_path/router'
import App from 'back_path/components/App'

import 'back_path/plugins'

new Vue({
  i18n,
  store,
  router,
  ...App
})
