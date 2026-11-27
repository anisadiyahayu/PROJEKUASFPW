<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShipRequest;
use App\Models\Owner;
use App\Models\Ship;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * AD-02: mengelola data kapal dan menghubungkannya dengan pemilik.
 *
 * Penghapusan memakai soft delete karena SHIPS memiliki kolom deleted_at
 * pada ERD. Opnam lama tetap dapat membaca data kapal setelah dihapus.
 */
class ShipController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';

        $ownerId = $request->query('owner_id');
        $ownerId = is_numeric($ownerId) ? (int) $ownerId : null;

        $ships = Ship::query()
            ->with('owner')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('hull_number', 'like', "%{$q}%");
                });
            })
            ->when($ownerId !== null, fn ($query) => $query->where('owner_id', $ownerId))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.ships.index', [
            'ships' => $ships,
            'owners' => Owner::orderBy('name')->get(),
            'q' => $q,
            'ownerId' => $ownerId,
        ]);
    }

    public function create(Request $request): View
    {
        $ownerId = $request->query('owner_id');

        return view('admin.ships.create', [
            'ship' => new Ship(['owner_id' => is_numeric($ownerId) ? (int) $ownerId : null]),
            'owners' => Owner::orderBy('name')->get(),
        ]);
    }

    public function store(ShipRequest $request): RedirectResponse
    {
        $ship = Ship::create($request->validated());

        return redirect()
            ->route('admin.ships.index')
            ->with('status', "Kapal \"{$ship->name}\" berhasil ditambahkan.");
    }

    public function edit(Ship $ship): View
    {
        return view('admin.ships.edit', [
            'ship' => $ship,
            'owners' => Owner::orderBy('name')->get(),
        ]);
    }

    public function update(ShipRequest $request, Ship $ship): RedirectResponse
    {
        $ship->update($request->validated());

        return redirect()
            ->route('admin.ships.index')
            ->with('status', "Kapal \"{$ship->name}\" berhasil diperbarui.");
    }

    public function destroy(Ship $ship): RedirectResponse
    {
        $ship->delete();

        return redirect()
            ->route('admin.ships.index')
            ->with('status', "Kapal \"{$ship->name}\" berhasil dihapus.");
    }
}
