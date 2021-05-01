<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
</head>

<style>
    a:link {
        color: whitesmoke;
        background-color: transparent;
        text-decoration: none;
    }

    a:visited {
        color: cadetblue;
        background-color: transparent;
        text-decoration: none;
    }

    a:hover {
        color: whitesmoke;
        background-color: transparent;
        text-decoration: underline;
    }

    a:active {
        color: whitesmoke;
        background-color: transparent;
        text-decoration: underline;
    }
</style>

<body style="background-color:gray;">
<form action="{{ url("/") }}" method="post">
    @csrf
    關鍵字:<input type="text" name="key" value="{{ $key }}">
    <button type="submit">查詢</button>
    開始:<input name="start_date" type="date" value="{{ $start_date }}">
    結束:<input name="end_date" type="date" value="{{ $end_date }}">
    @if ($page > 1)
        <a href="{{ url("?page={$prev_page}&key={$key}&start_date={$start_date}&end_date={$end_date}") }}" style="color: black">上一頁</a>
    @endif
    <a href="{{ url("?page={$next_page}&key={$key}&start_date={$start_date}&end_date={$end_date}") }}" style="color: black">下一頁</a>
    <a href="{{ url("/") }}" style="color: black">首頁</a>
    <a href="{{ url("/info") }}" style="color: black" target="_blank">info</a>
    {{ count($list) }}
    @if (count($list) > 0)
        <table class="table table-striped table-dark">
            <thead>
            <tr>
                <th scope="col">標題</th>
                <th scope="col">時間</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($list as $value)
                <tr>
                    <td><a href="{{ $value->url }}" target="_blank">{{ $value->title }}</a></td>
                    <td>{{ $value->publish_time }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</form>

@if ($page > 1)
    <a href="{{ url("?page={$prev_page}&key={$key}&start_date={$start_date}&end_date={$end_date}") }}" style="color: black">上一頁</a>
@endif
<a href="{{ url("?page={$next_page}&key={$key}&start_date={$start_date}&end_date={$end_date}") }}" style="color: black">下一頁</a>

</body>
</html>
