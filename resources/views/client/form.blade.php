@extends('layouts.front')

@section('title', 'create')
@section('bodyClass', 'd-flex flex-column h-100')
@section('content')
    <main class="flex-shrink-0">
        <div class="container pt-2">
            <div class="row">
                <div class="col">
                    <img src="/images/logo-sm.png" class="mx-auto d-block" height="100"
                        alt="{{ config('app.name', 'Project0') }}">
                    <!-- Logo area-->
                    <div class="row mt-3">
                        <div class="col-3">
                            <img src="/storage/{{ $office->avatar }}" class="mx-auto d-block" width="93" alt="logo">
                        </div>
                        <div class="col-9 border-bottom p-2 px-3">
                            <h4>{{ $office->name }}</h4>
                            <a href="tel:{{ $office->settings['phone'] }}" class="btn btn-sm btn-success">
                                <i class="fas fa-phone-alt"></i>
                                {{ __('app.Call') }}
                            </a>

                        </div>
                    </div>
                    <form action="{{ route('client.dist') }}" method="POST">
                        @csrf
                        <input type="hidden" name="from_lat" id="from_lat">
                        <input type="hidden" name="from_lng" id="from_lng">

                        <input type="hidden" name="session" value="{{ $session }}">
                        <input type="hidden" name="hash" value="{{ $office->id . '%&' . $session . '%&' . $agent->id }}">
                        <div class="row mt-3 text-center">
                            @foreach ($office->services as $service)
                                @if ($service->active)
                                    <div class="col-4">
                                        <img src="/storage/{{ $service->vehicle->avatar }}" class="img-thumbnail"
                                            alt="yellow">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="service_id"
                                                value="{{ $service->id }}" required>
                                            <label class="form-check-label">
                                                {{ $service->title }}
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <!-- alert -->
                        <div class="alert alert-warning mt-1 show fade" id="alert" role="alert">
                            Estimated time to arival: <i class="far fa-clock"></i> <strong id="estTime"></strong>
                            <span id="loading" class="spinner-border text-warning spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </span>

                        </div>
                        <div class="my-3">
                            <label for="exampleInputEmail1"
                                class="form-label text-muted">{{ __('app.Enter your name') }}</label>
                            <input type="text" class="form-control" value="{{ $meta['name'] }}"
                                placeholder="{{ __('app.Enter your name') }}" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputEmail1"
                                class="form-label text-muted">{{ __('app.Enter your phone') }}</label>
                            <input type="text" class="form-control" value="{{ $meta['phone'] }}"
                                placeholder="{{ __('app.Enter your phone') }}" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">{{ __('app.Enter your email') }}</label>
                            <input type="email" class="form-control" value="{{ $meta['email'] }}"
                                placeholder="{{ __('app.Enter your email') }}" name="email" required>
                            <div class="form-text">We'll never share your email with anyone else.</div>
                        </div>
                        <div class="mb-3">
                            <label for="from_address" class="form-label text-muted">Current Location</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ $meta['address'] }}"
                                    placeholder="Enter your address" id="from_address" name="from_address" required
                                    readonly>
                                <button class="btn btn-outline-secondary" type="button" id="confirmSource">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                {{ __('app.Address hint1') }}
                                <i class="fas fa-map-marker-alt text-primary"></i> {{ __('app.Address hint2') }}
                            </div>
                        </div>
                        <div id="source"></div>
                        <div class="row mt-3">
                            <div class="col-10 d-grid gap-2">
                                <button type="submit" class="btn btn-primary">{{ __('app.Continue') }}</button>
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-sliders-h"></i>
                                </button>

                            </div>
                        </div>


                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyANYVpeOpsNN4DqdKR4AKAyd03IQ3_9PvU"></script>
    <script src="https://unpkg.com/location-picker/dist/location-picker.min.js"></script>

    <script>
        $(document).ready(function() {



            $("#confirmSource").click(function() {
                $("#source").slideToggle();
            });

            var defaultLat = {!! json_encode($mapCenter[0]) !!};
            var defaultLng = {!! json_encode($mapCenter[1]) !!};
            var office = {!! json_encode($office) !!};
            //var defaultLat = 41.021011;
            //var defaultLng = 28.931812;



            var lp = new locationPicker('source', {
                setCurrentPosition: true, // You can omit this, defaults to true
                //lat: defaultLat,
                //lng: defaultLng,

            }, {
                zoom: 15, // You can set any google map options here, zoom defaults to 15,
                gestureHandling: "greedy",
                mapTypeControl: false,
                draggable: true,
                disableDefaultUI: true,
                zoomControl: false,
                styles: [{
                    stylers: [{
                        saturation: -100
                    }]
                }]
            });


            google.maps.event.addListener(lp.map, 'idle', function(event) {
                // Get current location and show it in HTML
                var loc = lp.getMarkerPosition();

                if (Math.abs(loc.lat - 34.4346) < 0.001 && Math.abs(loc.lng - 35.8362) < 0.001) {
                    loc = {
                        lat: defaultLat,
                        lng: defaultLng
                    }
                }

                $.getJSON(
                    'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + loc.lat + ',' + loc
                    .lng + '&key=AIzaSyANYVpeOpsNN4DqdKR4AKAyd03IQ3_9PvU',
                    function(result) {

                        //console.log({!! json_encode($mapCenter) !!});
                        document.getElementById('from_address').value = result.results[0]
                            .formatted_address;
                        document.getElementById('from_lat').value = loc.lat;
                        document.getElementById('from_lng').value = loc.lng;
                    });
            });
            // service esteemed
            $("#loading").hide();
            $("input[name='service_id']").click(function() {
                $("#alert").show();
                $("#loading").show();
                var locc = lp.getMarkerPosition();

                $.get("/api/nearby/" + office.id + "/" + locc.lat + "/" + locc.lng + "/" + $(this)
                    .val(),
                    function(data, status) {
                        $("#estTime").text('UNKnown');
                        if (data.time > 0) {
                            $("#estTime").text(Math.round(data.time / 60) + 'min');
                        }
                        $("#loading").hide();
                    });
            });

            // end

        });

    </script>

@endsection

@section('css')

    <style>
        .container {
            width: auto;
            max-width: 680px;
            padding: 0 15px;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .form-check-input:checked {
            background-color: #fbb921;
            border-color: #fbb921;
        }

        .form-check .form-check-input {
            float: left;
            margin-left: 0em;
        }

        .btn-primary {
            color: #fff;
            background-color: #fbb921;
            border-color: #fbb921;
        }

        #source {
            width: 100%;
            height: 300px;
            display: none;
        }

        #alert {
            display: none;
        }

    </style>
@endsection
