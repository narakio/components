<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="{{asset('favicon-bk.ico')}}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width, initial-scale=1 maximum-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <title></title>
    <link href="{{ mix('css/app.css','1b8eb') }}" rel="stylesheet">
</head>
<body id="backend" class="hold-transition skin-blue sidebar-mini">

<div id="app"></div>
@include('core::partials.javascript_footer')
@if (app()->environment()=='production')
    {{--<script src="{{ mix('js/manifest.js','1b8eb') }}"></script>--}}
    {{--<script src="{{ mix('js/vendor.js','1b8eb') }}"></script>--}}
    <script src="{{ mix('js/app.js','1b8eb') }}"></script>
@else
    <script src="{{ mix('js/app.js','1b8eb') }}"></script>
@endif
</body>
</html>
