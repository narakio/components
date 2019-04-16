/*****
 *
 ****************************************************
 *        NOT USED, TO BE DELETED
 ****************************************************
 *
 * @param Vue
 */
export default function install (Vue) {
  Vue.prototype['$routeTranslate'] = (route, i18n) => {
    let name = 'routes.' + route.name
    delete route.name

    if (i18n.locale !== i18n.fallbackLocale) {
      route.path = '/' + i18n.locale + '/' + i18n.$t(name)
    } else {
      route.path = '/' + i18n.$t(name)
    }

    return route
  }
}
