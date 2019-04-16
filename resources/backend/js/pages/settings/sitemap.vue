<template>
  <div class="container p-0 m-0">
    <div class="row p-0 m-0">
      <div class="col p-0 m-0">
        <form @submit.prevent="save">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-md-right"></label>
            <div class="col-md-8">
              <div class="custom-control custom-switch">
                <input type="checkbox" name="sitemap"
                       class="custom-control-input" id="chk-sitemap"
                       v-model="form.fields.sitemap">
                <label class="custom-control-label" for="chk-sitemap">{{ $t('pages.settings.enable_sitemap')
                  }}</label>
              </div>
            </div>
          </div>
          <hr class="col-md-9 ml-md-auto">
          <p class="font-italic">{{ $t('pages.settings.sitemap_help') }}</p>
          <div v-for="(field, idx) in form.fields.links" class="form-group row" :key="'links'+idx">
            <div class="row form-inline ml-4" style="width:90%">
              <span class="mr-2" style="width:90%">{{field.link}}</span>
              <button type="button"
                      class="btn btn-danger" @click="delLink(idx)"><i class="fa fa-minus"></i></button>
            </div>
            <div class="row ml-4" style="font-size:0.7rem;">({{convertDate(field.date)}})</div>
          </div>
          <div class="form-group row py-0 my-0">
            <div class="col form-inline py-0 my-0">
              <label class="col-form-label" style="width:60%">{{$t('pages.settings.link_url')}}</label>
              <label class="col-form-label" style="width:40%">{{$t('pages.settings.link_last_modified')}}</label>
            </div>
          </div>
          <div class="form-group row">
            <div class="col form-inline">
              <input type="text" class="form-control mr-2" id="input-link"
                     ref="inputLink" autocomplete="off" style="width:60%">
              <button type="button" class="btn btn-info" @click="addLink"><i class="fa fa-plus"></i></button>
              <datepicker
                  ref="datePicker"
                  name="updated_at"
                  :show-clear-button="false"
                  :open-left="true"
                  :class-list="['ml-3']"
                  v-model="link_updated_at"
                  :placeholder="$t('pages.settings.link_date_placeholder')"
              ></datepicker>
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
  import Datepicker from 'back_path/components/Datepicker'
  import dayjs from 'dayjs'
  import fr from 'dayjs/locale/fr'

  export default {
    name: 'settings-social',
    components: {
      Form,
      SubmitButton,
      Datepicker
    },
    mixins: [
      swal
    ],
    data () {
      return {
        form: new Form({
          sitemap: null,
          links: []
        }),
        link_updated_at: new Date()
      }
    },
    methods: {
      async save () {
        await this.form.post('/ajax/admin/settings/sitemap')
        this.swalNotification('success', this.$t('message.settings_updated'))
      },
      getInfo (data) {
        if (data.settings.length===undefined) {
          this.form = new Form(data.settings)
        }
      },
      convertDate(date){
        return dayjs(date)
          .locale(this.$store.getters['prefs/locale'])
          .format(this.$store.getters['prefs/dateTimeFormat'])
      },
      addLink () {
        let link = this.$refs.inputLink.value
        let date = this.link_updated_at
        if (link) {
          this.form.fields.links.push({link:link,date:date})
          this.$refs.inputLink.value = ''
          this.$refs.inputLink.focus()
        }
      },
      delLink (index) {
        this.form.fields.links.splice(index, 1)
      },
    },
    metaInfo () {
      return {title: this.$t('title.settings_sitemap')}
    },
    beforeRouteEnter (to, from, next) {
      axios.get('/ajax/admin/settings/sitemap').then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    }
  }
</script>