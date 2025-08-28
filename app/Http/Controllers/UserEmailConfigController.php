<?php

namespace App\Http\Controllers;

use App\Models\UserEmailConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;


class UserEmailConfigController extends Controller
{
    public function edit()
    {
        $config = UserEmailConfig::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        return view('email-config.edit', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'smtp_provider' => 'required|in:gmail,outlook',
            'smtp_username' => 'required|email',
            'smtp_password' => 'required|string',
            'smtp_from_name' => 'required|string|max:255',
            'smtp_from_address' => 'required|email',
        ]);

        // Establecer configuración automática según proveedor
        $providerSettings = [
            'gmail' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
            ],
            'outlook' => [
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
            ]
        ];

        $settings = $providerSettings[$request->smtp_provider];

        UserEmailConfig::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'smtp_provider' => $request->smtp_provider,
                'smtp_host' => $settings['host'],
                'smtp_port' => $settings['port'],
                'smtp_encryption' => $settings['encryption'],
                'smtp_username' => $request->smtp_username,
                'smtp_password' => $request->smtp_password,
                'smtp_from_name' => $request->smtp_from_name,
                'smtp_from_address' => $request->smtp_from_address,
            ]
        );

        return back()->with('success', 'Configuración guardada correctamente.');
    }


    public function testConnection(Request $request)
    {
        $request->validate([
            'smtp_provider' => 'required|in:gmail,outlook',
            'smtp_username' => 'required|email',
            'smtp_password' => 'required',
            'smtp_from_name' => 'required|string|max:255',
            'smtp_from_address' => 'required|email',
        ]);

        $config = match ($request->smtp_provider) {
            'gmail' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
            ],
            'outlook' => [
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
            ],
        };

        try {

            $encryption = $config['encryption']; // 'tls' o 'ssl' o null

            if ($encryption === 'ssl') {
                $useSsl = true; // true para SSL en puerto 465
            } else {
                $useSsl = false; // para STARTTLS (tls) en puerto 587
            }

            $transport = new EsmtpTransport(
                $config['host'],
                $config['port'],
                $useSsl
            );


            $transport->setUsername($request->smtp_username);
            $transport->setPassword($request->smtp_password);

            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from($request->smtp_from_address)
                ->to($request->smtp_username)  // Mismo usuario para probar
                ->subject('Prueba de conexión SMTP')
                ->text('Este es un mensaje de prueba para verificar la conexión SMTP.');

            $mailer->send($email);

            return response()->json(['success' => true, 'message' => 'Conexión SMTP exitosa y mensaje enviado.']);
        } catch (TransportExceptionInterface $e) {
            return response()->json(['success' => false, 'message' => 'Error al conectar o enviar: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
        }
    }
}
