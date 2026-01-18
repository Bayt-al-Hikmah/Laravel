<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource{

    public function toArray(Request $request): array{
        return [
        'id' => $this->id,
        'name' => $this->name,
        'state' => $this->state,
        'created_at' => $this->created_at,
        'user_id' => $this->user_id,
    ];
    }
}
