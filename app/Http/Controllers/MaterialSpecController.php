<?php

namespace App\Http\Controllers;

use App\Models\MaterialSpec;
use App\Models\WorkType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialSpecController extends Controller
{
    public function index(): View
    {
        $materialSpecs = MaterialSpec::query()
            ->with(['workType' => fn ($query) => $query->withTrashed()])
            ->orderBy('name')
            ->paginate(15);

        return view('material-specs.index', compact('materialSpecs'));
    }

    public function create(): View
    {
        $workTypes = WorkType::query()
            ->orderBy('name')
            ->get();

        return view('material-specs.create', compact('workTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'work_type_id' => ['required', 'integer', 'exists:work_types,id,deleted_at,NULL'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        MaterialSpec::create($data);

        return Redirect::route('admin.material-specs.index')
            ->with('status', 'Material/spesifikasi berhasil ditambahkan.');
    }

    public function edit(MaterialSpec $materialSpec): View
    {
        $workTypes = WorkType::withTrashed()
            ->where(function (Builder $query) use ($materialSpec): void {
                $query->whereNull('deleted_at')
                    ->orWhereKey($materialSpec->work_type_id);
            })
            ->orderBy('name')
            ->get();

        return view('material-specs.edit', compact('materialSpec', 'workTypes'));
    }

    public function update(Request $request, MaterialSpec $materialSpec): RedirectResponse
    {
        $data = $request->validate([
            'work_type_id' => [
                'required',
                'integer',
                Rule::exists('work_types', 'id')->where(
                    fn ($query) => $query->whereNull('deleted_at')
                        ->orWhere('id', $materialSpec->work_type_id)
                ),
            ],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $materialSpec->update($data);

        return Redirect::route('admin.material-specs.index')
            ->with('status', 'Material/spesifikasi berhasil diperbarui.');
    }

    public function destroy(MaterialSpec $materialSpec): RedirectResponse
    {
        $materialSpec->delete();

        return Redirect::route('admin.material-specs.index')
            ->with('status', 'Material/spesifikasi berhasil dihapus.');
    }
}
