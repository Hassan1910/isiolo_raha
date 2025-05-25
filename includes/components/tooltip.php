<?php
/**
 * Tooltip Component
 * 
 * Provides a tooltip component for better user guidance
 * 
 * @return void
 */

function renderTooltipScript() {
?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        initTooltips();
    });

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        // Get all elements with data-tooltip attribute
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(element => {
            // Create tooltip element
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip opacity-0 invisible absolute z-50 p-2 bg-gray-800 text-white text-xs rounded shadow-lg transition-all duration-300 max-w-xs';
            tooltip.textContent = element.dataset.tooltip;
            
            // Set tooltip position
            const position = element.dataset.tooltipPosition || 'top';
            tooltip.classList.add(`tooltip-${position}`);
            
            // Add tooltip to the document body
            document.body.appendChild(tooltip);
            
            // Show tooltip on hover/focus
            element.addEventListener('mouseenter', () => showTooltip(element, tooltip, position));
            element.addEventListener('focus', () => showTooltip(element, tooltip, position));
            
            // Hide tooltip on mouse leave/blur
            element.addEventListener('mouseleave', () => hideTooltip(tooltip));
            element.addEventListener('blur', () => hideTooltip(tooltip));
        });
    }

    /**
     * Show tooltip
     * 
     * @param {HTMLElement} element - The element that triggered the tooltip
     * @param {HTMLElement} tooltip - The tooltip element
     * @param {string} position - The position of the tooltip
     */
    function showTooltip(element, tooltip, position) {
        // Get element position
        const rect = element.getBoundingClientRect();
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Calculate tooltip position
        let top, left;
        
        switch (position) {
            case 'top':
                top = rect.top + scrollTop - tooltip.offsetHeight - 10;
                left = rect.left + scrollLeft + (rect.width / 2) - (tooltip.offsetWidth / 2);
                break;
            case 'bottom':
                top = rect.bottom + scrollTop + 10;
                left = rect.left + scrollLeft + (rect.width / 2) - (tooltip.offsetWidth / 2);
                break;
            case 'left':
                top = rect.top + scrollTop + (rect.height / 2) - (tooltip.offsetHeight / 2);
                left = rect.left + scrollLeft - tooltip.offsetWidth - 10;
                break;
            case 'right':
                top = rect.top + scrollTop + (rect.height / 2) - (tooltip.offsetHeight / 2);
                left = rect.right + scrollLeft + 10;
                break;
            default:
                top = rect.top + scrollTop - tooltip.offsetHeight - 10;
                left = rect.left + scrollLeft + (rect.width / 2) - (tooltip.offsetWidth / 2);
        }
        
        // Set tooltip position
        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;
        
        // Show tooltip
        tooltip.classList.remove('opacity-0', 'invisible');
        tooltip.classList.add('opacity-100', 'visible');
    }

    /**
     * Hide tooltip
     * 
     * @param {HTMLElement} tooltip - The tooltip element
     */
    function hideTooltip(tooltip) {
        tooltip.classList.remove('opacity-100', 'visible');
        tooltip.classList.add('opacity-0', 'invisible');
    }
</script>

<style>
    /* Tooltip arrow styles */
    .tooltip::after {
        content: '';
        position: absolute;
        border-width: 5px;
        border-style: solid;
    }
    
    .tooltip-top::after {
        border-color: #1f2937 transparent transparent transparent;
        top: 100%;
        left: 50%;
        margin-left: -5px;
    }
    
    .tooltip-bottom::after {
        border-color: transparent transparent #1f2937 transparent;
        bottom: 100%;
        left: 50%;
        margin-left: -5px;
    }
    
    .tooltip-left::after {
        border-color: transparent transparent transparent #1f2937;
        top: 50%;
        left: 100%;
        margin-top: -5px;
    }
    
    .tooltip-right::after {
        border-color: transparent #1f2937 transparent transparent;
        top: 50%;
        right: 100%;
        margin-top: -5px;
    }
</style>
<?php
}

/**
 * Create a tooltip element
 * 
 * @param string $text - The tooltip text
 * @param string $position - The tooltip position (top, bottom, left, right)
 * @return string - The tooltip attributes
 */
function tooltip($text, $position = 'top') {
    return 'data-tooltip="' . htmlspecialchars($text) . '" data-tooltip-position="' . htmlspecialchars($position) . '"';
}
?>
