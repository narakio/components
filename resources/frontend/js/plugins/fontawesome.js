import Vue from 'vue'
import { library } from '@fortawesome/fontawesome-svg-core'
import {
  faUser,
  faCircle,
  faInfo,
  faCheck,
  faHome,
  faAngleUp,
  faSync,
  faSyncAlt,
  faTrashAlt,
  faCloudUploadAlt,
  faExclamation
} from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

library.add(
  faUser, faCircle, faInfo, faCheck, faHome, faAngleUp, faSync, faSyncAlt,
  faTrashAlt, faCloudUploadAlt, faExclamation
)

Vue.component('fa', FontAwesomeIcon)

