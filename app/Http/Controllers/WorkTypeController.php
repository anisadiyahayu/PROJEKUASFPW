<?php

namespace App\Http\Controllers;

use App\Models\WorkCategory;
use App\Models\WorkType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class WorkTypeController extends Controller
{
    public function index(): View
    {
        $workTypes = WorkType::query()
            ->with(['workCategory' => fn ($query) => $query->withTrashed()])
            ->orderBy('name')
            ->paginate(15);

        return view('work-types.index', compact('workTypes'));
    }

    public function create(): View
    {
        $categories = WorkCategory::query()->orderBy('name')->get();

        return view('work-types.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'work_category_id' => ['required', 'integer', 'exists:work_categories,id,deleted_at,NULL'],
        ]);

        WorkType::create($data);

        return Redirect::route('admin.work-types.index')
            ->with('status', 'Jenis pekerjaan berhasil ditambahkan.');
    }

    public function edit(WorkType $workType): View
    {
        $categories = WorkCategory::query()->orderBy('name')->get();

        return view('work-types.edit', compact('workType', 'categories'));
    }

    public function update(Request $request, WorkType $workType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'work_category_id' => ['required', 'integer', 'exists:work_categories,id,deleted_at,NULL'],
        ]);

        $workType->update($data);

        return Redirect::route('admin.work-types.index')
            ->with('status', 'Jenis pekerjaan berhasil diperbarui.');
    }

    public function destroy(WorkType $workType): RedirectResponse
    {
        $workType->delete();

        return Redirect::route('admin.work-types.index')
            ->with('status', 'Jenis pekerjaan berhasil dihapus.');
    }
}
