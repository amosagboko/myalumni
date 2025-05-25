@extends('admindashboard')

@section('content')
<div class="main-content bg-lightblue theme-dark-bg right-chat-active">
    <div class="middle-sidebar-bottom">
        <div class="middle-sidebar-left">
            <div class="middle-wrap">
                <div class="card w-100 border-0 bg-white shadow-xs p-0 mb-4">
                    <div class="card-body p-4 w-100 bg-current border-0 d-flex justify-content-between align-items-center rounded-3">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('admin.dashboard') }}" class="d-inline-block mt-2">
                                <i class="ti-arrow-left font-sm text-white"></i>
                            </a>
                            <h4 class="font-xs text-white fw-600 ms-4 mb-0 mt-2">Upload Alumni</h4>
                        </div>
                        <a href="{{ route('students.index') }}" class="btn btn-light text-current">
                            View Alumni
                        </a>
                    </div>
                    <div class="card-body p-lg-5 p-4 w-100 border-0">
                        <form action="{{ route('students.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="file" class="form-label">Upload Alumni-Excel File</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection