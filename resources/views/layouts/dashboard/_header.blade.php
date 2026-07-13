 <!-- fixed-top-->
 <nav
     class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-semi-dark navbar-shadow">
     <div class="navbar-wrapper">
         <div class="navbar-header">
             <ul class="flex-row nav navbar-nav">
                 <li class="mr-auto nav-item mobile-menu d-md-none"><a
                         class="nav-link nav-menu-main menu-toggle hidden-xs" href="{{ route('dashboard.welcome') }}"><i
                             class="ft-menu font-large-1"></i></a></li>
                 <li class="mr-auto nav-item">
                     <a class="navbar-brand" href="{{ route('dashboard.welcome') }}">
                         {{-- <img class="brand-logo" alt="modern admin logo"
                             src="{{asset('uploads/general/' . $settings->logo)}}"
                            style="width: 55px" > --}}

                     </a>
                 </li>

                 <li class="nav-item d-md-none">
                     <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile"><i
                             class="la la-ellipsis-v"></i></a>
                 </li>
             </ul>
         </div>
         <div class="navbar-container content">
             <div class="collapse navbar-collapse" id="navbar-mobile">
                 <ul class="float-left mr-auto nav navbar-nav">
                     <li class="nav-item d-none d-md-block"><a class="nav-link nav-link-expand" href="#"><i
                                 class="ficon ft-maximize"></i></a></li>


                 </ul>
                 <ul class="float-right nav navbar-nav">
                     <li class="dropdown dropdown-user nav-item">
                         <a class="dropdown-toggle nav-link dropdown-user-link" href="#"
                             data-toggle="dropdown">
                             <span class="mr-1">{{ __('dashboard.Hello') }},
                                 <span class="user-name text-bold-700">{{ Auth::user()->name }}</span>
                             </span>
                             <span class="avatar avatar-online">
                                 <img src="{{ asset('asset/dashboard') }}/images/portrait/small/avatar-s-19.png"
                                     alt="avatar"><i></i></span>
                         </a>
                         <div class="dropdown-menu dropdown-menu-right">
                             <form action="{{ route('dashboard.managers.show', Auth::user()->id) }}" method="Get">
                                 @csrf
                                 <button type="submit" class="dropdown-item">
                                     <i class="ft-user"></i>{{ __('dashboard.edit_profile') }}
                                 </button>
                             </form>


                             <div class="dropdown-divider"></div>
                             <form action="{{ route('dashboard.logout') }}" method="POST">
                                 @csrf
                                 <button type="submit" class="dropdown-item" href="#"><i
                                         class="ft-power"></i> {{ __('auth.logout') }}</button>
                             </form>
                         </div>
                     </li>
                     {{-- selector language --}}
                     {{-- <li class="dropdown dropdown-language nav-item">

              <div class="dropdown-menu" aria-labelledby="dropdown-flag">
                <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-gb"></i> English</a>
                <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-fr"></i> French</a>
                <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-cn"></i> Chinese</a>
                <a class="dropdown-item" href="#"><i class="flag-icon flag-icon-de"></i> German</a>
              </div>
            </li> --}}

                     <li class="dropdown dropdown-notification nav-item">
                         <a class="nav-link nav-link-label"
                            href="{{ route('dashboard.support.notifications.inbox') }}"
                            title="الإشعارات">
                             <i class="ficon ft-bell"></i>
                             @if(!empty($unreadSupportCount) && $unreadSupportCount > 0)
                                 <span class="badge badge-pill badge-danger badge-up badge-glow"
                                       style="font-size:9px; padding:3px 5px;">
                                     {{ $unreadSupportCount > 99 ? '99+' : $unreadSupportCount }}
                                 </span>
                             @endif
                         </a>
                         <ul class="dropdown-menu dropdown-menu-media dropdown-menu-right">
                             <li class="dropdown-menu-header">
                                 <h6 class="m-0 dropdown-header">
                                     <span class="grey darken-2">{{ __('dashboard.last_messages') }}</span>
                                 </h6>
                             </li>
                             {{-- <li class="scrollable-container media-list w-100">
                                 @foreach ($getLastMesages as $message)
                                     <a href="javascript:void(0)">
                                         <div class="media">
                                             <div class="media-left align-self-center">
                                                 <i class="ft-plus-square icon-bg-circle bg-cyan"></i>
                                             </div>
                                             <div class="media-body">
                                                 <h6 class="media-heading">{{ __('dashboard.new_message_from') }}</h6>
                                                 <p class="notification-text font-small-3 text-muted">
                                                     {{ $message->name }}, {{ $message->number }}
                                                 </p>
                                                 <small>
                                                     <time class="media-meta text-muted">
                                                         @if ($message->created_at)
                                                             {{ $message->created_at->diffForHumans() }}
                                                         @else
                                                             {{ 'غير متاح' }}
                                                         @endif
                                                     </time>
                                                 </small>
                                             </div>
                                         </div>
                                     </a>
                                 @endforeach



                             </li> --}}
                             {{-- <li class="dropdown-menu-footer"><a class="text-center dropdown-item text-muted"
                                     href="{{route('dashboard.messages.index')}}">{{ __('dashboard.show_all_messages') }}</a></li> --}}
                         </ul>
                     </li>

                 </ul>
             </div>
         </div>
     </div>
 </nav>
 <!-- ////////////////////////////////////////////////////////////////////////////-->
