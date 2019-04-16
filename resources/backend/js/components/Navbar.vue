<template>
  <header class="main-header">
    <!-- Logo -->
    <div v-if="user" id="logo-wrapper">
      <router-link :to="{ name: 'admin.dashboard' }" class="logo">
        <span class="logo-mini"><img src="/media/img/site/logo-bk.png"></span>
        <span class="logo-lg"><img src="/media/img/site/logo-text.png"></span>
      </router-link>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark main-header navbar-static-top">
      <button v-if="user" class="navbar-toggler" type="button"
              data-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <i class="fa fa-bars"></i>
      </button>
      <div v-if="user" class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <span><a class="nav-link sidebar-toggle" id="button-sidebar-trigger"
                     data-toggle="push-menu" role="button">
              <i class="fa fa-bars"></i>
            </a></span>
          </li>
        </ul>
        <ul class="navbar-nav nav justify-content-end">
          <li class="btn-nav-group">
            <div class="btn-nav">
              <router-link :to="{ name: 'admin.settings.general' }"
                           :title="$t('pages.settings.title')">
                <i class="fa fa-cogs"></i>
              </router-link>
            </div>
          </li>
          <b-dropdown right variant="dark" no-caret
                      :class="['no-caret']"
                      @hide="resetNotifications">
            <template #button-content>
              <i class="fa fa-bell" :class="buttonBellClasses"></i>
              <span v-if="notificationCount>0"
                    id="alerts_badge"
                    class="badge badge-pill badge-danger">{{notificationCount}}</span>
            </template>
            <b-dropdown-header>{{$tc(
              'pages.system_log.notifications',
              notificationCount,
              {number:notificationCount}
              )}}
            </b-dropdown-header>
            <b-dropdown-divider v-show="notificationCount>0"></b-dropdown-divider>
            <b-dropdown-item v-for="(notification, idx) in notifications" :key="'notif'+idx">
              {{notification}}
            </b-dropdown-item>
            <b-dropdown-divider></b-dropdown-divider>
            <b-dropdown-item router-tag="a"
                             :to="{ name: 'admin.system.log' }">
              {{ $t('general.view_all') }}
            </b-dropdown-item>
          </b-dropdown>

          <b-dropdown right :text="user.username" variant="dark">
            <b-dropdown-item router-tag="a"
                             :to="{ name: 'admin.user.profile' }">
              <i class="fa fa-cog"></i>
              {{ $t('pages.user.settings') }}
            </b-dropdown-item>
            <b-dropdown-divider/>
            <b-dropdown-item-button @click="logout">
              <i class="fa fa-sign-out"></i>
              {{ $t('general.logout') }}
            </b-dropdown-item-button>
          </b-dropdown>
        </ul>
      </div>
    </nav>
  </header>
</template>

<script>
  import Vue from 'vue'
  import { mapGetters } from 'vuex'
  import { Dropdown, Link } from 'bootstrap-vue/es/components'

  Vue.use(Dropdown)

  export default {
    data: function () {
      return {
        appName: window.config.appName,
        buttonBellClasses: []
      }
    },
    components: {
      Dropdown, Link
    },
    computed: mapGetters({
      user: 'auth/user',
      notificationCount: 'session/notificationCount',
      notifications: 'session/notifications'
    }),
    watch: {
      notificationCount () {
        if (this.notificationCount > 0) {
          this.buttonBellClasses = ['faa-ring', 'animated']
        } else {
          this.buttonBellClasses = []
        }
      }
    },
    methods: {
      resetNotifications () {
        this.$store.dispatch('session/resetNotifications')
      },
      async logout () {
        // Log out the user.
        await this.$store.dispatch('auth/logout')

        // Redirect to login.
        this.$router.push({name: 'admin.login'})
      }
    }
  }
</script>