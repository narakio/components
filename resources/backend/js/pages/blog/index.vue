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
                                   @keyup.enter="filterBlogTitle">
                            <div class="input-group-append">
                                <label class="input-group-text"
                                       :title="$t('general.search')"
                                       @click="filterBlogTitle">
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
                                    <option value="del">{{$t('tables.option_del_blog')}}</option>
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
                                <span class="mr-2"
                                      v-if="data.total">{{data.total}}&nbsp;{{$tc(`db.${entity}`,data.total)}}</span>
                                <router-link :to="{name: 'admin.blog_posts.add'}">
                                    <button class="btn btn-add"
                                            type="button">{{$t('pages.blog.add_post')}}
                                    </button>
                                </router-link>
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
                <template #body-action="props">
                    <td>
                        <div class="inline">
                            <template v-if="props.row.blog_post_slug">
                                <router-link :to="{
                                                name: 'admin.blog_posts.edit',
                                                params: { slug: props.row.blog_post_slug }}">
                                    <button class="btn btn-sm btn-info"
                                            :title="$t(
                                                            'tables.edit_item',{
                                                            name:props.row[$t('db_raw_inv.blog_post_title')]
                                                            })"><i
                                        class="fa fa-pencil"></i></button>
                                </router-link>
                                <button type="button" class="btn btn-sm btn-danger"
                                        :title="$t('tables.delete_item',{name:props.row[$t('db_raw_inv.blog_post_title')]})"
                                        @click="deleteRow(
                                                            props.row,
                                                            'blog_post',
                                                            'blog_post_slug',
                                                            'blog_post_title',
                                                            '/ajax/admin/blog/post'
                                                        )">
                                    <i class="fa fa-trash"></i>
                                </button>
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
    name: 'blog',
    components: {
      'v-table': Table,
      TableFilter
    },
    data () {
      return {
        allSelected: false,
        selectApply: '',
        titleFilter: null,
        filterButtons: {},
        selectionBuffer: {},
        entity: 'blog_posts',
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
      setFilterButtons () {
        this.setFilterButton('title')
      },
      async applyToSelected () {
        let posts = this.$refs.table.getSelectedRows('blog_post_slug')
        if (posts.length > 0) {
          switch (this.selectApply) {
            case 'del':
              this.swalDeleteWarning(
                this.$t('modal.blog_post_delete.h'),
                this.$tc('modal.blog_post_delete.t', 2, {number: posts.length}),
                this.$t('general.delete')
              ).then(async (result) => {
                if (result.value) {
                  await axios.post('/ajax/admin/blog/post/batch/delete', {posts: posts})
                  this.refreshTableData()
                  this.swalNotification('success', this.$tc('message.blog_post_delete_ok', posts.length))
                }
              })
              break
          }
        }
      },
      filterBlogTitle () {
        this.applyFilter('title')
      },
    },
    metaInfo () {
      return {title: this.$t('title.media_index')}
    }
  }
</script>