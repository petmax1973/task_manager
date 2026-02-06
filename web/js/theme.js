/**
 * Theme switching functionality
 * Simplified to work with server-side theme management
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Add smooth transitions and accessibility features
     */
    function enhanceThemeToggle() {
        const themeToggle = document.querySelector('.theme-toggle a');
        if (themeToggle) {
            // Add accessibility attributes
            themeToggle.setAttribute('role', 'button');
            themeToggle.setAttribute('aria-label', 'Toggle theme');
            
            // Add keyboard support
            themeToggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
            
            // Add smooth animation on click
            themeToggle.addEventListener('click', function(e) {
                const icon = this.querySelector('i');
                if (icon) {
                    icon.style.transform = 'rotate(180deg)';
                    icon.style.transition = 'transform 0.3s ease';
                }
                
                // Add transition to body for smoother change
                document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
            });
        }
    }
    
    // Initialize theme enhancements
    enhanceThemeToggle();
});

/**
 * CSS transition utilities
 */
const ThemeUtils = {
    /**
     * Add smooth transition to element
     */
    addTransition: function(element, duration = '0.3s') {
        element.style.transition = `background-color ${duration} ease, color ${duration} ease, border-color ${duration} ease`;
    },
    
    /**
     * Remove transition from element
     */
    removeTransition: function(element) {
        element.style.transition = '';
    },
    
    /**
     * Get current theme
     */
    getCurrentTheme: function() {
        return document.body.classList.contains('theme-dark') ? 'dark' : 'light';
    }
};

// Export for global access
window.ThemeUtils = ThemeUtils;