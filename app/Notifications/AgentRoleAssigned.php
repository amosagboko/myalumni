<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgentRoleAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $electionTitle;
    protected $candidateName;
    protected $officeTitle;
    protected $candidateId;
    protected $electionId;

    /**
     * Create a new notification instance.
     */
    public function __construct($electionTitle, $candidateName, $officeTitle, $candidateId, $electionId)
    {
        $this->electionTitle = $electionTitle;
        $this->candidateName = $candidateName;
        $this->officeTitle = $officeTitle;
        $this->candidateId = $candidateId;
        $this->electionId = $electionId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'agent_role_assigned',
            'data' => [
                'election_title' => $this->electionTitle,
                'candidate_name' => $this->candidateName,
                'office_title' => $this->officeTitle,
                'candidate_id' => $this->candidateId,
                'election_id' => $this->electionId
            ]
        ];
    }
} 