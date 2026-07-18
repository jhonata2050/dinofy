<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public string $token;
    public string $resetRoute;

    public function __construct(string $token, string $resetRoute = 'admin.password.reset')
    {
        $this->token = $token;
        $this->resetRoute = $resetRoute;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route($this->resetRoute, ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));
        return (new MailMessage)
            ->subject('Redefinir Senha — Dinofy')
            ->greeting('Olá!')
            ->line('Você solicitou a redefinição da sua senha.')
            ->action('Redefinir Senha', $url)
            ->line('Este link expira em 60 minutos.')
            ->line('Se você não solicitou, ignore este e-mail.')
            ->salutation('Equipe Dinofy');
    }
}
