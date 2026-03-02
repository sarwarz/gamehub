'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const urls = window._dashboardWidgetUrls || {};
    const cardColor  = config.colors.cardColor;
    const labelColor = config.colors.textMuted;
    const headingColor = config.colors.headingColor;
    const borderColor  = config.colors.borderColor;
    const fontFamily   = config.fontFamily;

    const chartColors = {
        donut: {
            series1: '#24B364',
            series2: '#53D28C',
            series3: '#7EDDA9',
            series4: '#A9E9C5'
        }
    };

    function ajax(url) {
        return fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        }).then(function(r) { return r.json(); });
    }

    // ─────────────────────────────────────────────
    //  1. View Sales Widget
    // ─────────────────────────────────────────────
    ajax(urls.viewSales).then(function(d) {
        var el = document.getElementById('widget-view-sales');
        if (!el) return;
        var body = el.querySelector('.card-body');
        var titleEl = body.querySelector('h5');
        var pEl     = body.querySelector('p');
        var hEl     = body.querySelector('h4');
        var aEl     = body.querySelector('a');

        pEl.classList.remove('placeholder-glow');
        pEl.innerHTML = d.title;

        hEl.classList.remove('placeholder-glow');
        hEl.innerHTML = (typeof d.amount === 'number') ? d.amount.toLocaleString() : d.amount;
        if (d.formatted_amount) hEl.innerHTML = d.formatted_amount || hEl.innerHTML;

        aEl.classList.remove('disabled', 'placeholder');
        aEl.href = d.cta || '#';
        aEl.textContent = 'View Sales';
    });

    // ─────────────────────────────────────────────
    //  2. Statistics Widget
    // ─────────────────────────────────────────────
    ajax(urls.statistics).then(function(d) {
        var el = document.getElementById('widget-statistics');
        if (!el) return;
        var keys = ['sales', 'customers', 'products', 'revenue'];
        var cards = el.querySelectorAll('.card-info');
        keys.forEach(function(k, i) {
            var h5 = cards[i].querySelector('h5');
            h5.classList.remove('placeholder-glow');
            var val = d[k];
            h5.innerHTML = (typeof val === 'number') ? val.toLocaleString() : val;
        });
    });

    // ─────────────────────────────────────────────
    //  3. Profit Widget
    // ─────────────────────────────────────────────
    ajax(urls.profit).then(function(d) {
        var el = document.getElementById('widget-profit');
        if (!el) return;

        var h4 = el.querySelector('h4');
        h4.classList.remove('placeholder-glow');
        h4.innerHTML = d.formatted_amount;

        var small = el.querySelector('.d-flex.justify-content-between small');
        small.classList.remove('placeholder-glow');
        var changeSign = d.change >= 0 ? '+' : '';
        small.className = d.change >= 0 ? 'text-success' : 'text-danger';
        small.textContent = changeSign + d.change + '%';

        var chartEl = document.getElementById('profitLastMonth');
        chartEl.innerHTML = '';

        var chartOpt = {
            chart: { height: 110, type: 'line', parentHeightOffset: 0, toolbar: { show: false } },
            grid: {
                borderColor: borderColor, strokeDashArray: 6,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
                padding: { top: -18, left: -4, right: 7, bottom: -10 }
            },
            colors: [config.colors.info],
            stroke: { width: 2 },
            series: [{ data: d.series }],
            xaxis: { labels: { show: false }, axisTicks: { show: false }, axisBorder: { show: false } },
            yaxis: { labels: { show: false } },
            tooltip: { enabled: false },
            markers: {
                size: 3.5, fillColor: config.colors.info, strokeColors: 'transparent', strokeWidth: 3.2, offsetX: -1,
                discrete: [{
                    seriesIndex: 0, dataPointIndex: d.series.length - 1,
                    fillColor: cardColor, strokeColor: config.colors.info, size: 4.5, shape: 'circle'
                }],
                hover: { size: 5.5 }
            },
            responsive: [{ breakpoint: 768, options: { chart: { height: 110 } } }]
        };
        new ApexCharts(chartEl, chartOpt).render();
    });

    // ─────────────────────────────────────────────
    //  4. Expenses Widget
    // ─────────────────────────────────────────────
    ajax(urls.expenses).then(function(d) {
        var el = document.getElementById('widget-expenses');
        if (!el) return;

        var h5 = el.querySelector('.card-header h5');
        h5.classList.remove('placeholder-glow');
        h5.innerHTML = d.formatted_amount;

        var small = el.querySelector('.card-body small');
        small.classList.remove('placeholder-glow');
        var arrow = d.diff_direction === 'up' ? '↑' : '↓';
        small.innerHTML = d.formatted_diff + ' ' + arrow + ' vs last month';

        var chartEl = document.getElementById('expensesChart');
        chartEl.innerHTML = '';

        var chartOpt = {
            chart: { height: 170, sparkline: { enabled: true }, parentHeightOffset: 0, type: 'radialBar' },
            colors: [config.colors.warning],
            series: [d.percentage],
            plotOptions: {
                radialBar: {
                    offsetY: 0, startAngle: -90, endAngle: 90,
                    hollow: { size: '65%' },
                    track: { strokeWidth: '45%', background: borderColor },
                    dataLabels: {
                        name: { show: false },
                        value: { fontSize: '24px', color: headingColor, fontWeight: 500, offsetY: -5 }
                    }
                }
            },
            grid: { show: false, padding: { bottom: 5 } },
            stroke: { lineCap: 'round' },
            labels: ['Progress'],
            responsive: [
                { breakpoint: 1442, options: { chart: { height: 120 }, plotOptions: { radialBar: { dataLabels: { value: { fontSize: '18px' } }, hollow: { size: '60%' } } } } },
                { breakpoint: 1025, options: { chart: { height: 136 }, plotOptions: { radialBar: { hollow: { size: '65%' }, dataLabels: { value: { fontSize: '18px' } } } } } },
                { breakpoint: 769, options: { chart: { height: 120 }, plotOptions: { radialBar: { hollow: { size: '55%' } } } } },
                { breakpoint: 426, options: { chart: { height: 145 }, plotOptions: { radialBar: { hollow: { size: '65%' } } } } },
                { breakpoint: 376, options: { chart: { height: 105 }, plotOptions: { radialBar: { hollow: { size: '60%' } } } } }
            ]
        };
        new ApexCharts(chartEl, chartOpt).render();
    });

    // ─────────────────────────────────────────────
    //  5. Generated Leads Widget
    // ─────────────────────────────────────────────
    ajax(urls.generatedLeads).then(function(d) {
        var el = document.getElementById('widget-generated-leads');
        if (!el) return;

        var stats = el.querySelector('.chart-statistics');
        var h3 = stats.querySelector('h3');
        h3.classList.remove('placeholder-glow');
        h3.innerHTML = d.total_sales.toLocaleString();

        var pEl = stats.querySelector('p');
        pEl.classList.remove('placeholder-glow');
        pEl.innerHTML = '';

        var chartEl = document.getElementById('generatedLeadsChart');
        chartEl.innerHTML = '';

        var total = d.series.reduce(function(a, b) { return a + b; }, 0);

        var chartOpt = {
            chart: { height: 125, width: 120, parentHeightOffset: 0, type: 'donut' },
            labels: d.labels,
            series: d.series,
            colors: [chartColors.donut.series1, chartColors.donut.series2, chartColors.donut.series3, chartColors.donut.series4],
            stroke: { width: 0 },
            dataLabels: { enabled: false },
            legend: { show: false },
            tooltip: { theme: false },
            grid: { padding: { top: 15, right: -20, left: -20 } },
            states: { hover: { filter: { type: 'none' } } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            value: { fontSize: '1.5rem', fontFamily: fontFamily, color: headingColor, fontWeight: 500, offsetY: -15, formatter: function(val) { return parseInt(val) + '%'; } },
                            name: { offsetY: 20, fontFamily: fontFamily },
                            total: { show: true, showAlways: true, color: config.colors.success, fontSize: '.8125rem', label: 'Total', fontFamily: fontFamily, formatter: function() { return total; } }
                        }
                    }
                }
            },
            responsive: [
                { breakpoint: 1025, options: { chart: { height: 172, width: 160 } } },
                { breakpoint: 769, options: { chart: { height: 178 } } },
                { breakpoint: 426, options: { chart: { height: 147 } } }
            ]
        };
        new ApexCharts(chartEl, chartOpt).render();
    });

    // ─────────────────────────────────────────────
    //  6. Revenue Report Widget
    // ─────────────────────────────────────────────
    ajax(urls.revenueReport).then(function(d) {
        var el = document.getElementById('widget-revenue-report');
        if (!el) return;

        var budgetTotal = document.getElementById('revenue-budget-total');
        budgetTotal.classList.remove('placeholder-glow');
        budgetTotal.innerHTML = d.formatted_total;

        var chartEl = document.getElementById('totalRevenueChart');
        chartEl.innerHTML = '';

        var maxVal = Math.max.apply(null, d.earnings.concat(d.commissions.map(Math.abs)));
        var yMax = Math.ceil(maxVal / 100) * 100 + 100;
        var yMin = -yMax;

        var chartOpt = {
            series: [
                { name: 'Commission', data: d.earnings },
                { name: 'Seller Payout', data: d.commissions }
            ],
            chart: { height: 413, parentHeightOffset: 0, stacked: true, type: 'bar', toolbar: { show: false } },
            tooltip: { enabled: false },
            plotOptions: {
                bar: {
                    horizontal: false, columnWidth: '40%', borderRadius: 7,
                    startingShape: 'rounded', endingShape: 'rounded',
                    borderRadiusApplication: 'around', borderRadiusWhenStacked: 'last'
                }
            },
            colors: [config.colors.primary, config.colors.warning],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 6, lineCap: 'round', colors: [cardColor] },
            legend: {
                show: true, horizontalAlign: 'right', position: 'top', fontSize: '13px', fontFamily: fontFamily,
                markers: { size: 6, offsetY: 0, shape: 'circle', strokeWidth: 0 },
                labels: { colors: headingColor },
                itemMargin: { horizontal: 10, vertical: 2 }
            },
            grid: { show: false, padding: { bottom: -8, right: 0, top: 20 } },
            xaxis: {
                categories: d.categories,
                labels: { style: { fontSize: '13px', colors: labelColor, fontFamily: fontFamily } },
                axisTicks: { show: false }, axisBorder: { show: false }
            },
            yaxis: {
                labels: { offsetX: -16, style: { fontSize: '13px', colors: labelColor, fontFamily: fontFamily } },
                min: yMin, max: yMax, tickAmount: 5
            },
            responsive: [
                { breakpoint: 1700, options: { plotOptions: { bar: { columnWidth: '43%' } } } },
                { breakpoint: 1441, options: { plotOptions: { bar: { columnWidth: '50%' } } } },
                { breakpoint: 1300, options: { plotOptions: { bar: { columnWidth: '40%' } } } },
                { breakpoint: 991, options: { plotOptions: { bar: { columnWidth: '38%' } } } },
                { breakpoint: 850, options: { plotOptions: { bar: { columnWidth: '50%' } } } },
                { breakpoint: 449, options: { plotOptions: { bar: { columnWidth: '73%' } } } },
                { breakpoint: 394, options: { plotOptions: { bar: { columnWidth: '88%' } } } }
            ],
            states: { hover: { filter: { type: 'none' } }, active: { filter: { type: 'none' } } }
        };
        new ApexCharts(chartEl, chartOpt).render();

        var budgetEl = document.getElementById('budgetChart');
        budgetEl.innerHTML = '';

        var budgetOpt = {
            chart: { height: 100, toolbar: { show: false }, zoom: { enabled: false }, type: 'line' },
            series: [
                { name: 'Last Month', data: d.budget_last_month },
                { name: 'This Month', data: d.budget_this_month }
            ],
            stroke: { curve: 'smooth', dashArray: [5, 0], width: [1, 2] },
            legend: { show: false },
            colors: [borderColor, config.colors.primary],
            grid: { show: false, borderColor: borderColor, padding: { top: -30, bottom: -15, left: 25 } },
            markers: { size: 0 },
            xaxis: { labels: { show: false }, axisTicks: { show: false }, axisBorder: { show: false } },
            yaxis: { show: false },
            tooltip: { enabled: false }
        };
        new ApexCharts(budgetEl, budgetOpt).render();
    });

    // ─────────────────────────────────────────────
    //  7. Earning Report Widget
    // ─────────────────────────────────────────────
    ajax(urls.earningReport).then(function(d) {
        var el = document.getElementById('widget-earning-report');
        if (!el) return;

        var list = document.getElementById('earning-summary-list');
        var items = list.querySelectorAll('li');

        var values = [
            { amount: d.formatted_net_profit },
            { amount: d.formatted_total_income },
            { amount: d.formatted_total_expense }
        ];

        items.forEach(function(li, i) {
            var smallEl = li.querySelector('.user-progress small');
            smallEl.classList.remove('placeholder-glow');
            smallEl.innerHTML = values[i].amount;
        });

        var chartEl = document.getElementById('reportBarChart');
        chartEl.innerHTML = '';

        var barColors = d.categories.map(function(_, i) {
            return i === d.today_index ? config.colors.primary : config.colors_label.primary;
        });

        var chartOpt = {
            chart: { height: 230, type: 'bar', toolbar: { show: false } },
            plotOptions: {
                bar: { barHeight: '60%', columnWidth: '60%', startingShape: 'rounded', endingShape: 'rounded', borderRadius: 4, distributed: true }
            },
            grid: { show: false, padding: { top: -20, bottom: 0, left: -10, right: -10 } },
            colors: barColors,
            dataLabels: { enabled: false },
            series: [{ data: d.series }],
            legend: { show: false },
            xaxis: {
                categories: d.categories,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { colors: labelColor, fontSize: '13px' } }
            },
            yaxis: { labels: { show: false } },
            responsive: [
                { breakpoint: 1025, options: { chart: { height: 190 } } },
                { breakpoint: 769, options: { chart: { height: 250 } } }
            ],
            states: { hover: { filter: { type: 'none' } }, active: { filter: { type: 'none' } } }
        };
        new ApexCharts(chartEl, chartOpt).render();
    });

    // ─────────────────────────────────────────────
    //  8. Popular Products Widget
    // ─────────────────────────────────────────────
    ajax(urls.popularProducts).then(function(d) {
        var el = document.getElementById('widget-popular-products');
        if (!el) return;

        var visitors = document.getElementById('popular-products-visitors');
        visitors.innerHTML = d.total_visitors.toLocaleString() + ' items sold this month';

        var body = document.getElementById('popular-products-body');
        if (d.products.length === 0) {
            body.innerHTML = '<div class="text-center text-muted py-4"><i class="ti tabler-package-off icon-xl mb-2 d-block"></i>No product sales yet</div>';
            return;
        }

        var html = '<ul class="p-0 m-0">';
        d.products.forEach(function(p, i) {
            var imgSrc = p.image || 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0NiIgaGVpZ2h0PSI0NiIgZmlsbD0iI2NjYyI+PHJlY3Qgd2lkdGg9IjQ2IiBoZWlnaHQ9IjQ2IiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZvbnQtc2l6ZT0iMTIiPk5BPC90ZXh0Pjwvc3ZnPg==';
            var mb = i < d.products.length - 1 ? 'mb-6' : '';
            html += '<li class="d-flex ' + mb + '">' +
                '<div class="me-4"><img src="' + imgSrc + '" alt="' + p.title + '" class="rounded" width="46" height="46" style="object-fit:cover"></div>' +
                '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                    '<div class="me-2"><h6 class="mb-0">' + p.title + '</h6><small class="text-body d-block">Qty: ' + p.total_qty + '</small></div>' +
                    '<div class="user-progress d-flex align-items-center gap-1"><p class="mb-0">' + p.formatted_sales + '</p></div>' +
                '</div></li>';
        });
        html += '</ul>';
        body.innerHTML = html;
    });

    // ─────────────────────────────────────────────
    //  9. Recent Orders Widget
    // ─────────────────────────────────────────────
    ajax(urls.recentOrders).then(function(d) {
        var el = document.getElementById('widget-recent-orders');
        if (!el) return;

        var sub = document.getElementById('recent-orders-subtitle');
        sub.innerHTML = d.counts.pending + ' pending · ' + d.counts.processing + ' processing · ' + d.counts.completed + ' completed';

        var body = document.getElementById('recent-orders-body');
        if (d.orders.length === 0) {
            body.innerHTML = '<div class="text-center text-muted py-4"><i class="ti tabler-package-off icon-xl mb-2 d-block"></i>No orders yet</div>';
            return;
        }

        var statusColors = { pending: 'warning', processing: 'info', completed: 'success', cancelled: 'danger', refunded: 'secondary' };

        var html = '';
        d.orders.forEach(function(o) {
            var color = statusColors[o.status] || 'secondary';
            html += '<div class="d-flex mb-4 pb-1 align-items-center">' +
                '<div class="badge bg-label-' + color + ' me-4 rounded p-1_5"><i class="icon-base ti tabler-package icon-md"></i></div>' +
                '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                    '<div class="me-2"><h6 class="mb-0">#' + o.order_number + '</h6>' +
                    '<small class="text-body d-block">' + o.customer_name + ' · ' + o.created_at + '</small></div>' +
                    '<div class="user-progress"><h6 class="mb-0">' + o.formatted_total + '</h6></div>' +
                '</div></div>';
        });
        body.innerHTML = html;
    });

    // ─────────────────────────────────────────────
    //  10. Transactions Widget
    // ─────────────────────────────────────────────
    ajax(urls.recentTransactions).then(function(d) {
        var el = document.getElementById('widget-transactions');
        if (!el) return;

        var sub = document.getElementById('transactions-subtitle');
        sub.innerHTML = 'Total ' + d.total_this_month + ' transactions this month';

        var body = document.getElementById('transactions-body');
        if (d.transactions.length === 0) {
            body.innerHTML = '<div class="text-center text-muted py-4"><i class="ti tabler-receipt-off icon-xl mb-2 d-block"></i>No transactions yet</div>';
            return;
        }

        var typeIcons = {
            credit: { icon: 'tabler-arrow-down-left', color: 'success' },
            debit: { icon: 'tabler-arrow-up-right', color: 'danger' }
        };
        var methodIcons = {
            wallet: 'tabler-wallet',
            stripe: 'tabler-credit-card',
            paypal: 'tabler-brand-paypal',
            bank: 'tabler-building-bank'
        };

        var html = '<ul class="p-0 m-0">';
        d.transactions.forEach(function(t, i) {
            var ti = typeIcons[t.type] || { icon: 'tabler-arrows-exchange', color: 'info' };
            var mi = methodIcons[t.payment_method] || 'tabler-cash';
            var amountClass = t.type === 'credit' ? 'text-success' : 'text-danger';
            var prefix = t.type === 'credit' ? '+' : '-';
            var mb = i < d.transactions.length - 1 ? 'mb-3 pb-1' : '';

            html += '<li class="d-flex ' + mb + ' align-items-center">' +
                '<div class="badge bg-label-' + ti.color + ' me-4 rounded p-1_5"><i class="icon-base ti ' + mi + ' icon-md"></i></div>' +
                '<div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">' +
                    '<div class="me-2"><h6 class="mb-0">' + (t.category || t.payment_method || 'Transaction') + '</h6>' +
                    '<small class="text-body d-block">' + (t.description || t.created_at) + '</small></div>' +
                    '<div class="user-progress d-flex align-items-center gap-1">' +
                    '<h6 class="mb-0 ' + amountClass + '">' + prefix + t.formatted_amount + '</h6></div>' +
                '</div></li>';
        });
        html += '</ul>';
        body.innerHTML = html;
    });

    // ─────────────────────────────────────────────
    //  11. Invoices Widget
    // ─────────────────────────────────────────────
    ajax(urls.invoices).then(function(d) {
        var el = document.getElementById('widget-invoices');
        if (!el) return;

        var sub = document.getElementById('invoices-subtitle');
        var parts = [];
        if (d.counts.paid)    parts.push(d.counts.paid + ' paid');
        if (d.counts.unpaid)  parts.push(d.counts.unpaid + ' unpaid');
        if (d.counts.draft)   parts.push(d.counts.draft + ' draft');
        sub.textContent = d.counts.total + ' invoices' + (parts.length ? ' · ' + parts.join(' · ') : '');

        var tbody = document.getElementById('invoices-tbody');
        if (d.invoices.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No invoices yet</td></tr>';
            return;
        }

        var statusBadges = {
            paid:           { class: 'bg-label-success',   icon: 'tabler-circle-check' },
            unpaid:         { class: 'bg-label-danger',    icon: 'tabler-alert-circle' },
            partially_paid: { class: 'bg-label-warning',   icon: 'tabler-circle-half-2' },
            draft:          { class: 'bg-label-secondary',  icon: 'tabler-device-floppy' },
            cancelled:      { class: 'bg-label-dark',      icon: 'tabler-x' },
            sent:           { class: 'bg-label-info',      icon: 'tabler-send' }
        };

        var html = '';
        d.invoices.forEach(function(inv) {
            var badge = statusBadges[inv.status] || { class: 'bg-label-secondary', icon: 'tabler-file' };
            var statusLabel = inv.status.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });

            html += '<tr>' +
                '<td><a href="' + d.show_url + '/' + inv.id + '" class="fw-semibold text-body"><code>' + inv.invoice_number + '</code></a></td>' +
                '<td>' +
                    '<div class="lh-sm"><span class="fw-semibold d-block">' + inv.customer_name + '</span>' +
                    '<small class="text-muted">' + (inv.customer_email || '') + '</small></div>' +
                '</td>' +
                '<td>' + inv.formatted_total + '</td>' +
                '<td><small>' + (inv.issued_at || '—') + '</small></td>' +
                '<td><span class="badge p-1_5 rounded-pill ' + badge.class + '"><i class="icon-base icon-16px ti ' + badge.icon + '"></i></span> <small>' + statusLabel + '</small></td>' +
            '</tr>';
        });
        tbody.innerHTML = html;
    });
});
