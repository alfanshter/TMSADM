<?php

namespace App\Exports;

use App\Models\FawReport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class FawReportExport implements FromCollection, WithHeadings, WithDrawings, WithStyles, WithEvents
{
    private $reports;
    private $drawings = [];

    public function __construct()
    {
        $this->reports = FawReport::with('photos')->get();
    }

    public function collection()
    {
        $data = [];

        foreach ($this->reports as $index => $report) {
            $photos = $report->photos->pluck('photo_path')->toArray();

            foreach ($photos as $photoIndex => $photoPath) {
                if (!$photoPath) continue;

                $fullPath = Storage::disk('public')->path($photoPath);
                if (!file_exists($fullPath)) continue;

                $drawing = new Drawing();
                $drawing->setName('Photo ' . ($photoIndex + 1));
                $drawing->setPath($fullPath);

                // 🔧 Ukuran foto
                $drawing->setWidth(130);
                $drawing->setHeight(90);

                // Baris data mulai dari baris 4
                $rowNumber = $index + 4;

                // Maksimal 2 foto per baris
                $maxPerRow = 2;
                $colIndex = $photoIndex % $maxPerRow;
                $rowOffset = floor($photoIndex / $maxPerRow);

                // 📍 Semua gambar tetap di kolom E
                $drawing->setCoordinates('E' . $rowNumber);

                // 🔧 Posisi horizontal
                $baseOffsetX = 40; // posisi awal biar agak ke tengah
                $gapBetween = 170; // jarak antar foto
                $drawing->setOffsetX($baseOffsetX + ($colIndex * $gapBetween));

                // 🔧 Posisi vertikal (baris baru)
                $baseOffsetY = 5;
                $gapRow = 100;
                $drawing->setOffsetY($baseOffsetY + ($rowOffset * $gapRow));

                $this->drawings[] = $drawing;
            }

            $data[] = [
                'ID'          => $report->id,
                'Description' => strip_tags($report->description),
                'Date'        => $report->date,
                'Result'      => $report->result,
                'Photos'      => '',
                'Created At'  => $report->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            ['FAW REPORT'], // Judul besar
            [], // Baris kosong
            ['ID', 'Description', 'Date', 'Result', 'Photos', 'Created At'],
        ];
    }

    public function drawings()
    {
        // 🖼 Logo ADM kanan atas
        $logo = new Drawing();
        $logo->setName('ADM Logo');
        $logo->setDescription('Logo ADM');
        $logo->setPath(public_path('images/logoadm.png'));
        $logo->setHeight(45);
        $logo->setCoordinates('E1');
        $logo->setOffsetX(80);
        $logo->setOffsetY(-2);

        return array_merge([$logo], $this->drawings);
    }

    public function styles(Worksheet $sheet)
    {
        // 🧭 Judul besar
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'FAW REPORT');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // 🟦 Header tabel
        $sheet->getStyle('A3:F3')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF305496');
        $sheet->getStyle('A3:F3')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A3:F3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // 📏 Lebar kolom
        foreach (['A', 'B', 'C', 'D', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('E')->setWidth(60);

        // 📐 Atur tinggi baris sesuai jumlah foto
        $highestRow = $sheet->getHighestRow();
        for ($row = 4; $row <= $highestRow; $row++) {
            $reportIndex = $row - 4;
            if (!isset($this->reports[$reportIndex])) continue;

            $photosCount = count($this->reports[$reportIndex]->photos ?? []);
            $rowsNeeded = ceil($photosCount / 2); // karena 2 foto per baris
            $sheet->getRowDimension($row)->setRowHeight(95 * max(1, $rowsNeeded));
        }

        // 🧱 Border
        $sheet->getStyle("A3:F{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // 🎯 Rata tengah semua kecuali kolom B
        $sheet->getStyle("A1:F{$highestRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // 📜 Kolom B (Description)
        $sheet->getStyle("B4:B{$highestRow}")->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_TOP);

        return [];
    }

    public function registerEvents(): array
    {
        return [];
    }
}