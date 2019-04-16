<template>
    <div class="main-layout">
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <router-link :to="{ name: 'admin.login' }" class="navbar-brand">
                    {{ appName }}
                </router-link>

                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarToggler" aria-controls="navbarToggler"
                        aria-expanded="false" :aria-label="$t('toggle_navigation')"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarToggler">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <router-link :to="{ name: 'admin.login' }" class="nav-link" active-class="active">
                                {{ $t('general.login') }}
                            </router-link>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <transition name="page" mode="out-in">
                <slot>
                    <router-view></router-view>
                </slot>
            </transition>
        </div>
    </div>
</template>

<script>
  import Navbar from 'back_path/components/Navbar'

  export default {
    name: 'default',
    data: () => ({
      appName: window.config.appName
    }),
    components: {
      Navbar,
    }
  }
</script>
