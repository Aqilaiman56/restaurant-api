@extends('layouts.panel')

@section('title', 'My Orders')
@section('page_key', 'customer-orders')
@section('expected_role', 'customer')
@section('hero_eyebrow', 'Customer')
@section('hero_title', 'Pick dishes, build an order, send it to the kitchen, ready for pickup.')
@section('hero_text', 'Browse available menu items, add them to your basket, and place your order. Track every order you have placed right here and ready for pickup.')

@section('content')
<div class="dashboard-grid dashboard-grid--customer">

    {{-- ── Available menu ──────────────────────────────────────── --}}
    <section class="dashboard-card dashboard-card--full">
        <div class="dashboard-card-head">
            <div>
                <h2>Available menu</h2>
                <p>Add one or more items to your basket before placing an order.</p>
            </div>
        </div>

        <div class="menu-catalog" data-customer-menu>
            <div class="dashboard-empty">Available menu items will appear here.</div>
        </div>
    </section>

    {{-- ── Basket ───────────────────────────────────────────────── --}}
    <section class="dashboard-card dashboard-card--full">
        <div class="dashboard-card-head">
            <div>
                <h2>Your basket</h2>
                <p>Review items before placing your order.</p>
            </div>
        </div>

        <div class="cart-list" data-cart-items>
            <div class="dashboard-empty">No items added yet.</div>
        </div>

        <div class="cart-total-row">
            <span>Total</span>
            <strong data-cart-total>RM 0.00</strong>
        </div>

         <div class="cart-total-row">
                <span>  </span>
        </div>

        <div class="dashboard-actions">
            <button class="auth-button" type="button" data-place-order>Place order</button>
        </div>

        <div class="auth-feedback" data-customer-feedback></div>
    </section>

    {{-- ── Order history ────────────────────────────────────────── --}}
    <section class="dashboard-card dashboard-card--full">
        <div class="dashboard-card-head">
            <div>
                <h2>Your orders</h2>
                <p>Every order you have placed, with live status, item breakdown and ready for pickup.</p>
            </div>
        </div>

        <div class="table-shell">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Status</th>
                        <th>Total price</th>
                        <th>Created at</th>
                        <th>Items</th>
                    </tr>
                </thead>
                <tbody data-customer-orders>
                    <tr>
                        <td colspan="5" class="dashboard-empty">Your orders will appear here.</td>
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
    /* ── helpers ─────────────────────────────────────────────── */
    const $ = id => document.getElementById(id);
    const qs = (sel, ctx = document) => ctx.querySelector(sel);
    const menuWrap     = document.querySelector('[data-customer-menu]');
    const cartWrap     = document.querySelector('[data-cart-items]');
    const cartTotal    = document.querySelector('[data-cart-total]');
    const ordersBody   = document.querySelector('[data-customer-orders]');
    const feedback     = document.querySelector('[data-customer-feedback]');
    const placeBtn     = document.querySelector('[data-place-order]');

    const statusText = {
        pending: 'Pending',
        preparing: 'Preparing',
        ready_for_pickup: 'Ready for pickup',
        completed: 'Completed',
    };

    let basket = []; // [{ id, name, price }]

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

    /* ── feedback helper ─────────────────────────────────────── */
    function showFeedback(msg, type = 'success') {
        feedback.textContent = msg;
        feedback.className = `auth-feedback is-visible ${type === 'error' ? 'is-error' : 'is-success'}`;
        setTimeout(() => feedback.className = 'auth-feedback', 3200);
    }

    /* ── render basket ───────────────────────────────────────── */
    function renderBasket() {
        if (!basket.length) {
            cartWrap.innerHTML = '<div class="dashboard-empty">No items added yet.</div>';
            cartTotal.textContent = 'RM 0.00';
            return;
        }
        const total = basket.reduce((s, i) => s + i.price, 0);
        cartTotal.textContent = `RM ${total.toFixed(2)}`;
        cartWrap.innerHTML = basket.map((item, idx) => `
            <div class="cart-row">
                <span>${item.name}</span>
                <span class="menu-product-price">RM ${item.price.toFixed(2)}</span>
                <button class="table-danger" style="border-radius:12px;padding:6px 10px;font-size:0.8rem;cursor:pointer;"
                        data-remove="${idx}">Remove</button>
            </div>
        `).join('');

        cartWrap.querySelectorAll('[data-remove]').forEach(btn => {
            btn.addEventListener('click', () => {
                basket.splice(+btn.dataset.remove, 1);
                renderBasket();
            });
        });
    }

    /* ── load menu ───────────────────────────────────────────── */
    async function loadMenu() {
        try {
            const res  = await apiFetch('/api/menu-items');
            if (!res.ok) throw new Error();
            const data = await res.json();
            const items = (data.data ?? data).filter(i => i.availability === 'available');

            if (!items.length) {
                menuWrap.innerHTML = '<div class="dashboard-empty">No items available right now.</div>';
                return;
            }

            menuWrap.innerHTML = items.map(item => `
                <div class="menu-product">
                    ${item.image_url
                        ? `<img src="${item.image_url}" alt="${item.name}" class="menu-product-image">`
                        : ''}
                    <h3>${item.name}</h3>
                    <p>${item.description ?? ''}</p>
                    <div class="menu-product-meta">
                        <span class="menu-product-price">RM ${parseFloat(item.price).toFixed(2)}</span>
                        <div class="menu-product-actions">
                            <button class="auth-button" style="font-size:0.82rem;padding:8px 14px;"
                                    data-add-id="${item.id}"
                                    data-add-name="${item.name}"
                                    data-add-price="${item.price}">
                                + Add
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            menuWrap.querySelectorAll('[data-add-id]').forEach(btn => {
                btn.addEventListener('click', () => {
                    basket.push({ id: btn.dataset.addId, name: btn.dataset.addName, price: parseFloat(btn.dataset.addPrice) });
                    renderBasket();
                });
            });
        } catch {
            menuWrap.innerHTML = '<div class="dashboard-empty">Could not load menu.</div>';
        }
    }

    /* ── load orders ─────────────────────────────────────────── */
    async function loadOrders() {
        try {
            const res  = await apiFetch('/api/orders');
            if (!res.ok) throw new Error();
            const data = await res.json();
            const orders = data.data ?? data;

            if (!orders.length) {
                ordersBody.innerHTML = '<tr><td colspan="5" class="dashboard-empty">No orders yet.</td></tr>';
                return;
            }

            ordersBody.innerHTML = orders.map(o => `
                <tr>
                    <td>#${o.id}</td>
                    <td><span class="status-badge" data-status="${o.status}">${statusText[o.status] ?? o.status}</span></td>
                    <td>RM ${parseFloat(o.total_price).toFixed(2)}</td>
                    <td>${new Date(o.created_at).toLocaleString()}</td>
                    <td>
                        <div class="item-stack">
                            ${(o.order_items ?? o.orderItems ?? []).map(i => `
                                <span class="item-pill">${i.menu_item?.name ?? i.menuItem?.name ?? 'Item'} &times; ${i.quantity ?? 1}</span>
                            `).join('')}
                        </div>
                    </td>
                </tr>
            `).join('');
        } catch {
            ordersBody.innerHTML = '<tr><td colspan="5" class="dashboard-empty">Could not load orders.</td></tr>';
        }
    }

    /* ── place order ─────────────────────────────────────────── */
    placeBtn.addEventListener('click', async () => {
        if (!basket.length) { showFeedback('Your basket is empty.', 'error'); return; }

        placeBtn.disabled = true;
        try {
            const res = await apiFetch('/api/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify({
                    items: basket.map(i => ({ menu_item_id: i.id, quantity: 1 })),
                }),
            });

            if (!res.ok) throw new Error();
            basket = [];
            renderBasket();
            showFeedback('Order placed successfully!');
            loadOrders();
        } catch {
            showFeedback('Failed to place order. Please try again.', 'error');
        } finally {
            placeBtn.disabled = false;
        }
    });

    /* ── init ────────────────────────────────────────────────── */
    loadMenu();
    loadOrders();
})();
</script>
@endpush