<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class GuardianAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        // Never email the random password: the activation link proves ownership and lets the user set their own.
        $token = Password::broker()->createToken($notifiable);

        $url = route('password.reset', ['token' => $token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject(__('Bienvenido a :app', ['app' => config('app.name')]))
            ->greeting(__('¡Hola, :name!', ['name' => $notifiable->name]))
            ->line(__('Se creó tu cuenta de acudiente en :app.', ['app' => config('app.name')]))
            ->line(__('Tu correo de acceso es :email.', ['email' => $notifiable->email]))
            ->action(__('Crear mi contraseña'), $url)
            ->line(__('Por seguridad debes crear tu propia contraseña antes de ingresar por primera vez. Este enlace expira pronto, así que hazlo cuanto antes.'));
    }
}
