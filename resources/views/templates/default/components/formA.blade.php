@if ($data['show'])
    <!-- How It Work Start -->
    <section class="section bg-light border-bottom">
        @if ($data['header']['show'])
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 text-center">
                        <div class="section-title mb-4 pb-2">
                            <h4 class="title mb-4">{{ $data['header']['titleCaption'] }}</h4>
                            <p class="text-muted para-desc mb-0 mx-auto">{{ $data['header']['descriptionCaption'] }}
                            </p>
                        </div>
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->
            </div>

        @endif

        <div class="container mt-100 mt-60">
            <div class="row align-items-center">
                <div class="col-lg-7 col-md-6 order-2 order-md-1 mt-4 mt-sm-0 pt-2 pt-sm-0">
                    <div class="section-title">
                        <h4 class="title mb-4">
                            {{ $data['BIO']['titleCaption'] }}
                        </h4>
                        <p class="text-muted">
                            {{ $data['BIO']['descriptionCaption'] }}
                        </p>
                        @if ($data['BIO']['list']['show'])
                            <ul class="list-unstyled text-muted">
                                @foreach ($data['BIO']['list']['items'] as $items)
                                    <li class="mb-0"><span class="text-primary h5 me-2"><i
                                                class="uil uil-check-circle align-middle"></i></span>
                                        {{ $items['caption'] }}
                                    </li>

                                @endforeach
                            </ul>
                        @endif
                        @if ($data['BIO']['BTN']['show'])
                            <a href="{{ $data['BIO']['BTN']['link'] }}"
                                class="mt-3 h6 text-primary">{{ $data['BIO']['BTN']['caption'] }}</a> <i
                                class="uil uil-angle-right-b align-middle"></i></a>

                        @endif

                    </div>
                </div>
                <!--end col-->

                <div class="col-lg-5 col-md-6 order-1 order-md-2">
                    <div class="card rounded border-0 shadow ms-lg-5">
                        <div class="card-body">
                            <img src="themo/public/front/default/images/illustrator/Mobile_notification_SVG.svg" alt="">

                            <div class="content mt-4 pt-2">
                                <form method="post" name="myForm" onsubmit="return validateForm()">
                                    <p id="error-msg" class="mb-0"></p>
                                    <div id="simple-msg"></div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label
                                                    class="form-label">{{ $data['form']['items']['lebalNameCaption'] }}
                                                    <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="user" class="fea icon-sm icons"></i>
                                                    <input name="name" id="name" type="text" class="form-control ps-5"
                                                        placeholder="{{ $data['form']['items']['nameCaption'] }} :">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label
                                                    class="form-label">{{ $data['form']['items']['lebalEmailCaption'] }}
                                                    <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="mail" class="fea icon-sm icons"></i>
                                                    <input name="email" id="email" type="email"
                                                        class="form-control ps-5"
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
                                                <label
                                                    class="form-label">{{ $data['form']['items']['lebalMessageCaption'] }}
                                                    <span class="text-danger">*</span></label>
                                                <div class="form-icon position-relative">
                                                    <i data-feather="message-circle"
                                                        class="fea icon-sm icons clearfix"></i>
                                                    <textarea name="comments" id="comments" rows="4"
                                                        class="form-control ps-5"
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
    <!-- How It Work End -->

@endif
