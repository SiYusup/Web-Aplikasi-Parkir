<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Controller;

use UCrazy\AplikasiPengelolaanParkirKendaraan\App\View;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Exception\ValidationException;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Model\ParkirKeluarRequest;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Model\ParkirMasukRequest;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Repository\ParkirRepository;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Service\ParkirService;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Service\SessionService;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ParkirController
{
    private ParkirService $parkirService;
    private ParkirRepository $parkirRepository;
    private SessionService $sessionService;

    public function __construct()
    {
        $this->parkirRepository = new ParkirRepository();
        $this->parkirService = new ParkirService($this->parkirRepository);
        $this->sessionService = new SessionService();
    }

    public function dashboard(): void
    {
        $user = $this->sessionService->current();

        View::render('Home/dashboard', [
            'title' => 'Dashboard - Aplikasi Parkir Kendaraan',
            'user' => $user,
            'stats' => $this->parkirRepository->stats()
        ]);
    }

    public function masuk(): void
    {
        $user = $this->sessionService->current();

        View::render('parkir/masuk', [
            'title' => 'Parkir Masuk - Aplikasi Parkir Kendaraan',
            'user' => $user,
            'tarif' => ParkirService::tarif()
        ]);
    }

    public function postMasuk(): void
    {
        $user = $this->sessionService->current();

        $request = new ParkirMasukRequest();
        $request->nomor_plat = $_POST['nomor_plat'] ?? null;
        $request->jenis_kendaraan = $_POST['jenis_kendaraan'] ?? null;

        try {
            $this->parkirService->masuk($request);
            $this->sessionService->setFlash('success', "Kendaraan " . strtoupper(trim($request->nomor_plat)) . " berhasil tercatat masuk parkir.");
            View::redirect('/parkir-masuk');
        } catch (ValidationException $exception) {
            View::render('parkir/masuk', [
                'title' => 'Parkir Masuk - Aplikasi Parkir Kendaraan',
                'user' => $user,
                'error' => $exception->getMessage(),
                'old' => $_POST
            ]);
        }
    }

    public function keluar(): void
    {
        $user = $this->sessionService->current();

        $cari = $_GET['cari'] ?? null;
        $daftar = [];
        if ($cari !== null && trim($cari) !== '') {
            $daftar = $this->parkirRepository->findAll(null, $cari);
        }

        View::render('parkir/keluar', [
            'title' => 'Parkir Keluar - Aplikasi Parkir Kendaraan',
            'user' => $user,
            'cari' => $cari,
            'daftar' => $daftar,
            'kendaraanParkir' => $this->parkirRepository->findAllActive(),
            'tarif' => ParkirService::tarif()
        ]);
    }

    public function postKeluar(): void
    {
        $request = new ParkirKeluarRequest();
        $request->id = (int) ($_POST['id'] ?? 0);

        try {
            $parkir = $this->parkirService->keluar($request);
            $this->sessionService->setFlash(
                'success',
                "Kendaraan " . $parkir->nomor_plat . " berhasil keluar. Total bayar Rp "
                . number_format((int) $parkir->total_bayar, 0, ',', '.')
            );
        } catch (ValidationException $exception) {
            $this->sessionService->setFlash('error', $exception->getMessage());
        }

        View::redirect('/parkir-keluar');
    }

    public function laporan(): void
    {
        $user = $this->sessionService->current();

        $tanggal = $_GET['tanggal'] ?? null;
        $cari = $_GET['cari'] ?? null;
        $daftar = $this->parkirRepository->findAll($tanggal, $cari);

        $totalPendapatan = 0;
        foreach ($daftar as $parkir) {
            if ($parkir->status === 'selesai') {
                $totalPendapatan += (int) $parkir->total_bayar;
            }
        }

        View::render('parkir/laporan', [
            'title' => 'Laporan Parkir - Aplikasi Parkir Kendaraan',
            'user' => $user,
            'daftar' => $daftar,
            'totalPendapatan' => $totalPendapatan,
            'tanggal' => $tanggal,
            'cari' => $cari
        ]);
    }

    public function laporanPdf(): void
    {
        $tanggal = $_GET['tanggal'] ?? null;
        $cari = $_GET['cari'] ?? null;
        $daftar = $this->parkirRepository->findAll($tanggal, $cari);

        $labelFilter = $this->labelFilter($tanggal, $cari);
        $totalPendapatan = $this->totalPendapatan($daftar);

        $rows = '';
        $no = 1;
        foreach ($daftar as $parkir) {
            $rows .= '<tr>'
                . '<td style="border:1px solid #dee2e6;padding:6px 8px;text-align:center;">' . $no++ . '</td>'
                . '<td style="border:1px solid #dee2e6;padding:6px 8px;">' . htmlspecialchars($parkir->nomor_plat) . '</td>'
                . '<td style="border:1px solid #dee2e6;padding:6px 8px;">' . ($parkir->jenis_kendaraan === 'roda_2' ? 'Motor' : 'Mobil') . '</td>'
                . '<td style="border:1px solid #dee2e6;padding:6px 8px;">' . htmlspecialchars($parkir->waktu_masuk) . '</td>'
                . '<td style="border:1px solid #dee2e6;padding:6px 8px;">' . ($parkir->waktu_keluar ? htmlspecialchars($parkir->waktu_keluar) : '-') . '</td>'
                . '<td style="border:1px solid #dee2e6;padding:6px 8px;text-align:center;">' . ($parkir->status === 'parkir' ? 'Parkir' : 'Selesai') . '</td>'
                . '<td style="border:1px solid #dee2e6;padding:6px 8px;text-align:right;">' . ($parkir->total_bayar !== null ? 'Rp ' . number_format((int) $parkir->total_bayar, 0, ',', '.') : '-') . '</td>'
                . '</tr>';
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="7" style="border:1px solid #dee2e6;padding:6px 8px;text-align:center;">Belum ada data parkir.</td></tr>';
        }

        $html = '<html><head><meta charset="UTF-8">'
            . '<style>'
            . 'body{font-family:Helvetica,Arial,sans-serif;font-size:12px;}'
            . 'h1{text-align:center;margin:0 0 4px;}'
            . '.sub{text-align:center;color:#555;margin-bottom:20px;}'
            . 'table{border-collapse:collapse;width:100%;}'
            . 'th{background:#343a40;color:#fff;border:1px solid #343a40;padding:8px;}'
            . '.total{margin-top:16px;font-weight:bold;text-align:right;}'
            . '</style></head><body>'
            . '<h1>Laporan Parkir Kendaraan</h1>'
            . '<div class="sub">' . htmlspecialchars($labelFilter) . ' | Dicetak: ' . date('d-m-Y H:i:s') . '</div>'
            . '<table>'
            . '<thead><tr><th>#</th><th>Nomor Plat</th><th>Jenis</th><th>Waktu Masuk</th><th>Waktu Keluar</th><th>Status</th><th>Total Bayar</th></tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '<p class="total">Total Pendapatan: Rp ' . number_format($totalPendapatan, 0, ',', '.') . '</p>'
            . '</body></html>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Parkir_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
    }

    public function laporanExcel(): void
    {
        $tanggal = $_GET['tanggal'] ?? null;
        $cari = $_GET['cari'] ?? null;
        $daftar = $this->parkirRepository->findAll($tanggal, $cari);

        $labelFilter = $this->labelFilter($tanggal, $cari);
        $totalPendapatan = $this->totalPendapatan($daftar);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Parkir');

        $sheet->setCellValue('A1', 'Laporan Parkir Kendaraan');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', $labelFilter . ' | Dicetak: ' . date('d-m-Y H:i:s'));
        $sheet->mergeCells('A2:G2');

        $headers = ['No', 'Nomor Plat', 'Jenis', 'Waktu Masuk', 'Waktu Keluar', 'Status', 'Total Bayar (Rp)'];
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('343A40');
        $sheet->getStyle('A4:G4')->getFont()->getColor()->setRGB('FFFFFF');

        $row = 5;
        $no = 1;
        foreach ($daftar as $parkir) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $parkir->nomor_plat);
            $sheet->setCellValue('C' . $row, $parkir->jenis_kendaraan === 'roda_2' ? 'Motor' : 'Mobil');
            $sheet->setCellValue('D' . $row, $parkir->waktu_masuk);
            $sheet->setCellValue('E' . $row, $parkir->waktu_keluar ?? '-');
            $sheet->setCellValue('F' . $row, $parkir->status === 'parkir' ? 'Parkir' : 'Selesai');
            $sheet->setCellValue('G' . $row, $parkir->total_bayar !== null ? (int) $parkir->total_bayar : 0);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'Total Pendapatan');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('G' . $row, $totalPendapatan);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Parkir_' . date('Ymd_His') . '.xlsx';
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function laporanCsv(): void
    {
        $tanggal = $_GET['tanggal'] ?? null;
        $cari = $_GET['cari'] ?? null;
        $daftar = $this->parkirRepository->findAll($tanggal, $cari);

        $totalPendapatan = $this->totalPendapatan($daftar);

        $filename = 'Laporan_Parkir_' . date('Ymd_His') . '.csv';
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // BOM agar Excel Windows membuka UTF-8 dengan benar
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['No', 'Nomor Plat', 'Jenis', 'Waktu Masuk', 'Waktu Keluar', 'Status', 'Total Bayar (Rp)'], ';', '"', '\\');

        $no = 1;
        foreach ($daftar as $parkir) {
            fputcsv($out, [
                $no++,
                $parkir->nomor_plat,
                $parkir->jenis_kendaraan === 'roda_2' ? 'Motor' : 'Mobil',
                $parkir->waktu_masuk,
                $parkir->waktu_keluar ?? '-',
                $parkir->status === 'parkir' ? 'Parkir' : 'Selesai',
                $parkir->total_bayar !== null ? (int) $parkir->total_bayar : 0,
            ], ';', '"', '\\');
        }

        fputcsv($out, ['', '', '', '', '', 'Total Pendapatan', $totalPendapatan], ';', '"', '\\');
        fclose($out);
        exit;
    }

    private function labelFilter($tanggal, $cari): string
    {
        $label = 'Semua Data';
        if ($tanggal !== null && trim((string) $tanggal) !== '') {
            $label = 'Tanggal ' . date('d-m-Y', strtotime((string) $tanggal));
        }
        if ($cari !== null && trim((string) $cari) !== '') {
            $label .= ' - Plat: ' . $cari;
        }
        return $label;
    }

    private function totalPendapatan(array $daftar): int
    {
        $total = 0;
        foreach ($daftar as $parkir) {
            if ($parkir->status === 'selesai') {
                $total += (int) $parkir->total_bayar;
            }
        }
        return $total;
    }
}