<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecetaLote;
use App\Models\UserEmailConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\RecetaLoteEnvio;


class RecetaEmailController extends Controller
{
    public function getDatosEnvio($id)
    {
        $user = Auth::user();

        // 1. Obtener el lote
        $lote = RecetaLote::with('almacen')->findOrFail($id);

        // 2. Obtener configuración del usuario
        $emailConfig = UserEmailConfig::where('user_id', $user->id)->first();

        if (!$emailConfig) {
            return response()->json(['error' => 'No se ha configurado el correo del remitente.'], 400);
        }

        // 3. Verificar correo de almacen
        if (!$lote->almacen || !$lote->almacen->almacen_correo) {
            return response()->json(['error' => 'El almacén no tiene un correo configurado.'], 400);
        }

        // 4. Preparar mensaje (body)
        $body = "Estimado/a {$lote->almacen->almacen_nombre},\n\n";
        $body .= "Se adjunta el archivo correspondiente a las recetas.\n\n";
        $body .= "Saludos cordiales,\n";
        $body .= "{$emailConfig->smtp_from_name}";

        // 5. Obtener historial de envíos para este lote
        $historial = RecetaLoteEnvio::where('receta_lote_id', $id)
            ->orderBy('created_at', 'desc')
            ->get(['created_at', 'correo']);

        // Mapear formato para el JSON
        $historialFormateado = $historial->map(function ($envio) {
            return [
                'fecha' => $envio->created_at->format('Y-m-d H:i'),
                'destinatario' => $envio->correo,
            ];
        });

        return response()->json([
            'remitente_nombre' => $emailConfig->smtp_from_name,
            'remitente_correo' => $emailConfig->smtp_from_address,
            'destinatario' => $lote->almacen->almacen_correo,
            'body' => $body,
            'documento_url' => asset('storage/' . $lote->receta_lote_path),
            'lote_id' => $lote->receta_lote_id,
            'almacen_id' => $lote->almacen_id,
            'historial' => $historialFormateado,
        ]);
    }


    public function enviarCorreo(Request $request)
    {
        // 1. Validar la solicitud
        $request->validate([
            'receta_lote_id' => 'required|exists:receta_lote,receta_lote_id',
            'almacen_id'     => 'required|exists:almacen,almacen_id',
            'to'             => 'required|email',
            'subject'        => 'required|string|max:255',
            'body'           => 'required|string',
        ]);

        // 2. Obtener el usuario autenticado
        $user = Auth::user();

        // 3. Obtener la configuración SMTP del usuario
        $config = UserEmailConfig::where('user_id', $user->id)->firstOrFail();

        // 4. Obtener el lote con su relación al almacén
        $lote = RecetaLote::with('almacen')->findOrFail($request->receta_lote_id);

        // 5. Calcular el número de envío (conteo + 1)
        $numeroEnvio = RecetaLoteEnvio::where('receta_lote_id', $lote->receta_lote_id)->count() + 1;

        // 6. Ruta del documento adjunto
        $documentoPath = storage_path("app/public/{$lote->receta_lote_path}");

        // 7. Verificar existencia del documento
        if (!file_exists($documentoPath)) {
            return response()->json(['error' => 'El documento adjunto no se encontró.'], 404);
        }

        // 8. Configurar SMTP dinámico para este envío
        config([
            'mail.mailers.smtp.host'       => $config->smtp_host,
            'mail.mailers.smtp.port'       => $config->smtp_port,
            'mail.mailers.smtp.encryption' => $config->smtp_encryption,
            'mail.mailers.smtp.username'   => $config->smtp_username,
            'mail.mailers.smtp.password'   => $config->smtp_password,
            'mail.from.address'            => $config->smtp_from_address,
            'mail.from.name'               => $config->smtp_from_name,
        ]);

        try {
            // 9. Enviar el correo
            Mail::send([], [], function ($message) use ($request, $config, $documentoPath) {
                $message->to($request->to)
                    ->from($config->smtp_from_address, $config->smtp_from_name)
                    ->subject($request->subject)
                    ->html(nl2br($request->body)) // ✅ ESTA ES LA CORRECCIÓN
                    ->attach($documentoPath);
            });

            // 10. Registrar el envío en la tabla RecetaLoteEnvio
            RecetaLoteEnvio::create([
                'user_id'        => $user->id,
                'almacen_id'     => $request->almacen_id,
                'correo' => $request->to,
                'receta_lote_id' => $request->receta_lote_id,
                'fecha_envio'    => now(),
                'numero_envio'   => $numeroEnvio,
                'url_documento'  => $lote->receta_lote_path,
            ]);

            // 11. Actualizar el lote como enviado
            $lote->update([
                'receta_lote_enviado'      => 1,
                'receta_lote_fecha_envio'  => $lote->receta_lote_fecha_envio ?? now(), // se registra solo la primera vez
                'receta_lote_ultimo_envio' => now(), // se actualiza siempre
            ]);

            // 12. Respuesta de éxito
            return response()->json(['message' => 'Correo enviado correctamente.']);
        } catch (\Exception $e) {
            // 13. En caso de error al enviar
            return response()->json(['error' => 'Error al enviar el correo: ' . $e->getMessage()], 500);
        }
    }
}
