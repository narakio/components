<template>
    <div class="row mb-3">
        <div class="col-lg-2 pt-2">
            <button type="button" class="btn btn-primary" @click="resetFilters">
                {{$t('general.reset_filters')}}
            </button>
        </div>
        <div id="filters-list" class="col-lg-10 pt-2">
            <span
                    class="btn btn-default ml-2"
                    v-for="(button,idx) in filters"
                    :key="idx"
                    v-model="filters"
                    @click="removeFilter(idx)"
            >{{$t(`filter_labels.${entity}_${idx}`)}} {{button}}<button
                    type="button"
                    class="close button-list-close ml-2"
                    aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            </span>
        </div>
    </div>
</template>

<script>
  export default {
    name: 'table-filter',
    props: {
      filterButtons: Object,
      entity: String
    },
    computed: {
      filters () {
        return this.filterButtons
      }
    },
    data () {
      return {}
    },
    methods: {
      resetFilters () {
        this.$emit('filter-reset')
        this.$router.push({query: null})
      },
      removeFilter (idx) {
        let currentFilters = Object.assign({}, this.$route.query)
        delete currentFilters[idx]
        let obj = Object.assign({}, this.filters)
        delete obj[idx]
        this.$emit('filter-removed', obj)
        this.$router.push({query: currentFilters})
      }
    }
  }
</script>