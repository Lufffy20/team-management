document.addEventListener('DOMContentLoaded', function () {

    if (typeof dashboardData === 'undefined') {
        return;
    }

    /* ================= STATUS DONUT ================= */
    const statusEl = document.getElementById('statusChart');
    if (statusEl) {
        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: ['Todo', 'In Progress', 'Review', 'Done', 'Archived'],
                datasets: [{
                    data: dashboardData.statusChart,
                    backgroundColor: [
                        '#ffc107',
                        '#0dcaf0',
                        '#6f42c1',
                        '#28a745',
                        '#6c757d'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '70%'
            }
        });
    }

    /* ================= WEEKLY LINE ================= */
    const weeklyEl = document.getElementById('weeklyChart');
    if (weeklyEl) {
        new Chart(weeklyEl, {
            type: 'line',
            data: {
                labels: dashboardData.weeklyLabels,
                datasets: [{
                    data: dashboardData.weeklyCounts,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    /* ================= PRIORITY BAR ================= */
    const priorityEl = document.getElementById('priorityChart');
    if (priorityEl) {
        new Chart(priorityEl, {
            type: 'bar',
            data: {
                labels: ['Low', 'Medium', 'High'],
                datasets: [{
                    data: dashboardData.priorityStats,
                    backgroundColor: [
                        '#198754',
                        '#ffc107',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

});
