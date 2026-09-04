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
    let icon = 'hgi-check-circle-02';
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

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = input.name;
        hiddenInput.value = input.value || '0';
        
        input.removeAttribute('name');
        input.type = 'text';
        input.parentNode.insertBefore(hiddenInput, input.nextSibling);

        if (input.value) {
            input.value = parseInt(input.value || 0, 10).toLocaleString('vi-VN');
        }

        input.addEventListener('input', function(e) {
            let rawValue = this.value.replace(/[^\d]/g, '');
            if (rawValue === '') rawValue = '0';
            hiddenInput.value = rawValue;
            this.value = parseInt(rawValue, 10).toLocaleString('vi-VN');
        });
        
        // Also select all text on focus for easier editing
        input.addEventListener('focus', function() {
            this.select();
        });
    });
}
function mkvSetLang(lang, e) {
    if (e) e.preventDefault();
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: { action: 'mkv_switch_lang', lang: lang },
        success: function(res) {
            if(res.success) {
                location.reload();
            }
        }
    });
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
