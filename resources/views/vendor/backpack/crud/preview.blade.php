{{-- In resources/views/projects/preview.blade.php --}}
@extends(backpack_view('blank'))
@section('content')
    <div class="row">
        <h3>Resources</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Namespace</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Replica Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resources as $resource)
                    <tr>
                        <td>{{ $resource->id }}</td>
                        <td>{{ $resource->name }}</td>
                        <td>{{ $resource->namespace }}</td>
                        <td>{{ $resource->type }}</td>
                        <td>{{ $resource->status }}</td>
                        <td>{{ $resource->replica }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@stop

