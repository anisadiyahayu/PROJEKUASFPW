<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OwnerRequest;
use App\Models\Owner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * AD-02: mengelola data pemilik kapal.
 *
 * Penghapusan memakai soft delete karena OWNERS memiliki kolom deleted_at
 * pada ERD. Pemilik yang masih memiliki kapal aktif tidak dihapus agar
 * SHIPS.owner_id tidak menjadi yatim.
 */
class OwnerController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';

        $owners = Owner::query()
            ->withCount('ships')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.owners.index', compact('owners', 'q'));
    }

    public function create(): View
    {
        return view('admin.owners.create', ['owner' => new Owner]);
    }

    public function store(OwnerRequest $request): RedirectResponse
    {
        $owner = Owner::create($request->validated());

        return redirect()
            ->route('admin.owners.index')
            ->with('status', "Pemilik \"{$owner->name}\" berhasil ditambahkan.");
    }

    public function edit(Owner $owner): View
    {
        return view('admin.owners.edit', compact('owner'));
    }

    public function update(OwnerRequest $request, Owner $owner): RedirectResponse
    {
        $owner->update($request->validated());

        return redirect()
            ->route('admin.owners.index')
            ->with('status', "Pemilik \"{$owner->name}\" berhasil diperbarui.");
    }

    public function destroy(Owner $owner): RedirectResponse
    {
        if ($owner->ships()->exists()) {
            return redirect()
                ->route('admin.owners.index')
                ->withErrors([
                    'owner' => "Pemilik \"{$owner->name}\" masih memiliki kapal aktif. Hapus atau pindahkan kapalnya terlebih dahulu.",
                ]);
        }

        $owner->delete();

        return redirect()
            ->route('admin.owners.index')
            ->with('status', "Pemilik \"{$owner->name}\" berhasil dihapus.");
    }
}
