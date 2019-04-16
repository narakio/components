<template>
  <div class="card">
    <form @submit.prevent="mode==='edit'?save():create()" @keydown="form.onKeydown($event)">
      <b-tabs card>
        <b-tab :title="form.fields.full_name?form.fields.full_name:$t('pages.users.new_user')" active>
          <div class="col-md-8 offset-md-2">
            <div v-if="mediaData" class="form-group row justify-content-center">
              <img :src="getImageUrl(mediaData.uuid,null,mediaData.ext)"/>
            </div>
            <div class="form-group row">
              <label for="username"
                     class="col-md-3 col-form-label"
                     :class="{ 'is-invalid': form.errors.has('username') }"
              >{{$t('db.username')}}</label>
              <div class="col-md-9">
                <input v-model="form.fields.username" type="text" autocomplete="off"
                       name="username" id="username" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('username') }"
                       :placeholder="$t('db.username')"
                       maxlength="25"
                       aria-describedby="help_username"
                       @change="changedField('username')">
                <has-error :form="form" field="username"></has-error>
                <small id="help_username" class="text-muted">
                  <template v-if="mode==='edit'">{{$t('form.description.new_username',[form.fields.username])}}&nbsp;
                  </template>
                  {{$t('form.description.username',[form.fields.email])}}
                </small>
              </div>
            </div>
            <div class="form-group row" v-if="mode==='add'">
              <label for="password"
                     class="col-md-3 col-form-label"
                     :class="{ 'is-invalid': form.errors.has('password') }"
              >{{$t('general.password')}}</label>
              <div class="col-md-9">
                <input v-model="form.fields.password" type="password" autocomplete="off"
                       name="password" id="password" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('password') }"
                       :placeholder="$t('general.password')"
                       minlength="8"
                       maxlength="255"
                       aria-describedby="help_password">
                <has-error :form="form" field="password"></has-error>
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
              <label for="email" class="col-md-3 col-form-label">{{$t('db.email')}}</label>
              <div class="col-md-9">
                <input v-model="form.fields.email" type="text" autocomplete="off"
                       name="email" id="email" class="form-control"
                       :class="{ 'is-invalid': form.errors.has('email') }"
                       :placeholder="$t('db.email')"
                       aria-describedby="help_email"
                       @change="changedField('email')">
                <has-error :form="form" field="email"></has-error>
                <small id="help_new_email" class="text-muted">
                  <template v-if="mode==='edit'">
                    {{$t('form.description.new_email',[form.fields.email])}}
                  </template>
                </small>
              </div>
            </div>
            <div v-if="groups.length" class="form-group row">
              <label class="col-md-3 col-form-label">{{$tc('db.groups',2)}}</label>
              <div class="col-md-9">
                <ul class="form-groups-list">
                  <li v-for="(group,idx) in groups" :key="group+idx" :class="[idx%2===0?'odd':'even']">
                    <div class="container">
                      <div class="row">
                        <div class="col-md-6 align-middle">
                          <span class="group-name">{{group.name}}</span>
                        </div>
                        <div class="col-md-6 group-button-wrapper align-middle">
                          <button-circle
                              ref="buttonCircleGroups"
                              :enabled="group.isMember===true"
                              @clicked="changedField('groups')"
                              :slug="group.slug">
                          </button-circle>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </b-tab>
        <b-tab v-if="mode==='edit'" :title="$tc('general.permission',2)" :disabled="checkPermissions()">
          <div class="container">
            <div class="callout callout-warning">
              <p><span class="callout-tag callout-tag-warning">
                <i class="fa fa-exclamation"></i>
              </span>
                &nbsp;{{$t('pages.users.warning1')}}
              </p>
              <p>
                {{$t('pages.users.warning2')}}
              </p>
            </div>
            <div>
              <div class="card mb-2" v-for="(permissionSet,entity) in permissions.default" :key="entity">
                <div class="card-header">{{$tc(`db.${entity}`,2)}}</div>
                <div class="card-body">
                  <table class="table table-sm">
                    <thead>
                    <tr>
                      <th class="w-50">{{$tc('general.permission',1)}}</th>
                      <th class="w-50">{{$t('general.toggle')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(maskValue,type) in permissionSet" :key="entity+type">
                      <td>{{type}}</td>
                      <td>
                        <button-circle
                            ref="buttonCirclePermissions"
                            :maskval="maskValue"
                            :entity="entity"
                            :enabled="hasPermission(permissions.computed,entity,type)"
                            :hasPermission="hasPermission(permissions.computed,entity,type)"
                            @clicked="changedField('permissions')"/>
                      </td>
                    </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </b-tab>
      </b-tabs>
      <div class="row justify-content-center">
        <div class="col-md-6 offset-md-3 mb-4">
          <submit-button :loading="form.busy" :value="mode==='edit'?$t('general.update'):$t('general.create')">
          </submit-button>
          <button v-if="intended!==null"
                  type="button"
                  class="btn btn-secondary"
                  @click="redirect()">{{$t('general.cancel')}}
          </button>
        </div>
      </div>
    </form>
    <div v-if="Object.keys(nav).length>0" class="row justify-content-center">
      <div>
        <record-paginator
            :nav="nav"
            :is-loading="ajaxIsLoading"
            route-name="admin.users.edit"
            route-param-name="user"></record-paginator>
      </div>
    </div>
  </div>
</template>

<script>
  import Vue from 'vue'
  import SubmitButton from 'back_path/components/SubmitButton'
  import Checkbox from 'back_path/components/Checkbox'
  import PermissionMixin from 'back_path/mixins/permissions'
  import FormMixin from 'back_path/mixins/form'
  import MediaMixin from 'back_path/mixins/media'
  import { Form, HasError, AlertForm } from 'back_path/components/form'
  import { Tabs } from 'bootstrap-vue/es/components'
  import ButtonCircle from 'back_path/components/ButtonCircle'
  import RecordPaginator from 'back_path/components/RecordPaginator'
  import axios from 'axios'

  Vue.use(Tabs)

  export default {
    layout: 'basic',
    middleware: 'check-auth',
    name: 'user-edit',
    components: {
      SubmitButton,
      Checkbox,
      HasError,
      AlertForm,
      Tabs,
      ButtonCircle,
      RecordPaginator
    },
    data () {
      return {
        form: new Form(),
        permissions: {},
        nav: {},
        ajaxIsLoading: false,
        intended: null,
        mediaData: null,
        entity: 'users',
        media: 'image_avatar',
        mode: null,
        groups: []
      }
    },
    mixins: [
      PermissionMixin,
      FormMixin,
      MediaMixin
    ],
    watch: {
      '$route' () {
        this.ajaxIsLoading = true
        axios.get(`/ajax/admin/users/${this.$router.currentRoute.params.user}`).then(({data}) => {
          this.getInfo(data, 'edit')
          this.ajaxIsLoading = false
        })
      }
    },
    methods: {
      redirect () {
        this.$router.push(this.intended)
      },
      getInfo (data, mode) {
        this.mode = mode
        if (mode === 'edit') {
          this.form = new Form(data.user, true)
        } else {
          this.form = new Form(data.user)
        }
        this.permissions = data.permissions
        this.nav = data.nav
        this.mediaData = data.media
        if (data.hasOwnProperty('groups')) {
          this.groups = data.groups
        }
        let intended = this.$store.getters['session/intendedUrl']
        if (intended === null) {
          this.intended = {name: 'admin.users.index'}
        } else {
          this.intended = this.$store.getters['session/intendedUrl']
        }
      },
      async save () {
        this.addFormFields()
        await this.form.patch(`/ajax/admin/users/${this.$router.currentRoute.params.user}`)
        this.backToIndex('user_update_ok')
      },
      async create () {
        this.addFormFields()
        await this.form.post(`/ajax/admin/user/create`)
        this.backToIndex('user_create_ok')
      },
      addFormFields () {
        let groupBtnList = this.$refs.buttonCircleGroups
        let groupList = []
        if (groupBtnList.length) {
          groupBtnList.forEach((vueBtn) => {
            if (vueBtn.buttonEnabled === true) {
              groupList.push(vueBtn.$attrs.slug)
            }
          })
        }
        this.form.addField('groups', groupList)
        this.form.addField('permissions', this.getPermissions(this.$refs.buttonCirclePermissions))
      },
      backToIndex (msgIdx) {
        this.$store.dispatch('session/setFlashMessage',
          {msg: {type: 'success', text: this.$t(`message.${msgIdx}`)}})
        this.redirect()
      }
    },
    beforeRouteEnter (to, from, next) {
      if (to.name === 'admin.users.add') {
        axios.get(`/ajax/admin/user/add`).then(({data}) => {
          next(vm => vm.getInfo(data, 'add'))
        })
      } else {
        axios.get(`/ajax/admin/users/${to.params.user}`).then(({data}) => {
          next(vm => vm.getInfo(data, 'edit'))
        })
      }
    },
    metaInfo () {
      return {title: this.$t('title.user_edit')}
    }
  }
</script>