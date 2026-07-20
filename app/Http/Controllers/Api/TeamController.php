<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;

class TeamController extends Controller
{
    /** GET /api/v1/team — visible members, ordered. */
    public function index()
    {
        $members = TeamMember::where('visible', true)
            ->orderBy('sort_order')
            ->get();

        return TeamMemberResource::collection($members);
    }
}
