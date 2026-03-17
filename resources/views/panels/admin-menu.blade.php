@extends('layouts.panel')

@section('title', 'Menu Management')
@section('page_key', 'admin-menu')
@section('expected_role', 'admin')
@section('hero_eyebrow', 'Admin')
@section('hero_title', 'Menu management with full kitchen visibility.')
@section('hero_text', 'Create, update, and remove menu items. Monitor every order placed across the restaurant in real time.')

@section('content')
<div class="dashboard-grid">

    {{-- ── Create / edit form ──────────────────────────────────── --}}
    <section class="dashboard-card dashboard-card--form">
        <div class="dashboard-card-head">
            <div>
                <h2>Create or update a menu item</h2>
                <p>Fill in the fields below, then save. Select a row in the list to edit it.</p>
            </div>
        </div>

        <form class="dashboard-form" data-menu-form>
            <input type="hidden" name="menu_item_id" data-menu-id>

            <div class="dashboard-form-grid">
                <div class="auth-field">
                    <label for="menu-name">Name</label>
                    <input id="menu-name"
                           class="auth-input"
                           type="text"
                           name="name"
                           placeholder="Nasi Lemak Ayam Berempah"
                           required>
                </div>

                <div class="auth-field">
                    <label for="menu-price">Price (RM)</label>
                    <input id="menu-price"
                           class="auth-input"
                           type="number"
                           name="price"
                           min="0"
                           step="0.01"
                           placeholder="12.90"
                           required>
                </div>

                <div class="auth-field dashboard-form-span-full">
                    <label for="menu-description">Description</label>
                    <textarea id="menu-description"
                              class="auth-input dashboard-textarea"
                              name="description"
                              rows="3"
                              placeholder="Fragrant coconut rice, sambal, anchovies, peanuts, egg."></textarea>
                </div>

                <div class="auth-field">
                    <label for="menu-availability">Availability</label>
                    <select id="menu-availability" class="auth-input" name="availability" required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="dashboard-actions">
                <button class="auth-button" type="submit" data-menu-submit>
                    Create menu item
                </button>
                <button class="secondary-button" type="button" data-menu-reset>
                    Clear form
                </button>
            </div>

            <div class="auth-feedback" data-menu-feedback></div>
        </form>
    </section>

    {{-- ── Menu list ───────────────────────────────────────────── --}}
    <section class="dashboard-card">
        <div class="dashboard-card-head">
            <div>
                <h2>Menu list</h2>
                <p>Click <strong>Edit</strong> to load an item into the form above.</p>
            </div>
        </div>

        <div class="table-shell">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody data-menu-list>
                    <tr>
                        <td colspan="4" class="dashboard-empty">Menu items will appear here.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    {{-- ── All orders ───────────────────────────────────────────── --}}
    <section class="dashboard-card dashboard-card--full">
        <div class="dashboard-card-head">
            <div>
                <h2>All orders</h2>
                <p>Every order across the restaurant — order ID, user, status, total, and items.</p>
            </div>
        </div>

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
                    </tr>
                </thead>
                <tbody data-admin-orders>
                    <tr>
                        <td colspan="6" class="dashboard-empty">Orders will appear here.</td>
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
    const form       = document.querySelector('[data-menu-form]');
    const menuIdInput= document.querySelector('[data-menu-id]');
    const submitBtn  = document.querySelector('[data-menu-submit]');
    const resetBtn   = document.querySelector('[data-menu-reset]');
    const feedback   = document.querySelector('[data-menu-feedback]');
    const menuList   = document.querySelector('[data-menu-list]');
    const ordersBody = document.querySelector('[data-admin-orders]');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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

    /* ── feedback ─────────────────────────────────────────────── */
    function showFeedback(msg, type = 'success') {
        feedback.textContent = msg;
        feedback.className = `auth-feedback is-visible ${type === 'error' ? 'is-error' : 'is-success'}`;
        setTimeout(() => feedback.className = 'auth-feedback', 3200);
    }

    /* ── reset form to "create" mode ─────────────────────────── */
    function resetForm() {
        form.reset();
        menuIdInput.value = '';
        submitBtn.textContent = 'Create menu item';
    }
    resetBtn.addEventListener('click', resetForm);

    /* ── load menu list ───────────────────────────────────────── */
    async function loadMenu() {
        try {
            const res  = await apiFetch('/api/menu-items');
            if (!res.ok) throw new Error();
            const data = await res.json();
            const items = data.data ?? data;

            if (!items.length) {
                menuList.innerHTML = '<tr><td colspan="4" class="dashboard-empty">No menu items yet.</td></tr>';
                return;
            }

            menuList.innerHTML = items.map(item => `
                <tr>
                    <td>${item.name}</td>
                    <td>RM ${parseFloat(item.price).toFixed(2)}</td>
                    <td>
                        <span class="availability-badge" data-availability="${item.availability}">
                            ${item.availability}
                        </span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="table-action" data-edit-id="${item.id}">Edit</button>
                            <button class="table-danger" data-delete-id="${item.id}">Delete</button>
                        </div>
                    </td>
                </tr>
            `).join('');

            /* edit */
            menuList.querySelectorAll('[data-edit-id]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const res  = await apiFetch(`/api/menu-items/${btn.dataset.editId}`);
                    if (!res.ok) throw new Error();
                    const item = await res.json();
                    menuIdInput.value = item.id;
                    form.name.value         = item.name;
                    form.price.value        = item.price;
                    form.description.value  = item.description ?? '';
                    form.availability.value = item.availability;
                    submitBtn.textContent   = 'Update menu item';
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            /* delete */
            menuList.querySelectorAll('[data-delete-id]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Delete this menu item?')) return;
                    const res = await apiFetch(`/api/menu-items/${btn.dataset.deleteId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                    });
                    if (!res.ok) throw new Error();
                    showFeedback('Item deleted.');
                    loadMenu();
                });
            });
        } catch {
            menuList.innerHTML = '<tr><td colspan="4" class="dashboard-empty">Could not load menu.</td></tr>';
        }
    }

    /* ── load all orders ──────────────────────────────────────── */
    async function loadOrders() {
        try {
            const res    = await apiFetch('/api/orders');
            if (!res.ok) throw new Error();
            const data   = await res.json();
            const orders = data.data ?? data;

            if (!orders.length) {
                ordersBody.innerHTML = '<tr><td colspan="6" class="dashboard-empty">No orders yet.</td></tr>';
                return;
            }

            ordersBody.innerHTML = orders.map(o => `
                <tr>
                    <td>#${o.id}</td>
                    <td>${o.user?.name ?? o.user_id}</td>
                    <td><span class="status-badge" data-status="${o.status}">${o.status}</span></td>
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
            ordersBody.innerHTML = '<tr><td colspan="6" class="dashboard-empty">Could not load orders.</td></tr>';
        }
    }

    /* ── submit form (create or update) ──────────────────────── */
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const id     = menuIdInput.value;
        const method = id ? 'PUT' : 'POST';
        const url    = id ? `/api/menu-items/${id}` : '/api/menu-items';

        submitBtn.disabled = true;
        try {
            const res = await apiFetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    name:         form.name.value,
                    price:        parseFloat(form.price.value),
                    description:  form.description.value,
                    availability: form.availability.value,
                }),
            });
            if (!res.ok) throw new Error();
            showFeedback(id ? 'Item updated.' : 'Item created.');
            resetForm();
            loadMenu();
        } catch {
            showFeedback('Save failed. Please try again.', 'error');
        } finally {
            submitBtn.disabled = false;
        }
    });

    /* ── init ─────────────────────────────────────────────────── */
    loadMenu();
    loadOrders();
})();
</script>
@endpush