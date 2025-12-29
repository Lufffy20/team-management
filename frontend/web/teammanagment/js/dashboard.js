document.addEventListener('DOMContentLoaded', () => {

    if (typeof DASHBOARD_STATS_URL === 'undefined') {
        console.error('❌ DASHBOARD_STATS_URL not defined');
        return;
    }

    const teamSelect   = document.getElementById('teamSelect');
    const statusCanvas = document.getElementById('statusChart');
    const memberCanvas = document.getElementById('memberChart');
    const timeCanvas   = document.getElementById('timelineChart');

    let statusChart   = null;
    let memberChart   = null;
    let timelineChart = null;

    function destroy(chart) {
        if (chart) {
            chart.destroy();
            chart = null;
        }
    }

    function loadDashboard(teamId = '') {

        let url = DASHBOARD_STATS_URL;
        if (teamId) {
            url += '?team_id=' + encodeURIComponent(teamId);
        }

        fetch(url)
            .then(res => {
                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }
                return res.json();
            })
            .then(d => {

                /* ================= STATUS PIE ================= */
                if (statusCanvas) {
                    destroy(statusChart);

                    statusChart = new Chart(statusCanvas, {
                        type: 'pie',
                        data: {
                            labels: ['To Do', 'In Progress', 'Done'],
                            datasets: [{
                                data: [
                                    d.status.todo ?? 0,
                                    d.status.in_progress ?? 0,
                                    d.status.done ?? 0
                                ]
                            }]
                        }
                    });
                }

                /* ================= MEMBER BAR ================= */
                if (memberCanvas) {
                    destroy(memberChart);

                    memberChart = new Chart(memberCanvas, {
                        type: 'bar',
                        data: {
                            labels: d.members.map(m => m.name),
                            datasets: [{
                                label: 'Active Tasks',
                                data: d.members.map(m => m.tasks)
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false, 
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }

                /* ================= TIMELINE LINE ================= */
                if (timeCanvas) {
                    destroy(timelineChart);

                    timelineChart = new Chart(timeCanvas, {
                        type: 'line',
                        data: {
                            labels: d.timeline.days,
                            datasets: [
                                {
                                    label: 'Created',
                                    data: d.timeline.created,
                                    tension: 0.3
                                },
                                {
                                    label: 'Completed',
                                    data: d.timeline.completed,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0 }
                                }
                            }
                        }
                    });
                }

            })
            .catch(err => {
                console.error('❌ Dashboard charts error:', err);
            });
    }

    /* 🔹 Initial load */
    loadDashboard();

    /* 🔹 Team filter */
    if (teamSelect) {
        teamSelect.addEventListener('change', e => {
            loadDashboard(e.target.value);
        });
    }

});
