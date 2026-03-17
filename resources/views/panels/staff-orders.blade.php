@extends('layouts.panel')

@section('title', 'Order Management')
@section('page_key', 'staff-orders')
@section('expected_role', 'staff')
@section('hero_eyebrow', 'Staff')
@section('hero_title', 'Manage the service line one order at a time.')
@section('hero_text', 'View every incoming order and move it through the pipeline, from pending to preparing to ready for pickup to completed.')

@section('content')
<div class="dashboard-grid dashboard-grid--single">

    {{-- ── Order management table ──────────────────────────────── --}}
    <section class="dashboard-card dashboard-card--full">
        <div class="dashboard-card-head">
            <div>
                <h2>Order management</h2>
                <p>Update each order's status to keep the kitchen flow moving.</p>
            </div>
            <button class="secondary-button" type="button" data-refresh-orders style="flex-shrink:0;">
                ↻ Refresh
            </button>
        </div>

        <div class="auth-feedback" data-staff-feedback></div>

        <div class="table-shell">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Total price</th>
                        <th>Created at</th>
                        <th>Items</th>
                        <th>Update status</th>
                    </tr>
                </thead>
                <tbody data-staff-orders>
                    <tr>
                        <td colspan="7" class="dashboard-empty">Orders will appear here.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

</div>{{-- /dashboard-grid --}}
@endsection


@push('scripts')
<script>
(() => {
    const ordersBody = document.querySelector('[data-staff-orders]');
    const feedback   = document.querySelector('[data-staff-feedback]');
    const refreshBtn = document.querySelector('[data-refresh-orders]');

    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function getAuthToken() {
        return localStorage.getItem('restaurant-api-token') || localStorage.getItem('token') || '';
    }

    async function apiFetch(url, options = {}) {
        const token = getAuthToken();
        const headers = {
            'Accept': 'application/json',
            ...(options.headers || {}),
        };

        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        const response = await fetch(url, {
            ...options,
            headers,
        });

        if (response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('restaurant-api-token');
            localStorage.removeItem('restaurant-api-user');
            window.location.assign('{{ route('login') }}');
            throw new Error('Unauthorized');
        }

        return response;
    }

    /* status pipeline — what comes after each state */
    const nextStatus = {
        pending:           'preparing',
        preparing:         'ready_for_pickup',
        ready_for_pickup:  'completed',
        completed:         null,
    };

    const statusLabel = {
        pending:          'Mark preparing',
        preparing:        'Mark ready for pickup',
        ready_for_pickup: 'Mark completed',
        completed:        '—',
    };

    const statusText = {
        pending: 'Pending',
        preparing: 'Preparing',
        ready_for_pickup: 'Ready for pickup',
        completed: 'Completed',
    };

    /* ── feedback ─────────────────────────────────────────────── */
    function showFeedback(msg, type = 'success') {
        feedback.textContent = msg;
        feedback.className = `auth-feedback is-visible ${type === 'error' ? 'is-error' : 'is-success'}`;
        setTimeout(() => feedback.className = 'auth-feedback', 3000);
    }

    /* ── load orders ──────────────────────────────────────────── */
    async function loadOrders() {
        ordersBody.innerHTML = '<tr><td colspan="7" class="dashboard-empty">Loading…</td></tr>';
        try {
            const res    = await apiFetch('/api/orders');
            if (!res.ok) throw new Error();
            const data   = await res.json();
            const orders = data.data ?? data;

            if (!orders.length) {
                ordersBody.innerHTML = '<tr><td colspan="7" class="dashboard-empty">No orders yet.</td></tr>';
                return;
            }

            ordersBody.innerHTML = orders.map(o => {
                const next  = nextStatus[o.status];
                const label = statusLabel[o.status] ?? '—';

                return `
                    <tr data-order-row="${o.id}">
                        <td>#${o.id}</td>
                        <td>${o.user?.name ?? o.user_id}</td>
                        <td>
                            <span class="status-badge" data-status="${o.status}">
                                ${statusText[o.status] ?? o.status}
                            </span>
                        </td>
                        <td>RM ${parseFloat(o.total_price).toFixed(2)}</td>
                        <td>${new Date(o.created_at).toLocaleString()}</td>
                        <td>
                            <div class="item-stack">
                                ${(o.order_items ?? o.orderItems ?? []).map(i => `
                                    <span class="item-pill">${i.menu_item?.name ?? i.menuItem?.name ?? 'Item'} &times; ${i.quantity ?? 1}</span>
                                `).join('')}
                            </div>
                        </td>
                        <td>
                            ${next
                                ? `<button class="auth-button"
                                          style="font-size:0.82rem;padding:8px 14px;white-space:nowrap;"
                                          data-advance-id="${o.id}"
                                          data-next-status="${next}">
                                       ${label}
                                   </button>`
                                : `<span style="color:var(--muted);font-size:0.85rem;">Completed</span>`
                            }
                        </td>
                    </tr>
                `;
            }).join('');

            /* advance status buttons */
            ordersBody.querySelectorAll('[data-advance-id]').forEach(btn => {
                btn.addEventListener('click', () => advanceOrder(btn));
            });

        } catch {
            ordersBody.innerHTML = '<tr><td colspan="7" class="dashboard-empty">Could not load orders.</td></tr>';
        }
    }

    /* ── advance single order status ──────────────────────────── */
    async function advanceOrder(btn) {
        const id   = btn.dataset.advanceId;
        const next = btn.dataset.nextStatus;
        const originalLabel = btn.textContent;

        btn.disabled = true;
        btn.textContent = 'Updating…';

        try {
            const res = await apiFetch(`/api/orders/${id}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ status: next }),
            });

            if (!res.ok) throw new Error();

            showFeedback(`Order #${id} marked as ${statusText[next] ?? next}.`);

            /* optimistic row update without full reload */
            const row        = ordersBody.querySelector(`[data-order-row="${id}"]`);
            const badge      = row.querySelector('.status-badge');
            badge.dataset.status = next;
            badge.textContent    = statusText[next] ?? next;

            const afterNext  = nextStatus[next];
            const cell       = btn.closest('td');
            if (afterNext) {
                btn.dataset.nextStatus = afterNext;
                btn.textContent        = statusLabel[next];
                btn.disabled           = false;
            } else {
                cell.innerHTML = '<span style="color:var(--muted);font-size:0.85rem;">Completed</span>';
            }
        } catch {
            showFeedback(`Failed to update order #${id}.`, 'error');
            btn.disabled    = false;
            btn.textContent = originalLabel;
        }
    }

    /* ── manual refresh ───────────────────────────────────────── */
    refreshBtn.addEventListener('click', loadOrders);

    /* ── auto-refresh every 30 s ─────────────────────────────── */
    setInterval(loadOrders, 30_000);

    /* ── init ─────────────────────────────────────────────────── */
    loadOrders();
})();
</script>
@endpush
