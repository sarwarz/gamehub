/**
 * Support Ticket — Show page logic
 *
 * Expects a global `window.ticketConfig` object set by the Blade view:
 *   { csrfToken, ticketId, baseUrl, replyUrl, customerName, orderNumber, agentName, cannedMap }
 */
'use strict';

(function ($) {
    const cfg = window.ticketConfig || {};

    const thread = document.getElementById('chat-thread');
    if (thread) thread.scrollTop = thread.scrollHeight;

    function ajaxError(xhr) {
        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Something went wrong.' });
    }

    // ── Canned response placeholder replacement ──────────
    function replacePlaceholders(text) {
        return text
            .replace(/\{customer_name\}/g, cfg.customerName || '')
            .replace(/\{order_number\}/g, cfg.orderNumber || '')
            .replace(/\{agent_name\}/g, cfg.agentName || '')
            .replace(/\{amount\}/g, '[AMOUNT]')
            .replace(/\{reason\}/g, '[REASON]')
            .replace(/\{carrier\}/g, '[CARRIER]')
            .replace(/\{tracking\}/g, '[TRACKING]')
            .replace(/\{timeframe\}/g, '[TIMEFRAME]');
    }

    function insertCannedResponse(body) {
        var ta = document.getElementById('reply-message');
        if (!ta) return;
        var text = replacePlaceholders(body),
            start = ta.selectionStart,
            end = ta.selectionEnd;
        ta.value = ta.value.substring(0, start) + text + ta.value.substring(end);
        ta.focus();
        ta.selectionStart = ta.selectionEnd = start + text.length;
    }

    // Quick buttons
    $(document).on('click', '.canned-quick-btn', function () {
        insertCannedResponse($(this).data('body'));
    });

    // Offcanvas list items
    $(document).on('click', '.canned-item', function () {
        insertCannedResponse($(this).data('body'));
        var el = document.getElementById('cannedOffcanvas');
        if (el) bootstrap.Offcanvas.getInstance(el)?.hide();
    });

    // Search filter
    $('#canned-search').on('input', function () {
        var q = this.value.toLowerCase();
        $('#canned-list .canned-item').each(function () {
            var $el = $(this);
            $el.toggle(
                $el.data('title').toLowerCase().includes(q) ||
                ($el.data('shortcut') || '').toLowerCase().includes(q) ||
                $el.data('body').toLowerCase().includes(q)
            );
        });
    });

    // Shortcut expansion in textarea
    var cannedMap = cfg.cannedMap || {};
    var replyTa = document.getElementById('reply-message');

    if (replyTa) {
        replyTa.addEventListener('keydown', function (e) {
            if (e.key === ' ' || e.key === 'Enter' || e.key === 'Tab') {
                var val = this.value.substring(0, this.selectionStart),
                    words = val.split(/\s/),
                    last = words[words.length - 1];
                if (last && cannedMap[last]) {
                    e.preventDefault();
                    var before = val.substring(0, val.length - last.length),
                        after = this.value.substring(this.selectionStart),
                        r = replacePlaceholders(cannedMap[last]);
                    this.value = before + r + after;
                    this.selectionStart = this.selectionEnd = before.length + r.length;
                }
            }
        });
    }

    // ── File management ──────────────────────────────────
    var pendingFiles = [];
    var previewsEl = document.getElementById('attachment-previews');
    var fileInput = document.getElementById('file-input');

    function isImg(f) {
        return f.type.startsWith('image/');
    }

    function addFiles(files) {
        Array.from(files).forEach(function (f) {
            var id = Date.now() + '_' + Math.random().toString(36).substr(2, 6);
            pendingFiles.push({ id: id, file: f });
            renderPrev(id, f);
        });
    }

    function removeFile(id) {
        pendingFiles = pendingFiles.filter(function (p) { return p.id !== id; });
        var el = document.getElementById('att-' + id);
        if (el) el.remove();
    }

    function renderPrev(id, file) {
        var w = document.createElement('div');
        w.className = 'att-preview-item';
        w.id = 'att-' + id;

        var rb = document.createElement('button');
        rb.type = 'button';
        rb.className = 'att-remove';
        rb.innerHTML = '<i class="ti tabler-x"></i>';
        rb.onclick = function () { removeFile(id); };

        var nl = document.createElement('div');
        nl.className = 'att-name';
        nl.textContent = file.name.length > 10 ? file.name.substring(0, 8) + '…' : file.name;

        if (isImg(file)) {
            var img = document.createElement('img');
            var reader = new FileReader();
            reader.onload = function (e) { img.src = e.target.result; };
            reader.readAsDataURL(file);
            w.appendChild(img);
        } else {
            var icon = document.createElement('i');
            icon.className = 'ti tabler-file';
            icon.style.fontSize = '1.2rem';
            icon.style.color = '#a1acb8';
            w.appendChild(icon);
        }

        w.appendChild(rb);
        w.appendChild(nl);
        previewsEl.appendChild(w);
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () { addFiles(this.files); this.value = ''; });
    }

    // Paste handler
    if (replyTa) {
        replyTa.addEventListener('paste', function (e) {
            var items = e.clipboardData?.items;
            if (!items) return;
            var imgs = [];
            for (var i = 0; i < items.length; i++) {
                if (items[i].kind === 'file' && items[i].type.startsWith('image/')) {
                    var f = items[i].getAsFile();
                    if (f) {
                        var ext = f.type.split('/')[1] || 'png';
                        imgs.push(new File([f], 'pasted_' + Date.now() + '.' + ext, { type: f.type }));
                    }
                }
            }
            if (imgs.length) { e.preventDefault(); addFiles(imgs); }
        });

        replyTa.addEventListener('dragover', function (e) { e.preventDefault(); this.style.borderColor = '#7367f0'; });
        replyTa.addEventListener('dragleave', function () { this.style.borderColor = ''; });
        replyTa.addEventListener('drop', function (e) {
            e.preventDefault();
            this.style.borderColor = '';
            if (e.dataTransfer?.files?.length) addFiles(e.dataTransfer.files);
        });
    }

    // ── AJAX form submit ─────────────────────────────────
    var replyForm = document.getElementById('reply-form');
    if (replyForm) {
        replyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var m = document.getElementById('reply-message');
            if (!m.value.trim() && pendingFiles.length === 0) return;

            var fd = new FormData();
            fd.append('_token', cfg.csrfToken);
            fd.append('message', m.value);
            if (document.getElementById('chk-note').checked) fd.append('is_internal_note', '1');
            pendingFiles.forEach(function (p) { fd.append('attachments[]', p.file); });

            var btn = document.getElementById('btn-send-reply');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';

            $.ajax({
                url: cfg.replyUrl,
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function () { location.reload(); },
                error: function (x) {
                    btn.disabled = false;
                    btn.innerHTML = '<span class="d-none d-sm-inline-block me-1">Send</span><i class="ti tabler-send" style="font-size:.95rem"></i>';
                    var msg = 'Error.';
                    if (x.responseJSON?.errors) msg = Object.values(x.responseJSON.errors).flat().join('<br>');
                    else if (x.responseJSON?.message) msg = x.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Error', html: msg });
                }
            });
        });
    }

    // ── AI Reply ─────────────────────────────────────────
    if (cfg.aiEnabled) {
        var aiRow = document.getElementById('ai-instruction-row');
        var aiInput = document.getElementById('ai-instruction');

        $('#btn-ai-reply').on('click', function () {
            if (aiRow) {
                aiRow.classList.toggle('show');
                if (aiRow.classList.contains('show') && aiInput) {
                    aiInput.focus();
                }
            }
        });

        $('#btn-ai-cancel').on('click', function () {
            if (aiRow) aiRow.classList.remove('show');
            if (aiInput) aiInput.value = '';
        });

        function generateAiReply() {
            var instruction = aiInput ? aiInput.value.trim() : '';
            var context = $.extend({}, cfg.aiContext);
            if (instruction) context.instruction = instruction;

            var genBtn = document.getElementById('btn-ai-generate');
            var origHtml = genBtn.innerHTML;
            genBtn.disabled = true;
            genBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating…';

            var ta = document.getElementById('reply-message');

            $.ajax({
                url: cfg.aiGenerateUrl,
                type: 'POST',
                data: {
                    _token: cfg.csrfToken,
                    type: 'ticket_reply',
                    title: cfg.aiContext.subject,
                    ticket_context: context
                },
                success: function (res) {
                    if (res.success && res.data && res.data.content) {
                        ta.value = res.data.content;
                        ta.focus();
                        ta.style.height = 'auto';
                        ta.style.height = ta.scrollHeight + 'px';
                        if (aiRow) aiRow.classList.remove('show');
                        if (aiInput) aiInput.value = '';

                        Swal.fire({
                            icon: 'success',
                            title: 'AI Draft Ready',
                            text: 'Review and edit the draft before sending.',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: res.error || 'AI returned empty response.' });
                    }
                },
                error: function (xhr) {
                    var msg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to generate AI reply.';
                    Swal.fire({ icon: 'error', title: 'AI Error', text: msg });
                },
                complete: function () {
                    genBtn.disabled = false;
                    genBtn.innerHTML = origHtml;
                }
            });
        }

        $('#btn-ai-generate').on('click', generateAiReply);

        if (aiInput) {
            aiInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); generateAiReply(); }
            });
        }
    }

    // ── Sidebar actions ──────────────────────────────────
    var base = cfg.baseUrl;
    var tid = cfg.ticketId;
    var tok = cfg.csrfToken;

    $('#btn-assign').on('click', function () {
        var v = $('#assign-admin').val();
        $.ajax({ url: base + '/' + tid + '/assign', type: 'PUT', data: { admin_id: v || '', _token: tok },
            success: function (r) { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(function () { location.reload(); }); },
            error: ajaxError
        });
    });

    $('#btn-change-status').on('click', function () {
        $.ajax({ url: base + '/' + tid + '/status', type: 'PUT', data: { status: $('#change-status').val(), _token: tok },
            success: function (r) { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(function () { location.reload(); }); },
            error: ajaxError
        });
    });

    $('#btn-change-priority').on('click', function () {
        $.ajax({ url: base + '/' + tid + '/priority', type: 'PUT', data: { priority: $('#change-priority').val(), _token: tok },
            success: function (r) { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(function () { location.reload(); }); },
            error: ajaxError
        });
    });

    $('.btn-resolve').on('click', function () {
        $.ajax({ url: base + '/' + tid + '/status', type: 'PUT', data: { status: 'resolved', _token: tok },
            success: function (r) { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(function () { location.reload(); }); },
            error: ajaxError
        });
    });

    $('.btn-escalate').on('click', function () {
        Swal.fire({ title: 'Escalate?', text: 'Remove seller assignment and flag ticket.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Escalate' }).then(function (r) {
            if (r.isConfirmed) {
                $.ajax({ url: base + '/' + tid + '/escalate', type: 'POST', data: { _token: tok },
                    success: function (r) { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(function () { location.reload(); }); },
                    error: ajaxError
                });
            }
        });
    });

    $('.btn-close-ticket').on('click', function () {
        $.ajax({ url: base + '/' + tid + '/status', type: 'PUT', data: { status: 'closed', _token: tok },
            success: function (r) { Swal.fire({ icon: 'success', title: r.message, timer: 1500, showConfirmButton: false }).then(function () { location.reload(); }); },
            error: ajaxError
        });
    });

})(jQuery);
