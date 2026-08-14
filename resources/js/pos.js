const app = document.getElementById('pos-app');

if (app) {
    const cartEl = document.getElementById('pos-cart');
    const heldEl = document.getElementById('pos-held-container');
    const productsEl = document.getElementById('pos-products');
    const flashEl = document.getElementById('pos-flash');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const currency = window.currencySymbol ?? '';

    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
    };

    let searchTimer = null;

    function fmt(value) {
        return currency + Number(value).toLocaleString('en-NG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function showFlash(message, success) {
        flashEl.innerHTML = '';
        if (!message) return;
        const div = document.createElement('div');
        div.className = 'mb-4 rounded-lg border px-4 py-3 text-sm ' +
            (success
                ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                : 'bg-rose-50 border-rose-200 text-rose-800');
        div.textContent = message;
        flashEl.appendChild(div);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function api(url, options = {}) {
        const res = await fetch(url, options);
        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
    }

    function post(url, body) {
        const form = new FormData();
        Object.entries(body).forEach(([k, v]) => form.append(k, v));
        return api(url, { method: 'POST', headers, body: form });
    }

    function updateCartFromResponse({ data }) {
        if (data.cart_html) cartEl.innerHTML = data.cart_html;
        if (data.held_html) heldEl.innerHTML = data.held_html;
    }

    function updateTotals() {
        const totals = document.getElementById('pos-totals');
        if (!totals) return;

        const subtotal = parseFloat(totals.dataset.subtotal || '0');
        const taxRate = parseFloat(totals.dataset.taxRate || '0');
        const discountInput = document.getElementById('pos-discount');
        const discount = Math.min(parseFloat(discountInput?.value || '0') || 0, subtotal);

        const tax = (subtotal - discount) * (taxRate / 100);
        const total = subtotal - discount + tax;

        const setText = (sel, value) => {
            const el = totals.querySelector(sel);
            if (el) el.textContent = fmt(value);
        };

        setText('[data-total-subtotal]', subtotal);
        setText('[data-total-discount]', discount);
        setText('[data-total-tax]', tax);
        setText('[data-total-grand]', total);
    }

    async function handleCartAction(url, body, successMsg) {
        const { ok, data } = await post(url, body);
        updateCartFromResponse({ data });
        if (ok) {
            showFlash(data.message || successMsg, true);
        } else {
            showFlash(data.message || 'Something went wrong.', false);
        }
        updateTotals();
    }

    document.addEventListener('click', (e) => {
        const add = e.target.closest('[data-add-product]');
        if (add) {
            e.preventDefault();
            handleCartAction('/pos/add', { product_id: add.dataset.addProduct });
            return;
        }

        const remove = e.target.closest('[data-remove-item]');
        if (remove) {
            e.preventDefault();
            handleCartAction('/pos/remove', { product_id: remove.dataset.removeItem });
            return;
        }

        const clear = e.target.closest('[data-clear-cart]');
        if (clear) {
            e.preventDefault();
            handleCartAction('/pos/clear', {});
            return;
        }

        const hold = e.target.closest('[data-hold-sale]');
        if (hold) {
            e.preventDefault();
            handleCartAction('/pos/hold', {});
            return;
        }

        const resume = e.target.closest('[data-resume-hold]');
        if (resume) {
            e.preventDefault();
            handleCartAction('/pos/resume/' + resume.dataset.resumeHold, {});
            return;
        }

        const discard = e.target.closest('[data-discard-hold]');
        if (discard) {
            e.preventDefault();
            if (confirm('Discard this held sale?')) {
                handleCartAction('/pos/discard/' + discard.dataset.discardHold, {});
            }
            return;
        }

        const decrement = e.target.closest('[data-qty-decrement]');
        if (decrement) {
            e.preventDefault();
            const id = decrement.dataset.qtyDecrement;
            const input = document.querySelector(`[data-qty-input="${id}"]`);
            if (input) {
                const next = parseFloat(input.value) - 1;
                if (next <= 0) {
                    handleCartAction('/pos/remove', { product_id: id });
                } else {
                    handleCartAction('/pos/update-qty', { product_id: id, qty: next });
                }
            }
            return;
        }

        const increment = e.target.closest('[data-qty-increment]');
        if (increment) {
            e.preventDefault();
            const id = increment.dataset.qtyIncrement;
            const input = document.querySelector(`[data-qty-input="${id}"]`);
            if (input) {
                handleCartAction('/pos/update-qty', { product_id: id, qty: parseFloat(input.value) + 1 });
            }
            return;
        }
    });

    document.addEventListener('change', (e) => {
        const qtyInput = e.target.closest('[data-qty-input]');
        if (qtyInput) {
            const id = qtyInput.dataset.qtyInput;
            const qty = parseFloat(qtyInput.value);
            if (isNaN(qty) || qty <= 0) {
                handleCartAction('/pos/remove', { product_id: id });
            } else {
                handleCartAction('/pos/update-qty', { product_id: id, qty });
            }
        }
    });

    document.addEventListener('input', (e) => {
        if (e.target.closest('#pos-discount')) {
            updateTotals();
        }
    });

    const searchInput = document.getElementById('pos-search');
    const categorySelect = document.getElementById('pos-category');

    async function runSearch() {
        const search = searchInput.value;
        const category = categorySelect.value;
        const params = new URLSearchParams({ search, category_id: category });
        const { ok, data } = await api('/pos/search?' + params.toString(), { headers, method: 'GET' });
        if (ok && data.products_html) {
            productsEl.innerHTML = data.products_html;
        }
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 250);
    });

    categorySelect?.addEventListener('change', runSearch);

    const checkoutForm = document.getElementById('pos-checkout-form');
    checkoutForm?.addEventListener('submit', () => {
        const btn = checkoutForm.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Processing...';
        }
    });

    updateTotals();
}
