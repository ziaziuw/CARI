@extends('layout.template')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #FEF9D9 0%, #f5f0c8 100%);
        min-height: 100vh;
    }
    
    .container {
        background: transparent;
    }
    
    .card {
        background: rgba(255, 255, 255, 0.95);
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(147, 89, 0, 0.2);
        backdrop-filter: blur(10px);
    }
    
    .card-header {
        background: linear-gradient(135deg, #CE7D00 0%, #935900 100%);
        color: #FEF9D9;
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem;
        border: none;
    }
    
    .card-header h4 {
        margin: 0;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }
    
    .table {
        margin: 0;
    }
    
    .table thead th {
        background-color: #FEF9D9;
        color: #935900;
        border-bottom: 2px solid #CE7D00;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }
    
    .table tbody tr {
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(206, 125, 0, 0.1);
        transform: scale(1.01);
    }
    
    .table tbody td {
        vertical-align: middle;
        border-color: rgba(206, 125, 0, 0.2);
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(254, 249, 217, 0.3);
    }
    
    .table img {
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(147, 89, 0, 0.3);
        transition: transform 0.3s ease;
    }
    
    .table img:hover {
        transform: scale(1.05);
    }
    
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #CE7D00;
        border-radius: 5px;
        padding: 5px;
    }
    
    .dataTables_wrapper .dataTables_length select:focus,
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #935900;
        outline: none;
        box-shadow: 0 0 5px rgba(206, 125, 0, 0.3);
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #FEF9D9;
        border: 1px solid #CE7D00;
        color: #935900 !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #CE7D00 !important;
        color: #FEF9D9 !important;
        border-color: #935900;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #935900 !important;
        color: #FEF9D9 !important;
        border-color: #935900;
    }
</style>

<div class="container mt-4 mb-4">
    <div class="card">
        <div class="card-header">
            <h4>Data Pelaporan</h4>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="pointstable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($points as $p) <!-- memanggil berulang dari semua data yang mengandung point */ -->
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->description }}</td>
                        <td>
                            <img src="{{asset('storage/public/images/' . $p->image) }}" alt=""
                            width="200" title="{{ $p->image }}">
                        </td>
                        <td>{{ $p->created_at }}</td>
                        <td>{{ $p->updated_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

<!-- tambah section styles */ -->
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css">
@endsection

<!-- tambah section scripts */ -->
@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
<script>
    let tablepoints = new DataTable('#pointstable');
    let tablepolylines = new DataTable('#polylinestable');
    let tablepolygons = new DataTable('#polygonstable');
</script>
@endsection
