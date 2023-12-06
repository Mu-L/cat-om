<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\ArrayShape;

class TicketService
{
    public Ticket $ticket;

    public function __construct(?Ticket $ticket = null)
    {
        $this->ticket = $ticket ?? new Ticket();
    }

    /**
     * 创建工单记录.
     */
    #[ArrayShape(['comment' => 'string', 'user_id' => 'int'])]
    public function createHasTrack(array $data): Model
    {
        return $this->ticket->tracks()->create($data);
    }

    /**
     * 创建.
     */
    public function create(array $data): Ticket
    {
        $this->ticket->setAttribute('asset_number', $data['asset_number']);
        $this->ticket->setAttribute('subject', $data['subject']);
        $this->ticket->setAttribute('description', $data['description']);
        $this->ticket->setAttribute('category_id', $data['category_id']);
        $this->ticket->setAttribute('priority', $data['priority']);
        $this->ticket->setAttribute('user_id', auth()->id());
        $this->ticket->setAttribute('assignee_id', 0);
        $this->ticket->save();

        return $this->ticket;
    }

    /**
     * 完成.
     */
    public function finish(): ?bool
    {
        return $this->ticket->delete();
    }

    /**
     * 设置处理人.
     */
    public function setAssignee(int $user_id): bool
    {
        $this->ticket->setAttribute('assignee_id', $user_id);

        return $this->ticket->save();
    }

    /**
     * 获取工单是否有处理人.
     */
    public function isSetAssignee(): bool
    {
        return $this->ticket->getAttribute('assignee_id');
    }
}
