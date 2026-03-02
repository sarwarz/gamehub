'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var urls = window._reportUrls || {};
    var cSymbol = window._currencySymbol || '$';
    var cardColor   = config.colors.cardColor;
    var labelColor  = config.colors.textMuted;
    var headingColor = config.colors.headingColor;
    var borderColor = config.colors.borderColor;
    var fontFamily  = config.fontFamily;
    var primary = config.colors.primary;
    var success = config.colors.success;
    var info    = config.colors.info;
    var warning = config.colors.warning;
    var danger  = config.colors.danger;

    var chartPalette = [primary, success, info, warning, danger, '#ff6f61', '#a855f7', '#06b6d4'];

    var state = {
        from: null,
        to: null,
        period: 'daily',
        loaded: {},
        charts: {},
        activeTab: 'sales'
    };

    // ─── Date helpers ───────────────────────────────
    function fmt(d) { return d.toISOString().slice(0, 10); }
    function today() { var d = new Date(); d.setHours(0,0,0,0); return d; }
    function daysAgo(n) { var d = today(); d.setDate(d.getDate() - n); return d; }

    function setRange(from, to) {
        state.from = from;
        state.to = to;
        picker.setDate([from, to], true);
        reloadActiveTab();
    }

    function applyPreset(range) {
        var now = today();
        var y = now.getFullYear(), m = now.getMonth();
        switch (range) {
            case 'today':      setRange(now, now); break;
            case '7d':         setRange(daysAgo(6), now); break;
            case '30d':        setRange(daysAgo(29), now); break;
            case 'month':      setRange(new Date(y, m, 1), now); break;
            case 'last-month': setRange(new Date(y, m-1, 1), new Date(y, m, 0)); break;
            case 'quarter':
                var qm = m - (m % 3);
                setRange(new Date(y, qm, 1), now);
                break;
            case 'year':       setRange(new Date(y, 0, 1), now); break;
        }
    }

    // ─── Flatpickr ──────────────────────────────────
    var picker = flatpickr('#dateRangeInput', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        defaultDate: [fmt(daysAgo(29)), fmt(today())],
        onChange: function (dates) {
            if (dates.length === 2) {
                state.from = dates[0];
                state.to = dates[1];
                document.querySelectorAll('.preset-btn').forEach(function (b) { b.classList.remove('active'); });
                reloadActiveTab();
            }
        }
    });

    var exportPicker = flatpickr('#exportDateRange', {
        mode: 'range',
        dateFormat: 'Y-m-d',
        static: true,
        appendTo: document.getElementById('exportModal').querySelector('.modal-body')
    });

    // preset buttons
    state.from = daysAgo(29);
    state.to = today();

    document.querySelectorAll('.preset-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.preset-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            applyPreset(btn.dataset.range);
        });
    });

    document.getElementById('periodSelect').addEventListener('change', function () {
        state.period = this.value;
        reloadActiveTab();
    });

    // ─── Tab switching ──────────────────────────────
    document.querySelectorAll('#reportTabs .nav-link').forEach(function (link) {
        link.addEventListener('shown.bs.tab', function () {
            state.activeTab = link.dataset.tab;
            loadTab(state.activeTab);
        });
    });

    function reloadActiveTab() {
        state.loaded = {};
        Object.keys(state.charts).forEach(function (key) { destroyChart(key); });
        loadTab(state.activeTab);
    }

    var tabLabels = { sales:'Sales', revenue:'Revenue', products:'Products', customers:'Customers', sellers:'Sellers', payments:'Payments', refunds:'Refunds', support:'Support' };

    var loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'reportLoadingOverlay';
    loadingOverlay.innerHTML = '<div class="spinner-card"><div class="spinner-border" role="status"></div><span class="loading-text">Loading data...</span></div>';
    document.getElementById('reportTabContent').appendChild(loadingOverlay);
    var loadingText = loadingOverlay.querySelector('.loading-text');

    function showLoading(tab) {
        if (loadingText) loadingText.textContent = 'Loading ' + (tabLabels[tab] || 'report') + ' data...';
        loadingOverlay.classList.add('active');
    }

    function hideLoading() {
        loadingOverlay.classList.remove('active');
    }

    function loadTab(tab) {
        if (state.loaded[tab]) return;
        state.loaded[tab] = true;
        var url = urls[tab];
        if (!url) return;

        showLoading(tab);

        var params = new URLSearchParams({
            from: fmt(state.from),
            to: fmt(state.to),
            period: state.period
        });

        ajax(url + '?' + params.toString()).then(function (d) {
            renderKPI(tab, d.kpi);
            renderCharts(tab, d);
            renderTable(tab, d);
        }).catch(function (err) {
            console.error('Report load error (' + tab + '):', err);
        }).finally(function () {
            hideLoading();
        });
    }

    function ajax(url) {
        return fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
    }

    // ─── KPI Cards ──────────────────────────────────
    function renderKPI(tab, kpis) {
        var row = document.getElementById(tab + '-kpi-row');
        if (!row || !kpis) return;
        var html = '';
        kpis.forEach(function (k) {
            var val = k.value;
            if (k.format === 'currency') val = cSymbol + Number(val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            else if (k.format === 'percent') val = val + '%';
            else if (k.format === 'rating') val = val + ' / 5';
            else val = Number(val).toLocaleString();

            var changeClass = 'flat', arrow = '';
            if (k.change > 0) { changeClass = 'up'; arrow = '↑ +'; }
            else if (k.change < 0) { changeClass = 'down'; arrow = '↓ '; }
            var changeText = (k.change !== 0) ? arrow + k.change + '%' : '—';

            html += '<div class="col-xl-3 col-sm-6">' +
                '<div class="card kpi-card"><div class="card-body">' +
                '<div class="d-flex align-items-start justify-content-between">' +
                '<div><p class="kpi-label">' + k.label + '</p><h4 class="kpi-value">' + val + '</h4></div>' +
                '<div class="kpi-icon bg-label-' + k.color + '"><i class="ti ' + k.icon + '"></i></div>' +
                '</div>' +
                '<span class="kpi-change ' + changeClass + '">' + changeText + '</span>' +
                '</div></div></div>';
        });
        row.innerHTML = html;
    }

    // ─── Default chart opts ─────────────────────────
    function baseOpts(height) {
        return {
            chart: { height: height || 350, toolbar: { show: false }, fontFamily: fontFamily, parentHeightOffset: 0 },
            grid: { borderColor: borderColor, strokeDashArray: 4, padding: { top: -10, bottom: -5 } },
            colors: chartPalette,
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '12px', fontFamily: fontFamily, labels: { colors: labelColor }, markers: { size: 6, offsetY: 0 } },
            tooltip: { theme: 'light' },
            xaxis: { labels: { style: { colors: labelColor, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: labelColor, fontSize: '11px' } } }
        };
    }

    function destroyChart(key) {
        if (state.charts[key]) {
            try { state.charts[key].destroy(); } catch (e) {}
            delete state.charts[key];
        }
    }

    function renderApex(elId, opts) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        el.innerHTML = '';
        var c = new ApexCharts(el, opts);
        c.render();
        state.charts[elId] = c;
    }

    function merge(base, ext) {
        var r = JSON.parse(JSON.stringify(base));
        for (var k in ext) {
            if (ext[k] && typeof ext[k] === 'object' && !Array.isArray(ext[k]) && r[k]) {
                r[k] = merge(r[k], ext[k]);
            } else {
                r[k] = ext[k];
            }
        }
        return r;
    }

    // ─── Chart renders per tab ──────────────────────

    function renderCharts(tab, d) {
        switch (tab) {
            case 'sales':    renderSalesCharts(d); break;
            case 'revenue':  renderRevenueCharts(d); break;
            case 'products': renderProductCharts(d); break;
            case 'customers':renderCustomerCharts(d); break;
            case 'sellers':  renderSellerCharts(d); break;
            case 'payments': renderPaymentCharts(d); break;
            case 'refunds':  renderRefundCharts(d); break;
            case 'support':  renderSupportCharts(d); break;
        }
    }

    // ─── Sales charts ───────────────────────────────
    function renderSalesCharts(d) {
        setText('sales-chart1-title', 'Orders & Revenue Trend');
        setText('sales-chart2-title', 'Orders by Status');
        setText('sales-chart3-title', 'Payment Methods');
        setText('sales-table-title', 'Top 10 Orders by Amount');

        var lc = d.line_chart;
        renderApex('sales-chart1', merge(baseOpts(350), {
            chart: { type: 'line' },
            series: [
                { name: 'Orders', data: lc.orders, type: 'column' },
                { name: 'Revenue', data: lc.revenue, type: 'line' }
            ],
            xaxis: { categories: lc.labels },
            yaxis: [
                { title: { text: 'Orders' }, labels: { style: { colors: labelColor, fontSize: '11px' } } },
                { opposite: true, title: { text: 'Revenue (' + cSymbol + ')' }, labels: { style: { colors: labelColor, fontSize: '11px' }, formatter: function(v) { return cSymbol + v.toLocaleString(); } } }
            ],
            stroke: { width: [0, 3], curve: 'smooth' },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
            colors: [primary, success]
        }));

        var bc = d.bar_chart;
        var statusColors = { pending: warning, processing: info, completed: success, cancelled: danger, refunded: '#a8aaae' };
        renderApex('sales-chart2', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Orders', data: bc.values }],
            xaxis: { categories: bc.labels },
            colors: bc.labels.map(function (l) { return statusColors[l] || primary; }),
            plotOptions: { bar: { distributed: true, columnWidth: '50%', borderRadius: 6 } },
            legend: { show: false }
        }));

        var dc = d.donut_chart;
        renderApex('sales-chart3', merge(baseOpts(300), {
            chart: { type: 'donut' },
            series: dc.values,
            labels: dc.labels,
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '14px', fontFamily: fontFamily, color: headingColor } } } } }
        }));
    }

    // ─── Revenue charts ─────────────────────────────
    function renderRevenueCharts(d) {
        setText('revenue-chart1-title', 'Commission vs Seller Net (12 Months)');
        setText('revenue-chart2-title', 'Revenue by Gateway');
        setText('revenue-chart3-title', 'Revenue by Gateway (Donut)');
        setText('revenue-table-title', 'Revenue by Seller');

        var sb = d.stacked_bar;
        renderApex('revenue-chart1', merge(baseOpts(350), {
            chart: { type: 'bar', stacked: true },
            series: [
                { name: 'Commission', data: sb.commission },
                { name: 'Seller Net', data: sb.seller_net }
            ],
            xaxis: { categories: sb.months },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 5 } },
            colors: [primary, warning]
        }));

        var ac = d.area_chart;
        renderApex('revenue-chart2', merge(baseOpts(350), {
            chart: { type: 'area' },
            series: [
                { name: 'Revenue', data: ac.revenue },
                { name: 'Tax', data: ac.tax },
                { name: 'Discount', data: ac.discount }
            ],
            xaxis: { categories: ac.labels },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            colors: [success, info, danger]
        }));

        var dc = d.donut_chart;
        renderApex('revenue-chart3', merge(baseOpts(300), {
            chart: { type: 'donut' },
            series: dc.values.map(function (v) { return Number(v); }),
            labels: dc.labels,
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '14px', fontFamily: fontFamily, color: headingColor } } } } }
        }));
    }

    // ─── Product charts ─────────────────────────────
    function renderProductCharts(d) {
        setText('products-chart1-title', 'Top 10 Best Sellers by Units');
        setText('products-chart2-title', 'Top 10 by Revenue');
        setText('products-chart3-title', 'Sales by Category');
        setText('products-table-title', 'Product Performance');

        var hb = d.horizontal_bar;
        renderApex('products-chart1', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Units', data: hb.values }],
            xaxis: { categories: hb.labels },
            plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
            colors: [primary]
        }));

        var bc = d.bar_chart;
        renderApex('products-chart2', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Revenue', data: bc.values }],
            xaxis: { categories: bc.labels },
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 5 } },
            colors: [success],
            yaxis: { labels: { formatter: function (v) { return cSymbol + v.toLocaleString(); }, style: { colors: labelColor, fontSize: '11px' } } }
        }));

        var dc = d.donut_chart;
        renderApex('products-chart3', merge(baseOpts(300), {
            chart: { type: 'donut' },
            series: dc.values.map(function (v) { return Number(v); }),
            labels: dc.labels,
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '14px', fontFamily: fontFamily, color: headingColor } } } } }
        }));
    }

    // ─── Customer charts ────────────────────────────
    function renderCustomerCharts(d) {
        setText('customers-chart1-title', 'New Registrations Over Time');
        setText('customers-chart2-title', 'Top 10 Customers by Spend');
        setText('customers-chart3-title', 'Order Frequency Distribution');
        setText('customers-table-title', 'Customer Leaderboard');

        var ac = d.area_chart;
        renderApex('customers-chart1', merge(baseOpts(350), {
            chart: { type: 'area' },
            series: [{ name: 'New Customers', data: ac.values }],
            xaxis: { categories: ac.labels },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.05 } },
            colors: [primary]
        }));

        var bc = d.bar_chart;
        renderApex('customers-chart2', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Total Spent', data: bc.values }],
            xaxis: { categories: bc.labels },
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 5 } },
            colors: [info],
            yaxis: { labels: { formatter: function (v) { return cSymbol + v.toLocaleString(); }, style: { colors: labelColor, fontSize: '11px' } } }
        }));

        var dc = d.donut_chart;
        renderApex('customers-chart3', merge(baseOpts(300), {
            chart: { type: 'donut' },
            series: dc.values,
            labels: dc.labels,
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '14px', fontFamily: fontFamily, color: headingColor } } } } }
        }));
    }

    // ─── Seller charts ──────────────────────────────
    function renderSellerCharts(d) {
        setText('sellers-chart1-title', 'Top 10 Sellers by Revenue');
        setText('sellers-chart2-title', 'Earnings by Status');
        setText('sellers-chart3-title', 'New Seller Applications');
        setText('sellers-table-title', 'Seller Scorecard');

        var bc = d.bar_chart;
        renderApex('sellers-chart1', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Revenue', data: bc.values }],
            xaxis: { categories: bc.labels },
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 5 } },
            colors: [primary],
            yaxis: { labels: { formatter: function (v) { return cSymbol + v.toLocaleString(); }, style: { colors: labelColor, fontSize: '11px' } } }
        }));

        var sb = d.stacked_bar;
        renderApex('sellers-chart2', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Amount', data: sb.values.map(function (v) { return Number(v); }) }],
            xaxis: { categories: sb.labels },
            plotOptions: { bar: { distributed: true, columnWidth: '50%', borderRadius: 6 } },
            legend: { show: false }
        }));

        var lc = d.line_chart;
        renderApex('sellers-chart3', merge(baseOpts(300), {
            chart: { type: 'area' },
            series: [{ name: 'Applications', data: lc.values }],
            xaxis: { categories: lc.labels },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.05 } },
            colors: [success]
        }));
    }

    // ─── Payment charts ─────────────────────────────
    function renderPaymentCharts(d) {
        setText('payments-chart1-title', 'Credits vs Debits Over Time');
        setText('payments-chart2-title', 'Transactions by Category');
        setText('payments-chart3-title', 'Payment Gateway Share');
        setText('payments-table-title', 'Withdrawal Summary');

        var ac = d.area_chart;
        renderApex('payments-chart1', merge(baseOpts(350), {
            chart: { type: 'area' },
            series: [
                { name: 'Credits', data: ac.credits },
                { name: 'Debits', data: ac.debits }
            ],
            xaxis: { categories: ac.labels },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            colors: [success, danger]
        }));

        var bc = d.bar_chart;
        renderApex('payments-chart2', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Transactions', data: bc.values }],
            xaxis: { categories: bc.labels },
            plotOptions: { bar: { distributed: true, columnWidth: '50%', borderRadius: 6 } },
            legend: { show: false }
        }));

        var dc = d.donut_chart;
        renderApex('payments-chart3', merge(baseOpts(300), {
            chart: { type: 'donut' },
            series: dc.values.map(function (v) { return Number(v); }),
            labels: dc.labels,
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '14px', fontFamily: fontFamily, color: headingColor } } } } }
        }));
    }

    // ─── Refund charts ──────────────────────────────
    function renderRefundCharts(d) {
        setText('refunds-chart1-title', 'Refund Requests Over Time');
        setText('refunds-chart2-title', 'Status Distribution');
        setText('refunds-chart3-title', 'Refunds by Seller (Top 10)');
        setText('refunds-table-title', 'Recent Refund Requests');

        var lc = d.line_chart;
        renderApex('refunds-chart1', merge(baseOpts(350), {
            chart: { type: 'area' },
            series: [{ name: 'Refunds', data: lc.values }],
            xaxis: { categories: lc.labels },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.05 } },
            colors: [danger]
        }));

        var dc = d.donut_chart;
        var refStatusColors = { pending: warning, approved: success, rejected: danger };
        renderApex('refunds-chart2', merge(baseOpts(350), {
            chart: { type: 'donut' },
            series: dc.values,
            labels: dc.labels,
            colors: dc.labels.map(function (l) { return refStatusColors[l] || primary; }),
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '14px', fontFamily: fontFamily, color: headingColor } } } } }
        }));

        var bc = d.bar_chart;
        renderApex('refunds-chart3', merge(baseOpts(300), {
            chart: { type: 'bar' },
            series: [{ name: 'Refunds', data: bc.values }],
            xaxis: { categories: bc.labels },
            plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
            colors: [warning]
        }));
    }

    // ─── Support charts ─────────────────────────────
    function renderSupportCharts(d) {
        setText('support-chart1-title', 'Tickets Opened vs Resolved');
        setText('support-chart2-title', 'Tickets by Department');
        setText('support-chart3-title', 'Priority Distribution');
        setText('support-table-title', 'Unresolved Tickets');

        var ac = d.area_chart;
        renderApex('support-chart1', merge(baseOpts(350), {
            chart: { type: 'area' },
            series: [
                { name: 'Opened', data: ac.opened },
                { name: 'Resolved', data: ac.resolved }
            ],
            xaxis: { categories: ac.labels },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            colors: [danger, success]
        }));

        var bc = d.bar_chart;
        renderApex('support-chart2', merge(baseOpts(350), {
            chart: { type: 'bar' },
            series: [{ name: 'Tickets', data: bc.values }],
            xaxis: { categories: bc.labels },
            plotOptions: { bar: { distributed: true, columnWidth: '50%', borderRadius: 6 } },
            legend: { show: false }
        }));

        var dc = d.donut_chart;
        var prioColors = { low: success, medium: info, high: warning, urgent: danger };
        renderApex('support-chart3', merge(baseOpts(300), {
            chart: { type: 'donut' },
            series: dc.values.map(function (v) { return Number(v); }),
            labels: dc.labels,
            colors: dc.labels.map(function (l) { return prioColors[l] || primary; }),
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, fontSize: '14px', fontFamily: fontFamily, color: headingColor } } } } }
        }));
    }

    // ─── Table render ───────────────────────────────
    var tableColumns = {
        sales:     [['Order #','order_number'],['Customer','customer'],['Amount','amount'],['Status','status'],['Date','date']],
        revenue:   [['Seller','store_name'],['Gross','gross'],['Commission','commission'],['Net','net'],['Orders','orders_count']],
        products:  [['Product','product'],['Units','units'],['Revenue','revenue'],['Avg Price','avg_price']],
        customers: [['Name','name'],['Orders','orders'],['Total Spent','total_spent'],['Avg Order','avg_order'],['Last Order','last_order']],
        sellers:   [['Store','store_name'],['Orders','orders'],['Revenue','revenue'],['Commission','commission'],['Rating','rating']],
        payments:  [['Seller','store_name'],['Method','method'],['Amount','amount'],['Status','status'],['Date','date']],
        refunds:   [['Order #','order_number'],['Customer','customer'],['Seller','seller'],['Amount','amount'],['Reason','reason'],['Status','status']],
        support:   [['Ticket #','ticket_number'],['Subject','subject'],['Priority','priority'],['Department','department'],['Created','created'],['Last Reply','last_reply']]
    };

    function renderTable(tab, d) {
        var tbl = document.getElementById(tab + '-table');
        if (!tbl || !d.table) return;

        var cols = tableColumns[tab] || [];
        var thead = '<tr>';
        cols.forEach(function (c) { thead += '<th>' + c[0] + '</th>'; });
        thead += '</tr>';
        tbl.querySelector('thead').innerHTML = thead;

        var tbody = '';
        var rows = Array.isArray(d.table) ? d.table : (d.table.data || d.table);
        if (rows.length === 0) {
            tbody = '<tr><td colspan="' + cols.length + '" class="text-center text-muted py-4">No data available</td></tr>';
        } else {
            rows.forEach(function (r) {
                tbody += '<tr>';
                cols.forEach(function (c) {
                    var val = r[c[1]] !== undefined ? r[c[1]] : '—';
                    if (c[1] === 'status') {
                        var sc = {pending:'warning',processing:'info',completed:'success',cancelled:'secondary',approved:'success',rejected:'danger',active:'success'};
                        val = '<span class="badge bg-label-' + (sc[val] || 'secondary') + '">' + val + '</span>';
                    }
                    if (c[1] === 'priority') {
                        var pc = {low:'success',medium:'info',high:'warning',urgent:'danger'};
                        val = '<span class="badge bg-label-' + (pc[val] || 'secondary') + '">' + val + '</span>';
                    }
                    tbody += '<td>' + val + '</td>';
                });
                tbody += '</tr>';
            });
        }
        tbl.querySelector('tbody').innerHTML = tbody;
    }

    // ─── Export handlers ────────────────────────────
    function getExportUrl(tab, format) {
        var base = urls.export[tab];
        if (!base) return '#';
        var params = new URLSearchParams({
            from: fmt(state.from),
            to: fmt(state.to),
            format: format
        });
        return base + '?' + params.toString();
    }

    document.querySelectorAll('.export-tab').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var tab = a.dataset.tab || state.activeTab;
            window.location.href = getExportUrl(tab, a.dataset.format);
        });
    });

    document.querySelectorAll('.export-current').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = getExportUrl(state.activeTab, a.dataset.format);
        });
    });

    document.getElementById('exportGenerateBtn').addEventListener('click', function () {
        var type = document.getElementById('exportReportType').value;
        var format = document.querySelector('input[name="exportFormat"]:checked').value;
        var dates = exportPicker.selectedDates;
        var from = dates.length >= 1 ? fmt(dates[0]) : fmt(state.from);
        var to = dates.length >= 2 ? fmt(dates[1]) : fmt(state.to);

        var base = urls.export[type];
        if (!base) return;
        var params = new URLSearchParams({ from: from, to: to, format: format });
        window.location.href = base + '?' + params.toString();

        var modal = bootstrap.Modal.getInstance(document.getElementById('exportModal'));
        if (modal) modal.hide();
    });

    // ─── Utility ────────────────────────────────────
    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    // ─── Initial load ───────────────────────────────
    loadTab('sales');
});
