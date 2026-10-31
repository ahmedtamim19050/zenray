document.documentElement.classList.add('js');
window.zenrayReady = true;

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

// Split headings into words so they can rise in one by one
document.querySelectorAll('[data-split]').forEach((heading) => {
    let index = 0;
    const wrap = (node) => {
        [...node.childNodes].forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE) {
                const fragment = document.createDocumentFragment();
                child.textContent.split(/(\s+)/).forEach((part) => {
                    if (!part) return;
                    if (/^\s+$/.test(part)) {
                        fragment.append(part);
                        return;
                    }
                    const outer = document.createElement('span');
                    const inner = document.createElement('span');
                    outer.className = 'split-word';
                    inner.style.setProperty('--w', index++);
                    inner.textContent = part;
                    outer.append(inner);
                    fragment.append(outer);
                });
                child.replaceWith(fragment);
            } else if (child.nodeType === Node.ELEMENT_NODE) {
                wrap(child);
            }
        });
    };
    heading.setAttribute('aria-label', heading.textContent.replace(/\s+/g, ' ').trim());
    wrap(heading);
    [...heading.children].forEach((el) => el.setAttribute('aria-hidden', 'true'));
});

// Give staggered children their order index
document.querySelectorAll('[data-stagger]').forEach((group) => {
    [...group.children].forEach((child, i) => child.style.setProperty('--i', i));
});

// Floating header gains depth after scrolling
const siteHeader = document.querySelector('[data-site-header]');
if (siteHeader) {
    const onScroll = () => siteHeader.classList.toggle('is-scrolled', window.scrollY > 24);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

// Cursor spotlight + 3D tilt (fine pointers only, never with reduced motion)
if (finePointer && !reduceMotion) {
    document.querySelectorAll('[data-spotlight]').forEach((el) => {
        el.addEventListener('pointermove', (event) => {
            const rect = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${event.clientX - rect.left}px`);
            el.style.setProperty('--my', `${event.clientY - rect.top}px`);
        });
    });

    document.querySelectorAll('[data-tilt]').forEach((el) => {
        const max = Number(el.dataset.tilt) || 6;
        let frame = 0;

        el.addEventListener('pointermove', (event) => {
            cancelAnimationFrame(frame);
            frame = requestAnimationFrame(() => {
                const rect = el.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width - 0.5;
                const y = (event.clientY - rect.top) / rect.height - 0.5;
                el.classList.add('is-tilting');
                el.style.setProperty('--ry', `${(x * max).toFixed(2)}deg`);
                el.style.setProperty('--rx', `${(-y * max).toFixed(2)}deg`);
            });
        });

        el.addEventListener('pointerleave', () => {
            cancelAnimationFrame(frame);
            el.classList.remove('is-tilting');
            el.style.setProperty('--rx', '0deg');
            el.style.setProperty('--ry', '0deg');
        });
    });
}

// X-ray lens: on fine pointers, reveal the other layer of an <x-xray> frame around the cursor
if (finePointer) {
    document.querySelectorAll('[data-xray-lens]').forEach((el) => {
        el.addEventListener('pointermove', (event) => {
            const rect = el.getBoundingClientRect();
            el.style.setProperty('--lx', `${event.clientX - rect.left}px`);
            el.style.setProperty('--ly', `${event.clientY - rect.top}px`);
            el.classList.add('is-lensing');
        });
        el.addEventListener('pointerleave', () => el.classList.remove('is-lensing'));
    });
}

// Mobile navigation
const toggle = document.querySelector('[data-nav-toggle]');
const menu = document.getElementById('mobile-menu');

if (toggle && menu) {
    const isOpen = () => toggle.getAttribute('aria-expanded') === 'true';

    const setOpen = (open, { returnFocus = false } = {}) => {
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('[data-nav-label]').textContent = open ? 'Close menu' : 'Open menu';
        menu.hidden = !open;
        if (open) menu.style.height = `${window.innerHeight - toggle.closest("header").getBoundingClientRect().bottom}px`;
        document.body.classList.toggle('overflow-hidden', open);

        if (open) menu.querySelector('a')?.focus();
        else if (returnFocus) toggle.focus();
    };

    toggle.addEventListener('click', () => setOpen(!isOpen()));

    // Close after choosing a link
    menu.addEventListener('click', (event) => {
        if (event.target.closest('a')) setOpen(false);
    });

    document.addEventListener('keydown', (event) => {
        if (!isOpen()) return;

        if (event.key === 'Escape') {
            setOpen(false, { returnFocus: true });
            return;
        }

        // Keep Tab focus within the toggle + open menu
        if (event.key === 'Tab') {
            const focusable = [toggle, ...menu.querySelectorAll('a, button')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches && isOpen()) setOpen(false);
    });
}

// "What's inside an X-ray room" explorer: markers and list items highlight each other
document.querySelectorAll('[data-explorer]').forEach((explorer) => {
    const controls = explorer.querySelectorAll('[data-explorer-target]');

    const select = (index) => {
        controls.forEach((control) => {
            control.setAttribute('aria-pressed', String(control.dataset.explorerTarget === index));
        });
    };

    controls.forEach((control) => {
        control.addEventListener('click', () => {
            const index = control.dataset.explorerTarget;
            const alreadySelected = control.getAttribute('aria-pressed') === 'true';
            select(alreadySelected ? null : index);
        });
    });
});

// Reveal animations (fade-and-rise, staggered groups, split headings) play as
// elements scroll into view. Skipped entirely under reduced motion.
const revealables = document.querySelectorAll('[data-reveal], [data-stagger], [data-split]');

if ('IntersectionObserver' in window && !reduceMotion) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px' },
    );

    revealables.forEach((el) => observer.observe(el));
} else {
    revealables.forEach((el) => el.classList.add('is-visible'));
}

// Featured equipment filter. Category links ([data-filter-link]) and ?category= also drive it.
const filterGroup = document.querySelector('[data-filter-group]');
const filterGrid = document.querySelector('[data-filter-grid]');

if (filterGroup && filterGrid) {
    const buttons = filterGroup.querySelectorAll('[data-filter]');
    const items = filterGrid.querySelectorAll('[data-filter-item]');
    const status = document.querySelector('[data-filter-status]');

    const applyFilter = (slug) => {
        if (![...buttons].some((b) => b.dataset.filter === slug)) slug = 'all';

        buttons.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.filter === slug)));

        let shown = 0;
        items.forEach((item) => {
            const match = slug === 'all' || item.dataset.filterItem === slug;
            item.hidden = !match;
            if (match) shown++;
        });

        if (status) status.textContent = `Showing ${shown} ${shown === 1 ? 'item' : 'items'}`;
    };

    buttons.forEach((b) => b.addEventListener('click', () => applyFilter(b.dataset.filter)));

    document.querySelectorAll('[data-filter-link]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            applyFilter(link.dataset.filterLink);
            document.getElementById('featured-equipment').scrollIntoView({ behavior: 'smooth' });
            history.replaceState(null, '', '#featured-equipment');
        });
    });

    const initial = new URLSearchParams(location.search).get('category');
    if (initial) applyFilter(initial);
}

// Product quick view
const quickView = document.querySelector('[data-quick-view-dialog]');

if (quickView && typeof quickView.showModal === 'function') {
    const set = (selector, apply) => apply(quickView.querySelector(selector));

    document.querySelectorAll('[data-quick-view]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const d = trigger.dataset;
            set('[data-qv-image]', (el) => Object.assign(el, { src: d.image, alt: d.alt }));
            set('[data-qv-name]', (el) => (el.textContent = d.name));
            set('[data-qv-category]', (el) => (el.textContent = d.category));
            set('[data-qv-summary]', (el) => (el.textContent = d.summary));
            set('[data-qv-quote]', (el) => (el.href = d.quote));
            set('[data-qv-example]', (el) => (el.hidden = d.example !== '1'));
            quickView.showModal();
        });
    });

    quickView.querySelector('[data-qv-close]').addEventListener('click', () => quickView.close());
    quickView.addEventListener('click', (event) => {
        if (event.target === quickView) quickView.close(); // backdrop click
    });
}

// Gallery lightbox
document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const dialog = gallery.parentElement.querySelector('[data-lightbox]');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    const links = [...gallery.querySelectorAll('[data-lightbox-item]')];
    const image = dialog.querySelector('[data-lightbox-image]');
    const caption = dialog.querySelector('[data-lightbox-caption]');
    const count = dialog.querySelector('[data-lightbox-count]');
    let current = 0;

    const show = (index) => {
        current = (index + links.length) % links.length;
        const link = links[current];
        image.src = link.href;
        image.alt = link.dataset.caption;
        caption.textContent = link.dataset.caption;
        count.textContent = `${current + 1} / ${links.length}`;
    };

    links.forEach((link, index) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            show(index);
            dialog.showModal();
        });
    });

    dialog.querySelector('[data-lightbox-prev]').addEventListener('click', () => show(current - 1));
    dialog.querySelector('[data-lightbox-next]').addEventListener('click', () => show(current + 1));
    const close = () => {
        dialog.close();
        links[current].focus();
    };

    dialog.querySelector('[data-lightbox-close]').addEventListener('click', close);
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) close();
    });
    dialog.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') show(current - 1);
        if (event.key === 'ArrowRight') show(current + 1);
    });
    dialog.addEventListener('close', () => links[current].focus());
});

// Enquiry form: prevent double submission and focus status messages after a reload
document.querySelectorAll('[data-enquiry-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        button.querySelector('[data-submit-label]').textContent = 'Sending…';
    });
});

document.querySelector('[data-autofocus]')?.focus({ preventScroll: true });

// Hero carousel. A JS timer drives autoplay (remaining time is kept while paused);
// the progress bars are a CSS animation kept in step via the is-paused / is-stopped
// classes. Hover or keyboard focus pauses; any manual control stops autoplay.
document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const texts = carousel.querySelectorAll('[data-slide-text]');
    const media = carousel.querySelectorAll('[data-slide-media]');
    const tabs = carousel.querySelectorAll('[data-carousel-tab]');
    const toggleButton = carousel.querySelector('[data-carousel-toggle]');
    const toggleLabel = carousel.querySelector('[data-carousel-toggle-label]');
    const duration = parseFloat(getComputedStyle(carousel).getPropertyValue('--carousel-duration')) * 1000 || 7000;

    let current = 0;
    let stopped = false;
    let paused = false;
    let remaining = duration;
    let startedAt = null;
    let timer;

    const halt = () => {
        clearTimeout(timer);
        if (startedAt !== null) remaining -= performance.now() - startedAt;
        startedAt = null;
    };

    const schedule = () => {
        clearTimeout(timer);
        if (stopped || paused) return;
        startedAt = performance.now();
        timer = setTimeout(() => {
            startedAt = null;
            go(current + 1);
            schedule();
        }, Math.max(remaining, 0));
    };

    const go = (index) => {
        current = (index + tabs.length) % tabs.length;
        remaining = duration;
        [texts, media].forEach((list) =>
            list.forEach((slide, i) => {
                const active = i === current;
                slide.toggleAttribute('data-active', active);
                slide.inert = !active;
            }),
        );
        tabs.forEach((tab, i) => tab.setAttribute('aria-current', String(i === current)));
    };

    const setStopped = (value) => {
        stopped = value;
        carousel.classList.toggle('is-stopped', value);
        toggleLabel.textContent = value ? 'Play slideshow' : 'Pause slideshow';
        if (value) {
            halt();
        } else {
            remaining = duration;
            schedule();
        }
    };

    const setPaused = (value) => {
        if (paused === value) return;
        paused = value;
        carousel.classList.toggle('is-paused', value);
        if (value) halt();
        else schedule();
    };

    const manual = (index) => {
        setStopped(true);
        go(index);
    };

    tabs.forEach((tab, i) => tab.addEventListener('click', () => manual(i)));
    carousel.querySelector('[data-carousel-prev]').addEventListener('click', () => manual(current - 1));
    carousel.querySelector('[data-carousel-next]').addEventListener('click', () => manual(current + 1));
    toggleButton.addEventListener('click', () => setStopped(!stopped));

    carousel.addEventListener('pointerenter', () => setPaused(true));
    carousel.addEventListener('pointerleave', () => setPaused(false));
    carousel.addEventListener('focusin', () => setPaused(true));
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) setPaused(false);
    });
    document.addEventListener('visibilitychange', () => setPaused(document.hidden));

    if (reduceMotion) setStopped(true);
    else schedule();
});
// Desktop mega menu / dropdowns: click or hover to open, Escape or outside click to close
const menus = [...document.querySelectorAll('[data-menu]')];

const closeMenus = (except = null) => {
    menus.forEach((menuItem) => {
        if (menuItem === except) return;
        menuItem.querySelector('[data-menu-button]').setAttribute('aria-expanded', 'false');
        menuItem.querySelector('[data-menu-panel]').hidden = true;
    });
};

menus.forEach((menuItem) => {
    const button = menuItem.querySelector('[data-menu-button]');
    const panel = menuItem.querySelector('[data-menu-panel]');
    let hoverTimer;

    const setOpen = (open) => {
        if (open) closeMenus(menuItem);
        button.setAttribute('aria-expanded', String(open));
        panel.hidden = !open;
    };

    button.addEventListener('click', () => setOpen(button.getAttribute('aria-expanded') !== 'true'));

    if (finePointer) {
        menuItem.addEventListener('pointerenter', () => {
            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(() => setOpen(true), 80);
        });
        menuItem.addEventListener('pointerleave', () => {
            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(() => setOpen(false), 180);
        });
    }

    panel.addEventListener('click', (event) => {
        if (event.target.closest('a')) setOpen(false);
    });

    menuItem.addEventListener('focusout', (event) => {
        if (!menuItem.contains(event.relatedTarget)) setOpen(false);
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const open = menus.find((m) => m.querySelector('[data-menu-button]').getAttribute('aria-expanded') === 'true');
    if (open) {
        closeMenus();
        open.querySelector('[data-menu-button]').focus();
    }
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-menu]')) closeMenus();
});

// Confirm before destructive form submissions (e.g. delete buttons)
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

// Admin image fields: preview an upload or a library photo before saving
document.querySelectorAll('[data-image-field]').forEach((field) => {
    const preview = field.querySelector('[data-image-preview]');
    const empty = field.querySelector('[data-image-empty]');
    const show = (src) => {
        if (!src || !preview) return;
        preview.removeAttribute('srcset');
        preview.src = src;
        preview.classList.remove('hidden');
        empty?.classList.add('hidden');
    };

    field.querySelector('input[type="file"]')?.addEventListener('change', (event) => {
        const [file] = event.target.files;
        if (file) show(URL.createObjectURL(file));
    });
    field.querySelector('[data-image-library]')?.addEventListener('change', (event) => {
        show(event.target.selectedOptions[0]?.dataset.src);
    });
});

// Markdown toolbar: wrap the selection (or insert at the cursor)
document.querySelectorAll('[data-markdown-editor]').forEach((editor) => {
    const textarea = editor.querySelector('textarea');

    editor.querySelectorAll('[data-md-before]').forEach((button) => {
        button.addEventListener('click', () => {
            const { mdBefore: before, mdAfter: after } = button.dataset;
            const { selectionStart: start, selectionEnd: end, value } = textarea;
            const selected = value.slice(start, end) || 'text';
            // Line-level formats (headings, lists, quotes) start on a new line.
            const prefix = !after && start > 0 && value[start - 1] !== '\n' ? '\n' : '';

            textarea.setRangeText(prefix + before + selected + after, start, end, 'end');
            textarea.setSelectionRange(start + prefix.length + before.length, start + prefix.length + before.length + selected.length);
            textarea.focus();
        });
    });
});

// Quote / invoice editor: customer autofill, inventory picker, rows and live totals
document.querySelectorAll('[data-doc-editor]').forEach((form) => {
    const inventory = new Map(JSON.parse(form.dataset.inventory || '[]').map((item) => [String(item.id), item]));
    const rows = form.querySelector('[data-rows]');
    const template = form.querySelector('[data-row-template]');
    const symbol = form.dataset.currencySymbol || '';
    // en-IN gives lakh/crore grouping (1,15,00,000.00) for BDT and similar currencies.
    const locale = form.dataset.numberLocale || 'en';
    const money = (value) => (value < 0 ? '-' : '') + symbol + Math.abs(value).toLocaleString(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const round = (value) => Math.round((value + Number.EPSILON) * 100) / 100;
    const num = (input) => parseFloat(input?.value) || 0;
    let nextIndex = rows.querySelectorAll('[data-row]').length + 1000;

    const recalc = () => {
        let subtotal = 0;
        rows.querySelectorAll('[data-row]').forEach((row) => {
            const total = round(num(row.querySelector('[data-item-quantity]')) * num(row.querySelector('[data-item-price]')));
            row.querySelector('[data-item-total]').textContent = money(total);
            subtotal += total;
        });
        subtotal = round(subtotal);
        const discount = Math.min(num(form.querySelector('[data-discount]')), subtotal);
        const rate = num(form.querySelector('[data-tax-rate]'));
        const tax = round(((subtotal - discount) * rate) / 100);

        form.querySelector('[data-subtotal]').textContent = money(subtotal);
        form.querySelector('[data-tax]').textContent = money(tax);
        form.querySelector('[data-tax-label]').textContent = rate;
        form.querySelectorAll('[data-total]').forEach((el) => (el.textContent = money(round(subtotal - discount + tax))));
        form.querySelectorAll('[data-remove-row]').forEach((button) => (button.disabled = rows.children.length === 1));
    };

    const addRow = () => {
        rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
        rows.lastElementChild.querySelector('[data-item-picker]')?.focus();
        recalc();
    };

    form.querySelector('[data-add-row]')?.addEventListener('click', addRow);

    rows.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-row]');
        if (!button || rows.children.length === 1) return;
        button.closest('[data-row]').remove();
        recalc();
    });

    // Picking an inventory item fills in its description, unit and price.
    rows.addEventListener('change', (event) => {
        if (!event.target.matches('[data-item-picker]')) return;
        const item = inventory.get(event.target.value);
        if (!item) return;
        const row = event.target.closest('[data-row]');
        row.querySelector('[data-item-description]').value = item.description;
        row.querySelector('[data-item-unit]').value = item.unit || '';
        row.querySelector('[data-item-price]').value = item.price.toFixed(2);
        recalc();
    });

    form.addEventListener('input', recalc);

    // Choosing a customer account copies its details into the empty-or-previous fields.
    form.querySelector('[data-customer-picker]')?.addEventListener('change', (event) => {
        const option = event.target.selectedOptions[0];
        if (!option?.value) return;
        form.querySelectorAll('[data-customer-field]').forEach((input) => {
            input.value = option.dataset[input.dataset.customerField] || '';
        });
    });

    recalc();
});

// Dashboard sidebar (mobile drawer)
const dashOpen = document.querySelector('[data-dash-open]');

if (dashOpen) {
    const setDash = (open) => {
        document.body.classList.toggle('dash-open', open);
        dashOpen.setAttribute('aria-expanded', String(open));
        if (open) document.querySelector('#dash-sidebar a')?.focus();
        else dashOpen.focus();
    };

    dashOpen.addEventListener('click', () => setDash(true));
    document.querySelectorAll('[data-dash-close]').forEach((el) => el.addEventListener('click', () => setDash(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('dash-open')) setDash(false);
    });
}
