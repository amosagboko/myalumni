<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AgentSuggestion extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'id' => Str::uuid(),
            'type' => 'agent_suggestion',
            'data' => $this->data
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Agent Suggestion for ' . $this->data['election_title'])
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->data['candidate_name'] . ' has suggested you as their agent for the ' . $this->data['office_title'] . ' position in ' . $this->data['election_title'])
            ->action('View Details', url('/notifications'))
            ->line('Please review this suggestion and respond accordingly.');
    }
} 