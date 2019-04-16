<template>
    <div class="card">
        <div class="card-body">
            <div class="tree-list-container container p-0 m-0">
                <div v-if="error" class="row">
                    <p class="text-danger">{{error}}</p>
                </div>
                <div class="row">
                    <tree-list :add-root-button-label="$t('pages.blog.add_root_button')"
                               :data="data"
                               :addCallback="addCat"
                               :editCallback="editCat"
                               :deleteCallback="deleteCat"
                    ></tree-list>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import TreeList from 'back_path/components/tree-list/TreeList'
  import axios from 'axios'

  export default {
    layout: 'basic',
    middleware: 'check-auth',
    name: 'blog-category',
    components: {
      TreeList
    },
    data () {
      return {
        data: [],
        error: ''
      }
    },
    methods: {
      getInfo (data) {
        this.data = data
      },
      async addCat (node, newValue) {
        let vm = this
        try {
          await axios.post(
            `/ajax/admin/blog/categories`,
            {label: newValue, parent: node.parent}
          ).then(({data}) => {
            if (data.hasOwnProperty('id')) {
              node.id = data.id
              delete node.parent
            } else {
              vm.error = this.$t('error.add_category', {cat: newValue})
              return false
            }
          })
          return node
        } catch (e) {
          vm.error = this.$t('error.add_category', {cat: newValue})
          return false
        }
      },
      async editCat (node, newValue) {
        try {
          await axios.patch(
            `/ajax/admin/blog/categories/${node.id}`,
            {label: newValue}
          )
        } catch (e) {return false}
      },
      async deleteCat (nodeId) {
        await axios.delete(
          `/ajax/admin/blog/categories/${nodeId}`
        )
      }
    },
    metaInfo () {
      return {title: this.$t('title.blog_category')}
    },
    beforeRouteEnter (to, from, next) {
      axios.get('/ajax/admin/blog/categories').then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    }
  }
</script>