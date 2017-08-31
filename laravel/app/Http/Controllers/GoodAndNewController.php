<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Requests\GoodAndNewRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Team;
use App\MoveNumber;
use App\CreateDate;
use Carbon\Carbon;

class GoodAndNewController extends Controller
{
    const LEADER_COLUMN = '0';
    const MEMBER_COLUMN = '1';

    /**
    *トップページに表示するデータを取得し、topビューに渡す。
    */
    public function home()
    {
        $get_user_team_table_recode = $this->getUserTeamTableRecode();
        list($create_date_data,$teams,$all_users,$all_move_numbers) = $get_user_team_table_recode;
        //全チーム振り分けと全ユーザを比較してidがマッチしたらnameを渡す。
        foreach ($teams as $team => $team_data) {
            foreach ($all_users as $user => $user_data) {
                // $teams[$team]['name'] = $all_users->where('id',$team_data['user_id']);
                if ($team_data['user_id'] == $user_data['id']) {
                    $teams[$team]['name'] = $user_data['name'];
                }
            }
            //表示用にteam_noをキーにしてteams配列を整形。
            $default_teams[$team_data['team_no']][] = $team_data;
            // dd($teams[$team]->toArray());
        }
        $count_column = $all_move_numbers->count();
        ksort($default_teams);
        return view('top',compact('default_teams','create_date_data','count_column'));
    }

    /**
    *[create]ボタンを押した時にチームテーブルのそれぞれのレコードがもつ
    *move_numberの値だけteam_noを増やす。
    */

    public function teamCreate(GoodAndNewRequest $request)
    {
        $this->doublePostCheck();
        $get_user_team_table_recode = $this->getUserTeamTableRecode();
        list($create_date,$teams,$all_users,$all_move_numbers) = $get_user_team_table_recode;
        $this->linkName($teams,$all_users,$all_move_numbers);

        //topビューの表示用にteam_noをキーにしてteamsを整形する。
        foreach ($teams as $team => $team_data) {
            $new_teams[$team_data['team_no']][] = $team_data;
        }

        $insert_create_date = ['date' => Carbon::now()];
        CreateDate::insert($insert_create_date);

        $latest_recode = CreateDate::max('create_date_id');
        //新しいレコードとしてチームテーブルにレコード追加してmovenumberid分だけteam_noを増やす。
        foreach ($new_teams as $new_team => $team_members) {
            foreach ($team_members as $team_member => $user_data) {
                $insert_new_team[] = [
                    'team_no' => $user_data['team_no'],
                    'user_id' => $user_data['user_id'],
                    'create_date_id' => $latest_recode,
                    'move_number_id' => $user_data['move_number_id']
                ];
            }
        }
        Team::insert($insert_new_team);
        return redirect('/');
    }

    /*
    *過去のチーム分け情報を参照できる。
    *submitされた日付idからチームテーブルから対象のレコードを取得する。
    */

    public function preview(GoodAndNewRequest $request)
    {
        $required_date = $request->input('selectDate');
        $all_users = User::orderBy('id','asc')->get()->toArray();
        $all_move_numbers = MoveNumber::where('flag',1)->get();
        $target_recode = Team::where('create_date_id',$required_date)
                            ->orderBy('move_number_id','asc')
                            ->orderBy('user_id','asc')
                            ->orderBy('team_no')
                            ->get();


        $all_create_date = CreateDate::orderby('create_date_id','desc')->take(10)->get();
        foreach ($all_create_date as $key => $value) {
            $tmp[$key] = new Carbon($value['date']);
            $create_date_data[$key][] = $tmp[$key]->format('Y-m-d');
            $create_date_data[$key][] = $value['create_date_id'];
        }
        //期間表示用にthisTimeとnextPlanを求める。
        $tmp = new Carbon(CreateDate::where('create_date_id',$required_date)->value('date'));
        $create_date_data['thisTime'] = $tmp->format('Y-m-d');
        $create_date_data['nextPlan'] = $tmp->addWeek(2)->format('Y-m-d');
        //チームにユーザ名を紐づける
        foreach ($target_recode as $team => $team_data) {
            foreach ($all_users as $user => $user_data) {
                if ($team_data['user_id'] == $user_data['id']) {
                    $target_recode[$team]['name'] = $user_data['name'];
                }
            }
        $default_teams[$team_data['team_no']][] = $team_data;
        }

        $count_column = $all_move_numbers->count();
        return view('top',compact('default_teams','create_date_data','required_date','count_column'));
     }


    /*
    *ログインフォームを表示する。
    *
    */
    public function showLoginForm(){
        return view('auth.login');
    }

    /*
    *ログイン処理。
    */
    public function login(GoodAndNewRequest $request)
    {
        $admin_name = "test";
        $admin_password = "password";
        $input_admin_name = $request->input('userName');
        $input_password = $request->input('password');

        if ($admin_name == $input_admin_name && $admin_password == $input_password) {
            $request->session()->put('flag','1');
            $request->session()->flash('message','Login as Admin!!');
            return redirect('/');
        } else {
            $request->session()->flash('message','Login failed...');
            return redirect('login');
        }
    }

    /*
    *ログアウト処理。
    */
    public function logout(GoodAndNewRequest $request)
    {
        $request->session()->forget('flag');
        return redirect('/');
    }

    /*
    *管理画面の表示
    */
    public function showEditForm()
    {
        return view('edit');
    }

    /*
    *ユーザを新しく追加する。
    *リーダーとして追加する場合はリーダー列へ。また、1人リーダにならないように
    *一番少ない列からメンバーを補充
    *メンバーとして追加する場合はメンバーの一番少ない列を探してinsertする。
    */
    public function addUser(GoodAndNewRequest $request)
    {
        // $this->doublePostCheck();

        $add_user_name = $request->input('addUserName');
        $add_initial =  $request->input('addInitial');
        $select_column = $request->input('selectColumn');
        $all_request = $request->except(['_token,ticket']);
        //リクエストを[name,initial,0or1]に加工。
        $new_user = array_map(null,$add_user_name,$add_initial,$select_column);

        $count = MoveNumber::where('flag',1)->count();
        $latest_date = CreateDate::max('create_date_id');
        $all_group = Team::where('create_date_id',$latest_date)->get();
        $count_group = $all_group->count();
        $groups = $all_group->groupBy('move_number_id');

        //最もメンバーの少ない列を求める。
        foreach ($groups as $group => $group_data) {
            if ($count_group >= count($group_data)) {
                $count_group = count($group_data);
                $least_group = $group;
            }
        }
        //ユーザマスタに登録。nullが入ってたらスルー。
        foreach ($new_user as $key => $value) {
            $insert_user[$key]['name'] = $new_user[$key][0];
            $insert_user[$key]['initial'] = $new_user[$key][1];
            $insert_user[$key]['delete_flag'] = 0;
            unset($new_user[$key][0]);
            unset($new_user[$key][1]);
            foreach ($value as $key2 => $value2) {
                if (empty($value2)) {
                    unset($new_user[$key]);
                }
            }
        }
        User::insert($insert_user);

        $all_user = User::where('delete_flag',0)->get();
        $marge_request = array_map(null,$add_user_name,$add_initial,$select_column);

        $i = 1;
        $fill_member_no = 2;
        foreach ($marge_request as $request => $value) {
            if (empty($value[0]) || empty($value[1])) {
                continue;
            }
            if ($value[2] == self::LEADER_COLUMN) {
                //チームへのリーダの追加
                $user_id = $all_user->where('name',$value[0])
                                   ->take(1)
                                   ->values('id')
                                   ->all();
                $new_team = $all_group->max('team_no') + $i++;
                $push_team = [
                    'team_no' => $new_team,
                    'user_id' => $user_id[0]['id'],
                    'create_date_id' => $latest_date,
                    'move_number_id' => 1
                ];
                $insert_team[] = $push_team;
                $all_group->push($push_team);
                //新しくできたチームに最も少ないグループのメンバーをuser_idが小さい順に追加
                //補充する人数は列数-2(リーダー列、最もメンバーが少ない列)
                $count_fill = count($groups) - $fill_member_no;
                //最もメンバーが少ない列を求める。
                $tmp = $all_group->groupBy('move_number_id');
                $count = $all_group->max('team_no');
                foreach ($tmp as $key => $value) {
                    if ($count > count($value)) {
                        $count = count($value);
                        $least_group = $value[$key]['move_number_id'];
                    }
                }
                //最もメンバーの少ない列から補充に必要なレコードを取得。リクエスト数 * $fillCount。
                //補充する前の古いレコードは削除。
                $min_user_id = $all_group->where('move_number_id',$least_group)
                                      ->sortBy('user_id')
                                      ->take($count_fill);
                $delete_collection = $min_user_id->pluck('user_id')->all();
                foreach ($delete_collection as $val2) {
                    foreach ($all_group as $group => $group_data) {
                        if ($group_data['user_id'] == $val2) {
                            $forget_team[] = $group;
                            $delete_team[] = $group_data['id'];
                        }
                    }
                }
                //コレクションから削除
                $all_group->forget($forget_team);
                //新しいレコードとして補充用のメンバーをinsert。
                $column = 2;
                $j = 0;
                $fill_team = $new_team;
                foreach ($min_user_id as $key => $value) {
                    $insert_team[] = [
                        'team_no' => $fill_team,
                        'user_id' => $value['user_id'],
                        'create_date_id' => $latest_date,
                        'move_number_id' => $column++
                    ];
                }
            } elseif($value[2] == self::MEMBER_COLUMN) {
              //メンバーとして追加する場合(最も人数の少ない列の一番上の空きに追加)
              $user_id = $all_user->where('name',$value[0])
                                 ->take(1)
                                 ->values('id')
                                 ->all();

                $tmp = $all_group->groupBy('team_no');
                foreach ($tmp as $key =>$value2) {
                    if ($count > count($value2)) {
                        $team_no = $key;
                        break;
                    }
                }
                $push_group = [
                    'team_no' => $team_no,
                    'user_id' => $user_id[0]['id'],
                    'create_date_id' => $latest_date,
                    'move_number_id' => $least_group,
                ];
                $insert_team[] = $push_group;
                $all_group->push($push_group);
            }
        }
        Team::insert($insert_team);
        if (isset($delete_team)) {
            Team::destroy($delete_team);
        }
        \Session::flash('message','Complete Add User!');
        return redirect('/');
    }

    /*
    *管理画面からユーザを削除する時、検索しやすいようにリーダープールとメンバープールを表示。
    */
    public function selectUser(GoodAndNewRequest $request)
    {
        $select_category = $request->input('category');
        $all_move_numbers = MoveNumber::where('flag',1)->get();
        $all_users = User::where('delete_flag',0)->orderBy('id','asc')->get();

        $delete_users = [];
        $tmp = User::where('delete_flag',1)->get();
        if (!empty($tmp)) {
            $delete_users = $tmp->pluck('id')->all();
        }
        $latest_date = CreateDate::max('create_date_id');

        if ($select_category == self::LEADER_COLUMN) {
            $select_users = Team::where('create_date_id',$latest_date)
                               ->whereNotIn('user_id',$delete_users)
                               ->where('move_number_id',1)->get();
        } elseif($select_category == self::MEMBER_COLUMN) {
            $select_users = Team::where('create_date_id',$latest_date)
                               ->whereNotIn('user_id',$delete_users)
                               ->whereNotIn('move_number_id',[1])->get();
        }
        $select_users = $this->linkName($select_users,$all_users,$all_move_numbers);
        return view('edit',compact('select_users'));
    }

    /*
    *ユーザーの削除。
    *リーダーの削除だったら代理のリーダーを選択させる。
    */
    public function deleteUser(GoodAndNewRequest $request)
    {
        $delete_users = $request->input('select');
        $group_flag = $request->input('group');
        $all_users = User::where('delete_flag',0)->orderBy('id','asc')->get();
        $all_move_numbers = MoveNumber::where('flag',1)->get();
        //メンバーの削除の場合。ユーザーテーブルのdelte_flagを1にする。
        if ($group_flag != self::MEMBER_COLUMN) {
            User::whereIn('id',$delete_users)
                ->where('delete_flag',0)
                ->update(['delete_flag' => 1]);
            $request->session()->flash('message','Complete Delete User!');
            return redirect('edit');
          //リーダーだったら削除後、代わりのリーダーを選ぶページに飛ばす。
        } elseif($group_flag == self::MEMBER_COLUMN) {
            $delete_user_list = $all_users->whereIn('id',$delete_users);
            User::whereIn('id',$delete_users)
                ->where('delete_flag',0)
                ->update(['delete_flag' => 1]);

            $latest_date = CreateDate::max('create_date_id');
            $all_member = Team::where('create_date_id',$latest_date)
                             ->whereNotIn('user_id',$delete_users)
                             ->whereNotIn('move_number_id',[1])->get();
            $all_member = $this->linkName($all_member,$all_users,$all_move_numbers);
            return view('confirmLeader',compact('delete_user_list','all_member'));
        }
    }

    /*
    *リーダーを削除した際に代わりのリーダーを立てる。
    */
    public function replace(GoodAndNewRequest $request)
    {
        // $this->doublePostCheck();
        //削除したリーダーの代わりに選択したメンバーをリーダーにする。
        $select_replace = $request->except(['_token','ticket']);
        $latest_date = CreateDate::max('create_date_id');
        $latest_team = Team::where('create_date_id',$latest_date)->get();

        foreach ($select_replace as $leader_id => $replace_user_id) {
            $data = $latest_team->where('user_id',$leader_id)->pluck('team_no');
            $insert_team[] = [
                'team_no' => $data[0],
                'user_id' => $replace_user_id,
                'create_date_id' => $latest_date,
                'move_number_id' => 1,
            ];
            $delete_team = $latest_team->where('user_id',$replace_user_id)
                                   ->whereNotIn('move_number_id',[1])
                                   ->pluck('id')
                                   ->all();
        }
        Team::insert($insert_team);
        Team::destroy($delete_team);
        $request->session()->flash('message','Complete Delete Leader!');
        return redirect('edit');
    }

    /*
    *リーダーに昇格させる時、リーダーから降格させる時の
    *リーダーグループとメンバーグループの表示。
    */
    public function selectUser2(GoodAndNewRequest $request)
    {
        $select_category = $request->input('category');
        $all_move_numbers = MoveNumber::where('flag',1)->get();
        $all_users = User::where('delete_flag',0)->orderBy('id','asc')->get();

        $delete_users = [];
        $tmp = User::where('delete_flag',1)->get();
        if (!empty($tmp)) {
            $delete_users = $tmp->pluck('id')->toArray();
        }
        $latest_date = CreateDate::max('create_date_id');

        if ($select_category == self::LEADER_COLUMN) {
            $select_users2 = Team::where('create_date_id',$latest_date)
                                ->whereNotIn('user_id',$delete_users)
                                ->where('move_number_id',1)->get();

        } elseif($select_category != self::LEADER_COLUMN) {
            $select_users2 = Team::where('create_date_id',$latest_date)
                                ->whereNotIn('user_id',$delete_users)
                                ->whereNotIn('move_number_id',[1])->get();

        }
        $select_users2 = $this->linkName($select_users2,$all_users,$all_move_numbers);
        return view('edit',compact('select_users2'));
    }

    /*
    *リーダーをメンバーに降格、メンバーをリーダーに降格する。
    *リーダーをメンバーに降格する時は代わりのユーザを選んで代わりのリーダーとする。
    *メンバーをリーダーに昇格させる時は、新しいチームとしてリーダーにする。
    *同時に1人リーダーにならないように最もメンバーの少ない列からuser_idん小さい順にメンバーを補充する。
    */
    public function promote(GoodAndNewRequest $request)
    {
        $target_users = $request->input('select');
        $group_flag = $request->input('group');

        $all_users = User::where('delete_flag',0)->get();
        $all_move_numbers = MoveNumber::where('flag',1)->get()->toArray();
        $latest_date = CreateDate::max('create_date_id');
        $teams = Team::where('create_date_id',$latest_date)->get();

        $max_team_no = $teams->max('team_no');
        $new_team_no = $max_team_no + 1;


        if ($group_flag != 1) {
            //リクエストされたユーザーを一人ずつ取り出して新しい列に登録
            foreach ($target_users as $key => $target_user) {
            $insert_team[] = [
              'team_no' => ++$max_team_no,
              'user_id' => $target_user,
              'create_date_id' => $latest_date,
              'move_number_id' => 1
            ];

            $delete_users[] = $teams->where('user_id',$target_user)
                                 ->whereNotIn('move_number_id',[1])
                                 ->pluck('id')
                                 ->toArray();
          }
            //最も数の少ない列を$least_groupに格納する。
            $tmp = $teams->groupBy('move_number_id');
            $count_fill = count($tmp) - 2;
            $count_new_recode = count($target_users);
            foreach ($tmp as $key => $value) {
                if ($max_team_no > count($value)) {
                    $least_group = $value[$key]['move_number_id'];
                }
            }

            $min_user_id = $teams->where('move_number_id',$least_group)
                                  ->sortBy('user_id')
                                  ->take($count_fill * $count_new_recode);

            $column = 2;
            $i = 0;
            foreach ($min_user_id as $key => $value) {
                if ($i == $count_fill) {
                    $new_team_no++;
                    $column = 2;
                    $i = 0;
                }
                $insert_team[] = [
                    'team_no' => $new_team_no,
                    'user_id' => $value['user_id'],
                    'create_date_id' => $latest_date,
                    'move_number_id' => $column
                ];
                $column++;
                $i++;
            }
            $delete_users[] =  $min_user_id->pluck('id')->toArray();
            Team::insert($insert_team);
            Team::destroy(array_flatten($delete_users));
            \Session::flash('message','Promote to Leader as New Team!');
            return redirect('edit');

        } elseif($group_flag == 1) {
            $all_member = $teams->whereNotIn('move_number_id',[1]);
              $delete_user_list = $teams->whereIn('id',$target_users);
              $all_member = $this->linkName($all_member,$all_users,$all_move_numbers);
              $delete_user_list = $this->linkName($delete_user_list,$all_users,$all_move_numbers);
              return view('confirmLeader2',compact('delete_user_list','all_member'));
        }
    }



    public function replace2(GoodAndNewRequest $request)
    {
        $select_replace = $request->except('_token');
        $latest_date = CreateDate::max('create_date_id');
        $latest_team = Team::where('create_date_id',$latest_date)->get();

        foreach ($select_replace as $leader_id => $replace_user_id) {
            $team_no = $latest_team->where('user_id',$leader_id)->pluck('team_no')->toArray();
            $insert_member[] = [
                'team_no' => $team_no[0],
                'user_id' => $replace_user_id,
                'create_date_id' => $latest_date,
                'move_number_id' => 1
            ];
            $team_no = $latest_team->where('user_id',$replace_user_id)
                                ->whereNotIn('move_number_id',[1])
                                ->pluck('team_no')
                                ->toArray();
            $move_number_id = $latest_team->where('user_id',$replace_user_id)
                                         ->whereNotIn('move_number_id',[1])
                                         ->pluck('move_number_id')
                                         ->toArray();

            $insert_member[] = [
                'team_no' => $team_no[0],
                'user_id' => $leader_id,
                'create_date_id' => $latest_date,
                'move_number_id' => $move_number_id[0],
            ];

            $delete_user[] = $latest_team->where('user_id',$replace_user_id)
                                     ->whereNotin('move_number_id',[1])
                                     ->pluck('id')
                                     ->toArray();

           $delete_user[] = $latest_team->where('user_id',$leader_id)
                                    ->where('move_number_id',1)
                                    ->pluck('id')
                                    ->toArray();
        }
        Team::insert($insert_member);
        Team::destroy(array_flatten($delete_user));

        \Session::flash('message','Promote to Leader as New Team!');
        return redirect('/');
    }



    private function getUserTeamTableRecode()
    {
        $latest_date = CreateDate::max('create_date_id');
        $latest_recode = Team::where('create_date_id',$latest_date)->max('create_date_id');
        // 直近のcreate_date_idと対応する日付をcreate_dateテーブルから取得
        $all_create_date = CreateDate::orderby('create_date_id','desc')->take(10)->get();
        //日付をY-m-dに加工する。
        foreach ($all_create_date as $key => $value) {
            $tmp[$key] = new Carbon($value['date']);
            $create_date_data[$key][] = $tmp[$key]->format('Y-m-d');
            $create_date_data[$key][] = $value['create_date_id'];
        }

        $tmp = new Carbon($create_date_data[0][0]);
        //期間を取得
        $create_date_data['thisTime'] = $tmp->format('Y-m-d');
        $create_date_data['nextPlan'] = $tmp->addWeek(2)->format('Y-m-d');

        //userを全件取得
        $all_users = User::where('delete_flag',0)->orderBy('id','asc')->get();
        //削除済みユーザを取得
        $tmp = User::where('delete_flag',1)->get()->toArray();
        if ($tmp != []) {
            foreach ($tmp as $value) {
                $delete_users[] = $value['id'];
            }
        } else {
            $delete_users = [];
        }
        //直近のチーム振り分けを降順で全て取得する。
        $teams = Team::where('create_date_id','=',$latest_recode)
                     ->whereNotIn('user_id',$delete_users)
                     ->orderBy('move_number_id')
                     ->orderBy('user_id')
                     ->orderBy('team_no')
                     ->get();
        //MoveNumberの有効なレコードを取得
        $all_move_numbers = MoveNumber::where('flag',1)->get();
        return [$create_date_data,$teams,$all_users,$all_move_numbers];
    }



    private function linkName($teams,$all_users,$all_move_numbers){
        $max_team_number = $teams->max('team_no');
        foreach ($teams as $team => $team_data) {
            foreach ($all_users as $user => $user_data) {
                if ($team_data['user_id'] == $user_data['id']) {
                    $teams[$team]['name'] = $user_data['name'];
                }
            }
            foreach ($all_move_numbers as $allMoveNumber => $moveData) {
                if ($team_data['move_number_id'] == $moveData['move_number_id']) {
                    $teams[$team]['team_no'] += $moveData['move_to'];
                    if ($teams[$team]['team_no'] > $max_team_number) {
                        $teams[$team]['team_no'] -= $max_team_number;
                    }
                }
            }
    }
      return $teams;
    }


    private function doublePostCheck() {
        session_start();
        if (isset($_SESSION['ticket'])) {
            if ($_POST['ticket'] != $_SESSION['ticket']) {
                die("二重投稿です"."<a href='/'>戻る</a>");
            }
        } else {
            die("二重投稿です。"."<a href='/'>戻る</a>");
        }
        session_unset();
    }
}
