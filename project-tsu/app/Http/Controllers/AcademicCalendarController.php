<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holiday;

class AcademicCalendarController extends Controller
{

    public function index()
    {
        $academicCalendars = Holiday::orderBy('date', 'asc')->get();
        return view('settings.academic_calendar.index', compact('academicCalendars'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Holiday::create($request->all());

        return redirect()->route('settings.academic_calendar.index')
            ->with('success', 'Hari besar berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $calendar = Holiday::findOrFail($id);
        $calendar->update($request->all());

        return redirect()->route('settings.academic_calendar.index')
            ->with('success', 'Hari besar berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $calendar = Holiday::findOrFail($id);
        $calendar->delete();

        return redirect()->route('settings.academic_calendar.index')
            ->with('success', 'Hari besar berhasil dihapus.');
    }
}
