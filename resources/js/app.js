import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const post = (url, body) =>
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(body),
    }).then(async (r) => ({ ok: r.ok, status: r.status, data: await r.json().catch(() => ({})) }));

const readCompare = () => {
    try {
        return JSON.parse(localStorage.getItem('compare') || '[]');
    } catch {
        return [];
    }
};

// Trạng thái dùng chung toàn trang: giỏ hàng, thông báo, so sánh, yêu thích
Alpine.store('shop', {
    cart: Number(document.body.dataset.cart || 0),
    toast: document.body.dataset.flash || '',
    timer: null,
    compare: readCompare(),

    init() {
        if (this.toast) this.timer = setTimeout(() => (this.toast = ''), 4000);
    },
    say(message) {
        this.toast = message;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => (this.toast = ''), 2800);
    },
    async add(productId, quantity = 1) {
        const { ok, data } = await post('/gio-hang/them', { product_id: productId, quantity });
        if (ok) this.cart = data.count;
        this.say(data.message || 'Không thêm được vào giỏ. Vui lòng thử lại.');
    },
    toggleCompare(item) {
        const i = this.compare.findIndex((c) => c.id === item.id);
        if (i > -1) this.compare.splice(i, 1);
        else if (this.compare.length >= 3) return this.say('So sánh tối đa 3 sản phẩm');
        else this.compare.push(item);
        try {
            localStorage.setItem('compare', JSON.stringify(this.compare));
        } catch {}
    },
    inCompare(id) {
        return this.compare.some((c) => c.id === id);
    },
    async wish(productId, el) {
        const { status, data } = await post('/yeu-thich', { product_id: productId });
        if (status === 401) return this.say('Đăng nhập để lưu sản phẩm yêu thích');
        el?.classList.toggle('text-brand-600', data.saved);
        this.say(data.message);
    },
});

Alpine.data('shell', () => ({ mega: false, megaI: 0, drawer: false, drawerI: 0, searchOpen: false }));

Alpine.data('slider', (count) => ({
    i: 0,
    pause: false,
    init() {
        if (count > 1) setInterval(() => !this.pause && this.next(), 5000);
    },
    next() {
        this.i = (this.i + 1) % count;
    },
    prev() {
        this.i = (this.i - 1 + count) % count;
    },
}));

Alpine.data('row', () => ({
    scroll(direction) {
        const el = this.$refs.row;
        el.scrollBy({ left: direction * el.clientWidth * 0.85, behavior: 'smooth' });
    },
}));

// Nút thêm giỏ, so sánh, yêu thích trên thẻ sản phẩm
document.addEventListener('click', (e) => {
    const store = Alpine.store('shop');
    const add = e.target.closest('[data-add]');
    if (add) {
        e.preventDefault();
        return store.add(Number(add.dataset.add));
    }
    const cmp = e.target.closest('[data-compare]');
    if (cmp) {
        e.preventDefault();
        return store.toggleCompare(JSON.parse(cmp.dataset.compare));
    }
    const wish = e.target.closest('[data-wish]');
    if (wish) {
        e.preventDefault();
        store.wish(Number(wish.dataset.wish), wish);
    }
});

// Đồng hồ đếm ngược: mọi phần tử có data-countdown="<thời điểm kết thúc ISO>"
const pad = (n) => String(n).padStart(2, '0');
function tick() {
    document.querySelectorAll('[data-countdown]').forEach((el) => {
        const left = Math.max(0, Math.floor((new Date(el.dataset.countdown) - Date.now()) / 1000));
        const days = Math.floor(left / 86400);
        const parts = [pad(Math.floor((left % 86400) / 3600)), pad(Math.floor((left % 3600) / 60)), pad(left % 60)];
        const boxes = el.querySelectorAll('[data-part]');
        if (boxes.length) {
            boxes.forEach((b, i) => (b.textContent = parts[i]));
            const day = el.querySelector('[data-day]');
            if (day) {
                day.textContent = days;
                day.parentElement.hidden = days === 0;
            }
        } else {
            el.textContent = (days ? days + ' ngày ' : '') + parts.join(':');
        }
    });
}
tick();
setInterval(tick, 1000);

window.Alpine = Alpine;
Alpine.start();
