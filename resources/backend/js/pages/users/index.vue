<template>
  <div class="container">
    <div class="card filter-wrapper">
      <div class="container">
        <table-filter :filterButtons="filterButtons" :entity="entity"
                      @filter-removed="removeFilter"
                      @filter-reset="resetFilters">
        </table-filter>
        <div class="row pb-1">
          <div class="col-md-4">
            <div class="input-group">
              <input type="text" class="form-control" ref="inputFilterFullname"
                     :placeholder="$t('pages.users.filter_full_name')"
                     :aria-label="$t('pages.users.filter_full_name')"
                     v-model="nameFilter"
                     @keyup.enter="fullNameFilter">
              <div class="input-group-append">
                <label class="input-group-text"
                       :title="$t('pages.users.filter_full_name')"
                       @click="fullNameFilter">
                  <i class="fa fa-user"></i>
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group">
              <select class="custom-select" v-model="groupFilter">
                <option disabled value="">{{$t('pages.users.filter_group')}}</option>
                <option v-for="(group,idx) in data.groups" :key="idx">{{group}}</option>
              </select>
              <div class="input-group-append">
                <label class="input-group-text"
                       :title="$t('pages.users.filter_group')">
                  <i class="fa fa-users"></i>
                </label>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="input-group">
              <select class="custom-select" id="inputGroupSelect02" v-model="createdFilter">
                <option disabled value="">{{$t('pages.users.filter_created_at')}}</option>
                <option :value="$t('filters.day')">{{$t('filter_labels.created_today')}}</option>
                <option :value="$t('filters.week')">{{$t('filter_labels.created_week')}}</option>
                <option :value="$t('filters.month')">{{$t('filter_labels.created_month')}}</option>
                <option :value="$t('filters.year')">{{$t('filter_labels.created_year')}}</option>
              </select>
              <div class="input-group-append">
                <label class="input-group-text"
                       :title="$t('pages.users.filter_created_at')">
                  <i class="fa fa-calendar"></i>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="row pb-1">
          <div class="col-md-8">
            <div class="form-row align-items-center">
              <div class="col my-1">
                <select class="custom-select mr-sm-2" id="select_apply_to_all" v-model="selectApply">
                  <option disabled value="">{{$t('tables.grouped_actions')}}</option>
                  <option value="del">{{$t('tables.option_del_user')}}</option>
                </select>
              </div>
              <div class="col my-1">
                <button type="button" class="btn btn-primary"
                        :title="$t('tables.btn_apply_title')"
                        @click="applyToSelected">
                  {{$t('general.apply')}}
                </button>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <span class="float-right mt-3">{{data.total}}&nbsp;{{$tc(`db.${entity}`,data.total)}}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <v-table ref="table"
               :entity="entity" :data="computedTable"
               :is-multi-select="true"
               select-column-name="full_name">
        <template #body-action="props">
          <td>
            <div class="inline">
              <template v-if="props.row.username">
                <router-link v-if="(props.row.acl&4)!==0||props.row.acl===0"
                             :to="{ name: 'admin.users.edit', params: { user: props.row.username } }">
                  <button type="button" class="btn btn-sm btn-info"
                          :title="$t('tables.edit_item',{name:props.row[$t('db_raw_inv.full_name')]})">
                    <i class="fa fa-pencil"></i>
                  </button>
                </router-link>
              </template>
              <button v-if="(props.row.acl&8)!==0||props.row.acl===0"
                  type="button"
                      class="btn btn-sm btn-danger"
                      :title="$t('tables.delete_item',{name:props.row[$t('db_raw_inv.full_name')]})"
                      @click="deleteRow(props.row,'user','username','full_name','/ajax/admin/users')">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          </td>
        </template>
      </v-table>
    </div>
  </div>
</template>

<script>
  import Vue from 'vue'
  import Table from 'back_path/components/table/table'
  import TableFilter from 'back_path/components/table/TableFilter'
  import TableMixin from 'back_path/mixins/tables'
  import Swal from 'back_path/mixins/sweet-alert'
  import axios from 'axios'

  Vue.use(Table)

  export default {
    layout: 'basic',
    middleware: 'check-auth',
    name: 'users',
    components: {
      'v-table': Table,
      TableFilter
    },
    data: function () {
      return {
        allSelected: false,
        selectApply: '',
        groupFilter: null,
        createdFilter: null,
        nameFilter: null,
        filterButtons: {},
        entity: 'users',
        data: {
          total: 0,
          groups: []
        }
      }
    },
    mixins: [
      Swal,
      TableMixin
    ],
    watch: {
      groupFilter () {
        this.applyFilter('group')
      },
      createdFilter () {
        this.applyFilter('created')
      }
    },
    mounted () {
      this.$refs.inputFilterFullname.focus()
    },
    methods: {
      setFilterButtons () {
        this.setFilterButton('name')
        this.setFilterButton('group')
        this.setFilterButton('created')
      },
      async applyToSelected () {
        let users = this.$refs.table.getSelectedRows('username')
        if (users.length > 0) {
          switch (this.selectApply) {
            case 'del':
              this.swalDeleteWarning(
                this.$t('modal.user_delete.h'),
                this.$tc('modal.user_delete.t', 2, {number: users.length}),
                this.$t('general.delete')
              ).then(async (result) => {
                if (result.value) {
                  await axios.post('/ajax/admin/users/batch/delete', {users: users})
                  this.refreshTableData()
                  this.swalNotification('success', this.$tc('message.user_delete_ok', 2))
                }
              })
              break
          }
        }
      },
      fullNameFilter () {
        this.applyFilter('name')
      }
    },
    metaInfo () {
      return {title: this.$t('title.user_index')}
    }
  }
</script>