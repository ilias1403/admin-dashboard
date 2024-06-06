        <!-- navbar vertical -->
        <div class="app-menu">
            <!-- Sidebar -->

            <div class="navbar-vertical navbar nav-dashboard">
                <div class="h-100" data-simplebar>
                    <!-- Brand logo -->
                    <a class="navbar-brand" href="./index.html">
                        <img src="{{ asset('assets/images/brand/logo/analytics-41b6fe9c.png') }}" alt="ADMIN DASHBOARD">
                        ADMIN DASHBOARD
                    </a>
                    <!-- Navbar nav -->
                    <ul class="navbar-nav flex-column" id="sideNavbar">

                        <!-- Nav item -->
                        <li class="nav-item">
                            <a class="nav-link has-arrow " href="#">
                                <i data-feather="home" class="nav-icon me-2 icon-xxs"></i>
                                Dashboard
                            </a>

                        </li>

                        @can('view tier')
                            <li class="nav-item">
                                <a class="nav-link has-arrow" href="#" data-bs-toggle="collapse"
                                    data-bs-target="#navTier" aria-expanded="false" aria-controls="navTier">
                                    <i class="bi bi-trophy nav-icon me-2 icon-xxs"></i>
                                    Tier
                                </a>

                                <div id="navTier" class="collapse show" data-bs-parent="#sideNavbar">
                                    <ul class="nav flex-column">
                                        @php
                                            $tierLinks = [
                                                'view tier summary' => ['label' => 'Summary', 'url' => '#summary'],
                                                'view tier customer details' => [
                                                    'label' => 'Customer Details',
                                                    'url' => '#customer-details',
                                                ],
                                                'view tier setting' => [
                                                    'label' => 'Setting',
                                                    'url' => route('tiers.setting'),
                                                ],
                                                'view tier customer details' => [
                                                    'label' => 'Customer Details',
                                                    'url' => route('tiers.customer-details'),
                                                ]
                                            ];
                                        @endphp

                                        @foreach ($tierLinks as $permission => $link)
                                            @can($permission)
                                                <li class="nav-item">
                                                    <a class="nav-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                                </li>
                                            @endcan
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endcan

                </ul>

            </div>
        </div>

    </div>
