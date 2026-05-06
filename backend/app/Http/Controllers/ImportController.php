<?php

namespace App\Http\Controllers;

use App\Imports\GradesImport;
use App\Imports\StudentsImport;
use App\Models\Group;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    // ─── DOCENTE: grades + attendance ────────────────────────────────────────

    public function gradesForm()
    {
        $teacher = auth()->user()->teacher;
        $groups  = Group::with('subject')
            ->where('teacher_id', $teacher->id)
            ->get();

        return view('imports.grades', compact('groups'));
    }

    public function gradesImport(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'file'     => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $teacher = auth()->user()->teacher;

        // Verify the group belongs to this teacher
        $group = Group::where('id', $request->group_id)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $import = new GradesImport($group->id, auth()->id());
        Excel::import($import, $request->file('file'));

        $message = "Importación completada: {$import->updated} alumno(s) actualizado(s).";
        if ($import->skipped > 0) {
            $message .= " {$import->skipped} omitido(s).";
        }

        return back()
            ->with('success', $message)
            ->with('import_errors', $import->errors);
    }

    public function gradesTemplate()
    {
        $csv = "numero_control,parcial_1,parcial_2,parcial_3,total_clases,clases_asistidas\n";
        $csv .= "20210001,78,82,85,30,27\n";
        $csv .= "20210002,55,48,,30,20\n";
        $csv .= "20210003,90,88,92,30,30\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_calificaciones.csv"',
        ]);
    }

    // ─── ADMIN: bulk student import ──────────────────────────────────────────

    public function studentsForm()
    {
        return view('imports.students');
    }

    public function studentsImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $import = new StudentsImport();
        Excel::import($import, $request->file('file'));

        $message = "Importación completada: {$import->created} alumno(s) creado(s).";
        if ($import->skipped > 0) {
            $message .= " {$import->skipped} omitido(s).";
        }

        return back()
            ->with('success', $message)
            ->with('import_errors', $import->errors);
    }

    public function studentsTemplate()
    {
        $csv = "numero_control,nombre,apellidos,email,carrera,semestre\n";
        $csv .= "20210001,Juan,Garcia Lopez,20210001@itsc.edu.mx,ISC,4\n";
        $csv .= "20210002,Maria,Hernandez Cruz,,ISC,4\n";
        $csv .= "20210003,Carlos,Perez Sanchez,carlos.perez@itsc.edu.mx,ISC,5\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_alumnos.csv"',
        ]);
    }
}
