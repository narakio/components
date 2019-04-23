<div class="footer">
    <div class="container">
        <div class="row no-gutters">
            <div class="col-sm-6 col-lg-4 text-center px-5 col-left">
                <h6 class="subscribe-newsletter">{{trans('nk::titles.subscribe_newsletter')}}</h6>
                <form id="form-newsletter-subscribe"
                      accept-charset="UTF-8"
                      action="{{route('subscribe_newsletter')}}"
                      method="POST">
                    {{csrf_field()}}
                    <div class="form-group">
                        <input type="text" autocomplete="off"
                               name="full_name"
                               class="form-control text-center"
                               placeholder="{{trans('nk::titles.subscribe_fullname')}}">
                    </div>
                    <div class="form-group">
                        <input type="email" autocomplete="email"
                               name="email"
                               class="form-control text-center"
                               placeholder="{{trans('nk::titles.subscribe_email')}}">
                    </div>
                    <button type="submit"
                            class="btn btn-primary btn-block rounded-pill">{{trans('nk::titles.subscribe')}}</button>
                </form>
            </div>
            <div class="col-6 col-lg-4 col-links">
                <h6 class="bold">Service</h6>
                <div class="list-group list-group-flush list-group-no-border list-group-sm">
                    <a href="{{route_i18n('contact')}}">{{trans('nk::titles.contact_us')}}</a>
                    <a href="javascript:void(0)">{{trans('nk::titles.help')}}</a>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-links">
                <h6 class="bold">{{config('app.name')}}</h6>
                <div class="list-group list-group-flush list-group-no-border list-group-sm">
                    <a href="javascript:void(0)">{{trans('nk::titles.about_us')}}</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="copyright">{{trans('nk::general.copyright')}}, {{config('app.name')}}</div>