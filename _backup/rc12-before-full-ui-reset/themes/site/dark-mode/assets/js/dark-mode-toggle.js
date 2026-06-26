/**
 * Ahost One Dark Mode Toggle
 * Karanlık mod açıp kapama - localStorage ile kayıt
 */
(function() {
    const STORAGE_KEY = 'ahost_dark_mode';
    const CSS_FILE = '/themes/site/dark-mode/assets/css/dark-mode.css';
    
    let darkModeEnabled = false;
    let cssLoaded = false;
    
    // Check saved preference or system preference
    function getPreference() {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved !== null) {
            return saved === 'true';
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    
    // Save preference
    function savePreference(enabled) {
        localStorage.setItem(STORAGE_KEY, enabled ? 'true' : 'false');
    }
    
    // Load dark mode CSS
    function loadDarkCSS() {
        if (cssLoaded) return;
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = CSS_FILE + '?v=' + Date.now();
        link.id = 'dark-mode-css';
        document.head.appendChild(link);
        cssLoaded = true;
        
        // Fallback if CSS doesn't load
        link.onerror = function() {
            console.warn('Dark mode CSS could not be loaded');
        };
    }
    
    // Apply dark mode
    function applyDarkMode(enabled) {
        if (enabled) {
            loadDarkCSS();
            document.body.classList.add('dark-mode');
            updateToggleIcon(true);
        } else {
            document.body.classList.remove('dark-mode');
            updateToggleIcon(false);
        }
        darkModeEnabled = enabled;
        savePreference(enabled);
    }
    
    // Update toggle button icon
    function updateToggleIcon(enabled) {
        const toggle = document.querySelector('.dark-mode-toggle');
        if (toggle) {
            toggle.innerHTML = enabled ? '☀️' : '🌙';
            toggle.title = enabled ? 'Açık tema' : 'Karanlık tema';
        }
    }
    
    // Create toggle button
    function createToggle() {
        const toggle = document.createElement('button');
        toggle.className = 'dark-mode-toggle';
        toggle.setAttribute('aria-label', 'Tema değiştir');
        toggle.innerHTML = darkModeEnabled ? '☀️' : '🌙';
        toggle.title = darkModeEnabled ? 'Açık tema' : 'Karanlık tema';
        
        toggle.addEventListener('click', function() {
            applyDarkMode(!darkModeEnabled);
        });
        
        document.body.appendChild(toggle);
    }
    
    // Listen for system preference changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (localStorage.getItem(STORAGE_KEY) === null) {
            applyDarkMode(e.matches);
        }
    });
    
    // Initialize on DOM ready
    function init() {
        darkModeEnabled = getPreference();
        
        if (darkModeEnabled) {
            loadDarkCSS();
            document.body.classList.add('dark-mode');
        }
        
        // Create toggle button if not exists
        if (!document.querySelector('.dark-mode-toggle')) {
            createToggle();
        }
    }
    
    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Expose API for programmatic control
    window.darkMode = {
        enable: function() { applyDarkMode(true); },
        disable: function() { applyDarkMode(false); },
        toggle: function() { applyDarkMode(!darkModeEnabled); },
        isEnabled: function() { return darkModeEnabled; }
    };
})();
