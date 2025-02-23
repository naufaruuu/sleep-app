{{-- This file is used for menu items by any Backpack v6 theme --}}
{{-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li> --}}

<x-backpack::menu-item title="Quick Tutorial" icon="la la-book" :link="backpack_url('dashboard')" />

<x-backpack::menu-item title="Projects" icon="la la-project-diagram" :link="backpack_url('projects')" />

<x-backpack::menu-item title="Subservices" icon="la la-cogs" :link="backpack_url('subservice-all')" />

<x-backpack::menu-item title="Excludes" icon="la la-ban" :link="backpack_url('exclude')" />

<x-backpack::menu-item title="Application lists" icon="la la-list-alt" :link="backpack_url('application-lists')" />