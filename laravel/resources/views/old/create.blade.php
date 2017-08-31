@extends('layout')

@section('content')
  <h1>lesson</h1>
  <hr/>
  {!! Form::open(['url' =>'create']) !!}
    {!! Form::submit('新規作成',['class' => 'btn btn-primary form-control']) !!}
  {!! Form::close() !!}
  <br>

  <!--<h3>{{ $createDate['now'] }} ~ {{ $createDate['nextPlan'] }}</h3>-->
  <table class="table table-striped">
      <tr>
        <th>no</th>
        <th>リーダー</th>
        <th>メンバー1</th>
        <th>メンバー2</th>
        <th>メンバー3</th>
      </tr>

    @foreach($newTeams as $newTeam => $teamMembers)
      <tr>
        <td>{{ $newTeam }}</td>
          @foreach($teamMembers as $teamMember => $userData)
            <td>{{ $userData['userName'] }} {{ $userData['move_no']}}</td>
          @endforeach

      </tr>
    @endforeach
  </table>

@stop
