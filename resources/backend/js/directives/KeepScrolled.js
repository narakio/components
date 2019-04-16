const KeepScrolled = {
  bind (el, binding, vnode) {
    console.log('bind')
  },

  inserted (el, binding, vnode) {
    console.log('inserted')
  }
}
export default KeepScrolled
