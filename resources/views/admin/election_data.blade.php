@extends('layouts.adminLayout')

@section('content')

<div class="container mt-5">
    <div class="card shadow-lg p-4">
        <h2 class="text-center text-primary mb-4">🗳️ Election Data</h2>

        <!-- DataTable -->
        <table id="votesTable" class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Candidate Name</th>
                    <th>Total Votes</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function () {
    // Load Election Data
    $('#votesTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: "{{ route('admin.election.data') }}",
        columns: [
            { data: 'candidate', name: 'candidate', title: 'Candidate Name' },
            { data: 'total_votes', name: 'total_votes', title: 'Total Votes' }
        ],
        order: [[1, 'desc']],
        language: {
            emptyTable: "No votes recorded yet"
        }
    });
});
</script>
@endsection
