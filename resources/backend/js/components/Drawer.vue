<template>
  <aside id="slider" class="main-sidebar">
    <section class="sidebar">
      <ul data-widget="tree" class="sidebar-menu">
        <drawer-item
            v-for="(item,index) in MenuItems"
            :data="item"
            :key="index"
            :type="item.type"
            :isHeader="item.isHeader"
            :icon="item.icon"
            :name="item.name"
            :badge="item.badge"
            :items="item.items"
            :router="item.router"
            :link="item.link">
        </drawer-item>
      </ul>
    </section>
  </aside>
</template>

<script>
  import DrawerItem from './DrawerItem'
  import Cookies from 'js-cookie'

  export default {
    name: 'drawer',
    props: {
      MenuItems: {
        type: Array,
        default () { return []}
      }
    },
    components: {
      'drawer-item': DrawerItem
    },
    mounted () {
      let sidebarStatus = Cookies.get('sidebar_status')
      //Initializing the left sidebar with the status we stored in a cookie.
      $('#button-sidebar-trigger').pushMenu({collapsedOnInit: sidebarStatus === '0'})
      //Activating the accordion effect on the left sidebar
      $('.treeview>a').each(function () {
        $(this).tree()
      })
      //Memorizing the state of the left sidebar in a cookie so the layout stays the same between sessions.
      let body = $('body')
      body.bind('collapsed.pushMenu', {cookies: Cookies}, function () {
        Cookies.set('sidebar_status', 0, {expires: 365})
      })
      body.bind('expanded.pushMenu', {cookies: Cookies}, function () {
        Cookies.set('sidebar_status', 1, {expires: 365})
      })
    },
    destroyed () {
      let body = $('body')
      body.unbind('collapsed.pushMenu', function () {})
      body.unbind('expanded.pushMenu', function () {})
    }
  }
</script>
