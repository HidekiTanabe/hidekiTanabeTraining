@extends('layout')

@section('header')
  <h1 class="container">{{ $targetDateData['target'] }}</h1>
@endsection

@section('submit')
  <h2><a href="/">もどる</a></h2>
  <h3>{{ $targetDateData['target'] }} ~ {{ $targetDateData['span'] }}</h3>
@endsection

@section('content')
  <div>
    <table class="table table-hover">
        <tr>
          <th>no</th>
          <th>リーダー</th>
          <th>メンバー1</th>
          <th>メンバー2</th>
          <th>メンバー3</th>
        </tr>
    @foreach($targetTeams as $targetTeam => $teamMembers)
      <tr>
        <td>{{ $targetTeam }}</td>
          @foreach($teamMembers as $teamMember => $userData)
            <td>{{ $userData['userName'] }}</td>
          @endforeach
      </tr>
    @endforeach
  </table>
</div>
@endsection
