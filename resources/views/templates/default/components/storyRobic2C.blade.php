   @if ($data['show'])
       <section class="section">
           <div class="container">
               <div class="row align-items-center" id="counter">
                   <div class="col-md-6">
                       <img src="{{ $data['imageSrc'] }}" class="img-fluid" alt="">

                   </div>
                   <!--end col-->

                   <div class="col-md-6 mt-4 pt-2 mt-sm-0 pt-sm-0">
                       <div class="ms-lg-4">
                           @if ($data['numeric']['show'])
                               <div class="d-flex mb-4">
                                   <span class="text-primary h1 mb-0"><span class="counter-value display-1 fw-bold"
                                           data-target="{{ $data['numeric']['numberCaption'] }}">0</span>+</span>
                                   <span class="h6 align-self-end ms-2">{{ $data['numeric']['word1Caption'] }} <br>
                                       {{ $data['numeric']['word2Caption'] }}</span>
                               </div>
                           @endif
                           <div class="section-title">
                               <h4 class="title mb-4">{{ $data['titleCaption'] }}</h4>
                               <p class="text-muted">
                                   {{ $data['content1Caption'] }}
                               </p>
                               @if ($data['BTN']['show'])
                                   <a href="{{ $data['BTN']['link'] }}"
                                       class="btn btn-primary mt-3">{{ $data['BTN']['caption'] }}</a>
                               @endif
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

   @endif
