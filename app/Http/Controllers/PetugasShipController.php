<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Ship;
use Illuminate\Contracts\View\View;


class PetugasShipController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';

        $ownerId = $request->query('owner_id');
        $ownerId = is_numeric($ownerId) ? (int) $ownerId : null;

        $ships = Ship::query()
            ->with('owner')
            ->when($q !== '', fn ($query) => $query->where(fn ($b) => $b
                ->where('name', 'like', "%{$q}%")
                ->orWhere('hull_number', 'like', "%{$q}%")))
            ->when($ownerId !== null, fn ($query) => $query->where('owner_id', $ownerId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('ships.index', [
            'ships' => $ships,
            'owners' => Owner::orderBy('name')->get(),
            'q' => $q,
            'ownerId' => $ownerId,
        ]);
    }
}
