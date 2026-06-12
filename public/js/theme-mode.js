(function () {
    const storageKey = 'campushub-theme';

    function getSavedTheme() {
        return localStorage.getItem(storageKey);
    }

    function getSystemTheme() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    function getCurrentTheme() {
        return getSavedTheme() || getSystemTheme();
    }

    function applyTheme(theme) {
        const html = document.documentElement;

        if (theme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        html.setAttribute('data-theme', theme);

        document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
            icon.textContent = theme === 'dark' ? '☀' : '☾';
        });

        document.querySelectorAll('[data-theme-text]').forEach((text) => {
            text.textContent = theme === 'dark' ? 'Light' : 'Dark';
        });
    }

    function toggleTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        const nextTheme = isDark ? 'light' : 'dark';

        localStorage.setItem(storageKey, nextTheme);
        applyTheme(nextTheme);
    }

    applyTheme(getCurrentTheme());

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getCurrentTheme());

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.addEventListener('click', toggleTheme);
        });
    });
})();
