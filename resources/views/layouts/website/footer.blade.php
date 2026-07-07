

  {{-- <div class="whatsapp">
      <a href="https://wa.me/{{ $settings->whatsapp }}" target="_blank">
          <i class="bi bi-whatsapp"></i>
  </div> --}}





    <div class="py-5 my-5 text-center line">
        <img class="img-fluid" src="{{ asset('asset/website/assets/images/line.png') }}" alt="">
    </div>


    <footer class="pt-2">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-4 ">
                    <h2 class="text-start">د. نورة بطاح البرقان</h2>
                    <p class="py-3 text-start text-secondary">
                       {{ $settings->doctor_about }}
                    </p>
                </div>

                <div class="py-5 col-12 col-sm-4">
                    <ul class="list-unstyled ms-5">
                        <li class="py-2 list-unstyed-item">
                            <i class="fa-solid fa-envelope fa-lg contact-icon"></i>
                            <a href="mailto:n.albargan@hotmail.com" class="text-secondary" target="_blank">
                               {{ $settings->email }}
                            </a>
                        </li>

                        <li class="py-2 list-unstyed-item">
                            <i class="fa-brands fa-whatsapp fa-lg contact-icon"></i>
                            <a href="https://wa.me/966920011852" class="text-secondary" dir="ltr" target="_blank">
                               {{ $settings->phone }}
                            </a>
                        </li>

                        <li class="py-2 list-unstyed-item">
                            <i class="fa-solid fa-location-dot fa-lg contact-icon"></i>
                            <a href="#" class="text-secondary" target="_blank">{{ $settings->address }}</a>
                        </li>
                    </ul>
                </div>

                <div class="col-12 col-sm-4">
                    <a href="index.html"><img class="img-footer" src="{{ asset('uploads/general/' . $settings->logo)}}" alt=""></a>
                </div>
            </div>
        </div>

        <div class="py-3 bg-primary-50">
            <div class="container">

                <div class="row align-items-center">
                    <div class="col-12 col-md-6 ">

                        <ul class="text-center list-inline text-sm-start">

                            @if (!empty($settings->social_links))
                          @foreach ($settings->social_links as $social)
                              <li class="list-inline-item">
                                  <a href="{{ $social['link'] }}" target="_blank"
                                  class="social-icon" rel="noopener">
                                      <i class="{{ $social['icon'] }}"></i>
                                  </a>
                              </li>
                          @endforeach
                      @endif




                        </ul>
                    </div>

                    <div class="col-12 col-md-6">
                       <ul class="text-center list-inline text-sm-end">
    <li class="list-inline-item">
        <img src="{{ asset('asset/website/assets/images/visa-pay.png') }}" alt="Visa">
    </li>

    <li class="list-inline-item">
        <img class="my-2 my-sm-0" src="{{ asset('asset/website/assets/images/master-pay.png') }}" alt="MasterCard">
    </li>

    <li class="list-inline-item">
        <img class="my-2 my-sm-0" src="{{ asset('asset/website/assets/images/mada-pay.png') }}" alt="Mada Pay">
    </li>

    <li class="list-inline-item">
        <img class="my-2 my-sm-0" src="{{ asset('asset/website/assets/images/apple-pay.png') }}" alt="Apple Pay">
    </li>

    <li class="list-inline-item">
        <img class="my-2 my-sm-0" src="{{ asset('asset/website/assets/images/tamara-pay.png') }}" alt="Tamara">
    </li>

    <li class="list-inline-item">
        <img class="my-2 my-sm-0" src="{{ asset('asset/website/assets/images/tabby-pay.png') }}" alt="Tabby">
    </li>
</ul>

                    </div>

                </div>
            </div>
        </div>
    </footer>

