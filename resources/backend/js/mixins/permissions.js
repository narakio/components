export default {
  name: 'permission-mixin',
  methods: {
    getPermissions (permissions) {
      let savedPermissions = {hasChanged: false}
      if (!permissions) {
        return savedPermissions
      }
      let permissionsLength = permissions.length
      for (let i = 0; i < permissionsLength; i++) {
        let p = permissions[i].$attrs.entity
        if (!savedPermissions.hasOwnProperty(p)) {
          savedPermissions[p] = {mask: 0, hasChanged: false}
        }
        let currentlyEnabled = this.permissionIsCurrentlyEnabled(
          permissions[i].$el)
        if (currentlyEnabled) {
          savedPermissions[p].mask += permissions[i].$attrs.maskval
        }
        if (currentlyEnabled !== permissions[i].$attrs.hasPermission) {
          savedPermissions[p].hasChanged = true
          savedPermissions.hasChanged = true
        }
      }
      return savedPermissions
    },
    hasPermission (permissions, entity, type) {
      return (permissions.hasOwnProperty(entity) &&
        permissions[entity].hasOwnProperty(type))
    },
    permissionIsCurrentlyEnabled (el) {
      // class name is btn btn-circle btn-danger/btn-success
      return el.className.match(/d/) == null
    },
    checkPermissions () {
      let permissions = this.$store.getters['auth/permissions']
      if (permissions.hasOwnProperty('system')) {
        if (permissions.system.hasOwnProperty('Permissions')) {
          return false
        }
      }
      return true
    }
  }
}
