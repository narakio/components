<script src="https://www.google.com/recaptcha/api.js?render={{env('RECAPTCHA_SITE_KEY')}}"></script>
<script>
  grecaptcha.ready(function () {
    grecaptcha.execute('{{env('RECAPTCHA_SITE_KEY')}}', {action: '{{$action}}'}).then(function (token) {
      document.querySelector('.g-recaptcha').value = token
    })
  })
</script>