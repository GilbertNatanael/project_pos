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

    } catch (error) {
        console.error('Error:', error);
        showError('Prediction failed: ' + error.message);
    } finally {
        showLoading(false);
    }
};

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

function displaySingleResult(data) {
    const resultsDiv = document.getElementById('results');
    const mape = data.model_performance.mape;
    let performanceClass = 'success';
    if (mape > 20) performanceClass = 'danger';
    else if (mape > 10) performanceClass = 'warning';

    let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>🎯 Forecast Results for: ${data.item_name}</h5>
                <span class="badge bg-${performanceClass} performance-badge">
                    MAPE: ${mape.toFixed(2)}%
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>📅 Date</th>
                                <th>📦 Predicted Quantity</th>
                                <th>📊 Confidence Range</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    data.predictions.forEach(pred => {
        html += `
            <tr>
                <td><strong>${pred.date}</strong></td>
                <td><span class="badge bg-primary">${pred.predicted_quantity.toFixed(2)}</span></td>
                <td><small class="text-muted">${pred.lower_bound.toFixed(2)} - ${pred.upper_bound.toFixed(2)}</small></td>
            </tr>
        `;
    });

    html += `
                        </tbody>
                    </table>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4 text-center">
                        <small class="text-muted">MAE</small>
                        <div class="fw-bold">${data.model_performance.mae.toFixed(4)}</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted">RMSE</small>
                        <div class="fw-bold">${data.model_performance.rmse.toFixed(4)}</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted">MAPE</small>
                        <div class="fw-bold text-${performanceClass}">${mape.toFixed(2)}%</div>
                    </div>
                </div>
            </div>
        </div>
    `;

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

        html += `
            <div class="mb-4">
                <h6 class="d-flex justify-content-between align-items-center">
                    ${item} 
                    <span class="badge bg-${performanceClass} performance-badge">
                        MAPE: ${mape.toFixed(2)}%
                    </span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Date</th><th>Predicted Quantity</th></tr></thead>
                        <tbody>
        `;

        data[item].predictions.forEach(pred => {
            html += `<tr><td>${pred.date}</td><td><span class="badge bg-info">${pred.predicted_quantity.toFixed(2)}</span></td></tr>`;
        });

        html += '</tbody></table></div></div>';
    });

    html += '</div></div>';
    resultsDiv.innerHTML = html;
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
