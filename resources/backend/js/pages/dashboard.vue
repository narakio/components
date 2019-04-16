<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4" v-for="(data,entity) in info" :key="entity">
                <div class="small-box" :class="['bg-'+data.color]">
                    <div class="inner">
                        <h3>{{data.count}}</h3>
                        <p>{{$tc(`db.${entity}`,2)}}</p>
                    </div>
                    <div class="icon">
                        <i :class="'fa fa-'+data.icon"></i>
                    </div>
                    <router-link :to="{ name: `admin.${entity}.index` }" class="nav-link small-box-footer"
                                 active-class="active">
                        <span>More info <i class="fa fa-arrow-circle-o-right"></i></span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
  import axios from 'axios'

  export default {
    middleware: 'check-auth',
    layout: 'basic',
    name: 'dashboard',
    data () {
      return {
        info: {}
      }
    },
    methods: {
      getInfo (data) {
        this.info = data
      }
    },
    beforeRouteEnter (to, from, next) {
      axios.get(`/ajax/admin/dashboard`).then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    },
    metaInfo () {
      return {title: this.$t('title.dashboard')}
    }
  }
</script>