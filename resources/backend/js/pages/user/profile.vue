<template>
    <form @submit.prevent="update" @keydown="form.onKeydown($event)">
        <div class="form-group row">
            <label for="new_username" class="col-md-3 col-form-label">{{$t('db.new_username')}}</label>
            <div class="col-md-9">
                <input v-model="form.fields.new_username" type="text" autocomplete="off"
                       name="new_username" id="new_username" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('new_username') }"
                       :placeholder="$t('db.new_username')"
                       aria-describedby="help_new_username"
                       @change="changedField('new_username')">
                <has-error :form="form" field="new_username"></has-error>
                <small id="help_new_username" class="text-muted">
                    {{$t('form.description.new_username',[form.fields.username])}}
                </small>
            </div>
        </div>
        <div class="form-group row">
            <label for="first_name" class="col-md-3 col-form-label">{{$t('db.first_name')}}</label>
            <div class="col-md-9">
                <input v-model="form.fields.first_name" type="text" autocomplete="off"
                       name="first_name" id="first_name" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('first_name') }"
                       :placeholder="$t('db.first_name')"
                       aria-describedby="help_first_name"
                       @change="changedField('first_name')">
                <has-error :form="form" field="first_name"></has-error>
                <small id="help_first_name" class="text-muted">{{$t('form.description.first_name')}}
                </small>
            </div>
        </div>
        <div class="form-group row">
            <label for="last_name" class="col-md-3 col-form-label">{{$t('db.last_name')}}</label>
            <div class="col-md-9">
                <input v-model="form.fields.last_name" type="text" autocomplete="off"
                       name="last_name" id="last_name" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('last_name') }"
                       :placeholder="$t('db.last_name')"
                       aria-describedby="help_last_name"
                       @change="changedField('last_name')">
                <has-error :form="form" field="last_name"></has-error>
                <small id="help_last_name" class="text-muted">{{$t('form.description.last_name')}}
                </small>
            </div>
        </div>
        <div class="form-group row">
            <label for="new_email" class="col-md-3 col-form-label">{{$t('db.new_email')}}</label>
            <div class="col-md-9">
                <input v-model="form.fields.new_email" type="text"
                       name="new_email" id="new_email" class="form-control"
                       autocomplete="email"
                       :class="{ 'is-invalid': form.errors.has('new_email') }"
                       :placeholder="$t('db.new_email')"
                       aria-describedby="help_new_email"
                       @change="changedField('new_email')">
                <has-error :form="form" field="new_email"></has-error>
                <small id="help_new_email" class="text-muted">
                    {{$t('form.description.new_email',[form.fields.email])}}
                </small>
            </div>
        </div>
        <div class="form-group row">
            <div class="container col-lg-6 justify-content-center">
                <div class="col-lg text-center">
                    <submit-button :loading="form.busy">{{ $t('general.update') }}</submit-button>
                </div>
            </div>
        </div>
        <avatar-uploader ref="avatarUploader" :user="user" :avatars-parent="avatars"/>
    </form>
</template>

<script>
  import SubmitButton from 'back_path/components/SubmitButton'
  import AvatarUploader from 'back_path/components/AvatarUploader'
  import Swal from 'back_path/mixins/sweet-alert'
  import Forms from 'back_path/mixins/form'
  import axios from 'axios'

  import { Form, HasError } from 'back_path/components/form'
  import { mapGetters } from 'vuex'

  export default {
    scrollToTop: false,
    components: {
      SubmitButton,
      HasError,
      AvatarUploader
    },
    mixins: [
      Swal,
      Forms
    ],
    data () {
      return {
        form: new Form(),
        avatars: []
      }
    },
    computed: {
      ...mapGetters({
        user: 'auth/user'
      })
    },
    methods: {
      async update () {
        if (this.form.hasDetectedChanges()) {
          if (this.form.hasFieldChanged('new_email')) {
            const {data} = await this.form.patch('/ajax/admin/user/profile')
            this.swalEdumacationInfo(
              this.$t('modal.user_profile_updated.h'),
              this.$t('modal.user_profile_updated.t'),
              this.$t('modal.user_profile_updated.b')
            ).then(async (result) => {
              if (result.value) {
                await this.$store.dispatch('auth/revokeUser')
                this.$router.push({name: 'admin.login'})
              }
            })
            return
          } else {
            const {data} = await this.form.patch('/ajax/admin/user/profile')
            this.form = new Form(data.user, true)
            this.$store.dispatch('auth/updateUser', {user: data.user})
          }
        }
        this.swalNotification('success', this.$t('message.profile_updated'))
      },
      getInfo (data) {
        this.form = new Form(data.user, true)
        this.avatars = data.avatars
      }
    },
    beforeRouteEnter (to, from, next) {
      axios.get(`/ajax/admin/users/profile`).then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    },
    metaInfo () {
      return {title: this.$t('title.settings_profile')}
    }
  }
</script>
