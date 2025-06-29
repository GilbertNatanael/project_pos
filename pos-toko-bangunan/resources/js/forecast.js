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

// Check if date period overlaps with existing predictions (Updated for monthly)
function checkPeriodOverlap(itemName, dateFromInput, dateToInput) {
    if (!usedPeriods[itemName]) return { hasOverlap: false, details: [] };
    
    // Convert month inputs to proper dates for comparison
    const newStart = new Date(dateFromInput + '-01'); // Add day for month input
    const newEnd = new Date(dateToInput + '-01');
    
    for (const period of usedPeriods[itemName]) {
        const existingStart = new Date(period.tanggal_dari);
        const existingEnd = new Date(period.tanggal_sampai);
        
        // Check for overlap using month comparison
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

// Perbaikan di function predictSingle() - paste-3.txt
// Ganti function predictSingle() di paste-2.txt (sekitar baris 82-158)
window.predictSingle = async function () {
    const item = document.getElementById('item-select').value;
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;

    if (!dateFrom || !dateTo) {
        showError('Please select both from and to months');
        return;
    }

    if (new Date(dateFrom + '-01') >= new Date(dateTo + '-01')) {
        showError('From month must be before to month');
        return;
    }

    const overlapCheck = checkPeriodOverlap(item, dateFrom, dateTo);
    if (overlapCheck.hasOverlap) {
        showOverlapError({ details: overlapCheck.details });
        return;
    }

    showLoading(true);

    try {
        const apiDateFrom = dateFrom + '-01';
        const apiDateTo = dateTo + '-01';

        const response = await fetch('/api/forecast/single', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                item_name: item,
                date_from: apiDateFrom,
                date_to: apiDateTo
            })
        });

        // Cek apakah response berhasil terlebih dahulu
        if (!response.ok) {
            // Clone response untuk bisa dibaca multiple kali
            const responseClone = response.clone();
            
            try {
                const errorData = await response.json();
                if (response.status === 422 && errorData.details) {
                    showOverlapError(errorData);
                } else {
                    throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (parseError) {
                // Jika tidak bisa parse JSON, ambil text response dari clone
                try {
                    const textResponse = await responseClone.text();
                    console.error('Server error response:', textResponse);
                    throw new Error(`Server error (${response.status}): Check if Flask API is running on port 5000`);
                } catch (textError) {
                    throw new Error(`Server error (${response.status}): Unable to read error response`);
                }
            }
            return;
        }

        // Cek content type untuk response yang berhasil
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Non-JSON response:', textResponse);
            throw new Error('Server returned non-JSON response. Check if Flask API is running properly.');
        }

        const data = await response.json();
        
        await loadUsedPeriods();
        displaySingleResult(data);
        createChart([data]);

    } catch (error) {
        console.error('Error details:', error);
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            showError('Cannot connect to server. Please check if Laravel and Flask APIs are running.');
        } else if (error.message.includes('non-JSON')) {
            showError('Server configuration error. Please check if Flask API is running on port 5000.');
        } else {
            showError('Monthly prediction failed: ' + error.message);
        }
    } finally {
        showLoading(false);
    }
};

// Ganti function predictAll() di paste-2.txt (sekitar baris 160-238)
window.predictAll = async function () {
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;

    if (!dateFrom || !dateTo) {
        showError('Please select both from and to months');
        return;
    }

    if (new Date(dateFrom + '-01') >= new Date(dateTo + '-01')) {
        showError('From month must be before to month');
        return;
    }

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
        const apiDateFrom = dateFrom + '-01';
        const apiDateTo = dateTo + '-01';

        const response = await fetch('/api/forecast/all', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                date_from: apiDateFrom,
                date_to: apiDateTo
            })
        });

        // Cek apakah response berhasil terlebih dahulu
        if (!response.ok) {
            // Clone response untuk bisa dibaca multiple kali
            const responseClone = response.clone();
            
            try {
                const errorData = await response.json();
                if (response.status === 422 && errorData.overlapping_items) {
                    showOverlapError(errorData);
                } else {
                    throw new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (parseError) {
                // Jika tidak bisa parse JSON, ambil text response dari clone
                try {
                    const textResponse = await responseClone.text();
                    console.error('Server error response:', textResponse);
                    throw new Error(`Server error (${response.status}): Check if Flask API is running on port 5000`);
                } catch (textError) {
                    throw new Error(`Server error (${response.status}): Unable to read error response`);
                }
            }
            return;
        }

        // Cek content type untuk response yang berhasil
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Non-JSON response:', textResponse);
            throw new Error('Server returned non-JSON response. Check if Flask API is running properly.');
        }

        const data = await response.json();

        await loadUsedPeriods();
        displayAllResults(data);
        createChartAll(data);

    } catch (error) {
        console.error('Error details:', error);
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            showError('Cannot connect to server. Please check if Laravel and Flask APIs are running.');
        } else if (error.message.includes('non-JSON')) {
            showError('Server configuration error. Please check if Flask API is running on port 5000.');
        } else {
            showError('Monthly prediction failed: ' + error.message);
        }
    } finally {
        showLoading(false);
    }
};

// Updated display function for monthly results
function displaySingleResult(data) {
    const resultsDiv = document.getElementById('results');

    // Calculate total predicted quantity (rounded and non-negative)
    const totalPredicted = data.predictions.reduce((sum, p) => sum + Math.max(0, Math.round(p.predicted_quantity)), 0);

    // Format month display
    const formatMonth = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
    };

    let html = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>🎯 Monthly Forecast Results for: ${data.item_name}</h5>
                <small class="text-muted">Period: ${formatMonth(data.date_from)} to ${formatMonth(data.date_to)}</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>📅 Month</th>
                                <th>📦 Predicted Monthly Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    data.predictions.forEach(pred => {
        const qty = Math.max(0, Math.round(pred.predicted_quantity));
        html += `
            <tr>
                <td><strong>${formatMonth(pred.date)}</strong></td>
                <td><span class="badge bg-primary fs-6">${qty.toLocaleString()}</span></td>
            </tr>
        `;
    });

    html += `
                        </tbody>
                    </table>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="text-end fw-bold">
                            <span class="badge bg-success fs-6">Total Predicted Sales: ${totalPredicted.toLocaleString()}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-end">
                            <small class="text-muted">Average per month: ${Math.round(totalPredicted / data.predictions.length).toLocaleString()}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Add stock information if available
    if (data.stock_info) {
        html += generateMonthlyStockInfoCard(data.stock_info, data.item_name);
    }

    resultsDiv.innerHTML = html;
}

// Updated display function for all items monthly results
function displayAllResults(data) {
    const resultsDiv = document.getElementById('results');
    
    // Format month display
    const formatMonth = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
    };

    let html = `<div class="card">
        <div class="card-header">
            <h5>📈 Monthly Forecast for All Items</h5>
            <small class="text-muted">Period: ${formatMonth(data.period?.date_from)} to ${formatMonth(data.period?.date_to)}</small>
        </div>
        <div class="card-body">`;

    // Handle different response structures
    const itemsData = data.results || data;
    
    Object.keys(itemsData).forEach(item => {
        if (item === 'period') return; // Skip period info

        const itemData = itemsData[item];
        if (!itemData.predictions) return; // Skip if no predictions

        // Calculate total predicted quantity (rounded and non-negative)
        const totalPredicted = itemData.predictions.reduce((sum, p) => sum + Math.max(0, Math.round(p.predicted_quantity)), 0);

        html += `
            <div class="mb-4">
                <h6 class="d-flex justify-content-between align-items-center">
                    <span>${item}</span>
                    <span class="badge bg-info">Total: ${totalPredicted.toLocaleString()}</span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Month</th><th>Predicted Quantity</th></tr></thead>
                        <tbody>
        `;

        itemData.predictions.forEach(pred => {
            const qty = Math.max(0, Math.round(pred.predicted_quantity));
            html += `<tr>
                <td>${formatMonth(pred.date)}</td>
                <td><span class="badge bg-primary">${qty.toLocaleString()}</span></td>
            </tr>`;
        });

        html += `
                        </tbody>
                    </table>
                </div>
        `;

        // Add stock information for each item
        if (itemData.stock_info) {
            html += generateMonthlyStockInfoTable(itemData.stock_info, item);
        }

        html += '</div>';
    });

    html += '</div></div>';
    resultsDiv.innerHTML = html;
}

// Generate monthly stock info card
function generateMonthlyStockInfoCard(stockInfo, itemName) {
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
                <h6 class="mb-0">📊 Monthly Stock Analysis for ${itemName}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong>Current Stock:</strong></span>
                            <span class="badge bg-primary">${stockInfo.current_stock.toLocaleString()} units</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><strong>Months Until Depletion:</strong></span>
                            <span class="badge bg-${warningColor}">
                                ${stockInfo.months_until_depletion ? stockInfo.months_until_depletion + ' months' : 'Safe'}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Estimated Depletion Date:</strong></span>
                            <span class="badge bg-${warningColor}">
                                ${stockInfo.depletion_date ? new Date(stockInfo.depletion_date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long' }) : 'N/A'}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-${warningColor} mb-0">
                            <strong>Monthly Stock Status:</strong><br>
                            ${stockInfo.message}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Generate monthly stock info table for all items view
function generateMonthlyStockInfoTable(stockInfo, itemName) {
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
            <h6 class="text-muted mb-2">📊 Monthly Stock Analysis</h6>
            <div class="row">
                <div class="col-6">
                    <small><strong>Current Stock:</strong> <span class="badge bg-primary">${stockInfo.current_stock.toLocaleString()}</span></small>
                </div>
                <div class="col-6">
                    <small><strong>Months Until Depletion:</strong> 
                        <span class="badge bg-${warningColor}">
                            ${stockInfo.months_until_depletion ? stockInfo.months_until_depletion + ' months' : 'Safe'}
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

// Updated chart function for monthly data
function createChartAll(data) {
    const ctx = document.getElementById('forecast-chart').getContext('2d');

    if (currentChart) {
        currentChart.destroy();
    }

    // Handle different response structures
    const itemsData = data.results || data;
    
    // Filter out non-item keys (like 'period')
    const itemKeys = Object.keys(itemsData).filter(key => 
        key !== 'period' && 
        itemsData[key].predictions && 
        Array.isArray(itemsData[key].predictions)
    );

    if (itemKeys.length === 0) {
        console.error('No valid item data found');
        return;
    }

    // Get dates from first valid item
    const firstItemKey = itemKeys[0];
    const labels = itemsData[firstItemKey].predictions.map(p => {
        const date = new Date(p.date);
        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short' });
    });

    // Create datasets per item (only for valid items)
    const colors = [
        'rgb(75, 192, 192)', 'rgb(255, 99, 132)', 'rgb(255, 206, 86)',
        'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(255, 159, 64)',
        'rgb(201, 203, 207)'
    ];

    const datasets = itemKeys.map((item, index) => {
        const color = colors[index % colors.length];
        return {
            label: item,
            data: itemsData[item].predictions.map(p => Math.max(0, Math.round(p.predicted_quantity))),
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
                    text: 'Monthly Sales Forecast for All Items'
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Monthly Quantity' }
                },
                x: {
                    title: { display: true, text: 'Month' }
                }
            }
        }
    });

    document.getElementById('chart-section').style.display = 'block';
}

// Updated single chart function for monthly data
function createChart(data) {
    const ctx = document.getElementById('forecast-chart').getContext('2d');

    if (currentChart) {
        currentChart.destroy();
    }

    const chartData = data[0];

    const labels = chartData.predictions.map(p => {
        const date = new Date(p.date);
        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short' });
    });

    currentChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Monthly Predicted Sales',
                data: chartData.predictions.map(p => Math.max(0, Math.round(p.predicted_quantity))),
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
                    text: `Monthly Sales Forecast for ${chartData.item_name}`
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Monthly Quantity' }
                },
                x: {
                    title: { display: true, text: 'Month' }
                }
            }
        }
    });

    document.getElementById('chart-section').style.display = 'block';
}

// Updated used periods info for monthly periods
function showUsedPeriodsInfo(itemName) {
    const existingInfo = document.getElementById('used-periods-info');
    if (existingInfo) existingInfo.remove();
    
    if (!usedPeriods[itemName] || usedPeriods[itemName].length === 0) return;
    
    const formatMonth = (dateStr) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
    };
    
    const infoDiv = document.createElement('div');
    infoDiv.id = 'used-periods-info';
    infoDiv.className = 'alert alert-info mt-2';
    infoDiv.innerHTML = `
        <strong>Periode bulanan yang sudah digunakan untuk ${itemName}:</strong>
        <ul class="mb-0 mt-2">
            ${usedPeriods[itemName].map(period => 
                `<li>${formatMonth(period.tanggal_dari)} sampai ${formatMonth(period.tanggal_sampai)}</li>`
            ).join('')}
        </ul>
    `;
    
    const controlPanel = document.querySelector('.card-body');
    controlPanel.appendChild(infoDiv);
}

// Utility functions remain the same
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

function removeWarnings() {
    const warnings = document.querySelectorAll('#used-periods-info');
    warnings.forEach(warning => warning.remove());
}

function updateDateInputs() {
    const item = document.getElementById('item-select').value;
    
    // Clear previous warnings
    removeWarnings();
    
    if (usedPeriods[item] && usedPeriods[item].length > 0) {
        showUsedPeriodsInfo(item);
    }
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