<?php

namespace App\Http\Controllers;

use App\Models\TecnicoFirma;
use Illuminate\Http\Request;

class TecnicoFirmaController extends Controller
{
    public function viewIndex()
    {
        return view('technical.signature');
    }

    /**
     * Listar todas las firmas (puedes filtrar por estado si se desea)
     */
    public function index(Request $request)
    {
        $estado = $request->get('estado', '1'); // activo por defecto

        $query = TecnicoFirma::with('tecnico'); // ← importante para incluir el nombre del técnico

        if ($estado === '1') {
            $query->where('tecnico_firma_estado', 1);
        } elseif ($estado === '0') {
            $query->where('tecnico_firma_estado', 0);
        }

        $firmas = $query->orderBy('tecnico_firma_id', 'desc')->get();

        return response()->json($firmas);
    }


    /**
     * Crear nueva firma
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tecnico_firma_nombre' => 'required|string|max:255',
            'tecnico_firma_ruta' => 'nullable|file', // sin restricción de mime
            'tecnico_firma_clave'  => 'required|string|max:255',
            'fecha_emision'        => 'required|date',
            'fecha_expiracion'     => 'required|date|after_or_equal:fecha_emision',
            'tecnico_id'           => 'required|exists:tecnico,tecnico_id',
        ]);

        if ($request->hasFile('tecnico_firma_ruta')) {
            $archivo = $request->file('tecnico_firma_ruta');

            // Opcional: usa un nombre personalizado
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();

            // Guarda el archivo en 'storage/app/public/firmas'
            $ruta = $archivo->storeAs('firmas', $nombreArchivo, 'public');

            $validated['tecnico_firma_ruta'] = $ruta;
        }


        $firma = TecnicoFirma::create(array_merge($validated, [
            'tecnico_firma_estado' => 1
        ]));

        return response()->json(['message' => 'Firma creada correctamente', 'firma' => $firma]);
    }

    public function show($id)
    {
        $firma = TecnicoFirma::with('tecnico')->findOrFail($id);
        return response()->json($firma);
    }

    public function update(Request $request, $id)
    {
        $firma = TecnicoFirma::findOrFail($id);

        $validated = $request->validate([
            'tecnico_firma_nombre' => 'required|string|max:255',
            'tecnico_firma_ruta' => 'nullable|file', // sin restricción de mime
            'tecnico_firma_clave'  => 'required|string|max:255',
            'fecha_emision'        => 'required|date',
            'fecha_expiracion'     => 'required|date|after_or_equal:fecha_emision',
            'tecnico_id'           => 'required|exists:tecnico,tecnico_id',
        ]);

        if ($request->hasFile('tecnico_firma_ruta')) {
            $archivo = $request->file('tecnico_firma_ruta');

            // Opcional: usa un nombre personalizado
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();

            // Guarda el archivo en 'storage/app/public/firmas'
            $ruta = $archivo->storeAs('firmas', $nombreArchivo, 'public');

            $validated['tecnico_firma_ruta'] = $ruta;
        }

        $firma->update($validated);

        return response()->json(['message' => 'Firma actualizada', 'firma' => $firma]);
    }


    /**
     * Soft delete lógico cambiando estado
     */
    public function destroy($id)
    {
        $firma = TecnicoFirma::findOrFail($id);

        $firma->tecnico_firma_estado = $firma->tecnico_firma_estado ? 0 : 1;
        $firma->save();

        $mensaje = $firma->tecnico_firma_estado ? 'Firma activada' : 'Firma desactivada';

        return response()->json(['message' => $mensaje, 'firma' => $firma]);
    }
}
