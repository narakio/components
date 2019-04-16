<script>
  import Pages from 'front_path/pages'
  import Vue from 'vue'

  /**
   * Each page has a page-id meta tag containing a token that helps us identify the current page
   * We list the vue components and various js files that are needed on this page in the pages.js script.
   * We load those components asynchronously.
   */
  let pageSelector = document.head.querySelector('meta[name="page-id"]')
  let token = (pageSelector) ? pageSelector.content : null

  let getComponentsToLoadFromList = function getComponentsToLoadFromList (token, pages) {
    if (pages.hasOwnProperty(token)) {
      return pages[token].components
    }
    return []
  }
  /**
   * We grab every component, both from the backend and frontend, because we use backend components in the frontend.
   */
  const frontendContext = require.context('./', false, /[^App]\.vue$/)
  const backendContext = require.context('back_path/components', false, /[^App]\.vue$/)

  const frontend = frontendContext.keys()
    .map(file =>
      [file.replace(/(^.\/)|(\.vue$)/g, ''), frontendContext(file)]
    )
    .reduce((components, [name, component]) => {
      components[name] = component
      return components
    }, {})
  const backend = backendContext.keys()
    .map(file =>
      [file.replace(/(^.\/)|(\.vue$)/g, ''), backendContext(file)]
    )
    .reduce((components, [name, component]) => {
      components[name] = component
      return components
    }, {})

  let ComponentsToLoadList = getComponentsToLoadFromList(token, Pages)

  //We might load components on pages that have a nav. No need to load them on pages without a nav.
  //Page ids are referring to the login, register and search pages
  var pagesWithoutSearch = ['d56b699830', '9de4a97425','06a943c59f']
  if (pagesWithoutSearch.indexOf(token) === -1) {
    if (ComponentsToLoadList.hasOwnProperty('frontend')) {
      ComponentsToLoadList.frontend.push('InlineSearch')
    } else {
      ComponentsToLoadList.frontend = ['InlineSearch']
    }
  }

  if (ComponentsToLoadList.hasOwnProperty('frontend')) {
    ComponentsToLoadList.frontend.forEach(componentName => {
      Vue.component(componentName, frontend[componentName])
    })
  }
  if (ComponentsToLoadList.hasOwnProperty('backend')) {
    ComponentsToLoadList.backend.forEach(componentName => {
      Vue.component(componentName, backend[componentName])
    })
  }

  (async function pageLoader (token, pages) {
    if (pages.hasOwnProperty(token)) {
      if (pages[token].hasOwnProperty('page')) {
        if (typeof pages[token].page == 'object') {
          pages[token].page.forEach(async script => {
            await import(/* webpackChunkName: "page-" */ `front_path/${script}`)
          })
        } else {
          await import(/* webpackChunkName: "page-" */ `front_path/pages/${pages[token].page}`)
        }
      }
    }
  })(token, Pages)

  export default {}
</script>