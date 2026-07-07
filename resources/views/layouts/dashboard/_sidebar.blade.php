<div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

            {{-- لوحة التحكم --}}
            <li class="nav-item">
                <a href="{{ route('dashboard.welcome') }}">
                    <i class="la la-home"></i>
                    <span class="menu-title">لوحة التحكم</span>
                </a>
            </li>

            {{-- الإدارة --}}
            @can('admins')
                <li class="nav-item">
                    <a href="#">
                        <i class="la la-shield"></i>
                        <span class="menu-title">الإدارة</span>
                    </a>
                    <ul class="menu-content">
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.managers.index') }}">
                                المسؤولون
                            </a>
                        </li>
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.roles.index') }}">
                                الصلاحيات
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            {{-- البيانات الأساسية --}}
            @can('master-data')
                <li class="nav-item">
                    <a href="#">
                        <i class="la la-list-alt"></i>
                        <span class="menu-title">البيانات الأساسية</span>
                    </a>
                    <ul class="menu-content">
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.master-data.cities.index') }}">
                                المدن
                            </a>
                        </li>
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.master-data.platforms.index') }}">
                                المنصات
                            </a>
                        </li>
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.master-data.vehicle-types.index') }}">
                                أنواع المركبات
                            </a>
                        </li>
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.master-data.document-types.index') }}">
                                أنواع الوثائق
                            </a>
                        </li>
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.master-data.warning-types.index') }}">
                                أنواع المخالفات
                            </a>
                        </li>
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.master-data.leave-types.index') }}">
                                أنواع الإجازات
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            {{-- المناديب --}}
            @can('delegates')
                <li class="nav-item">
                    <a href="{{ route('dashboard.delegates.index') }}">
                        <i class="la la-users"></i>
                        <span class="menu-title">المناديب</span>
                    </a>
                </li>
            @endcan

            {{-- المركبات --}}
            @can('vehicles')
                <li class="nav-item">
                    <a href="{{ route('dashboard.vehicles.index') }}">
                        <i class="la la-car"></i>
                        <span class="menu-title">المركبات</span>
                    </a>
                </li>
            @endcan

            {{-- الفترات الشهرية --}}
            @can('monthly-periods')
                <li class="nav-item">
                    <a href="{{ route('dashboard.monthly.periods.index') }}">
                        <i class="la la-calendar"></i>
                        <span class="menu-title">الفترات الشهرية</span>
                    </a>
                </li>
            @endcan

            {{-- التقارير --}}
            @can('reports')
                <li class="nav-item {{ request()->routeIs('dashboard.reports.*') ? 'open' : '' }}">
                    <a href="#">
                        <i class="la la-bar-chart"></i>
                        <span class="menu-title">التقارير</span>
                    </a>
                    <ul class="menu-content">
                        <li class="{{ request()->routeIs('dashboard.reports.executive*') ? 'active' : '' }}">
                            <a class="menu-item" href="{{ route('dashboard.reports.executive') }}">
                                <i class="la la-chart-line" style="margin-left:6px;font-size:13px;"></i>
                                لوحة تقارير تنفيذية
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('dashboard.reports.comparison') ? 'active' : '' }}">
                            <a class="menu-item" href="{{ route('dashboard.reports.comparison') }}">
                                <i class="la la-exchange" style="margin-left:6px;font-size:13px;"></i>
                                مقارنة الفترات
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('dashboard.reports.bi*') ? 'active' : '' }}">
                            <a class="menu-item" href="{{ route('dashboard.reports.bi') }}">
                                <i class="la la-building" style="margin-left:6px;font-size:13px;"></i>
                                الأعمال
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            {{-- الإعدادات --}}
            @can('settings')
                <li class="nav-item">
                    <a href="#">
                        <i class="la la-cog"></i>
                        <span class="menu-title">الإعدادات</span>
                    </a>
                    <ul class="menu-content">
                        <li>
                            <a class="menu-item" href="{{ route('dashboard.settings.index') }}">
                                إعدادات النظام
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

        </ul>
    </div>
</div>
