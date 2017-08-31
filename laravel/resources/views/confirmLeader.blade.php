@extends('layout')

@section('content')
@php session_start(); $_SESSION['ticket'] = time(); @endphp
@foreach ($all_member as $key => $userData)
  @php $tmp[$userData['user_id']] = $userData['name']; @endphp
@endforeach

@php $i = 1; @endphp
{{ Form::open(['url' => 'replace']) }}
@foreach ($delete_user_list as $userName)
  <div class="well">
    <h3>削除するリーダー : {{ $userName['name'] }}</h3>
    <h3>代わりのリーダー :</h3>
        {{ Form::select($userName['id'],$tmp) }}
  </div>
  @php $i++; @endphp
@endforeach
{{ Form::hidden('ticket',time()) }}
{{ Form::submit('決定') }}
{{ Form::close() }}

<a href="/edit">戻る</a>

@endsection
