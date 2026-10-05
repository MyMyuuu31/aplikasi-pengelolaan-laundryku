/* ============================================================
   LaundryKu - Interactivity Scripts (vanilla JS, tanpa build step)
   ============================================================ */
(function () {
    'use strict';

    const root = document.documentElement;
    root.setAttribute('data-theme', 'light');
    localStorage.removeItem('laundryku-theme');

    /* ---------- Sidebar Collapse ---------- */
    function initSidebar() {
        const sidebar = document.getElementById('appSidebar');
        const btn = document.getElementById('sidebarToggle');
        if (!sidebar || !btn) return;

        const KEY = 'laundryku-sidebar';
        const isMobile = () => window.innerWidth < 992;

        if (localStorage.getItem(KEY) === 'collapsed' && !isMobile()) {
            sidebar.classList.add('collapsed');
        }

        btn.addEventListener('click', () => {
            if (isMobile()) {
                sidebar.classList.toggle('open');
                document.body.classList.toggle('overflow-lg-hidden', sidebar.classList.contains('open'));
            } else {
                sidebar.classList.toggle('collapsed');
                localStorage.setItem(KEY, sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
            }
        });

        // Tutup sidebar mobile saat backdrop diklik
        const backdrop = document.getElementById('sidebarBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', () => {
                sidebar.classList.remove('open');
                document.body.classList.remove('overflow-lg-hidden');
            });
        }
    }

    /* ---------- Auto-hide flash toast ---------- */
    function initToasts() {
        document.querySelectorAll('[data-auto-dismiss]').forEach((el) => {
            const delay = parseInt(el.dataset.autoDismiss || '5000', 10);
            setTimeout(() => {
                el.style.transition = 'opacity .4s ease, transform .4s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateX(120%)';
                setTimeout(() => el.remove(), 420);
            }, delay);
        });
    }

    /* ---------- Konfirmasi hapus ---------- */
    function initConfirm() {
        document.querySelectorAll('[data-confirm]').forEach((el) => {
            el.addEventListener('click', (e) => {
                if (!window.confirm(el.dataset.confirm)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    }

    /* ---------- Filter otomatis submit ---------- */
    function initAutoSubmit() {
        document.querySelectorAll('[data-auto-submit]').forEach((el) => {
            el.addEventListener('change', () => el.form && el.form.submit());
        });
    }

    /* ---------- Format Rupiah ---------- */
    function rupiah(n) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(n) || 0);
    }

    window.LaundryKu = { rupiah };

    /* ---------- Modal helper (Bootstrap) ---------- */
    function initModalTrigger() {
        document.querySelectorAll('[data-modal-target]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const el = document.getElementById(btn.dataset.modalTarget);
                if (el && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        });
    }

    /* ---------- Animated counter ---------- */
    function initCounter() {
        document.querySelectorAll('[data-counter]').forEach((el) => {
            const target = parseFloat(el.dataset.counter.replace(/\./g, '')) || 0;
            const suffix = el.dataset.counterSuffix || '';
            let current = 0;
            const step = target / 40;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = rupiah(current) + suffix;
            }, 18);
        });
    }

    /* ---------- Live search table ---------- */
    function initTableSearch() {
        document.querySelectorAll('[data-table-search]').forEach((input) => {
            const table = document.querySelector(input.dataset.tableSearch);
            if (!table) return;
            input.addEventListener('input', () => {
                const key = input.value.toLowerCase();
                table.querySelectorAll('tbody tr').forEach((tr) => {
                    tr.style.display = tr.innerText.toLowerCase().includes(key) ? '' : 'none';
                });
            });
        });
    }

    /* ---------- Init ---------- */
    document.addEventListener('DOMContentLoaded', () => {
        initSidebar();
        initToasts();
        initConfirm();
        initAutoSubmit();
        initModalTrigger();
        initCounter();
        initTableSearch();
    });
})();