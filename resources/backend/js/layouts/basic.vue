<template>
  <div id="app_backend_wrapper">
    <navbar></navbar>
    <drawer :menu-items="MenuItems"></drawer>
    <div class="content-wrapper">
      <section class="content-header">
        <div id="breadcrumb-container" class="container">
          <div class="row">
            <div class="link-back" v-if="hasBreadCrumbs()">
              <a @click="$router.go(-1)"><&nbsp;{{$t('general.back')}}</a>
            </div>
            <ol class="breadcrumb">
              <li v-for="(crumb,index) in breadCrumbs" :key="index">
                <template v-if="crumb.route!==$router.currentRoute.name">
                  <router-link
                      :to="{ name: crumb.route }">{{crumb.label}}
                  </router-link>
                </template>
                <template v-else>
                  <span>{{crumb.label}}</span>
                </template>
              </li>
            </ol>
          </div>
        </div>
      </section>
      <section class="content">
        <transition name="page" mode="out-in">
          <div class="container-fluid">
            <slot>
              <router-view></router-view>
            </slot>
          </div>
        </transition>
      </section>
    </div>
  </div>
</template>

<script>
  import Navbar from '../components/Navbar'
  import Drawer from '../components/Drawer'
  import MenuItems from '../menu-items'

  export default {
    name: 'basic',
    components: {
      Navbar,
      Drawer
    },
    data: function () {
      return {
        MenuItems,
        breadCrumbs: []
      }
    },
    watch: {
      '$route' () {
        this.breadCrumbs = this.makeBreadcrumbs(this.$router.currentRoute).reverse()
        this.$store.dispatch('session/checkFlashMessage')
      }
    },
    mounted () {
      this.$store.dispatch('broadcast/init', {
        data: {
          token: this.$store.getters['auth/token'],
          user: this.$store.getters['auth/user']
        }
      })
    },
    methods: {
      hasBreadCrumbs () {
        return this.breadCrumbs.length > 2
      },
      makeBreadcrumbs (route, path = [], child = false) {
        if (!child) {
          path.push(this.translateRouteName(route.name))
          if (typeof route.matched[0] == 'object') {
            if (typeof route.matched[0].meta.parent == 'string') {
              this.makeBreadcrumbs(this.$router.resolve({name: route.matched[0].meta.parent}), path, true)
            }
          }
        } else {
          if (typeof route.resolved.meta.parent == 'string') {
            path.push(this.translateRouteName(route.route.name))
            this.makeBreadcrumbs(this.$router.resolve({name: route.resolved.meta.parent}), path, true)
          } else {
            path.push(this.translateRouteName(route.location.name))
          }
        }
        return path
      },
      translateRouteName (route) {
        return {route: route, label: this.$t('breadcrumb.' + (route.replace(/\.+/g, '-')))}
      }
    }
  }
</script>