<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview" role="menu"
                data-accordion="false" >
                @each('partials.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>
</aside>
