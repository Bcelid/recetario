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

        $lote = RecetaLote::with(['almacen', 'recetas'])->findOrFail($id);

        $emailConfig = UserEmailConfig::where('user_id', $user->id)->first();

        if (!$emailConfig) {
            return response()->json(['error' => 'No se ha configurado el correo del remitente.'], 400);
        }

        if (!$lote->almacen || !$lote->almacen->almacen_correo) {
            return response()->json(['error' => 'El almacén no tiene correo configurado.'], 400);
        }

        // -----------------------------
        // LISTA DE DOCUMENTOS INDIVIDUALES
        // -----------------------------
        // -----------------------------
        // LISTA DE DOCUMENTOS INDIVIDUALES (nuevos)
        // -----------------------------
        $documentos = [];

        foreach ($lote->recetas as $receta) {
            if ($receta->receta_path) {
                $documentos[] = [
                    'nombre' => basename($receta->receta_path),
                    'url'    => asset("storage/" . $receta->receta_path),
                    'path'   => $receta->receta_path,
                ];
            }
        }

        // -----------------------------
        //  SI NO EXISTEN INDIVIDUALES → BUSCAR PDF ANTIGUO DEL LOTE
        // -----------------------------
        if (count($documentos) === 0 && $lote->receta_lote_path) {

            // agregar el PDF histórico del lote
            $documentos[] = [
                'nombre' => basename($lote->receta_lote_path),
                'url'    => asset("storage/" . $lote->receta_lote_path),
                'path'   => $lote->receta_lote_path,
            ];
        }

        // -----------------------------
        //  SI NO EXISTE NADA → ERROR
        // -----------------------------
        if (count($documentos) === 0) {
            return response()->json([
                'error' => 'Este lote no tiene recetas firmadas para enviar.'
            ], 400);
        }


        // -----------------------------
        // Cuerpo sugerido
        // -----------------------------
        $body = "Estimado/a {$lote->almacen->almacen_nombre},\n\n";
        $body .= "Se adjunta el archivo correspondiente a las recetas.\n\n";
        $body .= "Saludos cordiales,\n";
        $body .= "{$emailConfig->smtp_from_name}";

        // -----------------------------
        //  HISTORIAL COMPLETO CON RECETAS ENVIADAS
        // -----------------------------
        $historial = RecetaLoteEnvio::where('receta_lote_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($envio) {

                // convertir el JSON guardado a array
                $recetas = [];
                if ($envio->url_documento) {
                    $recetas = json_decode($envio->url_documento, true) ?: [];
                }

                return [
                    'fecha'   => $envio->created_at->format('Y-m-d H:i'),
                    'destinatario' => $envio->correo,
                    'recetas' => array_map(function ($nombre) {
                        return ['nombre' => $nombre];
                    }, $recetas)
                ];
            });

        return response()->json([
            'remitente_nombre' => $emailConfig->smtp_from_name,
            'remitente_correo' => $emailConfig->smtp_from_address,
            'destinatario'     => $lote->almacen->almacen_correo,
            'body'             => $body,
            'lote_id'          => $lote->receta_lote_id,
            'almacen_id'       => $lote->almacen_id,
            'documentos'       => $documentos,
            'historial'        => $historial
        ]);
    }



    public function enviarCorreo(Request $request)
    {
        $request->validate([
            'receta_lote_id' => 'required|exists:receta_lote,receta_lote_id',
            'almacen_id'     => 'required|exists:almacen,almacen_id',
            'to'             => 'required|email',
            'cc'             => 'nullable|email',
            'subject'        => 'required|string|max:255',
            'body'           => 'required|string',
            'paths'          => 'required|array|min:1',
        ]);

        $user = Auth::user();

        $config = UserEmailConfig::where('user_id', $user->id)->firstOrFail();

        $lote = RecetaLote::with('almacen')->findOrFail($request->receta_lote_id);

        $numeroEnvio = RecetaLoteEnvio::where('receta_lote_id', $lote->receta_lote_id)->count() + 1;

        // Configurar SMTP
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
            Mail::send([], [], function ($message) use ($request, $config) {

                $message->to($request->to)
                    ->from($config->smtp_from_address, $config->smtp_from_name)
                    ->subject($request->subject)
                    ->html(nl2br($request->body));

                // Si existe el campo CC, agregarlo
                if ($request->cc) {
                    $message->cc($request->cc);
                }

                // Adjuntar cada archivo seleccionado
                foreach ($request->paths as $ruta) {
                    $fullPath = storage_path("app/public/" . $ruta);

                    if (file_exists($fullPath)) {
                        $message->attach($fullPath);
                    }
                }
            });

            $archivosEnviados = [];

            foreach ($request->paths as $ruta) {
                $archivosEnviados[] = basename($ruta);
            }

            // Registrar envío
            RecetaLoteEnvio::create([
                'user_id'        => $user->id,
                'almacen_id'     => $request->almacen_id,
                'correo'         => $request->to,
                'receta_lote_id' => $request->receta_lote_id,
                'cc'             => $request->cc,
                'fecha_envio'    => now(),
                'numero_envio'   => $numeroEnvio,
                'url_documento'  => json_encode($archivosEnviados), // ya no es uno solo
            ]);

            // Actualizar estado del lote
            $lote->update([
                'receta_lote_enviado'      => 1,
                'receta_lote_fecha_envio'  => $lote->receta_lote_fecha_envio ?? now(),
                'receta_lote_ultimo_envio' => now(),
            ]);

            return response()->json(['message' => 'Correo enviado correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar el correo: ' . $e->getMessage()], 500);
        }
    }
}
