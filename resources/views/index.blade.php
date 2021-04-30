<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
</head>
<body style="background-color:gray;">
<form action="{{ url("/key") }}" method="post">
    @csrf
    關鍵字:<input type="text" name="key">
    <button type="submit">查詢</button>

    @if (count($list) > 0)
        <table class="table table-striped table-dark">
            <thead>
            <tr>
                <th scope="col">標題</th>
                <th scope="col">連結</th>
                <th scope="col">時間</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($list as $value)
                <tr>
                    <td>{{ $value->title }}</td>
                    <td><a href="{{ $value->url }}" target="_blank">連結</a></td>
                    <td>{{ $value->publish_time }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</form>
</body>
</html>
