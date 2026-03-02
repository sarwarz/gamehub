<!doctype html>

@php
    $localeRtl = (bool) \App\Models\Setting::get('currency_locale', 'rtl_enabled', false);
    $localeLang = \App\Models\Setting::get('currency_locale', 'default_language', 'en');
@endphp
<html
  lang="{{ $localeLang }}"
  class=" layout-navbar-fixed layout-menu-fixed layout-compact "
  dir="{{ $localeRtl ? 'rtl' : 'ltr' }}"
  data-skin="default"
  data-bs-theme="light"
  data-assets-path="assets/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>@yield('title') | {{ $appSettings['site_name'] ?? config('app.name') }}</title>

    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    @if(!empty($appSettings['favicon']))
    <link rel="icon" type="image/x-icon" href="{{ asset($appSettings['favicon']) }}" />
    @else
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    @endif

    <!-- Dynamic Brand Colors -->
    <style>
    :root {
        --bs-primary: {{ $appSettings['primary_color'] ?? '#7367f0' }};
        --bs-primary-rgb: {{ implode(',', array_map('hexdec', str_split(ltrim($appSettings['primary_color'] ?? '#7367f0', '#'), 2))) }};
        --bs-secondary: {{ $appSettings['secondary_color'] ?? '#a8aaae' }};
        --bs-secondary-rgb: {{ implode(',', array_map('hexdec', str_split(ltrim($appSettings['secondary_color'] ?? '#a8aaae', '#'), 2))) }};
    }
    </style>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

    <script src="{{ asset('assets/vendor/libs/@algolia/autocomplete-js.js') }}"></script>

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    

    <!-- endbuild -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/app-datatables.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/typography.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/katex.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/quill/editor.css') }}" />
    
    
    <!-- Page CSS -->

    @stack('page-css');

    <style>
    @keyframes bellRing {
        0%   { transform: rotate(0); }
        15%  { transform: rotate(14deg); }
        30%  { transform: rotate(-14deg); }
        45%  { transform: rotate(10deg); }
        60%  { transform: rotate(-10deg); }
        75%  { transform: rotate(4deg); }
        100% { transform: rotate(0); }
    }
    .bell-ring { animation: bellRing 0.8s ease-in-out; transform-origin: top center; }
    </style>

    @unless(auth()->check() && auth()->user()->canDelete())
    <style>
        body.no-delete-permission [title="Delete"],
        body.no-delete-permission .btn-delete,
        body.no-delete-permission .btn-bulk-delete,
        body.no-delete-permission .bulk-delete-btn,
        body.no-delete-permission .delete-btn,
        body.no-delete-permission .delete-user-btn,
        body.no-delete-permission .btn-delete-tax,
        body.no-delete-permission [id="bulk-delete-btn"],
        body.no-delete-permission [id="bulk-delete"],
        body.no-delete-permission [id="btn-delete-permission"],
        body.no-delete-permission [id="btn-delete-role"],
        body.no-delete-permission [id="btn-delete-seller"],
        body.no-delete-permission [data-action="delete"],
        body.no-delete-permission .card.border-danger:has([class*="tabler-alert-triangle"]) { display: none !important; }
    </style>
    @endunless

    @php
        $seoGA = \App\Models\Setting::get('seo', 'google_analytics', '');
        $seoHeadScripts = \App\Models\Setting::get('seo', 'head_scripts', '');
    @endphp
    @if(!empty($seoGA))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $seoGA }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $seoGA }}');</script>
    @endif
    @if(!empty($seoHeadScripts))
    {!! $seoHeadScripts !!}
    @endif

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="{{ asset('assets/js/config.js') }}"></script>
  </head>

  <body @unless(auth()->check() && auth()->user()->canDelete()) class="no-delete-permission" @endunless>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
      <div class="layout-container">
        <!-- Menu -->
        @include('include.sidebar')
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('include.header')
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
                @yield('content')
            </div>
            <!-- / Content -->

            <!-- Footer -->
            @include('include.footer')
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>

      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js  -->

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/quill/katex.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/quill/quill.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

    <!-- Main JS -->

    <script src="{{ asset('assets/js/main.js') }}"></script>

     <script src="{{ asset('assets/js/forms-pickers.js') }}"></script>

    <!-- Page JS -->
    <script>
    window._canDelete = {{ auth()->check() && auth()->user()->canDelete() ? 'true' : 'false' }};
    $(document).ready(function() {
            $('.select2').select2({
                placeholder: "-- Select Option --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
	@stack('page-js');

    <script src="{{ asset('assets/js/header-notifications.js') }}"></script>

    <script>
      // ===============================
      // Global Delete Handlers with SweetAlert2
      // ===============================

      // Select/Deselect all checkboxes
      $(document).on('click', '#select-all', function() {
          $('.bulk-checkbox').prop('checked', this.checked);
      });

      // Bulk Delete Handler
      $(document).on('click', '#bulk-delete', function() {
          let ids = [];
          $('.bulk-checkbox:checked').each(function() {
              ids.push($(this).val());
          });

          if(ids.length === 0){
              Swal.fire({
                  icon: 'warning',
                  title: 'No Selection',
                  text: 'Please select at least one record to delete.',
              });
              return;
          }

          Swal.fire({
              title: 'Are you sure?',
              text: "You won’t be able to revert this!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#6c757d',
              confirmButtonText: 'Yes, delete them!'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                      url: $('#bulk-delete').data('url'), // ✅ dynamic bulk delete URL
                      type: 'POST',
                      data: {
                          ids: ids,
                          _token: $('meta[name="csrf-token"]').attr('content')
                      },
                      success: function(response){
                          Swal.fire({
                              icon: 'success',
                              title: 'Deleted!',
                              text: response.message,
                              timer: 1500,
                              showConfirmButton: false
                          });
                          if (typeof table !== 'undefined') {
                              table.ajax.reload(null, false);
                          }
                      },
                      error: function(xhr){
                          Swal.fire({
                              icon: 'error',
                              title: 'Error',
                              text: 'Something went wrong. Please try again.'
                          });
                      }
                  });
              }
          });
      });

      // Single Delete Handler
      $(document).on('click', '.btn-delete', function(e) {
          e.preventDefault();

          let button = $(this);
          let url = button.data('url');

          Swal.fire({
              title: 'Are you sure?',
              text: "This record will be deleted!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#6c757d',
              confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.ajax({
                      url: url,
                      type: 'POST',
                      data: {
                          _token: $('meta[name="csrf-token"]').attr('content'),
                          _method: 'DELETE'
                      },
                      success: function(response){
                          Swal.fire({
                              icon: 'success',
                              title: 'Deleted!',
                              text: response.message ?? 'Deleted successfully.',
                              timer: 1500,
                              showConfirmButton: false
                          });
                          if (typeof table !== 'undefined') {
                              table.ajax.reload(null, false);
                          }
                      },
                      error: function(xhr){
                          Swal.fire({
                              icon: 'error',
                              title: 'Error',
                              text: 'Something went wrong. Please try again.'
                          });
                      }
                  });
              }
          });
      });


    </script>

  </body>
</html>
