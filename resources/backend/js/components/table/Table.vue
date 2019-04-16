<template>
  <div class="card col-lg p-0 m-0">
    <div class="table-container table-responsive">
      <template v-if="table.total===0">
        <h3>{{$t('tables.empty')}}</h3>
      </template>
      <template v-else>
        <table class="table table-hover table-striped">
          <thead>
          <tr>
            <slot name="header-select-all">
              <th v-show="isMultiSelect" class="column-header">
                <div class="form-check">
                  <input class="form-check-input position-static"
                         type="checkbox"
                         :aria-label="$t('general.select_all')"
                         :title="$t('general.select_all')"
                         @click="toggleSelectAll">
                </div>
              </th>
            </slot>
            <th v-for="(info, index) in table.columns"
                :key="index"
                @click="sort(info)"
                class="column-header"
                :class="[info.sortable?'sort-header':'']"
                :style="{
                  'width': info.hasOwnProperty('width')?info.width:'auto'
                }">{{info.label}}<span :title="$t('tables.sort_'+getOrder(info.order))"><i v-if="info.sortable"
                :class="[info.sortable?'fa fa-'+getColumnHeaderIcon(info):null]"></i></span>
            </th>
            <slot name="header-action">
              <th class="column-header">
                {{$t('general.actions')}}
              </th>
            </slot>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(row,rowIdx) in table.rows"
              :key="rowIdx">
            <slot name="body-select-row" :row="row">
              <td v-show="isMultiSelect">
                <div class="form-check">
                  <input class="form-check-input position-static"
                         type="checkbox"
                         :aria-label="$t('tables.select_item',{name:row[$t(`db_raw_inv.${selectColumnName}`)]})"
                         :title="$t('tables.select_item',{name:row[$t(`db_raw_inv.${selectColumnName}`)]})"
                         v-model="row.selected">
                </div>
              </td>
            </slot>
            <td v-for="(info,colIdx) in table.columns" :key="colIdx">
              {{row[info.name]}}
            </td>
            <slot name="body-action" :row="row">
            </slot>
          </tr>
          </tbody>
        </table>
        <div id="paginator_container" class="container mt-4">
          <div class="row justify-content-md-center">
            <div class="paginator col-lg-6">
              <b-pagination-nav v-if="table.lastPage>1" :link-gen="linkGen" :total-rows="table.total"
                                :value="table.currentPage"
                                :per-page="table.perPage" :limit="10" :number-of-pages="table.lastPage">
              </b-pagination-nav>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
  import Vue from 'vue'
  import { PaginationNav } from 'bootstrap-vue/es/components'

  Vue.use(PaginationNav)

  export default {
    name: 'v-table',
    components: {
      PaginationNav
    },
    data: function () {
      return {
        sortOrder: 'desc',
        allSelected: false
      }
    },
    props: {
      entity: {
        type: String,
        required: true
      },
      data: {
        type: Object,
        required: true
      },
      isMultiSelect: {
        type: Boolean,
        default: false
      },
      selectColumnName: {
        type: String,
        default: ''
      }
    },
    computed: {
      table () {
        return this.data
      }
    },
    updated () {
      //Triggering a jquery fix defined in plugins/jquery/Layout that fixes the height of the sidebar
      //to match the size of this table, which can get pretty large
      $('body').trigger('sidebar-fix')
    },
    methods: {
      toggleSelectAll () {
        this.allSelected = !this.allSelected
        this.table.rows.forEach(row => (row.selected = this.allSelected))
      },
      getSelectedRows (column = null) {
        let selected = []
        this.table.rows.forEach(row => {
          if (row.selected) {
            if (column) {
              selected.push(row[column])
            } else {
              selected.push(row)
            }
          }
        })
        return selected
      },
      sort (column) {
        if (!column.sortable) {
          return
        }
        let obj = Object.assign({}, this.$route.query)
        obj[this.$t('filters.sortBy')] = this.$t(`db_raw.${column.name}`)
        let orderTranslated = this.$t('filters.order')
        obj[orderTranslated] = this.toggleSortOrder()
        this.$router.push({query: obj})
      },
      getColumnHeaderIcon (info) {
        return (!info.hasOwnProperty('order'))
          ? 'sort'
          : (info.order === 'asc')
            ? 'angle-double-up'
            : 'angle-double-down'
      },
      toggleSortOrder () {
        this.sortOrder = this.getOrder(this.sortOrder)
        return this.sortOrder
      },
      linkGen (pageNum) {
        let obj = {
          name: `admin.${this.entity}.index`, query: Object.assign({}, this.$route.query)
        }
        obj.query.page = pageNum
        return obj
      },
      getOrder (val) {
        let asc = this.$t('filters.asc')
        let desc = this.$t('filters.desc')
        return val === asc ? desc : asc
      }

    }
  }
</script>