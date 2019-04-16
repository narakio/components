<template>
    <div class="card">
        <div class="card-body">
            <div class="card-title">
                <form @submit.prevent="update">
                    <input type="hidden" name="added_users" v-model="form.fields.added">
                    <input type="hidden" name="removed_users" v-model="form.fields.removed">
                    <button class="btn btn-primary float-right"
                            :disabled="removedUsers.length===0&&addedUsers.length===0">
                        {{$t('general.save_changes')}}
                    </button>
                </form>
                <h5>{{$t('pages.members.group_name')}}&nbsp;{{this.$route.params.group}}
                </h5>
            </div>
            <div id="member_edit_preview" class="card mb-3">
                <div class="card-header">
                    {{$t('pages.members.edit_preview')}}
                </div>
                <div class="card-body">
                    <div class="row ml-1" v-if="removedUsers.length>0||addedUsers.length>0">
                        <div class="col-md">
                            <div class="row">
                                <p>{{$t('pages.members.user_add_tag')}}</p>
                            </div>
                            <div class="row">
                                <ul v-if="addedUsers.length>0" class="list-group col-md-6">
                                    <li v-for="addedUser in addedUsers"
                                        class="list-group-item list-group-item-action list-group-item-success">
                                        {{addedUser.text}}
                                    </li>
                                </ul>
                                <p v-else>{{$t('pages.members.user_no_add')}}</p>
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="row">
                                <p>{{$t('pages.members.user_remove_tag')}}</p>
                            </div>
                            <ul v-if="removedUsers.length>0" class="list-group col-md-6">
                                <li v-for="(removedUser,idx) in removedUsers" :key="idx"
                                    class="list-group-item list-group-item-action list-group-item-danger member-tag-wrapper"
                                    @click="returnToUsersList(removedUser,idx)">
                                    <div>
                                        <div class="member-tag-text">{{removedUser.text}}</div>
                                        <div class="member-tag-btn">
                                            <i v-if="userCount<=userCountThreshold"
                                               href="#" class="button-list-close"></i>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <p v-else>{{$t('pages.members.user_no_remove')}}</p>
                        </div>
                    </div>
                    <p v-else>{{$t('pages.members.no_changes')}}</p>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    {{$t('pages.members.add_members')}}
                </div>
                <div class="card-body mb-2">
                    <input-tag-search :typeahead="true"
                                      :placeholder="$t('pages.members.member_search')"
                                      :searchUrl="'/ajax/admin/users/search'"
                                      @updateAddedItems="updateAddedUsersFromSearch"/>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">
                    {{$t('pages.members.remove_members')}}
                </div>
                <div class="card-body mb-2">
                    <div v-if="userCount>userCountThreshold">
                        <input-tag-search :typeahead="true"
                                          :placeholder="$t('pages.members.member_search')"
                                          :searchUrl="`/ajax/admin/members/${this.$route.params.group}/search`"
                                          @updateAddedItems="updateRemovedUsersFromSearch"/>
                    </div>
                    <div v-else-if="userCount>0">
                        <div class="container row">
                            <p>{{$t('pages.members.current_members')}}</p>
                        </div>
                        <div id="group-members-list" class="container row">
                            <div v-for="(member,idx) in members" :key="idx" class="col-md-3">
                                <div class="list-group-item list-group-item-action member-tag-wrapper"
                                     @click="addToRemoveUsersList(member,idx)">
                                    <div>
                                        <div class="member-tag-text">{{member.text}}</div>
                                        <div class="member-tag-btn"><i
                                                href="#" class="button-list-close"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else>{{$t('pages.members.user_none')}}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import InputTagSearch from 'back_path/components/InputTagSearch'
  import axios from 'axios'
  import { Form, HasError } from 'back_path/components/form'

  export default {
    name: 'member',
    layout: 'basic',
    middleware: 'check-auth',
    components: {
      InputTagSearch
    },
    data () {
      return {
        addedUsers: [],
        removedUsers: [],
        members: [],
        userCount: 0,
        userCountThreshold: 25,
        form: new Form({
          removed: [],
          added: []
        })
      }
    },
    methods: {
      updateAddedUsersFromSearch (users) {
        this.addedUsers = users
        this.form.fields.added = this.addedUsers
      },
      updateRemovedUsersFromSearch (users) {
        this.removedUsers = users
        this.form.fields.removed = this.removedUsers
      },
      getInfo (data) {
        this.userCount = data.count
        if (data.hasOwnProperty('users')) {
          this.members = data.users
        }
      },
      addToRemoveUsersList (elem, index) {
        this.members.splice(index, 1)
        this.removedUsers.push(elem)
        this.form.fields.removed = this.removedUsers
      },
      returnToUsersList (elem, index) {
        this.removedUsers.splice(index, 1)
        this.members.unshift(elem)
        this.form.fields.removed = this.removedUsers
      },
      update (e) {
        this.form.patch(`/ajax/admin/members/${this.$route.params.group}`).then(() => {
          this.$store.dispatch(
            'session/setFlashMessage',
            {msg: {type: 'success', text: this.$t('message.group_update_ok')}}
          )
          this.$router.push({name: 'admin.groups.index'})
        })
      }
    },
    metaInfo () {
      return {title: this.$t('title.members')}
    },
    beforeRouteEnter (to, from, next) {
      axios.get(`/ajax/admin/members/${to.params.group}`).then(({data}) => {
        next(vm => vm.getInfo(data))
      })
    }
  }
</script>