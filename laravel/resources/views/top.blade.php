@extends('layout')

@section('header')

@endsection

@section('submit')
@php session_start(); $_SESSION['ticket'] = time(); @endphp

  @if (Session::has('flag'))
  <ul class="list-inline">
    <li>
      {!! Form::open(['url' =>'create']) !!}
        {!! Form::submit('Create',['class' => 'btn btn-primary']) !!}</li>
        {{ Form::hidden('ticket',time()) }}
      {!! Form::close() !!}
    </li>
    <li>
      {!! Form::open(['url' =>'edit']) !!}
        {!! Form::submit('Edit',['class' => 'btn btn-primary']) !!}</li>
      {!! Form::close() !!}
    </li>
  </ul>
  @endif

  <div class="pull-right">
    {{ Form::open(['url' => 'preview']) }}
      <select name="selectDate">
        @foreach($create_date_data as $key => $value)
          @if($key == "thisTime" || $key == "nextPlan")
            @php continue @endphp
          @endif
          <option value="{{ $value[1] }}"
          @php
            if(isset($required_date)){
              if($value[1]==$required_date){
                echo 'selected';
              }else{
                echo "";
              }
            }
           @endphp>{{ $value[0] }}</option>
        @endforeach
      </select>
      {{ Form::submit('GO!'),['class' => 'btn' ] }}
    {{ Form::close() }}
  </div>

  <h3>{{ $create_date_data['thisTime'] }} ~ {{ $create_date_data['nextPlan'] }}</h3>

@endsection

@section('content')
  <div>
    <table class="table table-hover">
        <tr>
          <th>no</th>
            @for($i=0; $i<$count_column; $i++)
              @if($i == 0)
                <th>リーダー</th>
              @else
                <th>メンバー{{ $i }}</th>
              @endif
            @endfor
        </tr>
    @foreach($default_teams as $defaultTeam => $teamMembers)
      <tr>
        <td>{{ $defaultTeam }}</td>
          @foreach($teamMembers as $teamMember => $userData)
            @if ($userData['user_id'] % 52 == 1)
              <td style="background:#0000ff;color:#ffffff;">
                {{ $userData['name'] }}
              </td>
            @else
              <td>{{ $userData['name'] }}</td>
            @endif
          @endforeach
      </tr>
    @endforeach
  </table>
</div>
@endsection
