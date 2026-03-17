import './bootstrap';

const tokenStorageKey = 'restaurant-api-token';
const userStorageKey = 'restaurant-api-user';
const roleRoutes = {
	admin: '/admin/menu',
	staff: '/staff/orders',
	customer: '/customer/orders',
};

const escapeHtml = (value) =>
	String(value ?? '')
		.replaceAll('&', '&amp;')
		.replaceAll('<', '&lt;')
		.replaceAll('>', '&gt;')
		.replaceAll('"', '&quot;')
		.replaceAll("'", '&#039;');

const formatCurrency = (value) => `RM ${Number(value ?? 0).toFixed(2)}`;
const formatDate = (value) => {
	if (!value) {
		return '-';
	}

	return new Intl.DateTimeFormat('en-MY', {
		dateStyle: 'medium',
		timeStyle: 'short',
	}).format(new Date(value));
};

const setStoredToken = (token) => {
	if (token) {
		window.localStorage.setItem(tokenStorageKey, token);
		window.axios.defaults.headers.common.Authorization = `Bearer ${token}`;
		return;
	}

	window.localStorage.removeItem(tokenStorageKey);
	delete window.axios.defaults.headers.common.Authorization;
};

const bootstrapStoredToken = () => {
	const token = window.localStorage.getItem(tokenStorageKey);

	if (token) {
		window.axios.defaults.headers.common.Authorization = `Bearer ${token}`;
	}

	return token;
};

const storeUser = (user) => {
	window.localStorage.setItem(userStorageKey, JSON.stringify(user));
	return user;
};

const clearSession = () => {
	setStoredToken('');
	window.localStorage.removeItem(userStorageKey);
};

const getRoleRoute = (role) => roleRoutes[role] || '/login';

const updateCurrentUserLabels = (user) => {
	document.querySelectorAll('[data-current-user]').forEach((element) => {
		element.textContent = user.name || user.email || 'Authenticated user';
	});

	document.querySelectorAll('[data-current-role]').forEach((element) => {
		element.textContent = user.role ? `${user.role[0].toUpperCase()}${user.role.slice(1)}` : 'User';
	});
	};

const fetchCurrentUser = async () => {
	const { data } = await window.axios.get('/api/me');
	storeUser(data);
	updateCurrentUserLabels(data);
	return data;
};

const renderFeedback = (container, message, status) => {
	if (!container) {
		return;
	}

	container.textContent = message;
	container.classList.remove('is-error', 'is-success', 'is-visible');

	if (!message) {
		return;
	}

	container.classList.add('is-visible', status === 'error' ? 'is-error' : 'is-success');
};

const redirectToRoleHome = (role) => {
	window.location.assign(getRoleRoute(role));
};

const renderItemStack = (orderItems = []) => {
	if (orderItems.length === 0) {
		return '<span class="dashboard-empty">No items</span>';
	}

	return `<div class="item-stack">${orderItems
		.map((item) => {
			const menuName = item.menu_item?.name || item.menuItem?.name || 'Menu item';
			return `<span class="item-pill">${escapeHtml(menuName)} x ${item.quantity} <strong>${formatCurrency(item.price)}</strong></span>`;
		})
		.join('')}</div>`;
};

const clearFieldState = (form) => {
	form.querySelectorAll('.auth-input').forEach((input) => input.classList.remove('is-invalid'));
	form.querySelectorAll('.auth-error-text').forEach((errorText) => {
		errorText.textContent = '';
		errorText.classList.remove('is-visible');
	});
};

const setFieldError = (form, fieldName, message) => {
	const input = form.querySelector(`[name="${fieldName}"]`);
	const errorText = form.querySelector(`[data-error-for="${fieldName}"]`);

	if (input) {
		input.classList.add('is-invalid');
	}

	if (errorText) {
		errorText.textContent = message;
		errorText.classList.add('is-visible');
	}
};

document.querySelectorAll('[data-auth-form]').forEach((form) => {
	const submitButton = form.querySelector('button[type="submit"]');
	const feedback = form.querySelector('[data-auth-feedback]');
	const tokenBox = form.querySelector('[data-auth-token]');

	form.addEventListener('submit', async (event) => {
		event.preventDefault();

		clearFieldState(form);
		renderFeedback(feedback, '', 'success');

		if (tokenBox) {
			tokenBox.classList.remove('is-visible');
			tokenBox.textContent = '';
		}

		if (submitButton) {
			submitButton.disabled = true;
			submitButton.dataset.originalLabel = submitButton.dataset.originalLabel || submitButton.textContent;
			submitButton.textContent = 'Serving...';
		}

		try {
			const payload = Object.fromEntries(new FormData(form).entries());
			const { data } = await window.axios.post(form.dataset.endpoint, payload);

			if (data?.token && tokenBox) {
				tokenBox.innerHTML = `<strong>JWT token</strong>${data.token}`;
				tokenBox.classList.add('is-visible');
				setStoredToken(data.token);
			}

			const user = await fetchCurrentUser();
			const successCopy = form.dataset.successCopy || 'Request completed successfully.';
			renderFeedback(feedback, `${successCopy} Redirecting to ${user.role} workspace...`, 'success');

			if (form.dataset.authForm === 'register') {
				form.reset();
			}

			window.setTimeout(() => redirectToRoleHome(user.role), 500);
		} catch (error) {
			const response = error.response;
			const errors = response?.data?.errors;

			if (errors && typeof errors === 'object') {
				Object.entries(errors).forEach(([field, messages]) => {
					setFieldError(form, field, messages[0]);
				});

				renderFeedback(feedback, 'Please review the highlighted fields and try again.', 'error');
			} else {
				const message = response?.data?.error || response?.data?.message || 'Unable to complete the request.';
				renderFeedback(feedback, message, 'error');
			}
		} finally {
			if (submitButton) {
				submitButton.disabled = false;
				submitButton.textContent = submitButton.dataset.originalLabel || 'Submit';
			}
		}
	});
});

document.querySelectorAll('[data-logout]').forEach((button) => {
	button.addEventListener('click', async () => {
		try {
			if (window.localStorage.getItem(tokenStorageKey)) {
				await window.axios.post('/api/logout');
			}
		} catch (error) {
			// Ignore logout API failures and clear the local session regardless.
		} finally {
			clearSession();
			window.location.assign('/login');
		}
	});
});

const initPublicAuthPage = async () => {
	if (!document.querySelector('[data-public-auth]') || !bootstrapStoredToken()) {
		return;
	}

	try {
		const user = await fetchCurrentUser();
		redirectToRoleHome(user.role);
	} catch (error) {
		clearSession();
	}
};

const initAdminMenuPage = async (page) => {
	const form = page.querySelector('[data-menu-form]');
	const feedback = page.querySelector('[data-admin-feedback]');
	const menuList = page.querySelector('[data-menu-list]');
	const ordersList = page.querySelector('[data-admin-orders]');
	const submitButton = page.querySelector('[data-menu-submit]');
	const resetButton = page.querySelector('[data-menu-reset]');
	let menuItems = [];

	const resetForm = () => {
		form.reset();
		form.elements.menu_item_id.value = '';
		submitButton.textContent = 'Create menu item';
		renderFeedback(feedback, '', 'success');
	};

	const loadMenuItems = async () => {
		const { data } = await window.axios.get('/api/menu-items');
		menuItems = data;

		menuList.innerHTML = data.length
			? data
					.map(
						(item) => `
							<tr>
								<td>
									<strong>${escapeHtml(item.name)}</strong>
									<div class="auth-note">${escapeHtml(item.description || 'No description')}</div>
								</td>
								<td>${formatCurrency(item.price)}</td>
								<td><span class="availability-badge" data-availability="${item.availability}">${escapeHtml(item.availability)}</span></td>
								<td>
									<div class="table-actions">
										<button type="button" class="table-action" data-menu-edit="${item.id}">Edit</button>
										<button type="button" class="table-danger" data-menu-delete="${item.id}">Delete</button>
									</div>
								</td>
							</tr>`,
					)
					.join('')
			: '<tr><td colspan="4" class="dashboard-empty">No menu items yet.</td></tr>';
	};

	const loadOrders = async () => {
		const { data } = await window.axios.get('/api/orders');
		ordersList.innerHTML = data.length
			? data
					.map(
						(order) => `
							<tr>
								<td>#${order.id}</td>
								<td>${escapeHtml(order.user?.name || `User ${order.user_id}`)}<div class="auth-note">User ID: ${order.user_id}</div></td>
								<td><span class="status-badge" data-status="${order.status}">${escapeHtml(order.status)}</span></td>
								<td>${formatCurrency(order.total_price)}</td>
								<td>${formatDate(order.created_at)}</td>
								<td>${renderItemStack(order.order_items || order.orderItems || [])}</td>
							</tr>`,
					)
					.join('')
			: '<tr><td colspan="6" class="dashboard-empty">No orders available.</td></tr>';
	};

	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		submitButton.disabled = true;

		try {
			const formData = new FormData(form);
			const id = formData.get('menu_item_id');
			const payload = {
				name: formData.get('name'),
				description: formData.get('description'),
				price: Number(formData.get('price')),
				availability: formData.get('availability'),
			};

			if (id) {
				await window.axios.put(`/api/menu-items/${id}`, payload);
				renderFeedback(feedback, 'Menu item updated successfully.', 'success');
			} else {
				await window.axios.post('/api/menu-items', payload);
				renderFeedback(feedback, 'Menu item created successfully.', 'success');
			}

			resetForm();
			await Promise.all([loadMenuItems(), loadOrders()]);
		} catch (error) {
			const message = error.response?.data?.message || error.response?.data?.error || 'Unable to save the menu item.';
			renderFeedback(feedback, message, 'error');
		} finally {
			submitButton.disabled = false;
		}
	});

	resetButton.addEventListener('click', resetForm);

	page.addEventListener('click', async (event) => {
		const editButton = event.target.closest('[data-menu-edit]');
		const deleteButton = event.target.closest('[data-menu-delete]');

		if (editButton) {
			const item = menuItems.find((entry) => String(entry.id) === editButton.dataset.menuEdit);
			if (!item) {
				return;
			}

			form.elements.menu_item_id.value = item.id;
			form.elements.name.value = item.name;
			form.elements.description.value = item.description || '';
			form.elements.price.value = item.price;
			form.elements.availability.value = item.availability;
			submitButton.textContent = 'Update menu item';
			window.scrollTo({ top: 0, behavior: 'smooth' });
		}

		if (deleteButton) {
			const id = deleteButton.dataset.menuDelete;
			if (!window.confirm('Delete this menu item?')) {
				return;
			}

			try {
				await window.axios.delete(`/api/menu-items/${id}`);
				renderFeedback(feedback, 'Menu item deleted successfully.', 'success');
				await loadMenuItems();
			} catch (error) {
				renderFeedback(feedback, 'Unable to delete the selected menu item.', 'error');
			}
		}
	});

	await Promise.all([loadMenuItems(), loadOrders()]);
};

const initStaffOrdersPage = async (page) => {
	const ordersList = page.querySelector('[data-staff-orders]');
	const feedback = page.querySelector('[data-staff-feedback]');

	const loadOrders = async () => {
		const { data } = await window.axios.get('/api/orders');
		ordersList.innerHTML = data.length
			? data
					.map(
						(order) => `
							<tr>
								<td>#${order.id}</td>
								<td>${order.user_id}</td>
								<td>
									<select class="auth-input" data-order-status>
										<option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
										<option value="preparing" ${order.status === 'preparing' ? 'selected' : ''}>Preparing</option>
										<option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
									</select>
								</td>
								<td>${formatCurrency(order.total_price)}</td>
								<td>${formatDate(order.created_at)}</td>
								<td>${renderItemStack(order.order_items || order.orderItems || [])}</td>
								<td><button type="button" class="table-action" data-order-update="${order.id}">Save</button></td>
							</tr>`,
					)
					.join('')
			: '<tr><td colspan="7" class="dashboard-empty">No orders available.</td></tr>';
	};

	page.addEventListener('click', async (event) => {
		const updateButton = event.target.closest('[data-order-update]');
		if (!updateButton) {
			return;
		}

		const row = updateButton.closest('tr');
		const status = row.querySelector('[data-order-status]').value;

		try {
			await window.axios.put(`/api/orders/${updateButton.dataset.orderUpdate}/status`, { status });
			renderFeedback(feedback, 'Order status updated.', 'success');
			await loadOrders();
		} catch (error) {
			renderFeedback(feedback, 'Unable to update order status.', 'error');
		}
	});

	await loadOrders();
};

const initCustomerOrdersPage = async (page) => {
	const menuContainer = page.querySelector('[data-customer-menu]');
	const cartContainer = page.querySelector('[data-cart-items]');
	const cartTotal = page.querySelector('[data-cart-total]');
	const placeOrderButton = page.querySelector('[data-place-order]');
	const feedback = page.querySelector('[data-customer-feedback]');
	const ordersList = page.querySelector('[data-customer-orders]');
	let menuItems = [];
	const cart = new Map();

	const renderCart = () => {
		const cartEntries = Array.from(cart.values());
		const total = cartEntries.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0);
		cartTotal.textContent = formatCurrency(total);

		cartContainer.innerHTML = cartEntries.length
			? cartEntries
					.map(
						(item) => `
							<div class="cart-row">
								<div>
									<strong>${escapeHtml(item.name)}</strong>
									<div class="auth-note">${formatCurrency(item.price)} each</div>
								</div>
								<div>Qty: ${item.quantity}</div>
								<button type="button" class="table-danger" data-cart-remove="${item.id}">Remove</button>
							</div>`,
					)
					.join('')
			: '<div class="dashboard-empty">No items added yet.</div>';
	};

	const loadMenuItems = async () => {
		const { data } = await window.axios.get('/api/menu-items');
		menuItems = data.filter((item) => item.availability === 'available');
		menuContainer.innerHTML = menuItems.length
			? menuItems
					.map(
						(item) => `
							<article class="menu-product">
								<div>
									<h3>${escapeHtml(item.name)}</h3>
									<p>${escapeHtml(item.description || 'No description')}</p>
								</div>
								<div class="menu-product-meta">
									<span class="menu-product-price">${formatCurrency(item.price)}</span>
									<span class="availability-badge" data-availability="${item.availability}">${escapeHtml(item.availability)}</span>
								</div>
								<div class="menu-product-actions">
									<input class="auth-input" type="number" min="1" value="1" data-menu-quantity="${item.id}">
									<button type="button" class="auth-button" data-menu-add="${item.id}">Add to order</button>
								</div>
							</article>`,
					)
					.join('')
			: '<div class="dashboard-empty">No available menu items right now.</div>';
	};

	const loadOrders = async () => {
		const { data } = await window.axios.get('/api/orders');
		ordersList.innerHTML = data.length
			? data
					.map(
						(order) => `
							<tr>
								<td>#${order.id}</td>
								<td><span class="status-badge" data-status="${order.status}">${escapeHtml(order.status)}</span></td>
								<td>${formatCurrency(order.total_price)}</td>
								<td>${formatDate(order.created_at)}</td>
								<td>${renderItemStack(order.order_items || order.orderItems || [])}</td>
							</tr>`,
					)
					.join('')
			: '<tr><td colspan="5" class="dashboard-empty">You have not placed any orders yet.</td></tr>';
	};

	page.addEventListener('click', (event) => {
		const addButton = event.target.closest('[data-menu-add]');
		const removeButton = event.target.closest('[data-cart-remove]');

		if (addButton) {
			const item = menuItems.find((entry) => String(entry.id) === addButton.dataset.menuAdd);
			const quantityInput = page.querySelector(`[data-menu-quantity="${addButton.dataset.menuAdd}"]`);
			const quantity = Number(quantityInput?.value || 1);

			if (!item || quantity < 1) {
				return;
			}

			const existing = cart.get(item.id);
			cart.set(item.id, {
				id: item.id,
				name: item.name,
				price: Number(item.price),
				quantity: (existing?.quantity || 0) + quantity,
			});
			renderCart();
			renderFeedback(feedback, `${item.name} added to your basket.`, 'success');
		}

		if (removeButton) {
			cart.delete(Number(removeButton.dataset.cartRemove));
			renderCart();
		}
	});

	placeOrderButton.addEventListener('click', async () => {
		const items = Array.from(cart.values()).map((item) => ({
			menu_item_id: item.id,
			quantity: item.quantity,
		}));

		if (items.length === 0) {
			renderFeedback(feedback, 'Add at least one menu item before placing an order.', 'error');
			return;
		}

		placeOrderButton.disabled = true;

		try {
			await window.axios.post('/api/orders', { items });
			cart.clear();
			renderCart();
			renderFeedback(feedback, 'Order placed successfully.', 'success');
			await loadOrders();
		} catch (error) {
			const message = error.response?.data?.message || error.response?.data?.error || 'Unable to place order.';
			renderFeedback(feedback, message, 'error');
		} finally {
			placeOrderButton.disabled = false;
		}
	});

	renderCart();
	await Promise.all([loadMenuItems(), loadOrders()]);
};

const initRolePage = async () => {
	const page = document.querySelector('[data-role-page]');
	if (!page) {
		return;
	}

	if (!bootstrapStoredToken()) {
		window.location.replace('/login');
		return;
	}

	try {
		const user = await fetchCurrentUser();
		if (page.dataset.expectedRole && user.role !== page.dataset.expectedRole) {
			redirectToRoleHome(user.role);
			return;
		}

		const initializers = {
			'admin-menu': initAdminMenuPage,
			'staff-orders': initStaffOrdersPage,
			'customer-orders': initCustomerOrdersPage,
		};

		await initializers[page.dataset.page]?.(page, user);
	} catch (error) {
		clearSession();
		window.location.replace('/login');
	}
};

bootstrapStoredToken();
initPublicAuthPage();
initRolePage();
