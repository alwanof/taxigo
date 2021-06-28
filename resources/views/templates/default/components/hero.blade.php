@if ($data['show'])
    <!-- Hero Start -->
    <section class="bg-half-170 d-table w-100 overflow-hidden" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-7">
                    <div class="title-heading mt-4">
                        @if ($data['note']['show'])
                            <div class="alert alert-transparent alert-pills shadow" role="alert">
                                <span
                                    class="badge rounded-pill bg-primary me-1">{{ $data['note']['iconCaption'] }}</span>
                                <span class="content">{{ $data['note']['caption'] }}</span>
                            </div>
                        @endif
                        <h1 class="heading mb-3">{{ $data['titleCaption'] }}</h1>
                        <p class="para-desc text-muted">{{ $data['descriptionCaption'] }}</p>
                        <div class="mt-4 pt-2">
                            @if ($data['BTN']['show'])
                                <a href="javascript:void(0)"
                                    class="btn btn-primary m-1">{{ $data['BTN']['caption'] }}</a>
                            @endif
                            @if ($data['video']['show'])
                                <a href="#!" data-type="youtube" data-id="{{ $data['video']['linkID'] }}"
                                    class="btn btn-icon btn-pills btn-primary m-1 lightbox"><i data-feather="video"
                                        class="icons"></i></a><span
                                    class="fw-bold text-uppercase small align-middle ms-1">
                                    {{ $data['video']['caption'] }}
                                </span>
                            @endif


                        </div>
                    </div>
                </div>
                <!--end col-->

                <div class="col-lg-5 col-md-5 mt-4 pt-2 mt-sm-0 pt-sm-0">
                    <div class="classic-app-image position-relative">
                        <div class="bg-app-shape position-relative">
                            <img src="{{ $data['imageHolder'][0]['src'] }}" class="img-fluid mover mx-auto d-block "
                                alt="{{ $data['imageHolder'][0]['caption'] }}">
                        </div>
                        <div class="app-images d-none d-md-block">
                            <img src="{{ $data['imageHolder'][1]['src'] }}" class="img-fluid"
                                alt="{{ $data['imageHolder'][1]['caption'] }}">
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!--end container-->
    </section>
    <!--end section-->
    <!-- Hero End -->
@endif
