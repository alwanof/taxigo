  @if ($data['show'])
      <!-- rightStory2C -->
      <div class="container mt-100 mt-60">
          <div class="row align-items-center">
              <div class="col-lg-6 col-md-7 order-2 order-md-1 mt-5 mt-sm-0">
                  <div class="section-title">
                      <h1 class="title mb-3">{{ $data['titleCaption'] }}</h1>
                      <p class="para-desc text-muted">
                          {{ $data['descriptionCaption'] }}
                      </p>
                      @if ($data['listItem']['show'])
                          <ul class="list-unstyled text-muted">

                              @foreach ($data['listItem']['li'] as $item)

                                  <li class="mb-0"><span class="text-primary h5 me-2"><i
                                              class="uil uil-check-circle align-middle"></i></span>
                                      {{ $item['caption'] }}
                                  </li>

                              @endforeach

                          </ul>
                      @endif
                      @if ($data['BTN']['show'])
                          <div class="mt-4">
                              <a href="{{ $data['BTN']['link'] }}"
                                  class="mt-3 h6 text-primary">{{ $data['BTN']['caption'] }}
                                  <i class="uil uil-angle-right-b"></i></a>

                          </div>
                      @endif
                  </div>
              </div>
              <!--end col-->

              <div class="col-lg-6 col-md-5 order-1 order-md-2">
                  <div class="app-feature-shape-right position-relative">
                      <div class="text-center text-md-end">
                          <img src="{{ $data['imageSrc'] }}" class="img-fluid" alt="">
                      </div>
                  </div>
              </div>
              <!--end col-->
          </div>
          <!--end row-->
      </div>
      <!--end container-->
      <!-- end rightStory2C -->
  @endif
