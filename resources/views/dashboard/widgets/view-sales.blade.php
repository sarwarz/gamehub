<div class="col-xl-4">
    <div class="card">
        <div class="d-flex align-items-end row">
            <div class="col-7">
                <div class="card-body text-nowrap">
                    <h5 class="card-title mb-0">
                        Congratulations {{ ucwords($user->name) }}! 🎉
                    </h5>

                    <p class="mb-2">{{ $viewSales['title'] }}</p>

                    <h4 class="text-primary mb-1">
                        {{ format_currency($viewSales['amount']) }}
                    </h4>

                    <a href="{{ $viewSales['cta'] }}" class="btn btn-primary">
                        View Sales
                    </a>
                </div>
            </div>

            <div class="col-5 text-center">
                <img
                    src="{{ asset('assets/img/illustrations/card-advance-sale.png') }}"
                    height="140"
                    loading="lazy"
                    alt="view sales">
            </div>
        </div>
    </div>
</div>
