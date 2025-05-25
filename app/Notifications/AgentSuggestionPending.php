<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AgentSuggestionPending extends Notification implements ShouldQueue
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
            'type' => 'agent_suggestion_pending',
            'data' => $this->data
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Agent Suggestion Pending Approval')
            ->greeting('Hello ' . $notifiable->name)
            ->line('A new agent suggestion has been submitted for your approval:')
            ->line('Candidate: ' . $this->data['candidate_name'])
            ->line('Suggested Agent: ' . $this->data['suggested_agent_name'])
            ->line('Position: ' . $this->data['office_title'])
            ->line('Election: ' . $this->data['election_title'])
            ->action('Review Suggestion', url('/elcom/agent-suggestions'))
            ->line('Please review this suggestion and take appropriate action.');
    }
} 