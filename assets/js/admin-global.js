jQuery(document).ready(function($) {
    // Initialize currency inputs
    mkvInitCurrencyInputs();

    // Init toast container
    if ($('#mkv-toast-container').length === 0) {
        $('body').append('<div id="mkv-toast-container" style="position:fixed; top:40px; right:20px; z-index:999999; display:flex; flex-direction:column; gap:10px;"></div>');
    }

    // Auto-dismiss PHP success and warning notices after 3.5 seconds (Market standard UX)
    setTimeout(function() {
        $('.notice-success.is-dismissible, .notice-warning.is-dismissible, .updated.is-dismissible, .mkv-alert-success, .mkv-alert-warning').fadeOut(400, function() {
            $(this).remove();
        });
    }, 3500);
});

window.mkvToast = function(message, type = 'success') {
    let bgColor = '#10b981'; // success green
    let icon = 'hgi-checkmark-circle-02';
    if (type === 'error') {
        bgColor = '#ef4444';
        icon = 'hgi-alert-circle';
    } else if (type === 'warning') {
        bgColor = '#f59e0b';
        icon = 'hgi-information-circle';
    }

    const toast = jQuery(`
        <div style="background:${bgColor}; color:#fff; padding:12px 20px; border-radius:8px; display:flex; align-items:center; gap:10px; font-size:14px; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); opacity:0; transform:translateX(100%); transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
            <i class="hgi-stroke ${icon}" style="font-size:18px;"></i>
            <span>${message}</span>
        </div>
    `);

    jQuery('#mkv-toast-container').append(toast);
    
    // Animate in
    setTimeout(() => {
        toast.css({ opacity: 1, transform: 'translateX(0)' });
    }, 10);

    // Auto dismiss after 3.5s
    setTimeout(() => {
        toast.css({ opacity: 0, transform: 'translateX(100%)' });
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};

function mkvInitCurrencyInputs() {
    document.querySelectorAll('.mkv-currency-input').forEach(function(input) {
        if (input.dataset.currencyInitialized) return;
        input.dataset.currencyInitialized = 'true';

        // Parse initial value safely (avoid decimal points like 272727.27 turning into 27272727)
        var rawInitial = input.value || '0';
        var parsedInitial = Math.round(parseFloat(rawInitial)) || 0;

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = input.name;
        hiddenInput.value = parsedInitial.toString();
        
        input.removeAttribute('name');
        input.type = 'text';
        input.parentNode.insertBefore(hiddenInput, input.nextSibling);

        if (input.value) {
            input.value = parsedInitial > 0 ? parsedInitial.toLocaleString('vi-VN') : (rawInitial === '0' ? '0' : '');
        }

        input.addEventListener('input', function() {
            let rawValue = this.value.replace(/[^\d]/g, '');
            hiddenInput.value = rawValue;
        });

        input.addEventListener('blur', function() {
            const rawValue = this.value.replace(/[^\d]/g, '');
            hiddenInput.value = rawValue;
            this.value = rawValue === '' ? '' : parseInt(rawValue, 10).toLocaleString('vi-VN');
        });
        
        // Also select all text on focus for easier editing
        input.addEventListener('focus', function() {
            this.value = this.value.replace(/[^\d]/g, '');
            this.select();
        });
    });
}

// Shared keyboard and focus behavior for the existing generic modal overlays.
function mkvInitModalAccessibility() {
    if (window.mkvModalA11yInitialized) return;
    window.mkvModalA11yInitialized = true;

    var modalState = new WeakMap();
    var focusableSelector = 'a[href], area[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    function isOpen(overlay) {
        return getComputedStyle(overlay).display !== 'none' && !overlay.hidden;
    }

    function getFocusables(overlay) {
        return Array.from(overlay.querySelectorAll(focusableSelector)).filter(function(element) {
            return getComputedStyle(element).display !== 'none' && getComputedStyle(element).visibility !== 'hidden';
        });
    }

    function prepareModal(overlay) {
        var dialog = overlay.querySelector('.mkv-modal') || overlay;
        var title = dialog.querySelector('.mkv-modal-title, h2, h3');

        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        if (title) {
            if (!title.id) title.id = overlay.id + '-title';
            overlay.setAttribute('aria-labelledby', title.id);
        }
        if (!overlay.hasAttribute('aria-hidden')) overlay.setAttribute('aria-hidden', 'true');
        return dialog;
    }

    function syncModal(overlay) {
        var dialog = prepareModal(overlay);
        var open = isOpen(overlay);
        var state = modalState.get(overlay) || { open: false, opener: null };

        if (open && !state.open) {
            state.open = true;
            state.opener = document.activeElement;
            modalState.set(overlay, state);
            overlay.setAttribute('aria-hidden', 'false');
            var first = getFocusables(dialog)[0] || dialog;
            if (!first.hasAttribute('tabindex')) first.setAttribute('tabindex', '-1');
            setTimeout(function() { first.focus(); }, 0);
        } else if (!open && state.open) {
            state.open = false;
            overlay.setAttribute('aria-hidden', 'true');
            if (state.opener && document.contains(state.opener) && getComputedStyle(state.opener).display !== 'none') {
                state.opener.focus();
            }
            modalState.set(overlay, state);
        }
    }

    function closeModal(overlay) {
        var closeButton = overlay.querySelector('.mkv-modal-close');
        if (closeButton) closeButton.click();
        else overlay.style.display = 'none';
    }

    function getOpenModals() {
        return Array.from(document.querySelectorAll('.mkv-modal-overlay')).filter(isOpen);
    }

    document.querySelectorAll('.mkv-modal-overlay').forEach(prepareModal);
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.matches && mutation.target.matches('.mkv-modal-overlay')) syncModal(mutation.target);
        });
    });
    observer.observe(document.body, { attributes: true, attributeFilter: ['style', 'class', 'hidden'], subtree: true });

    document.addEventListener('keydown', function(event) {
        var overlays = getOpenModals();
        var overlay = overlays[overlays.length - 1];
        if (!overlay) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal(overlay);
            return;
        }
        if (event.key !== 'Tab') return;

        var focusables = getFocusables(overlay);
        if (!focusables.length) return;
        var first = focusables[0];
        var last = focusables[focusables.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mkvInitModalAccessibility);
} else {
    mkvInitModalAccessibility();
}
function mkvSetLang(lang, e) {
    if (e && typeof e.preventDefault === 'function') e.preventDefault();
    if (!lang || (lang !== 'vi' && lang !== 'en')) return;

    // Immediately synchronize client-side cookie and localStorage
    document.cookie = "mkv_lang=" + encodeURIComponent(lang) + "; path=/; max-age=31536000; SameSite=Lax";
    try {
        localStorage.setItem('mkv_lang', lang);
    } catch (err) {}

    const ajaxUrl = (window.mkv_global_vars && window.mkv_global_vars.ajax_url) || (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
    const nonce = (window.mkv_global_vars && window.mkv_global_vars.nonce) || '';

    if (typeof jQuery !== 'undefined') {
        jQuery.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: { 
                action: 'mkv_switch_lang', 
                lang: lang,
                nonce: nonce 
            },
            complete: function() {
                location.reload();
            }
        });
    } else {
        const formData = new FormData();
        formData.append('action', 'mkv_switch_lang');
        formData.append('lang', lang);
        formData.append('nonce', nonce);
        fetch(ajaxUrl, { method: 'POST', body: formData })
            .finally(function() {
                location.reload();
            });
    }
}

// Dark Mode Logic
function mkvInitDarkMode() {
    const isDark = localStorage.getItem('mkv-dark-mode') === 'true';
    if (isDark) {
        document.documentElement.classList.add('dark');
    }
}
// Run immediately to prevent flash
mkvInitDarkMode();

jQuery(document).ready(function($) {
    // Initial icon state
    if (document.documentElement.classList.contains('dark') || document.body.classList.contains('dark')) {
        $('#mkv-dark-mode-toggle i').removeClass('hgi-moon-02').addClass('hgi-sun-03');
        $('body').addClass('dark');
    }

    $('#mkv-dark-mode-toggle').on('click', function(e) {
        e.preventDefault();
        const isNowDark = !$('html').hasClass('dark');
        $('html').toggleClass('dark', isNowDark);
        $('body').toggleClass('dark', isNowDark);
        localStorage.setItem('mkv-dark-mode', isNowDark);
        
        const $icon = $(this).find('i');
        if (isNowDark) {
            $icon.removeClass('hgi-moon-02').addClass('hgi-sun-03');
        } else {
            $icon.removeClass('hgi-sun-03').addClass('hgi-moon-02');
        }
    });
});
