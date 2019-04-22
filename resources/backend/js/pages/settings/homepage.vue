<template>
  <div class="container p-0 m-0">
    <div class="row p-0 m-0">
      <div class="col p-0 m-0">
        <form @submit.prevent="save">

        </form>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'
  import { Form, HasError, AlertForm } from 'back_path/components/form'
  import ButtonGroup from 'back_path/components/ButtonGroup'
  import SubmitButton from 'back_path/components/SubmitButton'
  import swal from 'back_path/mixins/sweet-alert'

  export default {
    name: 'settings-general',
    components: {
      Form,
      ButtonGroup,
      SubmitButton
    },
    mixins: [
      swal
    ],
    data () {
      return {
        form: new Form({

        }),
      }
    },
    methods: {
      save(){

      }
    },
    metaInfo () {
      return {title: this.$t('title.settings')}
    },
    beforeRouteEnter (to, from, next) {
      axios.get('/ajax/admin/settings/homepage').then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    }
  }
</script>