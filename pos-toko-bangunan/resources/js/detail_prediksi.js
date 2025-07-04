// Detail Prediksi JavaScript - Versi Bulanan (Tanpa Akurasi)
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
        this.calculateDataSummary(this.chartData);
    }

    // Initialize charts for each item
    initializeCharts() {
        this.chartData.forEach((item, index) => {
            const ctx = document.getElementById(`chart-${index}`).getContext('2d');
            
            // Format label untuk bulan
            const labels = item.prediksi_data.map(d => {
                const date = new Date(d.tanggal);
                return date.toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
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
                            text: `Prediksi vs Aktual - ${item.nama_item} (Bulanan)`
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
                                text: `Jumlah per Bulan (${item.satuan_barang || 'unit'})`
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
                                text: 'Bulan'
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

    // Create data table for comparison (TANPA AKURASI)
    createDataTable(item, index) {
        const container = document.getElementById(`data-table-${index}`);
        const satuan = item.satuan_barang || 'unit';

        
        let reorderInfoHTML = `
        <div class="mb-2 p-2 bg-light rounded">
            <small>
                <strong>Reorder Point:</strong> ${item.reorder_point} ${satuan}<br>
                <strong>Order Quantity:</strong> ${item.order_quantity} ${satuan}<br>
                <strong>Lead Time Stock:</strong> ${item.lead_time_stock} ${satuan}<br>
                <strong>Safety Stock:</strong> ${item.safety_stock} ${satuan}
            </small>
        </div>
    `;
    
    let tableHTML = reorderInfoHTML + `<table class="table table-sm table-bordered">
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Prediksi (${satuan})</th>
                <th>Aktual (${satuan})</th>
                <th>Selisih (${satuan})</th>
            </tr>
        </thead>
        <tbody>`;
        
        item.prediksi_data.forEach((pred, i) => {
            const aktual = item.aktual_data[i];
            const prediksiFormatted = this.formatNumber(pred.jumlah, satuan);
            const aktualText = aktual.jumlah !== null ? this.formatNumber(aktual.jumlah, satuan) : 'Belum ada';
            const statusClass = aktual.jumlah !== null ? 'text-success' : 'text-muted';
            
            let selisihText = 'Belum ada';
            let selisihClass = 'text-muted';
            
            if (aktual.jumlah !== null) {
                const selisih = Math.abs(parseFloat(pred.jumlah) - parseFloat(aktual.jumlah));
                selisihText = this.formatNumber(selisih, satuan);
                selisihClass = 'text-info';
            }
            
            // Format bulan untuk tampilan
            const bulanText = new Date(pred.tanggal).toLocaleDateString('id-ID', { 
                month: 'short', 
                year: 'numeric' 
            });
            
            tableHTML += `
                <tr>
                    <td><small>${bulanText}</small></td>
                    <td><small>${prediksiFormatted}</small></td>
                    <td><small class="${statusClass}">${aktualText}</small></td>
                    <td><small class="${selisihClass}">${selisihText}</small></td>
                </tr>
            `;
        });
        
        tableHTML += '</tbody></table>';
        container.innerHTML = tableHTML;
    }

    // Calculate and display data summary (TANPA AKURASI)
    calculateDataSummary(chartData) {
        let totalPrediksi = 0;
        let totalAktual = 0;
        let availableDataPoints = 0;
        let totalSelisih = 0;
        
        chartData.forEach(item => {
            item.prediksi_data.forEach((pred, i) => {
                const aktual = item.aktual_data[i];
                
                if (aktual.jumlah !== null) {
                    availableDataPoints++;
                    const predValue = parseFloat(pred.jumlah);
                    const aktualValue = parseFloat(aktual.jumlah);
                    
                    totalPrediksi += predValue;
                    totalAktual += aktualValue;
                    
                    const selisih = Math.abs(predValue - aktualValue);
                    totalSelisih += selisih;
                }
            });
        });
        
        const rataRataSelisih = availableDataPoints > 0 ? (totalSelisih / availableDataPoints).toFixed(1) : 0;
        const selisihTotal = (totalPrediksi - totalAktual).toFixed(1);
        
        const summaryHTML = `
            <div class="row text-center">
                <div class="col-4">
                    <h4 class="text-primary">${availableDataPoints}</h4>
                    <small class="text-muted">Bulan Tersedia</small>
                </div>
                <div class="col-4">
                    <h4 class="text-info">${rataRataSelisih}</h4>
                    <small class="text-muted">Rata-rata Selisih</small>
                </div>
                <div class="col-4">
                    <h4 class="text-warning">${selisihTotal}</h4>
                    <small class="text-muted">Total Selisih</small>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <small class="text-muted">
                    Data prediksi dan aktual dihitung per bulan. Selisih = |Prediksi - Aktual|
                </small>
            </div>
        `;
        
        document.getElementById('accuracy-summary').innerHTML = summaryHTML;
    }
    
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