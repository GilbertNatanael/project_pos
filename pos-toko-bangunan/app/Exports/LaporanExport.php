<?php

namespace App\Exports;

use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;
    protected $data;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
        $this->prepareData();
    }

    private function prepareData()
    {
        $query = Transaksi::with(['detailTransaksi.barang']);

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('tanggal_waktu', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('tanggal_waktu', '<=', $this->filters['end_date']);
        }
        if (!empty($this->filters['keyword'])) {
            $keyword = $this->filters['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('id_transaksi', 'like', "%$keyword%")
                  ->orWhere('note', 'like', "%$keyword%");
            });
        }
        if (!empty($this->filters['metode'])) {
            $query->where('metode_pembayaran', $this->filters['metode']);
        }
        if (!empty($this->filters['harga_min'])) {
            $query->where('total_harga', '>=', $this->filters['harga_min']);
        }
        if (!empty($this->filters['harga_max'])) {
            $query->where('total_harga', '<=', $this->filters['harga_max']);
        }

        $this->data = $query->orderBy('tanggal_waktu', 'desc')->get();
    }

    public function array(): array
    {
        $exportData = [];
        $totalKeseluruhan = 0;

        foreach ($this->data as $transaksi) {
            // Header transaksi
            $exportData[] = [
                'ID Transaksi: ' . $transaksi->id_transaksi,
                'Tanggal: ' . \Carbon\Carbon::parse($transaksi->tanggal_waktu)->format('d/m/Y H:i'),
                'Metode: ' . $transaksi->metode_pembayaran,
                'Total: Rp ' . number_format($transaksi->total_harga, 0, ',', '.'),
                'Catatan: ' . ($transaksi->note ?: '-'),
                '', '', ''
            ];

            // Sub-header untuk detail barang
            $exportData[] = [
                '', 'Nama Barang', 'Jumlah', 'Harga Satuan', 'Subtotal', '', '', ''
            ];

            // Detail barang
            foreach ($transaksi->detailTransaksi as $detail) {
                $hargaSatuan = $detail->jumlah > 0 ? $detail->subtotal / $detail->jumlah : 0;
                $exportData[] = [
                    '',
                    $detail->barang->nama_barang ?? 'Barang Tidak Ditemukan',
                    $detail->jumlah,
                    'Rp ' . number_format($hargaSatuan, 0, ',', '.'),
                    'Rp ' . number_format($detail->subtotal, 0, ',', '.'),
                    '', '', ''
                ];
            }

            // Baris kosong sebagai pemisah
            $exportData[] = ['', '', '', '', '', '', '', ''];
            
            $totalKeseluruhan += $transaksi->total_harga;
        }

        // Summary total
        $exportData[] = ['', '', '', '', '', '', '', ''];
        $exportData[] = ['RINGKASAN LAPORAN', '', '', '', '', '', '', ''];
        $exportData[] = ['Total Transaksi:', count($this->data), '', '', '', '', '', ''];
        $exportData[] = ['Total Keseluruhan:', 'Rp ' . number_format($totalKeseluruhan, 0, ',', '.'), '', '', '', '', '', ''];

        return $exportData;
    }

    public function headings(): array
    {
        return [
            'LAPORAN TRANSAKSI DETAIL',
            'Periode: ' . ($this->filters['start_date'] ?? 'Semua') . ' s/d ' . ($this->filters['end_date'] ?? 'Semua'),
            'Dicetak: ' . now()->format('d/m/Y H:i'),
            '',
            '',
            '',
            '',
            ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        // Style untuk header utama
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]
        ]);

        // Style untuk info periode
        $sheet->getStyle('A2:H3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E7E6E6']
            ]
        ]);

        // Style untuk header transaksi (baris yang dimulai dengan "ID Transaksi:")
        for ($row = 5; $row <= $lastRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            
            if (strpos($cellValue, 'ID Transaksi:') === 0) {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9EAD3']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            }
            
            // Style untuk sub-header barang
            if ($cellValue === '' && $sheet->getCell('B' . $row)->getValue() === 'Nama Barang') {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F4F4F4']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            }
            
            // Style untuk detail barang
            if ($cellValue === '' && $sheet->getCell('B' . $row)->getValue() !== '' && 
                $sheet->getCell('B' . $row)->getValue() !== 'Nama Barang' &&
                strpos($sheet->getCell('A' . $row)->getValue(), 'RINGKASAN') === false) {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);
            }
            
            // Style untuk ringkasan
            if (strpos($cellValue, 'RINGKASAN') === 0 || strpos($cellValue, 'Total') === 0) {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFE599']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THICK,
                        ],
                    ],
                ]);
            }
        }

        // Merge cells untuk header
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 10,
            'D' => 15,
            'E' => 15,
            'F' => 10,
            'G' => 10,
            'H' => 10,
        ];
    }

    public function title(): string
    {
        return 'Laporan Transaksi Detail';
    }
}