<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\LoginFormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @return view
     */
    public function showLogin(){
        return view('login.login_form');
    }

    /**
     * @param App\Http\Requests\LoginFormRequest
     * $request
     */
    public function login(LoginFormRequest $request){ // $requestがLoginFormRequest型だったら(タイプヒント) 
        $credentials = $request->only('email', 'password');//$requestからemailとpasswordだけを取得

       
        $user = User::where('email', '=', $credentials['email'] )->first();

        if(!is_null($user)){
            if($user->locked_flag === 1){  // 1.アカウントがロックされていたら弾く
                return back()->withErrors([ // 配列をセッションに保存
                    'login_error' => 'アカウントがロックされています。',
                ]);
            }
            if(Auth::attempt($credentials)) { //ログインが成功したら
                $request->session()->regenerate(); //セッションIDの再設定
                if($user->error_count > 0){
                    $user->error_count = 0; // 2.成功したらerror_countカラムを0にする。
                    $user->save(); // 保存する。
                }
                // redirect('home')...home.blede.phpに直ちに再リクエストする。
                // with($key, $value)... セッションに保存
                return redirect()->route('home')->with('login_success', 'ログイン成功しました！');  
            }

            // 3.ログインに失敗したらerr_countを1増やす。
            $user->error_count =  $user->error_count + 1;

            // 4.err_countが6以上の場合はアカウントをロックする。
            if($user->error_count > 5){
                $user->locked_flag = 1;
                $user->save();
                return back()->withErrors([ // 配列をセッションに保存
                    'login_error' => 'アカウントがロックされました。',
                ]);
            }
            $user->save();
        }

        //attemptが失敗したら
        return back()->withErrors([ // 配列をセッションに保存
            'login_error' => 'メールアドレスかパスワードが間違っています。',
        ]);
    }

    /**
     * ユーザーをアプリケーションからログアウトさせる
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('showLogin')->with('logout', 'ログアウトしました！');
    }
}
