<?php

namespace App\Exports;

use App\Models\StockSparepart;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockSparepartsExport implements FromCollection, WithHeadings, WithEvents, WithStyles
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        $counter = 1;
        return StockSparepart::withSum('usages', 'qty')
            ->whereYear('created_at', $this->year)
            ->orderBy('nama_sparepart', 'ASC')
            ->get()
            ->map(function ($item) use (&$counter) {
                return [
                    $counter++,
                    $item->nama_sparepart,
                    $item->type,
                    $item->loc,
                    $item->stok,
                    $item->incoming,
                    $item->usages_sum_qty ?? 0,
                    $item->stok + $item->incoming - ($item->usages_sum_qty ?? 0),
                    $item->remark,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Sparepart',
            'Type',
            'Loc',
            'Stock (pcs)',
            'Incoming',
            'Usage',
            'End Month Stock',
            'Remark',
        ];
    }

    // Tambah styling
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Heading bold
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul besar di atas
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', "LIST KEBUTUHAN BELTING & HOSE ADM Workshop");

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Header warna
                $sheet->getStyle('A2:H2')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'FFFF00'] // kuning
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Set lebar kolom
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}