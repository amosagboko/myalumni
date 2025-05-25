<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Imports\StudentsImport;
use Maatwebsite\Excel\Facades\Excel;

class AlumniController extends Controller
{
    //
    public function index(Request $request)
{
    // Start a query on the Student model
    $students = \App\Models\Student::query();

    // Apply filters only if provided
    if ($request->filled('year_of_graduation')) {
        $students->where('year_of_graduation', $request->year_of_graduation);
    }

    if ($request->filled('faculty')) {
        $students->where('faculty', $request->faculty);
    }

    if ($request->filled('department')) {
        $students->where('department', $request->department);
    }

    if ($request->filled('programme')) {
        $students->where('programme', $request->programme);
    }

    // Apply sorting if provided
    if ($request->filled('sort_by')) {
        $students->orderBy($request->sort_by);
    }

    // Fetch distinct values for filters
    $years = \App\Models\Student::select('year_of_graduation')->distinct()->pluck('year_of_graduation');
    $faculties = \App\Models\Student::select('faculty')->distinct()->pluck('faculty');
    $departments = \App\Models\Student::select('department')->distinct()->pluck('department');
    $programmes = \App\Models\Student::select('programme')->distinct()->pluck('programme');

    // Return the view with filtered and sorted data
    return view('allstudents', [
        'students' => $students->paginate(25),
        'years' => $years,
        'faculties' => $faculties,
        'departments' => $departments,
        'programmes' => $programmes,
    ]);
}





    public function upload (Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        Excel::import(new StudentsImport, $request->file('file'));

        return redirect()->route('students.index')->with('success', 'Alumni uploaded successfully!');
    }


    public function showAlumniForm (){
        return view('showalumni');
    }


    public function viewAlumniInformation (Request $request){
        $request->validate([
            'matriculation_number' => 'required|string',
        ]);

        $student = Student::where('matriculation_id', $request->matriculation_number)->first();
        if ($student) {
            return redirect()->route('alumni')->with('student', $student);
        } else {
            return redirect()->route('alumni')->with('error', 'Student not found');
        }

        //logic of activating alumni and saving basic info into membership table
        
    }

    public function alumniUpload (){
        return view('alumniupload');
    }


    //SUpport Admin activities
    public function supportAlumniUpload (){
        return view('support.admin-view');
    }

    public function supportIndex (Request $request){
        // Start a query on the Student model
    $students = \App\Models\Student::query();

    // Apply filters only if provided
    if ($request->filled('year_of_graduation')) {
        $students->where('year_of_graduation', $request->year_of_graduation);
    }

    if ($request->filled('faculty')) {
        $students->where('faculty', $request->faculty);
    }

    if ($request->filled('department')) {
        $students->where('department', $request->department);
    }

    if ($request->filled('programme')) {
        $students->where('programme', $request->programme);
    }

    // Apply sorting if provided
    if ($request->filled('sort_by')) {
        $students->orderBy($request->sort_by);
    }

    // Fetch distinct values for filters
    $years = \App\Models\Student::select('year_of_graduation')->distinct()->pluck('year_of_graduation');
    $faculties = \App\Models\Student::select('faculty')->distinct()->pluck('faculty');
    $departments = \App\Models\Student::select('department')->distinct()->pluck('department');
    $programmes = \App\Models\Student::select('programme')->distinct()->pluck('programme');

    // Return the view with filtered and sorted data
    return view('support.allAlumni', [
        'students' => $students->paginate(25),
        'years' => $years,
        'faculties' => $faculties,
        'departments' => $departments,
        'programmes' => $programmes,
    ]);
    }

}
