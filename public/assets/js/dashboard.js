/**
 * Grafik dashboard (Chart.js).
 * Data dikirim dari PHP lewat window.SIMPEG_CHARTS agar tidak ada
 * permintaan AJAX tambahan saat halaman dimuat.
 */
(function () {
    'use strict';

    var payload = window.SIMPEG_CHARTS;
    if (!payload || typeof window.Chart === 'undefined') return;

    var C = {
        ink: '#334155',
        muted: '#94a3b8',
        grid: '#eef2f7',
        teal: '#0f766e',
        tealSoft: '#14b8a6',
        blue: '#2563eb',
        pink: '#ec4899',
        indigo: '#6366f1',
        amber: '#f59e0b'
    };

    Chart.defaults.font.family = '"Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.font.size = 11.5;
    Chart.defaults.color = C.muted;

    /**
     * Plugin kecil untuk menampilkan angka di ujung setiap batang.
     */
    var valueLabels = {
        id: 'valueLabels',
        afterDatasetsDraw: function (chart) {
            var horizontal = chart.options.indexAxis === 'y';
            var ctx = chart.ctx;
            var meta = chart.getDatasetMeta(0);
            var values = chart.data.datasets[0].data;

            ctx.save();
            ctx.fillStyle = C.ink;
            ctx.font = '600 11.5px "Segoe UI", system-ui, sans-serif';
            ctx.textAlign = horizontal ? 'left' : 'center';
            ctx.textBaseline = horizontal ? 'middle' : 'bottom';

            meta.data.forEach(function (bar, index) {
                var value = values[index];
                if (value === 0) return;
                if (horizontal) {
                    ctx.fillText(value, bar.x + 7, bar.y);
                } else {
                    ctx.fillText(value, bar.x, bar.y - 6);
                }
            });

            ctx.restore();
        }
    };

    function buildScales(horizontal) {
        var valueAxis = {
            beginAtZero: true,
            border: { display: false },
            grid: { color: C.grid, drawTicks: false },
            ticks: { precision: 0, padding: 8 }
        };

        var categoryAxis = {
            border: { display: false },
            grid: { display: false },
            ticks: { autoSkip: false, padding: 6 }
        };

        return horizontal
            ? { x: valueAxis, y: categoryAxis }
            : { x: categoryAxis, y: valueAxis };
    }

    function createChart(canvasId, config) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;

        new Chart(canvas.getContext('2d'), {
            type: 'bar',
            plugins: [valueLabels],
            data: config.data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: config.horizontal ? 'y' : 'x',
                layout: { padding: { top: 18, right: config.horizontal ? 26 : 6, left: 2, bottom: 0 } },
                animation: { duration: 650, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        titleFont: { size: 12, weight: '600' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function (context) {
                                var value = context.chart.options.indexAxis === 'y'
                                    ? context.parsed.x
                                    : context.parsed.y;
                                return value + ' pegawai';
                            }
                        }
                    }
                },
                scales: buildScales(Boolean(config.horizontal))
            }
        });
    }

    if (payload.gender) {
        createChart('genderChart', {
            data: {
                labels: payload.gender.labels,
                datasets: [{
                    data: payload.gender.values,
                    backgroundColor: [C.blue, C.pink],
                    hoverBackgroundColor: ['#1d4ed8', '#db2777'],
                    borderRadius: 7,
                    maxBarThickness: 62,
                    categoryPercentage: 0.62,
                    barPercentage: 0.85
                }]
            }
        });
    }

    if (payload.education) {
        createChart('educationChart', {
            horizontal: true,
            data: {
                labels: payload.education.labels,
                datasets: [{
                    data: payload.education.values,
                    backgroundColor: ['#cbd5e1', '#5eead4', C.indigo, '#a78bfa', '#38bdf8'],
                    borderRadius: 6,
                    maxBarThickness: 26,
                    categoryPercentage: 0.74,
                    barPercentage: 0.9
                }]
            }
        });
    }

    if (payload.age) {
        createChart('ageChart', {
            data: {
                labels: payload.age.labels,
                datasets: [{
                    data: payload.age.values,
                    backgroundColor: ['#99f6e4', '#5eead4', '#2dd4bf', C.tealSoft, C.teal],
                    borderRadius: 6,
                    maxBarThickness: 46,
                    categoryPercentage: 0.7,
                    barPercentage: 0.85
                }]
            }
        });
    }
})();
