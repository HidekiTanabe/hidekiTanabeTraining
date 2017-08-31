@extends('layout')

@section('content')

@foreach ($all_member as $key => $userData)
  @php $tmp[$userData['user_id']] = $userData['name']; @endphp
@endforeach

@php $i = 1; @endphp
{{ Form::open(['url' => 'replace2']) }}
@foreach ($delete_user_list as $userName)
  <div class="well">
    <h3>降格するリーダー名 : {{ $userName['name'] }}</h3>
    <h3>リーダーにするメンバ :</h3>
        {{ Form::select($userName['id'],$tmp) }}
  </div>
  @php $i++; @endphp
@endforeach

  {{ Form::submit('Replace!') }}
  {{ Form::close() }}

@endsection
