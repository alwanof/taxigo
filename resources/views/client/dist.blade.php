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
                    <form action="{{ route('client.composse') }}" method="POST">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $order['service_id'] }}">
                        <input type="hidden" name="from_lat" value="{{ $order['from_lat'] }}">
                        <input type="hidden" name="from_lng" value="{{ $order['from_lng'] }}">

                        <input type="hidden" name="to_lat" id="to_lat">
                        <input type="hidden" name="to_lng" id="to_lng">

                        <input type="hidden" name="session" value="{{ $order['session'] }}">
                        <input type="hidden" name="hash" value="{{ $order['hash'] }}">

                        <input type="hidden" name="email" value="{{ $order['email'] }}">
                        <input type="hidden" name="phone" value="{{ $order['phone'] }}">
                        <input type="hidden" name="from_address" value="{{ $order['from_address'] }}">

                        <div class="my-3">
                            <label for="exampleInputEmail1"
                                class="form-label text-muted">{{ __('app.Enter your name') }}</label>
                            <input type="text" class="form-control" value="{{ $order['name'] }}"
                                placeholder="{{ __('app.Enter your name') }}" name="name" required readonly>
                        </div>

                        <div class="mb-3">
                            <label for="from_address" class="form-label text-muted">Destination</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Enter your Distination" id="to_address"
                                    name="to_address" required>
                                <button class="btn btn-outline-secondary" type="button" id="confirmDist">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                {{ __('app.Address hint1') }}
                                <i class="fas fa-map-marker-alt text-primary"></i> {{ __('app.Address hint2') }}
                            </div>
                        </div>
                        <div id="dist"></div>
                        <div class="mb-3">
                            <label class="form-label text-muted">{{ __('app.Note') }}</label>
                            <input type="text" class="form-control" placeholder="{{ __('app.Note') }}" name="note">
                        </div>
                        <div class="row">
                            <div class="col-12 d-grid gap-2">
                                <button type="submit" class="btn btn-primary">{{ __('app.SUBMIT') }}</button>
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
            $("#confirmDist").click(function() {
                $("#dist").slideToggle();
            });

            var defaultLat = {!! json_encode($mapCenter[0]) !!};
            var defaultLng = {!! json_encode($mapCenter[1]) !!};
            var lp = new locationPicker('dist', {
                setCurrentPosition: true, // You can omit this, defaults to true
                lat: defaultLat,
                lng: defaultLng

            }, {
                zoom: 15 // You can set any google map options here, zoom defaults to 15
            });


            google.maps.event.addListener(lp.map, 'idle', function(event) {
                // Get current location and show it in HTML
                var loc = lp.getMarkerPosition();
                lat = loc.lat;
                lng = loc.lng;

                //onIdlePositionView.innerHTML = 'The chosen location is ' + location.lat + ',' + location.lng;
                $.getJSON(
                    'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + loc.lat + ',' + loc
                    .lng +
                    '&key=AIzaSyANYVpeOpsNN4DqdKR4AKAyd03IQ3_9PvU',
                    function(result) {
                        document.getElementById('to_address').value = (loc.lat == defaultLat) ? '' :
                            result.results[0].formatted_address;
                        document.getElementById('to_lat').value = (loc.lat == defaultLat) ? null : loc
                            .lat;
                        document.getElementById('to_lng').value = (loc.lng == defaultLng) ? null : loc
                            .lng;
                    });
            });

        });

    </script>


@endsection

@section('css')

    <style>
        #dist {
            width: 100%;
            height: 300px;
            display: none;

        }

        /* Custom page CSS
                              -------------------------------------------------- */
        /* Not required for template or sticky footer method. */

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

        .vl {
            border-left: 1px solid #ddd;
            height: 50%;
            margin: 0 50%;
        }

        .checked {
            color: orange;
        }

    </style>
@endsection
