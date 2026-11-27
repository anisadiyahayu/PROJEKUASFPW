<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        $units = Unit::query()
            ->orderBy('name')
            ->orderBy('code')
            ->paginate(15);

        return view('units.index', compact('units'));
    }

    public function create(): View
    {
        return view('units.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:units,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Unit::create($data);

        return Redirect::route('admin.units.index')
            ->with('status', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'code')->ignore($unit->getKey()),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $unit->update($data);

        return Redirect::route('admin.units.index')
            ->with('status', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        try {
            $deleted = DB::transaction(function () use ($unit): bool {
                $lockedUnit = Unit::query()
                    ->whereKey($unit->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedUnit->tariffs()->exists()) {
                    return false;
                }

                $lockedUnit->delete();

                return true;
            });
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                throw $exception;
            }

            return Redirect::route('admin.units.index')
                ->with('error', 'Satuan tidak dapat dihapus karena masih digunakan oleh data tarif.');
        }

        if (! $deleted) {
            return Redirect::route('admin.units.index')
                ->with('error', 'Satuan tidak dapat dihapus karena masih digunakan oleh data tarif.');
        }

        return Redirect::route('admin.units.index')
            ->with('status', 'Satuan berhasil dihapus.');
    }
}
