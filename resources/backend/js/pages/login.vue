<template>
  <div class="row">
    <div class="col-lg-5 m-auto">
      <div class="card">
        <div class="card-body p-5">
          <form @submit.prevent="login" @keydown="form.onKeydown($event)">
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-md-right">{{ $t('general.email') }}</label>
              <div class="col-md-9">
                <input v-model="form.fields.email" type="email" name="email"
                       class="form-control" autocomplete="username"
                       :class="{ 'is-invalid': form.errors.has('email') }">
                <has-error :form="form" field="email"></has-error>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-3 col-form-label text-md-right">{{ $t('general.password') }}</label>
              <div class="col-md-9">
                <input v-model="form.fields.password" type="password" name="password"
                       class="form-control" autocomplete="current-password"
                       :class="{ 'is-invalid': form.errors.has('password') }">
                <has-error :form="form" field="password"></has-error>
              </div>
            </div>
            <div class="form-group row">
              <div class="col-md-3"></div>
              <div class="col-md-9 d-flex">
                <checkbox v-model="form.fields.remember" name="remember">
                  {{ $t('pages.auth.remember_me') }}
                </checkbox>

                <a href="/password/reset" class="small ml-auto my-auto">{{ $t('pages.auth.forgot_password') }}</a>
                <!--<router-link :to="{ name: 'admin.password.request' }" class="small ml-auto my-auto">-->
                <!--{{ $t('pages.auth.forgot_password') }}-->
                <!--</router-link>-->
              </div>
            </div>
            <div class="form-group row">
              <div class="col-md-7 offset-md-3 d-flex">
                <submit-button :loading="form.busy">{{ $t('general.login') }}</submit-button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import SubmitButton from 'back_path/components/SubmitButton'
  import Checkbox from 'back_path/components/Checkbox'
  import { Form, HasError } from 'back_path/components/form'

  export default {
    middleware: 'guest',
    metaInfo () {
      return {title: this.$t('title.login')}
    },
    components: {
      SubmitButton,
      Checkbox,
      HasError
    },
    data: () => ({
      form: new Form({
        email: '',
        password: '',
        remember:false
      }),
    }),

    methods: {
      async login () {
        const {data} = await this.form.post('/admin/login')
        this.$store.dispatch('auth/updateUser', {user: data.user})
        this.$store.dispatch('auth/saveToken', {
          token: data.token,
          remember: this.form.remember
        })
        this.$router.push({name: 'admin.dashboard'})
      },
      metaInfo () {
        return {title: this.$t('title.log_in')}
      }
    }
  }
</script>
