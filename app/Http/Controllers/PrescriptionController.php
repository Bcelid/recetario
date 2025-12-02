<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecetaLote;
use App\Models\Receta;
use App\Models\Cliente;
use App\Models\Dosificacion;
use App\Models\RecetaProducto;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;

use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Font\NotoSans;

use TCPDF;
use setasign\Fpdi\Tcpdf\Fpdi;


class PrescriptionController extends Controller
{
    public function viewCreate()
    {
        return view('prescription.newprescription'); // Ajusta a la ruta de tu vista para crear producto
    }
    public function viewCreatev1()
    {
        return view('prescription.newprescriptionv1'); // Ajusta a la ruta de tu vista para crear producto
    }

    public function viewPrescriptionLote()
    {
        return view('prescription.list_prescription'); // Ajusta a la ruta de tu vista
    }


    public function getLotesData(Request $request)
    {
        $estado = $request->get('estado', 'all');
        $tecnico_id = $request->get('tecnico_id', 'all');
        $almacen_id = $request->get('almacen_id', 'all');

        $query = RecetaLote::with(['tecnico', 'almacen']);

        if ($estado !== 'all') {
            $query->where('receta_lote_estado', $estado);
        }

        if ($tecnico_id !== 'all') {
            $query->where('tecnico_id', $tecnico_id);
        }

        if ($almacen_id !== 'all') {
            $query->where('almacen_id', $almacen_id);
        }

        $lotes = $query->orderBy('receta_lote_id', 'desc')->get();

        $data = $lotes->map(function ($lote) {
            return [
                'receta_lote_id' => $lote->receta_lote_id,
                'receta_tipo' => $lote->receta_tipo,
                'fecha_creacion' => $lote->fecha_creacion,
                'receta_lote_firmado' => $lote->receta_lote_firmado,
                'receta_lote_estado' => $lote->receta_lote_estado,
                'receta_lote_fecha_envio' => $lote->receta_lote_fecha_envio,
                'receta_lote_path' => $lote->receta_lote_path,
                'almacen_nombre' => $lote->almacen->almacen_nombre ?? 'N/A',
                'tecnico_nombre' => $lote->tecnico
                    ? $lote->tecnico->tecnico_nombre . ' ' . $lote->tecnico->tecnico_apellido
                    : 'N/A',
            ];
        });

        return response()->json($data);
    }


    public function store(Request $request)
    {
        // Validaciones básicas
        $request->validate([
            'tecnico_id' => 'required|exists:tecnico,tecnico_id',
            'almacen_id' => 'required|exists:almacen,almacen_id',
            'receta_tipo' => 'required|in:0,1',
            'fecha_creacion' => 'required|date',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:producto,producto_id',
            'productos.*.producto_cantidad' => 'required|numeric|min:1',
            'productos.*.fecha_emision' => 'required|date',
            'productos.*.recetas' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            // Crear el lote de receta
            $recetaLote = RecetaLote::create([
                'tecnico_id' => $request->tecnico_id,
                'almacen_id' => $request->almacen_id,
                'receta_tipo' => $request->receta_tipo,
                'fecha_creacion' => $request->fecha_creacion,
                'receta_lote_estado' => 1,
                'receta_lote_path' => '',
                'receta_lote_firmado' => 0,
                'receta_lote_enviado' => 0,
                'receta_lote_fecha_envio' => null,
                'receta_lote_ultimo_envio' => null,
            ]);

            // Obtener clientes del almacén
            $clientes = Cliente::where('cliente_almacen_id', $request->almacen_id)
                ->where('cliente_estado', 1)
                ->pluck('cliente_id')
                ->toArray();


            if (empty($clientes)) {
                return response()->json(['error' => 'No hay clientes asociados al almacén.'], 400);
            }

            // Preparar secuencias
            $recetaSecuencias = [];

            foreach ($request->productos as $producto) {
                $productoId = $producto['producto_id'];
                $cantidad = floatval($producto['producto_cantidad']);
                $recetas = intval($producto['recetas']);
                $fechaEmision = $producto['fecha_emision'];

                // Obtener dosificaciones del producto
                $dosificaciones = Dosificacion::where('producto_id', $productoId)->pluck('dosificacion_id')->toArray();

                if (empty($dosificaciones)) {
                    return response()->json(['error' => "El producto ID $productoId no tiene dosificaciones asociadas."], 400);
                }

                // Reparto de cantidades por receta
                $cantidadesPorReceta = $this->repartirCantidad($cantidad, $recetas);

                $dosificacionesUsadas = [];

                foreach ($cantidadesPorReceta as $cant) {
                    // Cliente aleatorio
                    $clienteId = $clientes[array_rand($clientes)];

                    // Dosificación aleatoria que no se repita
                    $dosificacionesDisponibles = array_diff($dosificaciones, $dosificacionesUsadas);

                    if (empty($dosificacionesDisponibles)) {
                        // Si ya se usaron todas, reiniciamos el ciclo
                        $dosificacionesUsadas = [];
                        $dosificacionesDisponibles = $dosificaciones;
                    }

                    $dosificacionId = $dosificacionesDisponibles[array_rand($dosificacionesDisponibles)];
                    $dosificacionesUsadas[] = $dosificacionId;

                    // Obtener número de receta secuencial
                    $keySecuencia = $request->almacen_id . '_' . $request->receta_tipo;
                    if (!isset($recetaSecuencias[$keySecuencia])) {
                        $ultimaReceta = Receta::whereHas('recetaLote', function ($q) use ($request) {
                            $q->where('almacen_id', $request->almacen_id)
                                ->where('receta_tipo', $request->receta_tipo);
                        })->orderBy('receta_numero', 'desc')->first();

                        $recetaSecuencias[$keySecuencia] = $ultimaReceta ? $ultimaReceta->receta_numero + 1 : 1;
                    } else {
                        $recetaSecuencias[$keySecuencia]++;
                    }

                    // Crear receta
                    Receta::create([
                        'receta_lote_id' => $recetaLote->receta_lote_id,
                        'cliente_id' => $clienteId,
                        'producto_id' => $productoId,
                        'dosificacion_id' => $dosificacionId,
                        'producto_cantidad' => $cant,
                        'fecha_emision' => $fechaEmision,
                        'receta_numero' => $recetaSecuencias[$keySecuencia],
                    ]);
                }
            }

            DB::commit();

            // Generar PDF del lote agrícola automáticamente
            if ($request->receta_tipo == 0) { // solo para agrícolas
                $recetaLote = RecetaLote::with([
                    'almacen.propietario',
                    'tecnico',
                    'recetas.producto.formulacion',
                    'recetas.producto.ingredientes.ingredienteActivo',
                    'recetas.producto.unidadMedida',
                    'recetas.dosificacion.cultivo',
                    'recetas.dosificacion.maleza',
                    'recetas.dosificacion.subespecie',
                    'recetas.dosificacion.unidadMedidaDosificacion',
                    'recetas.cliente'
                ])->find($recetaLote->receta_lote_id);

                $pdf = Pdf::loadView('prescription.agricolas-imprimir', compact('recetaLote'))
                    ->setPaper('A4', 'portrait');



                // Guardar en storage/app/public/recetas/lote_123.pdf
                $path = 'recetas/lote_' . $recetaLote->receta_lote_id . '.pdf';
                // Guardar en storage/app/public/recetas/
                Storage::disk('public')->put($path, $pdf->output());

                // (opcional) guardar ruta en la BD
                $recetaLote->receta_lote_path = $path;
                $recetaLote->save();
            }

            if ($request->receta_tipo == 1) { // receta veterinaria
                $recetaLote = RecetaLote::with([
                    'almacen.propietario',
                    'tecnico',
                    'recetas.producto.ingredientes.ingredienteActivo',
                    'recetas.dosificacion.subespecie.especie',
                    'recetas.cliente'
                ])->find($recetaLote->receta_lote_id);

                $pdf = Pdf::loadView('prescription.veterinarias-imprimir', compact('recetaLote'))
                    ->setPaper('A4', 'portrait');

                $path = 'recetas/lote_veterinario_' . $recetaLote->receta_lote_id . '.pdf';

                Storage::disk('public')->put($path, $pdf->output());

                $recetaLote->receta_lote_path = $path;
                $recetaLote->save();
            }



            return response()->json([
                'message' => 'Receta lote generado correctamente.',
                'receta_lote_id' => $recetaLote->receta_lote_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al generar receta lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reparte una cantidad total entre N recetas de forma balanceada.
     */
    private function repartirCantidad($total, $recetas)
    {
        $base = floor($total / $recetas);
        $residuo = $total % $recetas;
        $reparto = [];

        for ($i = 0; $i < $recetas; $i++) {
            $reparto[] = $i < $residuo ? $base + 1 : $base;
        }

        return $reparto;
    }

    public function storev1(Request $request)
    {
        $request->validate([
            'tecnico_id' => 'required|exists:tecnico,tecnico_id',
            'almacen_id' => 'required|exists:almacen,almacen_id',
            'receta_tipo' => 'required|in:0,1',
            'fecha_creacion' => 'required|date',
            'recetas' => 'required|array|min:1',
            'recetas.*.fecha_emision' => 'required|date',
            'recetas.*.productos' => 'required|array|min:1',
            'recetas.*.productos.*.producto_id' => 'required|exists:producto,producto_id',
            'recetas.*.productos.*.producto_cantidad' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {

            // Crear lote
            $recetaLote = RecetaLote::create([
                'tecnico_id' => $request->tecnico_id,
                'almacen_id' => $request->almacen_id,
                'receta_tipo' => $request->receta_tipo,
                'fecha_creacion' => $request->fecha_creacion,
                'receta_lote_estado' => 1,
                'receta_lote_path' => '',
                'receta_lote_firmado' => 0,
                'receta_lote_enviado' => 0,
            ]);

            // Clientes del almacén
            $clientes = Cliente::where('cliente_almacen_id', $request->almacen_id)
                ->where('cliente_estado', 1)
                ->pluck('cliente_id')
                ->toArray();

            if (empty($clientes)) {
                return response()->json(['error' => 'No hay clientes asociados al almacén.'], 400);
            }

            // Control secuencial
            $key = $request->almacen_id . '_' . $request->receta_tipo;
            $ultimaReceta = Receta::whereHas('recetaLote', function ($q) use ($request) {
                $q->where('almacen_id', $request->almacen_id)
                    ->where('receta_tipo', $request->receta_tipo);
            })->orderBy('receta_numero', 'desc')->first();

            $secuencia = $ultimaReceta ? $ultimaReceta->receta_numero + 1 : 1;

            // Guardamos recipientes y recetas para generar PDF luego
            $recetasGeneradas = [];

            foreach ($request->recetas as $recetaInput) {

                $clienteId = $clientes[array_rand($clientes)];
                $numero = $secuencia++;

                // Crear receta
                $receta = Receta::create([
                    'receta_lote_id' => $recetaLote->receta_lote_id,
                    'cliente_id' => $clienteId,
                    'fecha_emision' => $recetaInput['fecha_emision'],
                    'receta_numero' => $numero,
                    'receta_path' => null,
                ]);

                foreach ($recetaInput['productos'] as $productoInput) {

                    $productoId = $productoInput['producto_id'];
                    $cantidad = floatval($productoInput['producto_cantidad']);

                    $producto = Producto::find($productoId);

                    if (!$producto) {
                        throw new \Exception("Producto con ID $productoId no encontrado.");
                    }

                    $dosificaciones = Dosificacion::where('producto_id', $productoId)
                        ->pluck('dosificacion_id')
                        ->toArray();

                    if (empty($dosificaciones)) {
                        throw new \Exception("El producto {$producto->producto_nombre} {$producto->producto_concentracion} no tiene dosificaciones asociadas.");
                    }

                    $dosificacionId = $dosificaciones[array_rand($dosificaciones)];

                    RecetaProducto::create([
                        'receta_id' => $receta->receta_id,
                        'producto_id' => $productoId,
                        'dosificacion_id' => $dosificacionId,
                        'producto_cantidad' => $cantidad,
                    ]);
                }

                $recetasGeneradas[] = $receta;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al generar receta lote: ' . $e->getMessage()
            ], 500);
        }

        /*
        ======================
        GENERAR PDFs INDIVIDUALES
        ======================
        (fuera de la transacción)
    */

        ini_set('memory_limit', '512M');

        foreach ($recetasGeneradas as $receta) {

            $receta->load([
                'cliente',
                'recetaLote.almacen.propietario',
                'recetaLote.tecnico',
                'productos.producto.formulacion',
                'productos.producto.ingredientes.ingredienteActivo',
                'productos.producto.unidadMedida',
                $request->receta_tipo == 0
                    ? 'productos.dosificacion.cultivo'
                    : 'productos.dosificacion.subespecie.especie',
            ]);

            $numero = $receta->receta_numero;
            $tipo = $request->receta_tipo == 0 ? 'agricola' : 'veterinaria';

            $path = "recetas/{$tipo}_{$numero}_lote{$recetaLote->receta_lote_id}.pdf";

            $view = $request->receta_tipo == 0
                ? 'prescription.agricolas-imprimirv2'
                : 'prescription.veterinarias-imprimirv2';

            $pdf = Pdf::loadView($view, [
                'recetaLote' => $recetaLote,
                'receta' => $receta,
            ])->setPaper('A4', 'landscape');

            Storage::disk('public')->put($path, $pdf->output());

            $receta->update(['receta_path' => $path]);
        }

        return response()->json([
            'message' => 'Recetas generadas correctamente.',
            'receta_lote_id' => $recetaLote->receta_lote_id
        ]);
    }




    public function imprimirRecetasAgricolas($loteId)
    {
        $recetaLote = RecetaLote::with([
            'almacen.propietario',
            'tecnico',
            'recetas.producto.formulacion',
            'recetas.producto.ingredientes.ingredienteActivo',
            'recetas.dosificacion.cultivo',
            'recetas.dosificacion.maleza',
            'recetas.dosificacion.subespecie',
            'recetas.dosificacion.unidadMedidaDosificacion',
            'recetas.cliente'
        ])
            ->where('receta_lote_id', $loteId)
            ->where('receta_tipo', 0) // Solo agrícolas
            ->firstOrFail();

        return view('prescription.agricolas-imprimir', compact('recetaLote'));
    }

    public function destroy($id)
    {
        $lote = RecetaLote::findOrFail($id);
        $lote->receta_lote_estado = $lote->receta_lote_estado ? 0 : 1;
        $lote->save();

        $mensaje = $lote->receta_lote_estado ? 'Lote activado' : 'Lote desactivado';

        return response()->json([
            'message' => $mensaje,
            'lote' => $lote
        ]);
    }

    public function firmarLote1(Request $request)
    {
        $id = $request->input('id');
        $recetaLote = RecetaLote::with('tecnico.categoria')->findOrFail($id);
        $tecnico = $recetaLote->tecnico;

        // Verificar que el técnico tenga una firma digital registrada y activa
        $firmaActiva = $tecnico->firmas()
            ->where('tecnico_firma_estado', 1)
            ->orderBy('tecnico_firma_id', 'desc')
            ->first();

        if (!$firmaActiva) {
            return response()->json(['error' => 'El técnico no tiene una firma digital registrada o activa.'], 400);
        }

        // 1. VALIDAR CADUCIDAD DE LA FIRMA
        $fechaExpiracion = Carbon::parse($firmaActiva->fecha_expiracion);
        $fechaActual = Carbon::now();
        $diasHastaExpiracion = $fechaActual->diffInDays($fechaExpiracion, false);

        // 1.1. Si la firma está CADUCADA (400)
        if ($fechaActual->gt($fechaExpiracion)) {
            return response()->json([
                'error' => 'FIRMA CADUCADA',
                'message' => 'La firma digital del técnico ha caducado. Fecha de expiración: ' . $fechaExpiracion->format('d/m/Y'),
                'fecha_expiracion' => $fechaExpiracion->format('d/m/Y'),
                'accion' => 'Actualice la firma digital para poder firmar documentos.'
            ], 400);
        }

        // 1.2. Si la firma está PRÓXIMA A CADUCAR (30 días o menos)
        $alertaCaducidad = null;
        if ($diasHastaExpiracion <= 30) {
            $alertaCaducidad = [
                'tipo' => 'advertencia',
                'mensaje' => 'La firma digital caduca en ' . $diasHastaExpiracion . ' días (' . $fechaExpiracion->format('d/m/Y') . ')',
                'dias_restantes' => $diasHastaExpiracion,
                'fecha_expiracion' => $fechaExpiracion->format('d/m/Y'),
                'accion_recomendada' => 'Renueve la firma digital lo antes posible'
            ];
        }

        // Verificar que los archivos de firma existan
        $certPath = 'file://' . storage_path('app/private/' . $firmaActiva->tecnico_firma_pub);
        $keyPath = 'file://' . storage_path('app/private/' . $firmaActiva->tecnico_firma_key);
        $password = $firmaActiva->tecnico_firma_clave;

        if (!file_exists(str_replace('file://', '', $certPath)) || !file_exists(str_replace('file://', '', $keyPath))) {
            return response()->json(['error' => 'Los archivos de firma digital no existen.'], 400);
        }

        // Generar contenido del QR
        $qrContent = "Cédula: {$tecnico->tecnido_cedula}\n"
            . "Nombre: {$tecnico->tecnico_nombre} {$tecnico->tecnico_apellido}\n"
            . "Fecha de firma: " . Carbon::now()->format('d/m/Y') . "\n"
            . "SENESCYT: {$tecnico->tecnico_senescyt}\n"
            . "Categoría: {$tecnico->categoria->tecnico_categoria_nombre}";

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(200)
            ->margin(10)
            ->build();

        $qrImage = base64_encode($result->getString());
        ini_set('memory_limit', '512M'); // aumenta el límite
        // Cargar relaciones y vista según tipo de receta
        if ($recetaLote->receta_tipo == 0) { // Agrícola
            $recetaLote->load([
                'almacen.propietario',
                'tecnico',
                'recetas.cliente',
                'recetas.productos.producto.formulacion',
                'recetas.productos.producto.ingredientes.ingredienteActivo',
                'recetas.productos.producto.unidadMedida',
                'recetas.productos.dosificacion.cultivo',
                'recetas.productos.dosificacion.maleza',
            ]);

            $domPdf = Pdf::loadView('prescription.agricolas-imprimirv2', compact('recetaLote', 'qrImage'))
                ->setPaper('A4', 'landscape');

            $relativePath = 'recetas/lote_firmado_' . $recetaLote->receta_lote_id . '.pdf';
        } elseif ($recetaLote->receta_tipo == 1) { // Veterinaria
            $recetaLote->load([
                'almacen.propietario',
                'tecnico',
                'recetas.cliente',
                'recetas.productos.producto.formulacion',
                'recetas.productos.producto.ingredientes.ingredienteActivo',
                'recetas.productos.producto.unidadMedida',
                'recetas.productos.dosificacion.subespecie.especie',
            ]);

            $domPdf = Pdf::loadView('prescription.veterinarias-imprimirv2', compact('recetaLote', 'qrImage'))
                ->setPaper('A4', 'landscape');

            $relativePath = 'recetas/lote_firmado_veterinario_' . $recetaLote->receta_lote_id . '.pdf';
        } else {
            return response()->json(['error' => 'Tipo de receta no válido.'], 400);
        }

        // 2. Guardar archivo generado por DomPDF
        Storage::disk('public')->put($relativePath, $domPdf->output());

        // 3. Obtener la ruta completa real del archivo
        $absolutePath = storage_path('app/public/' . $relativePath);

        // 4. Crear instancia de FPDI para firmar el PDF generado
        $fpdiPdf = new Fpdi();

        // 5. Importar archivo existente
        $pageCount = $fpdiPdf->setSourceFile($absolutePath);
        for ($pageNo = 1; $pageNo < $pageCount; $pageNo++) {
            $templateId = $fpdiPdf->importPage($pageNo);
            $fpdiPdf->AddPage("L", 'A4'); // Landscape
            $fpdiPdf->useTemplate($templateId);
        }

        // 6. Configurar la firma digital
        $fpdiPdf->setSignature(
            $certPath,                             // Certificado público (cert.pem)
            $keyPath,                              // Clave privada (key.pem)
            $password,                             // Contraseña
            '',                                    // Cert chain (opcional)
            2,                                     // Cert info
            [
                'Name' => $tecnico->tecnico_nombre . ' ' . $tecnico->tecnico_apellido,
                'Location' => 'Ecuador',
                'Reason' => 'Firma digital de receta profesional',
                'ContactInfo' => 'Cédula: ' . $tecnico->tecnido_cedula,
            ]
        );

        // 7. Guardar el nuevo archivo firmado sobrescribiendo el anterior
        $pdfFirmado = $fpdiPdf->Output('', 'S');
        Storage::disk('public')->put($relativePath, $pdfFirmado);

        // 8. Actualizar la base de datos
        $recetaLote->update([
            'receta_lote_path' => $relativePath,
            'receta_lote_firmado' => 1,
            'receta_lote_fecha_firma' => now(),
            'tecnico_firma_id' => $firmaActiva->tecnico_firma_id,
        ]);

        // 9. Preparar respuesta
        $response = [
            'message' => 'Lote firmado correctamente.',
            'pdf_path' => $relativePath,
            'firma_valida_hasta' => $fechaExpiracion->format('d/m/Y'),
            'dias_restantes_firma' => $diasHastaExpiracion
        ];

        // 10. Agregar alerta de caducidad si es necesario (200 con advertencia)
        if ($alertaCaducidad) {
            $response['alerta_caducidad'] = $alertaCaducidad;
        }

        return response()->json($response);
    }


    public function firmarLote(Request $request)
    {
        $id = $request->input('id');

        // Cargamos lote + técnico + recetas + relaciones necesarias
        $recetaLote = RecetaLote::with([
            'tecnico.categoria',
            'almacen.propietario',
            'recetas.productos.producto.formulacion',
            'recetas.productos.producto.ingredientes.ingredienteActivo',
            'recetas.productos.producto.unidadMedida',
            // relaciones de dosificacion segun tipo (cargamos ambas; la vista escogerá la que use)
            'recetas.productos.dosificacion.cultivo',
            'recetas.productos.dosificacion.subespecie.especie',
            'recetas.cliente'
        ])->findOrFail($id);

        $tecnico = $recetaLote->tecnico;

        // Verificar firma activa
        $firmaActiva = $tecnico->firmas()
            ->where('tecnico_firma_estado', 1)
            ->orderBy('tecnico_firma_id', 'desc')
            ->first();

        if (!$firmaActiva) {
            return response()->json(['error' => 'El técnico no tiene una firma digital registrada o activa.'], 400);
        }

        // Validar caducidad
        $fechaExpiracion = Carbon::parse($firmaActiva->fecha_expiracion);
        $fechaActual = Carbon::now();
        $diasHastaExpiracion = $fechaActual->diffInDays($fechaExpiracion, false);

        if ($fechaActual->gt($fechaExpiracion)) {
            return response()->json([
                'error' => 'FIRMA CADUCADA',
                'message' => 'La firma digital del técnico ha caducado. Fecha de expiración: ' . $fechaExpiracion->format('d/m/Y'),
                'fecha_expiracion' => $fechaExpiracion->format('d/m/Y'),
                'accion' => 'Actualice la firma digital para poder firmar documentos.'
            ], 400);
        }

        // Alerta de caducidad (<= 30 días)
        $alertaCaducidad = null;
        if ($diasHastaExpiracion <= 30) {
            $alertaCaducidad = [
                'tipo' => 'advertencia',
                'mensaje' => 'La firma digital caduca en ' . $diasHastaExpiracion . ' días (' . $fechaExpiracion->format('d/m/Y') . ')',
                'dias_restantes' => $diasHastaExpiracion,
                'fecha_expiracion' => $fechaExpiracion->format('d/m/Y'),
                'accion_recomendada' => 'Renueve la firma digital lo antes posible'
            ];
        }

        // Verificar archivos de firma
        $certAbs = storage_path('app/private/' . $firmaActiva->tecnico_firma_pub);
        $keyAbs  = storage_path('app/private/' . $firmaActiva->tecnico_firma_key);
        $certPath = 'file://' . $certAbs;
        $keyPath = 'file://' . $keyAbs;
        $password = $firmaActiva->tecnico_firma_clave;

        if (!file_exists($certAbs) || !file_exists($keyAbs)) {
            return response()->json(['error' => 'Los archivos de firma digital no existen.'], 400);
        }

        // Generar QR (se reutiliza para todas las recetas del lote)
        $qrContent = "Cédula: {$tecnico->tecnido_cedula}\n"
            . "Nombre: {$tecnico->tecnico_nombre} {$tecnico->tecnico_apellido}\n"
            . "Fecha de firma: " . Carbon::now()->format('d/m/Y') . "\n"
            . "SENESCYT: {$tecnico->tecnico_senescyt}\n"
            . "Categoría: {$tecnico->categoria->tecnico_categoria_nombre}";

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(200)
            ->margin(10)
            ->build();

        $qrImage = base64_encode($result->getString());

        // Aumentar memoria si hace falta
        ini_set('memory_limit', '512M');

        $tipo = $recetaLote->receta_tipo == 0 ? 'agricola' : 'veterinaria';
        $createdFiles = []; // tracks signed files to cleanup on failure
        $updatedRecetas = []; // para respuesta
        DB::beginTransaction();

        try {
            // Iterar recetas y firmar cada una
            foreach ($recetaLote->recetas as $receta) {
                // Cargar relaciones concretas de la receta (si no están cargadas)
                $receta->load([
                    'cliente',
                    'productos.producto.formulacion',
                    'productos.producto.ingredientes.ingredienteActivo',
                    'productos.producto.unidadMedida',
                    'productos.dosificacion.cultivo',
                    'productos.dosificacion.subespecie.especie',
                ]);

                // Generar el PDF individual (misma vista que al crearla)
                $view = $recetaLote->receta_tipo == 0
                    ? 'prescription.agricolas-imprimirv2'
                    : 'prescription.veterinarias-imprimirv2';

                // Renderizamos el PDF con el QR (la vista debe usar $qrImage)
                $domPdf = Pdf::loadView($view, [
                    'recetaLote' => $recetaLote,
                    'receta' => $receta,
                    'qrImage' => $qrImage,
                ])->setPaper('A4', 'landscape');

                // Ruta del firmado (convención solicitada)
                $relativePath = "recetas_firmadas/{$tipo}_{$receta->receta_numero}_lote{$recetaLote->receta_lote_id}.pdf";

                // Guardamos el PDF "sin firmar" temporalmente en storage (en public), luego lo sobreescribiremos con el firmado.
                Storage::disk('public')->put($relativePath, $domPdf->output());
                $absolutePath = storage_path('app/public/' . $relativePath);

                // Firma digital con FPDI
                $fpdi = new Fpdi();

                // abrir el archivo generado
                $pageCount = $fpdi->setSourceFile($absolutePath);
                for ($pageNo = 1; $pageNo < $pageCount; $pageNo++) {
                    $templateId = $fpdi->importPage($pageNo);
                    $fpdi->AddPage("L", 'A4'); // Landscape
                    $fpdi->useTemplate($templateId);
                }

                // Configurar la firma (asegúrate que la librería FPDI que usas soporta setSignature)
                $fpdi->setSignature(
                    $certPath,
                    $keyPath,
                    $password,
                    '',
                    2,
                    [
                        'Name' => $tecnico->tecnico_nombre . ' ' . $tecnico->tecnico_apellido,
                        'Location' => 'Ecuador',
                        'Reason' => 'Firma digital de receta profesional',
                        'ContactInfo' => 'Cédula: ' . $tecnico->tecnido_cedula,
                    ]
                );

                // Generar PDF firmado en memoria y guardarlo sobrescribiendo el temporal
                $pdfFirmado = $fpdi->Output('', 'S');
                Storage::disk('public')->put($relativePath, $pdfFirmado);

                // Registrar creado para posible limpieza si más adelante falla
                $createdFiles[] = $relativePath;

                // Actualizar campo receta_path con la ruta firmada (relativa en disco public)
                $receta->receta_path = $relativePath;
                $receta->save();

                $updatedRecetas[] = [
                    'receta_id' => $receta->receta_id,
                    'receta_numero' => $receta->receta_numero,
                    'receta_path' => $relativePath
                ];
            }

            // Si todo ok, actualizar recetalote como firmado
            $recetaLote->update([
                'receta_lote_firmado' => 1,
                'receta_lote_fecha_firma' => now(),
                // Si quieres guardar un path del lote firmado, podrías poner null o un resumen.
                // 'receta_lote_path' => 'recetas_firmadas/lote_'.$recetaLote->receta_lote_id.'_firmado.pdf'
            ]);

            DB::commit();

            $response = [
                'message' => 'Todas las recetas firmadas correctamente.',
                'receta_lote_id' => $recetaLote->receta_lote_id,
                'recetas' => $updatedRecetas,
                'firma_valida_hasta' => $fechaExpiracion->format('d/m/Y'),
                'dias_restantes_firma' => $diasHastaExpiracion
            ];

            if ($alertaCaducidad) {
                $response['alerta_caducidad'] = $alertaCaducidad;
            }

            return response()->json($response, 200);
        } catch (\Exception $e) {
            // Rollback DB
            DB::rollBack();

            // Borrar archivos firmados creados en este intento (limpieza)
            foreach ($createdFiles as $f) {
                try {
                    Storage::disk('public')->delete($f);
                } catch (\Exception $ex) {
                    // si no se puede borrar, ignoramos para no tapar el error principal
                }
            }

            return response()->json([
                'error' => 'Error al firmar recetas: ' . $e->getMessage()
            ], 500);
        }
    }
}
