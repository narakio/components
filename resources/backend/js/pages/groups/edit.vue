<template>
    <div class="card">
        <form @submit.prevent="save" @keydown="form.onKeydown($event)">
            <b-tabs card>
                <b-tab :title="form.fields.group_name">
                    <div class="col-md-8 offset-md-2">
                        <div class="form-group row">
                            <label for="new_group_name"
                                   class="col-md-3 col-form-label">{{$t('db.new_group_name')}}</label>
                            <div class="col-md-9">
                                <input v-model="form.fields.new_group_name" type="text" autocomplete="off"
                                       name="new_group_name" id="new_group_name" class="form-control"
                                       :class="{ 'is-invalid': form.errors.has('new_group_name') }"
                                       :placeholder="$t('db.new_group_name')"
                                       maxlength="60"
                                       aria-describedby="help_new_group_name">
                                <has-error :form="form" field="new_group_name"></has-error>
                                <small id="help_new_group_name" class="text-muted">
                                    {{$t('form.description.new_group_name',[form.fields.group_name])}}
                                </small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="group_mask" class="col-md-3 col-form-label">{{$t('db.group_mask')}}</label>
                            <div class="col-md-9">
                                <input v-model="form.fields.group_mask" type="text" autocomplete="off"
                                       name="group_mask" id="group_mask" class="form-control"
                                       :class="{ 'is-invalid': form.errors.has('group_mask') }"
                                       :placeholder="$t('db.group_mask')"
                                       maxlength="5"
                                       aria-describedby="help_group_mask">
                                <has-error :form="form" field="group_mask"></has-error>
                                <small id="help_group_mask" class="text-muted">{{$t('form.description.group_mask')}}
                                </small>
                            </div>
                        </div>
                    </div>
                </b-tab>
                <b-tab :title="$tc('general.permission',2)" :disabled="checkPermissions()">
                    <div class="container">
                        <div class="callout callout-info">
                            <p>{{$t('pages.groups.info1')}}</p>
                            <p>{{$t('pages.groups.info2')}}</p>
                        </div>
                        <hr>
                        <div>
                            <div class="card mb-2" v-for="(permissionSet,entity) in permissions.default"
                                 :key="entity">
                                <div class="card-header">{{$tc(`db.${entity}`,2)}}</div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                        <tr>
                                            <th>{{$tc('general.permission',1)}}</th>
                                            <th>{{$t('general.toggle')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(maskValue,type) in permissionSet" :key="entity+type">
                                            <td>{{type}}</td>
                                            <td>
                                                <button-circle
                                                        ref="buttonCircle"
                                                        :maskval="maskValue"
                                                        :entity="entity"
                                                        :enabled="hasPermission(permissions.computed,entity,type)"
                                                        :hasPermission="hasPermission(permissions.computed,entity,type)"
                                                >
                                                </button-circle>
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
                    <submit-button class="align-content-center" :loading="form.busy">
                        {{ $t('general.update') }}
                    </submit-button>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
  import Vue from 'vue'
  import axios from 'axios'
  import { Tabs } from 'bootstrap-vue/es/components'

  Vue.use(Tabs)

  import SubmitButton from 'back_path/components/SubmitButton'
  import Checkbox from 'back_path/components/Checkbox'
  import { Form, HasError, AlertForm } from 'back_path/components/form'
  import ButtonCircle from 'back_path/components/ButtonCircle'
  import PermissionMixin from 'back_path/mixins/permissions'

  export default {
    layout: 'basic',
    middleware: 'check-auth',
    name: 'group-edit',
    components: {
      SubmitButton,
      Checkbox,
      HasError,
      AlertForm,
      Tabs,
      ButtonCircle
    },
    data () {
      return {
        form: new Form(),
        group: this.$router.currentRoute.params.group,
        permissions: {}
      }
    },
    mixins: [PermissionMixin],
    methods: {
      getInfo (data) {
        this.form = new Form(data.group)
        this.permissions = data.permissions
      },
      async save () {
        try {
          this.form.addField('permissions', this.getPermissions(this.$refs.buttonCircle))
          await this.form.patch(`/ajax/admin/groups/${this.group}`)
          this.$store.dispatch(
            'session/setFlashMessage',
            {msg: {type: 'success', text: this.$t('message.group_update_ok')}}
          )
          this.$router.push({name: 'admin.groups.index'})
        } catch (e) {}
      }
    },
    metaInfo () {
      return {title: this.$t('title.group_edit')}
    },
    beforeRouteEnter (to, from, next) {
      axios.get(`/ajax/admin/groups/${to.params.group}`).then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    }
  }
</script>