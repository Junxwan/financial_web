<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview" role="menu"
                data-accordion="false" >

                <li>
                    <div class="form-inline my-2">
                        <div class="input-group" data-widget="sidebar-search" data-arrow-sign="»">
                            <input class="form-control form-control-sidebar" type="search" placeholder="search" aria-label="search">
                            <div class="input-group-append">
                                <button class="btn btn-sidebar">
                                    <i class="fas fa-fw fa-search"></i>
                                </button>
                            </div>
                        </div><div class="sidebar-search-results"><div class="list-group"><a href="#" class="list-group-item"><div class="search-title"><strong class="text-light"></strong>N<strong class="text-light"></strong>o<strong class="text-light"></strong> <strong class="text-light"></strong>e<strong class="text-light"></strong>l<strong class="text-light"></strong>e<strong class="text-light"></strong>m<strong class="text-light"></strong>e<strong class="text-light"></strong>n<strong class="text-light"></strong>t<strong class="text-light"></strong> <strong class="text-light"></strong>f<strong class="text-light"></strong>o<strong class="text-light"></strong>u<strong class="text-light"></strong>n<strong class="text-light"></strong>d<strong class="text-light"></strong>!<strong class="text-light"></strong></div><div class="search-path"></div></a></div></div>
                    </div>
                </li>

                @foreach($menu as $v)
                    @php
                        $u = route($v['route']);
                    @endphp
                    <li class="nav-item">
                        <a href="{{ $u }}" class="nav-link @if($url == $u) active @endif">
                            <i class="nav-icon {{ $v['icon'] }}"></i>
                            <p>{{ $v['name'] }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
