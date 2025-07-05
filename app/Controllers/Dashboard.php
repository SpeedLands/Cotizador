<?php

namespace App\Controllers;

use App\Models\CotizacionModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Dashboard extends BaseController
{
    public function index()
    {
        $anio = $this->request->getGet('anio') ?? date('Y');
        $mes = $this->request->getGet('mes');

        $model = new CotizacionModel();
        $query = $model->where("YEAR(fecha_evento)", $anio);

        if ($mes) {
            $query->where("MONTH(fecha_evento)", $mes);
        }

        // Datos principales
        $total = $query->countAllResults(false); // no resetea el builder
        $confirmados = $query->where('estado', 'confirmado')->countAllResults(false);

        // KPI de conversión
        $conversion = $total > 0 ? round(($confirmados / $total) * 100, 2) : 0;

        $ingresosModel = new CotizacionModel();
        $ingresos = $ingresosModel
            ->select("DATE_FORMAT(fecha_evento, '%Y-%m') as mes, SUM(presupuesto_total) as total")
            ->groupBy("mes")
            ->orderBy("mes", 'ASC')
            ->findAll();

        $tiposModel = new CotizacionModel();
        $tipos = $tiposModel
            ->select("tipo_evento, COUNT(*) as cantidad")
            ->groupBy("tipo_evento")
            ->findAll();


        // Meta proyectada fija
        $metaMensual = 80000;

        return view('admin/dashboard/index', [
            'total' => $total,
            'confirmados' => $confirmados,
            'conversion' => $conversion,
            'ingresos' => $ingresos,
            'tipos' => $tipos,
            'metaMensual' => $metaMensual,
            'anio' => $anio,
            'mes' => $mes
        ]);
    }

    public function exportarPDF()
    {
        $anio = $this->request->getGet('anio') ?? date('Y');
        $mes = $this->request->getGet('mes');

        $model = new \App\Models\CotizacionModel();
        $query = $model->where("YEAR(fecha_evento)", $anio);
        if ($mes) $query->where("MONTH(fecha_evento)", $mes);

        $data = $query->findAll();

        // Construcción HTML para el PDF
        $html = '<h2 style="text-align:center;">Reporte de Cotizaciones</h2>';
        $html .= '<style>
            table { border-collapse: collapse; font-size: 12px; }
            th, td { border: 1px solid #000; padding: 5px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style>';
        $html .= '<table width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Invitados</th>
                    <th>Presupuesto</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data as $row) {
            $html .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['nombre_cliente']}</td>
                <td>{$row['fecha_evento']}</td>
                <td>{$row['tipo_evento']}</td>
                <td>{$row['numero_invitados']}</td>
                <td>$" . number_format($row['presupuesto_total'], 2) . "</td>
                <td>{$row['estado']}</td>
            </tr>";
        }

        $html .= '</tbody></table>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = "cotizaciones_$anio" . ($mes ? "_$mes" : "") . ".pdf";
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    public function exportarExcel()
    {
        $anio = $this->request->getGet('anio') ?? date('Y');
        $mes = $this->request->getGet('mes');

        $model = new \App\Models\CotizacionModel();
        $query = $model->where("YEAR(fecha_evento)", $anio);
        if ($mes) $query->where("MONTH(fecha_evento)", $mes);

        $data = $query->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->fromArray([
            'ID', 'Cliente', 'Teléfono', 'Fecha Evento', 'Tipo Evento',
            'Invitados', 'Presupuesto Total', 'Estado'
        ], NULL, 'A1');

        // Contenido
        $i = 2;
        foreach ($data as $row) {
            $sheet->setCellValue("A$i", $row['id']);
            $sheet->setCellValue("B$i", $row['nombre_cliente']);
            $sheet->setCellValue("C$i", $row['telefono']);
            $sheet->setCellValue("D$i", $row['fecha_evento']);
            $sheet->setCellValue("E$i", $row['tipo_evento']);
            $sheet->setCellValue("F$i", $row['numero_invitados']);
            $sheet->setCellValue("G$i", $row['presupuesto_total']);
            $sheet->setCellValue("H$i", $row['estado']);
            $i++;
        }

        // Descargar archivo
        $writer = new Xlsx($spreadsheet);
        $filename = "cotizaciones_$anio" . ($mes ? "_$mes" : "") . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

}
