<template>
  <div class="container">
    <div class="row mb-2">
      <router-link :to="{name: 'admin.groups.add'}">
        <button class="btn btn-add"
                type="button">{{$t('pages.groups.add_group')}}
        </button>
      </router-link>
    </div>
    <div class="row">
      <v-table :entity="entity" :data="computedTable"
               select-column-name="group_name">
        <template #body-action="props">
          <td>
            <div class="inline">
              <template v-if="props.row.group_slug">
                <router-link :to="{
                    name: 'admin.groups.edit',
                    params: { group: props.row.group_slug }
                    }">
                  <button class="btn btn-sm btn-info">
                    <i class="fa fa-pencil"></i>
                  </button>
                </router-link>
              </template>
              <button class="btn btn-sm btn-danger"
                      @click="deleteRow(props.row)">
                <i class="fa fa-trash"></i>
              </button>
              <template v-if="props.row.group_slug">
                <router-link
                    :to="{ name: 'admin.groups.members', params: { group: props.row.group_slug }}">
                  <button class="btn btn-sm btn-success">
                    <i class="fa fa-users"></i>
                  </button>
                </router-link>
              </template>
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
  import axios from 'axios'
  import TableMixin from 'back_path/mixins/tables'
  import Swal from 'back_path/mixins/sweet-alert'

  Vue.use(Table)

  export default {
    name: 'groups',
    layout: 'basic',
    middleware: 'check-auth',
    components: {
      'v-table': Table
    },
    data: function () {
      return {
        entity: 'groups',
        data: {
          total: 0
        }
      }
    },
    mixins: [
      TableMixin,
      Swal
    ],
    methods: {
      async deleteRow (data) {
        this.swalDeleteWarning(
          this.$t('modal.group_delete.h'),
          this.$tc('modal.group_delete.t', 1, {name: data.group_slug}),
          this.$t('general.delete')
        ).then(async (result) => {
          if (result.value) {
            await axios.delete(`/ajax/admin/groups/${data.group_slug}`)
            this.refreshTableData()
            this.swalNotification('success', this.$tc('message.group_delete_ok', 1, {name: data.group_slug}))
          }
        })
      }
    },
    metaInfo () {
      return {title: this.$t('title.group_index')}
    }
  }
</script>