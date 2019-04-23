const Login = () => import('back_path/pages/login').then(m => m.default || m)

const Dashboard = () => import('back_path/pages/dashboard').then(
  m => m.default || m)

const Users = () => import('back_path/pages/users/index').then(
  m => m.default || m)
const UserAdd = () => import('back_path/pages/users/add').then(
  m => m.default || m)

const Groups = () => import('back_path/pages/groups/index').then(
  m => m.default || m)
const GroupEdit = () => import('back_path/pages/groups/edit').then(
  m => m.default || m)
const GroupAdd = () => import('back_path/pages/groups/add').then(
  m => m.default || m)
const GroupMember = () => import('back_path/pages/groups/member').then(
  m => m.default || m)

const Blog = () => import('back_path/pages/blog/index').then(
  m => m.default || m)
const BlogAdd = () => import('back_path/pages/blog/add').then(
  m => m.default || m)
const BlogCategory = () => import('back_path/pages/blog/category').then(
  m => m.default || m)

const User = () => import('back_path/pages/user/index').then(
  m => m.default || m)
const UserGeneral = () => import('back_path/pages/user/general').then(
  m => m.default || m)
const UserProfile = () => import('back_path/pages/user/profile').then(
  m => m.default || m)
const UserPassword = () => import('back_path/pages/user/password').then(
  m => m.default || m)

const Settings = () => import('back_path/pages/settings/index').then(
  m => m.default || m)
const SettingsGeneral = () => import('back_path/pages/settings/general').then(
  m => m.default || m)
const SettingsSocial = () => import('back_path/pages/settings/social').then(
  m => m.default || m)
const SettingsSitemap = () => import('back_path/pages/settings/sitemap').then(
  m => m.default || m)

const SystemLog = () => import('back_path/pages/system/log').then(
  m => m.default || m)

const Media = () => import('back_path/pages/media/index').then(
  m => m.default || m)
const MediaEdit = () => import('back_path/pages/media/edit').then(
  m => m.default || m)

import routesI18n from 'back_path/lang/routes'
import store from 'back_path/store'

let routes = [
  {
    name: 'admins',
    redirect: {name: 'admin.dashboard'}
  },
  {
    name: 'admin.dashboard',
    component: Dashboard
  },
  {
    name: 'admin.users.index',
    meta: {parent: 'admin.dashboard'},
    component: Users
  },
  {
    name: 'admin.users.add',
    meta: {parent: 'admin.users.index'},
    component: UserAdd
  },
  {
    name: 'admin.users.edit',
    meta: {parent: 'admin.users.index'},
    component: UserAdd
  },
  {
    name: 'admin.groups.index',
    meta: {parent: 'admin.dashboard'},
    component: Groups
  },
  {
    name: 'admin.groups.add',
    meta: {parent: 'admin.groups.index'},
    component: GroupAdd
  },
  {
    name: 'admin.groups.edit',
    meta: {parent: 'admin.groups.index'},
    component: GroupEdit
  },
  {
    name: 'admin.groups.members',
    meta: {parent: 'admin.groups.index'},
    component: GroupMember
  },
  {
    name: 'admin.blog_posts.index',
    meta: {parent: 'admin.dashboard'},
    component: Blog
  },
  {
    name: 'admin.blog_posts.add',
    meta: {parent: 'admin.blog_posts.index'},
    component: BlogAdd
  },
  {
    name: 'admin.blog_posts.edit',
    meta: {parent: 'admin.blog_posts.index'},
    component: BlogAdd
  },
  {
    name: 'admin.blog_posts.category',
    meta: {parent: 'admin.blog_posts.index'},
    component: BlogCategory
  },
  {
    name: 'admin.media.edit',
    meta: {parent: 'admin.dashboard'},
    component: MediaEdit
  },
  {
    name: 'admin.media.index',
    meta: {parent: 'admin.dashboard'},
    component: Media
  },
  {
    path: '',
    component: User,
    meta: {parent: 'admin.dashboard'},
    children: [
      {
        name: 'admin.user',
        redirect: {name: 'admin.user.profile'}
      },
      {
        name: 'admin.user.general',
        component: UserGeneral
      },
      {
        name: 'admin.user.profile',
        component: UserProfile
      },
      {
        name: 'admin.user.password',
        component: UserPassword
      }
    ]
  },
  {
    path: '',
    component: Settings,
    meta: {parent: 'admin.dashboard'},
    children: [
      {
        name: 'admin.settings',
        redirect: {name: 'admin.settings.general'}
      },
      {
        name: 'admin.settings.general',
        component: SettingsGeneral
      },
      {
        name: 'admin.settings.social',
        component: SettingsSocial
      },
      {
        name: 'admin.settings.sitemap',
        component: SettingsSitemap
      },
    ]
  },
  {
    name: 'admin.system.log',
    meta: {parent: 'admin.dashboard'},
    component: SystemLog
  },
  {
    name: 'admin.login',
    component: Login
  },
  {
    path: '*',
    component: require('back_path/pages/errors/404.vue')
  }
]

let locale = store.getters['prefs/locale']
// let isDefaultLocale = (
//   locale === store.getters['lang/fallback']
// )
let prefix = ''
// if (!isDefaultLocale) {
//   prefix += '/' + locale
// }
routes = translateRoute(routes, locale, prefix)

function translateRoute (routes, locale, prefix) {
  for (var i in routes) {
    if (routesI18n[locale].hasOwnProperty(routes[i].name)) {
      routes[i].path = prefix + '/' + routesI18n[locale][routes[i].name]
    }
    if (routes[i].hasOwnProperty('children')) {
      routes[i].children = translateRoute(routes[i].children, locale, prefix)
    }
  }

  return routes
}

export default routes
