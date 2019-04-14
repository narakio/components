<div class="footer">
    <div class="container">
        <div class="row no-gutters">
            <div class="col-sm-6 col-lg-4 text-center px-5">
                <h6 class="subscribe-newsletter">{{trans('titles.subscribe_newsletter')}}</h6>
                <form id="form-newsletter-subscribe"
                      accept-charset="UTF-8"
                      action="{{route('subscribe_newsletter')}}"
                      method="POST">
                    {{csrf_field()}}
                    <div class="form-group">
                        <input type="text" autocomplete="off"
                               name="full_name"
                               class="form-control text-center"
                               placeholder="{{trans('titles.subscribe_fullname')}}">
                    </div>
                    <div class="form-group">
                        <input type="email" autocomplete="email"
                               name="email"
                               class="form-control text-center"
                               placeholder="{{trans('titles.subscribe_email')}}">
                    </div>
                    <button type="submit"
                            class="btn btn-primary btn-block rounded-pill">{{trans('titles.subscribe')}}</button>
                </form>
            </div>
            <div class="col-6 col-lg-4">
                <h6 class="bold">Service</h6>
                <div class="list-group list-group-flush list-group-no-border list-group-sm">
                    <a href="javascript:void(0)" class="list-group-item">Help</a>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <h6 class="bold">{{config('app.name')}}</h6>
                <div class="list-group list-group-flush list-group-no-border list-group-sm">
                    <a href="about.html" class="list-group-item">About Us</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="copyright">{{trans('general.copyright')}}, {{config('app.name')}}</div>