document.documentElement.classList.add('js');
window.zenrayReady = true;

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

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

// Light / dark theme. Light is the default; the head script re-applies a saved choice before first paint.
const themeToggles = document.querySelectorAll('[data-theme-toggle]');

if (themeToggles.length) {
    const root = document.documentElement;
    const themeColor = document.querySelector('meta[name="theme-color"]');

    const reflectTheme = () => {
        const dark = root.dataset.theme === 'dark';
        themeToggles.forEach((button) => {
            button.setAttribute('aria-pressed', String(dark));
            button.title = dark ? 'Switch to light mode' : 'Switch to dark mode';
        });
        themeColor?.setAttribute('content', getComputedStyle(root).getPropertyValue('--color-film-950').trim());
    };

    const setTheme = (dark) => {
        if (dark) root.dataset.theme = 'dark';
        else delete root.dataset.theme;
        try {
            localStorage.setItem('zenray-theme', dark ? 'dark' : 'light');
        } catch {
            // Private browsing can block storage. The switch still works for this page.
        }
        reflectTheme();
    };

    themeToggles.forEach((button) => {
        button.addEventListener('click', () => {
            const dark = root.dataset.theme !== 'dark';
            if (document.startViewTransition && !reduceMotion) document.startViewTransition(() => setTheme(dark));
            else setTheme(dark);
        });
    });

    reflectTheme();
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

    window.matchMedia('(min-width: 1280px)').addEventListener('change', (event) => {
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

// Reveal animations (fade-and-rise, staggered groups) play as
// elements scroll into view. Skipped entirely under reduced motion.
const revealables = document.querySelectorAll('[data-reveal], [data-stagger]');

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

// Featured equipment filter. ?category= also drives it.
const filterGroup = document.querySelector('[data-filter-group]');
const filterGrid = document.querySelector('[data-filter-grid]');

if (filterGroup && filterGrid) {
    const buttons = filterGroup.querySelectorAll('[data-filter]');
    const items = filterGrid.querySelectorAll('[data-filter-item]');
    const status = document.querySelector('[data-filter-status]');

    // Mixed tile sizes on a four-column grid: wide, small, small / small, small, wide. The last
    // tile is wide too, so filtered results look deliberate, but never wider than two columns
    // because a photo stretched across the whole row crops badly.
    const arrangeTiles = () => {
        const visible = [...items].filter((item) => !item.hidden);
        let column = 0;
        visible.forEach((item, index) => {
            const remaining = 4 - column;
            const wanted = index % 6 === 0 || index % 6 === 5 || index === visible.length - 1 ? 2 : 1;
            const span = Math.min(wanted, remaining);
            item.style.setProperty('--span', span);
            column = (column + span) % 4;
        });
    };

    const applyFilter = (slug) => {
        if (![...buttons].some((b) => b.dataset.filter === slug)) slug = 'all';

        buttons.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.filter === slug)));

        let shown = 0;
        items.forEach((item) => {
            const match = slug === 'all' || item.dataset.filterItem === slug;
            item.hidden = !match;
            if (match) shown++;
        });

        arrangeTiles();
        if (status) status.textContent = `Showing ${shown} ${shown === 1 ? 'item' : 'items'}`;
    };

    buttons.forEach((b) => b.addEventListener('click', () => applyFilter(b.dataset.filter)));

    const initial = new URLSearchParams(location.search).get('category');
    if (initial) applyFilter(initial);
    else arrangeTiles();
}

// "Who we serve": hovering or focusing a row shows its photo. The last one stays until the next.
document.querySelectorAll('[data-industries]').forEach((section) => {
    const rows = [...section.querySelectorAll('[data-industry-row]')];
    const photos = [...section.querySelectorAll('[data-industry-photo]')];

    rows.forEach((row, current) => {
        const activate = () => {
            [rows, photos].forEach((list) => list.forEach((el, i) => el.toggleAttribute('data-active', i === current)));
        };
        row.addEventListener('pointerenter', activate);
        row.addEventListener('focusin', activate);
    });
});

// Section tabs: the tab for the section in view is marked, and scrolled into sight on small screens.
document.querySelectorAll('[data-section-tabs]').forEach((nav) => {
    const list = nav.querySelector('ul');
    const tabs = [...nav.querySelectorAll('a')];

    const mark = (id) => {
        tabs.forEach((tab) => {
            const current = tab.getAttribute('href') === `#${id}`;
            tab.toggleAttribute('aria-current', current);
            if (current) {
                tab.setAttribute('aria-current', 'true');
                list.scrollTo({ left: tab.offsetLeft - list.offsetLeft - 16, behavior: reduceMotion ? 'auto' : 'smooth' });
            }
        });
    };

    const sections = [...document.querySelectorAll('[data-section-tab-target]')];
    let active = null;
    let ticking = false;

    // The current section is the last one whose top has passed under the header and tabs.
    // Checked on every scroll, so fast scrolling and jumps can't leave the wrong tab marked.
    const sync = () => {
        ticking = false;
        const current = sections.filter((section) => section.getBoundingClientRect().top <= 120).pop() ?? sections[0];
        if (current === active) return;
        active = current;
        mark(current.id);
    };

    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(sync);
    }, { passive: true });

    sync();
});

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
            set('[data-qv-availability]', (el) => (el.textContent = d.availability));
            set('[data-qv-features]', (el) => {
                const features = JSON.parse(d.features || '[]');
                el.replaceChildren(...features.map((feature) => Object.assign(document.createElement('li'), { textContent: feature })));
                el.hidden = features.length === 0;
            });
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

// Gallery: hover or focus opens a panel on large screens, swiping centres one on small screens.
document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const track = gallery.querySelector('[data-gallery-track]');
    const panels = [...gallery.querySelectorAll('[data-gallery-panel]')];
    const current = gallery.querySelector('[data-gallery-current]');
    const progress = gallery.querySelector('[data-gallery-progress]');
    const large = window.matchMedia('(min-width: 64rem)');
    let active = parseInt(gallery.dataset.galleryStart, 10) || 0;

    const setActive = (index) => {
        if (index === active) return;
        panels[active]?.classList.remove('is-active');
        panels[index].classList.add('is-active');
        active = index;
        if (current) current.textContent = String(index + 1).padStart(2, '0');
        if (progress) progress.style.width = `${((index + 1) / panels.length) * 100}%`;
    };

    const centreOn = (index, behavior = reduceMotion ? 'auto' : 'smooth') => {
        const panel = panels[index];
        track.scrollTo({ left: panel.offsetLeft - (track.clientWidth - panel.offsetWidth) / 2, behavior });
    };

    const goTo = (index) => {
        const next = (index + panels.length) % panels.length;
        setActive(next);
        centreOn(next);
    };

    panels.forEach((panel, index) => {
        panel.addEventListener('pointerenter', () => {
            if (large.matches) setActive(index);
        });
        panel.addEventListener('focusin', () => setActive(index));
    });

    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => goTo(active - 1));
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => goTo(active + 1));

    // While swiping, the panel nearest the middle of the track is the active one.
    let ticking = false;
    track.addEventListener('scroll', () => {
        if (large.matches || ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            ticking = false;
            const middle = track.scrollLeft + track.clientWidth / 2;
            const distance = (panel) => Math.abs(panel.offsetLeft + panel.offsetWidth / 2 - middle);
            setActive(panels.indexOf(panels.reduce((nearest, panel) => (distance(panel) < distance(nearest) ? panel : nearest))));
        });
    }, { passive: true });

    if (!large.matches) centreOn(active, 'auto');
});

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

// Hero slideshow. The active tab's loading line is a CSS animation; when it finishes, the next
// slide opens, so pausing the animation pauses the slideshow. Hovering or keyboard focus pauses;
// choosing a tab or the pause button stops it. It never starts under reduced motion.
document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const texts = carousel.querySelectorAll('[data-slide-text]');
    const media = carousel.querySelectorAll('[data-slide-media]');
    const tabs = carousel.querySelectorAll('[data-carousel-tab]');
    const toggleButton = carousel.querySelector('[data-carousel-toggle]');
    const toggleLabel = carousel.querySelector('[data-carousel-toggle-label]');
    let current = 0;
    let hovering = false;
    let focused = false;

    if (!toggleButton) return;

    const go = (index) => {
        current = (index + tabs.length) % tabs.length;
        [texts, media].forEach((list) =>
            list.forEach((slide, i) => {
                slide.toggleAttribute('data-active', i === current);
                slide.inert = i !== current;
            }),
        );
        tabs.forEach((tab, i) => tab.setAttribute('aria-current', String(i === current)));
    };

    const syncPaused = () => carousel.classList.toggle('is-paused', hovering || focused || document.hidden);

    const setPlaying = (playing) => {
        const label = playing ? 'Pause slideshow' : 'Play slideshow';
        carousel.classList.toggle('is-playing', playing);
        toggleLabel.textContent = label;
        toggleButton.title = label;
    };

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => {
            setPlaying(false);
            go(i);
        });
        tab.addEventListener('animationend', (event) => {
            if (event.animationName === 'tab-progress') go(current + 1);
        });
    });

    toggleButton.addEventListener('click', () => {
        focused = false;
        syncPaused();
        setPlaying(!carousel.classList.contains('is-playing'));
    });

    if (finePointer) {
        carousel.addEventListener('mouseenter', () => {
            hovering = true;
            syncPaused();
        });
        carousel.addEventListener('mouseleave', () => {
            hovering = false;
            syncPaused();
        });
    }

    // Keyboard focus only: a mouse click also moves focus, but must not leave the slideshow paused.
    carousel.addEventListener('focusin', (event) => {
        focused = event.target.matches(':focus-visible');
        syncPaused();
    });
    carousel.addEventListener('focusout', (event) => {
        if (carousel.contains(event.relatedTarget)) return;
        focused = false;
        syncPaused();
    });
    document.addEventListener('visibilitychange', syncPaused);

    toggleButton.hidden = false;
    setPlaying(!reduceMotion);
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
    // Picking a service fills in its name and leaves the price to be typed.
    rows.addEventListener('change', (event) => {
        if (!event.target.matches('[data-item-picker]')) return;
        const row = event.target.closest('[data-row]');
        const service = event.target.selectedOptions[0]?.dataset.service;
        if (service) {
            row.querySelector('[data-item-description]').value = service;
            row.querySelector('[data-item-unit]').value = '';
            row.querySelector('[data-item-price]').value = '';
            row.querySelector('[data-item-price]').focus();
            recalc();
            return;
        }
        const item = inventory.get(event.target.value);
        if (!item) return;
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
