  @if ($data['show'])
      <!-- Feature Start -->
      <section class="section">
          <div class="container">
              <div class="row">
                  <div class="col-12 text-center">
                      <div class="section-title mb-4 pb-2">
                          <h4 class="title mb-4">{{ $data['titleCaption'] }}</h4>
                          <p class="text-muted para-desc mx-auto mb-0">{{ $data['descriptionCaption'] }}</p>
                      </div>
                  </div>
                  <!--end col-->
                  @foreach ($data['items'] as $service)
                      <div class="col-md-4 col-12">
                          <div class="features mt-5">
                              <div class="image position-relative d-inline-block">
                                  <i class="uil {{ $service['icon'] }} h1 text-primary"></i>
                              </div>

                              <div class="content mt-4">
                                  <h5>{{ $service['caption'] }}</h5>
                                  <p class="text-muted mb-0">{{ $service['descriptionCaption'] }}</p>
                              </div>
                          </div>
                      </div>
                  @endforeach


              </div>
              <!--end row-->
          </div>
          <!--end container-->


      </section>
      <!--end section-->
      <!-- Feature Start -->
  @endif
