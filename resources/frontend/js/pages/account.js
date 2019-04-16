import axios from 'axios'
import swal from 'sweetalert2/dist/sweetalert2.js'
import i18n from 'front_path/plugins/i18n'

$(document).ready(function () {
  $('#btn-account-delete').click(function (e) {
    swal.fire({
      title: i18n.t('modal.user_delete.h'),
      text: i18n.t('modal.user_delete.t'),
      type: 'question',
      showCancelButton: true,
      confirmButtonColor: '#4b0f09',
      cancelButtonColor: '#616161',
      confirmButtonText: i18n.t('general.confirm'),
      cancelButtonText: i18n.t('general.cancel')
    }).then(async (result) => {
      if (result.value) {
        axios.delete('/user/delete').then(() => {
          swal.fire({
            text: i18n.t('modal.user_delete_confirm.t'),
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: '#4b0f09',
            confirmButtonText: i18n.t('general.ok')
          }).then(() => {
            window.location.reload()
          })
        })
      }
    })
  })
})