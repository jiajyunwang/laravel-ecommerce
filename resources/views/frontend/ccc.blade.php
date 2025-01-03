{{-- resources/views/frontend/ccc.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
</head>
<body>
    <h1>Search Results</h1>

    @if(isset($results['hits']['hits']) && count($results['hits']['hits']) > 0)
        <ul>
            @foreach($results['hits']['hits'] as $hit)
                <li>
                    <strong>{{ $hit['_source']['title'] }}</strong><br>
                    {{ $hit['_source']['description'] }}<br>
                    Price: ${{ $hit['_source']['price'] }}
                </li>
            @endforeach
        </ul>
    @else
        <p>No results found for your query.</p>
    @endif
</body>
</html>