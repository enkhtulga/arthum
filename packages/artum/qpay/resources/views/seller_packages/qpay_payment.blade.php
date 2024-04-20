@extends('seller.layouts.app')

@section('panel_content')
<section class="py-8 bg-soft-primary">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 mx-auto text-center">
                <h1 class="mb-0 fw-700">{{ translate('Payment') }}</h1>
            </div>
        </div>
    </div>
</section>
<section class="py-4 py-lg-5">
        <div class="container">
            <div class="row row-cols-xxl-4 row-cols-lg-3 row-cols-md-2 row-cols-1 gutters-10 justify-content-center">
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
</section>
@section('script')
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
