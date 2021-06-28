@if ($data['show'])

    <!-- whatWeDo2C -->
    <div class="container ">
        <div class="row justify-content-center">
            <div class="col-12 text-center">
                <div class="section-title mb-4 pb-2">
                    @if ($data['tag']['show'])
                        <span class="badge bg-primary rounded-pill mb-2">{{ $data['tag']['tagCaption'] }}
                        </span>
                    @endif
                    <h4 class="title mb-4">{{ $data['titleCaption'] }}</h4>
                    <p class="text-muted para-desc mx-auto mb-0">
                        {{ $data['descriptionCaption'] }}</p>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->

        <div class="row pb-5 mb-5">
            @foreach ($data['items'] as $card)
                @if ($card['active'])
                    <div class="col-md-4 col-12 mt-4 pt-2">
                        <div class="card text-center bg-primary bg-gradient rounded border-0">
                            <div class="card-body">
                                <div class="p-3 bg-light rounded shadow d-inline-block">
                                    <img src="{{ $card['icon'] }}" class="avatar avatar-small" alt="">
                                </div>
                                <div class="mt-4">
                                    <h5><a href="javascript:void(0)"
                                            class="text-white title-dark">{{ $card['caption'] }}</a>
                                    </h5>
                                    <p class="text-white-50 mt-3 mb-0">
                                        {{ $card['descriptionCaption'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                @else
                    <div class="col-md-4 col-12 mt-4 pt-2">
                        <div class="card text-center rounded border-0">
                            <div class="card-body">
                                <div class="p-3 bg-light rounded shadow d-inline-block">
                                    <img src="{{ $card['icon'] }}" class="avatar avatar-small" alt="">
                                </div>
                                <div class="mt-4">
                                    <h5><a href="javascript:void(0)" class="text-dark">{{ $card['caption'] }}</a>
                                    </h5>
                                    <p class="text-muted mt-3 mb-0">
                                        {{ $card['descriptionCaption'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end col-->
                @endif
            @endforeach

        </div>
        <!--end row-->
    </div>
    <!--end container-->
    <!-- whatWeDo2C -->
@endif
