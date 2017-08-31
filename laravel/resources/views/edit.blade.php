@extends('layout')

@section('content')
@php session_start(); $_SESSION['ticket'] = time(); @endphp

<h3>ユーザーの追加</h3>
<h5>リーダーとして追加する場合：最もユーザーの少ない列からメンバーを補充します</h5>
<h5>普通のメンバーとして追加する場合：最もユーザーの少ない列に追加します。</h5>
<div class="well">
  {{ Form::open(['url' => 'add']) }}
    <table>
      <tr>
        <th>名前</th>
        <th>イニシャル ex)A.A</th>
        <th>リーダー/メンバー</th>
      </tr>
      @for($i=0; $i<3; $i++)
        <tr>
          <td>{{ Form::text('addUserName[]','test'.$i) }}</td>
          <td>{{ Form::text('addInitial[]','test'.$i) }}</td>
          <td>{{ Form::select('selectColumn[]',['Leader','Member']) }}</td>
        </tr>
      @endfor
    {{ Form::hidden('ticket',time()) }}
    {{ Form::submit('追加する',['class' => 'pull-right btn-primary']) }}
    </table>
  {{ Form::close() }}
</div>


<h3>ユーザーの削除</h3>
<h5>①リーダーかメンバーか選択します</h5>
<h5>②削除したいメンバーにチェックを入れて決定をクリックします</h5>
<div class="well">
{{ Form::open(['url' => 'select']) }}
    <ul class="list-inline">
      <li>
        {{ Form::radio('category','0',true) }}
        {{ Form::label('category','リーダー') }}
      </li>
      <li>
        {{ Form::radio('category','1') }}
        {{ Form::label('category','メンバー') }}
      </li>
        {{ Form::hidden('ticket',time()) }}
        {{ Form::submit('選択する',['class' => 'pull-right btn-sm']) }}
    </ul>
  {{ Form::close() }}

@if (isset($select_users))
  {{ Form::open(['url' => 'delete']) }}
    <ul class="list-inline">
      @foreach($select_users as $selectuser => $userData)
        <li>
          {{ Form::checkbox('select[]',$userData->user_id) }}
          {{ Form::hidden('group',$userData->move_number_id) }}
          {{ Form::label($userData->name) }}
        </li>
      @endforeach
        {{ Form::submit('削除する',['class' => 'pull-right btn-primary']) }}
    </ul>
  {{ Form::close() }}
@endif
</div>

<h3> リーダーにする / メンバーにする</h3>
<h5>メンバーからリーダーに昇格、あるいはリーダーからメンバーに降格します。</h5>
<h5></h5>
<div class="well">
  {{ Form::open(['url' => 'select2']) }}
    <ul class="list-inline">
      <li>
        {{ Form::radio('category','0',true) }}
        {{ Form::label('category','リーダー') }}
      </li>
      <li>
        {{ Form::radio('category','1') }}
        {{ Form::label('category','メンバー') }}
      </li>
        {{ Form::submit('select',['class' => 'pull-right btn-sm']) }}
    </ul>
  {{ Form::close() }}

@if (isset($select_users2))
  {{ Form::open(['url' => 'promote']) }}
    <ul class="list-inline">
      @foreach($select_users2 as $selectuser => $userData)
        <li>
          {{ Form::checkbox('select[]',$userData->user_id) }}
          {{ Form::hidden('group',$userData->move_number_id) }}
          {{ Form::label($userData->name) }}
        </li>
      @endforeach
        {{ Form::submit('Promote/Demote',['class' => 'pull-right btn-sm']) }}
    </ul>
  {{ Form::close() }}
@endif
</div>
@endsection
