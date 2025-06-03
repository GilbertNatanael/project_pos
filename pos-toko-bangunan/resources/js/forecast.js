import Chart from 'chart.js/auto';

let currentChart = null;

// Store used periods for validation
let usedPeriods = {};

// Load used periods when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadUsedPeriods();
});

// Load used periods for all items
async function loadUsedPeriods() {
    try {
        const items = Array.from(document.getElementById('item-select').options).map(opt => opt.value);
        
        for (const item of items) {
            const response = await fetch(`/api/forecast/available-dates/${encodeURIComponent(item)}`);
            if (response.ok) {
                const data = await response.json();
                usedPeriods[item] = data.used_periods || [];
            }
        }
    } catch (error) {
        console.error('Error loading used periods:', error);
    }
}

// Check if date period overlaps with existing predictions
function checkPeriodOverlap(itemName, dateFrom, dateTo) {
    if (!usedPeriods[itemName]) return { hasOverlap: false, details: [] };
    
    const newStart = new Date(dateFrom);
    const newEnd = new Date(dateTo);
    
    for (const period of usedPeriods[itemName]) {
        const existingStart = new Date(period.tanggal_dari);
        const existingEnd = new Date(period.tanggal_sampai);
        
        // Check for overlap
        if ((newStart <= existingEnd && newEnd >= existingStart)) {
            return {
                hasOverlap: true,
                details: [{
                    existing_period: {
                        from: period.tanggal_dari,
                        to: period.tanggal_sampai
                    }
                }]
            };
        }
    }
    
    return { hasOverlap: false, details: [] };
}

// Show overlap error message
function showOverlapError(overlapData) {
    let message = 'Periode prediksi bertabrakan dengan prediksi sebelumnya:\n\n';
    
    if (overlapData.overlapping_items) {
        // Multiple items overlap
        overlapData.overlapping_items.forEach(item => {
            message += `Item: ${item.item}\n`;
            item.details.forEach(detail => {
                message += `- Periode yang sudah digunakan: ${detail.existing_period.from} sampai ${detail.existing_period.to}\n`;
            });
            message += '\n';
        });
    } else if (overlapData.details) {
        // Single item overlap
        overlapData.details.forEach(detail => {
            message += `- Periode yang sudah digunakan: ${detail.existing_period.from} sampai ${detail.existing_period.to}\n`;
        });
    }
    
    message += '\nSilakan pilih periode yang berbeda.';
    showError(message);
}

window.predictSingle = async function () {
    const item = document.getElementById('item-select').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;

    // Validasi tanggal
    if (!dateFrom || !dateTo) {
        showError('Please select both from and to dates');
        return;
    }

    if (new Date(dateFrom) >= new Date(dateTo)) {
        showError('From date must be before to date');
        return;
    }

    // Validasi periode overlap di frontend
    const overlapCheck = checkPeriodOverlap(item, dateFrom, dateTo);
    if (overlapCheck.hasOverlap) {
        showOverlapError({ details: overlapCheck.details });
        return;
    }

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
                date_from: dateFrom,
                date_to: dateTo
            })
        });

        const data = await response.json();

        if (data.error) {
            if (response.status === 422 && data.details) {
                // Handle overlap error from backend
                showOverlapError(data);
            } else {
                throw new Error(data.error);
            }
            return;
        }

        // Update used periods after successful prediction
        await loadUsedPeriods();
        
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
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;

    // Validasi tanggal
    if (!dateFrom || !dateTo) {
        showError('Please select both from and to dates');
        return;
    }

    if (new Date(dateFrom) >= new Date(dateTo)) {
        showError('From date must be before to date');
        return;
    }

    // Validasi periode overlap untuk semua item di frontend
    const items = Array.from(document.getElementById('item-select').options).map(opt => opt.value);
    const overlappingItems = [];
    
    for (const item of items) {
        const overlapCheck = checkPeriodOverlap(item, dateFrom, dateTo);
        if (overlapCheck.hasOverlap) {
            overlappingItems.push({
                item: item,
                details: overlapCheck.details
            });
        }
    }
    
    if (overlappingItems.length > 0) {
        showOverlapError({ overlapping_items: overlappingItems });
        return;
    }

    showLoading(true);

    try {
        const response = await fetch('/api/forecast/all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                date_from: dateFrom,
                date_to: dateTo
            })
        });

        const data = await response.json();

        if (data.error) {
            if (response.status === 422 && data.overlapping_items) {
                // Handle overlap error from backend
                showOverlapError(data);
            } else {
                throw new Error(data.error);
            }
            return;
        }

        // Update used periods after successful prediction
        await loadUsedPeriods();
        
        displayAllResults(data);
        createChartAll(data);

    } catch (error) {
        console.error('Error:', error);
        showError('Prediction failed: ' + error.message);
    } finally {
        showLoading(false);
    }
};

// Add visual indicators for unavailable dates
function updateDateInputs() {
    const item = document.getElementById('item-select').value;
    const dateFromInput = document.getElementById('date-from');
    const dateToInput = document.getElementById('date-to');
    
    // Clear previous warnings
    removeWarnings();
    
    if (usedPeriods[item] && usedPeriods[item].length > 0) {
        showUsedPeriodsInfo(item);
    }
}

// Show information about used periods
function showUsedPeriodsInfo(itemName) {
    const existingInfo = document.getElementById('used-periods-info');
    if (existingInfo) existingInfo.remove();
    
    if (!usedPeriods[itemName] || usedPeriods[itemName].length === 0) return;
    
    const infoDiv = document.createElement('div');
    infoDiv.id = 'used-periods-info';
    infoDiv.className = 'alert alert-info mt-2';
    infoDiv.innerHTML = `
        <strong>Periode yang sudah digunakan untuk ${itemName}:</strong>
        <ul class="mb-0 mt-2">
            ${usedPeriods[itemName].map(period => 
                `<li>${period.tanggal_dari} sampai ${period.tanggal_sampai}</li>`
            ).join('')}
        </ul>
    `;
    
    const controlPanel = document.querySelector('.card-body');
    controlPanel.appendChild(infoDiv);
}

// Remove warning elements
function removeWarnings() {
    const warnings = document.querySelectorAll('#used-periods-info');
    warnings.forEach(warning => warning.remove());
}

// Update info when item selection changes
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('item-select');
    if (itemSelect) {
        itemSelect.addEventListener('change', updateDateInputs);
        // Show initial info
        updateDateInputs();
    }
});

function displaySingleResult(data) {
    const resultsDiv = document.getElementById('results');

    // Hitung total prediksi, bulat & minimal 0
    const totalPredicted = data.predictions.reduce((sum, p) => sum + Math.max(0, Math.round(p.predicted_quantity)), 0);

    let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>🎯 Forecast Results for: ${data.item_name}</h5>
                <small class="text-muted">Period: ${data.date_from} to ${data.date_to}</small>
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
    let html = `<div class="card">
        <div class="card-header">
            <h5>📈 Forecast for All Items</h5>
            <small class="text-muted">Period: ${data.period?.date_from} to ${data.period?.date_to}</small>
        </div>
        <div class="card-body">`;

    Object.keys(data).forEach(item => {
        if (item === 'period') return; // Skip period info

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

    // Filter out non-item keys (like 'period')
    const itemKeys = Object.keys(data).filter(key => key !== 'period' && data[key].predictions);

    if (itemKeys.length === 0) {
        console.error('No valid item data found');
        return;
    }

    // Ambil tanggal dari item pertama yang valid
    const firstItemKey = itemKeys[0];
    const labels = data[firstItemKey].predictions.map(p => p.date);

    // Buat dataset per item (hanya untuk item yang valid)
    const colors = [
        'rgb(75, 192, 192)', 'rgb(255, 99, 132)', 'rgb(255, 206, 86)',
        'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(255, 159, 64)',
        'rgb(201, 203, 207)'
    ];

    const datasets = itemKeys.map((item, index) => {
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
