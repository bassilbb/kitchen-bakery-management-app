import Chart from 'chart.js/auto';

window.formatCurrency = (value) => {
    return (window.currencySymbol || '₦') + Number(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

window.formatCurrencyTooltip = (context) => {
    return ' ' + context.label + ': ' + window.formatCurrency(context.parsed);
};

const resolveCallbacks = (node) => {
    if (node === null || typeof node !== 'object') {
        return;
    }

    Object.keys(node).forEach((key) => {
        const value = node[key];

        if (typeof value === 'string' && typeof window[value] === 'function') {
            node[key] = window[value];
        } else if (Array.isArray(value)) {
            value.forEach(resolveCallbacks);
        } else if (typeof value === 'object') {
            resolveCallbacks(value);
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-chart]').forEach((canvas) => {
        const config = JSON.parse(canvas.dataset.chart || '{}');

        if (!config.type || !config.data) {
            return;
        }

        resolveCallbacks(config);

        new Chart(canvas, config);
    });
});
