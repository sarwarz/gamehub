<!-- Statistics -->
<div class="col-xl-8 col-md-12">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title mb-0">Statistics</h5>
            <small class="text-body-secondary">
                Updated {{ now()->startOfMonth()->diffForHumans() }}
            </small>
        </div>

        <div class="card-body d-flex align-items-end">
            <div class="w-100">
                <div class="row gy-3">

                    <!-- Sales -->
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="badge rounded bg-label-primary me-4 p-2">
                                <i class="icon-base ti tabler-chart-pie-2 icon-lg"></i>
                            </div>
                            <div class="card-info">
                                <h5 class="mb-0">{{ format_currency($stats['sales']) }}</h5>
                                <small>Sales</small>
                            </div>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="badge rounded bg-label-info me-4 p-2">
                                <i class="icon-base ti tabler-users icon-lg"></i>
                            </div>
                            <div class="card-info">
                                <h5 class="mb-0">{{ number_format($stats['customers']) }}</h5>
                                <small>Customers</small>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="badge rounded bg-label-danger me-4 p-2">
                                <i class="icon-base ti tabler-shopping-cart icon-lg"></i>
                            </div>
                            <div class="card-info">
                                <h5 class="mb-0">{{ number_format($stats['products']) }}</h5>
                                <small>Products</small>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue (Seller Earning) -->
                    <div class="col-md-3 col-6">
                        <div class="d-flex align-items-center">
                            <div class="badge rounded bg-label-success me-4 p-2">
                                <i class="icon-base ti tabler-currency-dollar icon-lg"></i>
                            </div>
                            <div class="card-info">
                                <h5 class="mb-0">{{ format_currency($stats['revenue']) }}</h5>
                                <small>Revenue</small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- / Statistics -->
