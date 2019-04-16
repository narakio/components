<template>
    <form @submit.prevent="update" @keydown="form.onKeydown($event)">
        <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">{{ $t('pages.auth.current_password') }}</label>
            <div class="col-md-7">
                <input v-model="form.fields.current_password" type="password" name="current_password"
                       class="form-control" autocomplete="current-password"
                       :class="{ 'is-invalid': form.errors.has('current_password') }" required>
                <has-error :form="form" field="current_password"></has-error>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">{{ $t('pages.auth.new_password') }}</label>
            <div class="col-md-7">
                <input v-model="form.fields.password" type="password" name="password"
                       class="form-control" autocomplete="new-password"
                       :class="{ 'is-invalid': form.errors.has('password') }" required>
                <has-error :form="form" field="password"></has-error>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">{{ $t('pages.auth.confirm_password') }}</label>
            <div class="col-md-7">
                <input v-model="form.fields.password_confirmation" type="password" name="password_confirmation"
                       class="form-control" autocomplete="new-password"
                       :class="{ 'is-invalid': form.errors.has('password_confirmation') }" required>
                <has-error :form="form" field="password_confirmation"></has-error>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-9 ml-md-auto">
                <submit-button :loading="form.busy">{{ $t('general.update') }}</submit-button>
            </div>
        </div>
    </form>
</template>

<script>
  import SubmitButton from 'back_path/components/SubmitButton'
  import Swal from 'back_path/mixins/sweet-alert'
  import { Form, HasError } from 'back_path/components/form'
  import { mapGetters } from 'vuex'

  export default {
    scrollToTop: false,
    components: {
      SubmitButton,
      HasError
    },
    mixins:[
      Swal
    ],
    data: () => ({
      form: new Form({
        current_password: '',
        password: '',
        password_confirmation: ''
      })
    }),
    computed: {
      ...mapGetters({
        user: 'auth/user'
      })
    },
    methods: {
      async update () {
        await this.form.patch('/ajax/admin/user/password')
        this.form.reset()
        this.swalNotification('success', this.$t('message.password_updated'))
      }
    },
    metaInfo () {
      return {title: this.$t('title.settings_password')}
    }
  }
</script>
