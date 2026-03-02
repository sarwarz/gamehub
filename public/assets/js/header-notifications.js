'use strict';

(function () {
    var urls = window._notificationUrls;
    if (!urls) { console.warn('[Notifications] _notificationUrls not found'); return; }

    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

    var badge       = document.getElementById('notification-badge');
    var countBadge  = document.getElementById('notification-count-badge');
    var list        = document.getElementById('notification-list');
    var loading     = document.getElementById('notification-loading');
    var markAllBtn  = document.getElementById('mark-all-read-btn');

    if (!list) { console.warn('[Notifications] #notification-list not found in DOM'); return; }

    var typeColors = {
        order:            'bg-label-success',
        payment:          'bg-label-warning',
        order_status:     'bg-label-info',
        ticket:           'bg-label-primary',
        ticket_reply:     'bg-label-info',
        ticket_status:    'bg-label-secondary',
        ticket_assigned:  'bg-label-warning',
        ticket_escalated: 'bg-label-danger',
        info:             'bg-label-primary',
        warning:          'bg-label-warning',
        success:          'bg-label-success',
        danger:           'bg-label-danger',
        announcement:     'bg-label-info'
    };

    var POLL_INTERVAL = 30000;
    var pollTimer = null;
    var lastUnreadCount = -1;

    function fetchNotifications() {
        fetch(urls.fetch, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            if (lastUnreadCount !== -1 && data.unread_count > lastUnreadCount) {
                animateBell();
            }
            lastUnreadCount = data.unread_count;
            renderNotifications(data);
        })
        .catch(function (err) {
            console.error('[Notifications] Fetch error:', err);
            if (list) list.innerHTML = '<li class="list-group-item text-center py-5 text-muted">' +
                '<div class="d-flex flex-column align-items-center justify-content-center">' +
                '<i class="icon-base ti tabler-bell-off icon-xl mb-2"></i>' +
                '<span>No notifications</span></div></li>';
        });
    }

    function animateBell() {
        var bellIcon = document.querySelector('.dropdown-notifications .tabler-bell');
        if (!bellIcon) return;
        bellIcon.classList.add('bell-ring');
        setTimeout(function() { bellIcon.classList.remove('bell-ring'); }, 1000);
    }

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(fetchNotifications, POLL_INTERVAL);
    }

    function stopPolling() {
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    document.addEventListener('visibilitychange', function() {
        if (document.hidden) { stopPolling(); } else { fetchNotifications(); startPolling(); }
    });

    function renderNotifications(data) {
        if (data.unread_count > 0) {
            badge.classList.remove('d-none');
            countBadge.style.display = '';
            countBadge.textContent = data.unread_count + ' New';
        } else {
            badge.classList.add('d-none');
            countBadge.style.display = 'none';
        }

        if (!data.notifications || data.notifications.length === 0) {
            list.innerHTML = '<li class="list-group-item text-center py-5 text-muted">' +
                '<div class="d-flex flex-column align-items-center justify-content-center">' +
                '<i class="icon-base ti tabler-bell-off icon-xl mb-2"></i>' +
                '<span>No notifications yet</span></div></li>';
            return;
        }

        var html = '';
        data.notifications.forEach(function (n) {
            var readClass = n.read ? ' marked-as-read' : '';
            var colorClass = typeColors[n.type] || 'bg-label-primary';
            var icon = n.icon || 'tabler-bell';

            html += '<li class="list-group-item list-group-item-action dropdown-notifications-item' + readClass + '">' +
                '<div class="d-flex">' +
                    '<div class="flex-shrink-0 me-3">' +
                        '<div class="avatar"><span class="avatar-initial rounded-circle ' + colorClass + '">' +
                        '<i class="icon-base ti ' + icon + '"></i></span></div>' +
                    '</div>' +
                    '<div class="flex-grow-1">' +
                        (n.url ? '<a href="' + n.url + '" class="text-body">' : '') +
                        '<h6 class="small mb-1">' + escapeHtml(n.title) + '</h6>' +
                        '<small class="mb-1 d-block text-body">' + escapeHtml(n.message) + '</small>' +
                        (n.url ? '</a>' : '') +
                        '<small class="text-body-secondary">' + n.time_ago + '</small>' +
                    '</div>' +
                    '<div class="flex-shrink-0 dropdown-notifications-actions">' +
                        (!n.read ? '<a href="javascript:void(0)" class="dropdown-notifications-read" data-id="' + n.id + '">' +
                            '<span class="badge badge-dot"></span></a>' : '') +
                    '</div>' +
                '</div>' +
            '</li>';
        });

        list.innerHTML = html;

        list.querySelectorAll('.dropdown-notifications-read').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var id = this.getAttribute('data-id');
                markAsRead(id, this);
            });
        });
    }

    function markAsRead(id, btn) {
        var url = urls.markRead.replace(':id', id);
        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function () {
            var item = btn.closest('.dropdown-notifications-item');
            if (item) item.classList.add('marked-as-read');
            btn.remove();

            var currentCount = parseInt(countBadge.textContent) || 0;
            if (currentCount > 1) {
                countBadge.textContent = (currentCount - 1) + ' New';
            } else {
                badge.classList.add('d-none');
                countBadge.style.display = 'none';
            }
        });
    }

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            fetch(urls.markAllRead, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).then(function () {
                list.querySelectorAll('.dropdown-notifications-item').forEach(function (item) {
                    item.classList.add('marked-as-read');
                });
                list.querySelectorAll('.dropdown-notifications-read').forEach(function (btn) {
                    btn.remove();
                });
                badge.classList.add('d-none');
                countBadge.style.display = 'none';
            });
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    fetchNotifications();
    startPolling();
})();
