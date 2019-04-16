export default {
  name: 'form',
  methods: {
    changedField (field) {
      this.form.addChangedField(field)
    }
  }
}
