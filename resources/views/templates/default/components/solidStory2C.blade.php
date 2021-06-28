  @if ($data['show'])
      <!-- solidStory2C Start -->
      <section class="section bg-primary bg-gradient">
          <div class="bg-overlay bg-overlay-white"></div>
          <div class="container position-relative">
              <div class="row">
                  <div class="col-lg-5 col-md-6">
                      <div class="app-subscribe text-center text-md-start">
                          <img src="{{ $data['imageSrc'] }}" class="img-fluid" alt="">
                      </div>
                  </div>
                  <!--end col-->

                  <div class="col-lg-7 col-md-6 mt-4 pt-2 mt-sm-0 pt-sm-0">
                      <div class="section-title text-center text-md-start">
                          <h1 class="title text-white title-dark mb-2">
                              {{ $data['titleCaption'] }}</h1>
                          <p class="text-light para-dark">{{ $data['descriptionCaption'] }}
                          </p>
                      </div>

                  </div>
                  <!--end col-->
              </div>
              <!--end row-->
          </div>
          <!--end container-->
      </section>
      <!--end section-->
      <!-- solidStory2C End -->
  @endif
