<template>
  <div class="container">
    <div class="card filter-wrapper">
      <div class="container">
        <table-filter :filterButtons="filterButtons" :entity="this.entity"
                      @filter-removed="removeFilter"
                      @filter-reset="resetFilters"></table-filter>
        <div class="row pb-1">
          <div class="col-md-4">
            <div class="input-group">
              <input type="text" class="form-control"
                     :placeholder="$t('pages.blog.filter_title')"
                     :aria-label="$t('pages.blog.filter_title')"
                     v-model="titleFilter"
                     @keyup.enter="filterTitle">
              <div class="input-group-append">
                <label class="input-group-text"
                       :title="$t('general.search')"
                       @click="filterTitle">
                  <i class="fa fa-newspaper-o"></i>
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
            <div class="float-right row align-items-center">
              <div class="col">
                <span class="mr-2" v-if="data.total">{{data.total}}&nbsp;{{$tc(`db.${entity}`,data.total)}}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <v-table ref="table"
               :entity="entity" :data="computedTable"
               :is-multi-select="true" select-column-name="blog_post_title">
        <template #header-action>
          <th>{{$t('general.actions')}}</th>
        </template>
        <template #body-action="props">
          <td>
            <div class="inline">
              <template v-if="props.row.media_uuid">
                <router-link :to="{
                          name: 'admin.media.edit',
                          params: { entity:entity, media: props.row.media_uuid }
                          }">
                  <button
                      class="btn btn-sm btn-info"
                      :title="$t(
                                'tables.edit_item',{
                                name:props.row[$t('db_raw_inv.media_uuid')]
                                })">
                    <i class="fa fa-pencil"></i>
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
  import Swal from 'back_path/mixins/sweet-alert'
  import Table from 'back_path/components/table/table'
  import TableFilter from 'back_path/components/table/TableFilter'
  import TableMixin from 'back_path/mixins/tables'
  import axios from 'axios'

  Vue.use(Table)

  export default {
    layout: 'basic',
    middleware: 'check-auth',
    name: 'media-edit',
    components: {
      'v-table': Table,
      TableFilter
    },
    mixins: [
      TableMixin,
      Swal
    ],
    data () {
      return {
        allSelected: false,
        selectApply: '',
        titleFilter: null,
        filterButtons: {},
        selectionBuffer: {},
        entity: 'media',
        data: {
          total: 0
        }
      }
    },

    methods: {
      setFilterButtons () {
        this.setFilterButton('title')
      },
      filterTitle () {
        this.applyFilter('title')
      },
      async applyToSelected () {

      }
    }
  }
</script>