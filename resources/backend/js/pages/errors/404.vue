<template>

  <div class="container" style="color:#5b667d;">
    <div class="row justify-content-center">
      <h1 style="font-size:6rem;">404</h1>
    </div>
    <div class="row justify-content-center">
      <i class="fa fa-sitemap" style="font-size:6rem;"></i>
    </div>
    <div class="row justify-content-center">
      <h1 class="mb-4" style="font-size:4rem;">{{ $t('error.page_not_found') }}</h1>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'not-found'
  }
</script>
