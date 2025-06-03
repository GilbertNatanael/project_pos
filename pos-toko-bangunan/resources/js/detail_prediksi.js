// Detail Prediksi JavaScript
class DetailPrediksi {
    constructor() {
        this.charts = [];
        this.chartData = null;
    }

    // Format angka berdasarkan satuan
    formatNumber(value, satuan) {
        if (!value && value !== 0) return null;
        
        if (satuan && satuan.toUpperCase() === 'KG') {
            return parseFloat(value).toFixed(1);
        } else {
            return Math.round(value);
        }
    }

    // Initialize the detail prediksi functionality
    init(chartDataFromServer) {
        this.chartData = chartDataFromServer;
        this.initializeCharts();
        this.calculateAccuracySummary(this.chartData);
    }

    // Initialize charts for each item
    initializeCharts() {
        this.chartData.forEach((item, index) => {
            const ctx = document.getElementById(`chart-${index}`).getContext('2d');
            
            const labels = item.prediksi_data.map(d => {
                const date = new Date(d.tanggal);
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
            });
            
            const prediksiValues = item.prediksi_data.map(d => parseFloat(d.jumlah));
            const aktualValues = item.aktual_data.map(d => d.jumlah ? parseFloat(d.jumlah) : null);
            
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Prediksi',
                        data: prediksiValues,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        fill: false
                    }, {
                        label: 'Data Aktual',
                        data: aktualValues,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        fill: false,
                        spanGaps: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: `Prediksi vs Aktual - ${item.nama_item}`
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label || '';
                                    const value = context.parsed.y;
                                    const formattedValue = this.formatNumber(value, item.satuan_barang);
                                    const satuan = item.satuan_barang || 'unit';
                                    return `${label}: ${formattedValue} ${satuan}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: `Jumlah (${item.satuan_barang || 'unit'})`
                            },
                            ticks: {
                                callback: (value) => {
                                    return this.formatNumber(value, item.satuan_barang);
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Tanggal'
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 6
                        }
                    }
                }
            });
            
            this.charts.push(chart);
            
            // Create data table for this item
            this.createDataTable(item, index);
        });
    }

    // Create data table for comparison
    createDataTable(item, index) {
        const container = document.getElementById(`data-table-${index}`);
        const satuan = item.satuan_barang || 'unit';
        let tableHTML = `<table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Prediksi (${satuan})</th>
                    <th>Aktual (${satuan})</th>
                </tr>
            </thead>
            <tbody>`;
        
        item.prediksi_data.forEach((pred, i) => {
            const aktual = item.aktual_data[i];
            const prediksiFormatted = this.formatNumber(pred.jumlah, satuan);
            const aktualText = aktual.jumlah !== null ? this.formatNumber(aktual.jumlah, satuan) : 'Belum ada';
            const statusClass = aktual.jumlah !== null ? 'text-success' : 'text-muted';
            
            tableHTML += `
                <tr>
                    <td><small>${new Date(pred.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })}</small></td>
                    <td><small>${prediksiFormatted}</small></td>
                    <td><small class="${statusClass}">${aktualText}</small></td>
                </tr>
            `;
        });
        
        tableHTML += '</tbody></table>';
        container.innerHTML = tableHTML;
    }

    // Calculate and display accuracy summary
    calculateAccuracySummary(chartData) {
        let totalPredictions = 0;
        let totalActual = 0;
        let availableDataPoints = 0;
        let accuratePoints = 0;
        
        chartData.forEach(item => {
            item.prediksi_data.forEach((pred, i) => {
                const aktual = item.aktual_data[i];
                totalPredictions++;
                
                if (aktual.jumlah !== null) {
                    availableDataPoints++;
                    totalActual += parseFloat(aktual.jumlah);
                    
                    // Calculate accuracy with 20% tolerance
                    const predValue = parseFloat(pred.jumlah);
                    const aktualValue = parseFloat(aktual.jumlah);
                    const tolerance = predValue * 0.2;
                    
                    if (Math.abs(predValue - aktualValue) <= tolerance) {
                        accuratePoints++;
                    }
                }
            });
        });
        
        const accuracyPercentage = availableDataPoints > 0 ? (accuratePoints / availableDataPoints * 100).toFixed(1) : 0;
        const dataAvailability = (availableDataPoints / totalPredictions * 100).toFixed(1);
        
        const summaryHTML = `
            <div class="row text-center">
                <div class="col-4">
                    <h4 class="text-primary">${availableDataPoints}/${totalPredictions}</h4>
                    <small class="text-muted">Data Tersedia</small>
                </div>
                <div class="col-4">
                    <h4 class="text-success">${accuracyPercentage}%</h4>
                    <small class="text-muted">Akurasi</small>
                </div>
                <div class="col-4">
                    <h4 class="text-warning">${dataAvailability}%</h4>
                    <small class="text-muted">Kelengkapan Data</small>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <small class="text-muted">
                    Akurasi dihitung dengan toleransi ±20% dari nilai prediksi
                </small>
            </div>
        `;
        
        document.getElementById('accuracy-summary').innerHTML = summaryHTML;
    }

    // Destroy all charts (useful for cleanup)
    destroyCharts() {
        this.charts.forEach(chart => {
            if (chart) {
                chart.destroy();
            }
        });
        this.charts = [];
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if chartData is available from the server
    if (typeof window.chartDataFromServer !== 'undefined') {
        const detailPrediksi = new DetailPrediksi();
        detailPrediksi.init(window.chartDataFromServer);
    } else {
        console.warn('Chart data not found. Make sure chartDataFromServer is available in window object.');
    }
});