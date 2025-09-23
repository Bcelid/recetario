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
            'tecnico_firma_clave'  => 'required|string|max:255',
            'tecnico_id'           => 'required|exists:tecnico,tecnico_id',
        ]);

        // Guardar el archivo .p12 en storage
        $archivo = $request->file('tecnico_firma_ruta');
        $nombreBase = time() . '_' . pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $rutaP12 = $archivo->storeAs('firmas', $nombreBase . '.p12', 'private');

        // Construir rutas absolutas
        $rutaP12Absoluta = storage_path('app/private/' . $rutaP12);
        $rutaKey = "firmas/{$nombreBase}.key";
        $rutaCrt = "firmas/{$nombreBase}.crt";
        $rutaKeyAbsoluta = storage_path('app/private/' . $rutaKey);
        $rutaCrtAbsoluta = storage_path('app/private/' . $rutaCrt);

        // Verificar que el archivo existe
        if (!file_exists($rutaP12Absoluta)) {
            return response()->json([
                'message' => "El archivo no existe en la ruta: $rutaP12Absoluta"
            ], 422);
        }

        // PRIMERO: Validar la contraseña del archivo P12
        $validatePasswordCommand = "openssl pkcs12 -in \"$rutaP12Absoluta\" -noout -passin pass:\"{$validated['tecnico_firma_clave']}\" 2>&1";
        $validateOutput = shell_exec($validatePasswordCommand);

        // Si el comando falla, la contraseña es incorrecta
        if (strpos($validateOutput, 'Mac verify error') !== false || strpos($validateOutput, 'invalid password') !== false) {
            // Eliminar el archivo subido
            @unlink($rutaP12Absoluta);

            return response()->json([
                'message' => 'Contraseña incorrecta para el archivo P12'
            ], 422);
        }

        // SEGUNDO: Extraer certificado (.crt)
        $certCommand = "openssl pkcs12 -in \"$rutaP12Absoluta\" -clcerts -nokeys -out \"$rutaCrtAbsoluta\" -passin pass:\"{$validated['tecnico_firma_clave']}\" -passout pass: 2>&1";
        $certOutput = shell_exec($certCommand);

        // TERCERO: Extraer clave privada (.key)
        $keyCommand = "openssl pkcs12 -in \"$rutaP12Absoluta\" -nocerts -out \"$rutaKeyAbsoluta\" -passin pass:\"{$validated['tecnico_firma_clave']}\" -passout pass:\"{$validated['tecnico_firma_clave']}\" 2>&1";
        $keyOutput = shell_exec($keyCommand);

        // Verificar si los archivos se crearon correctamente
        if (!file_exists($rutaCrtAbsoluta) || !file_exists($rutaKeyAbsoluta)) {
            // Limpiar archivos en caso de error
            @unlink($rutaP12Absoluta);
            @unlink($rutaCrtAbsoluta);
            @unlink($rutaKeyAbsoluta);

            return response()->json([
                'message' => 'Error al extraer el certificado o la clave privada. Verifique que la contraseña sea correcta y el archivo no esté dañado.',
                'cert_output' => $certOutput,
                'key_output' => $keyOutput
            ], 422);
        }

        // Leer el certificado para obtener información de validez
        $certContent = file_get_contents($rutaCrtAbsoluta);
        $certResource = openssl_x509_read($certContent);

        if (!$certResource) {
            // Limpiar archivos en caso de error
            @unlink($rutaP12Absoluta);
            @unlink($rutaCrtAbsoluta);
            @unlink($rutaKeyAbsoluta);

            return response()->json([
                'message' => 'Error al leer el certificado extraído. El archivo puede estar corrupto.'
            ], 422);
        }

        // Obtener información del certificado
        $info = openssl_x509_parse($certResource);
        $validoDesde = date('Y-m-d H:i:s', $info['validFrom_time_t']);
        $validoHasta = date('Y-m-d H:i:s', $info['validTo_time_t']);

        // Liberar recurso
        openssl_x509_free($certResource);

        // Verificar si el certificado ha expirado
        if (now()->gt($validoHasta)) {
            // Eliminar archivos creados
            @unlink($rutaP12Absoluta);
            @unlink($rutaCrtAbsoluta);
            @unlink($rutaKeyAbsoluta);

            return response()->json([
                'message' => 'El certificado ha expirado. Expiró en ' . $validoHasta
            ], 422);
        }

        // Guardar en BD las rutas
        $firma = TecnicoFirma::create([
            'tecnico_firma_nombre' => $validated['tecnico_firma_nombre'],
            'tecnico_firma_ruta'   => $rutaP12,   // ruta del .p12
            'tecnico_firma_key'    => $rutaKey,   // ruta del .key
            'tecnico_firma_pub'    => $rutaCrt,   // ruta del .crt
            'tecnico_firma_clave'  => $validated['tecnico_firma_clave'],
            'fecha_emision'        => $validoDesde,
            'fecha_expiracion'     => $validoHasta,
            'tecnico_id'           => $validated['tecnico_id'],
            'tecnico_firma_estado' => 1
        ]);

        return response()->json([
            'message' => 'Firma creada correctamente',
            'firma'   => $firma,
            'validez' => [
                'desde' => $validoDesde,
                'hasta' => $validoHasta
            ]
        ]);
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
            'tecnico_firma_ruta' => 'nullable|file',
            'tecnico_firma_clave'  => 'required|string|max:255',
            'tecnico_id'           => 'required|exists:tecnico,tecnico_id',
            // Nota: Removí fecha_emision y fecha_expiracion ya que se calcularán automáticamente
        ]);

        // Variables para almacenar rutas antiguas (para limpieza)
        $oldP12Path = null;
        $oldKeyPath = null;
        $oldCrtPath = null;

        if ($request->hasFile('tecnico_firma_ruta')) {
            // Guardar archivo temporalmente para procesamiento
            $archivo = $request->file('tecnico_firma_ruta');
            $nombreBase = time() . '_' . pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);

            // Guardar el nuevo archivo .p12
            $rutaP12 = $archivo->storeAs('firmas', $nombreBase . '.p12', 'private');
            $rutaP12Absoluta = storage_path('app/private/' . $rutaP12);

            // Construir rutas para los nuevos archivos
            $rutaKey = "firmas/{$nombreBase}.key";
            $rutaCrt = "firmas/{$nombreBase}.crt";
            $rutaKeyAbsoluta = storage_path('app/private/' . $rutaKey);
            $rutaCrtAbsoluta = storage_path('app/private/' . $rutaCrt);

            // Verificar que el archivo existe
            if (!file_exists($rutaP12Absoluta)) {
                return response()->json([
                    'message' => "El archivo no existe en la ruta: $rutaP12Absoluta"
                ], 422);
            }

            // 1. Validar la contraseña del archivo P12
            $validatePasswordCommand = "openssl pkcs12 -in \"$rutaP12Absoluta\" -noout -passin pass:\"{$validated['tecnico_firma_clave']}\" 2>&1";
            $validateOutput = shell_exec($validatePasswordCommand);

            // Si el comando falla, la contraseña es incorrecta
            if (strpos($validateOutput, 'Mac verify error') !== false || strpos($validateOutput, 'invalid password') !== false) {
                // Eliminar el archivo subido
                @unlink($rutaP12Absoluta);

                return response()->json([
                    'message' => 'Contraseña incorrecta para el archivo P12'
                ], 422);
            }

            // 2. Extraer certificado (.crt)
            $certCommand = "openssl pkcs12 -in \"$rutaP12Absoluta\" -clcerts -nokeys -out \"$rutaCrtAbsoluta\" -passin pass:\"{$validated['tecnico_firma_clave']}\" -passout pass: 2>&1";
            $certOutput = shell_exec($certCommand);

            // 3. Extraer clave privada (.key)
            $keyCommand = "openssl pkcs12 -in \"$rutaP12Absoluta\" -nocerts -out \"$rutaKeyAbsoluta\" -passin pass:\"{$validated['tecnico_firma_clave']}\" -passout pass:\"{$validated['tecnico_firma_clave']}\" 2>&1";
            $keyOutput = shell_exec($keyCommand);

            // Verificar si los archivos se crearon correctamente
            if (!file_exists($rutaCrtAbsoluta) || !file_exists($rutaKeyAbsoluta)) {
                // Limpiar archivos en caso de error
                @unlink($rutaP12Absoluta);
                @unlink($rutaCrtAbsoluta);
                @unlink($rutaKeyAbsoluta);

                return response()->json([
                    'message' => 'Error al extraer el certificado o la clave privada. Verifique que la contraseña sea correcta y el archivo no esté dañado.',
                    'cert_output' => $certOutput,
                    'key_output' => $keyOutput
                ], 422);
            }

            // Leer el certificado para obtener información de validez
            $certContent = file_get_contents($rutaCrtAbsoluta);
            $certResource = openssl_x509_read($certContent);

            if (!$certResource) {
                // Limpiar archivos en caso de error
                @unlink($rutaP12Absoluta);
                @unlink($rutaCrtAbsoluta);
                @unlink($rutaKeyAbsoluta);

                return response()->json([
                    'message' => 'Error al leer el certificado extraído. El archivo puede estar corrupto.'
                ], 422);
            }

            // Obtener información del certificado
            $info = openssl_x509_parse($certResource);
            $validoDesde = date('Y-m-d H:i:s', $info['validFrom_time_t']);
            $validoHasta = date('Y-m-d H:i:s', $info['validTo_time_t']);

            // Liberar recurso
            openssl_x509_free($certResource);

            // Verificar si el certificado ha expirado
            if (now()->gt($validoHasta)) {
                // Eliminar archivos creados
                @unlink($rutaP12Absoluta);
                @unlink($rutaCrtAbsoluta);
                @unlink($rutaKeyAbsoluta);

                return response()->json([
                    'message' => 'El certificado ha expirado. Expiró en ' . $validoHasta
                ], 422);
            }

            // Almacenar rutas antiguas para limpieza posterior
            $oldP12Path = storage_path('app/private/' . $firma->tecnico_firma_ruta);
            $oldKeyPath = storage_path('app/private/' . $firma->tecnico_firma_key);
            $oldCrtPath = storage_path('app/private/' . $firma->tecnico_firma_pub);

            // Actualizar las rutas en los datos validados
            $validated['tecnico_firma_ruta'] = $rutaP12;
            $validated['tecnico_firma_key'] = $rutaKey;
            $validated['tecnico_firma_pub'] = $rutaCrt;
            $validated['fecha_emision'] = $validoDesde;
            $validated['fecha_expiracion'] = $validoHasta;
        } else {
            // Si no se sube nuevo archivo, mantener las fechas existentes
            $validated['fecha_emision'] = $firma->fecha_emision;
            $validated['fecha_expiracion'] = $firma->fecha_expiracion;
        }

        // Actualizar el registro
        $firma->update($validated);

        // Limpiar archivos antiguos después de una actualización exitosa
        if ($oldP12Path && file_exists($oldP12Path)) {
            @unlink($oldP12Path);
        }
        if ($oldKeyPath && file_exists($oldKeyPath)) {
            @unlink($oldKeyPath);
        }
        if ($oldCrtPath && file_exists($oldCrtPath)) {
            @unlink($oldCrtPath);
        }

        return response()->json([
            'message' => 'Firma actualizada correctamente',
            'firma' => $firma,
            'validez' => [
                'desde' => $firma->fecha_emision,
                'hasta' => $firma->fecha_expiracion
            ]
        ]);
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
