<!-- Start Contact -->
<section class="section pt-5 mt-4">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 p-0">
                <div class="card map border-0">
                    <div class="card-body p-0">
                        <iframe src="{{ $data['map'] }}" style="border:0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->
    </div>
    <!--end container-->

    <div class="container mt-100 mt-60">
        <div class="row align-items-center">
            <div class="col-lg-5 col-md-6 mt-4 mt-sm-0 pt-2 pt-sm-0 order-2 order-md-1">
                <div class="card custom-form rounded border-0 shadow p-4">
                    <form method="post" name="myForm" onsubmit="return validateForm()">
                        @csrf
                        <p id="error-msg" class="mb-0"></p>
                        <div id="simple-msg"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ $data['form']['items']['lebalNameCaption'] }} <span
                                            class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i data-feather="user" class="fea icon-sm icons"></i>
                                        <input name="name" id="name" type="text" class="form-control ps-5"
                                            placeholder="{{ $data['form']['items']['nameCaption'] }} :">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ $data['form']['items']['lebalEmailCaption'] }} <span
                                            class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i data-feather="mail" class="fea icon-sm icons"></i>
                                        <input name="email" id="email" type="email" class="form-control ps-5"
                                            placeholder="{{ $data['form']['items']['emailCaption'] }} :">
                                    </div>
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-12">
                                <div class="mb-3">
                                    <label
                                        class="form-label">{{ $data['form']['items']['lebalSubjectCaption'] }}</label>
                                    <div class="form-icon position-relative">
                                        <i data-feather="book" class="fea icon-sm icons"></i>
                                        <input name="subject" id="subject" class="form-control ps-5"
                                            placeholder="{{ $data['form']['items']['subjectCaption'] }} :">
                                    </div>
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">{{ $data['form']['items']['lebalMessageCaption'] }}
                                        <span class="text-danger">*</span></label>
                                    <div class="form-icon position-relative">
                                        <i data-feather="message-circle" class="fea icon-sm icons clearfix"></i>
                                        <textarea name="comments" id="comments" rows="4" class="form-control ps-5"
                                            placeholder="{{ $data['form']['items']['messageCaption'] }} :"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid">
                                    <button type="submit" id="submit" name="send" class="btn btn-primary">
                                        {{ $data['form']['items']['buttonCaption'] }}
                                    </button>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </form>
                </div>
                <!--end custom-form-->
            </div>
            <!--end col-->

            <div class="col-lg-7 col-md-6 order-1 order-md-2">
                <div class="title-heading ms-lg-4">
                    <h4 class="mb-4">{{ $data['mainCaption'] }}</h4>
                    @if ($data['description']['show'])
                        <p class="text-muted">
                            {{ $data['description']['caption'] }}
                        </p>
                    @endif

                    <div class="d-flex contact-detail align-items-center mt-3">
                        <div class="icon">
                            <i data-feather="mail" class="fea icon-m-md text-dark me-3"></i>
                        </div>
                        <div class="flex-1 content">
                            <h6 class="title fw-bold mb-0">{{ $data['elements'][0]['caption'] }}</h6>
                            <a href="mailto:{{ $data['elements'][0]['value'] }}"
                                class="text-primary">{{ $data['elements'][0]['value'] }}/a>
                        </div>
                    </div>

                    <div class="d-flex contact-detail align-items-center mt-3">
                        <div class="icon">
                            <i data-feather="phone" class="fea icon-m-md text-dark me-3"></i>
                        </div>
                        <div class="flex-1 content">
                            <h6 class="title fw-bold mb-0">{{ $data['elements'][1]['caption'] }}</h6>
                            <a href="tel:{{ $data['elements'][1]['value'] }}"
                                class="text-primary">{{ $data['elements'][1]['value'] }}</a>
                        </div>
                    </div>

                    <div class="d-flex contact-detail align-items-center mt-3">
                        <div class="icon">
                            <i data-feather="map-pin" class="fea icon-m-md text-dark me-3"></i>
                        </div>
                        <div class="flex-1 content">
                            <h6 class="title fw-bold mb-0">{{ $data['elements'][2]['caption'] }}</h6>
                            <p>{{ $data['elements'][2]['value'] }}</p>
                        </div>
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
<!-- End contact -->
