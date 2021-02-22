<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">

    <title>Test Lab!</title>
</head>

<body>
    <div class="container p-3">
        <h1 class="text-center m-3">Order Test Lab!</h1>
        <div class="text-center">
            <form class="row row-cols-lg-auto g-3 align-items-center" method="POST"
                action="{{ route('test.create') }}">
                @csrf
                @if (!$xdata['order'])
                    <div class="col-12">
                        <label class="visually-hidden" for="inlineFormSelectPref">Preference</label>
                        <select class="form-select" name="service_id" required>
                            <option disabled>Choose...</option>
                            <option value="1">OFFER</option>
                            <option value="3">NONE</option>
                            <option value="4">DRIVER</option>
                            <option value="5">TRACK</option>
                        </select>
                    </div>
                @endif
                <div class="col-12">
                    @if ($xdata['order'])
                        <a href="{{ route('test.reset') }}" class="btn btn-danger">Reset</a> |
                        {{ $xdata['service']->plan }}
                    @else
                        <button type="submit" class="btn btn-primary">Create Order</button>
                    @endif
                </div>
            </form>


        </div>
        <hr>
        <div class="row">
            <div class="div col">
                <div class="card">
                    <div class="card-header">
                        Front
                    </div>
                    <div class="card-body">
                        @if (!$xdata['order'])
                            <div class="alert alert-danger" role="alert">
                                No Order!
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    {{ $xdata['order']->name }}
                                    <span class="badge bg-secondary mx-1">{{ $xdata['order']->status }}</span>
                                </li>
                                <li class="list-group-item">{{ $xdata['order']->updated_at }}</li>
                                <li class="list-group-item">ED:/{{ round($xdata['order']->est_distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">ET:/{{ round($xdata['order']->est_time / 60, 0) }} min
                                </li>
                                <li class="list-group-item">EP:/{{ $xdata['order']->est_price }} TL</li>
                                <li class="list-group-item">Distanse:/{{ round($xdata['order']->distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">Time:/{{ round($xdata['order']->duration / 60, 0) }} min
                                </li>
                                <li class="list-group-item">Price:/{{ $xdata['order']->total }} TL</li>
                                @if ($xdata['order']->status == 3)
                                    <li class="list-group-item">
                                        <a href="{{ route('test.front.accept') }}" class="btn btn-success">Accept</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="{{ route('test.front.reject') }}" class="btn  btn-danger">Reject</a>
                                    </li>
                                @endif
                                @if ($xdata['order']->status == 21 || $xdata['order']->status == 22 || $xdata['order']->status == 23)
                                    <li class="list-group-item">
                                        <img src="storage/{{ $xdata['order']->driver->avatar }}"
                                            class="img-thumbnail" alt="">
                                    </li>
                                    <li class="list-group-item">
                                        {{ $xdata['order']->driver->name }}
                                    </li>

                                @endif

                            </ul>
                        @endif
                    </div>

                </div>
            </div>
            <div class="div col">
                <div class="card">
                    <div class="card-header">
                        Office
                    </div>
                    <div class="card-body">
                        @if (!$xdata['order'])
                            <div class="alert alert-danger" role="alert">
                                No Order!
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    {{ $xdata['order']->name }}
                                    <span class="badge bg-secondary mx-1">{{ $xdata['order']->status }}</span>
                                </li>
                                <li class="list-group-item">ED:/{{ round($xdata['order']->est_distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">ET:/{{ round($xdata['order']->est_time / 60, 0) }} min
                                </li>
                                <li class="list-group-item">EP:/{{ $xdata['order']->est_price }} TL</li>
                                <li class="list-group-item">
                                    Distanse:/{{ round($xdata['order']->distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">Time:/{{ round($xdata['order']->duration / 60, 0) }} min
                                </li>
                                <li class="list-group-item">Price:/{{ $xdata['order']->total }} TL</li>
                                @if ($xdata['order']->status == 0)
                                    <li class="list-group-item">

                                        @if ($xdata['service']->plan != 'OFFER')
                                            <a href="{{ route('test.office.accept') }}"
                                                class="btn btn-success">Accept</a>
                                        @else
                                            <form method="POST" action="{{ route('test.send.offer') }}">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="text" name="total" value="0" class="form-control">
                                                    <div class="input-group-text">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-primary">Send</button>
                                                    </div>
                                                </div>
                                            </form>

                                        @endif
                                    </li>
                                    <li class="list-group-item">
                                        <a href="{{ route('test.office.reject') }}"
                                            class="btn  btn-danger">Reject</a>
                                    </li>

                                @endif
                                @if ($xdata['order']->status == 1)
                                    <form method="POST" action="{{ route('test.driver.take.order') }}">
                                        @csrf
                                        <div class="input-group">
                                            <select class="form-select" name="driver_id" required>
                                                @foreach ($xdata['order']->drivers as $driver)
                                                    <option value="{{ $driver->id }}">{{ $driver->name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                            <div class="input-group-text">
                                                <button type="submit" class="btn btn-sm btn-primary">Select</button>
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            <div class="div col">
                <div class="card">
                    <div class="card-header">
                        Driver1:
                        @if ($xdata['order'])
                            @if ($xdata['order']->driver_id == 6)
                                {{ $xdata['order']->driver->name }}
                                <span class="badge bg-secondary mx-1">{{ $xdata['order']->driver->busy }}</span>
                                <span class="badge bg-secondary mx-1">{{ $xdata['order']->driver->distance }}m</span>
                            @endif
                        @endif
                    </div>
                    <div class="card-body">
                        @if (!$xdata['driver1'])
                            <div class="alert alert-danger" role="alert">
                                No Order!
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    {{ $xdata['order']->name }}
                                    <span class="badge bg-secondary mx-1">{{ $xdata['order']->status }}</span>
                                </li>
                                <li class="list-group-item">ED:/{{ round($xdata['order']->est_distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">ET:/{{ round($xdata['order']->est_time / 60, 0) }} min
                                </li>
                                <li class="list-group-item">EP:/{{ $xdata['order']->est_price }} TL</li>
                                <li class="list-group-item">
                                    Distanse:/{{ round($xdata['order']->distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">Time:/{{ round($xdata['order']->duration / 60, 0) }} min
                                </li>
                                <li class="list-group-item">Price:/{{ $xdata['order']->total }} TL</li>
                                @if ($xdata['order']->status == 2)
                                    <li class="list-group-item">
                                        <a href="{{ route('test.driver.accept') }}"
                                            class="btn btn-success">Accept</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="{{ route('test.driver.reject') }}"
                                            class="btn  btn-danger">Reject</a>
                                    </li>
                                @endif
                                @if ($xdata['order']->status == 21)
                                    <li class="list-group-item">
                                        <a href="{{ route('test.order.start') }}" class="btn btn-success">Start
                                            Trip</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="{{ route('test.order.abort') }}" class="btn btn-danger">Abort</a>
                                    </li>
                                @endif
                                @if ($xdata['order']->status == 22)
                                    <li class="list-group-item">
                                        <a href="{{ route('test.order.end') }}" class="btn btn-warning">End Trip</a>
                                    </li>
                                @endif
                                @if ($xdata['order']->status == 23)
                                    <li class="list-group-item">
                                        <form method="POST" action="{{ route('test.order.complete') }}">
                                            @csrf
                                            <div class="input-group">
                                                <input type="text" name="total" value="{{ $xdata['order']->total }}"
                                                    class="form-control">
                                                <div class="input-group-text">
                                                    <button type="submit" class="btn btn-sm btn-info">Complete</button>
                                                </div>
                                            </div>
                                        </form>
                                    </li>
                                @endif
                                <form method="POST" action="{{ route('test.tracking') }}">
                                    @csrf
                                    <div class="mb-1">
                                        <select name="loc" class="form-control">
                                            <option value="41.0220874,28.9447355">A0</option>
                                            <option value="41.0222871,28.9446004">A1</option>
                                            <option value="41.0215747,28.945396">A2</option>
                                            <option value="41.0191247,28.9405414">S0</option>
                                            <option value="41.0153066,28.943687">S1</option>
                                            <option value="41.0124116,28.9422497">S2</option>
                                            <option value="41.0110715,28.941122">S3</option>
                                            <option value="41.0109667,28.9416802">S4</option>
                                            <option value="41.008032,28.9348213">E0</option>
                                        </select>
                                    </div>

                                    <div class="input-group-text">
                                        <button type="submit" class="btn btn-sm btn-info">Move</button>
                                    </div>
                                </form>
                            </ul>

                        @endif
                    </div>
                </div>
            </div>
            <div class="div col">
                <div class="card">
                    <div class="card-header">
                        Driver2:
                        @if ($xdata['order'])
                            @if ($xdata['order']->driver_id == 16)
                                {{ $xdata['order']->driver->name }}
                                <span class="badge bg-secondary mx-1">{{ $xdata['order']->driver->busy }}</span>
                                <span class="badge bg-secondary mx-1">{{ $xdata['order']->driver->distance }}m</span>
                            @endif
                        @endif

                    </div>
                    <div class="card-body">
                        @if (!$xdata['driver2'])
                            <div class="alert alert-danger" role="alert">
                                No Order!
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    {{ $xdata['order']->name }}
                                    <span class="badge bg-secondary mx-1">{{ $xdata['order']->status }}</span>
                                </li>
                                <li class="list-group-item">ED:/{{ round($xdata['order']->est_distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">ET:/{{ round($xdata['order']->est_time / 60, 0) }} min
                                </li>
                                <li class="list-group-item">EP:/{{ $xdata['order']->est_price }} TL</li>
                                <li class="list-group-item">
                                    Distanse:/{{ round($xdata['order']->distance / 1000, 2) }}
                                    km</li>
                                <li class="list-group-item">Time:/{{ round($xdata['order']->duration / 60, 0) }} min
                                </li>
                                <li class="list-group-item">Price:/{{ $xdata['order']->total }} TL</li>
                                @if ($xdata['order']->status == 2)
                                    <li class="list-group-item">
                                        <a href="{{ route('test.driver.accept') }}"
                                            class="btn btn-success">Accept</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="{{ route('test.driver.reject') }}"
                                            class="btn  btn-danger">Reject</a>
                                    </li>
                                @endif
                                @if ($xdata['order']->status == 21)
                                    <li class="list-group-item">
                                        <a href="{{ route('test.order.start') }}" class="btn btn-success">Start
                                            Trip</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a href="{{ route('test.order.abort') }}" class="btn btn-danger">Abort</a>
                                    </li>
                                @endif
                                @if ($xdata['order']->status == 22)
                                    <li class="list-group-item">
                                        <a href="{{ route('test.order.end') }}" class="btn btn-warning">End Trip</a>
                                    </li>
                                @endif
                                @if ($xdata['order']->status == 23)
                                    <li class="list-group-item">
                                        <form method="POST" action="{{ route('test.order.complete') }}">
                                            @csrf
                                            <div class="input-group">
                                                <input type="text" name="total" value="{{ $xdata['order']->total }}"
                                                    class="form-control">
                                                <div class="input-group-text">
                                                    <button type="submit" class="btn btn-sm btn-info">Complete</button>
                                                </div>
                                            </div>
                                        </form>
                                    </li>
                                @endif
                                <form method="POST" action="{{ route('test.tracking') }}">
                                    @csrf
                                    <div class="mb-1">
                                        <select name="loc" class="form-control">
                                            <option value="41.0220874,28.9447355">A0</option>
                                            <option value="41.0222871,28.9446004">A1</option>
                                            <option value="41.0215747,28.945396">A2</option>
                                            <option value="41.0191247,28.9405414">S0</option>
                                            <option value="41.0153066,28.943687">S1</option>
                                            <option value="41.0124116,28.9422497">S2</option>
                                            <option value="41.0110715,28.941122">S3</option>
                                            <option value="41.0109667,28.9416802">S4</option>
                                            <option value="41.008032,28.9348213">E0</option>
                                        </select>
                                    </div>

                                    <div class="input-group-text">
                                        <button type="submit" class="btn btn-sm btn-info">Move</button>
                                    </div>
                                </form>
                            </ul>

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous">
    </script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>
    -->
</body>

</html>
