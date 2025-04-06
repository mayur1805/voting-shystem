@extends('layouts.adminLayout')

@section('content')
<div class="container">
    <h2 class="mb-4">Person-wise Yes/No Report</h2>

    <div class="table-responsive">
        <table id="votesReportTable" class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Person</th>
                    <th>Yes Votes</th>
                    <th>No Votes</th>
                    <th>Total Votes</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- DataTable Script -->
<script>
$(document).ready(function() {
    $('#votesReportTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ajax: "{{ route('admin.election.report') }}", // Route to fetch data
        columns: [
            { data: 'candidate', name: 'person' },
            { data: 'yes_votes', name: 'yes_votes' },
            { data: 'no_votes', name: 'no_votes' },
            { data: 'total_votes', name: 'total_votes' }
        ]
    });
});
</script>

@endsection
