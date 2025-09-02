<?php

namespace App\Exports;

use App\Models\ActivityTMS;
use App\Models\ItemMachine;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PMScheduleReport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents

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
            $row['weeks'][0]['total'], // week 1
            $row['weeks'][1]['total'], // week 2
            $row['weeks'][2]['total'], // week 3
            $row['weeks'][3]['total'], // week 4
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Judul besar
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header (baris 3)
        $sheet->getStyle('A3:I3')->getFont()->setBold(true);
        $sheet->getStyle('A3:I3')->getFill()->setFillType(Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFFF00'); // kuning
        $sheet->getStyle('A3:I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto width
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

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
        $drawing->setCoordinates('A1'); // letak gambar

        return $drawing;
    }
}
