import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', function () {
    const salesData = window.salesData || [];
    const salesLabels = window.salesLabels || [];

    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Total Penjualan',
                data: salesData,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                pointBackgroundColor: '#4e73df',
                pointBorderColor: '#fff',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#555',
                        font: {
                            size: 14
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#555'
                    },
                    grid: {
                        color: '#eee'
                    }
                },
                y: {
                    ticks: {
                        color: '#555'
                    },
                    grid: {
                        color: '#eee'
                    }
                }
            }
        }
    });
});
