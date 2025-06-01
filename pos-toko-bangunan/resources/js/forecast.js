import Chart from 'chart.js/auto';

let currentChart = null;

window.predictSingle = async function () {
    const item = document.getElementById('item-select').value;
    const days = document.getElementById('days-input').value;

    showLoading(true);

    try {
        const response = await fetch('/api/forecast/single', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                item_name: item,
                days_ahead: parseInt(days)
            })
        });

        const data = await response.json();

        if (data.error) throw new Error(data.error);

        displaySingleResult(data);
        createChart([data]);

    } catch (error) {
        console.error('Error:', error);
        showError('Prediction failed: ' + error.message);
    } finally {
        showLoading(false);
    }
};

window.predictAll = async function () {
    const days = document.getElementById('days-input').value;

    showLoading(true);

    try {
        const response = await fetch('/api/forecast/all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                days_ahead: parseInt(days)
            })
        });

        const data = await response.json();

        if (data.error) throw new Error(data.error);

        displayAllResults(data);
        createChartAll(data);

    } catch (error) {
        console.error('Error:', error);
        showError('Prediction failed: ' + error.message);
    } finally {
        showLoading(false);
    }
};

function displaySingleResult(data) {
    const resultsDiv = document.getElementById('results');

    // Hitung total prediksi, bulat & minimal 0
    const totalPredicted = data.predictions.reduce((sum, p) => sum + Math.max(0, Math.round(p.predicted_quantity)), 0);

    let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>🎯 Forecast Results for: ${data.item_name}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>📅 Date</th>
                                <th>📦 Predicted Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    data.predictions.forEach(pred => {
        const qty = Math.max(0, Math.round(pred.predicted_quantity));
        html += `
            <tr>
                <td><strong>${pred.date}</strong></td>
                <td><span class="badge bg-primary">${qty}</span></td>
            </tr>
        `;
    });

    html += `
                        </tbody>
                    </table>
                </div>
                <div class="text-end fw-bold mt-3">
                    Total Predicted Sales: ${totalPredicted}
                </div>
            </div>
        </div>
    `;

    // Tambahkan informasi stok jika tersedia
    if (data.stock_info) {
        html += generateStockInfoCard(data.stock_info, data.item_name);
    }

    resultsDiv.innerHTML = html;
}

function displayAllResults(data) {
    const resultsDiv = document.getElementById('results');
    let html = '<div class="card"><div class="card-header"><h5>📈 Forecast for All Items</h5></div><div class="card-body">';

    Object.keys(data).forEach(item => {
        const mape = data[item].mape;
        let performanceClass = 'success';
        if (mape > 20) performanceClass = 'danger';
        else if (mape > 10) performanceClass = 'warning';

        // Hitung total prediksi (bulat & tidak negatif)
        const totalPredicted = data[item].predictions.reduce((sum, p) => sum + Math.max(0, Math.round(p.predicted_quantity)), 0);

        html += `
            <div class="mb-4">
                <h6 class="d-flex justify-content-between align-items-center">
                    ${item} 
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Date</th><th>Predicted Quantity</th></tr></thead>
                        <tbody>
        `;

        data[item].predictions.forEach(pred => {
            const qty = Math.max(0, Math.round(pred.predicted_quantity));
            html += `<tr><td>${pred.date}</td><td><span class="badge bg-info">${qty}</span></td></tr>`;
        });

        html += `
                        </tbody>
                    </table>
                </div>
                <div class="text-end fw-bold">
                    Total Predicted Sales: ${totalPredicted}
                </div>
        `;

        // Tambahkan informasi stok untuk setiap item
        if (data[item].stock_info) {
            html += generateStockInfoTable(data[item].stock_info, item);
        }

        html += '</div>';
    });

    html += '</div></div>';
    resultsDiv.innerHTML = html;
}

function generateStockInfoCard(stockInfo, itemName) {
    const warningColors = {
        'out_of_stock': 'danger',
        'critical': 'danger',
        'warning': 'warning',
        'caution': 'info',
        'safe': 'success',
        'unknown': 'secondary',
        'error': 'danger'
    };

    const warningColor = warningColors[stockInfo.warning_level] || 'secondary';

    return `
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">📊 Stock Analysis for ${itemName}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong>Stok Saat ini:</strong></span>
                            <span class="badge bg-primary">${stockInfo.current_stock} units</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong>Kapan Habis:</strong></span>
                            <span class="badge bg-${warningColor}">
                                ${stockInfo.days_until_depletion ? stockInfo.days_until_depletion + ' days' : 'Safe'}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Estimasi Tanggal Habis:</strong></span>
                            <span class="badge bg-${warningColor}">
                                ${stockInfo.depletion_date || 'N/A'}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-${warningColor} mb-0">
                            <strong>Status:</strong><br>
                            ${stockInfo.message}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function generateStockInfoTable(stockInfo, itemName) {
    const warningColors = {
        'out_of_stock': 'danger',
        'critical': 'danger',
        'warning': 'warning',
        'caution': 'info',
        'safe': 'success',
        'unknown': 'secondary',
        'error': 'danger'
    };

    const warningColor = warningColors[stockInfo.warning_level] || 'secondary';

    return `
        <div class="mt-3 p-3 border rounded bg-light">
            <h6 class="text-muted mb-2">📊 Stock Analysis</h6>
            <div class="row">
                <div class="col-6">
                    <small><strong>Current Stock:</strong> <span class="badge bg-primary">${stockInfo.current_stock}</span></small>
                </div>
                <div class="col-6">
                    <small><strong>Days Until Depletion:</strong> 
                        <span class="badge bg-${warningColor}">
                            ${stockInfo.days_until_depletion ? stockInfo.days_until_depletion + ' days' : 'Safe'}
                        </span>
                    </small>
                </div>
            </div>
            <div class="mt-2">
                <small class="text-${warningColor}"><strong>${stockInfo.message}</strong></small>
            </div>
        </div>
    `;
}

function createChartAll(data) {
    const ctx = document.getElementById('forecast-chart').getContext('2d');

    if (currentChart) {
        currentChart.destroy();
    }

    // Ambil tanggal dari item pertama
    const firstItemKey = Object.keys(data)[0];
    const labels = data[firstItemKey].predictions.map(p => p.date);

    // Buat dataset per item
    const colors = [
        'rgb(75, 192, 192)', 'rgb(255, 99, 132)', 'rgb(255, 206, 86)',
        'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(255, 159, 64)',
        'rgb(201, 203, 207)'
    ];

    const datasets = Object.keys(data).map((item, index) => {
        const color = colors[index % colors.length];
        return {
            label: item,
            data: data[item].predictions.map(p => Math.max(0, Math.round(p.predicted_quantity))),
            borderColor: color,
            backgroundColor: color.replace('rgb', 'rgba').replace(')', ', 0.2)'),
            tension: 0.1,
            fill: false
        };
    });

    currentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Sales Forecast for All Items'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Quantity' }
                },
                x: {
                    title: { display: true, text: 'Date' }
                }
            }
        }
    });

    document.getElementById('chart-section').style.display = 'block';
}


function showLoading(show) {
    document.getElementById('loading').style.display = show ? 'block' : 'none';
}

function showError(message) {
    const resultsDiv = document.getElementById('results');
    resultsDiv.innerHTML = `
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Error!</h4>
            <p>${message}</p>
        </div>
    `;
}

function createChart(data) {
    const ctx = document.getElementById('forecast-chart').getContext('2d');

    if (currentChart) {
        currentChart.destroy();
    }

    const chartData = data[0];

    currentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.predictions.map(p => p.date),
            datasets: [{
                label: 'Predicted Sales',
                data: chartData.predictions.map(p => p.predicted_quantity),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: `Sales Forecast for ${chartData.item_name}`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Quantity' }
                },
                x: {
                    title: { display: true, text: 'Date' }
                }
            }
        }
    });

    document.getElementById('chart-section').style.display = 'block';
}
