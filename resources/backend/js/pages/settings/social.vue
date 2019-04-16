<template>
  <div class="container p-0 m-0">
    <div class="row p-0 m-0">
      <div class="col p-0 m-0">
        <form @submit.prevent="save">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right"></label>
            <div class="col-md-8">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="open_graph"
                       class="custom-control-input" id="chk-open-graph"
                       v-model="form.fields.open_graph">
                <label class="custom-control-label" for="chk-open-graph">{{ $t('pages.settings.enable_open_graph')
                  }}</label>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">{{ $t('pages.settings.facebook_publisher') }}</label>
            <div class="col-md-8">
              <input type="text" class="form-control" name="facebook_publisher"
                     id="input-facebook-publisher" autocomplete="off"
                     placeholder="https://www.facebook.com/publisher-name"
                     v-model="form.fields.facebook_publisher">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">{{ $t('pages.settings.facebook_app_id') }}</label>
            <div class="col-md-8">
              <input type="text" class="form-control" name="facebook_app_id"
                     id="input-facebook-app-id" autocomplete="off"
                     v-model="form.fields.facebook_app_id">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right"></label>
            <div class="col-md-8">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="twitter_cards"
                       class="custom-control-input" id="chk-twitter-cards"
                       v-model="form.fields.twitter_cards">
                <label class="custom-control-label"
                       for="chk-twitter-cards">{{ $t('pages.settings.enable_twitter_cards') }}</label>
              </div>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right">{{ $t('pages.settings.twitter_publisher') }}</label>
            <div class="col-md-8">
              <input type="text" class="form-control" name="twitter_publisher"
                     id="input-name" autocomplete="off" placeholder="@twitter"
                     v-model="form.fields.twitter_publisher">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-9 ml-md-auto">
              <submit-button :loading="form.busy">{{ $t('general.update') }}</submit-button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios'
  import { Form, HasError, AlertForm } from 'back_path/components/form'
  import SubmitButton from 'back_path/components/SubmitButton'
  import swal from 'back_path/mixins/sweet-alert'

  export default {
    name: 'settings-social',
    components: {
      Form,
      SubmitButton
    },
    mixins: [
      swal
    ],
    data () {
      return {
        form: new Form({
          open_graph: null,
          twitter_cards: null,
          twitter_publisher: null,
          facebook_app_id: null,
          facebook_publisher:null
        })
      }
    },
    methods: {
      async save () {
        await this.form.post('/ajax/admin/settings/social')
        this.swalNotification('success', this.$t('message.settings_updated'))
      },
      getInfo (data) {
        if (data.settings.length===undefined) {
          this.form = new Form(data.settings)
        }
      }
    },
    metaInfo () {
      return {title: this.$t('title.settings_social')}
    },
    beforeRouteEnter (to, from, next) {
      axios.get('/ajax/admin/settings/social').then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    }
  }
</script>