<?php

namespace App\Exports;

use App\Models\ActivityTms;
use App\Models\ItemMachine;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PMScheduleReport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithDrawings

{
    protected $month;
    protected $data;

    public function __construct($month, $data)
    {
        $this->month = $month;
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            ['PM SCHEDULE - FY ' . date('Y')],
            [],
            [],
            ['NO', 'NAMA MESIN', 'NOMOR MESIN', 'LOKASI', 'ACT / Month', '1', '2', '3', '4'],
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row['name'],
            $row['code'],
            $row['location'],
            $row['act_per_month'],
            $row['weeks'][0]['total'] > 0 ? '✔' : '', // Week 1
            $row['weeks'][1]['total'] > 0 ? '✔' : '', // Week 2
            $row['weeks'][2]['total'] > 0 ? '✔' : '', // Week 3
            $row['weeks'][3]['total'] > 0 ? '✔' : '', // Week 4
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Judul besar
        $sheet->mergeCells('A1:C2');
        $sheet->setCellValue('A1', 'PM SCHEDULE - FY 2025');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Header merges
        $sheet->mergeCells('A3:A4');
        $sheet->mergeCells('B3:B4');
        $sheet->mergeCells('C3:C4');
        $sheet->mergeCells('D3:D4');
        $sheet->mergeCells('E3:E4');
        $sheet->mergeCells('F3:I3');

        // Nama bulan
        $monthName = date('F');
        $sheet->setCellValue('F3', $monthName);

        // Isi header
        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Nama Mesin');
        $sheet->setCellValue('C3', 'Nomor Mesin');
        $sheet->setCellValue('D3', 'Lokasi');
        $sheet->setCellValue('E3', "PM IMPROVE By Actual\nACT / Month");
        $sheet->getStyle('E3')->getAlignment()->setWrapText(true);

        // Subheader minggu
        $sheet->setCellValue('F4', 'Week 1');
        $sheet->setCellValue('G4', 'Week 2');
        $sheet->setCellValue('H4', 'Week 3');
        $sheet->setCellValue('I4', 'Week 4');

        // Styling header
        $sheet->getStyle('A3:I4')->getFont()->setBold(true);
        $sheet->getStyle('A3:I4')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF00');

        // 🔥 Semua teks di sheet center horizontal + vertical
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $range = "A1:{$highestCol}{$highestRow}";

        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Auto width
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Tinggi baris
        for ($row = 1; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(30);
        }

        // 🔲 Tambahkan border mulai A3 sampai I(last row)
        $sheet->getStyle("A3:I{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        return [];
    }




    public function registerEvents(): array
    {
        return [];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Company Logo');
        $drawing->setDescription('Logo Perusahaan');
        $drawing->setPath(public_path('images/logoadm.png')); // lokasi file logo di public/images
        $drawing->setHeight(60); // tinggi px
        $drawing->setCoordinates('E1'); // letak gambar

        return $drawing;
    }
}
