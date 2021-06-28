@if ($data['show'])
    <!-- Project Start -->
    <section class="section bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center">
                    <div class="section-title mb-4 pb-2">
                        <h4 class="title mb-4">{{ $data['titleCaption'] }}</h4>
                        @if ($data['description']['show'])
                            <p class="text-muted para-desc mx-auto mb-0">
                                {{ $data['description']['descriptionCaption'] }}</p>
                        @endif

                    </div>
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <div class="row">
                @foreach ($data['items'] as $item)
                    <div class="col-md-6 col-12 mt-4 pt-2">
                        <div
                            class="card work-container work-modern position-relative overflow-hidden shadow rounded border-0">
                            <div class="card-body p-0">
                                <img src="{{ $item['src'] }}" class="img-fluid rounded" alt="work-image">
                                <div class="overlay-work bg-dark"></div>
                                <div class="content">
                                    <a href="{{ $item['linnk'] }}"
                                        class="title text-white d-block fw-bold">{{ $item['caption'] }}</a>
                                    <small class="text-light">{{ $item['tagCaption'] }}</small>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!--end col-->

                @endforeach

            </div>
            <!--end row-->
        </div>
        <!--end container-->
        @if ($data['footer']['show'])
            <div class="container mt-100 mt-60">
                <div class="row justify-content-center">
                    <div class="col-12 text-center">
                        <div class="section-title">
                            <h4 class="title mb-4">{{ $data['footer']['titleCaption'] }}</h4>
                            <p class="text-muted para-desc mx-auto">
                                {{ $data['footer']['descriptionCaption'] }}
                            </p>
                            @if ($data['footer']['BTN']['show'])
                                <div class="mt-4">
                                    <a href="{{ $data['footer']['BTN']['link'] }}" class="btn btn-outline-primary mt-2">
                                        {{ $data['footer']['BTN']['caption'] }}
                                    </a>
                                </div>

                            @endif


                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>
            <!--end container-->
        @endif

    </section>
    <!--end section-->
    <!-- Project End -->
@endif
