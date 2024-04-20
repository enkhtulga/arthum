@extends('frontend.layouts.app')

@section('content')
<!-- Company Overview section START -->
<section class="container-fluid inner-Page" >
    <div class="card-panel">
        <div class="media wow fadeInUp" data-wow-duration="1s">
            <div class="companyIcon">
            </div>
            <div class="media-body">

                <div class="container">
                    @if(session('success_msg'))
                    <div class="alert alert-success fade in alert-dismissible show">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                         <span aria-hidden="true" style="font-size:20px">×</span>
                        </button>
                        {{ session('success_msg') }}
                    </div>
                    @endif
                    @if(session('error_msg'))
                    <div class="alert alert-danger fade in alert-dismissible show">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                          <span aria-hidden="true" style="font-size:20px">×</span>
                        </button>
                        {{ session('error_msg') }}
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <h1>Payment</h1>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <div class="panel panel-primary qpay-wrapper">
                                <img width="200" src="data:image/png;base64,{{$response['qr_image']}}" alt="" />
                                <p class="qpay-warning">Төлбөр төлөгдсөний дараа хуудас шилжинэ. Түр хүлээнэ үү. Баярлалаа.</p>
                                <div class="qpay-deeplink">
                                @foreach($response['urls'] as $k => $u)
                                    <a class="deeplink-item" href="{{$u['link']}}" title="{{$u['description']}}">
                                        <img width="50" src="{{$u['logo']}}" alt="{{$u['description']}}" />
                                    </a>
                                @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="clearfix"></div>
</section>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function () {
        var timer = setInterval(function () {
            $.ajax({
                url: '{{ route("qpay.check") }}',
                type: 'GET',
                dataType: 'html',
                success: function (data) {
                    var dta = JSON.parse(data);
                    if (dta && dta.redirect) {
                        window.location.href = dta.redirect;
                        clearInterval(timer);
                    }
                },
                error: function (jqXHR, exception) {
                    // Handle error if needed
                }
            });
        }, 10000);
    });
</script>
<style>
    .qpay-wrapper {
        margin: 0 auto;
        text-align: center;
        max-width: 240px;
    }
    .qpay-warning {
        color: #ff6000;
    }
    .qpay-deeplink {
        display: flex;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    .deeplink-item {
        margin: 5px;
    }
</style>
@endsection
