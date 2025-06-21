<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskChallengeResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'challenges'=>$this->challenges->map(function($challenge){
                return [
                    'id'=>$challenge->id,
                    'title'=>$challenge->content,
                    'solution'=>$challenge->solution,
                ];
            })
        ];
    }
}
